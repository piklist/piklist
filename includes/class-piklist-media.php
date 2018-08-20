<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Media
 * Controls media modifications and features.
 *
 * @package     Piklist
 * @subpackage  Media
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Media
{
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
    add_action('admin_init', array('piklist_media', 'init'), 50);

    add_filter('attachment_fields_to_edit', array('piklist_media', 'attachment_fields_to_edit'), 100, 2);
  }

  /**
   * attachment_fields_to_edit
   * Checks if there are meta boxes to render.
   *
   * @param $form_fields
   * @param $post
   *
   * @return
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function attachment_fields_to_edit($form_fields, $post)
  {
    global $typenow;

    if ($typenow =='attachment')
    {
      if ($meta_boxes = self::meta_box($post))
      {
        $form_fields['_final'] = $meta_boxes . '<tr class="final"><td colspan="2">' . (isset($form_fields['_final']) ? $form_fields['_final'] : '');
      }
    }

    return $form_fields;
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
    self::add_image_sizes();
  }

  /**
   * add_image_sizes
   * adds images sizes used by Piklist.
   *
   * @return
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function add_image_sizes()
  {
    add_image_size('piklist_file_preview', 96, 96, true);
  }

  /**
   * register_meta_boxes
   * register meta boxes.
   *
   *
   * @return
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
              ,'role' => 'Role'
              ,'order' => 'Order'
              ,'new' => 'New'
              ,'id' => 'ID'
              ,'slug' => 'Slug'
            );

    piklist::process_parts('media', $data, array('piklist_media', 'register_meta_boxes_callback'));
  }

  /**
   * register_meta_boxes_callback
   * Handle the registration of a meta box for media.
   *
   * @param $arguments
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_meta_boxes_callback($arguments)
  {
    global $pagenow;

    extract($arguments);

    if (!$data['new'] || ($data['new'] && !in_array($pagenow, array('async-upload.php', 'media-new.php'))))
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
   * @param $post
   *
   * @return
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function meta_box($post)
  {
    if (!empty(self::$meta_boxes))
    {
      ob_start();

      $GLOBALS['piklist_attachment'] = $post;

      uasort(self::$meta_boxes, array('piklist', 'sort_by_data_order'));

      foreach (self::$meta_boxes as $meta_box)
      {
        piklist::render('shared/meta-box-start', array(
          'meta_box' => $meta_box
          ,'wrapper' => 'media_meta'
        ), false);

        do_action('piklist_pre_render_media_meta_box', $post, $meta_box);

        foreach ($meta_box['render'] as $render)
        {
          piklist::render($render, array(
            'data' => $meta_box['data']
          ), false);
        }

        do_action('piklist_post_render_media_meta_box', $post, $meta_box);

        piklist::render('shared/meta-box-end', array(
          'meta_box' => $meta_box
          ,'wrapper' => 'media_meta'
        ), false);
      }

      unset($GLOBALS['piklist_attachment']);

      $output = ob_get_contents();

      ob_end_clean();

      return $output;
    }

    return null;
  }


  /**
   * image_has_size
   * Returns whether the image had the file created for the provided size
   *
   * @param  integer  $file_id  file id to check
   * @param  string   $size     the size to check for
   *
   * @return boolean            whether or not the image size is available
   */
  public static function image_has_size($file_id, $size) {
    $data = wp_get_attachment_metadata($file_id);
    return !empty($data['sizes'][$size]);
  }


  /**
   * get_image_sizes
   * Gets images sizes
   *
   * @param string $size the image size (i.e. medium)
   *
   * @return
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_image_sizes($size = '')
  {
    global $_wp_additional_image_sizes;

    $sizes = array();

    $get_intermediate_image_sizes = get_intermediate_image_sizes();

    foreach($get_intermediate_image_sizes as $_size)
    {
      if(in_array($_size, array('thumbnail', 'medium', 'large')))
      {
        $sizes[$_size]['width'] = get_option($_size . '_size_w');
        $sizes[$_size]['height'] = get_option($_size . '_size_h');
        $sizes[$_size]['crop'] = (bool) get_option($_size . '_crop');

      }
      elseif (isset($_wp_additional_image_sizes[$_size]))
      {
        $sizes[$_size] = array(
          'width' => $_wp_additional_image_sizes[$_size]['width']
          ,'height' => $_wp_additional_image_sizes[$_size]['height']
          ,'crop' =>  $_wp_additional_image_sizes[$_size]['crop']
        );
      }

    }

    // Get only 1 size if found
    if ($size)
    {
      if (isset($sizes[$size]))
      {
        return $sizes[$size];
      }
      else
      {
        return false;
      }

    }

    return $sizes;
  }
}
