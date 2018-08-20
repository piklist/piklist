<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Piklist_Validate
 * Controls validation and sanitization rules.
 *
 * @package     Piklist
 * @subpackage  Validate
 * @copyright   Copyright (c) 2012-2018, Piklist, LLC.
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */
class Piklist_Validate
{
  /**
   * @var bool Whether the submission passed validation.
   * @access private
   */
  private static $valid = true;

  /**
   * @var bool Whether a form submission was checked.
   * @access private
   */
  private static $checked = false;

  /**
   * @var false|array The form submission.
   * @access private
   */
  private static $form_submission = false;

  /**
   * @var false|string The form id.
   * @access private
   */
  private static $id = false;

  /**
   * @var string The validation parameter.
   * @access private
   */
  private static $parameter = 'invalid';

  /**
   * @var array Registered validation rules.
   * @access private
   */
  private static $validation_rules = array();

  /**
   * @var array Registered sanitization rules.
   * @access private
   */
  private static $sanitization_rules = array();

  /**
   * @var array All fields data that has passed through validation.
   * @access private
   */
  private static $processed = array();

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
    add_action('init', array('piklist_validate', 'init'));
    add_action('admin_head', array('piklist_validate', 'admin_head'));
    add_action('admin_notices', array('piklist_validate', 'notices'));
    add_action('piklist_notices', array('piklist_validate', 'notices'));
    add_action('wp_ajax_piklist_validate', array('piklist_validate', 'ajax'));
    add_action('wp_ajax_nopriv_piklist_validate', array('piklist_validate', 'ajax'));
    add_action('wp_loaded', array('piklist_validate', 'set_data'), 99999);

    add_filter('piklist_assets_localize', array('piklist_validate', 'assets_localize'));
    add_filter('wp_redirect', array('piklist_validate', 'wp_redirect'), 10, 2);
    add_filter('piklist_validation_rules', array('piklist_validate', 'validation_rules'));
    add_filter('piklist_sanitization_rules', array('piklist_validate', 'sanitization_rules'));
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
    /**
     * piklist_validation_rules
     * Add your own validation rules.
     *
     * @since 1.0
     */
    self::$validation_rules = apply_filters('piklist_validation_rules', self::$validation_rules);

    /**
     * piklist_sanitization_rules
     * Add your own sanitization rules.
     *
     * @since 1.0
     */
    self::$sanitization_rules = apply_filters('piklist_sanitization_rules', self::$sanitization_rules);

    self::get_data();
  }

  /**
   * wp_redirect
   * Keeps error persistant across redirects.
   *
   * @param string $location The redirect location.
   * @param int $status Status code.
   *
   * @return string The redirect location.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function wp_redirect($location, $status)
  {
    global $pagenow;

    $parameter = piklist::$prefix . '[' . self::$parameter . ']';

    if (self::$id && $status == 302)
    {
      if (in_array($pagenow, array('edit-tags.php', 'term.php')))
      {
        $location = preg_replace('/&?' . $parameter . '=[^&]*/', '', $_SERVER['HTTP_REFERER']);
      }

      $location .= (stristr($location, '?') ? (substr($location, -1) == '&' ? '' : '&') : '?') . $parameter . '=' . self::$id;
    }
    else
    {
      if (in_array($pagenow, array('edit-tags.php', 'term.php')))
      {
        foreach (array('action', 'tag_ID', $parameter) as $variable)
        {
          $location = preg_replace('/&?' . $variable . '=[^&]*/', '', $location);
        }
      }
    }

    return $location;
  }

  /**
   * admin_head
   * Render admin notices for validation errors.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function admin_head()
  {
    if (self::$form_submission)
    {
      piklist::render('shared/notice-updated-hide');
    }
  }

  /**
   * ajax
   * Provides some ajax methods for validation
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
      case 'check':

        if (isset($_REQUEST['data']))
        {
          parse_str($_REQUEST['data'], $data);

          array_walk_recursive($data, array('piklist', 'array_values_strip_all_tags'));

          extract($data[piklist::$prefix]);

          if ($nonce && $fields && wp_verify_nonce($nonce, 'piklist-' . $fields))
          {
            $check = self::check($data, $fields, true);

            if ($check['valid'] === true)
            {
              wp_send_json(array(
                'success' => true
              ));
            }
            else
            {
              $errors = array();
              $error_indexes_per_field = array();

              self::$form_submission = $check['fields_data'];

              foreach (self::$form_submission as $fields)
              {
                foreach ($fields as $field)
                {
                  if ($field['errors'])
                  {
                    array_push($errors, $field['name']);
                    $error_indexes_per_field[$field['name']] = array_keys($field['errors']);
                  }
                }
              }

              wp_send_json_error(array(
                'errors' => $errors
                ,'error_indexes_per_field' => $error_indexes_per_field
                ,'notice' => self::notices(null, true)
              ));
            }
          }
        }

      break;
    }

    wp_send_json_error();
  }

  /**
   * notices
   * Render notices for each individual field that has errors.
   *
   * @param string $form_id The form id.
   * @param bool $fetch Whether to return the notice or render it.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function notices($form_id = null, $fetch = false)
  {
    if (!self::$form_submission)
    {
      return;
    }

    $submitted_form_id = piklist_form::get('form_id');

    $errors = array();

    foreach (self::$form_submission as $fields)
    {
      foreach ($fields as $field)
      {
        if ($field['errors'])
        {
          foreach ($field['errors'] as $error_messages)
          {
            $errors = array_merge($errors, $error_messages);
          }
        }
      }
    }

    $errors = array_unique($errors);

    if (((($submitted_form_id && $form_id == $submitted_form_id) || !$submitted_form_id) && !empty($errors)) || $fetch)
    {
      $rendered_errors = array();

      $content = '<ul>';

      foreach ($errors as $error)
      {
        $content .= '<li>' . $error . '</li>';
      }

      $content .= '</ul>';

      $arguments = array(
                     'id' => 'piklist_validation_error'
                     ,'notice_type' => 'error'
                     ,'content' => $content
                   );

      if ($fetch)
      {
        return piklist::render('shared/notice', $arguments, true);
      }
      else
      {
        piklist::render('shared/notice', $arguments);
      }
    }
  }

  /**
   * check
   * Run all validation and sanitization checks against the rendered fields.
   *
   * @param array $stored_data The data to parse if the REQUEST object is not used by default.
   * @param string $fields_id The fields id.
   *
   * @return array Results of the check.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function check($stored_data = null, $fields_id = null)
  {
    self::$valid = true;
    self::$checked = false;

    if (!$fields_id)
    {
      $fields_id = isset($_REQUEST[piklist::$prefix]['fields']) ? esc_attr($_REQUEST[piklist::$prefix]['fields']) : null;
    }

    if (!$fields_id || !$fields_data = get_transient(piklist::$prefix . $fields_id))
    {
      return false;
    }

    $clones = array();

    // Sort scopes so they are executed in proper order
    ksort($fields_data);
    $field_data_keys_append = array();
    $field_data_keys = array_keys($fields_data);
    foreach ($field_data_keys as $index => $field_data_key)
    {
      if (in_array($field_data_key, array('taxonomy', 'post_meta', 'user_meta', 'term_meta', 'comment_meta')))
      {
        unset($field_data_keys[$index]);
        array_push($field_data_keys_append, $field_data_key);
      }
    }
    $field_data_keys = array_merge($field_data_keys, $field_data_keys_append);
    $fields_data = array_merge(array_flip($field_data_keys), $fields_data);

    foreach ($fields_data as $type => &$fields)
    {
      foreach ($fields as &$field)
      {
        if (!is_null($stored_data)) // Widgets, Settings API
        {
          if ($field['prefix'] && isset($stored_data[piklist::$prefix . $field['scope']]))
          {
            $request_data = $stored_data[piklist::$prefix . $field['scope']];
          }
          elseif (!$field['prefix'] && isset($stored_data[$field['scope']]))
          {
            $request_data = $stored_data[$field['scope']];
          }
          else
          {
            $request_data = $stored_data;
          }
        }
        else // Everything else, pulled from request object
        {
          if (($scope_prefix = piklist_form::is_related_field($field)) != false && isset($_REQUEST[piklist::$prefix . $scope_prefix]))
          {
            $request_data = $_REQUEST[piklist::$prefix . $scope_prefix];
          }
          elseif ($field['scope'] && isset($_REQUEST[piklist::$prefix . $field['scope']]))
          {
            $request_data = $_REQUEST[piklist::$prefix . $field['scope']];
          }
          elseif ($field['scope'] && $field['scope'] == piklist::$prefix && isset($_REQUEST[piklist::$prefix]))
          {
            $request_data = $_REQUEST[piklist::$prefix];
          }
          else
          {
            $request_data = $_REQUEST;
          }
        }

        if ($request_data && $field['field'] && $field['type'] != 'html')
        {
          if (!in_array($field['field'], $clones))
          {
            if (isset($request_data[$field['field']]))
            {
              $field['request_value'] = $request_data[$field['field']];
            }
            elseif (strstr($field['field'], ':'))
            {
              $pluck = explode(':', $field['field']);
              $pluck_field = array_pop($pluck);

              if (is_numeric($pluck[count($pluck) - 1]))
              {
                array_pop($pluck);
              }

              $pluck = implode(':', $pluck);

              if (isset($request_data[$pluck][$pluck_field]))
              {
                $field['request_value'] = $request_data[$pluck][$pluck_field];
              }
              else
              {
                $request_data = piklist::array_path_get($request_data, explode(':', $pluck));

                if (isset($request_data[$pluck_field]))
                {
                  $field['request_value'] = $request_data[$pluck_field];
                }
                else
                {
                  $field['request_value'] = $request_data ? piklist::pluck($request_data, $pluck_field) : null;
                }
              }
            }
          }

          if ($field['type'] == 'group' && $field['field'] && !strstr($field['field'], ':'))
          {
            $paths = piklist::array_paths($field['request_value']);

            foreach ($paths as $path)
            {
              $path = explode(':', $path);
              if (is_numeric($path[count($path) - 1]))
              {
                unset($path[count($path) - 1]);
              }
              $path = implode(':', $path);

              $field_name = $field['field'] . ':' . $path;

              if (!isset($fields[$path]) && !in_array($field_name, $clones))
              {
                $original = preg_replace('/\:\d+\:/', ':0:', $field_name);
                $original = explode(':', $original);
                if (is_numeric($original[count($original) - 1]))
                {
                  unset($original[count($original) - 1]);
                }
                $original = implode(':', $original);

                $clone = piklist_form::get_fields_data($fields_data, $field['scope'], $original);

                if ($clone)
                {
                  $path = array_reverse(explode(':', $path));
                  for ($i = 0; $i < count($path); $i++)
                  {
                    if (is_numeric($path[$i]))
                    {
                      $clone['index'] = (int) $path[$i];

                      break;
                    }
                  }
                  $path = array_reverse($path);

                  $original_path = explode(':', $original);

                  $exists = piklist_form::get_fields_data($fields_data, $field['scope'], $field_name);

                  if ($exists)
                  {
                    $fields_data = piklist_form::update_fields_data($fields_data, $exists, null, 'request_value', piklist::array_path_get($field['request_value'], $path));
                  }
                  else
                  {
                    $clone['field'] = $field_name;
                    $clone['id'] = piklist_form::get_field_id($clone);
                    $clone['name'] = piklist_form::get_field_name($clone);
                    $clone['request_value'] = piklist::array_path_get($field['request_value'], $path);

                    array_push($fields_data[$field['scope']], $clone);
                  }

                  array_push($clones, $field_name);
                }
              }
            }
          }

          // Assume no errors
          $field['errors'] = false;

          // Strip Slashes
          $field['request_value'] = stripslashes_deep($field['request_value']);

          // Required
          if ($field['required'])
          {
            $field = self::required_value($field);
          }

          // Sanitization
          foreach ($field['sanitize'] as $sanitize)
          {
            if (isset(self::$sanitization_rules[$sanitize['type']]))
            {
              $sanitization = array_merge(self::$sanitization_rules[$sanitize['type']], $sanitize);

              if (isset($sanitization['callback']))
              {
                $field = self::sanitize_value_callback($field, $sanitization);

                if (strstr($field['field'], ':'))
                {
                  $path = explode(':', $field['field']);
                  $group = array_shift($path);

                  $group_field = piklist_form::get_fields_data($fields_data, $field['scope'], $group);

                  if (piklist::array_path_get($group_field['request_value'], $path))
                  {
                    piklist::array_path_set($group_field['request_value'], $path, $field['request_value']);
                  }

                  piklist_form::update_fields_data($fields_data, $group_field, null, 'request_value', $group_field['request_value']);
                }
              }
            }
            else
            {
              $trigger_error = sprintf(__('Sanitization type "%s" is not valid.', 'piklist'), $sanitize['type']);

              trigger_error($trigger_error, E_USER_NOTICE);
            }
          }

          // Validation
          foreach ($field['validate'] as $validate)
          {
            if (isset(self::$validation_rules[$validate['type']]))
            {
              $validation = array_merge(self::$validation_rules[$validate['type']], $validate);

              if (isset($validation['rule']))
              {
                $field = self::validate_value_rule($field, $validation);
              }

              if (isset($validation['callback']))
              {
                $field = self::validate_value_callback($field, $validation, $fields_data);
              }
            }
            else
            {
              $trigger_error = sprintf(__('Validation type "%s" is not valid.', 'piklist'), $validate['type']);

              trigger_error($trigger_error, E_USER_NOTICE);
            }
          }
        }
      }
      unset($field);
    }
    unset($fields);

    self::$checked = true;

    self::$processed = array(
      'fields_id' => $fields_id
      ,'fields_data' => $fields_data
    );

    if (piklist_admin::is_widget() || piklist_admin::is_setting())
    {
      self::set_data();
    }

    return array(
      'valid' => self::$valid
      ,'type' => $_SERVER['REQUEST_METHOD']
      ,'fields_data' => $fields_data
    );
  }

  /**
   * assets_localize
   * Add data to the local piklist variable
   *
   * @param array $localize The data being passed to the piklist javascript object.
   *
   * @return array Current data.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function assets_localize($localize)
  {
    $localize['validate_check'] = !self::errors() && self::$checked;
    $localize['validate'] = piklist::get_settings('piklist_core', 'form_validate_js') ? true : false;

    return $localize;
  }

  /**
   * required_value
   * Check to see if the value is set on a field.
   *
   * @param array $field The field object.
   *
   * @return array The field object.
   *
   * @access private
   * @static
   * @since 1.0
   */
  private static function required_value($field)
  {
    $message = is_string($field['required']) ? $field['required'] : __('is a required field.', 'piklist');

    if ($field['type'] == 'file' && isset($_FILES[piklist::$prefix . $field['scope']]['name'][$field['field']]))
    {
      $index = 0;

      foreach ($_FILES[piklist::$prefix . $field['scope']]['error'][$field['field']] as $error)
      {
        if ($error != 0)
        {
          $field = self::add_error($field, $index, $message);
        }

        $index++;
      }
    }
    elseif (is_array($field['request_value']))
    {
      $index = 0;

      foreach ($field['request_value'] as $request_value)
      {
        $required = is_array($request_value) ? array_filter($request_value) : $request_value;

        if (empty($required))
        {
          $field = self::add_error($field, $index, $message);
        }

        $index++;
      }
    }
    elseif (!$field['request_value'])
    {
      $field = self::add_error($field, 0, $message);
    }

    return $field;
  }

  /**
   * sanitize_value_callback
   * Run sanitize callback rules
   *
   * @param array $field The field object.
   * @param array $sanitization The sanitization rule.
   *
   * @return array The field object.
   *
   * @access private
   * @static
   * @since 1.0
   */
  private static function sanitize_value_callback($field, $sanitization)
  {
    $options = isset($sanitization['options']) ? $sanitization['options'] : array();

    if (is_array($field['request_value']))
    {
      $index = 0;

      foreach ($field['request_value'] as $request_value)
      {
        $field['request_value'][$index] = call_user_func_array($sanitization['callback'], array($request_value, $field, $options));

        $index++;
      }
    }
    else
    {
      $field['request_value'] = call_user_func_array($sanitization['callback'], array($field['request_value'], $field, $options));
    }

    return $field;
  }

  /**
   * validate_value_callback
   * Run validation callback rules
   *
   * @param array $field The field object.
   * @param array $validation The validation rule.
   * @param array $fields_data Collection of fields.
   *
   * @return array The field object.
   *
   * @access private
   * @static
   * @since 1.0
   */
  private static function validate_value_callback($field, $validation, $fields_data)
  {
    $options = isset($validation['options']) ? $validation['options'] : array();

    if (is_array($field['request_value']) && ($field['add_more'] || !piklist::is_flat($field['request_value'])) && $field['type'] != 'group')
    {
      $index = 0;

      foreach ($field['request_value'] as $request_value)
      {
        if ($field['request_value'][$index]) {
          $validation_result = call_user_func_array($validation['callback'], array($index, $request_value, $options, $field, $fields_data));

          if ($validation_result !== true)
          {
            $field = self::add_error($field, $index, !empty($validation['message']) ? $validation['message'] : (is_string($validation_result) ? $validation_result : __('is not valid input', 'piklist')));
          }
        }

        $index++;
      }
    }
    elseif ($field['request_value'])
    {
      $validation_result = call_user_func_array($validation['callback'], array(0, $field['request_value'], $options, $field, $fields_data));

      if ($validation_result !== true)
      {
        $field = self::add_error($field, 0, !empty($validation['message']) ? $validation['message'] : (is_string($validation_result) ? $validation_result : __('is not valid input', 'piklist')));
      }
    }

    return $field;
  }

  /**
   * validate_value_rule
   * Run validation rules
   *
   * @param array $field The field object.
   * @param array $validation The validation rule.
   *
   * @return array The field object.
   *
   * @access private
   * @static
   * @since 1.0
   */
  private static function validate_value_rule($field, $validation)
  {
    if (is_array($field['request_value']))
    {
      $index = 0;

      foreach ($field['request_value'] as $request_value)
      {
        if (!empty($request_value) && !preg_match($validation['rule'], $request_value))
        {
          $field = self::add_error($field, $index, $validation['message']);
        }

        $index++;
      }
    }
    elseif (!empty($field['request_value']) && !preg_match($validation['rule'], $field['request_value']))
    {
      $field = self::add_error($field, 0, $validation['message']);
    }

    return $field;
  }

  /**
   * add_error
   * Add errors to a field
   *
   * @param array $field The field object.
   * @param int $index The field index.s
   * @param string $message The error message.
   *
   * @return array The field object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function add_error($field, $index, $message)
  {
    self::$valid = false;

    $field['valid'] = false;

    $name = $field['label'] ? $field['label'] : (isset($field['attributes']['placeholder']) ? $field['attributes']['placeholder'] : __($field['field']));

    if (!is_array($field['errors']))
    {
      $field['errors'] = array(
        $index => array()
      );
    }
    elseif (!isset($field['errors'][$index]))
    {
      $field['errors'][$index] = array();
    }

    $name = $field['label'] ? $field['label'] : (isset($field['attributes']['placeholder']) ? $field['attributes']['placeholder'] : __($field['field']));

    array_push($field['errors'][$index], '<strong>' . $name . '</strong>' . "&nbsp;" . $message);

    if (!empty(self::$processed) && isset(self::$processed['fields_data'][$field['scope']]) && isset(self::$processed['fields_data'][$field['scope']][$field['field']]))
    {
      self::$processed['fields_data'] = self::update_fields_data(self::$processed['fields_data'], $field, null, 'errors', $field['errors']);
    }

    return $field;
  }

  /**
   * set_data
   * Save the request version of the fields object as a transient.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function set_data()
  {
    if (!self::$valid)
    {
      self::$id = substr(md5(self::$processed['fields_id']), 0, 10);

      $set = set_transient(piklist::$prefix . 'validation_' . self::$id, self::$processed['fields_data']);

      self::$form_submission = self::$processed['fields_data'];
    }
  }

  /**
   * get_data
   * Get the request version of the fields object.
   *
   * @access public
   * @static
   * @since 1.0
   */
  private static function get_data()
  {
    if (isset($_REQUEST[piklist::$prefix]) && isset($_REQUEST[piklist::$prefix][self::$parameter]))
    {
      self::$id = esc_attr($_REQUEST[piklist::$prefix][self::$parameter]);

      self::$form_submission = get_transient(piklist::$prefix . 'validation_' . self::$id);

      delete_transient(piklist::$prefix . 'validation_' . self::$id);
    }
  }

  /**
   * errors
   * Check for errors on a submitted form.
   *
   * @return bool Whether there are errors present.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function errors()
  {
    return self::$form_submission;
  }

  /**
   * get_errors
   * Get the errors for the field.
   *
   * @param array $field The field object.
   *
   * @return array The errors.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_errors($field)
  {
    if (null !== ($submission = piklist_form::get_fields_data(self::$form_submission, $field['scope'], $field['field'], piklist_form::is_related_field($field))))
    {
      return isset($submission['errors'][$field['index']]) ? $submission['errors'][$field['index']] : false;
    }

    return false;
  }

  /**
   * get_request_value
   * Get the request value from the submission.
   *
   * @param array $field The field object.
   *
   * @return array The request value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function get_request_value($field)
  {
    if (null !== ($submission = piklist_form::get_fields_data(self::$form_submission, $field['scope'], $field['field'], piklist_form::is_related_field($field))))
    {
      return isset($submission['request_value'][$field['index']]) ? $submission['request_value'] : false;
    }

    return $field['value'];
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
      'email' => array(
        'name' => __('Email Address', 'piklist')
        ,'description' => __('Verifies that the input is in the proper format for an email address.', 'piklist')
        ,'callback' => array('piklist_validate', 'validate_email')
      )
      ,'email_domain' => array(
        'name' => __('Email Domain', 'piklist')
        ,'description' => __('Verifies that the email domain entered is a valid domain.', 'piklist')
        ,'callback' => array('piklist_validate', 'validate_email_domain')
      )
      ,'email_exists' => array(
        'name' => __('Email exists?', 'piklist')
        ,'description' => __('Checks that the entered email is not already registered to another user.', 'piklist')
        ,'callback' => array('piklist_validate', 'validate_email_exists')
      )
      ,'file_exists' => array(
        'name' => __('File Exists?', 'piklist')
        ,'description' => __('Verifies that the file path entered leads to an actual file.', 'piklist')
        ,'callback' => array('piklist_validate', 'validate_file_exists')
      )
      ,'hex_color' => array(
        'name' => __('Hex Color', 'piklist')
        ,'description' => __('Verifies that the data entered is a valid hex color.', 'piklist')
        ,'callback' => array('piklist_validate', 'validate_hex_color')
      )
      ,'image' => array(
        'name' => __('Is Image?', 'piklist')
        ,'description' => __('Verifies that the file path entered leads to an image file.', 'piklist')
        ,'callback' => array('piklist_validate', 'validate_image')
      )
      ,'ip_address' => array(
        'name' => __('IP Address', 'piklist')
        ,'description' => __('Verifies that the data entered is a valid IP Address.', 'piklist')
        ,'rule' => "/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/"
        ,'message' => __('is not a valid ip address.', 'piklist')
      )
      ,'limit' => array(
        'name' => __('Entry Limit', 'piklist')
        ,'description' => __('Verifies that the number of items are within the defined limit.', 'piklist')
        ,'callback' => array('piklist_validate', 'validate_limit')
      )
      ,'range' => array(
        'name' => __('Range', 'piklist')
        ,'description' => __('Verifies that the data entered is within the defined range.', 'piklist')
        ,'callback' => array('piklist_validate', 'validate_range')
      )
      ,'safe_text' => array(
        'name' => __('Alphanumeric', 'piklist')
        ,'description' => __('Verifies that the data entered is alphanumeric.', 'piklist')
        ,'rule' => "/^[a-zA-Z0-9 .-]+$/"
        ,'message' => __('contains invalid characters. Must contain only letters and numbers.', 'piklist')
      )
      ,'url' => array(
        'name' => __('URL', 'piklist')
        ,'description' => __('Verifies that the data entered is a valid URL.', 'piklist')
        ,'rule' => "/^(https?:\/\/)?([\da-z\.\-]+)\.([a-z\.]{2,6})([\/\w \.\-@!+]*)*\/?$/"
        ,'message' => __('is not a valid url.', 'piklist')
      )
      ,'username_exists' => array(
        'name' => __('Username exists?', 'piklist')
        ,'description' => __('Checks that the entered username does not already exist.', 'piklist')
        ,'callback' => array('piklist_validate', 'validate_username_exists')
      )
      ,'match' => array(
        'name' => __('Match Fields', 'piklist')
        ,'description' => __('Checks to see if two fields match.', 'piklist')
        ,'callback' => array('piklist_validate', 'validate_match')
      )
    ));

    return $validation_rules;
  }

  /**
   * validate_email
   * Validate email address
   *
   * @param int $index The field index being checked.
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   * @param array $fields Collection of fields.
   *
   * @return bool true if string is a valid email address, message otherwise.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_email($index, $value, $options, $field, $fields)
  {
    return is_email($value) ? true : __('does not contain a valid Email Address.', 'piklist');
  }

  /**
   * validate_email_domain
   * Validate email address domain
   *
   * When checkdnsrr() returns false, it also returns a php warning.
   * The warning is being suppressed, since it will return a validation message.
   *
   * @param int $index The field index being checked.
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   * @param array $fields Collection of fields.
   *
   * @return bool true if string is a valid email domain, message otherwise.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_email_domain($index, $value, $options, $field, $fields)
  {
    return (bool) @checkdnsrr(preg_replace('/^[^@]++@/', '', $value), 'MX') ? true : __('does not contain a valid Email Domain.', 'piklist');
  }

  /**
   * validate_email_exists
   * Check if a email is already registered to another user
   *
   * Uses the WordPress function email_exists()
   *
   * @param int $index The field index being checked.
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   * @param array $fields Collection of fields.
   *
   * @return bool true if $email is registered to another user generated message, return false otherwise.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_email_exists($index, $value, $options, $field, $fields)
  {
    global $current_user;

    return (email_exists($value) && !is_user_logged_in()) || (email_exists($value) && is_user_logged_in() && $value != $current_user->user_email) ? sprintf(__('cannot be "%s". This email is registered to another user.', 'piklist'), $value) : true;
  }

  /**
   * validate_file_exists
   * Validate if a file exists
   *
   * When file_get_contents() returns false, it also returns a php warning.
   * The warning is being suppressed, since it will return a validation message.
   *
   * @param int $index The field index being checked.
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   * @param array $fields Collection of fields.
   *
   * @return bool true if $file exists, message otherwise.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_file_exists($index, $value, $options, $field, $fields)
  {
    $field_value = is_array($value) ? $value : array($value);

    foreach ($field_value as $value)
    {
      if ($field['type'] == 'file' && is_numeric($value))
      {
        $value = wp_get_attachment_url($value);
      }

      if (!@file_get_contents($value))
      {
        return __('contains a file that does not exist.', 'piklist');
      }
    }

    return true;
  }

  /**
   * validate_hex_color
   * Validate if a value is a valid hex color
   *
   * Uses the WordPress function sanitize_hex_color to sanitize the value and compare.
   *
   * @param int $index The field index being checked.
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   * @param array $fields Collection of fields.
   *
   * @return bool true if $file exists, message otherwise.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_hex_color($index, $value, $options, $field, $fields)
  {
    $hex = self::sanitize_hex_color($value);

    if ($hex === $value)
    {
      return true;
    }

    return false;
  }

  /**
   * validate_image
   * Validate if an image file exists
   *
   * When exif_imagetype() returns false, it also returns a php warning.
   * The warning is being suppressed, since it will return a validation message.
   *
   * @param int $index The field index being checked.
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   * @param array $fields Collection of fields.
   *
   * @return bool true if string is an image file, message otherwise.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_image($index, $value, $options, $field, $fields)
  {
    $field_value = is_array($value) ? $value : array($value);

    foreach ($field_value as $value)
    {
      if ($value)
      {
        if ($field['type'] == 'file' && is_numeric($value))
        {
          $value = wp_get_attachment_url($value);
        }

        if (!@exif_imagetype($value))
        {
          return __('contains a file that is not an image.', 'piklist');
        }
      }
    }

    return true;
  }

  /**
   * validate_limit
   * Validate how many items are in request value
   *
   * Request value can be any Piklist field.
   *
   * @param int $index The field index being checked.
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   * @param array $fields Collection of fields.
   *
   * @return bool true if value is within limit, message otherwise.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_limit($index, $value, $options = null, $field, $fields)
  {
    $options = wp_parse_args($options, array(
      'min' => 1
      ,'max' => INF
      ,'count' => false
    ));

    extract($options);

    switch ($count)
    {
      case 'words':

        $grammar = __('words', 'piklist');
        $words = preg_split('#\PL+#u', $value, -1, PREG_SPLIT_NO_EMPTY);
        $total = count($words);

      break;

      case 'characters':

        $grammar = __('characters', 'piklist');
        $total = strlen($value);

      break;

      default:

        $grammar = $field['type'] == 'file' || $field['add_more'] ? __('items added', 'piklist') : __('items selected', 'piklist');
        $total = $field['type'] != 'group' && !$field['multiple'] ? count($field['request_value']) : count($value);

      break;
    }

    if ($total < $min || $total > $max)
    {
      if ($min == $max)
      {
        return sprintf(__('must have exactly %1$s %2$s.', 'piklist'), $min, $grammar);
      }
      else
      {
        return sprintf(__('must have between %1$s and %2$s %3$s.', 'piklist'), $min, $max, $grammar);
      }
    }

    return true;
  }

  /**
   * validate_range
   * Validate if a numbered value is within a range.
   *
   * @param int $index The field index being checked.
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   * @param array $fields Collection of fields.
   *
   * @return bool true if value is within range, message otherwise.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_range($index, $value, $options = null, $field, $fields)
  {
    extract($options);

    $min = isset($options['min']) ? $options['min'] : 1;
    $max = isset($options['max']) ? $options['max'] : 10;

    if (($field['request_value'][0] >= $min) && ($field['request_value'][0] <= $max))
    {
      return true;
    }
    else
    {
      return sprintf(__('contains a value that is not between %s and %s', 'piklist'), $min, $max);
    }
  }

  /**
   * validate_username_exists
   * Check if a username already exists
   * Uses the WordPress function username_exists()
   *
   * @param int $index The field index being checked.
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   * @param array $fields Collection of fields.
   *
   * @return bool true if $username does not exist, message otherwise.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_username_exists($index, $value, $options, $field, $fields)
  {
    $current_user = wp_get_current_user();

    if ($current_user->user_login == $value)
    {
      return true;
    }
    elseif (username_exists($value))
    {
      return sprintf(__('cannot be "%s". This username already exists.', 'piklist'), $value);
    }
    else
    {
      return true;
    }
  }

  /**
   * validate_match
   * Check if two fields match
   *
   * @param int $index The field index being checked.
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   * @param array $fields Collection of fields.
   *
   * @return bool true if fields match, message otherwise.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function validate_match($index, $value, $options, $field, $fields)
  {
    if (isset($options['field']))
    {
      $scope = is_array($options['field']) && isset($options['field']['scope']) ? $options['field']['scope'] : $field['scope'];
      $group = is_array($options['field']) && isset($options['field']['group']) ? $options['field']['group'] : $field['field'];

      if (isset($options['field']) && isset($fields[$scope][$options['field']]))
      {
        $match_field = piklist_form::get_fields_data($fields, $scope, $group);

        if (isset($match_field['request_value'][$index]) && $match_field['request_value'][$index] === $value)
        {
          return true;
        }
        else
        {
          return sprintf(__('must match <strong>%s</strong>', 'piklist'), isset($match_field['label']) ? $match_field['label'] : $match_field['field'], $value);
        }
      }
    }

    return true;
  }

  /**
   * Included Sanitization Callbacks
   */

  /**
   * sanitization_rules
   * Array of included sanitization rules.
   *
   * @param array $sanitization_rules Sanitization rules.
   *
   * @return array Sanitization rules.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitization_rules($sanitization_rules)
  {
    $sanitization_rules = array_merge($sanitization_rules, array(
      'email' => array(
        'name' => __('Email address', 'piklist')
        ,'description' => __('Strips out all characters that are not allowable in an email address.', 'piklist')
        ,'callback' => array('piklist_validate', 'sanitize_email')
      )
      ,'file_name' => array(
        'name' => __('File name', 'piklist')
        ,'description' => __('Removes or replaces special characters that are illegal in filenames.', 'piklist')
        ,'callback' => array('piklist_validate', 'sanitize_file_name')
      )
      ,'html_class' => array(
        'name' => __('HTML class', 'piklist')
        ,'description' => __('Removes all characters that are not allowable in an HTML classname.', 'piklist')
        ,'callback' => array('piklist_validate', 'sanitize_html_class')
      )
      ,'text_field' => array(
        'name' => __('Text field', 'piklist')
        ,'description' => __('Removes all HTML markup, as well as extra whitespace, leaving only plain text.', 'piklist')
        ,'callback' => array('piklist_validate', 'sanitize_text_field')
      )
      ,'title' => array(
        'name' => __('Post title', 'piklist')
        ,'description' => __('Removes all HTML and PHP tags, returning a title that is suitable for a url', 'piklist')
        ,'callback' => array('piklist_validate', 'sanitize_title')
      )
      ,'user' => array(
        'name' => __('Username', 'piklist')
        ,'description' => __('Removes all unsafe characters for a username.', 'piklist')
        ,'callback' => array('piklist_validate', 'sanitize_user')
      )
      ,'wp_kses' => array(
        'name' => __('wp_kses', 'piklist')
        ,'description' => __('Makes sure that only the allowed HTML element names, attribute names and attribute values plus only sane HTML entities are accepted.', 'piklist')
        ,'callback' => array('piklist_validate', 'sanitize_wp_kses')
      )
      ,'wp_filter_kses' => array(
        'name' => __('wp_filter_kses', 'piklist')
        ,'description' => __('Makes sure only default HTML elements are accepted.', 'piklist')
        ,'callback' => array('piklist_validate', 'sanitize_wp_filter_kses')
      )
      ,'wp_kses_post' => array(
        'name' => __('wp_kses_post', 'piklist')
        ,'description' => __('Makes sure only appropriate HTML elements for post content are accepted.', 'piklist')
        ,'callback' => array('piklist_validate', 'sanitize_wp_kses_post')
      )
      ,'wp_strip_all_tags' => array(
        'name' => __('wp_strip_all_tags', 'piklist')
        ,'description' => __('Properly strip all HTML tags including script and style.', 'piklist')
        ,'callback' => array('piklist_validate', 'sanitize_wp_strip_all_tags')
      )
    ));

    return $sanitization_rules;
  }

  /**
   * sanitize_email
   * Strips out all characters that are not allowable in an email address.
   * Uses the WordPress function sanitize_email().
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_email($value, $field, $options)
  {
    return sanitize_email($value);
  }

  /**
   * sanitize_file_name
   * Sanitizes a filename
   * -Removes special characters that are illegal in filenames on certain operating systems
   * -Removes special characters requiring special escaping to manipulate at the command line.
   * -Replaces spaces and consecutive dashes with a single dash.
   * -Trims period, dash and underscore from beginning and end of filename
   * Uses the WordPress function sanitize_file_name()
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_file_name($value, $field, $options)
  {
    return sanitize_file_name($value);
  }

  /**
   * sanitize_html_class
   * Sanitizes a html classname to ensure it only contains valid characters.
   * Uses the WordPress function sanitize_html_class()
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_html_class($value, $field, $options = null)
  {
    $options = wp_parse_args($options, array(
      array()
    ));

    extract($options);

    return sanitize_html_class($value, isset($fallback) ? $fallback : null);
  }

  /**
   * sanitize_text_field
   * Sanitize a string from user input.
   * Uses the WordPress function sanitize_text_field()
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_text_field($value, $field, $options)
  {
    return sanitize_text_field($value);
  }

  /**
   * sanitize_title
   * -HTML and PHP tags are stripped
   * -Accents are removed (accented characters are replaced with non-accented equivalents).
   * Uses the WordPress function sanitize_title();
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_title($value, $field, $options = null)
  {
    $options = wp_parse_args($options, array(
      array()
    ));

    extract($options);

    return sanitize_title($value, isset($fallback) ? $fallback : null, isset($context) ? $context : null);
  }

  /**
   * sanitize_user
   * Sanitize username stripping out unsafe characters.
   * Uses WordPress function sanitize_user()
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_user($value, $field, $options = null)
  {
    $options = wp_parse_args($options, array(
      array()
    ));

    extract($options);

    return sanitize_user($value, isset($strict) ? $strict : null);
  }

  /**
   * sanitize_wp_kses
   * Makes sure that only the allowed HTML element names, attribute names and attribute values plus only sane HTML entities will occur in $string.
   * Uses the WordPress function wp_kses()
   *
   * accepts
   * array allowed_html
   * array allowed_protocols
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_wp_kses($value, $field, $options = null)
  {
    $options = wp_parse_args($options, array(
      array()
    ));

    extract($options);

    return wp_kses($value, isset($allowed_html) ? $allowed_html : null, isset($allowed_protocols) ? $allowed_protocols : null);
  }

  /**
   * sanitize_wp_kses_post
   * Sanitize content for allowed HTML tags for post content.
   * Uses the WordPress function wp_kses_post()
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_wp_kses_post($value, $field, $options)
  {
    return wp_kses_post($value);
  }

  /**
   * sanitize_wp_filter_kses
   * Sanitize content with allowed HTML Kses rules.
   * Uses the WordPress function wp_kses_data()
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_wp_filter_kses($value, $field, $options)
  {
    return wp_kses_data($value);
  }

  /**
   * sanitize_wp_strip_all_tags
   * Properly strip all HTML tags including script and style.
   * Uses the WordPress function wp_strip_all_tags()
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_wp_strip_all_tags($value, $field, $options = null)
  {
    $options = wp_parse_args($options, array(
      array()
    ));

    extract($options);

    return wp_strip_all_tags($value, isset($remove_breaks) ? $remove_breaks : null);
  }

  /**
   * sanitize_hex_color
   * Sanitizes a hex color.
   * Returns either '', a 3 or 6 digit hex color (with #), or null.
   *
   * @param mixed $value The value of the field.
   * @param array $options The options.
   * @param array $field The field object.
   *
   * @return mixed Sanitized value.
   *
   * @access public
   * @static
   * @since 1.0
   */
  public static function sanitize_hex_color($color)
  {
    if ('' === $color)
    {
      return '';
    }

    // 3 or 6 hex digits, or the empty string.
    if (preg_match('|^#([A-Fa-f0-9]{3}){1,2}$|', $color))
    {
      return $color;
    }

    return null;
  }
}
