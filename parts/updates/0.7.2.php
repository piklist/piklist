<?php
/*
 * Updates for v0.7.2
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Piklist_Update_0_7_2'))
{
  class Piklist_Update_0_7_2
  {
    public function __construct()
    {
      add_action('admin_init', array($this, 'admin_init'));
    }
    
    public function admin_init()
    {
      global $wpdb;

      $legacy_table = $wpdb->prefix . 'piklist_cpt_relate';

      $count = $wpdb->get_var("SHOW TABLES LIKE '{$legacy_table}'");

      // Does the legacy table exist?
      if (!empty($count))
      {
        // Grab data from legacy table
        $data = $wpdb->get_results("SELECT * FROM {$legacy_table}", ARRAY_A);

        // Move data to new table
        foreach ($data as $row)
        {
          $wpdb->insert(
            $wpdb->post_relationships
            ,$row
            ,array( 
              '%d'
              ,'%d'
              ,'%d'
            ) 
          );
        }

        // Delete legacy table
        $wpdb->query("DROP TABLE IF EXISTS {$legacy_table}");
      }
    }
  }
}