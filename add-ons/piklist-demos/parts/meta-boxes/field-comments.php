<?php
/*
Title: Comments
Post Type: piklist_demo
Order: 35
Priority: default
Context: side
Collapse: true
Flow: Demo Workflow
Tab: All
*/
?>

<p class="piklist-demo-highlight">
  <?php _e('Inline comments fields are a snap to add.', 'piklist-demo');?>
</p>

<?php

  piklist('field', array(
    'type' => 'comments'
    ,'label' => __('Notes', 'piklist-demo')
    ,'description' => __('Add some notes', 'piklist-demo')
    ,'attributes' => array(
      'class' => 'large-text code'
      ,'rows' => 5
    )
  ));

?>

<?php

  global $post, $current_user, $wp_post_statuses;

  wp_get_current_user();

  $comments = get_comments(array(
    'post_id' => $post_id
  ));

?>

<?php if ($comments): ?>

  <?php $date_format = get_option('date_format'); ?>
  <?php $time_format = get_option('time_format'); ?>

  <div class="piklist-field-container">

    <div class="piklist-label-container"></div>

      <div class="piklist-field">

        <?php foreach ($comments as $comment): ?>

          <div style="padding: 5px 10px 5px 10px;">

            <p>

              <small>

                <?php echo esc_html($comment->comment_author); ?>

                <?php printf(__('on %s', 'piklist-demo'), get_comment_date( 'l jS F, Y, g:ia', $comment->comment_ID)); ?><br>
                <?php printf(__('Status: %s', 'piklist-demo'), $wp_post_statuses[$post->post_status]->label); ?>

              </small>

            </p>

            <?php echo esc_html($comment->comment_content); ?>

          </div>

        <?php endforeach; ?>

      </div>

  </div>

<?php endif; ?>

<?php

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));
