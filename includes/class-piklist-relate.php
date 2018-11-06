<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Relate
 * Adds object relationships to WordPress.
 *
 * @package     Piklist
 * @subpackage  Relate
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Relate
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
    add_action('init', array(__CLASS__, 'register_meta'));
    add_action('rest_api_init', array(__CLASS__, 'rest_api_init'));
    add_action('deleted_post', array(__CLASS__, 'deleted_post'));
    add_action('deleted_user', array(__CLASS__, 'deleted_user'));
    add_action('deleted_comment', array(__CLASS__, 'deleted_comment'));

    add_filter('posts_where', array(__CLASS__, 'posts_where'), 10, 2);
    add_filter('pre_user_query', array(__CLASS__, 'pre_user_query'));
    add_filter('comments_clauses', array(__CLASS__, 'comments_clauses'), 10, 2);
  }

  /**
   * register_meta
   * Register relate meta with WordPress.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_meta()
  {
    $object_types = array('post', 'user', 'comment');

    foreach($object_types as $object_type)
    {
      foreach($object_types as $type)
      {
        register_meta($object_type, '_' . piklist::$prefix . "relate_$type", array(__CLASS__, 'absint_callback'), '__return_true');
      }
    }
  }

  /**
   * deleted_post.
   * Removes post's relationships
   *
   * @param  integer $post_id
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function deleted_post($post_id)
  {
    self::delete_object_relationships($post_id, 'post');
  }

  /**
   * deleted_user.
   * Removes users' relationships
   *
   * @param  integer $user_id
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function deleted_user($user_id)
  {
    self::delete_object_relationships($user_id, 'user');
  }

  /**
   * deleted_comment.
   * Removes comment's relationships
   *
   * @param  integer $comment_id
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function deleted_comment($comment_id)
  {
    self::delete_object_relationships($comment_id, 'comment');
  }

  /**
   * absint_callback
   * Sanitizes relate meta registered in register_meta.
   *
   * @param mixed $value Value to get the absint value of
   *
   * @return integer
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function absint_callback($value)
  {
    return absint($value);
  }

  /**
   * check_legacy_relate
   * Check for legacy relate methods.
   *
   * @param object $query Query to check.
   * @param string $object_scope Scope of the object to check for.
   *
   * @access private
   * @static
   * @since 1.0
   */
  private static function check_legacy_relate(&$query, $object_scope)
  {
    $belongs = "{$object_scope}_belongs";
    $has = "{$object_scope}_has";
    $relate = "{$object_scope}_relate";

    if (isset($query->query_vars[$belongs]))
    {
      $relate_type = 'belongs';
      $id = $query->query_vars[$belongs];
      unset($query->query_vars[$belongs]);
    }
    elseif(isset($query->query_vars[$has]))
    {
      $relate_type = 'has';
      $id = $query->query_vars[$has];
      unset($query->query_vars[$has]);
    }

    if (isset($id))
    {
      $scope = isset($query->query_vars[$relate]) ? $query->query_vars[$relate] : $object_scope;
      $query->query_vars['relate_query'] = array(
        array(
          'scope' => $scope
          ,'relate' => $relate_type
          ,'id' => $id
        )
      );
    }

    return $query;
  }

  /**
   * rest_api_init.
   * Applies various hooks that should only occur during a REST API request
   */
  public static function rest_api_init()
  {
    // Add filters to allow relate parameters in post queries
    $post_types = get_post_types(array('show_in_rest' => true));

    foreach($post_types as $post_type)
    {
      add_filter("rest_{$post_type}_query", array(__CLASS__, 'rest_post_query'), 10, 2);
    }

    // Prepare query parameters
    add_filter("rest_query_var-post_has", array(__CLASS__, 'absint_callback'));
    add_filter("rest_query_var-post_belongs", array(__CLASS__, 'absint_callback'));
  }

  /**
   * rest_query.
   * Open up simple relate queries to the REST API
   * @param  array $args    Arguments intended for post query
   * @param  array $request The API request
   * @return array          Resulting post query arguments
   */
  public static function rest_post_query($args, $request) {
    if (isset($request['post_has']))
    {
      $args['post_has'] = $request['post_has'];
    }

    if (isset($request['post_belongs']))
    {
      $args['post_belongs'] = $request['post_belongs'];
    }

    return $args;
  }

  /**
   * pre_user_query
   * Filter query for relationship.
   *
   * @param object $query The query object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function pre_user_query($query)
  {
    self::check_legacy_relate($query, 'user');

    if (null !== ($relate_where = self::relate_query($query, 'user')))
    {
      $query->query_where .= ' ' . $relate_where;
    }
  }

  /**
   * comments_clauses
   * Filter query for relationship.
   *
   * @param array $clauses The query clauses.
   * @param object $query The query object.
   *
   * @return array The query clauses.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function comments_clauses($clauses, $query)
  {
    self::check_legacy_relate($query, 'comment');

    if (null !== ($relate_where = self::relate_query($query, 'comment')))
    {
      $clauses['where'] .= ' ' . $relate_where;
    }

    return $clauses;
  }

  /**
   * posts_where
   * Filter query for relationship.
   *
   * @param string $where The query where clause.
   * @param object $query The query object.
   *
   * @return string The where clause.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function posts_where($where, $query)
  {
    self::check_legacy_relate($query, 'post');

    if (null !== ($relate_where = self::relate_query($query, 'post')))
    {
      $where .= ' ' . $relate_where;
    }

    return $where;
  }

  /**
   * relate_query
   * Determine if there is a relationship and if there is return the necessary mysql clause.
   *
   * @param object $query The query object.
   * @param string $scope The scope of the query.
   *
   * @return mixed The resulting where clause or null if no relationship found.
   *
   * @access private
   * @static
   * @since 1.0
   */
  private static function relate_query($query, $scope = 'post')
  {
    if (empty($query->query_vars['relate_query']))
    {
      return null;
    }

    global $wpdb;

    switch ($scope)
    {
      case 'post':

        $table = $wpdb->posts;
        $column_id = 'ID';

      break;

      case 'user':

        $table = $wpdb->users;
        $column_id = 'ID';

      break;

      case 'comment':

        $table = $wpdb->comments;
        $column_id = 'comment_ID';

      break;
    }

    $ids = self::get_related_ids($query->query_vars['relate_query'], $scope);

    if (!empty($ids))
    {
      $ids =  implode(',', array_map('intval', $ids));
      return " AND {$table}.{$column_id} IN ($ids)";
    }
    else
    {
      // Make sure query returns empty
      return " AND 0";
    }
  }

  /**
   * get_related_ids
   * Recursively walks through relate_query to retrieve object ids that meet all conditions
   *
   * @param array   $relations  The relate_query arrays.
   * @param string  $main_scope The scope of the main query.
   *
   * @return array  The object ids that match all relate queries.
   *
   * @access private
   * @static
   * @since 1.0
   */
  private static function get_related_ids($relations, $main_scope)
  {
    $and = empty($relations['relation']) || ('or' !== strtolower($relations['relation']));
    $ids = $and ? null : array();

    foreach($relations as $relation) {
      if (is_array($relation) && isset($relation[0]))
      {
        // Is nested query
        $new_ids = self::get_related_ids($relation, $main_scope);
        $new_ids = null !== $new_ids ? $new_ids : array();
      }
      elseif(!(empty($relation['relate']) || empty($relation['id'])))
      {
        $scope = isset($relation['scope']) ? $relation['scope'] : $main_scope;
        if ('has' === strtolower($relation['relate']))
        {
          // TODO: Consider opening up to arrays of ids
          $new_ids = get_metadata($scope, $relation['id'], '_' . piklist::$prefix . 'relate_' . $main_scope);
        }
        else
        {
          $new_ids = self::query_object_ids($relation['id'], $scope, $main_scope);
          $new_ids = null !== $new_ids ? $new_ids : array();
        }
      }
      else
      {
        continue;
      }

      if ( null === $ids )
      {
        $ids = $new_ids;
      }
      else
      {
        $ids = $and ? array_intersect($ids, $new_ids) : array_unique(array_merge($ids, $new_ids));
      }

      if ($and && empty($ids))
      {
        return $ids;
      }
    }

    return $ids;
  }

  /**
   * query_object_ids
   * Query the related object ids.
   *
   * @param int $object_id The object id.
   * @param string $scope The scope of the query.
   * @param string $relate_scope The relate scope of the query.
   *
   * @return array The related object ids or null.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function query_object_ids($object_id, $scope, $relate)
  {
    return self::get_object_ids(array(
      'object_id' => $object_id
      ,'scope' => $scope
      ,'relate' => array(
        'scope' => $relate
      )
    ), true);
  }

  /**
   * get_object_ids
   * Get the related object ids.
   *
   * @param int $field The field object
   * @param bool $is_query Whether we are using this for a query
   *
   * @return array The related object ids or null.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_object_ids($field, $is_query = false)
  {
    global $wpdb;

    if (!$field['object_id'])
    {
      return null;
    }

    $scope = strpos($field['scope'], '_meta') ? strstr($field['scope'], '_meta', true) : $field['scope'];

    $meta_key = '_' . piklist::$prefix . 'relate_' . $scope;

    $field_ids = is_array($field['object_id']) ? implode(', ', $field['object_id']) : $field['object_id'];
    $not_field_ids = $scope == $field['relate']['scope'] ? $field_ids : '-1';

    $object_ids = null;

    switch ($field['relate']['scope'])
    {
      case 'post':
      case 'post_meta':

        $query = $wpdb->prepare("
          SELECT DISTINCT $wpdb->postmeta.post_id
          FROM $wpdb->posts, $wpdb->postmeta
          WHERE $wpdb->postmeta.meta_key = %s
            AND $wpdb->postmeta.meta_value IN ($field_ids)
            AND $wpdb->postmeta.post_id NOT IN ($not_field_ids)
            AND $wpdb->posts.ID = $wpdb->postmeta.post_id
            AND $wpdb->posts.post_type != %s
            AND $wpdb->posts.post_type != %s
        ", $meta_key, 'revision', 'trash');

        $object_ids = $wpdb->get_col($query);

        if (isset($field['relate']['query']) && $object_ids)
        {
          $relate_query = $field['relate']['query'];
          $relate_query['post__in'] = $object_ids;

          $query = new WP_Query($relate_query);

          $object_ids = piklist($query->posts, array('ID'));
        }

      break;

      case 'user':
      case 'user_meta':

        $query = $wpdb->prepare("
          SELECT DISTINCT $wpdb->usermeta.user_id
          FROM $wpdb->users, $wpdb->usermeta
          WHERE $wpdb->usermeta.meta_key = %s
            AND $wpdb->usermeta.meta_value IN ($field_ids)
            AND $wpdb->usermeta.user_id NOT IN ($not_field_ids)
            AND $wpdb->users.ID = $wpdb->usermeta.user_id
        ", $meta_key);

        $object_ids = $wpdb->get_col($query);

        if (isset($field['relate']['query']) && $object_ids)
        {
          $relate_query = $field['relate']['query'];
          $relate_query['include'] = $object_ids;

          $query = new WP_User_Query($relate_query);

          $object_ids = piklist($query->results, array('ID'));
        }

      break;

      case 'comment':
      case 'comment_meta':

        $query = $wpdb->prepare("
          SELECT DISTINCT $wpdb->commentmeta.comment_id
          FROM $wpdb->comments, $wpdb->commentmeta
          WHERE $wpdb->commentmeta.meta_key = %s
            AND $wpdb->commentmeta.meta_value IN ($field_ids)
            AND $wpdb->commentmeta.comment_id NOT IN ($not_field_ids)
            AND $wpdb->comments.comment_ID = $wpdb->commentmeta.comment_id
        ", $meta_key);

        $object_ids = $wpdb->get_col($query);

        if (isset($field['relate']['query']) && $object_ids)
        {
          $relate_query = $field['relate']['query'];

          $query = new WP_Comment_Query($field['relate']['query']);

          $_object_ids = array();

          foreach ($query->comments as $comment)
          {
            if (in_array($comment->ID, $object_ids))
            {
              array_push($_object_ids, $comment->ID);
            }
          }

          $object_ids = $_object_ids;
        }

      break;
    }

    if (!$is_query && $object_ids && is_array($object_ids) && count($object_ids) == 1)
    {
      $object_ids = current($object_ids);
    }

    return $object_ids ? $object_ids : null;
  }

  /**
   * relate_field
   * Determine the default object id to relate to a field.
   *
   * @param array $field The field object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function relate_field($field)
  {
    global $wpdb;

    if (!isset($field['relate']) || !isset($field['relate']['scope']) || $field['object_id'] != $field['relate_to'] || $field['new'])
    {
      return $field;
    }

    // Get the id of what we are relating the field to
    if (!isset($field['relate']['field']))
    {
      switch ($field['relate']['scope'])
      {
        case 'post':
        case 'post_meta':

          $field['relate_to'] = piklist_admin::is_post();

        break;

        case 'user':
        case 'user_meta':

          $field['relate_to'] = piklist_admin::is_user();

        break;

        case 'comment':
        case 'comment_meta':

          $field['relate_to'] = piklist_admin::is_comment();

        break;
      }
    }

    $object_ids = self::get_object_ids($field);

    if (substr($field['field'], 0, strlen('_' . piklist::$prefix . 'relate_')) == '_' . piklist::$prefix . 'relate_')
    {
      if ( isset($field['choices']) && is_array($field['choices']) )
      {
        // Filter ids by choices
        $field['value'] = array();
        foreach((array) $object_ids as $id)
        {
          if ( isset($field['choices'][$id]) )
          {
            array_push($field['value'], $id);
          }
        }
      } else {
        $field['value'] = $object_ids;
      }
    }
    elseif (is_null($field['object_id']) || (!is_null($field['object_id']) && !is_null($object_ids) && $field['object_id'] != $object_ids))
    {
      $field['object_id'] = $object_ids;
    }
    elseif ($object_ids == $field['object_id'] || $field['object_id'] == $field['relate_to'])
    {
      $field['object_id'] = null;
    }

    return $field;
  }

  /**
   * get_object_data.
   * Converts object classes to standard array or sanitizes array
   *
   * @param  wp_post|wp_user|wp_comment|array $args class to convert or array to sanitize
   * @return array       object array with id and scope
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_object_data($args)
  {
    if ( is_object($args) ) {
      switch( strtolower(get_class($args)) ) {
        case 'wp_post':
          return array('id' => $args->ID, 'scope' => 'post');
        case 'wp_user':
          return array('id' => $args->ID, 'scope' => 'user');
        case 'wp_comment':
          return array('id' => $args->ID, 'scope' => 'comment');
      }
    }

    if ( isset($args['scope']) )
    {
      $args['scope'] = strtolower($args['scope']);
    }

    return $args;
  }

  /**
   * relate_objects.
   * Relate two objects. Object may either by class or array with id and scope
   *
   * @param  wp_post|wp_user|wp_comment|array $owner Object that will own the other
   * @param  wp_post|wp_user|wp_comment|array $has   Object that will belong to the other
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function relate_objects($owner, $has)
  {
    global $wpdb;

    $owner = self::get_object_data($owner);
    $has = self::get_object_data($has);

    $key = '_' . piklist::$prefix . 'relate_' . $owner['scope'];
    $meta_table = $has['scope'] . 'meta';

    // Make sure relationship doesn't already exist
    $check = $wpdb->get_var($wpdb->prepare("
      SELECT 1 FROM {$wpdb->$meta_table}
        WHERE %s = %d
          AND meta_key = %s
          AND meta_value = %d
    ", "{$has['scope']}_id", $has['id'], $key, $owner['id'] ));

    if ( empty($check) )
    {
      add_metadata($has['scope'], $has['id'], $key, $owner['id']);
    }
  }

  /**
   * unrelate_objects.
   * Deletes the relatonship between two objects
   *
   * @param  wp_post|wp_user|wp_comment|array $owner Object that owns the other
   * @param  wp_post|wp_user|wp_comment|array $has   Object that belongs to the other
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function unrelate_objects($owner, $has)
  {
    $owner = self::get_object_data($owner);
    $has = self::get_object_data($has);

    $key = '_' . piklist::$prefix . 'relate_' . strtolower($owner['scope']);

    delete_metadata($has['scope'], $has['id'], $key, $owner['id']);
  }

  /**
   * delete_object_relationships.
   * Delete all relationship meta for object.
   *
   * @param  integer  $id     Object id
   * @param  string   $scope  Object scope (e.g. 'post')
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function delete_object_relationships($id, $scope)
  {
    $key = '_' . piklist::$prefix . "relate_$scope";
    foreach(array('post', 'user', 'comment') as $meta_type)
    {
      delete_metadata($meta_type, null, $key, $id, true);
    }
  }
}
