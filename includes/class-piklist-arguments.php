<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
  
/**
 * Piklist_Arguments
 * Manges and Validates any arguments used for building Piklist Components.
 *
 * @package     Piklist
 * @subpackage  Parameters
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Arguments
{
  /**
   * @var array Registered arguments.
   * @access private
   */
  private static $arguments = array();
  
  /**
   * @var array Registered validation rules.
   * @access private
   */
  private static $validation_rules = array();
  
  /**
   * _construct
   * Class constructor.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function _construct()
  {
    add_action('init', array(__CLASS__, 'init'));
    
    add_filter('piklist_argument_validation_rules', array(__CLASS__, 'validation_rules'));
  }
  
  /**
   * init
   * Initializes system.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function init()
  {
    self::$validation_rules = apply_filters('piklist_argument_validation_rules', self::$validation_rules);
  }
  
  /**
   * register
   * Register and paramters for use with a component.
   *
   * @param $arguments
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register($component, $arguments)
  {
    if (!isset(self::$arguments[$component]))
    {
      self::$arguments[$component] = array();
      
      foreach ($arguments as $argument => $data)
      {
        if (!isset($data['name']))
        {
          $data['name'] = piklist::humanize($argument);
        }
        
        if (!isset($data['type']))
        {
          $data['type'] = 'string';
        }
        
        self::$arguments[$component][$argument] = $data;
      }
      
      if (empty(self::$arguments[$component]))
      {
        unset(self::$arguments[$component]);
      }
    }
  }
  
  /**
   * get
   * Return the argument options for a component.
   *
   * @param $arguments
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get($component, $return = 'all', $protected = false)
  {
    $output = false;
    
    if (isset(self::$arguments[$component]))
    {
      switch ($return)
      {
        case 'part':

          foreach (self::$arguments[$component] as $argument => $data)
          {
            $output[$argument] = $data['name'];
          }
          
        break;
        
        case 'default':

          foreach (self::$arguments[$component] as $argument => $data)
          {
            $output[$argument] = isset($data['default']) ? $data['default'] : null;
          }
          
        break;
        
        
        case 'all':
        default:
          
          $output = self::$arguments[$component];
          
          /**
           * piklist_arguments
           * Allows other components to add arguments.
           *
           * @param array $arguments
           * @param string $component
           *
           * @since 1.0
           */
          $_output = apply_filters('piklist_arguments', array(), $component);
          
          /**
           * piklist_arguments_{component}
           * Allows other components to add arguments.
           *
           * @param array $arguments
           *
           * @since 1.0
           */
          $_output = apply_filters("piklist_arguments_{$component}", $_output);
          
          $output = array_merge($_output, $output);
        
        break;
      }
    }

    return $output;
  }
  
  /**
   * validate
   * Validate the argument values passed against the registered argument set.
   *
   * @param $component
   * @param $arguments
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate($component, $arguments, $method = false)
  {
    $errors = array();
  
    if (($registered_arguments = self::get($component)) === false)
    {
      array_push($errors, __('No arguments have been registered for this part.', 'piklist'));
    }
    else
    {
      foreach ($registered_arguments as $argument => $data)
      {
        $argument_label = $method ? $argument : piklist::humanize($argument);
        
        if (isset($data['required']) && $data['required'] === true)
        {
          array_push($errors, sprintf(__('The argument <strong>%s</strong> is required.', 'piklist'), $argument));
        }
        elseif (isset($arguments[$argument]) && !empty($arguments[$argument]))
        {
          list($valid, $value) = self::check_and_cast($arguments[$argument], $data['type'], $method);
          
          if ($valid)
          {
            $arguments[$argument] = $value;
          }
          else
          {
            array_push($errors, sprintf(__('The argument <strong>%s</strong> was the incorrect type; <strong>%s</strong> was expected.', 'piklist'), $argument_label, $data['type']));
          }

          if (isset($data['validate']) && isset(self::$validation_rules[$data['validate']])) 
          {
            $validation_rule = self::$validation_rules[$data['validate']];
          
            if (isset($validation_rule['callback']))
            {
              $response = call_user_func_array($validation_rule['callback'], array($argument, $value));
    
              if (is_string($response))
              {
                array_push($errors, $response);
              }
            }
            else
            {
              array_push($errors, sprintf(__('The argument <strong>%s</strong> has a validation rule applied to the argument but there is no callback method specified.', 'piklist'), $argument_label));
            }
          }
          elseif (isset($data['validate']) && !isset(self::$validation_rules[$data['validate']]))
          {
            array_push($errors, sprintf(__('The argument <strong>%s</strong> has a validation rule <strong>%s</strong> set but it does not exist.', 'piklist'), $argument_label, $data['validate']));
          }
        }
        
        if (!isset($arguments[$argument]) || empty($arguments[$argument]) || is_null($arguments[$argument]))
        {
          $arguments[$argument] = isset($data['default']) ? $data['default'] : null;
        }
      }
    }
    
    if (!empty($errors))
    {
      foreach ($errors as $error)
      {
        piklist::error($error);
      }
    }
    
    return array(empty($errors), $arguments);
  }
  
  /**
   * check_and_cast
   * Check the type of a value and cast it to that type if valid.
   *
   * @param $value
   * @param $type
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function check_and_cast($value, $type = 'string', $method = false)
  {
    if (is_array($value) && $type != 'array')
    {
      $items = $value;
      
      foreach ($items as &$item)
      {
        list($valid, $_value) = self::check_and_cast($item, $type, $method);
        
        if ($valid)
        {
          $item = $_value;
        }
        else
        {
          return array($valid, $value);
        }
      }
      
      $value = $items;
    }
    elseif (is_array($type))
    {
      $types = $type;
    
      foreach ($types as $type)
      {
        list($valid, $value) = self::check_and_cast($value, $type, $method);
        
        if ($valid)
        {
          break;
        }
      }
    }
    else
    {
      switch ($type)
      {
        case 'int':
        case 'integer':
      
          if (($valid = (is_integer($value) | ctype_digit($value))) === true)
          {
            $value = (int) $value;
          }
      
        break;
      
        case 'float':
      
          if (($valid = is_float($value)) === true)
          {
            $value = (float) $value;
          }
      
        break;
      
        case 'bool':
        case 'boolean':
        
          if (($valid = piklist::is_bool($value)) === true)
          {
            $value = piklist::to_bool($value);
          }
      
        break;
      
        case 'array':
        
          if (is_string($value) && !$method)
          {
            if (strstr($value, ','))
            {
              $value = piklist::explode(',', $value);
            }
            else
            {
              $value = array($value);
            }
          }
      
          $valid = is_array($value);
      
        break;
      
        case 'object':
      
          $valid = is_object($value);
      
        break;
      
        case 'string':
        default:
      
          if (($valid = is_string($value)) === true)
          {
            $value = (string) $value;
          }
      
        break;
      }
    }

    return array($valid, $value);
  }
  

  
  /**
   * Included Validation Callbacks
   */

  /**
   * validation_rules
   * Array of included validation rules.
   *
   * @param array $validation_rules Validation rules.
   *
   * @return array Validation rules.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validation_rules($validation_rules)
  {
    $validation_rules = array_merge($validation_rules, array(
      'role' => array(
        'name' => __('Role', 'piklist')
        ,'callback' => array(__CLASS__, 'validate_role')
      )
      ,'capability' => array(
        'name' => __('Capability', 'piklist')
        ,'callback' => array(__CLASS__, 'validate_capability')
      )
      ,'logged_in' => array(
        'name' => __('Logged In', 'piklist')
        ,'callback' => array(__CLASS__, 'validate_logged_in')
      )
      ,'post_type' => array(
        'name' => __('Post Type', 'piklist')
        ,'callback' => array(__CLASS__, 'validate_post_type')
      )
      ,'post_status' => array(
        'name' => __('Post Status', 'piklist')
        ,'callback' => array(__CLASS__, 'validate_post_status')
      )
      ,'post_format' => array(
        'name' => __('Post Format', 'piklist')
        ,'callback' => array(__CLASS__, 'validate_post_format')
      )
      ,'page_template' => array(
        'name' => __('Page Template', 'piklist')
        ,'callback' => array(__CLASS__, 'validate_page_template')
      )
    ));

    return $validation_rules;
  }
  
  /**
   * validate_role
   *
   * @param $argument
   * @param $value
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_role($argument, $value)
  {
    return current_user_can($value);
  }
  
  /**
   * validate_capability
   *
   * @param $argument
   * @param $value
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_capability($argument, $value)
  {
    return current_user_can($value);
  }
  
  /**
   * validate_logged_in
   *
   * @param $argument
   * @param $value
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_logged_in($argument, $value)
  {
    return is_user_logged_in();
  }  
  
  /**
   * validate_post_type
   *
   * @param $argument
   * @param $value
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_post_type($argument, $value)
  {
    global $post;
    
    return (isset($_REQUEST['post_type']) && $value === $_REQUEST['post_type']) || !$post || in_array($post->post_type, $value);
  }
  
  /**
   * validate_post_status
   *
   * @param $argument
   * @param $value
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_post_status($argument, $value)
  {
    return count(array_intersect($value, get_post_stati('', 'names'))) > 0;
  }
  
  /**
   * validate_post_format
   *
   * @param $argument
   * @param $value
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_post_format($argument, $value)
  {
    global $post;
    
    return in_array(get_post_format($post), $value);
  }
  
  /**
   * validate_page_template
   *
   * @param $argument
   * @param $value
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_page_template($argument, $value)
  {
    return count(array_intersect($value, array_keys(get_page_templates()))) > 0;
  }
}