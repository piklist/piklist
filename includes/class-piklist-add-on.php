<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Add_On
 * Loads and manages any Piklist plugins or add-ons included within Piklist plugin.
 *
 * @package     Piklist
 * @subpackage  Add ons
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Add_On
{
  /**
   * @var string Available piklist add-ons and their locations.
   * @access public
   */
  public static $available_add_ons = array();

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
    add_action('init', array('piklist_add_on', 'include_add_ons'), 0);
  }

  /**
   * include_add_ons
   * Inlcude add-ons registered with piklist.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function include_add_ons()
  {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';

    $site_wide_plugins = get_site_option('active_sitewide_plugins');

    $plugins = get_option('active_plugins');
    $plugins = $plugins ? $plugins : array();

    if (!empty($site_wide_plugins))
    {
      $plugins = array_merge($plugins, array_keys($site_wide_plugins));
    }

    foreach ($plugins as $plugin)
    {
      $path = WP_PLUGIN_DIR . '/' . $plugin;

      if (file_exists($path))
      {
        $data = piklist::get_file_data($path, array(
                   'name' => 'Plugin Name'
                  ,'type' => 'Plugin Type'
                  ,'version' => 'Version'
                ));

        if ($data['type'] && strtolower($data['type']) == 'piklist')
        {
          piklist::add_plugin(basename(dirname($plugin)), dirname($path));

          add_action('load-plugins.php', array('piklist_admin', 'deactivation_link'));

          piklist_admin::$piklist_dependent['plugins'][] = $data;

          if ($data['version'])
          {
            $file = $plugin;
            $version = $data['version'];

            piklist_admin::check_update($file, $version);
          }
        }
      }
    }

    $addon_paths = piklist::paths();
    $paths = array();
    foreach ($addon_paths as $from => $path)
    {
      if ($from != 'theme')
      {
        array_push($paths, $path  . '/add-ons');
        if ($from != 'piklist')
        {
          array_push($paths, $path);
        }
      }
    }

    foreach ($paths as $path)
    {
      if (is_dir($path))
      {
        if (strstr($path, 'add-ons'))
        {
          $add_ons = piklist::get_directory_list($path);
          foreach ($add_ons as $add_on)
          {
            $file = file_exists($path . '/' . $add_on . '/' . $add_on . '.php') ? $path . '/' . $add_on . '/' . $add_on . '.php' : $path . '/' . $add_on . '/plugin.php';
            self::register_add_on($add_on, $file, $path);
          }
        }
        else
        {
          $add_on = basename($path);
          $file = file_exists($path . '/' . $add_on . '.php') ? $path . '/' . $add_on . '.php' : $path . '/plugin.php';
          self::register_add_on($add_on, $file, $path, true);
        }
      }
    }

    do_action('piklist_activate_add_on');
  }

  /**
   * register_add_on
   * Activate any add-ons that are activated.
   *
   * @param string $add_on Add-on slug.
   * @param string $file File name of the add-on plugin file.
   * @param string $path Path to the filename.
   * @param bool $plugin Whether or not its a plugin or included as an add-on in a plugin.
   *
   * @access private
   * @static
   * @since 1.0
   */
  private static function register_add_on($add_on, $file, $path, $plugin = false)
  {
    if (file_exists($file))
    {      
      $data = piklist::get_file_data($file, array(
                'name' => 'Plugin Name'
                ,'plugin_uri' => 'Plugin URI'
                ,'version' => 'Version'
                ,'description' => 'Description'
                ,'author' => 'Author'
                ,'author_uri' => 'Author URI'
                ,'text_domain' => 'Text Domain'
                ,'domain_path' => 'Domain Path'
              ));
              
      $data['plugin'] = $plugin;
      $data['add_on'] = $add_on;
      
      self::$available_add_ons[$add_on] = $data;

      if (self::is_active($add_on))
      {
        include_once $file;

        $class_name = str_replace(piklist::$prefix, 'piklist_', piklist::slug($add_on));

        if (class_exists($class_name) && method_exists($class_name, '_construct') && !is_subclass_of($class_name, 'WP_Widget'))
        {
          call_user_func(array($class_name, '_construct'));
        }

        piklist::$paths[$add_on] = $path . (!$plugin ? '/' . $add_on : '');
        piklist::$add_ons[$add_on]['path'] = $path . (!$plugin ? '/' . $add_on : '');

        $path = str_replace(chr(92), '/', str_replace('/add-ons', '', $path));

        piklist::$urls[$add_on] = plugins_url() . substr($path, strrpos($path, '/')) . '/add-ons/' . $add_on;
        piklist::$add_ons[$add_on]['url'] = plugins_url() . substr($path, strrpos($path, '/')) . '/add-ons/' . $add_on;
      }
    }
  }

  /**
   * is_active
   * Check whether an add-on is active.
   *
   * @param string $add_on Add-on slug.
   *
   * @return bool Whether or not the add-on is active.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function is_active($add_on = '')
  {
    $add_ons = get_option('piklist_core_addons');

    if (isset($add_ons['add-ons']))
    {
      $add_ons = is_array($add_ons['add-ons']) ? $add_ons['add-ons'] : array($add_ons['add-ons']);

      return !empty($add_ons) && in_array($add_on, $add_ons) && isset(self::$available_add_ons[$add_on]);
    }

    return false;
  }
  
  /**
   * current
   * Get the current add-on
   *
   * @return string The slug of the current add-on.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function current()
  {
    $backtrace = debug_backtrace();
    foreach ($backtrace as $trace)
    {
      if (!isset($trace['file'])) 
      {
          continue;
      }
      
      $file = $trace['file'];
      $parts = DIRECTORY_SEPARATOR . "parts" . DIRECTORY_SEPARATOR;
      
      if (strstr($file, $parts))
      {
        $add_on = substr($file, 0, strpos($file, $parts));
        $add_on = substr($add_on, strrpos($add_on, DIRECTORY_SEPARATOR) + 1);

        if (isset(piklist::$add_ons[$add_on]))
        {
          return $add_on;
        }
      }
    }

    return false;
  }
}