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
    add_action('init', array(__CLASS__, 'register_arguments'));
    add_action('init', array(__CLASS__, 'meta_grouped'), 100);
    add_action('init', array(__CLASS__, 'meta_reset'));
    add_action('query', array(__CLASS__, 'meta_sort'));
    add_action('add_meta_boxes', array(__CLASS__, 'register'), 1000);
    add_action('admin_head', array(__CLASS__, 'sort_meta_boxes'), 1050, 3);
    add_action('piklist_parts_process-meta-boxes', array(__CLASS__, 'clear_screen'), 50);

    add_filter('get_post_metadata', array(__CLASS__, 'get_post_meta'), 100, 4);
    add_filter('get_user_metadata', array(__CLASS__, 'get_user_meta'), 100, 4);
    add_filter('get_term_metadata', array(__CLASS__, 'get_term_meta'), 100, 4);

    add_filter('wp_save_post_revision_check_for_changes', array(__CLASS__, 'wp_save_post_revision_check_for_changes'), -1, 3);
    add_filter('wp_save_post_revision_post_has_changed', array(__CLASS__, 'wp_save_post_revision_post_has_changed'), -1, 3);
    add_filter('get_post_metadata', array(__CLASS__, 'wp_save_post_revision_post_meta_serialize'), 100, 4);

    add_filter('piklist_part_process-meta-boxes', array(__CLASS__, 'part_process'), 10, 2);
    add_filter('piklist_argument_validation_rules', array(__CLASS__, 'validation_rules'));
  }

  /**
   * register
   * Register the meta-boxes parts folder
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register()
  {
    piklist::process_parts('meta-boxes', piklist_arguments::get('meta-boxes', 'part'), array(__CLASS__, 'register_callback'));
  }

  /**
   * register_callback
   * Process the resulting parts from the registration of the meta-boxes part folder.
   *
   * @param $arguments
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_callback($arguments)
  {
    extract($arguments);

    $textdomain = isset(piklist_add_on::$available_add_ons[$add_on]['TextDomain']) ? piklist_add_on::$available_add_ons[$add_on]['TextDomain'] : null;
    
    $data['title'] = !empty($data['title']) ? $data['title'] : $id;
    $data['title'] = !empty($textdomain) ? __($data['title'], $textdomain) : __($data['title']);
    
    $data['post_type'] = empty($data['post_type']) ? get_post_types() : $data['post_type'];
    
    foreach ($data['post_type'] as $type)
    {
      if ($data['extend'] && $data['extend_method'] == 'remove')
      {
        $original_order = self::update_meta_box($type, $data['extend'], 'remove');
      }
      elseif (!$data['extend'] || ($data['extend'] && self::update_meta_box($type, $id)) || ($data['extend'] && in_array($data['extend'], self::$meta_boxes_removed)))
      {
        $original_order = self::update_meta_box($type, $id, 'remove');

        add_meta_box(
          $id
          ,$data['title']
          ,array(__CLASS__, 'meta_box')
          ,$type
          ,$data['context']
          ,$data['priority']
          ,array(
            'render' => $render
            ,'add_on' => $add_on
            ,'order' => $data['order'] ? $data['order'] : $original_order
            ,'data' => $data
          )
        );
        
        if ($data['meta_box'] === false)
        {
          add_filter("postbox_classes_{$type}_{$id}", array(__CLASS__, 'lock_meta_boxes'));
          add_filter("postbox_classes_{$type}_{$id}", array(__CLASS__, 'no_meta_boxes'));
        }
        else
        {
          if ($data['lock'] === true)
          {
            add_filter("postbox_classes_{$type}_{$id}", array(__CLASS__, 'lock_meta_boxes'));
          }

          if ($data['collapse'] === true)
          {
            add_filter("postbox_classes_{$type}_{$id}", array(__CLASS__, 'collapse_meta_boxes'));
          }
        }

        if ($data['title'] == $id)
        {
          add_filter("postbox_classes_{$type}_{$id}", array(__CLASS__, 'no_title_meta_boxes'));
        }

        add_filter("postbox_classes_{$type}_{$id}", array(__CLASS__, 'default_classes'));
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
   * clear_screen
   * Clear the screen of all meta-boxes
   *
   * @param boolean $title Whether or not to remove the title
   * @param boolean $editor Whether or not to remove the editor
   * @param array $exclude_meta_boxes Meta boxes to exclude from the clear
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function clear_screen($title = true, $editor = true, $exclude_meta_boxes = array())
  {
    global $wp_meta_boxes, $current_screen;

    $workflow = piklist_workflow::get('workflow');

    if ($workflow && !empty($workflow['data']['clear']) && piklist_admin::is_post())
    {
      if (!piklist::is_bool($workflow['data']['clear']))
      {
        $clear_arguments = piklist::explode(',', $workflow['data']['clear']);
        
        $found = array_search('title', $clear_arguments);
        if ($found !== false)
        {
          $title = false;
          unset($clear_arguments[$found]);
        }
        
        $found = array_search('editor', $clear_arguments);
        if ($found !== false)
        {
          $editor = false;
          unset($clear_arguments[$found]);
        }
        
        if (!empty($clear_arguments))
        {
          $exclude_meta_boxes = $clear_arguments;
        }
      }
      elseif (piklist::to_bool($workflow['data']['clear']) == true)
      { 
        $title = true;
        $editor = true;
      }
      
      if ($title)
      {
        remove_post_type_support('post', 'title');
      }
      
      if ($editor)
      {
        remove_post_type_support('post', 'editor');
      }

      foreach (array('normal', 'advanced', 'side') as $context)
      {
        foreach (array('high', 'sorted', 'core', 'default', 'low') as $priority)
        {
          if (isset($wp_meta_boxes[$current_screen->id][$context][$priority]))
          {
            foreach ($wp_meta_boxes[$current_screen->id][$context][$priority] as $meta_box)
            {
              if ($meta_box['id'] != 'submitdiv' && !in_array($meta_box['id'], $exclude_meta_boxes))
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
      remove_filter('get_post_metadata', array(__CLASS__, 'get_post_meta'), 100);
      remove_filter('get_user_metadata', array(__CLASS__, 'get_user_meta'), 100);
      remove_filter('get_term_metadata', array(__CLASS__, 'get_term_meta'), 100);

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

      add_filter('get_post_metadata', array(__CLASS__, 'get_post_meta'), 100, 4);
      add_filter('get_user_metadata', array(__CLASS__, 'get_user_meta'), 100, 4);
      add_filter('get_term_metadata', array(__CLASS__, 'get_term_meta'), 100, 4);
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
  
  /**
   * register_arguments
   * Register arguments for our helper methods
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_arguments()
  {
    piklist_arguments::register('meta-boxes', array(
      // Basics
      'title' => array(
        'description' => __('The title of the meta box.', 'piklist')
      )
      ,'description' => array(
        'description' => __('The description of what the meta box is for.', 'piklist')
      )
          
      // Permissions
      ,'capability' => array(
        'description' => __('The user capability needed by the user to view the meta box.', 'piklist')
        ,'validate' => 'capability'
      )
      ,'role' => array(
        'description' => __('The user role needed by the user to view the meta box.', 'piklist')
        ,'validate' => 'role'
      )
          
      // Display
      ,'context' => array(
        'description' => __('The context within the screen where the box should display.', 'piklist')
        ,'validate' => 'context'
        ,'default' => 'normal'
      )
      ,'priority' => array(
        'description' => __('The priority within the context where the box should show.', 'piklist')
        ,'validate' => 'priority'
        ,'default' => 'low'
      )
      ,'order' => array(
        'description' => __('The order within the context where the box should show, defined as an integer.', 'piklist')
        ,'type' => 'integer'
      )
      ,'lock' => array(
        'description' => __('Whether or not to allow the meta box to be re-positioned.', 'piklist')
        ,'type' => 'boolean'
        ,'default' => false
      )
      ,'collapse' => array(
        'description' => __('Whether or not to collapse the meta-box by default.', 'piklist')
        ,'type' => 'boolean'
        ,'default' => false
      )
      ,'meta_box' => array(
        'description' => __('Show the default UI Chrome for a meta box.', 'piklist')
        ,'type' => 'boolean'
        ,'default' => true
      )
            
      // Display - Conditions
      ,'new' => array(
        'description' => __('Show the meta box for new post type content only.', 'piklist')
        ,'type' => 'boolean'
        ,'default' => false
      )
      ,'post_type' => array(
        'description' => __('The post type the meta box should be show for.', 'piklist')
        ,'type' => 'array'
        ,'validate' => 'post_type'
        ,'default' => 'post'
      )
      ,'post_status' => array(
        'description' => __('The post status the meta box should be show for.', 'piklist')
        ,'type' => 'array'
        ,'validate' => 'post_status'
      )
      ,'status' => array(
        'description' => __('The post status the meta box should be show for.', 'piklist')
        ,'type' => 'array'
        ,'validate' => 'post_status'
        ,'depreciated' => true
      )
      ,'post_format' => array(
        'description' => __('The title of the meta box.', 'piklist')
        ,'validate' => 'post_format'
      )
      ,'id' => array(
        'description' => __('Show the meta box only for a specific id or list of ids\'.', 'piklist')
        ,'type' => 'integer'
      )
      ,'template' => array(
        'description' => __('Only show the meta box for a specified template.', 'piklist')
        ,'type' => 'array'
        ,'validate' => 'page_template'
      )
    ));
  }
  
  /**
   * Included Validation Callbacks
   */

  /**
   * validation_rules
   * Array of included validation rules.
   *
   * @param array $validation_rules Validation rules.
   *
   * @return array Validation rules.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validation_rules($validation_rules)
  {
    $validation_rules = array_merge($validation_rules, array(
      'context' => array(
        'name' => __('Context', 'piklist')
        ,'callback' => array(__CLASS__, 'validate_context')
      )          
      ,'priority' => array(
        'name' => __('Priority', 'piklist')
        ,'callback' => array(__CLASS__, 'validate_priority')
      )
    ));

    return $validation_rules;
  }
  
  /**
   * validate_context
   *
   * @param $argument
   * @param $value
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_context($argument, $value)
  {
    return in_array($value, array('normal', 'side', 'advanced')) ? true : sprintf(__('The argument <strong>Context</strong> with the value of <strong>%s</strong> is not valid.', 'piklist'), $value);
  }  

  /**
   * validate_priority
   *
   * @param $argument
   * @param $value
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_priority($argument, $value)
  {
    return in_array($value, array('default', 'low', 'high', 'sorted', 'core')) ? true : sprintf(__('The argument <strong>Priority</strong> with the value of <strong>%s</strong> is not valid.', 'piklist'), $value);
  }
}
