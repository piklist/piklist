<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Notice
 * Manages notices.
 *
 * @package     Piklist
 * @subpackage  Notice
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Notice
{
  /**
   * @var array Registered notices.
   * @access private
   */
  private static $notices = array();
  
  /**
   * @var string The meta key for dismissed notices.
   * @access private
   */
  private static $notice_meta_key = '_dismissed_piklist_notices';

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
      add_action('current_screen', array('piklist_notice', 'register_notice'));
      add_action('admin_notices', array('piklist_notice', 'notice'));
      add_action('wp_ajax_piklist_notice', array('piklist_notice', 'ajax'));
    }
  }
   
  /**
   * register_notice
   * Register any notices available.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_notice()
  {
    $data = array(
              'notice_type' => 'Notice Type' // error, updated, update-nag
              ,'notice_id' => 'Notice ID'
              ,'capability' => 'Capability'
              ,'role' => 'Role'
              ,'page' => 'Page'
              ,'dismiss' => 'Dismiss'
              ,'id' => 'ID'
              ,'slug' => 'Slug'
            );

    piklist::process_parts('notices', $data, array('piklist_notice', 'register_notice_callback'));
  }

  /**
   * register_notice_callback
   * Handle and render a registered admin notice.
   *
   * @param array $arguments The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_notice_callback($arguments)
  {
    extract($arguments);
    
    $content = '';
    $dismissed = get_user_meta(get_current_user_id(), self::$notice_meta_key, true);
    
    if (is_array($dismissed) && in_array($id, $dismissed))
    {
      return false;
    }
    
    foreach ($render as $file)
    {
      $content .= piklist::render($file, array(
        'data' => $data
      ), true);
    }

    array_push(self::$notices, array_merge($arguments, array(
      'content' => $content
    )));
  }
  
  /**
   * notice
   * Render the admin notices.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function notice()
  {
    foreach (self::$notices as $notices => $notice)
    {
      piklist::render('shared/notice', array(
        'type' => $notice['data']['notice_type']
        ,'content' => $notice['content']
        ,'id' => $notice['id']
        ,'notice_type' => $notice['data']['notice_type']
        ,'dismiss' => $notice['data']['dismiss']
      ));
    }
  }

  /**
   * ajax
   * Updates the user meta field 'piklist_notice_dismissed' with the notice_id
   * Only triggered if user dismisses notice.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function ajax()
  {
    if (isset($_POST['id']))
    {
      $user_id = get_current_user_id();

      $dismiss = esc_attr($_REQUEST['id']);
      
      $dismissed = get_user_meta($user_id, self::$notice_meta_key, true);
      $dismissed = !$dismissed ? array() : $dismissed;
      
      if (!in_array($dismiss, $dismissed))
      {
        array_push($dismissed, $dismiss);

        update_user_meta($user_id, self::$notice_meta_key, $dismissed);
      }
    }

    // wp_die();
  }
}