<?php
/*
 * Updates for v0.9.9
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Piklist_Update_0_9_9'))
{
  class Piklist_Update_0_9_9
  {
    var $slug = 'piklist_update_0_9_9';

    var $widget = false;

    var $fields = array();

    var $setting = false;

    public function __construct()
    {
      add_action('admin_init', array($this, 'admin_init'));

      if ((defined('DOING_AJAX') && DOING_AJAX) || (isset($_REQUEST['page']) && $_REQUEST['page'] == 'piklist_update_0_9_9'))
      {
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('wp_ajax_' . $this->slug,  array($this, 'ajax'));

        add_filter('piklist_request_field', array($this, 'piklist_request_field'));
        add_filter('piklist_get_meta_sql', array($this, 'piklist_get_meta_sql'), 9999, 9);
      }

      add_action('admin_notices', array($this, 'admin_notices'));
    }

    public function piklist_get_meta_sql($enabled, $sql, $query, $type, $primary_table, $primary_id_column, $context, $parent_relation, $depth)
    {
      // This filter isn't for everyones install, so lets disable during our upgrade.
      return false;
    }

    public function admin_notices()
    {
      if (!isset($_REQUEST['page']) || (isset($_REQUEST['page']) && $_REQUEST['page'] != 'piklist_update_0_9_9')):
?>
        <div class="updated-nag notice notice-warning">
          <p>
            <strong><?php _e('IMPORTANT MESSAGE FROM PIKLIST', 'piklist'); ?></strong>
          </p>
          <p>
            <?php _e('The data structure for Piklist Add-More and Workflow tabs have changed in this version and require an update.'); ?>
						<?php _e('Your repeater field output and WorkFlow tabs will not work after you run this upgrade, unless you make the neccessary updates.'); ?>
						<strong><?php _e('Do not save any Piklist data until you run this update.'); ?></strong>
					</p>
          <p>
						<?php _e('Here are your choices:'); ?>
						<ul>
							<li><?php printf(__('1. Run the upgrade script and then make changes to your files. You can learn more about the changes needed %1$s on our website.%2$s', 'piklist'), '<a href="https://piklist.com/2016/06/09/piklist-v0-9-9-8-coming-need-know/" target="_blank">', '</a>');?></li>
							<li><?php printf(__('2. If you are not ready to make these changes, then STOP. Delete this version of Piklist, %1$s download the old version%2$s from WordPress.org, and install it. Use it until you are ready to do the upgrade.', 'piklist'), '<a href="https://downloads.wordpress.org/plugin/piklist.0.9.4.31.zip" target="_blank">', '</a>');?></li>
						</ul>
            <strong><?php _e('IMPORTANT: Backup your database before running the upgrade.'); ?></strong>
          </p>
          <p>
            <a href="<?php echo admin_url('admin.php?page=piklist_update_0_9_9'); ?>" class="button button-secondary"><?php _e('Update Now'); ?></a>
          </p>
        </div>
<?php
      endif;
    }

    public function admin_menu()
    {
      add_submenu_page(null, 'Piklist Update', 'Piklist Update v0.9.9', 'manage_options', $this->slug, array($this, 'admin_page'));
    }

    public function admin_init()
    {
      require_once ABSPATH . 'wp-admin/includes/plugin.php';

      $updates = piklist_admin::$network_wide ? get_site_option('piklist_updates') : get_option('piklist_updates');
      $updates = $updates ? $updates : array('piklist' => array());

      if (!isset($updates['piklist']))
      {
        $updates['piklist'] = array();
      }

      if (!in_array('0.9.9', $updates['piklist']))
      {
        array_push($updates['piklist'], '0.9.9');

        piklist_admin::$network_wide ? update_site_option('piklist_updates', $updates) : update_option('piklist_updates', $updates);
      }
    }

    public function ajax()
    {      
      if (!isset($_REQUEST['method']) || !isset($_REQUEST['nonce']) || !wp_verify_nonce((string) $_REQUEST['nonce'], $this->slug))
      {
        wp_send_json_error();
      }

      global $wpdb;

      $output = array();
      $method = esc_attr($_REQUEST['method']);

      switch ($method)
      {
        case 'complete-update':

          piklist_admin::$network_wide = is_plugin_active_for_network('piklist/piklist.php');

          $updates = piklist_admin::$network_wide ? get_site_option('piklist_updates') : get_option('piklist_updates');

          foreach ($updates['piklist'] as $index => $version)
          {
            if ($version == '0.9.9')
            {
              unset($updates['piklist'][$index]);
            }
          }

          $updates['piklist'] = array_values($updates['piklist']);

          piklist_admin::$network_wide ? update_site_option('piklist_updates', $updates) : update_option('piklist_updates', $updates);

          $output['message'] = 'Piklist Update 0.9.9 Completed!';

        break;

        case 'options-update':

          /**
           * Fix arrays created by piklist_admin::check_update()
           * Remove null values and zero values
           */
          $piklist_active_plugin_versions = piklist_admin::$network_wide ? get_site_option('piklist_active_plugin_versions') : get_option('piklist_active_plugin_versions');

          foreach ($piklist_active_plugin_versions as $plugins => $versions)
          {
            foreach ($versions as $key => $value)
            {
              if (is_null($value) || $value == 0)
              {
                unset($piklist_active_plugin_versions[$plugins][$key]);
              }
            }
          }

          piklist_admin::$network_wide ? update_site_option('piklist_active_plugin_versions', $piklist_active_plugin_versions) : update_option('piklist_active_plugin_versions', $piklist_active_plugin_versions);

          $output['message'] = 'Active Plugin Versions Updated...';

        break;

        case 'relate-update':

			$relate_table = $wpdb->prefix . 'post_relationships';

			if($wpdb->get_var("SHOW TABLES LIKE '$relate_table'") == $relate_table)
			{
	          $related = $wpdb->get_results("SELECT post_id, has_post_id FROM $relate_table", ARRAY_A);
	          $meta_key = '_' . piklist::$prefix . 'relate_post';

	          foreach ($related as $relate)
	          {
	            $current_related = get_metadata('post', $relate['post_id'], $meta_key);

	            if (!$current_related || ($current_related && !in_array($relate['has_post_id'], $current_related)))
	            {
	              add_metadata('post', $relate['post_id'], $meta_key, $relate['has_post_id']);
	              add_metadata('post', $relate['has_post_id'], $meta_key, $relate['post_id']);
	            }
	          }

	          $output['message'] = 'Post Relationships Updated...';

	          $wpdb->query("DROP TABLE IF EXISTS {$relate_table}");

	          $output['message'] = 'Legacy post_relationships Table Deleted...';
		  	}

        break;

        case 'widgets':
        case 'settings':
        case 'meta-boxes':
        case 'users':
        case 'terms':
        case 'media':

          $folder = $method;
          $buffer = null;
          $this->widget = false;
          $this->setting = false;
          $this->fields = array();

          // Read all fields for folder
          foreach (piklist::paths() as $add_on => $path)
          {
            $files = piklist::get_directory_list($path . '/parts/' . $folder);

            if (empty($files) && in_array($add_on, array('theme', 'parent-theme')))
            {
              $files = piklist::get_directory_list($path . '/' . $folder);
            }

            ob_start();

            foreach ($files as $part)
            {
              if (($folder == 'widgets' && stristr($part, '-form')) || $folder != 'widgets')
              {
                if ($folder == 'settings')
                {
                  $data = piklist::get_file_data($path . '/parts/' . $folder . '/' . $part, array(
                            'setting' => 'Setting'
                          ));

                  $this->setting = $data['setting'];
                }
                elseif ($folder == 'widgets')
                {
                  $data = piklist::get_file_data($path . '/parts/' . $folder . '/' . $part, array(
                            'title' => 'Title'
                          ));

                  if (empty($data['title']))
                  {
                    $data = piklist::get_file_data($path . '/parts/' . $folder . '/' . str_replace('-form', '', $part), array(
                              'title' => 'Title'
                            ));
                  }

                  $this->widget = $add_on . ':' . strtolower($data['title']);
                }

                ob_start();

                @include $path . '/parts/' . $folder . '/' . $part;

                ob_end_clean();

              }
            }

            ob_end_clean();
          }

          // Setup our defaults
          switch ($folder)
          {
            case 'meta-boxes':
            case 'media':

              $table = $wpdb->postmeta;
              $object_key = 'post_id';
              $meta_id = 'meta_id';
              $output['message'] = $folder == 'meta-boxes' ? __('Post Meta Updated...') : __('Media Meta Updated...');

            break;

            case 'users':

              $table = $wpdb->usermeta;
              $object_key = 'user_id';
              $meta_id = 'umeta_id';
              $output['message'] = __('User Meta Updated...');

            break;

            case 'terms':

              $table = $wpdb->termmeta;
              $object_key = 'term_id';
              $meta_id = 'meta_id';
              $output['message'] = __('Term Meta Updated...');

            break;
          }

          // Run through fields and update the ones we need to update
          foreach ($this->fields as $index => $field)
          {
            switch ($folder)
            {
              case 'media':
              case 'meta-boxes':
              case 'users':
              case 'terms':

                $keys = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE meta_key = %s", $field['field']), ARRAY_A);

                foreach ($keys as $key)
                {
                  if (!empty($key['meta_value']))
                  {
                    $old = maybe_unserialize($key['meta_value']);
                    if ($this->needs_update($old))
                    {
                      $new = piklist::object_format($old);
                      $new = isset($field['add_more']) && $field['add_more'] == true ? $new : current($new);

                      if ($old != $new)
                      {
                        $result = $wpdb->update($table, array('meta_value' => maybe_serialize($new)), array($meta_id => $key[$meta_id], $object_key => $key[$object_key], 'meta_key' => $key['meta_key']));
                      }
                    }
                  }
                }

              break;

              case 'settings':

                remove_filter('pre_update_option_' . $field['context'], array('piklist_setting', 'pre_update_option'), 10, 2);

                $option = get_option($field['context']);

                if (isset($option[$field['field']['field']]) && $this->needs_update($option[$field['field']['field']]))
                {
                  $option[$field['field']['field']] = piklist::object_format($option[$field['field']['field']]);

                  // Helpers Dashboard Widgets field
                  $option[$field['field']['field']] = isset($field['field']['add_more']) && $field['field']['add_more'] == true ? $option[$field['field']['field']] : current($option[$field['field']['field']]);

                  update_option($field['context'], $option);
                }

                $output['message'] = __('Settings Updated...');

              break;

              case 'widgets':

                $data = explode(':', $field['context']);

                $widgets = get_option('widget_piklist-universal-widget-' . $data[0]);

                if(!empty($widgets))
                {
                  foreach ($widgets as $widget_number => &$widget)
                  {
                    if (is_numeric($widget_number))
                    {
                      foreach ($widget as $_field => &$_value)
                      {
                        if ($field['field']['field'] == $_field)
                        {
                          $old = maybe_unserialize($_value);

                          if ($this->needs_update($old))
                          {
                            $_value = maybe_serialize(piklist::object_format($old));
                          }
                        }
                        elseif ($_field == 'widget')
                        {
                          $old = maybe_unserialize($_value);
                          $add_on = piklist::slug($data[0]);

                          if (is_array($old) && substr($old[0], 0, strlen($add_on)) != $add_on)
                          {
                            $_value = piklist::slug($data[0] . '_' . $old[0]);
                          }
                        }
                      }
                    }
                  }

                  update_option('widget_piklist-universal-widget-' . $data[0], $widgets);

                  $output['message'] = __('Widgets Updated...');
                }

              break;
            }
          }

          $output['fields'] = array();
          foreach ($this->fields as $index => $field)
          {
            if (is_array($field))
            {
              array_push($output['fields'], (isset($field['context']) ? $field['context'] . ' - ' : null) . (is_array($field['field']) ? $field['field']['field'] : $field['field']));
            }
            else
            {
              array_push($output['fields'], $field);
            }
          }

        break;
      }

      wp_send_json($output);
    }

    public function needs_update($object)
    {
      return is_array($object) && !piklist::is_flat($object) && !isset($object[0]);
    }

    public function piklist_request_field($field)
    {
      if ($field['type'] == 'group' && $field['field'])
      {
        if ($this->setting)
        {
          array_push($this->fields, array(
            'context' => $this->setting
            ,'field' => $field
          ));
        }
        elseif ($this->widget)
        {
          array_push($this->fields, array(
            'context' => $this->widget
            ,'field' => $field
          ));
        }
        else
        {
          array_push($this->fields, $field);
        }
      }

      return false;
    }

    public function admin_page()
    {
?>
      <div class="wrap">

        <h1><?php _e('Be sure to backup your database before running this update!', 'piklist'); ?></h1>
        <h3><?php _e('This update is not reversible.', 'piklist'); ?></h3>

        <p>
          <?php _e('Only serialized field data created and saved by Piklist will be effected by this update.', 'piklist'); ?>
        </p>

        <form method="post" action="#" name="upgrade" class="upgrade">
          <input type="hidden" id="nonce" name="nonce" value="<?php echo wp_create_nonce($this->slug); ?>" />

          <p>
            <input type="submit" name="upgrade" id="upgrade" class="button" value="Update Now"  />&nbsp;&nbsp;&nbsp;<span id="piklist-database-upgrade-progress"></span>
          </p>
        </form>

        <p>
          <strong><?php _e('Please Note'); ?>:</strong> As always if you have any issues at all, please contact <a href="https://piklist.com/support">support</a> for assistance.
        </p>

        <div id="update-messages"></div>

      </div>

      <script>

        (function($)
        {
          var methods = [
                'options-update'
                ,'relate-update'
                ,'widgets'
                ,'settings'
                ,'meta-boxes'
                ,'users'
                ,'terms'
                ,'media'
              ];

          $(document).ready(function()
          {
            $('#upgrade').on('click', function(event)
            {
              event.preventDefault();

              var button = $(this)
                nonce = button.parents('form:eq(0)').find('#nonce').val();

              button
                .prop('value', 'Scanning...')
                .prop('disabled', true);

              run_update();

              return false;
            });
          });

          function run_update(folder)
          {
            $.ajax({
              type: 'POST',
              url: ajaxurl,
              dataType: 'json',
              data: {
                action: '<?php echo $this->slug; ?>',
                method: methods.shift(),
                nonce: nonce
              }
              ,beforeSend: function(request)
              {
                $('#piklist-database-upgrade-progress').html('Please be patient, this process can take up to 3 minutes, with long pauses between upgrading data types.');
              }
              ,success: function(response)
              {
                var progress = Math.ceil((methods.length / 5) * 100);

                $('#piklist-database-upgrade-progress').html((100 - progress) + '&#37;');

                if (typeof response.message != 'undefined')
                {
                  $('#update-messages').append('<h3>' + response.message + '</h3>');
                }

                if (typeof response.fields != 'undefined' && response.fields.length > 0)
                {
                  $('#update-messages').append(response.fields.join('<br>') + '<br><br>');
                }

                if (progress == 0)
                {
                  $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                      action: '<?php echo $this->slug; ?>',
                      method: 'complete-update',
                      nonce: nonce
                    }
                    ,success: function(response)
                    {
                      $('#update-messages').append('<h4>' + response.message + '</h4>');

                      $('#upgrade').prop('value', 'Update Complete');
                    }
                  });
                }
                else if (methods.length > 0)
                {
                  run_update();
                }
              }
            });
          }

        })(jQuery);

      </script>

<?php
    }
  }
}
