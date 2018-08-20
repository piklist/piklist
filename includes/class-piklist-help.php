<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Help
 * Manages the admin help tabs.
 *
 * @package     Piklist
 * @subpackage  Help
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Help
{
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
    add_action('admin_head', array('piklist_help', 'register_help'));
  }

  /**
   * register_help
   * Register any help tabs available.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_help()
  {
    $data = array(
              'title' => 'Title'
              ,'capability' => 'Capability'
              ,'role' => 'Role'
              ,'page' => 'Page'
              ,'sidebar' => 'Sidebar'
              ,'post_type' => 'Post Type'
              ,'post_status' => 'Post Status'
              ,'template' => 'Template'
              ,'post_format' => 'Post Format'
              ,'setting' => 'Setting'
              ,'taxonomy' => 'Taxonomy'
              ,'id' => 'ID'
              ,'slug' => 'Slug'
            );

    piklist::process_parts('help', $data, array('piklist_help', 'register_help_callback'));
  }

  /**
   * register_help_callback
   * Handle and render a registered help tab.
   *
   * @param array $arguments The help tab configuration.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_help_callback($arguments)
  {
    extract($arguments);

    $screen = get_current_screen();
    $content = '';

    foreach ($render as $file)
    {
      $content .= piklist::render($file, array(
        'data' => $data
      ), true);
    }

    if ($data['sidebar'] == 'true')
    {
      $screen->set_help_sidebar($content);
    }
    else
    {
      $existing = $screen->get_help_tab($id);

      if ($existing)
      {
        if (empty($data['title']))
        {
          $data['title'] = $existing['title'];
        }

        switch ($data['extend_method'])
        {
          case 'before':
            $content = $content . $existing['content'];
          break;

          case 'after':
            $content = $existing['content'] . $content;
          break;

          case 'replace':
            $content = $content;
          break;
        }
      }

      $screen->add_help_tab(array(
        'id' => $id
        ,'title' => __($data['title'])
        ,'content' => $content
      ));
    }
  }
}
