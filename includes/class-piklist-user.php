<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_User
 * Controls user modifications and features.
 *
 * @package     Piklist
 * @subpackage  User
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_User
{
  /**
   * @var array Registered meta boxes.
   * @access private
   */
  private static $meta_boxes = array();
    
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
    add_action('init', array('piklist_user', 'init'));
    add_action('show_user_profile', array('piklist_user', 'meta_box'));
    add_action('edit_user_profile', array('piklist_user', 'meta_box'));
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
    self::register_meta_boxes();

    $use_multiple_user_roles = piklist::get_settings('piklist_core', 'multiple_user_roles');

    if ($use_multiple_user_roles && (!is_multisite() || (isset($pagenow) && $pagenow == 'user-edit.php' && is_multisite())))
    {
      add_action('profile_update', array('piklist_user', 'multiple_roles'));
      add_action('user_register', array('piklist_user', 'multiple_roles'), 9);
      add_action('admin_footer', array('piklist_user', 'multiple_roles_field'));

      add_filter('additional_capabilities_display', array('piklist_user', 'additional_capabilities_display'));
    }
  }

  /**
   * register_meta_boxes
   * Register user meta sections.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_meta_boxes()
  {
    $data = array(
              'title' => 'Title'
              ,'description' => 'Description'
              ,'capability' => 'Capability'
              ,'order' => 'Order'
              ,'role' => 'Role'
              ,'new' => 'New'
              ,'id' => 'ID'
              ,'slug' => 'Slug'
            );

    piklist::process_parts('users', $data, array('piklist_user', 'register_meta_boxes_callback'));
  }

  /**
   * register_meta_boxes_callback
   * Handle the registration of a user meta section.
   *
   * @param array $arguments The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_meta_boxes_callback($arguments)
  {
    global $pagenow;
    
    extract($arguments);

    if (!$data['new'] || ($data['new'] && (isset($pagenow) && $pagenow != 'user-new.php')))
    {
      foreach (self::$meta_boxes as $key => $meta_box)
      {
        if ($id == $meta_box['id'])
        {
          unset(self::$meta_boxes[$key]);
        }
      }
      
      if (isset($order))
      {
        self::$meta_boxes[$order] = $arguments;
      }
      else
      {
        array_push(self::$meta_boxes, $arguments);
      }
    }  
  }

  /**
   * meta_box
   * Render the meta box.
   *
   * @param int $user_id The user id.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function meta_box($user_id)
  {
    if (!empty(self::$meta_boxes))
    {
      $user = get_userdata($user_id);

      uasort(self::$meta_boxes, array('piklist', 'sort_by_data_order'));

      foreach (self::$meta_boxes as $meta_box)
      {
        piklist::render('shared/meta-box-start', array(
          'meta_box' => $meta_box
          ,'wrapper' => 'user_meta'
        ), false);

        do_action('piklist_pre_render_user_meta_box', $user, $meta_box);
  
        foreach ($meta_box['render'] as $render)
        {
          piklist::render($render, array(
            'user_id' => $user_id
            ,'data' => $meta_box['data']
          ), false);
        }

        do_action('piklist_post_render_user_meta_box', $user, $meta_box);
        
        piklist::render('shared/meta-box-end', array(
          'meta_box' => $meta_box
          ,'wrapper' => 'user_meta'
        ), false);
      }
    }
  }
  
  /**
   * additional_capabilities_display
   * Remove the "additional capabilites" section on a users profile page.
   *
   * @param int $user_id The user id.
   *
   * @return false
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function additional_capabilities_display($user_id)
  {
    return false;
  }
  
  /**
   * multiple_roles
   * Allow saving of multiple user roles.
   * Keeps a log of when roles were updated.
   * 
   * @param int $user_id The user id.
   * @param mixed $roles Collection of roles.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function multiple_roles($user_id, $roles = false)
  {
    global $wpdb, $wp_roles, $current_user, $pagenow;
    
    $roles = $roles ? $roles : (isset($_POST['roles']) ? $_POST['roles'] : false);
    
    if ($roles && current_user_can('edit_user', $current_user->ID))
    {      
      $editable_roles = get_editable_roles();
      $user = new WP_User($user_id);
      $user_roles = array_intersect(array_values($user->roles), array_keys($editable_roles));
      
      $_user_role_log = get_user_meta($user_id, $wpdb->prefix . 'capabilities_log', true);
      $user_role_log = $_user_role_log ? $_user_role_log : array();
      
      $roles = is_array($roles) ? $roles : array($roles);
      foreach ($roles as $role)
      {
        if (!in_array($role, $user_roles) && $wp_roles->is_role($role))
        {
          $user->add_role($role);
          
          array_push($user_role_log, array(
            'action' => 'add'
            ,'role' => $role
            ,'timestamp' => time()
          ));
        }
      }
      
      foreach ($user_roles as $role)
      {
        if (!in_array($role, $roles) && $wp_roles->is_role($role))
        {
          $user->remove_role($role);
          
          array_push($user_role_log, array(
            'action' => 'remove'
            ,'role' => $role
            ,'timestamp' => time()
          ));
        }
      }
      
      update_user_meta($user_id, $wpdb->prefix . 'capabilities_log', $user_role_log);
    }
  }
  
  /**
   * multiple_roles_field
   * Render a checkbox field on the user screen to allow for selecting multiple user roles.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function multiple_roles_field()
  {
    global $pagenow, $user_id;
    
    if (in_array($pagenow, array('user-edit.php', 'user-new.php')))
    {
      $editable_roles = get_editable_roles();
      
      if ($user_id)
      {
        $user = get_user_to_edit($user_id);
        $user_roles = array_intersect(array_values($user->roles), array_keys($editable_roles));
      }
      else
      {
        $user_roles = null;
      }

      $roles = array();
      foreach ($editable_roles as $role => $details) 
      {
        $roles[$role] = translate_user_role($details['name']); 
      }
    
      piklist::render('shared/field-user-role', array(
        'user_roles' => $user_roles
        ,'roles' => $roles
      ), false);
    }
  }

  /**
   * available_capabilities
   * Returns an array of all available capabilites.
   *
   * @return array Collection of capabilities.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function available_capabilities()
  {
    global $wp_roles;

    $roles = array();
    $capabilities = array();

    $roles = $wp_roles->role_objects;

    $caps = piklist($roles, array('capabilities'));

    foreach ($caps as $section => $items)
    {
      foreach ($items as $key => $value)
      {
        $capabilities[$key] = ucwords(str_replace('_', ' ', $key));
      }
    }

    $capabilities = array_unique($capabilities);
    natcasesort($capabilities);

    return $capabilities;
  }

  /**
   * available_roles
   * Returns an array of all available role names.
   *
   * @return array Collection of roles.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function available_roles()
  {
    global $wp_roles;
    
    $roles = $wp_roles->get_names();

    return $roles;
  }

  /**
   * current_user_role
   * Checks if the current user's role in an array of $roles.
   *
   * @return array Collection of roles.
   *
   * @return bool Whether the role was found.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function current_user_role($roles)
  {
    $current_user = wp_get_current_user();

    $roles = is_array($roles) ? $roles : explode(',', $roles);

    $roles_array = array_filter(array_map('trim', $roles));
    $roles_array = array_filter(array_map('strtolower', $roles_array));

    foreach ($current_user->roles as $user_role)
    {
      if (in_array(strtolower($user_role), $roles_array))
      {
        return true;
      }
    }

    return false;
  }
  
  /**
   * current_user_can
   * Checks if the current user's capability is listed in an array of $capabilities.
   *
   * @param array $capabilities Collection of capabilities.
   * @param bool $needs_all The curent user's capability must match ALL capabilities in the array.
   *
   * @return bool Status of check.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function current_user_can($capabilities, $needs_all = false) 
  {
    $capabilities = is_array($capabilities) ? $capabilities : explode(',', $capabilities);
  
    foreach ($capabilities as $capability) 
    {
      $user_can = current_user_can(trim(strtolower($capability)));
    
      if ($needs_all && !$user_can)
      { 
        return false;
      }
      elseif (!$needs_all && $user_can)
      {
        return true;
      }
    }
  
    return $needs_all;
  }
}