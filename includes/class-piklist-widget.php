<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Widget
 * Controls widget modifications and features.
 *
 * @package     Piklist
 * @subpackage  Widget
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Widget
{
  /**
   * @var array The current widget.
   * @access public
   */
  public static $current_widget = null;

  /**
   * @var string The class used to handle the widget instances.
   * @access private
   */
  private static $widget_class = 'piklist_universal_widget';
  
  /**
   * @var array Contains any widget groups.
   * @access private
   */
  private static $widget_groups = array();

  /**
   * @var array Contains any widget forms.
   * @access private
   */
  private static $widget_forms = array();

  /**
   * @var array Classes for registered widgets.
   * @access private
   */
  private static $widget_classes = array();

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
    add_action('init', array('piklist_widget', 'init'));
    add_action('widgets_init', array('piklist_widget', 'register_widgets'));
    add_action('widgets_init', array('piklist_widget', 'register_widget_groups'), 90);

    add_filter('dynamic_sidebar_params', array('piklist_widget', 'dynamic_sidebar_params'));
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
    self::register_sidebars();
  }

  /**
   * register_sidebars
   * Register sidebars via the piklist_sidebars
   * Sets better defaults than WordPress
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_sidebars()
  {
    /**
     * piklist_sidebars
     * Filter register_sidebar()
     *
     * @param array Sidebar parameters.
     *
     * @since 1.0
     */
    $sidebars = apply_filters('piklist_sidebars', array());

    foreach ($sidebars as $sidebar)
    {
      register_sidebar(array_merge(array(
        'name' => $sidebar['name']
        ,'id' => sanitize_title_with_dashes($sidebar['name'])
        ,'description' => isset($sidebar['description']) ? $sidebar['description'] : null
        ,'before_widget' => isset($sidebar['before_widget']) ? $sidebar['before_widget'] : '<div id="%1$s" class="widget-container %2$s">'
        ,'after_widget' => isset($sidebar['after_widget']) ? $sidebar['after_widget'] : '</div>'
        ,'before_title' => isset($sidebar['before_title']) ? $sidebar['before_title'] : '<h3 class="widget-title">'
        ,'after_title' => isset($sidebar['after_title']) ? $sidebar['after_title'] : '</h3>'
     ), $sidebar));
    }
  }

  /**
   * register_widget_groups
   * Groups widgets for universal widgets.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_widget_groups()
  {
    global $wp_widget_factory;

    foreach (self::$widget_groups as $add_on => $widget)
    {
      if (!empty($widget['widgets']))
      {
        $wp_widget_factory->widgets[$widget['class']] = new self::$widget_class($widget['class'], $widget['title'], $widget['description'], $widget['customize_selective_refresh'], $widget['widgets']);
      }
    }
  }
  
  /**
   * register_widgets
   * Register the widgets parts folder
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_widgets()
  {
    // Run all widgets through callback
    $data = array(
              'title' => 'Title'
              ,'description' => 'Description'
              ,'tags' => 'Tags'
              ,'class' => 'Class'
              ,'height' => 'Height'
              ,'width' => 'Width'
              ,'standalone' => 'Standalone'
              ,'customize_selective_refresh' => 'Customize Selective Refresh'
            );
    
    piklist::process_parts('widgets', $data, array('piklist_widget', 'register_widgets_callback'));

    // Create widget groups 
    $addons_paths = piklist::paths();
    
    foreach ($addons_paths as $from => $path)
    {
      if (!piklist::directory_empty($path . '/parts/widgets'))
      {
        $widget_class_name = self::$widget_class . '_' . piklist::slug($from);

        $suffix = '';
        $title = '';
        $description = '';

        if (isset(piklist_add_on::$available_add_ons[$from]))
        {
          if (stripos(piklist_add_on::$available_add_ons[$from]['name'], 'widget') === false)
          {
            $suffix = ' ' . __('Widgets', 'piklist');
          }

          $title = piklist_add_on::$available_add_ons[$from]['name'] . $suffix;

          $description = strip_tags(piklist_add_on::$available_add_ons[$from]['description']);
        }
        elseif ($from == 'piklist')
        {
          $title = __('Piklist Widgets', 'piklist');
          $description = __('Core Widgets for Piklist.', 'piklist');
        }
        elseif ($from == 'theme')
        {
          $current_theme = wp_get_theme();

          $title = $current_theme . ' ' . __('Widgets', 'piklist');
          $description = sprintf(__('Widgets for the %s Theme', 'piklist'), $current_theme);
        }

        /**
         * piklist_widget_groups_customize_selective_refresh
         * Filters the customize_selective_refresh setting for a widget groups
         *
         * @param boolean The default setting
         * @param string There the group is from
         *
         * @since 1.0
         */
        $customize_selective_refresh = apply_filters('piklist_widget_groups_customize_selective_refresh', true, $from);
        
        self::$widget_groups[$from] = array(
          'class' => $widget_class_name
          ,'title' => $title
          ,'description' => $description
          ,'customize_selective_refresh' => $customize_selective_refresh
          ,'widgets' => array()
        );
      }
    }
  }

  /**
   * register_widgets_callback
   * Process the resulting parts from the registration of the widgets part folder.
   *
   * @param $arguments
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_widgets_callback($arguments)
  {
    global $wp_widget_factory;

    extract($arguments);
    
    if (substr(strtolower($part), -9) == '-form.php')
    {
      $id = substr($id, 0, -5);

      self::$widget_forms[$id] = $arguments;
    }
    elseif (isset(self::$widget_groups[$add_on]) && !$data['standalone'])
    {
      self::$widget_groups[$add_on]['widgets'][$id] = $arguments;
    }
    elseif ($data['standalone'] == true)
    {
      $class = self::$widget_class . '_' . $id;
      
      $wp_widget_factory->widgets[$class] = new self::$widget_class($class, $data['title'], $data['description'], $data['customize_selective_refresh'], array($id => $arguments));
    }
  }
  
  /**
   * widget
   * Get the current widget object.
   *
   * @return array The current widget object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function widget()
  {
    global $wp_widget_factory;
    
    return isset($wp_widget_factory->widgets[self::$current_widget]) ? $wp_widget_factory->widgets[self::$current_widget] : null;
  }

  /**
   * dynamic_sidebar_params
   * Add helpful classes to widget areas on frontend of website.
   *
   * @param array $params The widget params.
   *
   * @return array The widget params.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function dynamic_sidebar_params($params)
  {
    $id = $params[0]['id'];

    if (!isset(self::$widget_classes[$id]))
    {
      self::$widget_classes[$id] = 0;
    }
    self::$widget_classes[$id]++;

    $class = 'class="widget-' . self::$widget_classes[$id] . ' ';

    if (self::$widget_classes[$id] % 2 == 0)
    {
      $class .= 'widget-even ';
      $class .= 'widget-alt ';
    }
    else
    {
      $class .= 'widget-odd ';
    }

    $params[0]['before_widget'] = str_replace('class="', $class, $params[0]['before_widget']);

    return $params;
  }
  

  /**
   * get
   * A simple getter function.
   *
   * @param string $variable The variable name to get.
   *
   * @return mixed The requested value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get($variable)
  {
    return isset(self::$$variable) ? self::$$variable : false;
  }
}