<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Form
 * Manages fields and forms for Piklist.
 *
 * @package     Piklist
 * @subpackage  Form
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Form
{
  /**
   * @var array The field list types
   * @access private
   */
  private static $field_list_types = array(
    'multiple_fields' => array(
      'checkbox'
      ,'checkbox-tree'
      ,'file'
      ,'radio'
      ,'add-ons'
    )
    ,'multiple_value' => array(
      'checkbox'
      ,'checkbox-tree'
      ,'file'
      ,'select'
      ,'add-ons'
    )
  );

  /**
   * @var array Registered field templates
   * @access private
   */
  private static $templates = array();

  /**
   * @var array List of the template shortcodes used in field templates.
   * @access private
   */
  private static $template_shortcodes = array(
    'field_wrapper'
    ,'field_label'
    ,'field'
    ,'field_description_wrapper'
    ,'field_description'
  );

  /**
   * @var array The list of built in scopes and their attributes.
   * @access private
   */
  private static $scopes = array(
    'post' => array(
      'ID'
      ,'menu_order'
      ,'comment_status'
      ,'ping_status'
      ,'pinged'
      ,'post_author'
      ,'post_category'
      ,'post_content'
      ,'post_date'
      ,'post_date_gmt'
      ,'post_excerpt'
      ,'post_name'
      ,'post_parent'
      ,'post_password'
      ,'post_status'
      ,'post_title'
      ,'post_type'
      ,'tags_input'
      ,'to_ping'
      ,'tax_input'
    )
    ,'post_meta' => array()
    ,'comment' => array(
      'comment_ID'
      ,'comment_post_ID'
      ,'comment_author'
      ,'comment_author_email'
      ,'comment_author_url'
      ,'comment_content'
      ,'comment_type'
      ,'comment_parent'
      ,'user_id'
      ,'comment_author_IP'
      ,'comment_agent'
      ,'comment_date'
      ,'comment_approved'
    )
    ,'comment_meta' => array()
    ,'user' => array(
      'ID'
      ,'user_pass'
      ,'user_login'
      ,'user_nicename'
      ,'user_url'
      ,'user_email'
      ,'display_name'
      ,'nickname'
      ,'first_name'
      ,'last_name'
      ,'description'
      ,'rich_editing'
      ,'user_registered'
      ,'role'
      ,'user_role'
      ,'jabber'
      ,'aim'
      ,'yim'
      ,'rememberme'
      ,'signon'
    )
    ,'user_meta' => array()
    ,'taxonomy' => array()
    ,'term_meta' => array()
  );

  /**
   * @var array Aliases for field types.
   * @access private
   */
  private static $field_alias = array(
    'datepicker' => 'text'
    ,'timepicker' => 'text'
    ,'colorpicker' => 'text'
    ,'password' => 'text'
    ,'color' => 'text'
    ,'date' => 'text'
    ,'datetime' => 'text'
    ,'datetime-local' => 'text'
    ,'email' => 'text'
    ,'month' => 'text'
    ,'range' => 'text'
    ,'search' => 'text'
    ,'tel' => 'text'
    ,'time' => 'text'
    ,'url' => 'text'
    ,'week' => 'text'
    ,'submit' => 'button'
    ,'reset' => 'button'
  );

  /**
   * @var array Field assets by type.
   * @access private
   */
  private static $field_assets = array(
    'colorpicker' => array(
      'callback' => array('piklist_form', 'render_field_custom_assets')
    )
    ,'datepicker' => array(
      'scripts' => array(
        'jquery-ui-datepicker'
      )
    )
    ,'editor' => array(
      'styles' => array(
        'editor-buttons'
      )
      ,'scripts' => array(
        'editor'
      )
    )
  );

  /**
   * @var array The fields that have been rendered.
   * @access private
   */
  private static $fields_rendered = array();

  /**
   * @var array The last field rendered.
   * @access private
   */
  private static $field_rendered = null;

  /**
   * @var array The current field rendering.
   * @access private
   */
  private static $field_rendering = null;

  /**
   * @var array The current field types rendered.
   * @access private
   */
  private static $field_types_rendered = array();

  /**
   * @var string The current form id.
   * @access private
   */
  private static $form_id = null;

  /**
   * @var array The form submission.
   * @access private
   */
  private static $form_submission = array();

  /**
   * @var array Registered forms.
   * @access private
   */
  private static $forms = array();

  /**
   * @var array The form nonce.
   * @access private
   */
  private static $nonce = false;

  /**
   * @var array The settings for any field editors.
   * @access private
   */
  private static $field_editor_settings = array();

  /**
   * @var array The attributes for any field editors.
   * @access private
   */
  private static $field_editor_attributes = array();

  /**
   * @var array A collection of rendered object ids.
   * @access private
   */
  private static $related_object_ids = array();

  /**
   * @var int Store newly created term if from admin ajax form.
   * @access private
   */
  private static $tag_ID = null;

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
    add_action('wp_loaded', array('piklist_form', 'wp_loaded'), 100);

    add_action('post_edit_form_tag', array('piklist_form', 'add_enctype'));
    add_action('user_edit_form_tag', array('piklist_form', 'add_enctype'));
    add_action('edit_category_form_pre', array('piklist_form', 'setup_add_enctype'));
    add_action('edit_link_category_form_pre', array('piklist_form', 'setup_add_enctype'));
    add_action('edit_tag_form_pre', array('piklist_form', 'setup_add_enctype'));
    add_action('created_term', array('piklist_form', 'created_term'), 10000, 3);

    add_action('init', array('piklist_form', 'save_fields_actions'), 100);
    add_action('init', array('piklist_form', 'register_forms'));
    add_action('wp_ajax_piklist_form', array('piklist_form', 'ajax'));
    add_action('wp_ajax_nopriv_piklist_form', array('piklist_form', 'ajax'));
    add_action('admin_enqueue_scripts', array('piklist_form', 'wp_enqueue_media'));
    add_action('admin_footer', array('piklist_form', 'render_field_assets'));
    add_action('wp_footer', array('piklist_form', 'render_field_assets'));
    add_action('customize_controls_print_footer_scripts', array('piklist_form', 'render_field_assets'));

    add_action('piklist_notices', array('piklist_form', 'notices'));

    if (is_admin())
    {
      add_action('admin_enqueue_scripts', 'wp_enqueue_media');
    }

    add_filter('teeny_mce_before_init', array('piklist_form', 'tiny_mce_settings'), 100, 2);
    add_filter('tiny_mce_before_init', array('piklist_form', 'tiny_mce_settings'), 100, 2);
    add_filter('quicktags_settings', array('piklist_form', 'quicktags_settings'), 100, 2);
    add_filter('piklist_field_templates', array('piklist_form', 'field_templates'), 0);
    add_filter('the_editor', array('piklist_form', 'the_editor'));
    add_filter('tiny_mce_before_init', array('piklist_form', 'remove_theme_css'), 10, 2);
    add_filter('teeny_mce_before_init', array('piklist_form', 'remove_theme_css'), 10, 2);
  }

  /**
   * wp_loaded
   * Setup field templates, listeners for editors and process forms if necessary.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_loaded()
  {
    global $pagenow;

    /**
     * piklist_field_templates
     * Add custom field templates.
     *
     * @since 1.0
     */
    self::$templates = apply_filters('piklist_field_templates', self::$templates);

    /**
     * piklist_field_list_types
     * Add custom fields to the list types object.
     *
     * @since 1.0
     */
    $piklist_field_list_types = apply_filters('piklist_field_list_types', array());

    // Do not allow filter to overwrite default $field_list_types
    self::$field_list_types = array_merge_recursive($piklist_field_list_types, self::$field_list_types);

    /**
     * piklist_field_alias
     * Add custom aliases to the default aliases.
     *
     * @since 1.0
     */
    $piklist_field_alias = apply_filters('piklist_field_alias', array());

    // Do not allow filter to overwrite default $field_alias
    self::$field_alias = array_merge($piklist_field_alias, self::$field_alias);

    foreach (self::$template_shortcodes as $template_shortcode)
    {
      add_shortcode($template_shortcode, array('piklist_form', 'template_shortcode'));
    }

    if (in_array($pagenow, array('widgets.php', 'customize.php')))
    {
      if (!class_exists('_WP_Editors'))
      {
        require(ABSPATH . WPINC . '/class-wp-editor.php');
      }

      if ($pagenow == 'widgets.php')
      {
        add_action('admin_print_footer_scripts', array('_WP_Editors', 'editor_js'), 50);
        add_action('admin_footer', array('piklist_form', 'editor_proxy'));
      }
      else
      {
        add_action('customize_controls_print_footer_scripts', array('_WP_Editors', 'editor_js'), 50);
        add_action('customize_controls_print_footer_scripts', array('piklist_form', 'editor_proxy'));
      }
    }

    self::check_nonce();

    self::process_form();
  }

  /**
   * created_term
   * Process the add-tag form in the admin.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function created_term($term_id, $tt_id, $taxonomy)
  {
    if (did_action('wp_ajax_add-tag'))
    {
      self::$tag_ID = $term_id;

      self::process_form();
    }
  }

  /**
   * get
   * A simple getter function.
   *
   * @param string $variable The variable name to get.
   *
   * @return mixed The requested value.
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
   * valid
   * Check if there is a valid nonce.
   *
   * @return bool Status of nonce.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function valid()
  {
    return self::$nonce;
  }

  /**
   * check_nonce
   * Check for a nonce in a submission.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function check_nonce()
  {
    if (isset($_REQUEST[piklist::$prefix]['nonce']) && isset($_REQUEST[piklist::$prefix]['fields']))
    {
      self::$nonce = wp_verify_nonce($_REQUEST[piklist::$prefix]['nonce'], 'piklist-' . $_REQUEST[piklist::$prefix]['fields']);
    }
  }

  /**
   * editor_proxy
   * Render the editor proxy.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function editor_proxy()
  {
    piklist::render('shared/editor-proxy');
  }

  /**
   * field_templates
   * Define field layouts for each section.
   *
   * @param array $templates The field templates.
   *
   * @return array The field templates.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function field_templates($templates)
  {
    $submit = '<p class="%1$s submit">
                 [field]
               </p>';

    $form_table_field = '<fieldset>
                           <legend class="screen-reader-text"><span>[field_label label_tag="false"]</span></legend>
                           [field]
                         </fieldset>
                         [field_description_wrapper]
                           <p class="description">[field_description]</p>
                         [/field_description_wrapper]';

    $form_table = '<table class="form-table piklist-form-table">
                     [field_wrapper]
                       <tr class="%1$s">
                         <th scope="row">
                           [field_label]
                         </th>
                         <td>
                           <fieldset>
                             <legend class="screen-reader-text"><span>[field_label label_tag="false"]</span></legend>
                             [field]
                           </fieldset>
                           [field_description_wrapper]
                             <p class="description">[field_description]</p>
                           [/field_description_wrapper]
                         </td>
                       </tr>
                     [/field_wrapper]
                   </table>';

    $form_table_inline_description = '<table class="form-table">
                                        [field_wrapper]
                                          <tr class="%1$s">
                                            <th scope="row">
                                              [field_label]
                                            </th>
                                            <td>
                                              <fieldset>
                                                <legend class="screen-reader-text"><span>[field_label label_tag="false"]</span></legend>
                                                [field][field_description_wrapper]&nbsp;[field_description][/field_description_wrapper]
                                              </fieldset>
                                            </td>
                                          </tr>
                                        [/field_wrapper]
                                      </table>';

    $responsive = '[field_wrapper]
                     <div class="%1$s piklist-field-container">
                       <div class="piklist-field-container-row">
                         <div class="piklist-label-container">
                           [field_label]
                           [field_description_wrapper]
                             <p class="piklist-field-description description">[field_description]</p>
                           [/field_description_wrapper]
                         </div>
                         <div class="piklist-field">
                           [field]
                         </div>
                       </div>
                     </div>
                   [/field_wrapper]';

    $theme = '[field_wrapper]
                <div class="%1$s piklist-theme-field-container">
                  <div class="piklist-theme-label">
                    [field_label]
                  </div>
                  <div class="piklist-theme-field">
                    [field]
                    [field_description_wrapper]
                      <p class="piklist-theme-field-description">[field_description]</p>
                    [/field_description_wrapper]
                  </div>
                </div>
              [/field_wrapper]';

    $term_meta_new = '[field_wrapper]
                        <div class="%1$s form-field ui-helper-clearfix">
                          [field_label]
                          [field]
                          [field_description_wrapper]
                            <p class="piklist-theme-field-description">[field_description]</p>
                          [/field_description_wrapper]
                        </div>
                      [/field_wrapper]';

    // Helpers and Defaults

    $templates['field'] = array(
      'name' => __('Field', 'piklist')
      ,'description' => __('Displays a field.', 'piklist')
      ,'template' => '[field]'
    );

    $templates['field_label'] = array(
      'name' => __('Field and Label', 'piklist')
      ,'description' => __('Displays a field then the label.', 'piklist')
      ,'template' => '[field][field_label]'
    );

    $templates['label_field'] = array(
      'name' => __('Label and Field', 'piklist')
      ,'description' => __('Displays a label then the field.', 'piklist')
      ,'template' => '[field_label][field]'
    );

    $templates['field_description'] = array(
      'name' => __('Field and Description', 'piklist')
      ,'description' => __('Displays a field then the description.', 'piklist')
      ,'template' => '[field]
                      [field_description_wrapper]
                        <p class="piklist-theme-field-description">[field_description]</p>
                      [/field_description_wrapper]'
    );

    // Core

    $templates['default'] = array(
      'name' => __('Default', 'piklist')
      ,'description' => __('General responsive layout with a fixed label column.', 'piklist')
      ,'template' => $responsive
    );

    $templates['submit'] = array(
      'name' => __('Submit Button', 'piklist')
      ,'description' => __('General layout for submit button in WordPress.', 'piklist')
      ,'template' => $submit
    );

    $templates['form_table'] = array(
      'name' => __('Form Table', 'piklist')
      ,'description' => __('Default layout for the WordPress form table.', 'piklist')
      ,'template' => $form_table
    );

    $templates['form_table_field'] = array(
      'name' => __('Form Table - Field', 'piklist')
      ,'description' => __('The field component of the WordPress form table layout.', 'piklist')
      ,'template' => $form_table_field
    );

    $templates['form_table_inline_description'] = array(
      'name' => __('Form Table - Inline Description', 'piklist')
      ,'description' => __('The description is just added as appended text.', 'piklist')
      ,'template' => $form_table_inline_description
    );

    // Scopes
    $templates['widget'] = array(
      'name' => __('Widget', 'piklist')
      ,'description' => __('Default layout for Widget fields.', 'piklist')
      ,'template' => $responsive
    );

    $templates['post_meta'] = array(
      'name' => __('Post', 'piklist')
      ,'description' => __('Default layout for Post fields.', 'piklist')
      ,'template' => $responsive
    );

    $templates['term_meta'] = array(
      'name' => __('Terms', 'piklist')
      ,'description' => __('Default layout for Term fields.', 'piklist')
      ,'template' => $form_table
    );

    $templates['term_meta_new'] = array(
      'name' => __('New Terms', 'piklist')
      ,'description' => __('Default layout for New Term fields.', 'piklist')
      ,'template' => $term_meta_new
    );

    $templates['user_meta'] = array(
      'name' => __('User', 'piklist')
      ,'description' => __('Default layout for User fields.', 'piklist')
      ,'template' => $form_table
    );

    $templates['option'] = array(
      'name' => __('Option', 'piklist')
      ,'description' => __('Default layout for Option fields.', 'piklist')
      ,'template' => $responsive
    );

    $templates['shortcode'] = array(
      'name' => __('Shortcode', 'piklist')
      ,'description' => __('Default layout for Shortcode fields.', 'piklist')
      ,'template' => $responsive
    );

    $templates['media_meta'] = array(
      'name' => __('Media', 'piklist')
      ,'description' => __('Default layout for Media fields.', 'piklist')
      ,'template' => $responsive
    );

    $templates['theme'] = array(
      'name' => __('Theme', 'piklist')
      ,'description' => __('Default layout for frontend fields.', 'piklist')
      ,'template' => $theme
    );

    return $templates;
  }

  /**
   * get_field_id
   * Get the field id attribute.
   *
   * @param array $field The field object.
   *
   * @return string The field id.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_field_id($field)
  {
    if (!$field['field'])
    {
      return false;
    }

    $field['prefix'] = $field['scope'] && $field['prefix'] ? piklist::$prefix : null;
    $context = self::get_field_context($field, true);

    if (piklist_admin::is_widget() && (!$field['scope'] || ($field['scope'] && ($field['scope'] != piklist::$prefix && $field['field'] != 'fields'))))
    {
      $id = piklist_widget::widget()->get_field_id(str_replace(':', '_', $field['field']))
            . (is_numeric($field['index']) ? '_' . $field['index'] : null);
    }
    else
    {
      $field_name = $field['field'];

      if (!is_numeric($field['index']) && strstr($field['field'], ':'))
      {
        $parts = explode(':', $field_name);
        $parts = array_filter($parts, array('piklist', 'is_not_numeric'));

        $field_name = implode(':', $parts);
      }

      $id = $field['prefix']
            . ($field['scope'] && $field['scope'] != piklist::$prefix ? $context . '_' : null)
            . str_replace(':', '_', $field_name)
            . (is_numeric($field['index']) ? '_' . $field['index'] : null);
    }

    self::$fields_rendered = self::update_fields_data(self::$fields_rendered, $field, null, 'id', $id);

    return $id;
  }

  /**
   * get_field_name
   * Get the field name attribute.
   *
   * @param array $field The field object.
   *
   * @return string The field name.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_field_name($field)
  {
    if (!$field['field'])
    {
      return false;
    }

    $prefix = !in_array($field['scope'], array(piklist::$prefix, false)) && $field['prefix'] ? piklist::$prefix : null;
    $context = self::get_field_context($field, true);
    $child_add_more = isset($field['child_add_more']) && $field['child_add_more'];
    $child_field = isset($field['child_field']) && $field['child_field'];
    $multiple = isset($field['multiple']) && $field['multiple'];
    $add_more = isset($field['add_more']) && $field['add_more'];
    $grouped = $child_field && strstr($field['field'], ':');
    $grouped_add_more = $grouped && $child_add_more;
    $ungrouped_add_more = !$grouped && $child_add_more;
    $use_index = (($grouped_add_more || $add_more || $child_add_more) && !$grouped) && is_numeric($field['index']);

    $field_choices = isset($field['choices']) && is_array($field['choices']) ? $field['choices'] : array();
    $use_object = (($multiple && (count($field_choices) > 1 || !$field_choices)) || $ungrouped_add_more || $add_more) && $field['scope'] != piklist::$prefix;

    if (piklist_admin::is_widget() && (!$field['scope'] || ($field['scope'] && ($field['scope'] != piklist::$prefix && $field['field'] != 'fields'))))
    {
      $name = piklist_widget::widget()->get_field_name(str_replace(':', '][', $field['field']))
              . (($multiple && count($field_choices) > 1) && $use_index ? '[' . $field['index'] . ']' : null)
              . ($use_object ? '[]' : null);
    }
    else
    {
      $name = $prefix
              . ($field['scope'] ? $context . (piklist_admin::is_media() && isset($GLOBALS['piklist_attachment']) ? '_' . $GLOBALS['piklist_attachment']->ID : '') : null)
              . ($field['field'] ? ($context ? '[' : null) . str_replace(':', '][', $field['field']) . ($field['scope'] ? ']' : null) : null)
              . (($multiple && count($field_choices) > 1) && $use_index ? '[' . $field['index'] . ']' : null)
              . ($use_object ? '[]' : null);
    }

    self::$fields_rendered = self::update_fields_data(self::$fields_rendered, $field, null, 'name', $name);

    return $name;
  }

  /**
   * get_field_object_id
   * Get the field object id.
   *
   * @return int The field object id.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_field_object_id($field)
  {
    global $post, $tag_ID, $current_user, $wp_taxonomies, $pagenow, $user_id, $blog_id;

    // Retrieve the object_id from a submitted form field first if possible
    if (!empty(self::$form_submission) && isset(self::$form_submission[$field['scope']][$field['field']]['object_id']))
    {
      return self::$form_submission[$field['scope']][$field['field']]['object_id'];
    }

    $id = null;

    switch ($field['scope'])
    {
      case 'comment':
      case 'post_meta':
      case 'taxonomy':

        if (isset($wp_taxonomies[$field['field']]) && isset($wp_taxonomies[$field['field']]->object_type) && $wp_taxonomies[$field['field']]->object_type[0] == 'user')
        {
          if (isset($_REQUEST[piklist::$prefix . 'user']['ID']) && self::valid())
          {
            $id = (int) $_REQUEST[piklist::$prefix . 'user']['ID'][0];
          }
          elseif ($pagenow == 'user-edit.php')
          {
            $id = $user_id;
          }
          elseif (is_user_logged_in())
          {
            $id = $current_user->ID;
          }
        }
        else
        {
          if (isset($GLOBALS['piklist_attachment']))
          {
            $id = $GLOBALS['piklist_attachment']->ID;
          }
          else
          {
            if (isset($_REQUEST[piklist::$prefix . 'post']['ID']))
            {
              $id = (int) $_REQUEST[piklist::$prefix . 'post']['ID'];
            }
            elseif (is_admin() && $post)
            {
              $id = $post->ID;
            }
          }
        }

      break;

      case 'term_meta':

        if ($tag_ID)
        {
          $id = $tag_ID;
        }
        elseif (self::$tag_ID)
        {
          $id = self::$tag_ID;
        }

      break;

      case 'user_meta':

        if (isset($_REQUEST[piklist::$prefix . 'user']['ID']))
        {
          $id = (int) $_REQUEST[piklist::$prefix . 'user']['ID'];
        }
        elseif ($pagenow == 'user-edit.php')
        {
          $id = $user_id;
        }
        elseif (in_array($pagenow, array('post.php', 'post-new.php')))
        {
          $id = $post->ID;
        }
        elseif (is_user_logged_in())
        {
          $id = $current_user->ID;
        }

      break;

      case 'post':

        if ($field['field'] == 'ID' && !empty($field['value']))
        {
          $id = $field['value'];
        }

        if (isset($_REQUEST[piklist::$prefix . 'post']['ID']))
        {
          $id = (int) $_REQUEST[piklist::$prefix . 'post']['ID'];
        }
        elseif (is_admin() && $post)
        {
          $id = $post->ID;
        }

      break;

      case 'user':

        if ($field['field'] == 'ID' && !empty($field['value']))
        {
          $id = $field['value'];
        }

        if (isset($_REQUEST[piklist::$prefix . 'user']['ID']))
        {
          $id = (int) $_REQUEST[piklist::$prefix . 'user']['ID'];
        }
        elseif ($pagenow == 'user-edit.php')
        {
          $id = $user_id;
        }
        elseif (in_array($pagenow, array('post.php', 'post-new.php')))
        {
          $id = $post->ID;
        }
        elseif (is_user_logged_in())
        {
          $id = $current_user->ID;
        }

      break;

      case 'option':

        if (isset($field['options']['type']))
        {
          if ($field['options']['type'] == 'blog')
          {
            $id = $blog_id;
          }
          elseif ($field['options']['type'] == 'user')
          {
            if (isset($_REQUEST[piklist::$prefix . 'user']['ID']))
            {
              $id = (int) $_REQUEST[piklist::$prefix . 'user']['ID'];
            }
            elseif ($pagenow == 'user-edit.php')
            {
              $id = $user_id;
            }
          }
        }

      break;
    }

    return $id;
  }

  /**
   * get_field_value
   * Get the field value.
   *
   * @param string $scope The field scope.
   * @param string $field The field field attribute.
   * @param string $type The field type.
   * @param int $id The field object id
   * @param bool $unique Whether to get a unique value or not.
   *
   * @return mixed The field value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_field_value($scope, $field, $type, $id = false, $unique = false)
  {
    global $wpdb;

    if (piklist_admin::is_widget())
    {
      return isset(piklist_widget::widget()->instance[$field['field']]) ? maybe_unserialize(piklist_widget::widget()->instance[$field['field']]) : $field['value'];
    }

    $key = $field['field'] ? $field['field'] : null;
    $prefix = !in_array($scope, array(piklist::$prefix, false)) ? piklist::$prefix : null;

    if ($id || $type == 'option')
    {
      switch ($type)
      {
        case 'post':
        case 'user':
        case 'comment':

          switch ($type)
          {
            case 'user':
              $method = 'get_userdata';
            break;

            case 'comment':
              $method = 'get_comment';
            break;

            default:
              $method = 'get_post';
            break;
          }

          $return = array();
          $ids = is_array($id) ? $id : array($id);

          foreach ($ids as $id)
          {
            $object = $method($id);

            if ($object)
            {
              $allowed = true;

              if ($type == 'post' && $object->post_status == 'auto-draft')
              {
                $allowed = false;
              }

              if ($type == 'user' && $field['field'] == 'user_pass')
              {
                $allowed = false;
              }

              if (!is_wp_error($object) && is_object($object) && $allowed && $field['field'])
              {
                array_push($return, $object->{$field['field']} ? $object->{$field['field']} : $field['value']);
              }
            }
          }

          return !empty($return) ? (count($return) == 1 ? current($return) : $return) : $field['value'];

        break;

        case 'option':

          if ($type != $scope)
          {
            $options = get_option($scope);

            if (stristr($key, ':'))
            {
              $value = piklist::array_path_get($options, explode(':', $key));
            }
            else
            {
              $value = isset($options[$key]) ? $options[$key] : $field['value'];
            }

            return $value;
          }
          else
          {
            if ($field['field'] && !stristr($field['field'], ':'))
            {
              if (!isset($field['options']['type']))
              {
                return get_option($field['field'], $field['value']);
              }
              elseif ($field['options']['type'] == 'blog' && $field['object_id'])
              {
                return get_blog_option($field['object_id'], $field['field'], $field['value']);
              }
              elseif ($field['options']['type'] == 'user' && $field['object_id'])
              {
                return get_user_option($field['field'], $field['object_id']);
              }
              elseif ($field['options']['type'] == 'site')
              {
                $use_cache = isset($field['options']['use_cache']) ? $field['options']['use_cache'] : true;

                return get_site_option($field['field'], $field['value'], $use_cache);
              }
            }
          }

        break;

        case 'taxonomy':

          $key = $field['save_as'] ? $field['save_as'] : $key;

          /**
           * piklist_taxonomy_value_key
           *
           * @since 1.0
           */
          $terms = piklist(wp_get_object_terms($id, $key), apply_filters('piklist_taxonomy_value_key', 'term_id', $key));

          /**
           * piklist_taxonomy_value
           *
           * @since 1.0
           */
          $terms = apply_filters('piklist_taxonomy_value', $terms, $id, $key, $field);

          return !empty($terms) ? $terms : $field['value'];

        break;

        case 'post_meta':
        case 'term_meta':
        case 'user_meta':
        case 'comment_meta':

          $meta_type = substr($type, 0, strpos($type, '_'));

          if ($key)
          {
            $meta_key = strstr($key, ':') ? substr($key, 0, strpos($key, ':')) : $key;
            $meta_key = $field['save_as'] ? $field['save_as'] : $meta_key;
          }
          else
          {
            $meta_key = $scope;
          }

          switch ($type)
          {
            case 'post_meta':

              $meta_table = $wpdb->postmeta;
              $meta_id_field = 'meta_id';
              $meta_id = 'post_id';

            break;

            case 'term_meta':

              $meta_table = $wpdb->termmeta;
              $meta_id_field = 'meta_id';
              $meta_id = 'term_id';

            break;

            case 'comment_meta':

              $meta_table = $wpdb->commentmeta;
              $meta_id_field = 'umeta_id';
              $meta_id = 'comment_id';

            break;

            case 'user_meta':

              $meta_table = $wpdb->usermeta;
              $meta_id_field = 'umeta_id';
              $meta_id = 'user_id';

            break;
          }

          $meta_object_ids = is_array($id) ? $id : array($id);

          $meta_values = array();

          foreach ($meta_object_ids as $meta_object_id)
          {
            if ($field['multiple'])
            {
              $keys = $wpdb->get_results($wpdb->prepare("SELECT {$meta_id_field} FROM $meta_table WHERE meta_key = %s AND $meta_id = %d", $meta_key, $meta_object_id));
              $unique = count($keys) == 1 ? true : $unique;
            }
            elseif ($field['type'] == 'group' && $field['field'])
            {
              $unique = true;
            }

            $data_id = null;
            $save_id = null;

            if (!is_null($field['save_id']))
            {
              $meta = get_metadata_by_mid($meta_type, $field['save_id']);

              if ($meta)
              {
                $meta = maybe_unserialize($meta->meta_value);

                $save_id = $field['save_id'];
              }
              else
              {
                $data_id = add_metadata($meta_type, $meta_object_id, $meta_key, null);
                $save_id = $data_id;
              }
            }
            else
            {
              $meta = get_metadata($meta_type, $meta_object_id, $meta_key, $unique);
              $meta_ids = $wpdb->get_col($wpdb->prepare("SELECT {$meta_id_field} FROM $meta_table WHERE meta_key = %s AND $meta_id = %d", $meta_key, $meta_object_id), ARRAY_N);

              if ($meta_ids)
              {
                $data_id = count($meta_ids) <= 1 ? current($meta_ids) : $meta_ids;
              }
            }

            // Update save and data ids
            foreach (array('data_id', 'save_id') as $variable_id)
            {
              $variable = $variable_id == 'data_id' ? $data_id : $save_id;

              if ($variable)
              {
                $variable = is_array($variable) ? $variable : array($variable);

                if (!isset(self::$field_rendering[$variable_id]) || !self::$field_rendering[$variable_id])
                {
                  self::$field_rendering[$variable_id] = $variable;
                }
                elseif (self::$field_rendering[$variable_id])
                {
                  if (!is_array(self::$field_rendering[$variable_id]))
                  {
                    self::$field_rendering[$variable_id] = array(self::$field_rendering[$variable_id]);
                  }

                  self::$field_rendering[$variable_id] = array_merge(self::$field_rendering[$variable_id], $variable);
                }

                // Remove any booleans from the field as we don't need them anymore
                self::$field_rendering[$variable_id] = array_filter(self::$field_rendering[$variable_id], array(__CLASS__, 'remove_booleans_filter'));
              }
            }

            if (strstr($key, ':'))
            {
              $key = substr($key, strrpos($key, ':') + 1);

              if ($meta)
              {
                $_meta = array();
                foreach ($meta as $index => $value)
                {
                  if (isset($value[$key]))
                  {
                    $_meta[$index] = $value[$key];
                  }
                }
                $meta = $_meta;
              }
            }

            if ($meta != 0)
            {
              if (metadata_exists($meta_type, $meta_object_id, $meta_key) && !$meta)
              {
                $meta = array();
              }
              elseif (!metadata_exists($meta_type, $meta_object_id, $meta_key))
              {
                if ($field['value'])
                {
                  $meta = $field['value'];
                }
                else
                {
                  $meta = null;
                }
              }
            }

            array_push($meta_values, is_array($meta) && count($meta) == 1 && !$field['add_more'] ? current($meta) : $meta);
          }

          return count($meta_object_ids) > 1 ? $meta_values : current($meta_values);

        break;
      }
    }
    elseif (!$id && $key)
    {
      if (isset($_REQUEST[piklist::$prefix . $key]))
      {
        $request_value = $_REQUEST[piklist::$prefix . $key];
      }
      elseif (isset($_REQUEST[piklist::$prefix . $scope][$key]))
      {
        $request_value = $_REQUEST[piklist::$prefix . $scope][$key];
      }
      elseif (isset($_REQUEST[$scope][$key]))
      {
        $request_value = $_REQUEST[$scope][$key];
      }

      if (isset($request_value))
      {
        if (is_array($request_value))
        {
          array_walk_recursive($request_value, array(__CLASS__, 'urldecode_array_values'));
        }
        else
        {
          $request_value = urldecode($request_value);
        }

        return $request_value;
      }
    }

    return isset($field['value']) ? $field['value'] : null;
  }

  public static function remove_booleans_filter($a) {
      return !is_bool($a);
  }

  public static function urldecode_array_values(&$value, $key) {
	 $value = urldecode($value);
  }

  /**
   * the_editor
   * Add the correct editor id to field editors.
   *
   * @param string $editor The editor markup.
   *
   * @return
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function the_editor($editor)
  {
    if (!empty(self::$field_editor_attributes))
    {
      foreach (self::$field_editor_attributes as $editor_id => $attributes)
      {
        if (stristr($editor, 'wp-' . $editor_id))
        {
          return str_replace('<textarea', '<textarea ' . $attributes, $editor);
        }
      }
    }

    return $editor;
  }

  /**
   * get_field_template
   * Get the field teplate.
   *
   * @param string $scope The field scope.
   *
   * @return string The field template.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_field_template($scope = null)
  {
    global $pagenow;

    if (!is_admin())
    {
      $wrapper = 'theme';
    }
    elseif ($scope == 'term_meta')
    {
      $type = piklist_admin::is_term();
      $wrapper = 'term_meta' . ($type === 'new' ? '_new' : '');
    }
    elseif (isset(self::$templates[$scope]))
    {
      $wrapper = $scope;
    }
    else
    {
      if (piklist_admin::is_post())
      {
        $wrapper = 'post_meta';
      }
      elseif (piklist_admin::is_media())
      {
        $wrapper = 'media_meta';
      }
      elseif (piklist_admin::is_widget())
      {
        $wrapper = 'widget';
      }
      elseif ($type = piklist_admin::is_term())
      {
        $wrapper = 'term_meta' . ($type === 'new' ? '_new' : '');
      }
      elseif (piklist_admin::is_user())
      {
        $wrapper = 'user_meta';
      }
      elseif (piklist_admin::is_widget())
      {
        $wrapper = 'widget';
      }
      else
      {
        $wrapper = 'form_table_field';
      }
    }

    return $wrapper;
  }

  /**
   * get_field_scope
   * Get the field scope based off of environment.
   *
   * @return string The field scope.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_field_scope()
  {
    global $pagenow;

    $scope = null;

    if (piklist_admin::is_post())
    {
      $scope = 'post_meta';
    }
    elseif (piklist_admin::is_media())
    {
      $scope = 'post_meta';
    }
    elseif (piklist_admin::is_term())
    {
      $scope = 'term_meta';
    }
    elseif (piklist_admin::is_user())
    {
      $scope = 'user_meta';
    }
    elseif (piklist_admin::is_widget())
    {
      $scope = 'widget';
    }
    elseif ($pagenow == 'admin.php' && isset($_REQUEST['page']) && $_REQUEST['page'] == 'shortcode_editor')
    {
      $scope = 'shortcode';
    }

    return $scope;
  }

  /**
   * get_field_show_value
   * Get the fields display value.
   *
   * @param array $field The field object.
   *
   * @return string The display value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_field_show_value($field)
  {
    extract($field);

    if (isset($value) && !empty($value))
    {
      switch ($type)
      {
        case 'radio':
        case 'checkbox':
        case 'select':

          $value = is_array($value) ? $value : array($value);
          $_value = array();
          foreach ($value as $v)
          {
            if (piklist::is_flat($value))
            {
              if (isset($choices[$v]))
              {
                array_push($_value, $choices[$v]);
              }
            }
            else
            {
              foreach ($v as $_v)
              {
                if (isset($choices[$_v]))
                {
                  array_push($_value, $choices[$_v]);
                }
              }
              array_push($_value, '');
            }
          }
          $value = $_value;

        break;
      }
    }

    return $value;
  }

  /**
   * setup_add_enctype
   * Adds enctype setup for forms.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function setup_add_enctype()
  {
    global $taxnow;

    add_action($taxnow . '_term_new_form_tag', array('piklist_form', 'add_enctype'));
    add_action($taxnow . '_term_edit_form_tag', array('piklist_form', 'add_enctype'));
  }

  /**
   * add_enctype
   * Adds enctype to forms.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function add_enctype()
  {
    echo ' enctype="multipart/form-data" ';
  }

  /**
   * ajax
   * Remote methods for the form class.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function ajax()
  {
    $method = isset($_REQUEST['method']) ? esc_attr($_REQUEST['method']) : false;

    switch ($method)
    {
      case 'field':

        if (isset($_REQUEST['field']) && is_array($_REQUEST['field']))
        {
          $field = $_REQUEST['field'];

          array_walk_recursive($field, array('piklist', 'array_values_strip_all_tags'));
          array_walk_recursive($field, array('piklist', 'array_values_cast'));

          $widget = isset($_REQUEST['widget']) ? $_REQUEST['widget'] : null;

          if ($widget)
          {
            global $wp_widget_factory;

            $wp_widget_factory->widgets[piklist::slug($widget)]->setup(piklist::slug($widget));
          }

          wp_send_json(array(
            'field' => self::render_field($field, true)
            ,'data' => $field
          ));
        }

      break;
    }

    wp_send_json_error();
  }

  /**
   * setup_field
   * Setup the field defaults.
   *
   * @param array $field The initial field object to setup
   *
   * @return array The field object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function setup_field($field)
  {
    $field = wp_parse_args($field, array(
      'field' => null
      ,'scope' => isset($field['scope']) ? $field['scope'] : self::get_field_scope()
      ,'type' => 'text'
      ,'label' => null
      ,'description' => null
      ,'help' => null
      ,'value' => null
      ,'choices' => null
      ,'id' => null
      ,'name' => null
      ,'attributes' => array(
        'class' => array()
      )
      ,'list' => true
      ,'list_type' => null
      ,'list_item_type' => null
      ,'label_position' => 'before'
      ,'label_tag' => true
      ,'position' => null
      ,'columns' => null
      ,'template' => null
      ,'wrapper' => null
      ,'options' => array()
      ,'add_more' => false
      ,'sortable' => isset($field['sortable']) ? $field['sortable'] : (isset($field['add_more']) && is_bool($field['add_more']) && $field['add_more'] ? true : false)
      ,'save_as' => null
      ,'save_id' => null
      ,'conditions' => array()
      ,'required' => false
      ,'validate' => array()
      ,'sanitize' => array()
      ,'request_value' => null
      ,'valid' => true
      ,'new' => false

      ,'capability' => null
      ,'role' => null
      ,'logged_in' => false
      ,'on_post_status' => array()
      ,'redirect' => null
      ,'query' => array()
      ,'tax_query' => array()
      ,'meta_query' => array()

      ,'prefix' => true
      ,'index' => 0
      ,'object_id' => null
      ,'data_id' => null
      ,'relate' => false
      ,'relate_to' => null
      ,'display' => false
      ,'embed' => false
      ,'group_field' => false
      ,'child_field' => false
      ,'child_add_more' => false
      ,'multiple' => in_array($field['type'], self::$field_list_types['multiple_fields']) || (isset($field['attributes']) && is_array($field['attributes']) && in_array('multiple', $field['attributes']))
      ,'errors' => false
    ));

    foreach (array('class', 'wrapper_class') as $attribute)
    {
      if (!isset($field['attributes'][$attribute]))
      {
        $field['attributes'][$attribute] = array();
      }
      elseif (!is_array($field['attributes'][$attribute]))
      {
        $field['attributes'][$attribute] = explode(' ', $field['attributes'][$attribute]);
      }
    }

    return $field;
  }

  /**
   * render_field
   * Render a field from a field object.
   *
   * @param array $field The field object
   * @param bool $return Whether to return the output.
   *
   * @return string The field output if return is true.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function render_field($field, $return = false)
  {
    self::$field_rendering = &$field;

    // Setup the defaults
    $field = self::setup_field($field);

    /**
     * piklist_request_field
     * Filter the request field
     *
     * @param array $field
     *
     * @since 1.0
     */
    $field = apply_filters('piklist_request_field', $field);

    /**
     * piklist_request_field_$scope_$field
     * Filter a specific request field
     *
     * The dynamic portions of the hook name, `$field['scope']` and `$field['field']`,
     * refer to the 'scope' and 'field' parameters, of an individual field.
     *
     * @param array $field
     *
     * @since 1.0
     */
    $field = apply_filters('piklist_request_field_' . $field['scope'] . '_' . $field['field'], $field);

    // Validate field
    if (!$field || !self::validate_field($field) || ($field['embed'] && !$return))
    {
      return false;
    }

    // Get object id if related
    if (self::is_related_field($field) && isset($field['relate']['scope']) && isset($field['relate']['field']))
    {
      $related_field = self::get_related_field($field);

      if ($related_field)
      {
        $field['object_id'] = $related_field['object_id'];
      }
    }

    // Set object id
    if (is_null($field['object_id']))
    {
      $field['object_id'] = self::get_field_object_id($field);
    }

    // Assign a field name and generic scope to generic relate fields
    if (!$field['field'] && is_array($field['relate']) && isset($field['relate']['scope']))
    {
      $field['relate']['field'] = piklist::unique_id();
      $field['relate_to'] = self::get_field_object_id($field);

      if ($field['type'] != 'group')
      {
        $field['field'] = '_' . piklist::$prefix . 'relate_' . $field['relate']['scope'];
      }
    }

    // Handle relate
    if (!$field['group_field'])
    {
      $field = piklist_relate::relate_field($field);
    }

    // Clear object id from relate field it necessary
    if ($field['relate'] && $field['object_id'] == self::get_field_object_id($field))
    {
      $field['object_id'] = null;
    }

    // Set Template
    if (!$field['template'])
    {
      $field['template'] = self::get_field_template($field['scope']);
    }

    if (!in_array($field['type'], self::$field_types_rendered))
    {
      array_push(self::$field_types_rendered, $field['type']);
    }

    if ($field['type'] == 'html')
    {
      $field['display'] = true;

      if (!$field['field'])
      {
        $field['field'] = piklist::unique_id();
      }
    }

    // Manage Classes
    $field['attributes']['class'] = !is_array($field['attributes']['class']) ? explode(' ', $field['attributes']['class']) : $field['attributes']['class'];

    if ($field['type'] == 'hidden' && !array_key_exists('data-piklist-field-addmore-clear', $field['attributes']))
    {
      $field['attributes']['data-piklist-field-addmore-clear'] = false;
    }

    // Add default classes
    array_push($field['attributes']['class'], self::get_field_id(array_merge($field, array('index' => false))));
    array_push($field['attributes']['class'], 'piklist-field-type-' . $field['type']);
    array_push($field['attributes']['class'], 'piklist-field-element');

    // Set Columns
    if (is_numeric($field['columns']) && !$field['child_field'])
    {
      array_push($field['attributes']['wrapper_class'], 'piklist-field-type-group piklist-field-column-' . $field['columns']);
    }

    if (is_numeric($field['columns']))
    {
      $field['attributes']['data-piklist-field-columns'] = $field['columns'];
    }

    if (isset($field['attributes']['columns']) && is_numeric($field['attributes']['columns']))
    {
      array_push($field['attributes']['class'], 'piklist-field-column-' . $field['attributes']['columns']);
      unset($field['attributes']['columns']);
    }

    // Check Statuses - Legacy, these get mapped to conditions post_status_hide, post_status_value
    if (!empty($field['on_post_status']))
    {
      $object = !is_null($field['object_id']) ? get_post($field['object_id'], ARRAY_A) : (isset($GLOBALS['post']) ? (array) $GLOBALS['post'] : null);

      if ($object)
      {
        $status_list = isset($object['post_type']) ? piklist_cpt::get_post_statuses($object['post_type']) : array();
        foreach (array('value', 'hide') as $status_display)
        {
          if (isset($field['on_post_status'][$status_display]))
          {
            $field['on_post_status'][$status_display] = is_array($field['on_post_status'][$status_display]) ? $field['on_post_status'][$status_display] : array($field['on_post_status'][$status_display]);
            foreach ($field['on_post_status'][$status_display] as $_status)
            {
              if (strstr($_status, '--'))
              {
                $status_range = explode('--', $_status);
                $status_range_start = array_search($status_range[0], $status_list);
                $status_range_end = array_search($status_range[1], $status_list);

                if (is_numeric($status_range_start) && is_numeric($status_range_end))
                {
                  $status_slice = array();
                  for ($i = $status_range_start; $i <= $status_range_end; $i++)
                  {
                    array_push($status_slice, $status_list[$i]);
                  }

                  array_splice($field['on_post_status'][$status_display], array_search($_status, $field['on_post_status'][$status_display]), 1, $status_slice);
                }
              }
            }
          }
        }
      }

      foreach ($field['on_post_status'] as $status_display => $statuses)
      {
        array_push($field['conditions'], array(
          'type' => 'post_status_' . $status_display
          ,'value' => $statuses
        ));
      }

      unset($field['on_post_status']);
    }

    // Get errors
    if (piklist_validate::errors() && !$field['group_field'])
    {
      $field['errors'] = piklist_validate::get_errors($field);
    }

    // Highlight errors if needed.
    if ($field['errors'])
    {
      array_push($field['attributes']['class'], 'piklist-error');
    }

    // Get field value
    if (!$field['group_field']
        && $field['value'] !== false
        && !in_array($field['type'], array('button', 'submit', 'reset'))
        && $field['scope'] != piklist::$prefix
        && !$field['new'])
    {
      if (piklist_validate::errors())
      {
        $field['value'] = piklist_validate::get_request_value($field);
      }
      elseif (!piklist_validate::errors() && (!$field['relate'] || ($field['relate'] && substr($field['field'], 0, strlen('_' . piklist::$prefix . 'relate_')) != '_' . piklist::$prefix . 'relate_')))
      {
        $field['value'] = self::get_field_value($field['scope'], $field, $field['scope'], $field['object_id'], false);
      }
    }

    // Check for nested fields
    if ($field['description'])
    {
      $field['description'] = self::render_nested_field($field, $field['description']);
    }

    if (is_array($field['choices']) && !in_array($field['type'], array('select', 'multiselect', 'checkbox-tree')))
    {
      foreach ($field['choices'] as &$choice)
      {
        $choice = self::render_nested_field($field, $choice);
      }
      unset($choice);
    }

    if (!empty($field['conditions']))
    {
      array_push($field['attributes']['class'], 'piklist-field-element-condition');

      foreach ($field['conditions'] as &$condition)
      {
        if (is_array($condition))
        {
          if (!isset($condition['type']) || empty($condition['type']))
          {
            $condition['type'] = 'toggle';
          }
          elseif (piklist_admin::is_post())
          {
            global $post;

            $condition['value'] = is_array($condition['value']) ? $condition['value'] : array($condition['value']);

            if (substr($condition['type'], 0, 12) == 'post_status_' && in_array($post->post_status, $condition['value']))
            {
              if ($condition['type'] == 'post_status_hide')
              {
                return false;
              }
              elseif ($condition['type'] == 'post_status_value')
              {
                $field['display'] = true;
              }
            }
          }

          if (isset($condition['field']))
          {
            $condition['scope'] = isset($condition['scope']) ? $condition['scope'] : $field['scope'];

            $condition_field = array(
              'field' => $condition['field']
              ,'scope' => $condition['scope']
              ,'index' => false
              ,'prefix' => $field['prefix']
            );

            $condition['id'] = self::get_field_id($condition_field);
            $condition['name'] = self::get_field_name($condition_field);
            $condition['reset'] = isset($condition['reset']) ? $condition['reset'] : true;

            if (!in_array('piklist-field-condition', $field['attributes']['class']))
            {
              if (!in_array('piklist-field-condition', $field['attributes']['wrapper_class']))
              {
                array_push($field['attributes']['wrapper_class'], 'piklist-field-condition');
              }

              if (!in_array('piklist-field-condition-' . $condition['type'], $field['attributes']['wrapper_class']))
              {
                array_push($field['attributes']['wrapper_class'], 'piklist-field-condition-' . $condition['type']);
              }
            }
          }
        }
      }
      unset($condition);
    }

    // Check if the field is an add more
    if (($field['add_more'] || $field['sortable']) && !$field['display'])
    {
      $field['attributes']['data-piklist-field-addmore'] = $field['add_more'];
      $field['attributes']['data-piklist-field-sortable'] = $field['sortable'];
      $field['attributes']['data-piklist-field-addmore-actions'] = $field['add_more'];
      $field['attributes']['data-piklist-field-addmore-single'] = $field['add_more'];
    }

    // Check if field is an editor and prepare its additional attributes
    if ($field['type'] == 'editor')
    {
      $editor_id = self::get_field_id($field);
      $editor_id = substr($editor_id, 0, -2);

      self::$field_editor_attributes[$editor_id] = '';

      foreach ($field['attributes'] as $key => $value)
      {
        if (substr($key, 0, strlen('data-piklist-field-')) == 'data-piklist-field-')
        {
          self::$field_editor_attributes[$editor_id] .= $key . '="' . $value . '" ';
        }
      }
    }

    // Set the field template
    if ($field['group_field'] && self::get_field_template($field['scope']) == $field['template'] && (strstr(self::$templates[$field['template']]['template'], '</tr>') || $field['template'] == 'default'))
    {
      $field['child_field'] = true;
      $field['template'] = 'field';
    }
    elseif ($field['type'] == 'hidden' || $field['embed'])
    {
      $field['template'] = 'field';
    }

    $field['wrapper'] = preg_replace(
      array(
        '/ {2,}/'
        ,'/<!--.*?-->|\t|(?:\r?\n[ \t]*)+/s'
      )
      ,array(
        ' '
        ,''
      )
      ,sprintf(self::$templates[$field['template']]['template'], implode(' ', $field['attributes']['wrapper_class']))
    );

    ksort($field);

    /**
     * piklist_pre_render_field
     * Filter the request field before it renders
     *
     * @param array $field
     *
     * @since 1.0
     */
    $field = apply_filters('piklist_pre_render_field', $field);

    /**
     * piklist_pre_render_field_$scope_$field
     * Filter a specific request field before it renders
     *
     * The dynamic portions of the hook name, `$field['scope']` and `$field['field']`,
     * refer to the 'scope' and 'field' parameters, of an individual field.
     *
     * @param array $field
     *
     * @since 1.0
     */
    $field = apply_filters('piklist_pre_render_field_' . $field['scope'] . '_' . $field['field'], $field);

    // Bail from rendering the field if its a display with no value or its already been rendered in this form
    if (!$field || (!$field['group_field'] && (($field['display'] && empty($field['value']) && $field['type'] != 'group'))))
    {
      return false;
    }

    $field_name = $field['field'] ? $field['field'] : piklist::unique_id();

    if ((!$field['scope'] && !isset(self::$fields_rendered[0])) || ($field['scope'] && !array_key_exists($field['scope'], self::$fields_rendered)))
    {
      self::$fields_rendered[$field['scope']] = array();
    }

    array_push(self::$fields_rendered[$field['scope']], $field);

    $field_to_render = self::template_tag_fetch('field_wrapper', $field['wrapper']);

    $rendered_field = do_shortcode($field_to_render);

    switch ($field['position'])
    {
      case 'start':

        $rendered_field = self::template_tag_fetch('field_wrapper', $field['wrapper'], 'start') . $rendered_field;

      break;

      case 'end':

        $rendered_field .= self::template_tag_fetch('field_wrapper', $field['wrapper'], 'end');

      break;

      case 'wrap':

        $rendered_field = self::template_tag_fetch('field_wrapper', $field['wrapper'], 'start') . $rendered_field . self::template_tag_fetch('field_wrapper', $field['wrapper'], 'end');

      break;
    }

    /**
     * piklist_post_render_field
     * Filter the request field after it renders
     *
     * @param array $field
     *
     * @return $rendered_field
     *
     * @since 1.0
     */
    $rendered_field = apply_filters('piklist_post_render_field', $rendered_field, $field);

    /**
     * piklist_post_render_field_$scope_$field
     * Filter a specific request field after it renders
     *
     * The dynamic portions of the hook name, `$field['scope']` and `$field['field']`,
     * refer to the 'scope' and 'field' parameters, of an individual field.
     *
     * @param array $field
     *
     * @return $rendered_field
     *
     * @since 1.0
     */
    $rendered_field = apply_filters('piklist_post_render_field_' . $field['scope'] . '_' . $field['field'], $rendered_field, $field);

    // Update options for editor field
    if ($field['type'] == 'editor')
    {
      self::$fields_rendered = self::update_fields_data(self::$fields_rendered, $field, $field_name, 'options', self::$field_editor_settings, true);
    }

    // Store rendered field
    self::$field_rendered = self::$field_rendering;

    // Reset field rendering
    self::$field_rendering = null;

    // Return the field as requested
    if ($return)
    {
      return $rendered_field;
    }
    else
    {
      echo $rendered_field;
    }
  }

  /**
   * validate_field
   * Check to see if a field should be rendered.
   *
   * @param array $parts Comment block data at the top of the view.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_field($field)
  {
    foreach ($field as $parameter => $value)
    {
      if (!empty($value) && !is_null($value))
      {
        if (!self::validate_field_parameter($parameter, $value))
        {
          return false;
        }
      }
    }

    return true;
  }

  /**
   * validate_field_parameter
   * Check to see if the field parameter passes validation.
   *
   * @param string $parts The parameter name.
   * @param mixes $parts The parameter value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_field_parameter($parameter, $value)
  {
    global $post, $pagenow, $current_screen;

    switch ($parameter)
    {
      case 'capability':

        return empty($value) || piklist_user::current_user_can($value);

      break;

      case 'role':

        return empty($value) || piklist_user::current_user_role($value);

      break;

      case 'logged_in':

        return $value === true ? is_user_logged_in() : true;

      break;

      default:

        /**
         * piklist_validate_field_parameter
         * Add custom part parameters to check.
         *
         * @param $parameter Parameter to check.
         * @param $value Value to compare.
         *
         * @since 1.0
         */
        return apply_filters('piklist_validate_field_parameter', true, $parameter, $value);

      break;
    }
  }

  /**
   * save_fields
   * Render the fields necessary to render and process a form, also set the fields object as a transient for later usage.
   *
   * @param mixed $object Unsed object passed by some actions.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function save_fields($object = null)
  {
    if (!empty(self::$fields_rendered))
    {
      // Fire actions with all rendered fields for the form
      do_action('piklist_save_fields', self::$fields_rendered);

      // Generate a unique id for this field configuration
      $fields_id = md5(serialize(self::$fields_rendered));

      // Save configuration for later use
      if (false === get_transient(piklist::$prefix . $fields_id))
      {
        if (false === set_transient(piklist::$prefix . $fields_id, self::$fields_rendered, 60 * 60 * 24) && wp_using_ext_object_cache())
        {
          wp_using_ext_object_cache(false);

          set_transient(piklist::$prefix . $fields_id, self::$fields_rendered, 60 * 60 * 24);

          wp_using_ext_object_cache(true);
        }
      }

      piklist('field', array(
        'type' => 'hidden'
        ,'scope' => piklist::$prefix
        ,'field' => 'nonce'
        ,'value' => wp_create_nonce('piklist-' . $fields_id)
      ));

      piklist('field', array(
        'type' => 'hidden'
        ,'scope' => piklist::$prefix
        ,'field' => 'fields'
        ,'value' => $fields_id
        ,'attributes' => array(
          'data-piklist-fields' => json_encode(self::$fields_rendered, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_QUOT)
        )
      ));

      self::$fields_rendered = array();
    }
  }

  /**
   * save_fields_actions
   * Add actions necessary to embed save fields.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function save_fields_actions()
  {
    $actions = array(
      'dbx_post_sidebar'
      ,'show_user_profile'
      ,'edit_user_profile'
      ,'piklist_settings_form'
      ,'media_meta'
    );

    foreach ($actions as $action)
    {
      add_action($action, array('piklist_form', 'save_fields'), 101);
    }

    $taxonomies = get_taxonomies('', 'names');
    foreach ($taxonomies as $taxonomy)
    {
      add_action($taxonomy . '_add_form', array('piklist_form', 'save_fields'), 101);
      add_action($taxonomy . '_edit_form', array('piklist_form', 'save_fields'), 101);
    }
  }

  /**
   * update_fields_data
   * Update a field in a fields_data collection.
   *
   * @param array $fields_data Fields Collection.
   * @param array $field Field object.
   * @param string $field_name Field name.
   * @param string $attribute Attribute to update.
   * @param mixed $value Value to update with.
   * @param bool $merge If the value is an array, optionally merge the values.
   *
   * @return bool Whether the rendered configuration was updated
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function update_fields_data($fields_data, $field, $field_name = null, $attribute, $value, $merge = false)
  {
    if ((!$field['scope'] && isset($fields_data[0])) || ($field['scope'] && array_key_exists($field['scope'], $fields_data)))
    {
      if (!is_null($field_name))
      {
        $field['field'] = $field_name;
      }

      foreach ($fields_data[$field['scope']] as &$field_rendered)
      {
        if ($field_rendered['field'] == $field['field'] && self::is_related_field($field_rendered) == self::is_related_field($field))
        {
          $field_rendered[$attribute] = is_array($value) && $merge ? array_merge($field_rendered[$attribute], $value) : $value;
        }
      }
      unset($field_rendered);
    }

    return $fields_data;
  }

  /**
   * get_fields_data
   * Get a field in a fields_data collection.
   *
   * @param array $fields_data Fields Collection.
   * @param string $scope Scope of the field.
   * @param string $field The field name.
   * @param string $related_field The related field to look for.
   *
   * @return mixed The field object found.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_fields_data($fields_data, $scope, $field, $related_field = null)
  {
    if ((!$scope && isset($fields_data[0])) || ($scope && array_key_exists($scope, $fields_data)))
    {
      foreach ($fields_data[$scope] as &$field_rendered)
      {
        if ($field_rendered['field'] == $field && (!$related_field || ($related_field && self::is_related_field($field_rendered) == $related_field)))
        {
          return $field_rendered;
        }
      }
      unset($field_rendered);
    }

    return null;
  }

  /**
   * render_nested_field
   * Render a nested field.
   *
   * @param array $field The field object
   * @param string $content The content of the nest area.
   *
   * @return string The content of the nest area.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function render_nested_field($field, $content)
  {
    preg_match_all("#\[field=(.*?)\]#i", $content, $matches);

    if (!empty($matches[1]))
    {
      for ($i = 0; $i < count($matches[1]); $i++)
      {
        $nested_field = false;

        foreach ($field['fields'] as $f)
        {
          if ($f['field'] == $matches[1][$i])
          {
            $nested_field = $f;
            break;
          }
        }

        if ($nested_field)
        {
          $field['child_field'] = true;

          $field_rendering = self::$field_rendering;

          $content = str_replace(
            $matches[0][$i]
            ,self::render_field(
              wp_parse_args(array(
                  'scope' => $field['scope']
                  ,'field' => $nested_field['field']
                  ,'embed' => true
                  ,'prefix' => $field['prefix']
                  ,'value' => self::get_field_value($field['scope'], $nested_field, isset(self::$scopes[$field['scope']]) ? $field['scope'] : 'option')
                )
                ,$nested_field
              )
              ,true
            )
            ,$content
          );

          self::$field_rendering = $field_rendering;
        }
      }
    }


    return $content;
  }

  /**
   * render_field_assets
   * Render any assets needed by a field.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function render_field_assets()
  {
    global $wp_scripts, $pagenow;

    /**
     * piklist_field_assets
     * Register and Enqueue assets for fields.
     *
     *
     * @since 1.0
     */
    $field_assets = apply_filters('piklist_field_assets', self::$field_assets);

    $field_types_rendered = piklist_admin::is_widget() ? array_keys($field_assets) : self::$field_types_rendered;

    if (!empty($field_types_rendered))
    {
      $jquery_ui_core = $wp_scripts->query('jquery-ui-core');

      wp_register_style('jquery-ui-core', piklist::$add_ons['piklist']['url'] . '/parts/css/jquery-ui/jquery-ui.css', false, $jquery_ui_core->ver);
      wp_register_style('jquery-ui-core-piklist', piklist::$add_ons['piklist']['url'] . '/parts/css/jquery-ui.piklist.css', false, piklist::$version);

      wp_enqueue_style('jquery-ui-core');
      wp_enqueue_style('jquery-ui-core-piklist');

      foreach ($field_types_rendered as $type)
      {
        if (isset($field_assets[$type]))
        {
          if (isset($field_assets[$type]['callback']))
          {
            call_user_func_array($field_assets[$type]['callback'], array($type));
          }
          else
          {
            if (isset($field_assets[$type]))
            {
              if (isset($field_assets[$type]['scripts']))
              {
                foreach ($field_assets[$type]['scripts'] as $script)
                {
                  wp_enqueue_script($script);
                }
              }

              if (isset($field_assets[$type]['styles']))
              {
                foreach ($field_assets[$type]['styles'] as $style)
                {
                  wp_enqueue_style($style);
                }
              }
            }
          }
        }
      }
    }
  }

  /**
   * render_field_custom_assets
   * Render custom assets for fields.
   *
   * @param string $type The field type
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function render_field_custom_assets($type)
  {
    switch ($type)
    {
      case 'colorpicker':

        wp_enqueue_style('wp-color-picker');

        wp_enqueue_script('iris', admin_url('js/iris.min.js'), array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'), false, 1);
        wp_enqueue_script('wp-color-picker', admin_url('js/color-picker.min.js'), array('iris'), false, 1);

        wp_localize_script('wp-color-picker', 'wpColorPickerL10n', array(
          'clear' => __('Clear')
          ,'defaultString' => __('Default')
          ,'pick' => __('Select Color')
        ));

      break;

      default:

        /**
         * piklist_validate_part_parameter
         * Allow custom assets for fields
         *
         * @param $type Field type.
         *
         * @since 1.0
         */
        do_action('piklist_render_field_custom_assets', $type);

      break;
    }
  }

  /**
   * template_tag_fetch
   * Get the field tempalate part.
   *
   * @param string $template_tag The template tag
   * @param string $template The template
   * @param bool $wrapper Use a wrapper
   *
   * @return string The field template part.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function template_tag_fetch($template_tag, $template, $wrapper = false)
  {
    if (!strstr('[', $template) && isset(self::$templates[$template]['template']))
    {
      $template = self::$templates[$template]['template'];
    }

    if ($wrapper == 'start')
    {
      $output = substr($template, 0, strpos($template, '[' . $template_tag));
    }
    elseif ($wrapper == 'end')
    {
      $output = substr($template, strpos($template, '[/' . $template_tag . ']') + strlen('[/' . $template_tag . ']'));
    }
    else
    {
      $output = strstr($template, '[' . $template_tag) ? substr($template, strpos($template, '[' . $template_tag), strpos($template, '[/' . $template_tag . ']') + strlen('[/' . $template_tag . ']') - strpos($template, '[' . $template_tag)) : $template;
    }

    return $output;
  }

  /**
   * template_shortcode
   * Run the field template shortcodes
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
  public static function template_shortcode($attributes, $content = '', $tag)
  {
    extract(shortcode_atts(array(
      'label_tag' => true
    ), $attributes));

    $content = do_shortcode($content);
    $type = isset(self::$field_alias[self::$field_rendering['type']]) ? self::$field_alias[self::$field_rendering['type']] : self::$field_rendering['type'];

    switch ($tag)
    {
      case 'field_label':

        $label_tag = $label_tag === 'false' ? false : true;

        $clone = self::$field_rendering;
        $clone['label_tag'] = $clone['label_tag'] ? $clone['label_tag'] : $label_tag;

        $content = self::template_label($clone);

      break;

      case 'field_description_wrapper':

        $content = isset(self::$field_rendering['description']) && !empty(self::$field_rendering['description']) ? $content : '';

      break;

      case 'field_description':

        $content = self::$field_rendering['display'] ? '' : __(self::$field_rendering['description']);

      break;

      case 'field':

        $content = '';

        if (self::$field_rendering['display'])
        {
          self::$field_rendering['value'] = is_array(self::$field_rendering['value']) && count(self::$field_rendering['value']) == 1 ? current(self::$field_rendering['value']) : self::$field_rendering['value'];
          self::$field_rendering['value'] = self::get_field_show_value(self::$field_rendering);

          $content = self::template_field('show', self::$field_rendering);
        }
        else
        {
          $field_rendering_value = is_array(self::$field_rendering['value']) ? self::$field_rendering['value'] : array(self::$field_rendering['value']);
          if (((isset($field_rendering_value[0]) && null !== $field_rendering_value[0]) && !self::$field_rendering['multiple'])
              || (self::$field_rendering['multiple'] && !piklist::is_flat($field_rendering_value))
              || (in_array(self::$field_rendering['type'], self::$field_list_types['multiple_fields']) && !in_array(self::$field_rendering['type'], self::$field_list_types['multiple_value']) && count($field_rendering_value) > 1)
             )
          {
            $values = self::$field_rendering['value'];
          }
          else
          {
            $values = array(self::$field_rendering['value']);
          }

          if (self::$field_rendering['type'] == 'group')
          {
            $content .= self::template_field_group(self::$field_rendering);
          }
          elseif (is_array($values) && !piklist::is_associative($values))
          {
            $clone = self::$field_rendering;

            for ($index = 0; $index < count($values); $index++)
            {
              $clone['index'] = $index;

              if ($clone['group_field'] && $index > 0)
              {
                $clone['label_tag'] = $clone['label_tag'] ? $clone['label_tag'] : $label_tag;

                $content .= self::template_label($clone);
              }

              if ($clone['errors'] && array_key_exists($clone['index'], $clone['errors']))
              {
                array_push($clone['attributes']['class'], 'piklist-error');
              }

              $clone['value'] = $values[$clone['index']];

              $content .= self::template_field($type, $clone);
            }
          }
          else
          {
            $content .= self::template_field($type, self::$field_rendering);
          }

          // Handle relate groups
          if (self::$field_rendering['relate'] && self::$field_rendering['type'] != 'group' && in_array(self::$field_rendering['scope'], array('post', 'user', 'comment')))
          {
            $context = self::get_field_context(self::$field_rendering);

            if (!isset(self::$related_object_ids[$context]) || !in_array(self::$field_rendering['object_id'], self::$related_object_ids[$context]))
            {
              if (!isset(self::$related_object_ids[$context]))
              {
                self::$related_object_ids[$context] = array();
              }

              array_push(self::$related_object_ids[$context], self::$field_rendering['object_id']);

              $content .= self::render_field(array(
                'type' => 'hidden'
                ,'scope' => self::$field_rendering['scope']
                ,'field' => self::$field_rendering['scope'] == 'comment' ? (self::$field_rendering['relate'] ? 'comment_ID' : 'comment_post_ID') : 'ID'
                ,'relate' => self::$field_rendering['relate']
                ,'object_id' => self::$field_rendering['object_id']
                ,'attributes' => array(
                  'class' => 'piklist-field-part'
                  ,'data-piklist-field-addmore-clear' => true
                )
              ), true);
            }
          }
        }

      break;
    }

    return $content;
  }

  /**
   * template_label
   * Process the field template label
   *
   * @param array $field The field object
   *
   * @return string The field label.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function template_label($field)
  {
    if (empty($field['label']))
    {
      return '';
    }

    if (isset(self::$templates[$field['template']]['label']) && self::$templates[$field['template']]['label'] === false)
    {
      return self::field_label($field);
    }

    $attributes = array(
      'for' => self::get_field_name($field)
      ,'class' => 'piklist-field-part '
                  . 'piklist' . ($field['child_field'] ? '-child' : '') . '-label '
                  . 'piklist-label-position-' . $field['label_position'] . ' '
                  . ($field['type'] == 'group' ? 'piklist-group-label ' : '')
                  . (isset($field['attributes']['label_class']) ? $field['attributes']['label_class'] . ' ' : '')
    );

    return $field['label_tag'] ? '<label ' . self::attributes_to_string($attributes) . '>' . self::field_label($field) . '</label>' : self::field_label($field);
  }

  /**
   * template_field
   * Render the actual field.
   *
   * @param string $type The field type.
   * @param array $field The field object.
   *
   * @return
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function template_field($type, $field)
  {
    return piklist::render('fields/' . $type, $field, true);
  }

  /**
   * template_field_group
   * Render a field group.
   *
   * @param array $field The field object.
   *
   * @return string The content from rendering the group.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function template_field_group($field)
  {
    $content = '';

    $cardinality = 1;

    foreach ($field['fields'] as &$column)
    {
      // Pass through the proper scope and set the field name appropriately
      if (!isset($column['scope']) && $field['scope'])
      {
        $column['scope'] = $field['scope'];
      }

      $column = self::setup_field($column);

      // Pass through common configuration options
      $column['prefix'] = $field['prefix'];
      $column['new'] = $field['new'];
      $column['child_field'] = true;
      $column['child_add_more'] = $field['add_more'] || $field['child_add_more'];

      if (!$column['relate'] && $field['relate'])
      {
        $column['relate_to'] = $field['relate_to'];
        $column['relate'] = $field['relate'];
      }

      array_push($column['attributes']['wrapper_class'], 'piklist-field-part');

      // Check for template
      if (is_null($column['template']))
      {
        if ($column['label_position'] == 'after')
        {
          $column['template'] = 'field_label';
        }
        else
        {
          $column['template'] = 'label_field';
        }
      }

      // If there is an error pass it through
      if ($field['errors'])
      {
        array_push($column['attributes']['class'], 'piklist-error');
      }

      // Set field if needed
      if ($column['type'] == 'html')
      {
        $column['display'] = true;

        if (!isset($column['field']))
        {
          $column['field'] = piklist::unique_id();
        }
      }

      // Set the object id
      if ($field['object_id'])
      {
        $column['object_id'] = $field['object_id'];
      }

      // Relate field
      $column = piklist_relate::relate_field($column);

      if (!$field['field'] && !$column['new'])
      {
        $column['value'] = self::get_field_value($column['scope'], $column, $column['scope'], $column['object_id']);

		$column_value = is_array($column['value']) ? $column['value'] : array($column['value']);
        $cardinality = !$column['multiple'] && count($column_value) > $cardinality && (!is_array($field['value']) || (is_array($field['value']) && !piklist::is_associative($field['value']))) ? count($column_value) : $cardinality;
      }
    }

    unset($column);

    if ($field['field'])
    {
      if (empty($field['value']))
      {
        $cardinality = 1;
      } else {
        $cardinality = count($field['value']) > 1 && (!is_array($field['value']) || (is_array($field['value']) && !piklist::is_associative($field['value']))) ? count($field['value']) : 1;
      }
    }

    $field_rendering = self::$field_rendering;

    for ($index = 0; $index < $cardinality; $index++)
    {
      // Setup group details
      $group_id = piklist::unique_id();
      $group_add_more = false;

      foreach ($field['fields'] as $column)
      {
        // Set index
        $column['index'] = $index;

        // Update object id
        if (is_array($column['object_id']) && isset($column['object_id'][$index]))
        {
          $column['object_id'] = $column['object_id'][$index];
        }

        // Flag this field as a group field
        $column['group_field'] = true;

        // Add specific group index
        $column['attributes']['data-piklist-field-group'] = $group_id;

        // Update fields on child element if its a group
        if ($column['type'] == 'group')
        {
          foreach ($column['fields'] as &$_field)
          {
            $_field['attributes']['data-piklist-field-sub-group'] = $group_id;
          }
          unset($_field);
        }

        // Check sortable
        if (isset($field['attributes']['data-piklist-field-sortable']))
        {
          $column['attributes']['data-piklist-field-sortable'] = $field['attributes']['data-piklist-field-sortable'];
        }

        // Check add more
        if ($column['type'] != 'group' && !$group_add_more && isset($field['attributes']['data-piklist-field-addmore']))
        {
          $group_add_more = true;

          $column['attributes']['data-piklist-field-addmore'] = $field['attributes']['data-piklist-field-addmore'];
          $column['attributes']['data-piklist-field-addmore-actions'] = $field['attributes']['data-piklist-field-addmore-actions'];
        }

        // Check add more type
        if (isset($column['add_more']) && $column['add_more'] && isset($field['attributes']['data-piklist-field-addmore']))
        {
          $column['attributes']['data-piklist-field-addmore-single'] = $field['attributes']['data-piklist-field-addmore'];
        }

        // Check conditions
        if (!empty($field['conditions']))
        {
          if (is_array($column['conditions']))
          {
            $column['conditions'] = array_merge($column['conditions'], $field['conditions']);

            array_push($column['attributes']['class'], 'piklist-field-condition');
          }
          else
          {
            $column['conditions'] = $field['conditions'];
          }
        }

        // Setup child field name if necessary
        if ($field['field'] && !stristr($column['field'], ':'))
        {
          $column['field'] = $field['field'] . ':' . ($group_add_more ? $index . ':' : null) . $column['field'];
        }

        if ($column['type'] != 'html')
        {
          // Get values
          if (piklist_validate::errors())
          {
            $column['errors'] = piklist_validate::get_errors($column);

            $column['value'] = piklist_validate::get_request_value($column);

            // Update cardinality if necessary
            if (!$field['field'])
            {
              $cardinality = !$column['multiple'] && count($column['value']) > $cardinality && (!is_array($field['value']) || (is_array($field['value']) && !piklist::is_associative($field['value']))) ? count($column['value']) : $cardinality;
            }

            if (is_array($column['value']) && (!$column['multiple'] || ($column['multiple'] && !piklist::is_flat($column['value']))))
            {
              $column['value'] = $column['value'][$column['index']];
            }
          }
          elseif (is_array($field['value']))
          {
            $path = explode(':', str_replace($field['field'] . ':', '', $column['field']));

            $column['value'] = piklist::array_path_get($field['value'], $path);
          }
          elseif (!$column['new'])
          {
            $column['value'] = self::get_field_value($column['scope'], $column, $column['scope'], $column['object_id'], false);

            if (is_array($column['value']) && (!$column['multiple'] || ($column['multiple'] && !piklist::is_flat($column['value']))))
            {
              $column['value'] = array_key_exists($column['index'], $column['value']) ? $column['value'][$column['index']] : null;
            }
          }
        }

        $content .= self::render_field($column, true);
      }
    }

    self::$field_rendering = $field_rendering;

    return $content;
  }

  /**
   * field_label
   * Generates the proper markup for 'required' and 'help' paramaters.
   *
   * @param array $field The field object.
   *
   * @return string The text for the label.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function field_label($field)
  {
    $label = '';

    $label .= $field['label'];
    $label .= $field['required'] ? '<span class="piklist-required">*</span>' : null;
    $label .= $field['help'] ? piklist::render('shared/tooltip-help', array('message' => $field['help']), true) : null;

    return __($label);
  }

  /**
   * register_forms
   * Register forms from the forms part folder.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_forms()
  {
    $data = array(
              'title' => 'Title'
              ,'class' => 'Class'
              ,'description' => 'Description'
              ,'method' => 'Method'
              ,'action' => 'Action'
              ,'filter' => 'Filter'
              ,'redirect' => 'Redirect'
              ,'message' => 'Message'
              ,'capability' => 'Capability'
              ,'logged_in' => 'Logged In'
            );

    piklist::process_parts('forms', $data, array('piklist_form', 'register_forms_callback'));
  }

  /**
   * register_forms_callback
   * The callback for successfully registered form parts.
   *
   * @param array $arguments The part object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function register_forms_callback($arguments)
  {
    global $pagenow;

    extract($arguments);

    self::$forms[$id] = $arguments;
  }

  /**
   * render_form
   * Render the form
   *
   * @param string $form The form id.
   * @param string $add_on The add-on where the form resides.
   * @param bool $return Whether to return the output or display.
   *
   * @return string The output of the rendered form.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function render_form($form, $add_on = null, $return = false)
  {
    if (strstr(strtolower($form), '.php'))
    {
      $form = str_replace('.php', '', strtolower($form));
    }

    if (!isset(self::$forms[$form]))
    {
      $_form = null;

      if (!is_null($add_on))
      {
        $_form = piklist::slug($add_on . ' ' . $form);
      }

      if (!isset(self::$forms[$_form]))
      {
        $_form = piklist::slug(piklist_add_on::current() . ' ' . $form);
      }

      if (!isset(self::$forms[$_form]))
      {
        return false;
      }

      $form = $_form;
    }

    $form = self::$forms[$form];

    if (!$form['data']['logged_in'] || ($form['data']['logged_in'] && is_user_logged_in()))
    {
      $output = piklist::render('fields/form', $form, true);

      if ($return)
      {
        return $output;
      }
      else
      {
        echo $output;
      }
    }
    else
    {
      _e('You must be logged in to view this form.', 'piklist');
    }
  }

  /**
   * process_form
   * Process any form submissions.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function process_form()
  {
    if (self::valid() && !piklist_admin::is_widget() && !piklist_admin::is_setting())
    {
      $form_id = isset($_REQUEST[piklist::$prefix]['form_id']) ? $_REQUEST[piklist::$prefix]['form_id'] : false;

      if ($form_id)
      {
        self::$form_id = $form_id;
      }

      if (self::save())
      {
        $redirect = isset($_REQUEST[piklist::$prefix]['redirect']) ? $_REQUEST[piklist::$prefix]['redirect'] : false;

        if ($redirect)
        {
          $redirect = preg_replace('/#.*/', '', $redirect);
          $url_arguments = array();

          foreach (self::$form_submission as $scope => $fields)
          {
            foreach ($fields as $field)
            {
              if ($field['redirect'])
              {
                $url_arguments[$field['name']] = current($field['request_value']);
              }
            }
          }

          if (!empty($url_arguments))
          {
            $redirect .= (stristr($redirect, '?') ? '&' : '?') . http_build_query($url_arguments);
          }

          wp_redirect($redirect);

          exit;
        }
      }
    }
  }

  /**
   * save
   * Save the form data, excludes widgets and settings api fields.
   *
   * @return bool Whether or not data was saved.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function save()
  {
    global $wpdb, $wp_post_types, $wp_taxonomies, $current_user, $pagenow, $taxnow;

    $check = piklist_validate::check();

    // Get our field data after its been sanitized and validated
    if (!isset($_REQUEST[piklist::$prefix]['fields']) || isset($_REQUEST[piklist::$prefix]['filter']) || !$check['valid'] || !in_array($check['type'], array('POST', 'PUT')))
    {
      self::$form_submission = $check['fields_data'];

      return false;
    }
    
    $fields_data = $check['fields_data'];

    // Handle normal file uploads
    foreach ($fields_data as $scope => &$fields)
    {
      if (in_array($scope, array('post_meta', 'term_meta', 'user_meta', 'comment_meta')))
      {
        $meta_type = substr($scope, 0, strpos($scope, '_'));

        foreach ($fields as &$field)
        {
          if (!$field['display'] && array_key_exists(piklist::$prefix . $scope, $_FILES) && array_key_exists($field['field'], $_FILES[piklist::$prefix . $scope]['name']))
          {
            $paths = piklist::array_paths($_FILES[piklist::$prefix . $scope]['name'][$field['field']]);

            $allowed = $field['role'] === null || $field['capability'] === null || current_user_can('upload_files');

            if (!$allowed)
            {
              $field = piklist_validate::add_error($field, 0, __('Insufficient permissions to save this data.', 'piklist'));
            }

            if (!empty($paths) && $allowed)
            {
              if (strstr($paths[0], ':'))
              {
                foreach ($paths as $path)
                {
                  $files_path = explode(':', $path);

                  unset($files_path[count($files_path) - 1]);

                  $files_path = array_merge(array(
                            piklist::$prefix . $scope
                            ,'name'
                          ), explode(':', $field['field'] . ':' . implode(':', $files_path)));

                  $field_name = explode(':', $path);
                  $field_name = $field_name[1];

                  $options = $field['options'];
                  foreach ($field['fields'] as $_field)
                  {
                    if ($_field['field'] == $field_name)
                    {
                      $options = $_field['options'];

                      break;
                    }
                  }

                  $storage = array();
                  $storage_type = isset($field['options']['save']) && $field['options']['save'] == 'url';

                  $upload = self::save_upload($files_path, $storage, $storage_type);

                  if ($upload)
                  {
                    piklist::array_path_set($field['request_value'], explode(':', $path), current($upload));
                  }
                }
              }
              else
              {
                $path = array_merge(array(
                          piklist::$prefix . $scope
                          ,'name'
                        ), array($field['field']));

                $storage = is_array($field['request_value']) ? array_filter($field['request_value']) : $field['request_value'];
                $storage_type = isset($field['options']['save']) && $field['options']['save'] == 'url';

                $upload = self::save_upload($path, $storage, $storage_type);

                if ($upload)
                {
                  $field['request_value'] = $upload;
                }
              }
            }
          }
        }
        unset($field);
      }
    }

    $object_ids = array();

    // Save field data
    foreach ($fields_data as $scope => &$fields)
    {
      if (in_array($scope, array('post', 'user', 'comment')))
      {
        $objects = array();
        foreach ($fields as &$field)
        {
          $allowed = $field['role'] === null || $field['capability'] === null;
          $context = self::get_field_context($field);

          if (!$allowed)
          {
            $field_object_ids = is_array($field['object_id']);

            switch ($scope)
            {
              case 'post':

                if (isset($field['object_id']))
                {
                  $field_object_ids = is_array($field['object_id']) ? $field['object_id'] : array($field['object_id']);

                  foreach ($field_object_ids as $field_object_id)
                  {
                    $allowed = current_user_can('edit_post', $field_object_id);

                    if (!$allowed)
                    {
                      break;
                    }
                  }
                }
                else
                {
                  $allowed = current_user_can('edit_posts');
                }

              break;

              case 'comment':

                if (isset($field['object_id']))
                {
                  $allowed = current_user_can('moderate_comments');
                }

              break;

              case 'user':

                if (isset($field['object_id']))
                {
                  $field_object_ids = is_array($field['object_id']) ? $field['object_id'] : array($field['object_id']);

                  foreach ($field_object_ids as $field_object_id)
                  {
                    $allowed = $current_user->ID == $field_object_id || current_user_can('edit_users');

                    if (!$allowed)
                    {
                      break;
                    }
                  }
                }
                else
                {
                  $allowed = current_user_can('create_users');
                }

              break;
            }

            if (!$allowed)
            {
              $field = piklist_validate::add_error($field, 0, __('Insufficient permissions to save this data.', 'piklist'));
            }
          }

          if ($allowed && $field['field'])
          {
            $values = is_array($field['request_value']) ? $field['request_value'] : array($field['request_value']);
            $id_field = $scope == 'comment' ? ($field['relate'] ? 'comment_ID' : 'comment_post_ID') : 'ID';

            foreach ($values as $index => $value)
            {
              if (is_array($field['object_id']))
              {
                $id = isset($field['object_id'][$index]) ? $field['object_id'][$index] : null;
              }
              else
              {
                $id = isset($field['object_id']) ? $field['object_id'] : null;
              }

              if ($id && !is_null($id) && !$field['relate'])
              {
                $objects[$context][$index][$id_field] = $id;
              }

              if (array_key_exists($index, $values))
              {
                $field_name = strrpos($field['field'], ':') > 0 ? substr($field['field'], strrpos($field['field'], ':') + 1) : $field['field'];

                if ($field_name != $id_field)
                {
                  $objects[$context][$index][$field_name] = $values[$index];
                }
              }
            }
          }
        }

        unset($field);

        foreach ($objects as $context => $ids)
        {
          foreach ($ids as $id => $object)
          {
            if (count($object) == 1 && current($object) == $id)
            {
              unset($objects[$context][$id]);
            }
          }
        }

        foreach ($objects as $context => $ids)
        {
          foreach ($ids as $id => $object)
          {
            $result_id = self::save_object($scope, $object);

            if (!isset($object['comment_ID']) && !isset($object['comment_post_ID']) && !isset($object['ID']))
            {
              foreach ($fields as &$field)
              {
                $field_context = self::get_field_context($field);

                if ($context == $field_context)
                {
                  if ($field['object_id'])
                  {
                    $field['object_id'] = is_array($field['object_id']) ? $field['object_id'] : array($field['object_id']);

                    array_push($field['object_id'], $result_id);
                  }
                  else
                  {
                    $field['object_id'] = $result_id;
                  }
                }
              }
              unset($field);
            }

            if (!isset($object_ids[$context]))
            {
              $object_ids[$context] = $result_id;
            }
            else
            {
              $object_ids[$context] = is_array($object_ids[$context]) ? $object_ids[$context] : array($object_ids[$context]);
              array_push($object_ids[$context], $result_id);
            }
            
            if ($scope == 'user' && isset($object['signon']))
            {
              if ($result_id)
              {
                wp_set_current_user($result_id);
              }
              else
              {
                foreach ($fields as &$field)
                {
                  if (in_array($field['field'], array('user_login', 'user_pass')))
                  {
                    $field = piklist_validate::add_error($field, 0, __('Invalid credentials.', 'piklist'));
                  }
                }
              }
            }
          }
        }
      }
      elseif (in_array($scope, array('post_meta', 'term_meta', 'user_meta', 'comment_meta')))
      {
        $meta_type = substr($scope, 0, strpos($scope, '_'));
        $meta = piklist_meta::get_meta_properties($meta_type);

        foreach ($fields as &$field)
        {
          $field['data_id'] = array();

          if ($meta_type == 'term' && defined('DOING_AJAX') && DOING_AJAX && !$field['object_id'])
          {
            $field['object_id'] = self::get_field_object_id($field);
          }

          $context = self::get_field_context($field);

          if (!$field['object_id'])
          {
            if (isset($object_ids[$context]))
            {
              $field['object_id'] = $object_ids[$context];
            }
            elseif (!$field['object_id'] && isset($object_ids[$meta_type]))
            {
              $field['object_id'] = $object_ids[$meta_type];
            }
          }

          $allowed = $field['role'] === null || $field['capability'] === null;

          if (!$allowed)
          {
            switch ($meta_type)
            {
              case 'post':
              case 'comment':

                $allowed = $field['object_id'] ? current_user_can('edit_' . $meta_type, $field['object_id']) : current_user_can('edit_posts');

              break;

              case 'user':

                $allowed = current_user_can('edit_users') || $current_user->ID == $field['object_id'];

              break;

              case 'term':

                if ($field['object_id'])
                {
                  $taxonomy = $wpdb->get_var($wpdb->prepare("SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id = %d", $field['object_id']));

                  $allowed = current_user_can($wp_taxonomies[$taxonomy]->cap->edit_terms);
                }
                elseif (isset($_POST['taxonomy']))
                {
                  $taxonomy = esc_attr($_POST['taxonomy']);

                  $allowed = current_user_can($wp_taxonomies[$taxonomy]->cap->edit_terms);
                }

              break;
            }

            if (!$allowed)
            {
              $field = piklist_validate::add_error($field, 0, __('Insufficient permissions to save this data.', 'piklist'));
            }
          }

          $save_as = is_string($field['save_as']) ? $field['save_as'] : $field['field'];

          if ($field['object_id'] && !$field['display'] && substr($save_as, 0, strlen('_' . piklist::$prefix . 'relate_')) != '_' . piklist::$prefix . 'relate_' && !strstr($field['field'], ':') && $allowed)
          {
            $grouped = in_array($field['type'], self::$field_list_types['multiple_value']);

            $meta_object_ids = is_array($field['object_id']) ? $field['object_id'] : array($field['object_id']);

            foreach ($meta_object_ids as $index => $meta_object_id)
            {
              $request_value = count($meta_object_ids) > 1 && is_array($field['request_value']) && count($field['request_value']) == count($meta_object_ids) ? $field['request_value'][$index] : $field['request_value'];

              $current_meta_ids = $wpdb->get_col($wpdb->prepare("SELECT $meta->id FROM $meta->table WHERE $meta->object_id = %d AND meta_key = %s", $meta_object_id, $save_as));

              if ($grouped)
              {
                $current_group_meta_ids = $wpdb->get_col($wpdb->prepare("SELECT $meta->id FROM $meta->table WHERE $meta->object_id = %d AND meta_key = %s", $meta_object_id, '_' . piklist::$prefix . $save_as));
              }

              if (!is_null($field['save_id']))
              {
                $current_meta_ids = is_array($field['save_id']) ? $field['save_id'] : array($field['save_id']);
              }

              $current_meta_ids = empty($current_meta_ids) ? null : $current_meta_ids;

              if (is_array($request_value) && $field['type'] != 'group')
              {
                foreach ($request_value as $values)
                {
                  if (is_array($values))
                  {
                    $meta_ids = array();

                    foreach ($values as $value)
                    {
                      if (!empty($current_meta_ids))
                      {
                        $meta_id = array_shift($current_meta_ids);

                        update_metadata_by_mid($meta_type, $meta_id, $value);
                      }
                      else
                      {
                        $meta_id = add_metadata($meta_type, $meta_object_id, $save_as, $value);
                      }

                      if ($meta_id)
                      {
                        array_push($meta_ids, $meta_id);
                      }
                    }

                    if ($grouped)
                    {
                      if (!empty($current_group_meta_ids))
                      {
                        $group_meta_id = array_shift($current_group_meta_ids);

                        update_metadata_by_mid($meta_type, $group_meta_id, $meta_ids);
                      }
                      else
                      {
                        add_metadata($meta_type, $meta_object_id, '_' . piklist::$prefix . $save_as, $meta_ids);
                      }
                    }

                    $field['data_id'] = array_merge($field['data_id'], $meta_ids);
                  }
                  else
                  {
                    if (!empty($current_meta_ids))
                    {
                      $meta_id = array_shift($current_meta_ids);

                      update_metadata_by_mid($meta_type, $meta_id, $values);
                    }
                    else
                    {
                      $meta_id = add_metadata($meta_type, $meta_object_id, $save_as, $values);
                    }

                    array_push($field['data_id'], $meta_id);
                  }
                }

                if (!empty($current_group_meta_ids))
                {
                  foreach ($current_group_meta_ids as $current_group_meta_id)
                  {
                    delete_metadata_by_mid($meta_type, $current_group_meta_id);
                  }
                }
              }
              else
              {
                if (!empty($current_meta_ids))
                {
                  $meta_id = array_shift($current_meta_ids);

                  update_metadata_by_mid($meta_type, $meta_id, $request_value);
                }
                else
                {
                  $meta_id = add_metadata($meta_type, $meta_object_id, $save_as, $request_value);
                }

                array_push($field['data_id'], $meta_id);
              }

              if (!empty($current_meta_ids))
              {
                foreach ($current_meta_ids as $current_meta_id)
                {
                  delete_metadata_by_mid($meta_type, $current_meta_id);
                }
              }
            }
          }
        }

        unset($field);
      }
      elseif ($scope == 'taxonomy')
      {
        foreach ($fields as &$field)
        {
          if (!$field['display'])
          {
            $taxonomy = is_string($field['save_as']) ? $field['save_as'] : $field['field'];
            $append = isset($field['options']['append']) && is_bool($field['options']['append']) ? $field['options']['append'] : false;

            $context = self::get_field_context($field);

            if (!$field['object_id'])
            {
              if (isset($object_ids[$context]))
              {
                $field['object_id'] = $object_ids[$context];
              }
              elseif (isset($object_ids[$wp_taxonomies[$taxonomy]->object_type[0]]))
              {
                $field['object_id'] = $object_ids[$wp_taxonomies[$taxonomy]->object_type[0]];
              }
            }

            $allowed = is_null($field['role']) && is_null($field['capability']);

            if (!$allowed)
            {
              switch ($wp_taxonomies[$taxonomy]->object_type[0])
              {
                case 'user':

                  $allowed = current_user_can('edit_user', $field['object_id']) && current_user_can($wp_taxonomies[$taxonomy]->cap->assign_terms);

                break;

                default:

                  $allowed = current_user_can($wp_taxonomies[$taxonomy]->cap->assign_terms);

                break;
              }

              if (!$allowed)
              {
                $field = piklist_validate::add_error($field, 0, __('Insufficient permissions to save this data.', 'piklist'));
              }
            }

            if ($allowed)
            {
              $taxonomy_object_ids = is_array($field['object_id']) ? $field['object_id'] : array($field['object_id']);

              foreach ($taxonomy_object_ids as $index => $taxonomy_object_id)
              {
                $request_value = count($taxonomy_object_ids) > 1 && is_array($field['request_value']) && count($field['request_value']) == count($taxonomy_object_ids) ? $field['request_value'][$index] : $field['request_value'];

                $all_terms = array();

                if ($request_value)
                {
                  $request_value = is_array($request_value) ? $request_value : array($request_value);

                  foreach ($request_value as $terms)
                  {
                    if (!empty($terms))
                    {
                      $terms = !is_array($terms) ? array($terms) : $terms;

                      foreach ($terms as $term)
                      {
                        if (!in_array($term, $all_terms))
                        {
                          array_push($all_terms, is_numeric($term) ? (int) $term : $term);
                        }
                      }
                    }
                  }
                }

                wp_set_object_terms($taxonomy_object_id, $all_terms, $field['field'], $append);

                clean_object_term_cache($taxonomy_object_id, $field['field']);
              }
            }
            else
            {
              $field = piklist_validate::add_error($field, 0, __('Insufficient permissions to save this data.', 'piklist'));
            }
          }
        }

        unset($field);
      }
      elseif ($scope == 'option')
      {
        foreach ($fields as &$field)
        {
          $allowed = $field['role'] === null || $field['capability'] === null;
          
          if (!$allowed)
          {
            $allowed = current_user_can('manage_options');
          }

          if ($allowed)
          {
            if ($field['field'] && !stristr($field['field'], ':'))
            {
              $value = $field['request_value'];

              if (is_array($value) && piklist::is_flat($value) && count($value) == 1)
              {
                $value = current($value);
              }

              if (!isset($field['options']['type']))
              {
                $auto_load = isset($field['options']['auto_load']) ? $field['options']['auto_load'] : null;

                update_option($field['field'], $value, $auto_load);
              }
              elseif ($field['options']['type'] == 'blog' && $field['object_id'])
              {
                $deprecated = isset($field['options']['deprecated']) ? $field['options']['deprecated'] : null;

                update_blog_option($field['object_id'], $field['field'], $value, $deprecated);
              }
              elseif ($field['options']['type'] == 'user' && $field['object_id'])
              {
                $global = isset($field['options']['global']) ? $field['options']['global'] : false;

                update_user_option($field['object_id'], $field['field'], $value, $global);
              }
              elseif ($field['options']['type'] == 'site')
              {
                update_site_option($field['field'], $value);
              }
            }
          }
          else
          {
            $field = piklist_validate::add_error($field, 0, __('Insufficient permissions to save this data.', 'piklist'));
          }
        }

        unset($field);
      }
      
      /**
       * piklist_save_field
       * Fires after fields have been saved
       *
       * @param $type Field type.
       *
       * @since 1.0
       */
      do_action('piklist_save_field', $scope, $fields);

      /**
       * piklist_save_field-{$scope}
       * Fires after fields have been saved and is scope specific
       *
       * @param $type Field type.
       *
       * @since 1.0
       */
      do_action("piklist_save_field-{$scope}", $fields);
    }

    self::$form_submission = $fields_data;

    self::relate();

    return true;
  }

  /**
   * save_upload
   * Save any FILES uploaded by a file field.
   *
   * @param array $path The file paths
   * @param array $storage The object to store the object ids in to return.
   *
   * @return array The object ids.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function save_upload($path, $storage = array(), $url = false)
  {
    $files = $_FILES;

    if (!function_exists('media_handle_sideload'))
    {
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      require_once(ABSPATH . 'wp-admin/includes/file.php');
      require_once(ABSPATH . 'wp-admin/includes/media.php');
    }

    $paths = array();
    $paths['name'] = $path;
    $path[1] = 'size';
    $paths['size'] = $path;
    $path[1] = 'tmp_name';
    $paths['tmp_name'] = $path;
    $path[1] = 'error';
    $paths['error'] = $path;

    $codes = piklist::array_path_get($files, $paths['error']);
    $names = piklist::array_path_get($files, $paths['name']);
    $sizes = piklist::array_path_get($files, $paths['size']);
    $tmp_names = piklist::array_path_get($files, $paths['tmp_name']);

    if (!$storage)
    {
      $storage = array();
    }

    foreach ($codes as $set => $code)
    {
      if (in_array($code, array(UPLOAD_ERR_OK, 0), true))
      {
        $attach_id = media_handle_sideload(
                        array(
                          'name' => $names[$set]
                          ,'size' => $sizes[$set]
                          ,'tmp_name' => $tmp_names[$set]
                        )
                        ,0
                      );

        if (!is_wp_error($attach_id))
        {
          array_push($storage, $url ? wp_get_attachment_url($attach_id) : $attach_id);
        }
      }
    }

    return $storage;
  }

  /**
   * save_object
   * Save a core WordPress object (post, user, comment)
   *
   * @param string $type The type of object
   * @param array $data The object
   *
   * @return int The object id.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function save_object($type, $data)
  {
    global $wpdb, $current_user;

    $object = array();

    if (isset(self::$scopes[$type]))
    {
      foreach (self::$scopes[$type] as $allowed)
      {
        /**
         * There used to be an && !empty($data[$allowed]) here, but it caused a bug
         * that fields (e.g. post_content) could not be returned to an empty value.
         * @see piklist_field::save same issue
         * @TODO Determine why !empty($data[$allowed]) was introduced
         */
        if (isset($data[$allowed]))
        {
          $object[$allowed] = is_array($data[$allowed]) && count($data[$allowed]) == 1 ? current($data[$allowed]) : $data[$allowed];
        }
      }
    }

    switch ($type)
    {
      case 'post':

        $id = isset($object['ID']) ? wp_update_post($object) : wp_insert_post($object);

      break;

      case 'comment':

        if (!empty($object['comment_content']))
        {
          $id = isset($object['comment_ID']) ? wp_update_comment($object) : wp_insert_comment($object);
        }

      break;

      case 'user':

        if (isset($object['signon']))
        {
          $user = wp_signon(array(
                    'user_login' => $object['user_login']
                    ,'user_password' => $object['user_pass']
                    ,'remember' => $object['rememberme']
                  ), is_ssl());
           
          if (!is_wp_error($user))
          {
            $id = $user->ID;
          }
          
          break;
        }
      
        $re_auth_cookie = false;

        if (isset($object['user_pass']) && empty($object['user_pass']))
        {
          unset($object['user_pass']);
        }

        if (isset($object['ID']) && isset($object['user_login']) && !empty($object['user_login']))
        {
          $user_login = $object['user_login'];
          $increment = 0;

          $user_login_check = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_login = %s LIMIT 1" , $user_login, $user_login));

          if ($user_login_check != $object['ID'])
          {
            while ($user_login_check)
            {
              $user_login = $object['user_login'] . '-' . ++$increment;
              $user_login_check = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->users WHERE user_login = %s LIMIT 1" , $user_login, $user_login));
            }

            $result = $wpdb->query($wpdb->prepare("UPDATE $wpdb->users SET user_login = %s WHERE ID = %d ", $user_login, $object['ID']));

            unset($object['user_login']);

            if (!isset($object['user_nicename']))
            {
              $object['user_nicename'] = $user_login;
            }

            $re_auth_cookie = true;
          }
        }

        if (isset($object['ID']))
        {
          $id = wp_update_user($object);
        }
        elseif (isset($object['user_pass']) && isset($object['user_login']))
        {
          $id = wp_insert_user($object);
        }

        if (isset($id) && !is_wp_error($id))
        {
          if ($re_auth_cookie)
          {
            wp_set_auth_cookie($id);
          }

          if (isset($object['user_role']))
          {
            piklist_user::multiple_roles($id, $object['user_role']);
          }
        }

      break;
    }

    return isset($id) ? $id : false;
  }

  /**
   * relate
   * Process the save of relate data for objects.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function relate()
  {
    $related = array();

    foreach (self::$form_submission as $fields)
    {
      foreach ($fields as $field)
      {
        if ($field['relate_to'] && $field['field'])
        {
          if (!isset($related[$field['relate_to']]))
          {
            $related[$field['relate_to']] = array();
          }

          $is_meta = in_array($field['scope'], array('post_meta', 'user_meta', 'comment_meta')) && substr($field['field'], 0, strlen('_' . piklist::$prefix . 'relate_')) == '_' . piklist::$prefix . 'relate_';

          if (!isset($field['relate']['remove']))
          {
            $field['relate']['remove'] = array();
          }

          if ($is_meta && $field['value'])
          {
            $field['relate']['remove'] = array_diff(is_array($field['value']) ? $field['value'] : array($field['value']), is_array($field['request_value']) ? $field['request_value'] : array($field['request_value']));
          }
          elseif (!$is_meta && ($field['add_more'] || $field['child_add_more']) && !array_key_exists($field['index'], $field['request_value']))
          {
            array_push($field['relate']['remove'], $field['object_id']);
          }

          $relate = array(
            'scope_to' => $field['relate']['scope']
            ,'from' => $is_meta ? $field['request_value'] : $field['object_id']
            ,'scope_from' => $is_meta ? str_replace('_meta', '', $field['scope']) : $field['scope']
            ,'remove' => isset($field['relate']['remove']) ? $field['relate']['remove'] : array()
          );

          if (!in_array($relate, $related[$field['relate_to']]))
          {
            array_push($related[$field['relate_to']], $relate);
          }
        }
      }
    }

    foreach ($related as $to => $objects)
    {
      $active_related = array();

      foreach ($objects as $object)
      {
        $meta_key = '_' . piklist::$prefix . 'relate_' . $object['scope_from'];

        $froms = is_array($object['from']) ? $object['from'] : array($object['from']);

        foreach ($froms as $from)
        {
          if (!in_array($from, $object['remove']))
          {
            $current_related = get_metadata($object['scope_to'], $from, $meta_key);

            array_push($active_related, $from);

            if ($from && !$current_related || ($current_related && !in_array($to, $current_related)))
            {
              add_metadata($object['scope_to'], $from, $meta_key, $to);
            }
          }
        }
      }

      foreach ($objects as $object)
      {
        foreach ($object['remove'] as $remove)
        {
          if (!in_array($remove, $active_related))
          {
            delete_metadata($object['scope_to'], $remove, $meta_key, $to);
          }
        }
      }
    }
  }

  /**
   * is_related_field
   * Check if the field is related to another field
   *
   * @param array $field The field object.
   *
   * @return bool The attributes string.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function is_related_field($field)
  {
    if (isset($field['relate']) && is_array($field['relate']))
    {
      if (isset($field['relate']['id']))
      {
        return $field['relate']['id'];
      }
      elseif (isset($field['relate']['field']))
      {
        return $field['relate']['field'];
      }
    }

    return false;
  }

  /**
   * get_field_context
   * Get the field context.
   *
   * @param array $field The field object.
   * @param boolean $render Whether or not this is for rendering.
   *
   * @return string The field context
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_field_context($field, $render = false)
  {
    if (($context = self::is_related_field($field)) !== false)
    {
      return $context;
    }

    $context = $field['scope'];

    if (!$render)
    {
      switch ($field['scope'])
      {
        case 'taxonomy':

          $context = 'post';

        break;
      }
    }

    return $context;
  }

  /**
   * get_related_field
   * Get the related field object.
   *
   * @param array $field The field object.
   *
   * @return array $field The field object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_related_field($field)
  {
    $related_field = self::get_fields_data(self::$fields_rendered, $field['relate']['scope'], $field['relate']['field']);

    if (!$related_field && isset(self::$fields_rendered[$field['scope']]))
    {
      foreach (self::$fields_rendered[$field['scope']] as $_field)
      {
        if (is_array($_field['relate']) && isset($_field['relate']['id']) && $_field['relate']['id'] == $field['relate']['field'])
        {
          $related_field = $_field;

          break;
        }
      }
    }

    return $related_field;
  }

  /**
   * attributes_to_string
   * Convert an array of html attributes to a string.
   *
   * @param array $attributes The html attributes.
   *
   * @return string The attributes string.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function attributes_to_string($attributes = array(), $exclude = array('id', 'name', 'value', 'wrapper_class'))
  {
    $attribute_string = '';

    if (!is_array($attributes))
    {
      return $attribute_string;
    }

    foreach ($attributes as $key => $value)
    {
      if (isset($value) && ($value !== ''))
      {
        if (is_numeric($key) && !in_array($value, $exclude))
        {
          $attribute_string .= ($value) . ' ';
        }
        else if (!in_array($key, $exclude))
        {
          $attribute_string .= $key . '="' . esc_attr(is_array($value) ? implode(' ', $value) : ($value === false ? 0 : $value)) . '" ';
        }
      }
    }

    return $attribute_string;
  }

  /**
   * tiny_mce_settings
   * Store the tiny_mce settings of an editor as its rendered
   *
   * @param array $settings The editor settings object.
   * @param string $editor_id The editor id.
   *
   * @return array The editor settings object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function tiny_mce_settings($settings, $editor_id)
  {
    self::set_editor_settings('tiny_mce', $settings, $editor_id);

    return $settings;
  }

  /**
   * quicktags_settings
   * Store the qtag settings of an editor as its rendered
   *
   * @param array $settings The editor settings object.
   * @param string $editor_id The editor id.
   *
   * @return array The editor settings object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function quicktags_settings($settings, $editor_id)
  {
    self::set_editor_settings('quicktags', $settings, $editor_id);

    return $settings;
  }

  /**
   * set_editor_settings
   * Set the editor settings for later use.
   *
   * @param string $type The type of settings
   * @param array $settings The editor settings object.
   * @param string $editor_id The editor id.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function set_editor_settings($type, $settings, $editor_id)
  {
    if (!empty($settings))
    {
      $_settings = self::get_editor_settings($settings);

      $settings = array();
      $settings[$editor_id] = $_settings;
    }
    else
    {
      $settings = array();
    }

    if ($type == 'tiny_mce')
    {
      self::$field_editor_settings['mceInit'] = $settings[$editor_id];
    }
    elseif ($type == 'quicktags')
    {
      self::$field_editor_settings['qtInit'] = $settings[$editor_id];
    }
  }

  /**
   * get_editor_settings
   * Get the editor settings from a string.
   *
   * @param string $settings The editor settings object.
   *
   * @return array The converted settings.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_editor_settings($settings)
  {
    $objects = array();
    $new_settings = array();

    foreach ($settings as $key => $value)
    {
      if (is_bool($value))
      {
        $new_settings[$key] = $value ? true : false;

        continue;
      }
      elseif (!empty($value) && is_string($value) && (('{' == $value{0} && '}' == $value{strlen($value) - 1}) || ('[' == $value{0} && ']' == $value{strlen($value) - 1}) || preg_match('/^\(?function ?\(/', $value)))
      {
        $new_settings[$key] = $value;

        array_push($objects, $key);

        continue;
      }

      $new_settings[$key] = $value;
    }

    foreach ($objects as $object)
    {
      if (isset($new_settings[$object]))
      {
        $decoded = json_decode($new_settings[$object]);

        if (empty($decoded))
        {
          $decoded = preg_replace('/(\w+)\s{0,1}:/', '"\1":', str_replace(array("\r\n", "\r", "\n", "\t"), '', str_replace("'", '"', stripslashes($new_settings[$object]))));
          $decoded = json_decode($decoded);
        }

        $new_settings[$object] = $decoded;
      }
    }

    return $new_settings;
  }

  /**
   * remove_theme_css
   * Remove the editor css from editors that aren't for post content.
   *
   * @param array $mceInit The tinymce init object.
   * @param string $editor_id The editor id.
   *
   * @return array The tinymce init object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function remove_theme_css($mceInit, $editor_id)
  {
    $prefix = piklist::$prefix . 'post_post_content';

    if ($editor_id != 'content' && substr($editor_id, 0, strlen($prefix)) !== $prefix)
    {
      $stylesheets = get_editor_stylesheets();

      if (isset($mceInit['content_css']))
      {
        $content_css = explode(',', $mceInit['content_css']);
        $content_css = array_diff($content_css, $stylesheets);
      }
      else
      {
        $content_css = array();
      }

      array_push($content_css,  piklist::$add_ons['piklist']['url'] . '/parts/css/tinymce-piklist.css');

      $mceInit['content_css'] = implode(',', $content_css);
    }

    return $mceInit;
  }

  /**
   * render_assets
   * Check if assets need to be rendered for fields.
   *
   * @return bool Whether or not assets need to be rendered.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function render_assets()
  {
    return empty(self::$field_types_rendered) ? false : true;
  }

  /**
   * notices
   * Handle any form notices for forms with piklist fields.
   *
   * @param string $form_id The form id.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function notices($form_id = null)
  {
    if (!empty(self::$form_submission)
        && self::$form_id
        && self::$form_id == $form_id
        && isset(self::$forms[self::$form_id])
        && !empty(self::$forms[self::$form_id]['data']['message'])
      )
    {
      piklist::render('shared/notice', array(
        'id' => 'piklist_form_notice'
        ,'notice_type' => 'update'
        ,'dismiss' => is_admin()
        ,'content' => self::$forms[self::$form_id]['data']['message']
      ));
    }
  }

  /**
   * wp_enqueue_media
   * Enqueues media if necessary.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_enqueue_media()
  {
    global $post_ID;

    if (is_admin())
    {
      wp_enqueue_media(array(
        'post' => $post_ID
      ));
    }
  }
}
