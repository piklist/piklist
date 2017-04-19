<?php
/*
Title: At a Glance
Capability: manage_options
Network: false
Extend: dashboard_right_now
Extend Method: replace
*/

global $wp_registered_sidebars;

?>

  <div class="main">

    <h4><?php _e('Content','piklist'); ?></h4>

    <ul>

      <?php $post_types = get_post_types(array(), 'objects'); ?>

        <?php $exclude = array('revision');?>

        <?php foreach ($exclude as $exclude_post_type) : ?>

          <?php unset($post_types[$exclude_post_type]); ?>

        <?php endforeach; ?>

      <?php asort($post_types); ?>

      <?php $custom_status = array('attachment' => 'inherit'); ?>

      <?php foreach ($post_types as $post_type) : ?>

				<?php if($post_type->public == 1) : // Only show public Post Types ?>

	        <?php $status_count = 0; ?>

	        <?php $num_pages = wp_count_posts($post_type->name); ?>

	        <?php $statuses = piklist_cpt::get_post_statuses_for_type($post_type->name); ?>

	        <?php foreach($statuses as $status => $value) : ?>

	          <?php if($value->public == 1) : ?>

	            <?php $status_count = $status_count + $num_pages->$status; ?>

	          <?php endif; ?>

	        <?php endforeach; ?>

	        <li class="<?php echo piklist::strtolower($post_type->name) . __('_right_now', 'piklist');?>">

	          <?php if (array_key_exists($post_type->name, $custom_status)) : ?>

	            <?php $status = implode($custom_status); ?>

	          <?php endif; ?>

	          <a href="<?php echo $post_type->name == 'attachment' ? 'upload.php' : 'edit.php?post_type=' . $post_type->name;?>">

	            <?php echo $status_count . '&nbsp;' . ($status_count > 1 ? piklist::pluralize($post_type->label) : $post_type->label); ?>

	          </a>

	        </li>

				<?php endif; ?>

      <?php endforeach; ?>

    </ul>

    <h4><?php _e('Organization','piklist'); ?></h4>

    <ul>

        <?php $taxonomies = get_taxonomies(array(), 'objects'); ?>

        <?php foreach ($taxonomies as $taxonomy) : ?>

          <li class="<?php echo piklist::strtolower($post_type->name); ?>">

            <?php $num_pages = wp_count_terms($taxonomy->name); ?>

            <a href="edit-tags.php?taxonomy=<?php echo $taxonomy->name; ?>">

              <?php echo number_format_i18n( $num_pages) . '&nbsp;' . $taxonomy->label; ?>

            </a>

          </li>

        <?php endforeach; ?>

    </ul>

    <h4>

      <?php $comments = wp_count_comments(); ?>

      <?php echo $comments->total_comments . '&nbsp;' . __('Comment(s)','piklist'); ?>

    </h4>

    <ul>

      <li>

        <a href="edit-comments.php?comment_status=approved">

          <?php echo $comments->approved . '&nbsp;' . __('Approved','piklist'); ?>

        </a>

      </li>

      <li>

        <a href="edit-comments.php?comment_status=moderated">

          <?php echo $comments->moderated . '&nbsp;' . __('Pending','piklist'); ?>

        </a>

      </li>

      <li>

        <a href="edit-comments.php?comment_status=spam">

          <?php echo $comments->spam . '&nbsp;' . __('Spam','piklist'); ?>

        </a>

      </li>

    </ul>

    <h4>

      <?php $users = count_users(); ?>

      <?php echo $users['total_users'] . '&nbsp;' . __('User(s)','piklist'); ?>

    </h4>

    <ul>

        <?php foreach ($users['avail_roles'] as $role => $count) : ?>

          <li class="<?php echo $role; ?>">

            <a href="users.php?role=<?php echo $role; ?>">

              <?php echo $count . '&nbsp;' . ucfirst($count > 1 ? piklist::pluralize($role) : $role); ?>

            </a>

          </li>

        <?php endforeach; ?>

    </ul>

    <?php $elements = apply_filters('dashboard_glance_items', array()); ?>

    <?php if ($elements) : ?>

      <?php echo implode( "</li>\n<li>", $elements); ?>

    <?php endif; ?>

    <?php $theme = wp_get_theme(); ?>

    <?php if (current_user_can('switch_themes')) : ?>

      <?php $theme_name = sprintf('<a href="themes.php">%1$s</a>', $theme->display('Name')); ?>

    <?php else : ?>

      <?php $theme_name = $theme->display('Name'); ?>

    <?php endif; ?>


    <?php require_once(ABSPATH . 'wp-admin/includes/translation-install.php'); ?>

    <?php $translations = wp_get_available_translations(); ?>

    <?php $local = str_replace('-', '_' , get_bloginfo('language', 'raw')) ;?>

    <?php $language = isset($translations[$local]) ? isset($translations[$local]['native_name']) ? $translations[$local]['native_name'] : $translations[$local] : 'English (United States)'; ?>

    <?php $language = '<a href="options-general.php">' . $language . '</a>'; ?>

    <p><?php printf(__('WordPress %1$s running %2$s theme in %3$s.','piklist'), get_bloginfo('version', 'display'), $theme_name, $language); ?></p>



    <?php if (!is_network_admin() && !is_user_admin() && current_user_can('manage_options') && '1' != get_option('blog_public')) : ?>

      <?php $title = apply_filters('privacy_on_link_title', __('Your site is asking search engines not to index its content','piklist')); ?>

      <?php $content = apply_filters('privacy_on_link_text' , __('Search Engines Discouraged','piklist')); ?>

      <p>

        <a href='options-reading.php' title='<?php echo $title; ?>'><?php echo $content; ?></a>

      </p>

    <?php endif; ?>

    <?php

      ob_start();

      do_action('rightnow_end');

      do_action('activity_box_end');

      $actions = ob_get_clean();

    ?>

    <?php if (!empty($actions)) : ?>

      <div class="sub">

        <?php echo $actions; ?>

      </div>

    <?php endif;?>

  </div>
