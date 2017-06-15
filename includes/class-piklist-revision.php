<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Revision
 * Manages and enhances post revisions.
 *
 * @package     Piklist
 * @subpackage  Revision
 * @copyright   Copyright (c) 2012-2016, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Revision
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
    add_action('save_post', array('piklist_revision', 'save_post'), -1, 2);
    add_action('wp_restore_post_revision', array('piklist_revision', 'restore_revision'), 10, 2);

    add_filter('_wp_post_revision_fields', array('piklist_revision', 'wp_post_revision_fields'));
  }

  /**
   * save_post
   * Make sure metadata is saved on post revisions
   *
   * @param int $post_id The post id.
   * @param object $post The post object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function save_post($post_id, $post)
  {
    global $wpdb;

    if (($parent_id = wp_is_post_revision($post_id)) && !wp_is_post_autosave($post_id))
    {
      $wpdb->get_results($wpdb->prepare("INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) SELECT %d, meta_key, meta_value FROM $wpdb->postmeta WHERE post_id = %d", $post_id, $parent_id));
    }
  }

  /**
   * restore_revision
   * Restores a revision to the current post.
   *
   * @param int $post_id The post id.
   * @param int $revision_id The post revision id.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function restore_revision($post_id, $revision_id)
  {
    global $wpdb;

    $meta = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE post_id = %d", $revision_id));

    if ($meta)
    {
      foreach ($meta as $object)
      {
        update_metadata('post', $post_id, $object->meta_key, $object->meta_value);
      }
    }
  }

  /**
   * wp_post_revision_fields
   * Adds a custom field for metadata to the revision ui.
   *
   * @param array $fields The current set of fields for the ui.
   *
   * @return array Updated fields.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_post_revision_fields($fields)
  {
    global $wpdb;

    $meta_keys = $wpdb->get_col("SELECT DISTINCT meta_key FROM $wpdb->postmeta");

    foreach ($meta_keys as $meta_key)
    {
      $label = ucwords(str_replace(array('-', '_'), ' ', $meta_key));

      $fields[$meta_key] = __($label, 'piklist');

      add_filter('_wp_post_revision_field_' . $meta_key, array('piklist_revision', 'wp_post_revision_field'), 10, 4);
    }

    return $fields;
  }

  /**
   * wp_post_revision_field
   * Render the metadata in the field.
   *
   * @param int $value The field value.
   * @param int $field The field to retrieve.
   * @param int $revision The revistion to compare against.
   * @param string $type Type of comparison.
   *
   * @return mixed The metadata.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_post_revision_field($value, $field, $revision, $type)
  {
    return get_metadata('post', $revision->ID, $field, true);
  }
}
