<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_WordPress
 * Modifications and upgrades to WordPress, most of these are fixes for some of the inconsistencies in WordPress.
 *
 * @package     Piklist
 * @subpackage  WordPress
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_WordPress
{
  /**
   * @var string The user query relation.
   * @access public
   */
  private static $user_query_role_relation = 'AND';

  /**
   * @var string The primary type of table.
   * @access public
   */
  private static $primary_type = array();

  /**
   * @var string The primary ids based on type.
   * @access public
   */
  private static $primary_ids = array();

  /**
   * @var string The primary table based on type.
   * @access public
   */
  private static $primary_table;

  /**
   * @var string The primary id column name based on type.
   * @access public
   */
  private static $primary_id_column;

  /**
   * @var string The meta table based on type.
   * @access public
   */
  private static $meta_table;

  /**
   * @var string The meta table id column name based on type.
   * @access public
   */
  private static $meta_id_column;

  /**
   * @var string The meta key used for orderby if applicable.
   * @access public
   */
  private static $meta_key;

  /**
   * @var string The order method for the meta query if the orderby is for meta.
   * @access public
   */
  private static $meta_order = 'ASC';

  /**
   * @var string The orderby method for the meta table.
   * @access public
   */
  private static $meta_orderby;

  /**
   * @var string The meta order field to use.
   * @access public
   */
  private static $meta_order_field = array();

  /**
   * @var string The capabilities meta key.
   * @access public
   */
  private static $capabilities_meta_key;

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
    add_action('pre_user_query', array('piklist_wordpress', 'pre_user_query'));
    add_action('posts_where', array('piklist_wordpress', 'relation_taxonomy'));
    add_action('wp_scheduled_delete', array('piklist_wordpress', 'garbage_collection'));
    add_action('pre_get_posts', array('piklist_wordpress', 'pre_get_posts'));
    add_action('piklist_pre_render_workflow', array('piklist_wordpress', 'pre_render_workflow'));

    add_filter('piklist_part_data', array('piklist_wordpress', 'part_data'), 10, 2);
    add_filter('piklist_part_data_parameter', array('piklist_wordpress', 'piklist_part_data_parameter'), 10, 2);
    add_filter('get_meta_sql', array('piklist_wordpress', 'get_meta_sql'), 101, 6);
  }

  /**
   * garbage_collection
   * Deletes all expired transients from the options table.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function garbage_collection()
  {
    global $wpdb, $_wp_using_ext_object_cache;

    if (!$_wp_using_ext_object_cache)
    {
      $expired_transients = $wpdb->get_col("SELECT option_name FROM $wpdb->options WHERE option_name LIKE '_transient_timeout%' AND option_value < " . (isset($_SERVER['REQUEST_TIME']) ? (int) $_SERVER['REQUEST_TIME'] : time()));

      foreach ($expired_transients as $transient)
      {
        delete_transient(str_replace('_transient_timeout_', '', $transient));
      }
    }
  }

  /**
   * get_meta_sql
   * Seperate meta queries instead of using a table JOIN. https://core.trac.wordpress.org/ticket/30044
   *
   * @param array $sql The current constructed sql queries.
   * @param object $query The query object.
   * @param string $type The type for the meta query.
   * @param string $primary_table The primary table for the query.
   * @param string $primary_id_column The name of the primary id column.
   * @param string $context The context of the query.
   * @param string $parent_relation The relation between parent queries.
   * @param int $depth The current depth of the query.
   *
   * @return array Updated sql for the meta query.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_meta_sql($sql, $query, $type, $primary_table, $primary_id_column, $context, $parent_relation = null, $depth = 0)
  {
    /**
     * piklist_get_meta_sql
     *
     * @since 1.0
     */
    if (piklist::get_settings('piklist_core', 'meta_queries') !== 'true' || !apply_filters('piklist_get_meta_sql', true, $sql, $query, $type, $primary_table, $primary_id_column, $context, $parent_relation, $depth))
    {
      return $sql;
    }

    self::$primary_type = $type;
    self::$primary_table = $primary_table;
    self::$primary_id_column = $primary_id_column;
    self::$meta_table = substr($primary_table, 0, strlen($primary_table) - 1) . 'meta';
    self::$meta_id_column = self::$primary_type . '_id';
    self::$meta_order_field = array();

    if (is_null($parent_relation))
    {
      $parent_relation = get_query_var('meta_relation') ? strtoupper(get_query_var('meta_relation')) : 'AND';
    }

    return self::get_sql_for_query($query, 0, $parent_relation);
  }

  /**
   * pre_get_posts
   * Get the order parameter if it has to do with meta
   *
   * @param object $query The current query object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function pre_get_posts(&$query)
  {
    if (isset($query->query_vars['orderby']))
    {
      if (in_array($query->query_vars['orderby'], array('meta_value', 'meta_value_num')) && !is_null($query->query_vars['order']))
      {
        self::$meta_order = $query->query_vars['order'];
      }

      if (!is_null($query->query_vars['meta_key']))
      {
        self::$meta_key = $query->query_vars['meta_key'];
      }
    }
  }

  /**
   * pre_user_query
   * Adds the ability to search for users by multiple roles.
   *
   * @param object $query The current query object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function pre_user_query(&$query)
  {
    global $wpdb;

    $query->query_fields = 'DISTINCT ' . $query->query_fields;

    if (isset($query->query_vars['roles']) && is_array($query->query_vars['roles']))
    {
      if (isset($query->query_vars['relation']) && in_array(strtolower($query->query_vars['relation']), array('or', 'and')))
      {
        self::$user_query_role_relation = $query->query_vars['relation'];
      }

      self::$capabilities_meta_key = $wpdb->get_blog_prefix($query->query_vars['blog_id']) . 'capabilities';

      if (is_array($query->query_vars['meta_query']))
      {
        foreach ($query->query_vars['meta_query'] as $index => $meta_query)
        {
          if ($meta_query['key'] == $capabilities_meta_key)
          {
            unset($query->query_vars['meta_query'][$index]);
          }
        }
      }

      $query->query_vars['meta_query'] = is_array($query->query_vars['meta_query']) ? $query->query_vars['meta_query'] : array();

      foreach ($query->query_vars['roles'] as $role)
      {
        array_push($query->query_vars['meta_query'], array(
          'key' => self::$capabilities_meta_key
          ,'value' => '"' . $role . '"'
          ,'compare' => 'like'
        ));
      }

      $query->query_vars['role'] = null;

      remove_action('pre_user_query', array('piklist_wordpress', 'pre_user_query'));

      $query->prepare_query();

      add_action('pre_user_query', array('piklist_wordpress', 'pre_user_query'));
    }
  }

  /**
   * get_sql_for_query
   * Generates the seperate queries for get_meta_sql.
   *
   * @param object $query The current query object.
   * @param int $depth The current depth of the query.
   * @param string $parent_relation The relation between parent queries.
   *
   * @return array The generated sql queries.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_sql_for_query($query, $depth = 0, $parent_relation = 'AND')
  {
    global $wpdb, $wp_query, $wp_version;

    $sql_chunks = array(
      'join' => array()
      ,'where' => array()
    );

    $sql = array(
      'join' => ''
      ,'where' => ''
    );

    $sql_nested = false;

    $indent = '';
    for ($i = 0; $i < $depth; $i++)
    {
      $indent .= "  ";
    }

    if (isset($query['relation']) && in_array(strtoupper($query['relation']), array('AND', 'OR')))
    {
      $relation = strtoupper($query['relation']);
    }
    elseif ($depth == 0)
    {
      $backtrace = debug_backtrace();
      foreach ($backtrace as $callee)
      {
        if ((isset($callee['class']) && $callee['class'] === 'WP_Meta_Query') && isset($callee['object']->relation))
        {
          $relation = $callee['object']->relation;
          break;
        }
        else
        {
          $relation = 'AND';
        }
      }
    }
    else
    {
      $relation = 'AND';
    }

    $meta_key_order_by = false;

    self::$primary_ids = array();
    foreach ($query as $key => $clause)
    {
      if (is_array($clause))
      {
        if (self::is_first_order_clause($clause))
        {
          if (isset($clause['compare']))
          {
            $meta_compare = strtoupper($clause['compare']);
          }
          else
          {
            $meta_compare = isset($clause['value']) && is_array($clause['value']) ? 'IN' : '=';
          }

          if (!in_array($meta_compare, array(
            '=', '!=', '>', '>=', '<', '<=',
            'LIKE', 'NOT LIKE',
            'IN', 'NOT IN',
            'BETWEEN', 'NOT BETWEEN',
            'EXISTS', 'NOT EXISTS',
            'REGEXP', 'NOT REGEXP', 'RLIKE'
          )))
          {
            $meta_compare = '=';
          }

          $meta_order_string =
          $meta_orderby_string = '';

          $meta_key_string = '';
          if (array_key_exists('key', $clause))
          {
            $clause['key'] = trim($clause['key']);

            $meta_key_string = $wpdb->prepare("meta_key = %s", trim($clause['key']));

            $meta_order_string = $clause['key'] == self::$meta_key ? self::$meta_order : null;
            $meta_orderby_string = "ORDER BY meta_value " . $meta_order_string;

            if (self::$capabilities_meta_key === $key)
            {
              $relation = 'AND';
            }
          }

          $meta_value_string = '';
          if (array_key_exists('value', $clause))
          {
            $meta_value = $clause['value'];
            $meta_type = self::get_cast_for_type(isset($clause['type']) ? $clause['type'] : '');

            if (in_array($meta_compare, array('IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN')))
            {
              if (!is_array($meta_value))
              {
                $meta_value = preg_split('/[,\s]+/', $meta_value);
              }
            }
            else
            {
              $meta_value = trim($meta_value);
            }

            if ('IN' == substr($meta_compare, -2))
            {
              $meta_value_string = '(' . substr(str_repeat(',%s', count($meta_value)), 1) . ')';
            }
            elseif ('BETWEEN' == substr($meta_compare, -7))
            {
              $meta_value = array_slice($meta_value, 0, 2);
              $meta_value_string = '%s AND %s';
            }
            elseif ('LIKE' == $meta_compare || 'NOT LIKE' == $meta_compare)
            {
              $meta_value = '%' . (version_compare($wp_version, '4.0', '<') && function_exists('like_escape') ? like_escape($meta_value) : $wpdb->esc_like($meta_value)) . '%';
              $meta_value_string = '%s';
            }
            else
            {
              $meta_value_string = '%s';
            }

            if ($meta_value || (!$meta_value && in_array($meta_compare, array('=', '!='))))
            {
              $meta_value_string = $wpdb->prepare("CAST(meta_value AS {$meta_type}) {$meta_compare} {$meta_value_string}", $meta_value);
            }

            $meta_orderby_string = "ORDER BY CAST(meta_value AS {$meta_type}) " . $meta_order_string;
          }

          switch (self::$primary_type)
          {
            case 'post':

              add_filter('posts_where', array('piklist_wordpress', 'where'), 10, 2);
              add_filter('posts_where_request', array('piklist_wordpress', 'where'), 10, 2);
              add_filter('posts_orderby', array('piklist_wordpress', 'orderby'), 10, 2);
              add_filter('posts_orderby_request', array('piklist_wordpress', 'orderby'), 10, 2);

            break;
          }

          $meta_compare_string = $meta_key_string;

          if ($meta_value_string != '')
          {
            $meta_compare_string .= ($meta_compare_string != '' ? ' AND ' : '') . $meta_value_string;
          }
          elseif ('NOT EXISTS' !== $meta_compare)
          {
            $meta_key_order_by = $meta_key_string;
          }

          if ('AND' == $relation && !empty(self::$primary_ids))
          {
            $meta_compare_string .= " $relation " . self::$meta_id_column . " IN (" . implode(',', array_map('intval', self::$primary_ids)) . ")";
          }

          if (!empty($meta_compare_string))
          {
            $meta_query = "SELECT " . self::$meta_id_column . " FROM " . self::$meta_table . " WHERE " . $meta_compare_string . " " . $meta_orderby_string;

            if ('NOT EXISTS' === $meta_compare)
            {
              $meta_query = "SELECT DISTINCT " . self::$meta_id_column . " FROM " . self::$meta_table . " WHERE " . self::$meta_id_column . " NOT IN (" . $meta_query . ") " . $meta_orderby_string;
            }

            $_primary_ids = $wpdb->get_col($meta_query);

            if ($_primary_ids)
            {
              if ($meta_orderby_string != '' && empty(self::$meta_order_field))
              {
                self::$meta_order_field = $_primary_ids;
              }

              self::$primary_ids = ('AND' == $relation && !empty(self::$primary_ids)) ? array_intersect(self::$primary_ids, $_primary_ids) : array_merge(self::$primary_ids, $_primary_ids);
            }
            elseif ('AND' == $relation)
            {
              self::$primary_ids = array();

              break;
            }
          }
        }
        else
        {
          $sql_nested = $depth == 0;

          $clause_sql = self::get_sql_for_query($clause, $depth + 1, $relation);

          if (!in_array($clause_sql['where'], $sql_chunks['where']))
          {
            $sql_chunks['where'][] = $clause_sql['where'];
          }

          $sql_chunks['join'][] = $clause_sql['join'];
        }
      }
    }

    self::$primary_ids = array_unique(self::$primary_ids);

    $sql_chunks['where'][] = !empty(self::$primary_ids) ? " " . self::$primary_table . "." . self::$primary_id_column . " IN (" . implode(',', array_map('intval', self::$primary_ids)) . ") " : ' 0';

    if (!empty($sql_chunks['where']))
    {
      $sql['join'] = " INNER JOIN " . self::$meta_table . " ON " . self::$primary_table . "." . self::$primary_id_column . " = " . self::$meta_table . "." . self::$meta_id_column;
      $sql['where'] = ($depth == 0 ? " $parent_relation " : null) . ($sql_nested ? "(" : null) . $indent . implode($indent . $relation . $indent, $sql_chunks['where']) . $indent . ($sql_nested ? ")" : null);

      if ($meta_key_order_by && $depth == 0)
      {
        self::$meta_orderby = " AND (" . self::$meta_table . "." . $meta_key_order_by . ") ";
        $sql['where'] = $sql['where'] . self::$meta_orderby;
      }

      if ($depth == 0)
      {
        switch (self::$primary_type)
        {
          case 'post':

            add_filter('posts_distinct', array('piklist_wordpress', 'distinct'), 10, 2);
            add_filter('posts_distinct_request', array('piklist_wordpress', 'distinct'), 10, 2);

          break;
        }
      }
    }
    
    return $sql;
  }

  /**
   * where
   * Updates the where clause based on how the seperate queries are constructed.
   *
   * @param string $where The current where clause.
   * @param object $query The current query object.
   *
   * @return string The new where clause.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function where($where, $query)
  {
    remove_filter('posts_where', array('piklist_wordpress', 'where'), 10);
    remove_filter('posts_where_request', array('piklist_wordpress', 'where'), 10);

    if (!empty(self::$meta_orderby) && (!isset($query->query_vars['orderby']) || (in_array($query->query_vars['orderby'], array('meta_value', 'meta_value_num')))))
    {
      $where = str_replace(self::$meta_orderby, '', $where);
    }

    return $where;
  }

  /**
   * orderby
   * Updates the orderby clause based on how the seperate queries are constructed.
   *
   * @param string $orderby The current orderby clause.
   * @param object $query The current query object.
   *
   * @return string The new orderby clause.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function orderby($orderby, $query)
  {
    remove_filter('posts_orderby', array('piklist_wordpress', 'orderby'), 10);
    remove_filter('posts_orderby_request', array('piklist_wordpress', 'orderby'), 10);

    if (isset($query->query_vars['orderby']) && !isset($query->query_vars['meta_type']) && in_array($query->query_vars['orderby'], array('meta_value', 'meta_value_num')))
    {
      $orderby = !empty(self::$meta_order_field) ? "FIELD(" . self::$primary_table . "." . self::$primary_id_column . "," . implode(',', self::$meta_order_field) . ")" : null;
    }

    return $orderby;
  }

  /**
   * distinct
   * Forces the query to be distinct when modifed.
   *
   * @param string $distinct The current distinct clause.
   * @param object $query The current query object.
   *
   * @return
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function distinct($distinct, $query)
  {
    remove_filter('posts_distinct', array('piklist_wordpress', 'distinct'), 10);
    remove_filter('posts_distinct_request', array('piklist_wordpress', 'distinct'), 10);

    return 'DISTINCT';
  }

  /**
   * relation_taxonomy
   * Applies a taxonomy relation.
   *
   * @param string $where The current where clause.
   *
   * @return string The new where clause.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function relation_taxonomy($where)
  {
    global $wpdb;

    $taxonomy_relation = get_query_var('taxonomy_relation');

    if ($taxonomy_relation)
    {
      $where = str_replace(' AND ( ' . "\n" . '  ' . $wpdb->term_relationships . '.', ' ' . $taxonomy_relation . ' ( ' . "\n" . '  ' . $wpdb->term_relationships . '.', $where);
    }

    return $where;
  }

  /* --------------------------------------------------------------------------------------------------------------
    Pulled from wp-includes/meta.php with no changes
  -------------------------------------------------------------------------------------------------------------- */

  /**
   * Determine whether a query clause is first-order.
   *
   * A first-order meta query clause is one that has either a 'key' or
   * a 'value' array key.
   *
   * @since 4.1.0
   * @access protected
   *
   * @param array $query Meta query arguments.
   * @return bool Whether the query clause is a first-order clause.
   */
  protected static function is_first_order_clause($query)
  {
    return isset($query['key']) || isset($query['value']);
  }

  /**
   * Return the appropriate alias for the given meta type if applicable.
   *
   * @since 3.7.0
   * @access public
   *
   * @param string $type MySQL type to cast meta_value.
   * @return string MySQL type.
   */
  protected static function get_cast_for_type($type = '')
  {
    if (empty($type))
    {
      return 'CHAR';
    }

    $meta_type = strtoupper($type);

    if (!preg_match('/^(?:BINARY|CHAR|DATE|DATETIME|SIGNED|UNSIGNED|TIME|NUMERIC(?:\(\d+(?:,\s?\d+)?\))?|DECIMAL(?:\(\d+(?:,\s?\d+)?\))?)$/', $meta_type))
    {
      return 'CHAR';
    }

    if ('NUMERIC' == $meta_type)
    {
      $meta_type = 'SIGNED';
    }

    return $meta_type;
  }

  /**
   * pre_render_workflow
   * Handle the poorly built admin forms when being extended with Workflows.
   *
   * @param string $type The active tab.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function pre_render_workflow($active_tab)
  {
    global $pagenow, $typenow;
    
    if (isset($active_tab['data']['default_form']) && !$active_tab['data']['default_form'])
    {
      $type = null;

      if (in_array($pagenow, array('user-edit.php', 'profile.php')))
      {
        $type = 'user';
      }
      elseif ($pagenow == 'post.php' && $typenow == 'attachment')
      {
        $type = 'media';
      }
      elseif (in_array($pagenow, array('edit-tags.php', 'term.php')))
      {
        $type = 'term';
      }

      if ($type)
      {
        piklist::render('shared/wordpress-form-hide', array(
          'type' => $type
        ));
      }
    }
  }

  /**
   * part_data
   * Adds tab to all part types for easy association
   *
   * @param array $data The part object.
   * @param string $folder The folder name.
   *
   * @return array The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function part_data($data, $folder)
  {
    if ($folder == 'workflows')
    {
      $data['default_form'] = 'Default Form';
    }

    return $data;
  }
  
  /**
   * piklist_part_data_parameter
   * Checks the default form variable and casts its value
   *
   * @param mixed $value The value.
   * @param string $parameter The parameter name.
   *
   * @return mixed The value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function piklist_part_data_parameter($value, $parameter)
  {
    if ($parameter == 'default_form')
    {
      return $value === true;
    }

    return $value;
  }
}
