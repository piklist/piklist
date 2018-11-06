<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Taxonomy
 * Controls taxonomy modifications and features.
 *
 * @package     Piklist
 * @subpackage  Taxonomy
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Taxonomy
{
  /**
   * @var array Registered meta boxes.
   * @access private
   */
  private static $meta_boxes;

  /**
   * @var array Registered taxonomies.
   * @access private
   */
  private static $taxonomies = array();

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
    global $wp_version;

    add_action('piklist_activate', array('piklist_taxonomy', 'activate'));

    add_action('admin_init', array('piklist_taxonomy', 'register_meta_boxes'), 50);
    add_action('registered_taxonomy',  array('piklist_taxonomy', 'registered_taxonomy'), 10, 3);
    add_action('admin_menu', array('piklist_taxonomy', 'admin_menu'));

    add_filter('wp_redirect', array('piklist_taxonomy', 'wp_redirect'), 10, 2);
    add_filter('parent_file', array('piklist_taxonomy', 'parent_file'));
    add_filter('sanitize_user', array('piklist_taxonomy', 'restrict_username'));
    add_filter('piklist_meta_tables_sort', array('piklist_taxonomy', 'piklist_meta_tables_sort'));

    // Load before termmeta was native to WordPress
    if ( version_compare($wp_version, '4.4.0', '<') )
    {
      add_filter('init', array('piklist_taxonomy', 'register_tables'));
      add_filter('terms_clauses', array('piklist_taxonomy', 'terms_clauses'), 10, 3);
      add_filter('get_terms_args', array('piklist_taxonomy', 'get_terms_args'), 0);
    }
  }

  /**
   * piklist_meta_tables
   * Adds the termmeta table to the tables used in the meta sorting.
   *
   * @param array $meta_tables The registered meta tables.
   *
   * @return array The registered meta tables.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function piklist_meta_tables_sort($meta_tables)
  {
    global $wpdb;

    $meta_tables['term_id'] = $wpdb->prefix . 'termmeta';

    return $meta_tables;
  }

  /**
   * register_tables
   * Register the termmeta table.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_tables()
  {
    global $wpdb;

    $termmeta_table = $wpdb->prefix . 'termmeta';

    if ($wpdb->get_var("SHOW TABLES LIKE '{$termmeta_table}'") == $termmeta_table)
    {
      array_push($wpdb->tables, 'termmeta');

      $wpdb->termmeta = $wpdb->prefix . 'termmeta';
    }
  }

  /**
   * terms_clauses
   * Clause updates for term queries.
   *
   * @param array $pieces The pieces of the sql query
   * @param array $taxonomies The taxonomies for the query
   * @param array $arguments The arguments for the query
   *
   * @return array $pieces
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function terms_clauses($pieces = array(), $taxonomies = array(), $arguments = array())
  {
    if (!empty($arguments['meta_query']))
    {
      $query = new WP_Meta_Query($arguments['meta_query']);
      $query->parse_query_vars($arguments);

      if (!empty($query->queries))
      {
        $clauses = $query->get_sql('term', 'tt', 'term_id', $taxonomies);

        $pieces['join'] .= $clauses['join'];
        $pieces['where'] .= $clauses['where'];
      }
    }

    return $pieces;
  }

  /**
   * get_terms_args
   * Allow a meta query for terms
   *
   * @param array $arguments The arguments for the query
   *
   * @return array $arguments
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_terms_args($arguments = array())
  {
    return wp_parse_args($arguments, array(
      'meta_query' => ''
    ));
  }

  /**
   * register_meta_boxes
   * Register term sections.
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
              ,'taxonomy' => 'Taxonomy'
              ,'new' => 'New'
              ,'id' => 'ID'
              ,'slug' => 'Slug'
            );

    piklist::process_parts('terms', $data, array('piklist_taxonomy', 'register_meta_boxes_callback'));
  }

  /**
   * register_meta_boxes_callback
   * Handle the registration of a term section.
   *
   * @param array $arguments The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_meta_boxes_callback($arguments)
  {
    extract($arguments);

    $taxonomies = empty($data['taxonomy']) ? get_taxonomies() : $data['taxonomy'];

    foreach ($taxonomies as $taxonomy)
    {
      $data['taxonomy'] = trim($taxonomy);

      if (!isset(self::$meta_boxes[$data['taxonomy']]))
      {
        self::$meta_boxes[$data['taxonomy']] = array();

        add_action($data['taxonomy'] . '_edit_form_fields', array('piklist_taxonomy', 'meta_box_edit'), 10, 2);
        add_action($data['taxonomy'] . '_add_form_fields', array('piklist_taxonomy', 'meta_box_new'), 10, 1);
      }

      foreach (self::$meta_boxes[$data['taxonomy']] as $key => $meta_box)
      {
        if ($id == $meta_box['id'])
        {
          unset(self::$meta_boxes[$data['taxonomy']][$key]);
        }
      }

      if (isset($data['order']))
      {
        while (isset(self::$meta_boxes[$data['taxonomy']][$data['order']]))
        {
          $data['order']++;
        }

        self::$meta_boxes[$data['taxonomy']][$data['order']] = $arguments;
      }
      else
      {
        array_push(self::$meta_boxes[$data['taxonomy']], $arguments);
      }
    }
  }

  /**
   * meta_box_new
   * Render meta box in add form.
   *
   * @param string $taxonomy The taxonomy.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function meta_box_new($taxonomy)
  {
    self::meta_box($taxonomy, true);
  }

  /**
   * meta_box_edit
   * Render meta box.
   *
   * @param object $term The term object.
   * @param string $taxonomy The taxonomy.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function meta_box_edit($term = null, $taxonomy)
  {
    self::meta_box($taxonomy);
  }

  /**
   * meta_box
   * Render meta box.
   *
   * @param string $taxonomy The taxonomy.
   * @param bool $new Whether this is for the new form or edit form.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function meta_box($taxonomy, $new = false)
  {
    if ($taxonomy)
    {
      $close = false;

      uasort(self::$meta_boxes[$taxonomy], array('piklist', 'sort_by_data_order'));

      foreach (self::$meta_boxes[$taxonomy] as $_taxonomy => $meta_box)
      {
        if ($meta_box['data']['new'] == $new)
        {
          piklist::render('shared/meta-box-seperator', array(
            'meta_box' => $meta_box
            ,'wrapper' => 'term_meta' . ($new ? '_new' : null)
            ,'close' => $close
          ), false);

          if (!$new)
          {
            $close = true;
          }

          foreach ($meta_box['render'] as $render)
          {
            piklist::render($render, array(
              'taxonomy' => $_taxonomy
              ,'data' => $meta_box['data']
            ), false);
          }
        }
      }
    }
  }

  /**
   * activate
   * Creates custom tables.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function activate()
  {
    global $wpdb;

    $limit = stristr($wpdb->collate, 'mb4') ? 191 : 255;

    $table = piklist::create_table(
      'termmeta'
      ,'meta_id bigint(20) unsigned NOT NULL auto_increment
        ,term_id bigint(20) unsigned NOT NULL default "0"
        ,meta_key varchar(' . $limit . ') default NULL
        ,meta_value longtext
        ,PRIMARY KEY (meta_id)
        ,KEY term_id (term_id)
        ,KEY meta_key (meta_key)'
    );
  }

  /**
   * registered_taxonomy
   * Add taxonomy support for users.
   *
   * @param string $taxonomy The taxonomy name.
   * @param string $object_type The object type for the taxonomy.
   * @param string $arguments The taxonomy object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function registered_taxonomy($taxonomy, $object_type, $arguments)
  {
    global $wp_taxonomies;

    if ($object_type == 'user')
    {
      $arguments  = (object) $arguments;

      add_filter("manage_edit-{$taxonomy}_columns",  array('piklist_taxonomy', 'user_taxonomy_column'));

      add_action("manage_{$taxonomy}_custom_column",  array('piklist_taxonomy', 'user_taxonomy_column_value'), 10, 3);

      if (empty($arguments->update_count_callback))
      {
        $arguments->update_count_callback  = array('piklist_taxonomy', 'user_update_count');
      }

      $wp_taxonomies[$taxonomy]  = $arguments;
      self::$taxonomies[$taxonomy] = $arguments;
    }
  }

  /**
   * user_update_count
   * Show counts for user taxonomies
   *
   * @param array $terms Collection of terms
   * @param string $taxonomy The taxonomy name.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function user_update_count($terms, $taxonomy)
  {
    global $wpdb;

    foreach ($terms as $term)
    {
      $count  = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->term_relationships WHERE term_taxonomy_id = %d", $term));

      do_action('edit_term_taxonomy', $term, $taxonomy);

      $wpdb->update($wpdb->term_taxonomy, compact('count'), array(
        'term_taxonomy_id' => $term
      ));

      do_action('edited_term_taxonomy', $term, $taxonomy);
    }
  }

  /**
   * admin_menu
   * Add edit pages for user taxonomies
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function admin_menu()
  {
    $taxonomies  = self::$taxonomies;

    ksort(self::$taxonomies);

    foreach (self::$taxonomies as $slug => $taxonomy)
    {
      add_users_page($taxonomy->labels->menu_name, $taxonomy->labels->menu_name, $taxonomy->cap->manage_terms, 'edit-tags.php?taxonomy=' . $slug);
    }
  }

  /**
   * parent_file
   * Filter the parent file.
   *
   * @param string $file The parent file for the menu.
   *
   * @return string The parent file for the menu.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function parent_file($file = '')
  {
    global $pagenow;

    if (!empty($_REQUEST['taxonomy']) && isset(self::$taxonomies[$_REQUEST['taxonomy']]) && (in_array($pagenow, array('edit-tags.php', 'term.php'))))
    {
      return 'users.php';
    }

    return $file;
  }

  /**
   * user_taxonomy_column
   * Add a 'Users' column header to all user taxonomy edit pages.
   *
   * @param array $columns Collection of columns.
   *
   * @return array Collection of columns.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function user_taxonomy_column($columns)
  {
    $columns['users']  = __('Users', 'piklist');

    unset($columns['posts']);

    return $columns;
  }

  /**
   * user_taxonomy_column_value
   * Adds term data to 'Users' column on all user taxonomy edit pages.
   *
   * @param string $display The display.
   * @param string $column The column.
   * @param int $term_id The term id.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function user_taxonomy_column_value($display, $column, $term_id)
  {
    switch ($column)
    {
      case 'users':

        $term  = get_term($term_id, $_REQUEST['taxonomy']);

        echo $term->count;

      break;
    }
  }

  /**
   * restrict_username
   * Don't allow usernames to match taxonomy names.
   *
   * @param string $username The username.
   *
   * @return string The username.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function restrict_username($username)
  {
    if (isset(self::$taxonomies[$username]))
    {
      return '';
    }

    return $username;
  }

  /**
   * wp_redirect
   * Handle redirects of the edit tags form with piklist fields.
   *
   * @param string $location The redirect location.
   *
   * @return string The redirect location.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_redirect($location, $status)
  {
    $url = parse_url($location);

    if (isset($url['query']) && !empty($url['query']))
    {
      parse_str($url['query'], $url_defaults);

      if (stristr($url['path'], 'edit-tags.php') || stristr($url['path'], 'term.php')
          && isset($url_defaults['taxonomy'])
          && isset($url_defaults['message'])
          && $status == 302
          && isset($_POST)
          && isset($_POST['_wp_original_http_referer'])
          && (isset($_POST['action']) && $_POST['action'] == 'editedtag')
          && (isset($_POST['tag_ID']) && $_POST['tag_ID'] == $url_defaults['tag_ID'])
         )
      {
        $original_url = parse_url($_POST['_wp_original_http_referer']);

        parse_str($original_url['query'], $original_url_defaults);

        $wp_http_referer = 'edit-tags.php';
        $wp_http_referer = add_query_arg('taxonomy', $url_defaults['taxonomy'], $wp_http_referer);
        
        if (isset($original_url_defaults['post_type']))
        {
          $wp_http_referer = add_query_arg('post_type', $original_url_defaults['post_type'], $wp_http_referer);
        }
        
        $wp_http_referer = admin_url($wp_http_referer);

        $location = 'edit-tags.php';
        $location = add_query_arg('taxonomy', $url_defaults['taxonomy'], $location);
        $location = add_query_arg('action', 'edit', $location);
        $location = add_query_arg('message', $url_defaults['message'], $location);
        $location = add_query_arg('tag_ID', $url_defaults['tag_ID'], $location);
        $location = add_query_arg('wp_http_referer', urlencode($wp_http_referer), $location);
        $location = admin_url($location);
      }
    }

    return $location;
  }
}

if (!function_exists('add_term_meta'))
{
  /**
   * Add meta data field to a term.
   *
   * post meta data is called "Custom Fields" on the Administration Screen.
   *
   * @param int $term_id post ID.
   * @param string $meta_key Metadata name.
   * @param mixed $meta_value Metadata value.
   * @param bool $unique Optional, default is false. Whether the same key should not be added.
   * @return bool False for failure. True for success.
   */
  function add_term_meta($term_id, $meta_key, $meta_value, $unique = false)
  {
    return add_metadata('term', $term_id, $meta_key, $meta_value, $unique);
  }
}

if (!function_exists('delete_term_meta'))
{
  /**
   * Remove metadata matching criteria from a term.
   *
   * You can match based on the key, or key and value. Removing based on key and
   * value, will keep from removing duplicate metadata with the same key. It also
   * allows removing all metadata matching key, if needed.
   *
   * @param int $term_id term ID
   * @param string $meta_key Metadata name.
   * @param mixed $meta_value Optional. Metadata value.
   * @return bool False for failure. True for success.
   */
  function delete_term_meta($term_id, $meta_key, $meta_value = '')
  {
    return delete_metadata('term', $term_id, $meta_key, $meta_value);
  }
}

if (!function_exists('get_term_meta'))
{
  /**
   * Retrieve term meta field for a term.
   *
   * @param int $term_id post ID.
   * @param string $key Optional. The meta key to retrieve. By default, returns data for all keys.
   * @param bool $single Whether to return a single value.
   * @return mixed Will be an array if $single is false. Will be value of meta data field if $single is true.
   */
  function get_term_meta($term_id, $key = '', $single = false)
  {
    return get_metadata('term', $term_id, $key, $single);
  }
}

if (!function_exists('update_term_meta'))
{
  /**
   * Update term meta field based on term ID.
   *
   * Use the $prev_value parameter to differentiate between meta fields with the
   * same key and term ID.
   *
   * If the meta field for the term does not exist, it will be added.
   *
   * @param int $term_id post ID.
   * @param string $meta_key Metadata key.
   * @param mixed $meta_value Metadata value.
   * @param mixed $prev_value Optional. Previous value to check before removing.
   * @return bool False on failure, true if success.
   */
  function update_term_meta($term_id, $meta_key, $meta_value, $prev_value = '')
  {
    return update_metadata('term', $term_id, $meta_key, $meta_value, $prev_value);
  }
}

if (!function_exists('delete_term_meta_by_key'))
{
  /**
   * Delete everything from term meta matching meta key.
   *
   * @param string $term_meta_key Key to search for when deleting.
   * @return bool Whether the term meta key was deleted from the database
   */
  function delete_term_meta_by_key($term_meta_key)
  {
    return delete_metadata('term', null, $term_meta_key, '', true);
  }
}

if (!function_exists('get_term_custom'))
{
  /**
   * Retrieve all term meta fields, based on term ID.
   *
   * The term meta fields are retrieved from the cache where possible,
   * so the function is optimized to be called more than once.
   *
   * @param int $term_id post ID.
   * @return array
   */
  function get_term_custom($term_id = 0)
  {
    $term_id = absint($term_id);

    return !$term_id ? null : get_term_meta($term_id);
  }
}

if (!function_exists('get_term_custom_keys'))
{
  /**
   * Retrieve meta field names for a term.
   *
   * If there are no meta fields, then nothing (null) will be returned.
   *
   * @param int $term_id term ID
   * @return array|null Either array of the keys, or null if keys could not be retrieved.
   */
  function get_term_custom_keys($term_id = 0)
  {
    $custom = get_term_custom($term_id);

    if (!is_array($custom))
    {
      return;
    }

    if ($keys = array_keys($custom))
    {
      return $keys;
    }
  }
}

if (!function_exists('get_term_custom_values'))
{
  /**
   * Retrieve values for a custom term field.
   *
   * The parameters must not be considered optional. All of the term meta fields
   * will be retrieved and only the meta field key values returned.
   *
   * @param string $key Meta field key.
   * @param int $term_id post ID
   * @return array Meta field values.
   */
  function get_term_custom_values($key = '', $term_id = 0)
  {
    if (!$key)
    {
      return null;
    }

    $custom = get_term_custom($term_id);

    return isset($custom[$key]) ? $custom[$key] : null;
  }
}
