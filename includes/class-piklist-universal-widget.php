<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Universal_Widget
 * Controls modifications and features for Universal Widgets.
 *
 * @package     Piklist
 * @subpackage  Universal Widget
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Universal_Widget extends WP_Widget
{
  /**
   * @var array Registered widgets.
   * @access private
   */
  public $widgets = array();

  /**
   * @var array The current instance.
   * @access private
   */
  public $instance = array();

  /**
   * @var string The core name of the widget.
   * @access private
   */
  public $widget_core_name = 'piklist_universal_widget';

  /**
   * @var string The widget name.
   * @access private
   */
  public $widget_name = '';

  /**
   * __construct
   * Create a new universal widget.
   *
   * @param string $name The name.
   * @param string $title The title.
   * @param string $description The description.
   * @param array $path The path.
   * @param array $control_options The control options.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public function __construct($name, $title = '', $description = '', $customize_selective_refresh = false, $widgets = array(), $control_options = array())
  {
    global $pagenow;

    $this->widget_name = $name;
    $this->widgets = $widgets;

    if ($pagenow == 'customize.php')
    {
      $control_options['width'] = 300;
      $control_options['height'] = 200;
    }
    elseif (count($widgets) == 1)
    {
      $widget = current($widgets);
      
      $control_options['width'] = $widget['data']['width'] ? $widget['data']['width'] : null;
      $control_options['height'] = $widget['data']['height'] ? $widget['data']['height'] : null;
    }

    $class = parent::__construct(
      piklist::dashes($this->widget_name)
      ,__($title)
      ,array(
        'classname' => piklist::dashes($this->widget_core_name)
        ,'description' => __($description)
        ,'customize_selective_refresh' => $customize_selective_refresh
      )
      ,$control_options
    );
    
    if (is_active_widget(false, false, $this->id_base ) || is_customize_preview()) 
    {
      do_action('piklist_widget_active', $this->widget_name, $widgets);
    }
     
    add_filter('piklist_part_id-widgets', array(&$this, 'part_id'), 10, 4);

    add_action('wp_ajax_' . $name, array(&$this, 'ajax'));
  }

  /**
   * setup
   * Setup the current widget.
   *
   * @param string $widget The widget name.
   *
   * @access public
   * @since 1.0
   */
  public function setup($widget)
  {
    piklist_widget::$current_widget = $widget;
  }

  /**
   * form
   * Render the widget select form.
   *
   * @param array $instance The widget instance.
   *
   * @return array The widget instance.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public function form($instance)
  {
    $this->setup($this->widget_name);

    $this->instance = $instance;

    $forms = piklist_widget::get('widget_forms');

    if (count($this->widgets) <= 1)
    {
      $widget = current($this->widgets);
      
      if (isset($forms[$widget['id']]))
      {
        do_action('piklist_notices');
        
        foreach ($forms[$widget['id']]['render'] as $render)
        {
          piklist::render($render, array(
            'instance' => $this->instance
          ));
        }
        
        piklist_form::save_fields();   
      }
    }
    else
    {
      piklist::render('shared/widget-select', array(
        'instance' => $instance
        ,'widgets' => $this->widgets
        ,'forms' => $forms
        ,'name' => $this->widget_core_name
        ,'widget_name' => $this->widget_name
        ,'class_name' => piklist::dashes($this->widget_core_name)
        ,'widget' => isset($this->instance['widget']) ? maybe_unserialize($this->instance['widget']) : null
      ));
    }

    return $instance;
  }

  /**
   * ajax
   * Render the widget form.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public function ajax()
  {
    global $wp_widget_factory;

    $widget = isset($_REQUEST['widget']) ? esc_attr($_REQUEST['widget']) : null;

    if ($widget && current_user_can('edit_theme_options'))
    {
      $this->setup($this->widget_name);

      if (isset($_REQUEST['number']))
      {
        $instances = get_option('widget_' . piklist::dashes($this->widget_name));

        piklist_widget::widget()->_set($_REQUEST['number']);

        if (isset($instances[$_REQUEST['number']]))
        {
          piklist_widget::widget()->instance = $instances[$_REQUEST['number']];
        }
      }

      if (isset($this->widgets[$widget]))
      {
        $response = array(
                      'widget' => $this->widgets[$widget]
                    );
                  
        $forms = piklist_widget::get('widget_forms');
        
        if (isset($forms[$widget]))
        {
          ob_start();

          do_action('piklist_notices');
        
          foreach ($forms[$widget]['render'] as $render)
          {
            piklist::render($render);
          }

          piklist_form::save_fields();

          $response['form'] = ob_get_contents();

          ob_end_clean();
        }

        wp_send_json($response);
      }
    }

    wp_send_json_error();
  }

  /**
   * update
   * Save the new widget data.
   *
   * @param array $new_instance The new widget instance.
   * @param array $old_instance The old widget instance.
   *
   * @return array The updated widget instance.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public function update($new_instance, $old_instance)
  {
    if (!isset($_REQUEST['id_base']))
    {
      return $old_instance;
    }

    $widget_index = !empty($_REQUEST['multi_number']) ? $_REQUEST['multi_number'] : $_REQUEST['widget_number'];
    $request_data = $_REQUEST['widget-' . $_REQUEST['id_base']][$widget_index];

    $this->setup(piklist::slug($_REQUEST['id_base']));

    $check = piklist_validate::check($request_data);

    if (false !== $check['valid'] && $check['type'] == 'POST')
    {
      $instance = array();

      $fields_data = array_shift($check['fields_data']);

      foreach ($new_instance as $field => $value)
      {
        if (isset($fields_data[$field]))
        {
          $value = $fields_data[$field]['request_value'];
        }

        if (!empty($value))
        {
          $instance[$field] = is_array($value) ? maybe_serialize($value) : stripslashes($value);
        }
      }

      return $instance;
    }
    elseif (count($old_instance) <= 1)
    {
      return array(
        'widget' => $new_instance['widget']
      );
    }

    $old_instance['widget'] = $new_instance['widget'];

    return $old_instance;
  }

  /**
   * widget
   * Render the front end view of the widget.
   *
   * @param array $arguments The part object.
   * @param array $instance The widget instance.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public function widget($arguments, $instance)
  {
    extract($arguments);

    if (isset($instance['widget']))
    {
      $widget = $instance['widget'];

      unset($instance['widget']);
    }
    
    if (!empty($instance))
    {
      foreach ($instance as $field => &$value)
      {
        $value = maybe_unserialize($value);
      }
    }
    
    $this->setup($this->widget_name);

    if (!isset($widget))
    {
      $widget = current(array_keys($this->widgets));
    }

    $this->widgets[$widget]['instance'] = $instance;

    do_action('piklist_pre_render_widget', $this->widgets[$widget]);

    foreach ($this->widgets[$widget]['render'] as $render)
    {
      if (!strstr(strtolower($render), '-form.php'))
      {
         piklist::render($render, array(
          'instance' => $instance
          ,'settings' => $instance
          ,'before_widget' => str_replace('class="', 'class="' . piklist::dashes($this->widgets[$widget]['id']) . ' ' . $this->widgets[$widget]['data']['class'] . ' ', $before_widget)
          ,'after_widget' => $after_widget
          ,'before_title' => $before_title
          ,'after_title' => $after_title
          ,'data' => $this->widgets[$widget]['data']
        ));
      }
    }

    do_action('piklist_post_render_widget', $this->widgets[$widget]);
  }

  /**
   * part_id
   * Specify the part id.
   *
   * @param string $part_id The current id for the part.
   * @param string $add_on The add-on for the part.
   * @param string $part The current id for the part
   * @param array $part_data The part object.
   *
   * @return string The update part id.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public function part_id($part_id, $add_on, $part, $part_data)
  {
    return piklist::slug($add_on . ' ' . str_replace('-form.php', '.php', strtolower($part)));
  }
}