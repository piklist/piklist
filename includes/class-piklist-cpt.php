<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_CPT
 * Controls post type modifications and features.
 *
 * @package     Piklist
 * @subpackage  CPT
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_CPT
{
  /**
   * @var array Post Types registered with piklist_post_types
   * @access private
   */
  private static $post_types = array();

  /**
   * @var array Taxonomies registered with piklist_taxonomies
   * @access private
   */
  private static $taxonomies = array();

  /**
   * @var string Current taxonomy.
   * @access private
   */
  private static $taxonomy;

  /**
   * @var string Stores taxonomy filter meta boxes.
   * @access private
   */
  private static $taxonomy_filter_meta_box = array();

  /**
   * @var array Meta boxes locked when lock: true
   * @access private
   */
  private static $meta_boxes_locked = array();

  /**
   * @var array Meta boxes hidden
   * @access private
   */
  private static $meta_boxes_hidden = array();

  /**
   * @var array Builtin meta boxes.
   * @access private
   */
  private static $meta_boxes_builtin = array(
    'slug'
    ,'author'
    ,'revision'
    ,'pageparent'
    ,'comments'
    ,'commentstatus'
    ,'postcustom'
  );

  /**
   * @var array The data submitted via a filter type form.
   * @access private
   */
  private static $search_data = array();

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
    add_action('init', array('piklist_cpt', 'init'));
    add_action('pre_get_posts', array('piklist_cpt', 'pre_get_posts'), 100);
    add_action('edit_page_form', array('piklist_cpt', 'edit_form'));
    add_action('edit_form_advanced', array('piklist_cpt', 'edit_form'));
    add_action('restrict_manage_posts', array('piklist_cpt', 'taxonomy_filters_list_table'));
    add_action('admin_footer', array('piklist_cpt', 'quick_edit_post_statuses'));
    add_action('piklist_save_fields', array('piklist_cpt', 'save_fields'));

    add_filter('post_row_actions', array('piklist_cpt', 'post_row_actions'), 10, 2);
    add_filter('page_row_actions', array('piklist_cpt', 'post_row_actions'), 10, 2);
    add_filter('wp_insert_post_data', array('piklist_cpt', 'wp_insert_post_data'), 100, 2);
    add_filter('display_post_states', array('piklist_cpt', 'display_post_states'), 999);
    add_filter('piklist_assets_localize', array('piklist_cpt', 'assets_localize'));

    if (piklist_admin::is_post())
    {
      add_filter('is_protected_meta', array('piklist_cpt', 'is_protected_meta'), 100, 3);
    }
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
    self::register_taxonomies();
    self::register_post_types();
  }

  /**
   * edit_form
   * Appends piklist variables to the edit form if necessary.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function edit_form()
  {
    $fields = array(
      'post_id'
      ,'admin_hide_ui'
    );

    foreach ($fields as $field)
    {
      if (isset($_REQUEST[piklist::$prefix][$field]) && !empty($_REQUEST[piklist::$prefix][$field]))
      {
        piklist_form::render_field(array(
          'type' => 'hidden'
          ,'scope' => piklist::$prefix
          ,'field' => $field
          ,'value' => $_REQUEST[piklist::$prefix][$field]
        ));
      }
    }
  }

  /**
   * register_post_types
   * registers Post Types that use the piklist_post_types filter.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_post_types()
  {
    /**
     * piklist_post_types
     * Register Post Types with Piklist
     *
     * Allows for all custom Piklist parameters when registering a Post Type
     *
     * @param array $post_types
     *
     * @since 1.0
     */
    self::$post_types = apply_filters('piklist_post_types', self::$post_types);

    $check = array();

    foreach (self::$post_types as $post_type => &$configuration)
    {
      $configuration['supports'] = empty($configuration['supports']) ? array(false) : $configuration['supports'];

      register_post_type($post_type, $configuration);

      if (!isset($check[$post_type]) || !$check[$post_type])
      {
        $check[$post_type] = $configuration;
      }

      if (!empty($configuration['status']))
      {
        /**
         * piklist_post_type_statuses
         *
         * @since 1.0
         */
        $configuration['status'] = apply_filters('piklist_post_type_statuses', $configuration['status'], $post_type);

        foreach ($configuration['status'] as $status => &$status_data)
        {
          $status_data['label_count'] = _n_noop($status_data['label'] . ' <span class="count">(%s)</span>', $status_data['label'] . ' <span class="count">(%s)</span>');
          $status_data['capability_type'] = $post_type;

		  // Use WordPress defaults for register_post_status
		  // except 'show_in_admin_status_list' for backwards compatibility
          $status_data = wp_parse_args($status_data, array(
						'show_in_admin_status_list' => true
						,'public' => true
          ));

          $status_data = (object) $status_data;

          if ($status != 'draft')
          {
            register_post_status($status, $status_data);
          }
        }
      }

      if (!empty($configuration['hide_meta_box']) && is_array($configuration['hide_meta_box']))
      {
        foreach ($configuration['hide_meta_box'] as $meta_box)
        {
          if (!isset(self::$meta_boxes_hidden[$post_type]))
          {
            self::$meta_boxes_hidden[$post_type] = array();
          }
          array_push(self::$meta_boxes_hidden[$post_type], $meta_box . (in_array($meta_box, self::$meta_boxes_builtin) ? 'div' : null));
        }
      }

      add_action('admin_head', array('piklist_cpt', 'hide_meta_boxes'), 100);

      if (!empty($configuration['title']))
      {
        add_filter('enter_title_here', array('piklist_cpt', 'enter_title_here'));
      }

      if (!empty($configuration['page_icon']))
      {
        global $pagenow;

        if (in_array($pagenow, array('edit.php', 'post.php', 'post-new.php')) && !isset($_REQUEST['page']) && isset($_REQUEST['post_type']) && ($_REQUEST['post_type'] == $post_type))
        {
          piklist_admin::$page_icon = array(
            'page_id' => '.icon32.icon32-posts-' . $post_type
            ,'icon_url' => $configuration['page_icon']
          );
        }
      }

      if (!empty($configuration['hide_screen_options']))
      {
        add_filter('screen_options_show_screen', array('piklist_cpt', 'hide_screen_options'));
      }

      if (!empty($configuration['edit_columns']))
      {
        add_filter('manage_edit-' . $post_type . '_columns', array('piklist_cpt', 'manage_edit_columns'));
      }

      if (!empty($configuration['admin_body_class']))
      {
        add_filter('admin_body_class', array('piklist_cpt', 'admin_body_class'), 10000);
      }

      add_filter('post_updated_messages', array('piklist_cpt', 'post_updated_messages_filter'));

    }

    self::sort_post_statuses();

    self::flush_rewrite_rules(md5(serialize($check)), 'piklist_post_type_rules_flushed');
  }

  /**
   * register_taxonomies
   * registers Taxonomies that use the piklist_taxonomies filter.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_taxonomies()
  {
    global $wp_taxonomies, $pagenow;

    /**
     * Register Taxonomies with Piklist
     *
     * Allows for all custom Piklist parameters when registering a Taxonomy
     *
     * @param array $taxonomies
     *
     * @since 1.0
     */
    self::$taxonomies = apply_filters('piklist_taxonomies', self::$taxonomies);

    $check = array();

    foreach (self::$taxonomies as $taxonomy_name => $taxonomy)
    {
      $taxonomy['name'] = isset($taxonomy['name']) || is_numeric($taxonomy_name) ? $taxonomy['name'] : $taxonomy_name;

      $type = isset($taxonomy['object_type']) ? $taxonomy['object_type'] : $taxonomy['post_type'];

      if (!isset($taxonomy['update_count_callback']))
      {
        $taxonomy['update_count_callback'] = '_update_generic_term_count';
      }

      register_taxonomy($taxonomy['name'], $type, $taxonomy['configuration']);

      if (!isset($check[$taxonomy['name']]) || !$check[$taxonomy['name']])
      {
        $check[$taxonomy['name']] = $taxonomy;
      }

      if (isset($taxonomy['configuration']['hide_meta_box']) && !empty($taxonomy['configuration']['hide_meta_box']))
      {
        $object_types = is_array($type) ? $type : array($type);
        foreach ($object_types as $object_type)
        {
          if (!isset(self::$meta_boxes_hidden[$object_type]))
          {
            self::$meta_boxes_hidden[$object_type] = array();
          }
          array_push(self::$meta_boxes_hidden[$object_type], $taxonomy['configuration']['hierarchical'] ? $taxonomy['name'] . 'div' : 'tagsdiv-' . $taxonomy['name']);
        }
      }

      add_action('admin_head', array('piklist_cpt', 'hide_meta_boxes'), 100);

      if (isset($taxonomy['configuration']['page_icon']) && !empty($taxonomy['configuration']['page_icon']))
      {
        if ((in_array($pagenow, array('edit-tags.php', 'term.php'))) && ($_REQUEST['taxonomy'] == $taxonomy['name']))
        {
          piklist_admin::$page_icon = array(
            'page_id' => isset($taxonomy['object_type']) && $taxonomy['object_type'] == 'user' ? '#icon-users.icon32' : '#icon-edit.icon32'
            ,'icon_url' => $taxonomy['configuration']['page_icon']
          );
        }
      }

      if (isset($taxonomy['object_type']) && $taxonomy['object_type'] == 'user')
      {
        if (isset($taxonomy['configuration']['show_admin_column']) && $taxonomy['configuration']['show_admin_column'] == true)
        {
          piklist_cpt::$taxonomy = $taxonomy;

          add_filter('manage_users_columns', array('piklist_cpt', 'user_column_header'), 10);
          add_action('manage_users_custom_column', array('piklist_cpt', 'user_column_data'), 10, 3);
        }
      }

      if (isset($taxonomy['configuration']['meta_box_filter']) && $taxonomy['configuration']['meta_box_filter'] == '1')
      {
        if (in_array($pagenow, array('post.php', 'post-new.php')))
        {
          array_push(self::$taxonomy_filter_meta_box, $taxonomy);

          add_action('admin_head', array('piklist_cpt', 'taxonomy_filter_meta_box'));
        }
      }
    }

    self::flush_rewrite_rules(md5(serialize($check)), 'piklist_taxonomy_rules_flushed');
  }

  /**
   * taxonomy_filter_meta_box
   * Display taxonomy filter in meta boxes
   * taxonomy parameter: 'meta_box_filter' => true
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function taxonomy_filter_meta_box()
  {
    foreach (piklist_cpt::$taxonomy_filter_meta_box as $taxonomy)
    {
      piklist::render('shared/taxonomy-filter-meta-box', array(
        'name' => $taxonomy['name']
      ));
    }
  }

  /**
   * user_column_header
   * Adds taxonomy name to a column header on the Users list table.
   * User Taxonomy parameter: 'show_admin_column' => true
   *
   * @param array $columns The current columns to be used on the user column table.
   *
   * @return array User columns.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function user_column_header($columns)
  {
    $columns[piklist_cpt::$taxonomy['name']] = piklist_cpt::$taxonomy['configuration']['labels']['name'];

    return $columns;
  }

  /**
   * user_column_data
   * Adds term data to a column on the Users list table.
   *
   * @param $term_list array List of terms.
   * @param $column_name string The name of the column.
   * @param $value mixed The value of the column cell.
   *
   * @return string The list of terms
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function user_column_data($term_list, $column_name, $value)
  {
    $term = wp_get_object_terms($value, piklist_cpt::$taxonomy['name']);

    if (isset($term[0]))
    {
      $prefix = '';

      foreach ($term as $name => $value)
      {
        $term_list .= $prefix . $value->name;

        $prefix = ', ';
      }
    }
    else
    {
      $term_list = '';
    }

    return $term_list;
  }

  /**
   * flush_rewrite_rules
   * Flushes rewrite rules
   *
   * @param string $check The last configureation flushed.
   * @param string $option The option that stores the last configuration.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function flush_rewrite_rules($check, $option)
  {
    if ($check != get_option($option))
    {
      flush_rewrite_rules(false);

      update_option($option, $check);
    }
  }

  /**
   * hide_meta_boxes
   * Accepts an array of div ID's for meta boxes and hides them.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function hide_meta_boxes()
  {
    global $pagenow, $wp_meta_boxes, $typenow, $post;

    if (in_array($pagenow, array('post.php', 'post-new.php')))
    {
      foreach (array('normal', 'advanced', 'side') as $context)
      {
        foreach (array('high', 'core', 'default', 'low') as $priority)
        {
          if (isset($wp_meta_boxes[$typenow][$context][$priority]))
          {
            foreach ($wp_meta_boxes[$typenow][$context][$priority] as $meta_box => $data)
            {
              if ($meta_box != 'submitdiv' && isset(self::$meta_boxes_hidden[$typenow]) && in_array($meta_box, self::$meta_boxes_hidden[$typenow]))
              {
                unset($wp_meta_boxes[$typenow][$context][$priority][$meta_box]);
              }
            }
          }
        }
      }

      if (isset($wp_meta_boxes[$typenow]['side']['core']['submitdiv']))
      {
        $meta_boxes = array('submitdiv' => $wp_meta_boxes[$typenow]['side']['core']['submitdiv']);

        unset($wp_meta_boxes[$typenow]['side']['core']['submitdiv']);

        foreach ($wp_meta_boxes[$typenow]['side']['core'] as $id => $meta_box)
        {
          $meta_boxes[$id] = $meta_box;
        }

        $wp_meta_boxes[$typenow]['side']['core'] = $meta_boxes;
      }
    }
  }

  /**
   * get_post_statuses_for_type
   * return a list of post statuses for a post type.
   *
   * @param string $post_type set post type or leave empty for current.
   * @param bool $all set to true to return all post status data, or false to return simple array
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_post_statuses_for_type($post_type = null, $all = true)
  {
    global $post, $wp_post_statuses;

    if (empty($post_type) && $post)
    {
      $post_type = $post->post_type;
    }

    $default_statuses = array(
      'draft' => $wp_post_statuses['draft']
      ,'pending' => $wp_post_statuses['pending']
      ,'private' => $wp_post_statuses['private']
    );

    if (($post && $post->post_status == 'publish') || (!isset(self::$post_types[$post_type]['status']) || (isset(self::$post_types[$post_type]['status']) && isset(self::$post_types[$post_type]['status']['publish']))))
    {
      $default_statuses['publish'] = $wp_post_statuses['publish'];
    }

    $statuses = $post && isset(self::$post_types[$post_type]['status']) ? self::$post_types[$post_type]['status'] : $default_statuses;

    if ($all == false)
    {
      foreach ($statuses as $key => $value)
      {
        $statuses[$key] = $value->label;
      }
    }

    return $statuses;
  }

  /**
   * sort_post_statuses
   * Sorts custom post statuses.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sort_post_statuses()
  {
    global $wp_post_types, $wp_post_statuses, $typenow;

    $statuses = array();
    $_wp_post_statuses = array();
    $current_post_type = $typenow ? $typenow : (isset($_REQUEST['post_type']) ? $_REQUEST['post_type'] : null);

    foreach (self::$post_types as $post_type => $post_type_data)
    {
      if (isset($post_type_data['status']) && is_array($post_type_data['status']))
      {
        $statuses = $current_post_type == $post_type ? array_merge(array_keys($post_type_data['status']), $statuses) : array_merge($statuses, array_keys($post_type_data['status']));
      }
    }

    $statuses = array_reverse(array_unique(array_reverse($statuses)));

    foreach ($statuses as $status)
    {
      $_wp_post_statuses[$status] = $wp_post_statuses[$status];
    }

    foreach ($wp_post_statuses as $status => $data)
    {
      if (!isset($_wp_post_statuses[$status]))
      {
        $_wp_post_statuses = array_merge(array($status => $data), $_wp_post_statuses);
      }
    }

    $wp_post_statuses = $_wp_post_statuses;
  }

  /**
   * manage_edit_columns
   * Change the titles of the columns on the list posts screen.
   * Post Type parameter: 'edit_columns' => array()
   *
   * @param array $columns The edit table columns
   *
   * @return array The edit table columns.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function manage_edit_columns($columns)
  {
    $post_type = esc_attr($_REQUEST['post_type']);

    if (isset(self::$post_types[$post_type]))
    {
      return array_merge($columns, self::$post_types[$post_type]['edit_columns']);
    }

    return $columns;
  }

  /**
   * post_row_actions
   * Hide the post row actions that show up when you hover over a post in the list posts screen (e.g. edit, quick edit, trash, view). Post Type parameter: 'hide_post_row_actions' => array()
   *
   * @param array $actions The actions currently set for the row.
   * @param object $post The post object.
   *
   * @return array The updated actions.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function post_row_actions($actions, $post)
  {
    global $current_screen;

    if (isset($current_screen))
    {
      if (isset(self::$post_types[$current_screen->post_type]) && isset(self::$post_types[$current_screen->post_type]['hide_post_row_actions']))
      {
        foreach (self::$post_types[$current_screen->post_type]['hide_post_row_actions'] as $action)
        {
          unset($actions[$action == 'quick-edit' ? 'inline hide-if-no-js' : $action]);
        }
      }
    }

    return $actions;
  }

  /**
   * admin_body_class
   * Add a custom body class to your post type page in the admin. Allows you to easily target css.
   * Post Type parameter: 'admin_body_class' => array()
   *
   * @param string $classes The current list of classes for the admin body tag.
   *
   * @return string Classes to add to the admin body tag.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function admin_body_class($classes)
  {
    global $current_screen;

    if (!empty(self::$post_types[$current_screen->post_type]['admin_body_class']))
    {
      $admin_body_class = is_array(self::$post_types[$current_screen->post_type]['admin_body_class']) ? self::$post_types[$current_screen->post_type]['admin_body_class'] : array(self::$post_types[$current_screen->post_type]['admin_body_class']);

      foreach ($admin_body_class as $class)
      {
        $classes .= ' ' . $class;
      }
    }

    return $classes;
  }

  /**
   * display_post_states
   * Show post status states on the post type list table.
   *
   * @param array $states The current list of post states for the list table.
   *
   * @return array The updated list of post states.
   *
   * @access public
   * @static
   * @since 1.0.2
   */
  public static function display_post_states($states)
  {
    global $post;

    if (!empty($states))
    {
      $type = key($states);

      switch ($type)
      {
        case 'page_on_front':

        break;

        case 'Publish':
  
          $states = '';
  
        break;
  
        default:
  
        break;
      }

    }
    else
    {
      $current_status = $post->post_status;
      $post_statuses = self::get_post_statuses_for_type();
      $status = isset($post_statuses[$current_status]) ? $post_statuses[$current_status] : current($post_statuses);

      $states = !empty($status->label) ? $status->label : $status['label'];
      $states = $states == 'Published' ? array() : array($states);
    }

    return $states;
  }




  /**
   * quick_edit_post_statuses
   * Add custom post statuses field to quick edit area on list tables.
   *
   * @param string $column_name The column name.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function quick_edit_post_statuses($column_name)
  {
    global $pagenow;

    if ($pagenow == 'edit.php')
    {
      $post_statuses = self::get_post_statuses_for_type();

      if (!empty($post_statuses))
      {
        $choices = array();

        foreach ($post_statuses as $status => $data)
        {
          $choices[$status == 'auto-draft' ? 'draft' : $status] = is_array($data) ? $data['label'] : $data->label;
        }

        piklist::render('shared/list-table-post-statuses', array(
          'choices' => $choices
        ));
      }
    }
  }

  /**
   * post_updated_messages_filter
   * Customizes the messaging in the admin when post types are saved.
   * Messages are specific to the individual post type.
   *
   * @param array $messages Messages used for post actions
   *
   * @return array Messages used for post actions
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function post_updated_messages_filter($messages)
  {
    global $post, $post_ID;

    $post_type = get_post_type($post_ID);

    $obj = get_post_type_object($post_type);

    $singular = $obj->labels->singular_name;

    $permalink = get_permalink($post_ID);
    if (!$permalink) {
      $permalink = '';
    }

    $preview_post_link_html = $scheduled_post_link_html = $view_post_link_html = '';

    if (is_post_type_viewable($post_type)) {

      $preview_url = get_preview_post_link($post);
      $preview_post_link_html = sprintf( ' <a target="_blank" href="%1$s">%2$s</a>', esc_url($preview_url), __('Preview ' . $singular));

      $scheduled_post_link_html = sprintf( ' <a target="_blank" href="%1$s">%2$s</a>', esc_url($permalink), __('Preview ' . $singular));

      $view_post_link_html = sprintf( ' <a href="%1$s">%2$s</a>', esc_url($permalink), __('View '. $singular));

    }

    $scheduled_date = date_i18n(__( 'M j, Y @ H:i'), strtotime($post->post_date));

    $messages[$post_type] = array(
      0 => false  // Unused. Messages start at index 1.
      ,1 => __($singular . ' updated.') . $view_post_link_html
      ,2 => __('Custom field updated.')
      ,3 => __('Custom field deleted.')
      ,4 => __($singular . ' updated.')
      ,5 => isset($_REQUEST['revision']) ? sprintf( __($singular . ' restored to revision from %s'), wp_post_revision_title((int) $_REQUEST['revision'], false )) : false
      ,6 => __($singular . ' published.') . $view_post_link_html
      ,7 => __('Page saved.')
      ,8 => __($singular . ' submitted.') . $preview_post_link_html
      ,9 => sprintf( __($singular.' scheduled for: %s.'), '<strong>' . $scheduled_date . '</strong>') . $scheduled_post_link_html
      ,10 => __($singular.' draft updated.' ) . $preview_post_link_html
    );

    return $messages;
  }

  /**
   * enter_title_here
   * Customizes the placeholder text in the title post on the add new post screen.
   * Post Type parameter: 'title' => 'Enter my title'
   *
   * @param string $title The placeholder title for the title field on the Post edit screen.
   *
   * @return string The updated title.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function enter_title_here($title)
  {
    $post_type = get_post_type();

    return isset(self::$post_types[$post_type]['title']) ? self::$post_types[$post_type]['title'] : $title;
  }

  /**
   * hide_screen_options
   * Hide the screen options tab on the post edit screens.
   * Post Type parameter: 'hide_screen_options' => true
   *
   * @return bool Whether to hide teh screen options on the edit screen.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function hide_screen_options()
  {
    global $pagenow;

    if (in_array($pagenow, array('edit.php', 'post.php', 'post-new.php')))
    {
      $post_type = get_post_type();

      if (isset(self::$post_types[$post_type]['hide_screen_options']))
      {
        return self::$post_types[$post_type]['hide_screen_options'] ? false : true;
      }
      else
      {
        return true;
      }
    }
  }

  /**
   * get_post_statuses
   * Return a list of post statuses for an object type.
   *
   * @param string $object_type The object type for the object.
   *
   * @return array The post statuses
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_post_statuses($object_type = null)
  {
    global $wp_post_statuses;

    $status_list = array();
    $object_type = $object_type ? $object_type : get_post_type();

    foreach ($wp_post_statuses as $status)
    {
      if (isset($status->capability_type) && $status->capability_type == $object_type)
      {
        array_push($status_list, $status->name);
      }
    }

    return $status_list;
  }

  /**
   * wp_insert_post_data
   * Check whether or not to allow an empty post title on the save of a post.
   *
   * @param array $data The data for the post to be saved.
   * @param array $post_array The post object as an array.
   *
   * @return array The data for the post to be saved.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_insert_post_data($data, $post_array)
  {
    if (($data['post_status'] != 'auto-draft') && (($data['post_title'] == __('Auto Draft') || empty($data['post_title']))))
    {
      /**
       * piklist_empty_post_title
       * Filter the post title on save if empty.
       * Primarily used when registering a post type and the 'supports' parameter does not contain 'title'.
       *
       * @param array $post_array
       *
       * @since 1.0
       */
      $data['post_title'] = apply_filters('piklist_empty_post_title', $data['post_title'], $post_array);
    }

    return $data;
  }

  /**
   * pre_get_posts
   * Hooks into forms with the filter set to true, allowing modification of the main query being run on the page.
   *
   * @param object $query The WP_Query object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function pre_get_posts(&$query)
  {
    if ($query->is_main_query() && isset($_REQUEST) && (isset($_REQUEST[piklist::$prefix]['filter']) && strtolower($_REQUEST[piklist::$prefix]['filter']) == 'true') && isset($_REQUEST[piklist::$prefix]['fields']))
    {
      $args = array(
        'meta_query' => array()
        ,'tax_query' => array()
        ,'s' => array()
        ,'meta_relation' => false
        ,'taxonomy_relation' => false
      );

      $fields = get_transient(piklist::$prefix . $_REQUEST[piklist::$prefix]['fields']);

      self::$search_data = $_REQUEST;

      foreach (self::$search_data as $key => $values)
      {
        foreach ($values as $_key => $_values)
        {
          foreach ($_values as $_value)
          {
            $filter = substr($key, 0, 1) == piklist::$prefix ? substr($key, strlen(piklist::$prefix)) : $key;
            $_value = is_array($_value) ? array_filter($_value) : $_value;

            if (empty($_value))
            {
              break;
            }

            array_push($fields[$filter][$_key]['query'], array(
              'scope' => $filter
              ,'field' => $_key
            ));

            foreach ($fields[$filter][$_key]['query'] as $_query)
            {
              switch ($_query['scope'])
              {
                case 'post':

                  switch ($_query['field'])
                  {
                    case 'post_title':
                    case 'post_content':
                    case 'post_excerpt':

                      array_push($args['s'], $_value);

                    break;

                    case 'cat':
                    case 'tag_id':
                    case 'p':
                    case 'page_id':
                    case 'post_parent':
                    case 'posts_per_page':
                    case 'posts_per_archive_page':
                    case 'offset':
                    case 'page':
                    case 'paged':

                      $args[$_query['field']] = (int) $_value;

                    break;

                    case 'author':

                      $args[$_query['field']] = is_array($_values) ? implode(',', $_values) : $_values;

                    break;

                    case 'author_name':
                    case 'category_name':
                    case 'tag':
                    case 'post_password':
                    case 'perm':
                    case 'fields':
                    case 'order':
                    case 'orderby':

                      $args[$_query['field']] = $_value;

                    break;

                    case 'author__in':
                    case 'author__not_in':
                    case 'category__and':
                    case 'category__in':
                    case 'category__not_in':
                    case 'tag__and':
                    case 'tag__in':
                    case 'tag__not_in':
                    case 'tag_slug__and':
                    case 'tag_slug__in':
                    case 'post_parent__in':
                    case 'post_parent__not_in':
                    case 'post__in':
                    case 'post__not_in':
                    case 'post_status':
                    case 'post_type':

                      $args[$_query['field']] = is_array($_values) ? $_values : array($_values);

                    break;

                    case 'has_password':
                    case 'cache_results':
                    case 'update_post_meta_cache':
                    case 'update_post_term_cache':
                    case 'nopaging':
                    case 'ignore_sticky_posts':

                      $args[$_query['field']] = (bool) $_value;

                    break;
                  }

                break;

                case 'post_meta':

                  if (isset($_query['relation']))
                  {
                    $args['meta_query']['relation'] = strtoupper($_query['relation']);
                  }

                  if (isset($_query['scope_relation']))
                  {
                    $args['meta_relation'] = strtoupper($_query['scope_relation']);
                  }

                  if (!isset($_query['relation']) && !isset($_query['scope_relation']))
                  {
                    if (strstr($_query['field'], '__min'))
                    {
                      array_push($args['meta_query'], array(
                        'key' => str_replace('__min', '', $_query['field'])
                        ,'value' => array($_value, self::$search_data[$key][str_replace('__min', '__max', $_query['field'])])
                        ,'type' => 'NUMERIC'
                        ,'compare' => 'BETWEEN'
                      ));
                    }
                    elseif (!strstr($_query['field'], '__min') && !strstr($_query['field'], '__max'))
                    {
                      array_push($args['meta_query'], array(
                        'key' => $_query['field']
                        ,'value' => $_value
                        ,'type' => isset($_query['type']) ? $_query['type'] : 'CHAR'
                        ,'compare' => isset($_query['compare']) ? $_query['compare'] : '='
                      ));
                    }
                  }

                break;

                case 'taxonomy':

                  if (isset($_query['relation']))
                  {
                    $args['tax_query']['relation'] = strtoupper($_query['relation']);
                  }

                  if (isset($_query['scope_relation']))
                  {
                    $args['taxonomy_relation'] = strtoupper($_query['scope_relation']);
                  }

                  if (!isset($_query['relation']) && !isset($_query['scope_relation']))
                  {
                    array_push(
                      $args['tax_query']
                      ,array(
                        'taxonomy' => $_query['field']
                        ,'terms' => !is_array($_value) && strstr($_value, ',') ? explode(',', $_value) : $_value
                        ,'field' => isset($_query['attribute']) ? $_query['attribute'] : 'term_id'
                        ,'include_children' => isset($_query['include_children']) ? $_query['include_children'] : true
                        ,'operator' => isset($_query['operator']) ? $_query['operator'] : 'IN'
                      )
                    );
                  }

                break;
              }
            }
          }
        }
      }

      if (isset($args['meta_query']))
      {
        $args['meta_query'] = empty($query->query_vars['meta_query']) ? $args['meta_query'] : array_merge($query->query_vars['meta_query'], $args['meta_query']);
        $args['meta_query'] = array_map('unserialize', array_unique(array_map('serialize', $args['meta_query'])));;
      }

      if (isset($args['tax_query']))
      {
        $args['tax_query'] = empty($query->query_vars['tax_query']) ? $args['tax_query'] : array_merge($query->query_vars['tax_query'], $args['tax_query']);
        $args['tax_query'] = array_map('unserialize', array_unique(array_map('serialize', $args['tax_query'])));;
      }

      if (isset($args['s']))
      {
        $args['s'] = empty($query->query_vars['s']) ? $args['s'] : array_merge($query->query_vars['s'], $args['s']);
        $args['s'] = array_map('unserialize', array_unique(array_map('serialize', $args['s'])));
        $args['s'] = implode(' ', $args['s']);
      }

      foreach ($args as $var => $value)
      {
        $query->set($var, $value);
      }

      if (isset(self::$search_data[piklist::$prefix . 'post']))
      {
        add_filter('posts_search', array('piklist_cpt', 'posts_search'), 10, 2);
      }
    }
  }

  /**
   * posts_search
   * Adjusts the main query based on a form filter.
   *
   * @param object $search The search clause for the query.
   * @param object $query The WP_Query object.
   *
   * @return object The search clause for the query.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function posts_search($search, &$query)
  {
    global $wpdb;

    if ($query->is_main_query() && !empty(self::$search_data[piklist::$prefix . 'post']) && empty($query->query_vars['s']))
    {
      $n = !empty($query->query_vars['exact']) ? '' : '%';
      foreach (self::$search_data[piklist::$prefix . 'post'] as $field => $terms)
      {
        $search_terms = '';
        $search_or = '';
        $search_join = empty($search) ? '' : ' OR ';
        foreach ($terms as $term)
        {
          $term = function_exists('like_escape') ? like_escape($term) : $wpdb->esc_like($term);
          $search_terms .= "{$search_or}$wpdb->posts.$field LIKE '{$n}{$term}{$n}'";
          $search_or = ' OR ';
        }
        $search .= "{$search_join}$search_terms";
      }

      if (!empty($search))
      {
        $search = "AND ({$search})";
        if (!is_user_logged_in())
        {
          $search .= " AND $wpdb->posts.post_password = '' ";
        }
      }
    }

    return $search;
  }

  /**
   * taxonomy_filters_list_table
   * Adds a filter for a taxonomy on the list table. Currently only works for taxonomies associated with Post Types
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function taxonomy_filters_list_table()
  {
    global $pagenow;

		if (in_array($pagenow, array('edit.php', 'upload.php')))
    {
      $object_type = isset($_REQUEST['post_type']) ? esc_attr($_REQUEST['post_type']) : 'post';
			$object_type = $pagenow == 'upload.php' ? 'attachment' : $object_type;

      $taxonomies = get_object_taxonomies($object_type);

      foreach (self::$taxonomies as $taxonomy)
      {
        if (in_array($taxonomy['name'], $taxonomies))
        {
          if (isset($taxonomy['configuration']['list_table_filter']) && $taxonomy['configuration']['list_table_filter'] == '1')
          {
						piklist::render('shared/list-table-filter-taxonomies', array(
			      	'taxonomy_name' => $taxonomy['name']
			      ));
          }
        }
      }
    }
  }

  /**
   * detect_post_type
   * Attempts to detect the post type given the state of the system when called.
   *
   * @return mixed The current post type or false.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function detect_post_type($post_id = null)
  {
    global $typenow, $pagenow, $post;

    $post_type = false;

    if (piklist_admin::is_post())
    {
      if (is_null($post_id))
      {
        if ($post)
        {
          $post_id = $post->ID;
        }
        elseif (isset($_REQUEST['post']))
        {
          $post_id = (int) $_REQUEST['post'];
        }
      }

      $post_type = get_post_type($post_id);

      if (!$post_type)
      {
        if ($typenow)
        {
          $post_type = $typenow;
        }
        elseif (isset($_REQUEST['post_type']))
        {
          $post_type = esc_attr($_REQUEST['post_type']);
        }
        elseif ($pagenow == 'post-new.php')
        {
          $post_type = 'post';
        }
      }
    }

    return $post_type;
  }

  /**
   * save_fields
   * Save any rendered post_meta fields to an option to filter out of the custom fields meta box later.
   *
   * @param array $fields The rendered fields for the form.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function save_fields($fields)
  {
    if (array_key_exists('post_meta', $fields))
    {
      $meta_keys = array();

      foreach ($fields['post_meta'] as $post_meta => $field)
      {
      	$meta_key = $field['field'];

        if (!strstr($meta_key, ':') && !$field['display'] && $field['type'] != 'group')
        {
          array_push($meta_keys, $meta_key);
        }
      }

      if (!empty($meta_keys))
      {
        $saved_meta_keys = get_option('piklist_post_meta_keys');
        $saved_meta_keys = $saved_meta_keys ? $saved_meta_keys : array();
        $saved_meta_keys = array_merge($saved_meta_keys, $meta_keys);
        $saved_meta_keys = array_unique($saved_meta_keys);

        sort($saved_meta_keys);

        update_option('piklist_post_meta_keys', $saved_meta_keys, true);
      }
    }
  }

  /**
   * is_protected_meta
   * Remove fields from the default custom fields meta box
   *
   * @param bool $protected Whether the field is protected from the list.
   * @param string $meta_key The meta key.
   * @param string $meta_type The meta type.
   *
   * @return bool Whether the field is protected from the list.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function is_protected_meta($protected, $meta_key, $meta_type)
  {
    $saved_meta_keys = get_option('piklist_post_meta_keys', null);
    if (is_null($saved_meta_keys))
    {
      return $protected;
    }

    return in_array($meta_key, $saved_meta_keys) ? true : $protected;
  }

  /**
   * assets_localize
   * Add data to the local piklist variable
   *
   * @return array Current data.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function assets_localize($localize)
  {
    if (piklist_admin::is_post())
    {
      global $post;

      $localize['post'] = $post;
      $localize['post_statuses'] = self::get_post_statuses_for_type();
    }

    return $localize;
  }
}
