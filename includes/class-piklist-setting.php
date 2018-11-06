<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!is_admin())
{
  return;
}

/**
 * Piklist_Setting
 * Controls settings and features. Uses the WordPress settings api.
 *
 * @package     Piklist
 * @subpackage  Setting
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Setting
{
  /**
   * @var array Registered settings.
   * @access private
   */
  private static $settings = array();

  /**
   * @var array Registered sections as meta boxes.
   * @access private
   */
  private static $meta_boxes = array();

  /**
   * @var mixed The current active section.
   * @access private
   */
  private static $active_section = null;

  /**
   * @var array Arguments for settings sections that have been registered.
   * @access private
   */
  private static $setting_section_callback_args = array();

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
    if (is_admin())
    {
      add_action('admin_init', array('piklist_setting', 'register_settings'));
      add_action('current_screen', array('piklist_setting', 'add_meta_boxes'));
      add_action('admin_enqueue_scripts', array('piklist_setting', 'admin_enqueue_scripts'));
      add_action('piklist_parts_processed-settings', array('piklist_setting', 'parts_processed'));

      add_filter('piklist_admin_pages', array('piklist_setting', 'admin_pages'));
      add_filter('piklist_part_add-workflows', array('piklist_setting', 'part_add'), 10, 2);
      add_filter('piklist_part_process-settings', array('piklist_setting', 'part_process'), 10, 2);
    }
  }

  /**
   * admin_pages
   * Create default settings pages for Piklist.
   *
   * @param array $pages Registered admin pages.
   *
   * @return array Registered admin pages.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function admin_pages($pages)
  {
    $pages[] = array(
      'page_title' => __('About', 'piklist')
      ,'menu_title' => __('Piklist', 'piklist')
      ,'capability' => defined('PIKLIST_SETTINGS_CAP') ? PIKLIST_SETTINGS_CAP : 'manage_options'
      ,'menu_slug' => 'piklist'
      ,'single_line' => false
      ,'menu_icon' => piklist::$add_ons['piklist']['url'] . '/parts/img/piklist-menu-icon.svg'
      ,'page_icon' => piklist::$add_ons['piklist']['url'] . '/parts/img/piklist-page-icon-32.png'
    );

    $pages[] = array(
      'page_title' => __('Piklist Settings', 'piklist')
      ,'menu_title' => __('Settings', 'piklist')
      ,'capability' => defined('PIKLIST_SETTINGS_CAP') ? PIKLIST_SETTINGS_CAP : 'manage_options'
      ,'sub_menu' => 'piklist'
      ,'menu_slug' => 'piklist-core-settings'
      ,'setting' => 'piklist_core'
      ,'menu_icon' => piklist::$add_ons['piklist']['url'] . '/parts/img/piklist-menu-icon.svg'
      ,'page_icon' => piklist::$add_ons['piklist']['url'] . '/parts/img/piklist-page-icon-32.png'
      ,'single_line' => true
    );

    $pages[] = array(
      'page_title' => __('Piklist Add-ons', 'piklist')
      ,'menu_title' => __('Add-ons', 'piklist')
      ,'capability' => defined('PIKLIST_SETTINGS_CAP') ? PIKLIST_SETTINGS_CAP : 'manage_options'
      ,'sub_menu' => 'piklist'
      ,'menu_slug' => 'piklist-core-addons'
      ,'setting' => 'piklist_core_addons'
      ,'menu_icon' => piklist::$add_ons['piklist']['url'] . '/parts/img/piklist-menu-icon.svg'
      ,'page_icon' => piklist::$add_ons['piklist']['url'] . '/parts/img/piklist-page-icon-32.png'
      ,'single_line' => true
    );

    return $pages;
  }

  /**
   * get
   * Standard get method
   *
   * @param string $variable The variable being requested.
   *
   * @return mixed The value requested
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get($variable)
  {
    return isset(self::$$variable) ? self::$$variable : false;
  }

  /**
   * register_settings
   * Register any settings sections available. Uses the WordPress settings api.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_settings()
  {
    global $current_screen;

    $data = array(
              'title' => 'Title'
              ,'setting' => 'Setting'
              ,'tab' => 'Tab'
              ,'order' => 'Order'
            );

    piklist::process_parts('settings', $data, array('piklist_setting', 'register_settings_callback'));
  }

  /**
   * register_setting
   * Register a settings field to a settings page and section.
   *
   * @param array $field The field object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_setting($field)
  {
    $field = piklist_form::setup_field($field);

    add_settings_field(
      isset($field['field']) ? $field['field'] : piklist::unique_id()
      ,isset($field['label']) ? piklist_form::field_label($field) : null
      ,array('piklist_setting', 'render_setting')
      ,self::$active_section['data']['setting']
      ,self::$active_section['id']
      ,array(
        'field' => $field
        ,'section' => self::$active_section
      )
    );
  }

  /**
   * register_settings_callback
   * Process successfully registered settings parts.
   *
   * @param array $arguments The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_settings_callback($arguments)
  {
    extract($arguments);

    if (!isset(self::$settings[$data['setting']]))
    {
      self::$settings[$data['setting']] = array();
    }

    array_push(self::$settings[$data['setting']], $arguments);
  }

  /**
   * register_settings_section_callback
   * Register settings sections.
   *
   * @param array $arguments The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_settings_section_callback($arguments)
  {
    extract($arguments);

    $section = self::$setting_section_callback_args[$id];

    self::$active_section = $section;

    $options = get_option($section['data']['setting']);

    do_action('piklist_pre_render_setting_section', $section, $options);

    foreach ($section['render'] as $render)
    {
      piklist::render($render, array(
        'data' => $section['data']
      ));
    }

    do_action('piklist_post_render_setting_section', $section, $options);

    self::$active_section = null;
  }

  /**
   * do_settings_sections
   * Render settings sections.
   *
   * @param string $page The page to get settings sections for.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function do_settings_sections($page)
  {
    global $wp_settings_sections, $wp_settings_fields;

    if (!isset($wp_settings_sections[$page]))
    {
      return;
    }

    foreach ((array) $wp_settings_sections[$page] as $section)
    {
      if ($section['callback'])
      {
        call_user_func($section['callback'], $section);
      }
    }
  }

  /**
   * add_meta_box_callback
   * Register successfully processed settings sections as meta boxes.
   *
   * @param $setting
   * @param $arguments
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function add_meta_box_callback($setting, $arguments)
  {
    echo '<table class="form-table">';

    $arguments['args']['meta_box'] = true;

    self::register_settings_section_callback($arguments['args']);

    do_settings_fields($setting, $arguments['args']['id']);

    echo '</table>';
  }

  /**
   * pre_update_option
   * Handle saving the setting.
   *
   * @param array $new The new setting object.
   * @param array $old The old setting object.
   *
   * @return public
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function pre_update_option($new, $old = false)
  {
    $check = piklist_validate::check($new);

    if (true === $check['valid'] && $check['type'] == 'POST')
    {
      $fields_data = $check['fields_data'];

      $setting = $_REQUEST['option_page'];

      $_old = $old;

      foreach ($fields_data[$setting] as &$data)
      {
        if (empty($data['display']))
        {
          $field = $data['field'];

          // Replace with sanitized data
          if (isset($new[$field]))
          {
            $new[$field] = $data['request_value'];
          }

          if (!isset($new[$field]) && isset($_old[$field]))
          {
            unset($_old[$field]);
          }

          if (isset($new[$field]) && is_array($new[$field]) && $data['multiple'] && is_array($data['choices']) && count($data['choices']) == 1)
          {
            $new[$field] = current($new[$field]);
          }

          // Save Uploads
          if (isset($data['field']) && !$data['display'] && array_key_exists($setting, $_FILES) && array_key_exists($data['field'], $_FILES[$setting]['name']))
          {
            $paths = piklist::array_paths($_FILES[$setting]['name'][$data['field']]);

            if (!empty($paths))
            {
              if (strstr($paths[0], ':'))
              {
                foreach ($paths as $path)
                {
                  $files_path = explode(':', $path);

                  unset($files_path[count($files_path) - 1]);

                  $files_path = array_merge(array(
                            $setting
                            ,'name'
                          ), explode(':', $data['field'] . ':' . implode(':', $files_path)));

                  $field_name = explode(':', $path);
                  $field_name = $field_name[1];

                  $options = $data['options'];
                  foreach ($data['fields'] as $_field)
                  {
                    if ($_field['field'] == $field_name)
                    {
                      $options = $_field['options'];

                      break;
                    }
                  }

                  $storage = array();
                  $storage_type = isset($options['save']) && $options['save'] == 'url';

                  $upload = piklist_form::save_upload($files_path, $storage, $storage_type);

                  if ($upload)
                  {
                    piklist::array_path_set($new[$data['field']], explode(':', $path), current($upload));
                  }
                }
              }
              else
              {
                $path = array_merge(array(
                          $setting
                          ,'name'
                        ), array($data['field']));

                $storage = is_array($data['request_value']) ? array_filter($data['request_value']) : $data['request_value'];
                $storage_type = isset($data['options']['save']) && $data['options']['save'] == 'url';

                $upload = piklist_form::save_upload($path, $storage, $storage_type);

                if ($upload)
                {
                  $new[$data['field']] = $upload;
                }
              }
            }
          }
        }
      }

      unset($data);

      $settings = wp_parse_args($new, $_old);

      /**
       * piklist_pre_update_option
       * Filter settings before they update.
       *
       * @param array $settings All settings fields that are getting saved.
       * @param  $setting The setting.
       * @param  array $new The new data in the form (what is currently being saved)
       * @param  array $old The old data in the form (what is currently in the database)
       *
       * @since 1.0
       */
      $settings = apply_filters('piklist_pre_update_option', $settings, $setting, $new, $old);

      /**
       * piklist_pre_update_option_$setting
       * Filter a particular setting before it's update.
       *
       * @param  $setting The setting to filter.
       * @param array $settings All settings fields that are getting saved.
       * @param  array $new The new data in the form (what is currently being saved)
       * @param  array $old The old data in the form (what is currently in the database)
       *
       * @since 1.0
       */
      $settings = apply_filters('piklist_pre_update_option_' . $setting, $settings, $new, $old);
    }
    else
    {
      $settings = $old;
    }

    return $settings;
  }

  /**
   * render_setting
   * Render a setting.
   *
   * @param array $setting The setting object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function render_setting($setting)
  {
    $field = wp_parse_args(array(
        'scope' => $setting['section']['data']['setting']
        ,'prefix' => false
        ,'disable_label' => true
        ,'position' => false
        ,'value' => piklist_form::get_field_value($setting['section']['data']['setting'], $setting['field'], 'option')
      )
      ,$setting['field']);

    if ($field['type'] == 'group' && !$field['field'])
    {
      foreach ($field['fields'] as &$column)
      {
        if (!isset($column['value']))
        {
          $column['value'] = null;
        }

        $column['value'] = piklist_form::get_field_value($setting['section']['data']['setting'], $column, 'option');
      }
      unset($column);
    }

    piklist_form::render_field($field);
  }

  /**
   * part_add
   * Generate workflow from legacy tabs.
   *
   * @param array $parts A collection of parts.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function part_add($parts)
  {
    $page = isset($_REQUEST['page']) ? esc_attr($_REQUEST['page']) : false;

    if ($page)
    {
      $workflow = array();
      $default_tab = array();
      $process_parts = piklist::get_processed_parts('settings');

      if ($process_parts)
      {
        foreach ($process_parts['parts'] as $part)
        {
          if ($part['data']['setting'] == $page && is_array($part['data']['flow']) && in_array($part['data']['setting'], $part['data']['flow']))
          {
            if ($part['data']['tab'])
            {
              $tab = current($part['data']['tab']);
              $part['data']['flow'] = $part['data']['setting'];

              /*
               * Backwards compatible for "tab_order" parameter
               * Replaced with "order" parameter
               */
              if (isset($workflow[$tab]))
              {
                if (!empty($part['data']['tab_order']))
                {
                  $workflow[$tab]['data']['order'] = $part['data']['tab_order'];
                }
              }
              else
              {
                $workflow[$tab] = array(
                  'folder' => 'workflows'
                  ,'render' => array()
                  ,'add_on' => $part['add_on']
                  ,'prefix' => $part['prefix']
                  ,'path' => null
                  ,'part' => null
                  ,'data' => array(
                    'flow' => array($part['data']['setting'])
                    ,'page' => array($part['data']['setting'])
                    ,'order' => $part['data']['order']
                    ,'title' => ucwords($tab)
                    ,'position' => 'title'
                    ,'tab' => null
                    ,'post_type' => null
                    ,'header' => false
                    ,'disable' => false
                    ,'redirect' => false
                    ,'default' => false
                  )
                );
              }
            }
            elseif (empty($default_tab))
            {
              $admin_pages = piklist_admin::get('admin_pages');

              foreach ($admin_pages as $admin_page)
              {
                if ($_REQUEST['page'] == $admin_page['menu_slug'])
                {
                  break;
                }
              }

              $default_tab = array(
                'id' => $part['id']
                ,'folder' => 'workflows'
                ,'render' => array()
                ,'add_on' => $part['add_on']
                ,'prefix' => $part['prefix']
                ,'path' => null
                ,'part' => null
                ,'data' => array(
                  'flow' => array($part['data']['setting'])
                  ,'page' => array($part['data']['setting'])
                  ,'order' => $part['data']['order']
                  ,'title' => __(isset($admin_page['default_tab']) && $admin_page['default_tab'] ? piklist::slug($admin_page['default_tab']) : 'General')
                  ,'position' => 'title'
                  ,'tab' => null
                  ,'post_type' => null
                  ,'header' => false
                  ,'disable' => false
                  ,'redirect' => false
                  ,'default' => false
                )
              );
            }
          }
        }

        if (!empty($workflow) && count($workflow) > 1)
        {
          if (!empty($default_tab))
          {
            array_push($workflow, $default_tab);
          }

          foreach ($workflow as $tab)
          {
            array_push($parts, $tab);
          }
        }
      }
    }

    return $parts;
  }

  /**
   * parts_processed
   * Process registered settings parts.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function parts_processed()
  {
    $submitdiv = false;

    foreach (self::$settings as $setting => $sections)
    {
      if ((isset($_REQUEST['page']) && $_REQUEST['page'] == $setting) || (isset($_REQUEST['option_page']) && $_REQUEST['option_page'] == $setting))
      {
        add_filter('pre_update_option_' . $setting, array('piklist_setting', 'pre_update_option'), 10, 2);
      }

      register_setting($setting, $setting);

      uasort($sections, array('piklist', 'sort_by_data_order'));

      $active = isset($_REQUEST['page']) && $setting == $_REQUEST['page'];

      foreach ($sections as $section)
      {
        self::$setting_section_callback_args[$section['id']] = $section;

        $textdomain = isset(piklist_add_on::$available_add_ons[$section['add_on']]['TextDomain']) ? piklist_add_on::$available_add_ons[$section['add_on']]['TextDomain'] : null;
        $title = !empty($section['data']['title']) ? $section['data']['title'] : (!empty($id) ? $id : __('Settings', 'piklist'));
        $title = !empty($textdomain) ? __($title, $textdomain) : $title;

        if ($active && piklist_admin::$admin_page_layout == 'meta-boxes')
        {
          if (!$submitdiv)
          {
            $admin_pages = piklist_admin::get('admin_pages');

            foreach ($admin_pages as $page)
            {
              if (isset($page['setting']) && $page['setting'] == $setting)
              {
                break;
              }
            }

            array_push(self::$meta_boxes, array(
              'submitdiv-' . $page['setting']
              ,__('Actions')
              ,array('piklist_admin', 'add_meta_box_submitdiv_callback')
              ,null
              ,'side'
              ,'high'
              ,$page
            ));

            $submitdiv = true;
          }

          $context = empty($section['data']['context']) ? 'normal' : $section['data']['context'];
          $priority = empty($section['data']['priority']) ? 'low' : $section['data']['priority'];

          array_push(self::$meta_boxes, array(
            $section['id']
            ,$title
            ,array('piklist_setting', 'add_meta_box_callback')
            ,null
            ,$context
            ,$priority
            ,array(
              'id' => $section['id']
              ,'title' => __($section['data']['title'])
            )
          ));
        }
        else
        {
          add_settings_section($section['id'], $title, array('piklist_setting', 'register_settings_section_callback'), $setting);
        }
      }
    }
  }

  /**
   * part_process
   * Convert old tabs to Workflow tabs.
   *
   * @param array $part The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function part_process($part)
  {
    $page = isset($_REQUEST['page']) ? esc_attr($_REQUEST['page']) : false;

    if ($page && empty($part['data']['flow']))
    {
      $part['data']['flow'] = array($part['data']['setting']);

      if (!$part['data']['tab'])
      {
        $admin_pages = piklist_admin::get('admin_pages');

        foreach ($admin_pages as $admin_page)
        {
          if ($_REQUEST['page'] == $admin_page['menu_slug'])
          {
            break;
          }
        }

        $part['data']['tab'] = array(isset($admin_page['default_tab']) ? piklist::slug($admin_page['default_tab']) : 'general');
      }
    }

    return $part;
  }

  /**
   * add_meta_boxes
   * Add the meta boxes that were registered from settings parts.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function add_meta_boxes()
  {
    global $current_screen;

    if (!empty(self::$meta_boxes))
    {
      foreach (self::$meta_boxes as $meta_box)
      {
        add_meta_box($meta_box[0], $meta_box[1], $meta_box[2], $current_screen, $meta_box[4], $meta_box[5], $meta_box[6]);
      }
    }
  }

  /**
   * admin_enqueue_scripts
   * Enqueues neccessary scripts.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function admin_enqueue_scripts()
  {
    if (!empty(self::$meta_boxes))
    {
      wp_enqueue_script('postbox');
    }
  }
}
