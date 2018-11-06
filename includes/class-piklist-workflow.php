<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Workflow
 * Manages the addition and usage of Workflow Tabs.
 *
 * @package     Piklist
 * @subpackage  Workflow
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Workflow
{
  /**
   * @var bool Stores the active workflow.
   * @access private
   */
  private static $workflow = false;

  /**
   * @var bool Are we rendering a workflow directly.
   * @access private
   */
  private static $rendering = false;

  /**
   * @var array Stores all registered workflows.
   * @access private
   */
  private static $workflows = array();

  /**
   * @var array Stores all registered workflows that belong to a tab in a workflow.
   * @access private
   */
  private static $sub_workflows = array();

  /**
   * @var array A list of hooks where workflows can be inserted.
   * @access private
   */
  private static $after_positions = array();

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
    add_filter('redirect_post_location', array('piklist_workflow', 'redirect'), 10, 2);
    add_filter('wp_redirect', array('piklist_workflow', 'redirect'), 10, 2);
    add_filter('piklist_part_process_callback', array('piklist_workflow', 'part_process_callback'), 100, 2);
    add_filter('piklist_part_data', array('piklist_workflow', 'part_data'), 10, 2);
    add_filter('admin_body_class', array('piklist_workflow', 'admin_body_class'));
    add_filter('piklist_validate_part_parameter_skip', array('piklist_workflow', 'validate_part_parameter_skip'), 10, 3);
    add_action('init', array('piklist_workflow', 'register_workflows'), 100);
    add_action('piklist_parts_processed-workflows', array('piklist_workflow', 'detect_workflow'), 100);

    add_shortcode('piklist_workflow', array('piklist_workflow', 'shortcode'));
  }

  /**
   * redirect
   * Handle redirects for workflow pages.
   *
   * @param string $location The location being redirected to.
   * @param int $post_id The post id in the redirect.
   *
   * @return string The new location to redirect to.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function redirect($location, $post_id)
  {
    if (isset($_REQUEST['_wp_http_referer']))
    {
      $url = parse_url($_REQUEST['_wp_http_referer']);

      if (isset($url['query']))
      {
        parse_str($url['query'], $url_defaults);

        if ((isset($url_defaults[piklist::$prefix]['flow']) && !stristr($location, 'flow=')) && (isset($url_defaults[piklist::$prefix]['flow_page']) && !stristr($location, 'flow_page=')))
        {
          $url_arguments = array(
            piklist::$prefix => array(
              'flow' => urlencode($url_defaults[piklist::$prefix]['flow'])
              ,'flow_page' => urlencode($url_defaults[piklist::$prefix]['flow_page'])
            )
          );

          $location .= (stristr($location, '?') ? '&' : null) . http_build_query(array_filter($url_arguments));
        }
      }
    }

    return $location;
  }

  /**
   * get
   * Stendard get method.
   *
   * @param $variable
   *
   * @return $variable
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get($variable)
  {
    return isset(self::$$variable) ? self::$$variable : false;
  }

  /**
   * register_workflows
   * Regsiter workflows to be added to the system.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_workflows()
  {
    $data = array(
              'title' => 'Title'
              ,'description' => 'Description'
              ,'capability' => 'Capability'
              ,'order' => 'Order'
              ,'flow' => 'Flow'
              ,'page' => 'Page'
              ,'post_type' => 'Post Type'
              ,'taxonomy' => 'Taxonomy'
              ,'template' => 'Template'
              ,'id' => 'ID'
              ,'slug' => 'Slug'
              ,'role' => 'Role'
              ,'redirect' => 'Redirect'
              ,'header' => 'Header'
              ,'disable' => 'Disable'
              ,'position' => 'Position'
              ,'default' => 'Default'
              ,'layout' => 'Layout'
              ,'clear' => 'Clear'
            );

    piklist::process_parts('workflows', $data, array('piklist_workflow', 'register_workflows_callback'));

    self::$after_positions = array(
      'header' => 'in_admin_header'
      ,'body' => 'all_admin_notices'
      ,'title' => piklist_admin::is_post() ? 'edit_form_after_title' : 'piklist_admin_page_after_title'
      ,'editor' => 'edit_form_after_editor'
    );

    foreach (self::$after_positions as $position => $filter)
    {
      add_action($filter, array('piklist_workflow', 'render_workflow'));
    }
  }

  /**
   * register_workflows_callback
   * Handle registered workflow tabs.
   *
   * @param array $arguments The configuration data for the workflow tab.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_workflows_callback($arguments)
  {
    $pagenow = basename($_SERVER['SCRIPT_NAME']);

    extract($arguments);

    foreach ($data['flow'] as $flow)
    {
      $tab = $data['tab'] ? current($data['tab']) : null;

      $data['flow_page'] = piklist::slug($tab ? $tab : $data['title'], 'UTF-8');
      $data['flow_slug'] = $flow == piklist::dashes($flow) ? $flow : piklist::slug($flow, 'UTF-8');
      $data['flow_sub_page'] = $tab ? piklist::slug($data['title'], 'UTF-8') : false;

      if ((piklist_admin::is_user() || piklist_admin::is_term()) && $data['position'] == 'title')
      {
        $data['position'] = 'body';
      }

      $workflow = array(
        'part' => isset($part) ? $path . '/parts/' . $folder . '/' . $part : null
        ,'data' => $data
      );

      $url_arguments = array(
        piklist::$prefix => array(
          'flow' => $data['flow_slug']
          ,'flow_page' => $data['flow_page']
          ,'flow_sub_page' => $data['flow_sub_page']
        )
      );

      $url_arguments['post'] = isset($post->ID) ? $post->ID : (isset($_REQUEST['post']) ? (int) $_REQUEST['post'] : null);

      parse_str($_SERVER['QUERY_STRING'], $url_defaults);

      foreach (array('message', 'paged', 'updated') as $variable)
      {
        unset($url_defaults[$variable]);
      }

      $url = array_merge($url_defaults, $url_arguments);

      if ($data['redirect'] != false)
      {
        /**
         * piklist_workflow_redirect_url
         *
         * @since 1.0
         */
        $data['redirect'] = apply_filters('piklist_workflow_redirect_url', $data['redirect'], $workflow, $data);

        $url_query_string = http_build_query(array_filter($url));

        $workflow['url'] = is_admin() ? admin_url($data['redirect'] . (strstr($data['redirect'], '?') ? '&' : '?') . $url_query_string) : piklist::current_url() . '?' . $url_query_string;
      }
      elseif ($data['disable'] == false)
      {
        if ($url_arguments['post'])
        {
          unset($url['page']);

          $url['action'] = 'edit';

          $pagenow = 'post.php';
        }

        $url_query_string = http_build_query(array_filter($url));

        $workflow['url'] = is_admin() ? admin_url($pagenow . '?' . $url_query_string) : piklist::current_url() . '?' . $url_query_string;
      }

      if (!isset(self::$workflows[$data['flow_slug']]))
      {
        self::$workflows[$data['flow_slug']] = array();
      }

      if (!$tab)
      {
        if ($data['header'] === true)
        {
          array_unshift(self::$workflows[$data['flow_slug']], $workflow);
        }
        elseif (!empty($data['order']))
        {
          self::$workflows[$data['flow_slug']][$data['order']] = $workflow;
        }
        else
        {
          array_push(self::$workflows[$data['flow_slug']], $workflow);
        }
      }
      else
      {
        if (!isset(self::$sub_workflows[$data['flow_slug']]))
        {
          self::$sub_workflows[$data['flow_slug']] = array();
        }

        if (!isset(self::$sub_workflows[$data['flow_slug']][$tab]))
        {
          self::$sub_workflows[$data['flow_slug']][$tab] = array();
        }

        if (!empty($data['order']))
        {
          self::$sub_workflows[$data['flow_slug']][$tab][$data['order']] = $workflow;
        }
        else
        {
          array_push(self::$sub_workflows[$data['flow_slug']][$tab], $workflow);
        }
      }
    }
  }

  /**
   * detect_workflow
   * Detect the workflow tabs according to their configuration.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function detect_workflow($folder = null, $shortcode = false)
  {
    global $pagenow, $post;

    if (empty(self::$workflows))
    {
      return false;
    }
    
    foreach (self::$workflows as $flow => $workflows)
    {
      uasort($workflows, array('piklist', 'sort_by_data_order'));

      $workflows = array_values($workflows);

      $has_data = false;

      foreach ($workflows as $workflow)
      {
        if ($workflow['data']['header'])
        {
          $has_data = $workflow;

          break;
        }
        elseif ($workflow['data']['position'] && ($workflow['data']['page'] || $workflow['data']['post_type'] || $workflow['data']['taxonomy']))
        {
          $has_data = $workflow;
        }
      }

      if (!$has_data && is_null($workflows[0]['part']))
      {
        $has_data = $workflows[0];
      }

      if (self::is_allowed($has_data) || $shortcode)
      {
        $default_workflow = null;
        $default_sub_workflow = null;
        $tab = null;
        $sub_tab = null;
        $active = null;

        foreach ($workflows as $index => &$workflow)
        {
          if (!self::is_allowed($workflow, false))
          {
            unset($workflows[$index]);
          }
          else
          {
            if ($workflow['data']['default'] == true || (!$default_workflow && !$workflow['data']['header'] == true))
            {
              $default_workflow = &$workflow;
            }

            $workflow['data'] = self::is_active($workflow['data']);

            if ($workflow['data']['active'])
            {
              $workflow['data']['active'] = true;
              $tab = piklist::slug($workflow['data']['title'], 'UTF-8');
              $data = $workflow['data'];
            }
            
            if (isset(self::$sub_workflows[$flow][$tab]))
            {
              $workflow['parts'] = self::$sub_workflows[$flow][$tab];

              uasort($workflow['parts'], array('piklist', 'sort_by_data_order'));

              foreach ($workflow['parts'] as $sub_index => &$sub_workflow)
              {
                if (!self::is_allowed($sub_workflow, false))
                {
                  unset($workflow['parts'][$sub_index]);
                }
                else
                {
                  if ($sub_workflow['data']['default'] == true)
                  {
                    $default_sub_workflow = &$sub_workflow;
                  }

                  $sub_workflow['data'] = self::is_active($sub_workflow['data']);

                  if ($sub_workflow['data']['active'])
                  {
                    $sub_workflow['data']['active'] = true;
                    $sub_tab = piklist::slug($sub_workflow['data']['title'], 'UTF-8');
                    $data = $workflow['data'];
                  }

                }
              }

              if ($workflow['data']['active'] && is_null($sub_tab))
              {
                $default_sub_workflow['data']['active'] = true;
                $sub_tab = !empty($default_sub_workflow['data']['title']) ? piklist::slug($default_sub_workflow['data']['title'], 'UTF-8') : null;
                $data = $default_sub_workflow['data'];
              }

              $workflow['parts'] = array_values($workflow['parts']);
            }
            
            if ($workflow['data']['active'])
            {
              $active = $workflow;
            }
          }
        }

        $workflows = array_values($workflows);

        unset($workflow);

        if (!$tab)
        {
          if(!empty($default_workflow))
          {
            $default_workflow['data']['active'] = true;
            $tab = piklist::slug($default_workflow['data']['title'], 'UTF-8');
            $data = $default_workflow['data'];

            if (!empty($default_workflow['parts']))
            {
              foreach ($default_workflow['parts'] as &$sub_workflow)
              {
                if ($sub_workflow['data']['default'] == true)
                {
                  $sub_workflow['data']['active'] = true;
                  $sub_tab = piklist::slug($sub_workflow['data']['title'], 'UTF-8');
                  $data = $sub_workflow['data'];

                  break;
                }
              }
              unset($sub_workflow);
            }
          }
        }

        if (isset($data)) 
        {
          if (empty(self::$workflow['workflows'][0]['data']['layout'])) 
          {
            self::$workflow['layout'] = 'tab';
          } 
          else 
          {
            self::$workflow['layout'] = self::$workflow['workflows'][0]['data']['layout'];
          }
          
          self::$workflow = array(
            'flow' => $flow
            ,'tab' => $tab
            ,'sub_tab' => $sub_tab
            ,'data' => $data
            ,'workflows' => $workflows
            ,'pages' => $has_data['data']['page']
            ,'layout' => empty($has_data['data']['layout']) ? 'tab' : $has_data['data']['layout']
            ,'active' => $active
          );
        }
      }
    }
  }

  /**
   * is_allowed
   * Determines if a workflow tab is allowed to be shown.
   *
   * @param array $data The tab configuration object.
   *
   * @return array The statuse of whether its allowed.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function is_allowed($data, $strict = true)
  {
    global $pagenow;

    $allowed = !$strict;

    // Check to see if there is an active flow
    if (isset($_REQUEST['flow']) && piklist::slug($flow, 'UTF-8') == $_REQUEST['flow'])
    {
      $allowed = true;
    }

    // Check Pages
    $pages = $data['data']['page'];
    if (!empty($pages))
    {
      $allowed = in_array($pagenow, $pages);

      if (isset($_REQUEST['page']))
      {
        $allowed = in_array($_REQUEST['page'], $pages);
      }
    }

    // Check Post Types
    if ($allowed && piklist_admin::is_post())
    {
      $post_types = $data['data']['post_type'];
      $current_post_type = piklist_cpt::detect_post_type();

      if (!empty($post_types))
      {
        $allowed = in_array($current_post_type, $post_types);
      }
    }

    // Check which mode we are on for the term pages
    if ($is_term = piklist_admin::is_term())
    {
      if ($is_term === 'new')
      {
        $allowed = false;
      }

      $taxonomies = $data['data']['taxonomy'];
      $current_taxonomy = isset($_REQUEST['taxonomy']) ? $_REQUEST['taxonomy'] : false;

      if (!empty($taxonomies) && $current_taxonomy)
      {
        $allowed = in_array($current_taxonomy, $taxonomies);
      }
    }

    return $allowed;
  }

  /**
   * is_active
   * Determines if a workflow tab is active.
   *
   * @param array $data The tab configuration object.
   *
   * @return array The modified tab configuration object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function is_active($data)
  {
    $data['active'] = false;

    if ($data['header'] == true)
    {
      return $data;
    }

    $is_active = false;
    $tab = $data['tab'] ? current($data['tab']) : null;

    if ($tab && isset($_REQUEST[piklist::$prefix]['flow_sub_page']))
    {
      $is_active = esc_attr($_REQUEST[piklist::$prefix]['flow_page']) === $data['flow_page'] && esc_attr($_REQUEST[piklist::$prefix]['flow_sub_page']) === $data['flow_sub_page'];
    }
    elseif (!$tab && isset($_REQUEST[piklist::$prefix]['flow_page']))
    {
      $is_active = esc_attr($_REQUEST[piklist::$prefix]['flow_page']) === $data['flow_page'];
    }
    else
    {
      $is_active = $data['default'] == true;
    }

    $data['active'] = $is_active;

    return $data;
  }

  /**
   * render_workflow
   * Render a workflow if applicable.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function render_workflow()
  {
    if (self::$workflow)
    {
      if (!self::$rendering)
      {
        $position = self::$workflow['workflows'][0]['data']['position'];

        $backtrace = debug_backtrace();

        foreach ($backtrace as $trace)
        {
          if ($trace['function'] == 'do_action' && isset($trace['args'][0]) && array_search($trace['args'][0], self::$after_positions) == $position)
          {
            self::$workflow['position'] = $position;

            self::$rendering = true;

            break;
          }
        }
      }

      if (self::$rendering)
      {
        piklist::render(is_admin() ? 'shared/admin-workflow' : 'shared/workflow', self::$workflow);

        self::$rendering = false;
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
    $data['tab'] = 'Tab';
    $data['sub_tab'] = 'Sub Tab';

    if ($folder != 'workflows')
    {
      $data['flow'] = 'Flow';
    }

    return $data;
  }

  /**
   * part_process_callback
   * Checks to see if something is associated with a workflow tab
   *
   * @param array $part The part object.
   * @param string $folder The folder name.
   *
   * @return array The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function part_process_callback($part, $folder)
  {
    /**
     * piklist_workflow_part_exclude_folders
     *
     * @since 1.0
     */
    $exclude_folders = apply_filters('piklist_workflow_part_exclude_folders', array('notices', 'pointers', 'help', 'widgets', 'shortcodes'), $part, $folder);

    if (!self::$workflow || in_array($folder, $exclude_folders))
    {
      return $part;
    }

    if ((is_null($part['data']['flow']) || is_null($part['data']['tab']))
          || ((!in_array(self::$workflow['flow'], $part['data']['flow']) && !in_array('all', $part['data']['flow']))
            || (!in_array(self::$workflow['tab'], $part['data']['tab']) && !in_array('all', $part['data']['tab']))
            || (is_array($part['data']['sub_tab']) && !in_array(self::$workflow['sub_tab'], $part['data']['sub_tab']) && !in_array('all', $part['data']['sub_tab']))
           )
       )
    {
      return null;
    }

    return $part;
  }

  /**
   * admin_body_class
   * Add custom classes to the admin body tag.
   *
   * @param string $classes Classes to add.
   *
   * @return string Updated classes.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function admin_body_class($classes)
  {
    if (self::$workflow)
    {
      $classes .= ' piklist-workflow-active piklist-workflow-' . self::$workflow['flow'] . ' piklist-workflow-tab-' . self::$workflow['tab'];

      if (self::$workflow['sub_tab'])
      {
        $classes .= ' piklist-workflow-sub-tab-' . self::$workflow['tab'];
      }
    }

    return $classes;
  }

  /**
   * validate_part_parameter_skip
   * Determine if there are part parameters we don't want validated.
   *
   * @param bool $skip Skip status.
   * @param string $parameter Parameter being checked.
   * @param array $part Part to add.
   *
   * @return bool Skip status
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_part_parameter_skip($skip, $parameter, $part)
  {
    if ($parameter == 'page' && $part['folder'] == 'workflow')
    {
      $skip = true;
    }

    return $skip;
  }

  /**
   * shortcode
   * Handle the shortcode for inserting a workflow
   *
   * @param array $attributes The attributes.
   * @param string $content The content of the shortcode.
   * @param string $tag The shortcode tag.
   *
   * @return string The output.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function shortcode($attributes, $content = '', $tag)
  {
    extract(shortcode_atts(array(
      'flow' => null
    ), $attributes));

    if ($flow)
    {
      $flow = piklist::slug($flow);

      self::detect_workflow(null, $flow);

      self::$rendering = true;

      self::render_workflow($flow);
    }
  }
}
