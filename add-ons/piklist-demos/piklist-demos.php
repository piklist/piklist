<?php
/*
Plugin Name: Piklist Demos
Plugin URI: https://piklist.com
Description: Creates a Demo post type, Taxonomy, Settings Page, User fields, Dashboard widget, Help tabs and Widget, with Field Examples.
Version: 0.3
Author: Piklist
Author URI: https://piklist.com/
Text Domain: piklist-demo
Domain Path: /languages
*/

  if (!defined('ABSPATH'))
  {
    exit;
  }

  add_action( 'init', 'piklist_demo_add_post_formats');
  function piklist_demo_add_post_formats()
  {
    add_theme_support('post-formats', array('aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat'));
  }


  add_filter('piklist_post_types', 'piklist_demo_post_types');
  function piklist_demo_post_types($post_types)
  {
    $post_types['piklist_demo'] = array(
      'labels' => piklist('post_type_labels', 'Piklist Demo')
      ,'title' => __('Enter a new Demo Title')
      ,'menu_icon' => piklist('url', 'piklist') . '/parts/img/piklist-menu-icon.svg'
      ,'page_icon' => piklist('url', 'piklist') . '/parts/img/piklist-page-icon-32.png'
      ,'show_in_rest' => true
      ,'rest_base' => 'piklist-demos'
      ,'supports' => array(
        'title'
        ,'post-formats'
      )
      ,'public' => true
      ,'admin_body_class' => array(
        'piklist-demonstration'
        ,'piklist-sample'
      )
      ,'has_archive' => true
      ,'rewrite' => array(
        'slug' => 'piklist-demo'
      )
      ,'capability_type' => 'post'
      ,'edit_columns' => array(
        'title' => __('Demo')
        ,'author' => __('Assigned to')
      )
      ,'hide_meta_box' => array(
        'slug'
        ,'author'
      )
      ,'status' => array(
        'publish' => array(
          'label' => 'Publish'
          ,'public' => true
        )
        ,'new' => array(
          'label' => 'New'
          ,'public' => false
        )
        ,'pending' => array(
          'label' => 'Pending Review'
          ,'public' => false
        )
        ,'demo' => array(
          'label' => 'Demo'
          ,'public' => true
          ,'exclude_from_search' => true
          ,'show_in_admin_all_list' => true
          ,'show_in_admin_status_list' => true
       )
        ,'lock' => array(
          'label' => 'Lock'
          ,'public' => true
        )
      )
    );

    return $post_types;
  }

  add_filter('piklist_taxonomies', 'piklist_demo_taxonomies');
  function piklist_demo_taxonomies($taxonomies)
  {
    $taxonomies[] = array(
      'post_type' => 'piklist_demo'
      ,'name' => 'piklist_demo_type'
      ,'configuration' => array(
        'hierarchical' => true
        ,'labels' => piklist('taxonomy_labels', 'Demo Taxonomy')
        ,'page_icon' => piklist('url', 'piklist') . '/parts/img/piklist-page-icon-32.png'
        ,'show_ui' => true
        ,'query_var' => true
        ,'rewrite' => array(
          'slug' => 'demo-type'
        )
        ,'show_admin_column' => true
        ,'list_table_filter' => true
        ,'meta_box_filter' => true
        ,'comments' => true
      )
    );

    $taxonomies[] = array(
      'object_type' => 'user'
      ,'name' => 'piklist_demo_user_type'
      ,'configuration' => array(
        'hierarchical' => true
        ,'labels' => piklist('taxonomy_labels', 'Demo User Type')
        ,'page_icon' => piklist('url', 'piklist') . '/parts/img/piklist-page-icon-32.png'
        ,'show_ui' => true
        ,'query_var' => true
        ,'rewrite' => array(
          'slug' => 'demo-user-type'
        )
        ,'show_admin_column' => true
        ,'list_table_filter' => true
      )
    );

    return $taxonomies;
  }

  add_filter('piklist_admin_pages', 'piklist_demo_admin_pages');
  function piklist_demo_admin_pages($pages)
  {
    $pages[] = array(
      'page_title' => __('Demo Settings')
      ,'menu_title' => __('Demo Settings', 'piklist-demo')
      ,'sub_menu' => 'edit.php?post_type=piklist_demo'
      ,'capability' => 'manage_options'
      ,'menu_slug' => 'piklist_demo_fields'
      ,'setting' => 'piklist_demo_fields'
      ,'menu_icon' => piklist('url', 'piklist') . '/parts/img/piklist-icon.png'
      ,'default_tab' => 'Basic'
      // ,'layout' => 'meta-boxes' // NOTE: Uncomment this to use the meta box layout on this settings page!
      ,'save_text' => 'Save Demo Settings'
    );

    $pages[] = array(
      'page_title' => __('Reading Settings')
      ,'menu_title' => __('Demo Reading', 'piklist-demo')
      ,'sub_menu' => 'edit.php?post_type=piklist_demo'
      ,'capability' => 'manage_options'
      ,'menu_slug' => 'piklist_demo_options'
      ,'menu_icon' => piklist('url', 'piklist') . '/parts/img/piklist-icon.png'
    );

    $pages[] = array(
      'page_title' => __('Bulk Create')
      ,'menu_title' => __('Bulk Create', 'piklist-demo')
      ,'sub_menu' => 'edit.php?post_type=piklist_demo'
      ,'capability' => 'create_posts'
      ,'menu_slug' => 'piklist_demo_bulk_create'
      ,'menu_icon' => piklist('url', 'piklist') . '/parts/img/piklist-icon.png'
    );

    return $pages;
  }

  add_filter('piklist_field_templates', 'piklist_demo_field_templates');
  function piklist_demo_field_templates($templates)
  {
    $templates['piklist_demo'] = array(
                                'name' => __('User', 'piklist-demo')
                                ,'description' => __('Default layout for User fields from Piklist Demos.', 'piklist-demo')
                                ,'template' => '[field_wrapper]
                                                  <div id="%1$s" class="%2$s">
                                                    [field_label]
                                                    [field]
                                                    [field_description_wrapper]
                                                      <small>[field_description]</small>
                                                    [/field_description_wrapper]
                                                  </div>
                                                [/field_wrapper]'
                              );

    $templates['theme_tight'] = array(
                                  'name' => __('Theme - Tight', 'piklist-demo')
                                  ,'description' => __('A front end form wrapper example from Piklist Demos.', 'piklist-demo')
                                  ,'template' => '[field_wrapper]
                                                    <div id="%1$s" class="%2$s piklist-field-container">
                                                      [field_label]
                                                      <div class="piklist-field">
                                                        [field]
                                                        [field_description_wrapper]
                                                          <span class="piklist-field-description">[field_description]</span>
                                                        [/field_description_wrapper]
                                                      </div>
                                                    </div>
                                                  [/field_wrapper]'
                                );

    return $templates;
  }

  add_filter('piklist_post_submit_meta_box_title', 'piklist_demo_post_submit_meta_box_title', 10, 2);
  function piklist_demo_post_submit_meta_box_title($title, $post)
  {
    switch ($post->post_type)
    {
      case 'piklist_demo':
        $title = __('Create Demo');
      break;
    }

    return $title;
  }

  add_filter('piklist_post_submit_meta_box', 'piklist_demo_post_submit_meta_box', 10, 3);
  function piklist_demo_post_submit_meta_box($show, $section, $post)
  {
    switch ($post->post_type)
    {
      case 'piklist_demo':

        switch ($section)
        {
          case 'minor-publishing-actions':
          //case 'misc-publishing-actions':
          //case 'misc-publishing-actions-status':
          case 'misc-publishing-actions-visibility':
          case 'misc-publishing-actions-published':

            $show = false;

          break;
        }

      break;
    }

    return $show;
  }

  add_filter('piklist_assets', 'piklist_demo_assets');
  function piklist_demo_assets($assets)
  {
    array_push($assets['styles'], array(
      'handle' => 'piklist-demos'
      ,'src' => piklist('url', 'piklist-demos') . '/parts/css/piklist-demo.css'
      ,'media' => 'screen, projection'
      ,'enqueue' => true
      ,'admin' => true
    ));

    return $assets;
  }

  function piklist_demo_get_states()
  {
    return   $states = array(
      'AL' => 'AL'
      ,'AK' => 'AK'
      ,'AZ' => 'AZ'
      ,'AR' => 'AR'
      ,'CA' => 'CA'
      ,'CO' => 'CO'
      ,'CT' => 'CT'
      ,'DE' => 'DE'
      ,'DC' => 'DC'
      ,'FL' => 'FL'
      ,'GA' => 'GA'
      ,'HI' => 'HI'
      ,'ID' => 'ID'
      ,'IL' => 'IL'
      ,'IN' => 'IN'
      ,'IA' => 'IA'
      ,'KS' => 'KS'
      ,'KY' => 'KY'
      ,'LA' => 'LA'
      ,'ME' => 'ME'
      ,'MD' => 'MD'
      ,'MA' => 'MA'
      ,'MI' => 'MI'
      ,'MN' => 'MN'
      ,'MS' => 'MS'
      ,'MO' => 'MO'
      ,'MT' => 'MT'
      ,'NE' => 'NE'
      ,'NV' => 'NV'
      ,'NH' => 'NH'
      ,'NJ' => 'NJ'
      ,'NM' => 'NM'
      ,'NY' => 'NY'
      ,'NC' => 'NC'
      ,'ND' => 'ND'
      ,'OH' => 'OH'
      ,'OK' => 'OK'
      ,'OR' => 'OR'
      ,'PA' => 'PA'
      ,'RI' => 'RI'
      ,'SC' => 'SC'
      ,'SD' => 'SD'
      ,'TN' => 'TN'
      ,'TX' => 'TX'
      ,'UT' => 'UT'
      ,'VT' => 'VT'
      ,'VA' => 'VA'
      ,'WA' => 'WA'
      ,'WV' => 'WV'
      ,'WI' => 'WI'
      ,'WY' => 'WY'
    );
  }


  /**
   * Show the "Get value" link after data is saved in Piklist Demos
   */
  $piklist_demo_thickbox_loaded = false;

  add_filter('piklist_pre_render_field', 'piklist_demo_pre_render_field', 10, 2);
  function piklist_demo_pre_render_field($field)
  {
    global $pagenow, $typenow, $piklist_demo_thickbox_loaded;

    if (!in_array($pagenow, array('user-edit.php', 'profile.php')) && $typenow != 'piklist_demo')
    {
      return $field;
    }

    if (!$piklist_demo_thickbox_loaded)
    {
      add_thickbox();

      $piklist_demo_thickbox_loaded = true;
    }

    $codes = $values = array();

    if ($field['type'] != 'html' && !$field['relate'])
    {
      switch ($field['scope'])
      {
        case 'post_meta':
        case 'user_meta':
        case 'term_meta':
        case 'comment_meta':

          // Only show this if data is saved.
          if ($field['object_id'])
          {
            $type = str_replace('_meta', '', $field['scope']);

            if (!$field['group_field'])
            {
              if ($field['type'] == 'group')
              {
                if ($field['field'])
                {
                  $unique = true;

                  array_push($codes, '$value = get_' . $type . '_meta(' . $field['object_id'] . ', \'' . $field['field'] . '\', ' . ($unique ? 'true' : 'false') . ');');
                  array_push($values, get_metadata($type, $field['object_id'], $field['field'], $unique));
                }
                else
                {
                  $unique = !$field['add_more'];

                  foreach ($field['fields'] as $column)
                  {
                    array_push($codes, '$value = get_' . $type . '_meta(' . $field['object_id'] . ', \'' . $column['field'] . '\', ' . ($unique ? 'true' : 'false') . ');');
                    array_push($values, get_metadata($type, $field['object_id'], $column['field'], $unique));
                  }
                }
              }
              elseif (empty($field['conditions']))
              {
                $unique = !$field['add_more'];

                if (!$unique && $field['type'] == 'radio')
                {
                  $unique = true;
                }
                else if ($unique && $field['multiple'])
                {
                  $unique = false;
                }

                array_push($codes, '$value = get_' . $type . '_meta(' . $field['object_id'] . ', \'' . $field['field'] . '\', ' . ($unique ? 'true' : 'false') . ');');
                array_push($values, get_metadata($type, $field['object_id'], $field['field'], $unique));
              }
            }
          }

        break;

        default:

          if (!$field['group_field'])
          {
            if ($field['type'] == 'group')
            {
              if ($field['field'])
              {
                array_push($codes, '$option = get_option(' . $field['scope'] . ');<br>  $value = $option[\'' . $field['field'] . '\'];');

                $option = get_option($field['scope']);

                if (isset($option[$field['field']]))
                {
                  array_push($values, $option[$field['field']]);
                }
              }
              else
              {
                $unique = !$field['add_more'];

                foreach ($field['fields'] as $column)
                {
                  array_push($codes, '$option = get_option(' . $field['scope'] . ');<br>  $value = $option[\'' . $column['field'] . '\'];');

                  $option = get_option($field['scope']);

                  if (isset($option[$column['field']]))
                  {
                    array_push($values, $option[$column['field']]);
                  }
                }
              }
            }
            elseif (empty($field['conditions']))
            {
              array_push($codes, '$option = get_option(' . $field['scope'] . ');<br>  $value = $option[\'' . $field['field'] . '\'];');

              $option = get_option($field['scope']);

              if (isset($option[$field['field']]))
              {
                array_push($values, $option[$field['field']]);
              }
            }
          }

        break;
      }

      if (!empty($values[0]))
      {
        $field['description'] .= piklist('shared/field-value', array(
                                   'id' => piklist::unique_id()
                                   ,'codes' => $codes
                                   ,'values' => $values
                                   ,'field' => $field
                                   ,'return' => true
                                 ));
      }
    }

    return $field;
  }

  function piklist_demo_workflow_bar($flow)
  {
    if ($flow == 'demo_workflow')
    {
      $domain = $_SERVER['HTTP_HOST'];

      $url = 'http://' . $domain . $_SERVER['REQUEST_URI'];

      $help = piklist::render(
        'shared/tooltip-help'
          ,array(
            'message' => __('By default WorkFlows are setup as TABS. To change them to a BAR, use "Layout : Bar" in your Workflow Header file.', 'piklist-demo')
          )
          ,true
      );

      if (isset($_REQUEST['piklist_demo_workflow']))
      {
        // remove demo_workflow parameter
        $url = preg_replace('/(.*)(?|&)piklist_demo_workflow=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');

        echo '<a href="' . $url . '" class="alignright button button-secondary demo-tab-bar">' . __('View as Tabs', 'piklist-demo') . $help . '</a>';
      }
      else
      {
        echo '<a href="' . $url . '&piklist_demo_workflow=bar" class="alignright button button-secondary demo-tab-bar">' . __('View as Bar', 'piklist-demo') . $help . '</a>';
      }
    }

  }
  add_action('piklist_workflow_flow_append', 'piklist_demo_workflow_bar');


  function piklist_demo_change_workflow_layout($part)
  {
    if (isset($_REQUEST['piklist_demo_workflow']) && $_REQUEST['piklist_demo_workflow'] == 'bar')
    {
      $part['data']['layout'] = 'bar';
    }

    return $part;
  }
  add_action('piklist_part_process-workflows', 'piklist_demo_change_workflow_layout');



  function piklist_demo_part_add_meta_boxes($parts)
  {
    array_push($parts, array(
      // Give the part a unique id
      'id' => 'piklist_demo_part_custom_id'
      // Associate it with an plugin/add-on
      ,'add_on' => 'piklist-demos'
      // Specify its configuration, same as the comment block except keys are slugs
      ,'data' => array(
        'title' => 'Custom Meta Box'
        ,'post_type' => 'piklist_demo'
        ,'order' => 22
        ,'tab' => 'Common'
        ,'sub_tab' => 'Basic'
        ,'flow' => 'Demo Workflow'
      )
      // Where to render the contents, either from a path string or an array with a callback. Pass as many as needed for extensions
      ,'render' => array(
        array(
          'callback' => 'piklist_demo_part_add_meta_boxes_callback'
          ,'args' => array(
            'foo' => 'bar'
          )
        )
      )
    ));

    return $parts;
  }
  add_filter('piklist_part_add-meta-boxes', 'piklist_demo_part_add_meta_boxes', 100);


  function piklist_demo_part_add_meta_boxes_callback($post, $arguments)
  {
    piklist::pre($arguments);
  }
