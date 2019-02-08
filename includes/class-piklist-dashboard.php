<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Dashboard
 * Controls admin dashboard widgets and features.
 *
 * @package     Piklist
 * @subpackage  Dashboard
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Dashboard
{
  /**
   * @var array Registered dashboard widgets.
   * @access private
   */
  private static $widgets = array();
  
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
    global $pagenow;
    
    if (is_admin())
    {
      add_filter('piklist_part_process-dashboard', array(__CLASS__, 'part_process'), 5);
      add_filter('piklist_part_process-dashboard', array('piklist_meta', 'part_process'), 10);
      add_filter('piklist_argument_validation_rules', array(__CLASS__, 'validation_rules'));
      
      add_action('init', array(__CLASS__, 'register_arguments'));
      add_action('wp_dashboard_setup', array(__CLASS__, 'register_dashboard_widgets'));
      add_action('wp_network_dashboard_setup', array(__CLASS__, 'register_dashboard_widgets'));
    }
  }

  /**
   * register_dashboard_widgets
   * Register dashboard widgets.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_dashboard_widgets()
  {
    piklist::process_parts('dashboard', piklist_arguments::get('dashboard', 'part'), array(__CLASS__, 'register_dashboard_widgets_callback'));
  }

  /**
   * register_dashboard_widgets_callback
   * Handle the registration of a dashboard widget.
   *
   * @param array $arguments The dashboard widget configuration.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_dashboard_widgets_callback($arguments)
  {
    extract($arguments);
    
    $screen = get_current_screen();
    
    self::$widgets[$id] = $arguments;
    
    if (piklist_meta::update_meta_box($screen, $id))
    {
      piklist_meta::update_meta_box($screen, $id, 'remove');
    }
    
    wp_add_dashboard_widget(
      $id
      ,__($data['title'])
      ,array(__CLASS__, 'render_dashboad_widget')
    );
  }
  
  /**
   * render_dashboad_widget
   * Render the dashboad widget
   *
   * @param mixed $null No data.
   * @param array $data Widget configuration.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function render_dashboad_widget($null, $data)
  {
    $widget = self::$widgets[$data['id']];
    
    do_action('piklist_pre_render_dashboard_widget', $null, $widget);
    
    if ($widget['render'])
    {
      foreach ($widget['render'] as $render)
      {
        if (is_array($render))
        {
          call_user_func($render['callback'], $null, $render['args']);
        }
        else
        {
          piklist::render($render, array(
            'data' => $widget['data']
          ));
        }
      }
    }

    do_action('piklist_post_render_dashboard_widget', $null, $widget);
  }

  /**
   * part_process
   * Dashboard specific processing on whether to allow core dashboard widgets.
   *
   * @param array $part being validated
   *
   * @return array The part object being processed.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function part_process($part)
  {
    if ($part['id'] == 'dashboard_right_now')
    {
      return piklist::get_settings('piklist_core', 'dashboard_at_a_glance') ? $part : null;
    }

    return $part;
  }
  
  /**
   * register_arguments
   * Register arguments for our helper methods
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_arguments()
  {
    piklist_arguments::register('dashboard', array(
      // Basics
      'title' => array(
        'description' => __('The title of the meta box.', 'piklist')
      )
          
      // Permissions
      ,'capability' => array(
        'description' => __('The user capability needed by the user to view the meta box.', 'piklist')
        ,'validate' => 'capability'
      )
      ,'role' => array(
        'description' => __('The user role needed by the user to view the meta box.', 'piklist')
        ,'validate' => 'role'
      )
            
      // Display - Conditions
      ,'network' => array(
        'description' => __('Show the dashboard widget on the network dashboard.', 'piklist')
        ,'default' => false
        ,'validate' => 'network_dashboard'
      )
    ));
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
      'network_dashboard' => array(
        'name' => __('Network', 'piklist')
        ,'callback' => array(__CLASS__, 'validate_network_dashboard')
      )
    ));

    return $validation_rules;
  }
  
  /**
   * validate_network_dashboard
   *
   * @param $argument
   * @param $value
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_network_dashboard($argument, $value)
  {
    global $current_screen;
    
    if (!piklist::is_bool($value) && $value != 'only') 
    {
      return sprintf(__('The argument <strong>Network</strong> with the value of <strong>%s</strong> is not valid.', 'piklist'), $value);
    }
    
    if (isset($current_screen) && $current_screen->id == 'dashboard-network')
    {
      return $value || $value == 'only';
    }
    elseif (isset($current_screen) && $current_screen->id == 'dashboard')
    {
      return $value === true;
    }
    
    return false;
  }
}