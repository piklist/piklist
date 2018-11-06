<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Pointer
 * Manages the admin pointers.
 *
 * @package     Piklist
 * @subpackage  Pointer
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Pointer
{
  /**
   * @var array Registered pointers.
   * @access private
   */
  private static $pointers = array();

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
      add_filter('piklist_assets_localize', array('piklist_pointer', 'assets_localize'));

      add_action('current_screen', array('piklist_pointer', 'register_pointer'));
      add_action('admin_footer', array('piklist_pointer', 'admin_footer'));
    }
  }

  /**
   * admin_footer
   * Enqueue neccessary scripts and styles.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function admin_footer()
  {
    if (!empty(self::$pointers))
    {
      wp_enqueue_script('wp-pointer');
      wp_enqueue_style('wp-pointer');
    }
  }

  /**
   * register_pointer
   * Register any admin pointers available.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_pointer()
  {
    $data = array(
              'title' => 'Title'
              ,'capability' => 'Capability'
              ,'role' => 'Role'
              ,'page' => 'Page'
              ,'anchor_id' => 'Anchor ID'
              ,'edge' => 'Edge'
              ,'align' => 'Align'
              ,'id' => 'ID'
              ,'slug' => 'Slug'
            );

    piklist::process_parts('pointers', $data, array('piklist_pointer', 'register_pointer_callback'));
  }

  /**
   * register_pointer_callback
   * Handle and render a registered admin pointer.
   *
   * @param array $arguments The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_pointer_callback($arguments)
  {
    extract($arguments);
    
    $content =  '<h3 id="' . $id . '">' . $data['title'] . '</h3>';
    $dismissed = get_user_meta(get_current_user_id(), 'dismissed_wp_pointers', true);
    $dismissed = explode(',', $dismissed);
    
    if (!empty($dismissed[0]) && in_array($id, $dismissed))
    {
      return false;
    }
    
    foreach ($render as $file)
    {
      $content .= piklist::render($file, array(
        'data' => $data
      ), true);
    }

    array_push(self::$pointers, array_merge($arguments, array(
      'content' => $content
    )));
  }
  
  /**
   * assets_localize
   * Add data to the local piklist variable.
   *
   * @param array $localize The data being passed to the piklist javascript object.
   *
   * @return array Current data.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function assets_localize($localize)
  {
    $localize['pointers'] = array();
    
    foreach (self::$pointers as $pointer)
    {
      array_push($localize['pointers'], array(
        'target' => $pointer['data']['anchor_id']
        ,'options' => array(
          'content' => $pointer['content']
          ,'position' => array(
            'edge' => $pointer['data']['edge']
            ,'align' => $pointer['data']['align']
          )
        )
        ,'pointer_id' => $pointer['id'] 
      ));
    }

    return $localize;
  }
}