<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Menu
 * Controls menu modifications and features.
 *
 * @package     Piklist
 * @subpackage  Menu
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Menu
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
    add_filter('wp_nav_menu', array('piklist_menu', 'wp_nav_menu_updates'));
  }
  
  /**
   * wp_nav_menu_updates
   * Add first and last classes to the menus
   *
   * @param string $output The html output of the menu.
   *
   * @return string The html output of the menu.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_nav_menu_updates($output) 
  {
    $permalink_structure = get_option('permalink_structure');
    
    if (!empty($permalink_structure))
    {
      $parse = preg_match_all('/<li id="menu-item-(\d+)/', $output, $matches);
      
      for ($i = 0; $i < count($matches[1]); $i++)
      {
        $menu_id = $matches[1][$i];
        
        $id = get_post_meta($menu_id, '_menu_item_object_id', true);
  
        $class = '';
        
        if ($i == 0)
        {
          $class = 'first-menu-item';
        }
        elseif ($i + 1 == count($matches[1]))
        {
          $class = 'last-menu-item';
        }
  
        $output = preg_replace('/menu-item-' . $menu_id . '">/', 'menu-item-' . $menu_id . ' menu-item-' . basename(get_permalink($id)) . ' ' . $class . '">', $output, 1);
      }
    }

    return $output;
  }
}