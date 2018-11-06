<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Meta
 * Handles all aspects of meta data for Piklist
 *
 * @package     Piklist
 * @subpackage  Meta
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Meta
{
  /**
   * @var array The meta keys that are grouped for multiple select fields.
   * @access public
   */
  public static $grouped_meta_keys = array(
    'post' => array()
    ,'term' => array()
    ,'comment' => array()
    ,'user' => array()
  );

  /**
   * @var array The meta that should be cleared from the cache when using the edit screens.
   * @access private
   */
  private static $reset_meta = array(
    'post.php' => array(
      'id' => 'post'
      ,'group' => 'post_meta'
    )
    ,'user-edit.php' => array(
      'id' => 'user_id'
      ,'group' => 'user_meta'
    )
    ,'comment.php' => array(
      'id' => 'comment_id'
      ,'group' => 'comment_meta'
    )
  );

  /**
   * @var bool Whether a post revision check was fired.
   * @access private
   */
  private static $wp_save_post_revision_check = false;

  /**
   * @var array Non Piklist meta-boxes that have already been removed.
   * @access private
   */
  private static $meta_boxes_removed = array();

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
    add_action('init', array('piklist_meta', 'meta_grouped'), 100);
    add_action('init', array('piklist_meta', 'meta_reset'));
    add_action('query', array('piklist_meta', 'meta_sort'));
    add_action('add_meta_boxes', array('piklist_meta', 'register_meta_boxes'), 1000);
    add_action('admin_head', array('piklist_meta', 'sort_meta_boxes'), 1050, 3);
    add_action('piklist_parts_process-meta-boxes', array('piklist_meta', 'clear_screen'), 50);

    add_filter('get_post_metadata', array('piklist_meta', 'get_post_meta'), 100, 4);
    add_filter('get_user_metadata', array('piklist_meta', 'get_user_meta'), 100, 4);
    add_filter('get_term_metadata', array('piklist_meta', 'get_term_meta'), 100, 4);

    add_filter('wp_save_post_revision_check_for_changes', array('piklist_meta', 'wp_save_post_revision_check_for_changes'), -1, 3);
    add_filter('wp_save_post_revision_post_has_changed', array('piklist_meta', 'wp_save_post_revision_post_has_changed'), -1, 3);
    add_filter('get_post_metadata', array('piklist_meta', 'wp_save_post_revision_post_meta_serialize'), 100, 4);

    add_filter('piklist_part_process-meta-boxes', array('piklist_meta', 'part_process'), 10, 2);
  }

  /**
   * update_meta_box
   * Check if a meta box exists and possible remove it.
   *
   * @param mixed $screen The current screen
   * @param string $id The id of the meta box
   * @param string $action Whether to search or remove the meta box
   *
   * @return mixed The position of the meta box if it was removed, otherwise the status of whether it was found.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function update_meta_box($screen, $id, $action = 'search')
  {
    global $wp_meta_boxes;

    $check = false;

    if (empty($screen))
    {
      $screen = get_current_screen();
    }
    elseif (is_string($screen))
    {
      $screen = convert_to_screen($screen);
    }

    $page = $screen->id;

    foreach (array('normal', 'advanced', 'side') as $context)
    {
      foreach (array('high', 'sorted', 'core', 'default', 'low') as $priority)
      {
        if (isset($wp_meta_boxes[$page][$context][$priority]))
        {
          foreach ($wp_meta_boxes[$page][$context][$priority] as $order => $meta_box)
          {
            if ($meta_box['id'] == $id)
            {
              if ($action == 'remove')
              {
                unset($wp_meta_boxes[$page][$context][$priority][$order]);

                return $order;
              }

              $check = true;
            }
          }
        }
      }
    }

    return $check;
  }

  /**
   * register_meta_boxes
   * Register the meta-boxes parts folder
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_meta_boxes()
  {
    $data = array(
              'title' => 'Title'
              ,'context' => 'Context'
              ,'description' => 'Description'
              ,'capability' => 'Capability'
              ,'role' => 'Role'
              ,'priority' => 'Priority'
              ,'order' => 'Order'
              ,'post_type' => 'Post Type'
              ,'post_status' => 'Post Status'
              ,'lock' => 'Lock'
              ,'collapse' => 'Collapse'
              ,'status' => 'Status'
              ,'new' => 'New'
              ,'id' => 'ID'
              ,'slug' => 'Slug'
              ,'template' => 'Template'
              ,'meta_box' => 'Meta Box'
              ,'post_format' => 'Post Format'
            );

    piklist::process_parts('meta-boxes', $data, array('piklist_meta', 'register_meta_boxes_callback'));
  }

  /**
   * clear_screen
   * Clear the screen of all meta-boxes
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function clear_screen()
  {
    global $wp_meta_boxes, $current_screen;

    $workflow = piklist_workflow::get('workflow');

    if ($workflow && !empty($workflow['data']['clear']) && $workflow['data']['clear'] == true && piklist_admin::is_post())
    {
      remove_post_type_support('post', 'editor');
      remove_post_type_support('post', 'title');

      foreach (array('normal', 'advanced', 'side') as $context)
      {
        foreach (array('high', 'sorted', 'core', 'default', 'low') as $priority)
        {
          if (isset($wp_meta_boxes[$current_screen->id][$context][$priority]))
          {
            foreach ($wp_meta_boxes[$current_screen->id][$context][$priority] as $meta_box)
            {
              if ($meta_box['id'] != 'submitdiv')
              {
                unset($wp_meta_boxes[$current_screen->id][$context][$priority][$meta_box['id']]);
              }
            }
          }
        }
      }
    }
  }

  /**
   * register_meta_boxes_callback
   * Process the resulting parts from the registration of the meta-boxes part folder.
   *
   * @param $arguments
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_meta_boxes_callback($arguments)
  {
    extract($arguments);

    $textdomain = isset(piklist_add_on::$available_add_ons[$add_on]['TextDomain']) ? piklist_add_on::$available_add_ons[$add_on]['TextDomain'] : null;
    $title = !empty($data['title']) ? $data['title'] : $id;
    $title = !empty($textdomain) ? __($title, $textdomain) : __($title);
    $types = empty($data['post_type']) ? get_post_types() : $data['post_type'];
    $context = empty($data['context']) ? 'normal' : $data['context'];
    $priority = empty($data['priority']) ? 'low' : $data['priority'];

    foreach ($types as $type)
    {
      $type = trim($type);

      if ($data['extend'] && $data['extend_method'] == 'remove')
      {
        $original_order = self::update_meta_box($type, $data['extend'], 'remove');
      }
      elseif (!$data['extend'] || ($data['extend'] && self::update_meta_box($type, $id)) || ($data['extend'] && in_array($data['extend'], self::$meta_boxes_removed)))
      {
        $original_order = self::update_meta_box($type, $id, 'remove');

        add_meta_box(
          $id
          ,$title
          ,array('piklist_meta', 'meta_box')
          ,$type
          ,$context
          ,$priority
          ,array(
            'render' => $render
            ,'add_on' => $add_on
            ,'order' => $data['order'] ? $data['order'] : $original_order
            ,'data' => $data
          )
        );

        if ($data['meta_box'] === false)
        {
          add_filter("postbox_classes_{$type}_{$id}", array('piklist_meta', 'lock_meta_boxes'));
          add_filter("postbox_classes_{$type}_{$id}", array('piklist_meta', 'no_meta_boxes'));
        }
        else
        {
          if ($data['lock'] === true)
          {
            add_filter("postbox_classes_{$type}_{$id}", array('piklist_meta', 'lock_meta_boxes'));
          }

          if ($data['collapse'] === true)
          {
            add_filter("postbox_classes_{$type}_{$id}", array('piklist_meta', 'collapse_meta_boxes'));
          }
        }

        if ($title == $id)
        {
          add_filter("postbox_classes_{$type}_{$id}", array('piklist_meta', 'no_title_meta_boxes'));
        }

        add_filter("postbox_classes_{$type}_{$id}", array('piklist_meta', 'default_classes'));
      }
    }
  }

  /**
   * meta_box
   * Render the meta box.
   *
   * @param array $post The post object.
   * @param array $meta_box The meta box object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function meta_box($post, $meta_box)
  {
    do_action('piklist_pre_render_meta_box', $post, $meta_box);

    if ($meta_box['args']['render'])
    {
      foreach ($meta_box['args']['render'] as $render)
      {
        if (is_array($render) && array_key_exists('callback', $render) && array_key_exists('args', $render))
        {
          call_user_func($render['callback'], $post, $render['args']);
        }
        elseif (!is_array($render))
        {
          piklist::render($render, array(
            'data' => $meta_box['args']['data']
          ));
        }
      }
    }

    do_action('piklist_post_render_meta_box', $post, $meta_box);
  }

  /**
   * part_process
   * Process part addition.
   *
   * @param array $part
   *
   * @return array The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function part_process($part)
  {
    global $wp_meta_boxes, $current_screen;

    foreach (array('normal', 'advanced', 'side') as $context)
    {
      foreach (array('high', 'sorted', 'core', 'default', 'low') as $priority)
      {
        if (isset($wp_meta_boxes[$current_screen->id][$context][$priority]))
        {
          foreach ($wp_meta_boxes[$current_screen->id][$context][$priority] as $meta_box)
          {
            if ($meta_box['id'] == $part['id'] && (!isset($part['data']['post_type']) || ($part['data']['post_type'] && in_array($current_screen->id, $part['data']['post_type']))))
            {
              if (is_array($part['render']) && !in_array($meta_box, $part['render']))
              {
                if ($part['data']['extend_method'] == 'before')
                {
                  array_push($part['render'], $meta_box);
                }
                elseif ($part['data']['extend_method'] == 'after')
                {
                  array_unshift($part['render'], $meta_box);
                }

                if (empty($part['data']['context']))
                {
                  $part['data']['context'] = $context;
                }

                if (empty($part['data']['priority']))
                {
                  $part['data']['priority'] = $priority;
                }

                unset($wp_meta_boxes[$current_screen->id][$context][$priority][$meta_box['id']]);

                array_push(self::$meta_boxes_removed, $meta_box['id']);
              }
            }
          }
        }
      }
    }

    return $part;
  }

  /**
   * sort_meta_boxes
   * Sort the meta boxes by the order parameter
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sort_meta_boxes()
  {
    global $pagenow, $typenow;

    if (in_array($pagenow, array('edit.php', 'post.php', 'post-new.php')) && post_type_exists(get_post_type()))
    {
      global $wp_meta_boxes;

      foreach (array('side', 'normal', 'advanced') as $context)
      {
        foreach (array('high', 'sorted', 'core', 'default', 'low') as $priority)
        {
          if (isset($wp_meta_boxes[$typenow][$context][$priority]))
          {
            uasort($wp_meta_boxes[$typenow][$context][$priority], array('piklist', 'sort_by_args_order'));
          }
        }
      }
    }
  }

  /**
   * lock_meta_boxes
   * Returns classes to be used by a metabox, to lock the metabox.
   *
   * @param string $classes The classes for the meta box.
   *
   * @return string The classes for the meta box.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function lock_meta_boxes($classes)
  {
    array_push($classes, 'piklist-meta-box-lock');

    return $classes;
  }

  /**
   * no_title_meta_boxes
   * Returns classes to be used by a metabox, to remove the title.
   *
   * @param string $classes The classes for the meta box.
   *
   * @return string The classes for the meta box.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function no_title_meta_boxes($classes)
  {
    array_push($classes, 'piklist-meta-box-no-title');

    return $classes;
  }

  /**
   * no_meta_boxes
   * Returns classes to be used by a metabox, to remove the metabox ui.
   *
   * @param string $classes The classes for the meta box.
   *
   * @return string The classes for the meta box.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function no_meta_boxes($classes)
  {
    array_push($classes, 'piklist-meta-box-none');

    return $classes;
  }

  /**
   * default_classes
   * Returns classes to be used by a metabox, to identify it as a meta-box created by Piklist.
   *
   * @param string $classes The classes for the meta box.
   *
   * @return string The classes for the meta box.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function default_classes($classes)
  {
    array_push($classes, 'piklist-meta-box');

    return $classes;
  }

  /**
   * collapse_meta_boxes
   * Returns classes to be used by a metabox, to collapse it by default.
   *
   * @param string $classes The classes for the meta box.
   *
   * @return string The classes for the meta box.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function collapse_meta_boxes($classes)
  {
    array_push($classes, 'piklist-meta-box-collapse');

    return $classes;
  }

  /**
   * default_post_title
   * Sets the default post title to post_type and post_id.
   *
   * @param int $id The post id.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function default_post_title($id)
  {
    $post = get_post($id);

    wp_update_post(array(
      'ID' => $id
      ,'post_title' => ucwords(str_replace(array('-', '_'), ' ', $post->post_type)) . ' ' . $id
    ));
  }

  /**
   * meta_grouped
   * Find all meta keys that are grouped
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function meta_grouped()
  {
    global $wpdb;

    foreach (self::$grouped_meta_keys as $meta_type => $meta_keys)
    {
      if (false !== ($meta = self::get_meta_properties($meta_type)) && !empty($meta->table))
      {
        $prefix = trim($wpdb->prepare('%s', piklist::$prefix), "'");
        $group_keys = $wpdb->get_col("SELECT DISTINCT meta_key FROM $meta->table WHERE meta_key LIKE '\\_\\{$prefix}%'");

        foreach ($group_keys as $group_key)
        {
          $key = $wpdb->get_var($wpdb->prepare("SELECT DISTINCT meta_key FROM $meta->table WHERE meta_key = %s", str_replace('_' . piklist::$prefix, '', $group_key)));

          if ($key)
          {
            array_push(self::$grouped_meta_keys[$meta_type], $group_key);
          }
        }
      }
    }
  }

  /**
   * meta_reset
   * Reset the cache for meta based on the admin edit page.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function meta_reset()
  {
    global $pagenow;

    /**
     * piklist_reset_meta_admin_pages
     *
     * @since 1.0
     */
    self::$reset_meta = apply_filters('piklist_reset_meta_admin_pages', self::$reset_meta);

    if (in_array($pagenow, self::$reset_meta))
    {
      foreach (self::$reset_meta as $page => $data)
      {
        if (isset($_REQUEST[$data['id']]))
        {
          wp_cache_replace($_REQUEST[$data['id']], false, $data['group']);

          break;
        }
      }
    }
  }

  /**
   * meta_sort
   * Sort meta by meta_id when pulling it using standard functions to maintain sort orders from fields.
   *
   * @param string $query The query for the meta.
   *
   * @return string The query for the meta.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function meta_sort($query)
  {
    global $wpdb;

    if (stristr($query, ', meta_key, meta_value FROM'))
    {
      $default_meta_tables_sort = array(
        'post_id' => $wpdb->postmeta
        ,'comment_id' => $wpdb->commentmeta
        ,'user_id' => $wpdb->usermeta
      );

      /**
       * piklist_meta_tables_sort
       * The tables to re-order when running meta queries
       *
       * @since 1.0
       */
      $piklist_meta_tables_sort = apply_filters('piklist_meta_tables_sort', array());

      // Do not allow filter to overwrite $default_meta_tables_sort
      $meta_tables = array_merge($piklist_meta_tables_sort, $default_meta_tables_sort);


      foreach ($meta_tables as $id => $meta_table)
      {
        if (stristr($query, "SELECT {$id}, meta_key, meta_value FROM {$meta_table} WHERE {$id} IN") && !stristr($query, ' ORDER BY '))
        {
          return $query . ' ORDER BY meta_id ASC';
        }
      }
    }

    return $query;
  }

  /**
   * get_post_meta
   * Filter the meta call to preserve an group structures that are not stored as serialized arrays.
   *
   * @param mixed $value The value returned.
   * @param int $object_id The id of the object.
   * @param string $meta_key The meta key requested.
   * @param bool $single Whether a single value should be returned
   *
   * @return mixed An updated value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_post_meta($value, $object_id, $meta_key, $single = false)
  {
    return self::get_metadata($value, 'post', $object_id, $meta_key, $single);
  }

  /**
   * get_user_meta
   * Filter the meta call to preserve an group structures that are not stored as serialized arrays.
   *
   * @param mixed $value The value returned.
   * @param int $object_id The id of the object.
   * @param string $meta_key The meta key requested.
   * @param bool $single Whether a single value should be returned
   *
   * @return mixed An updated value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_user_meta($value, $object_id, $meta_key, $single = false)
  {
    return self::get_metadata($value, 'user', $object_id, $meta_key, $single);
  }

  /**
   * get_term_meta
   * Filter the meta call to preserve an group structures that are not stored as serialized arrays.
   *
   * @param mixed $value The value returned.
   * @param int $object_id The id of the object.
   * @param string $meta_key The meta key requested.
   * @param bool $single Whether a single value should be returned
   *
   * @return mixed An updated value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_term_meta($value, $object_id, $meta_key, $single = false)
  {
    return self::get_metadata($value, 'term', $object_id, $meta_key, $single);
  }

  /**
   * get_metadata
   * Get the meta data update if it is grouped.
   *
   * @param mixed $value The value returned.
   * @param string $meta_type The type of meta.
   * @param int $object_id The id of the object.
   * @param string $meta_key The meta key requested.
   * @param bool $single Whether a single value should be returned
   *
   * @return mixed An updated value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_metadata($value, $meta_type, $object_id, $meta_key, $single)
  {
    global $wpdb;

    $meta_key = '_' . piklist::$prefix . $meta_key;

    if (is_array(self::$grouped_meta_keys[$meta_type]) && in_array($meta_key, self::$grouped_meta_keys[$meta_type]))
    {
      remove_filter('get_post_metadata', array('piklist_meta', 'get_post_meta'), 100);
      remove_filter('get_user_metadata', array('piklist_meta', 'get_user_meta'), 100);
      remove_filter('get_term_metadata', array('piklist_meta', 'get_term_meta'), 100);

      if (($meta_ids = get_metadata($meta_type, $object_id, $meta_key)) && ($meta = self::get_meta_properties($meta_type)) !== false)
      {
        foreach ($meta_ids as &$group)
        {
          foreach ($group as &$meta_id)
          {
            $meta_id = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $meta->table WHERE $meta->id = %d", $meta_id));
          }
          unset($meta_id);
        }
        unset($group);

        $value = $meta_ids;
      }

      add_filter('get_post_metadata', array('piklist_meta', 'get_post_meta'), 100, 4);
      add_filter('get_user_metadata', array('piklist_meta', 'get_user_meta'), 100, 4);
      add_filter('get_term_metadata', array('piklist_meta', 'get_term_meta'), 100, 4);
    }

    return $value;
  }

  /**
   * get_meta_properties
   * Get all the properties needed to updated a meta table.
   *
   * @param string $meta_type The meta type for the properties.
   *
   * @return array The properties needed to updated a meta table.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_meta_properties($meta_type)
  {
    global $wpdb;

    switch ($meta_type)
    {
      case 'post':

        $meta = array(
          'table' => $wpdb->postmeta
          ,'id' => 'meta_id'
          ,'object_id' => 'post_id'
        );

      break;

      case 'term':

        $meta = !isset($wpdb->termmeta) ? false : array(
          'table' => $wpdb->termmeta
          ,'id' => 'meta_id'
          ,'object_id' => 'term_id'
        );

      break;

      case 'user':

        $meta = array(
          'table' => $wpdb->usermeta
          ,'id' => 'umeta_id'
          ,'object_id' => 'user_id'
        );

      break;

      case 'comment':

        $meta = array(
          'table' => $wpdb->commentmeta
          ,'id' => 'meta_id'
          ,'object_id' => 'comment_id'
        );

      break;
    }

    return is_array($meta) ? (object) $meta : false;
  }

  /**
   * wp_save_post_revision_check_for_changes
   * Set a flag if a check for changes was fired.
   *
   * @param bool $check_for_changes Whether to check for changes before saving a new revision.
   * @param string $last_revision The the last revision post object.
   * @param object $post The post object.
   *
   * @return bool Whether to check.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_save_post_revision_check_for_changes($check_for_changes, $last_revision, $post)
  {
    self::$wp_save_post_revision_check = true;

    return $check_for_changes;
  }

  /**
   * wp_save_post_revision_post_has_changed
   * Set a flag if a change was fired.
   *
   * @param bool $post_has_changed Whether the post has changed
   * @param string $last_revision The the last revision post object.
   * @param object $post The post object.
   *
   * @return bool Whether it changed.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_save_post_revision_post_has_changed($post_has_changed, $last_revision, $post)
  {
    self::$wp_save_post_revision_check = false;

    return $post_has_changed;
  }

  /**
   * wp_save_post_revision_post_meta_serialize
   * Serialize meta data for a revsion.
   *
   * @param mixed $value The value returned.
   * @param int $object_id The id of the object.
   * @param string $meta_key The meta key requested.
   * @param bool $single Whether a single value should be returned
   *
   * @return mixed An updated value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_save_post_revision_post_meta_serialize($value, $object_id, $meta_key, $single)
  {
    global $wpdb;

    if (self::$wp_save_post_revision_check)
    {
      $meta = self::get_meta_properties('post');

      $value = $wpdb->get_var($wpdb->prepare("SELECT meta_value FROM $meta->table WHERE $meta->object_id = %d AND meta_key = %s", $object_id, $meta_key));
      $value = maybe_serialize($value);
    }

    return $value;
  }
}
