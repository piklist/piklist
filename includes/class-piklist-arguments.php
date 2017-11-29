<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly
  
/**
 * Piklist_Arguments
 * Manges and Validates any arguments used for building Piklist Components.
 *
 * @package     Piklist
 * @subpackage  Parameters
 * @copyright   Copyright (c) 2012-2016, Piklist, LLC.
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
  public static function validate($component, $arguments)
  {
    $warnings = array();
  
    if (($registered_arguments = self::get($component)) === false)
    {
      array_push($warnings, __('No arguments have been registered for this component.', 'piklist'));
    }
    else
    {
      foreach ($registered_arguments as $argument => $data)
      {
        if (isset($data['required']) && $data['required'] === true)
        {
          array_push($warnings, sprintf(__('The argument <strong>%s</strong> is required.'), $argument));
        }
        elseif (isset($arguments[$argument]) && !empty($arguments[$argument]))
        {
          list($valid, $value) = self::check_and_cast($arguments[$argument], $data['type']);
          
          if ($valid)
          {
            $arguments[$argument] = $value;
          }
          else
          {
            array_push($warnings, sprintf(__('The argument <strong>%s</strong> was the incorrect type; <strong>%s</strong> was expected.'), $argument, $data['type']));
          }
          
          if (isset($data['allowed']) && !in_array($arguments[$argument], $data['allowed']))
          {
            array_push($warnings, sprintf(__('The argument <strong>%s</strong> is not allowed; <em>%s</em>.'), $argument, implode(',', $data['allowed'])));
          }
        }
        
        if (!isset($arguments[$argument]) || empty($arguments[$argument]) || is_null($arguments[$argument]))
        {
          $arguments[$argument] = isset($data['default']) ? $data['default'] : null;
        }
      }
    }
    
    if (!empty($warnings))
    {
      foreach ($warnings as $warning)
      {
        piklist::error($warning);
      }
    }
    
    return array(empty($warnings), $arguments);
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
  public static function check_and_cast($value, $type = 'string')
  {
    if (is_array($value) && $type != 'array')
    {
      $items = $value;
      
      foreach ($items as &$item)
      {
        list($valid, $_value) = self::check_and_cast($item, $type);
        
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
        list($valid, $value) = self::check_and_cast($value, $type);
        
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
        
          if (($valid = in_array(strtolower($value), array('0', '1', 'true', 'false', 'on', 'off', 'yes', 'no', 'y', 'n'))) === true || is_bool($value))
          {
            $value = piklist::to_bool($value);
          }
      
        break;
      
        case 'array':
      
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
}