<?php
/*
Title: HTML Field
Post Type: piklist_demo
Order: 20
Collapse: false
Tab: Common
Sub Tab: Basic
Flow: Demo Workflow
*/
?>

<div class="piklist-demo-highlight">
  <?php _e('HTML fields can output your markup in the same format as other fields.', 'piklist-demo');?>
</div>

<?php

  piklist('field', array(
    'type' => 'html'
    ,'label' => __('HTML Field', 'piklist-demo')
    ,'description' => __('Allows you to output any HTML in the proper format.', 'piklist-demo')
    ,'value' => sprintf(__('%1$s %2$sFirst Item%3$s %2$sSecond Item%3$s %4$s', 'piklist-demo'), '<ul>', '<li>', '</li>', '</ul>')
  ));

  $current_user = wp_get_current_user();

  piklist('field', array(
    'type' => 'html'
    ,'label' => __('Display information from your database.', 'piklist-demo')
    ,'value' => sprintf(__('Pull information and display it in the proper format. Does that sound good %s?', 'piklist-demo'), $current_user->display_name)
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));