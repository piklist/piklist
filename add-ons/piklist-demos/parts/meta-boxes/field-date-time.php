<?php
/*
Title: DatePicker Fields
Post Type: piklist_demo
Order: 50
Tab: Common
Sub Tab: Basic
Flow: Demo Workflow
*/
?>

<p class="piklist-demo-highlight">
  <?php _e('DatePicker fields are easy to create and format.', 'piklist-demo');?>
</p>

<?php

  piklist('field', array(
    'type' => 'datepicker'
    ,'field' => 'date'
    ,'label' => __('Date', 'piklist-demo')
    ,'description' => __('Choose a date', 'piklist-demo')
    ,'options' => array(
      'dateFormat' => 'M d, yy'
    )
    ,'attributes' => array(
      'size' => 12
    )
    ,'value' => date('M d, Y', time() + 604800)
    ,'on_post_status' => array(
      'value' => 'lock'
    )
  ));

  piklist('field', array(
    'type' => 'datepicker'
    ,'field' => 'date_add_more'
    ,'add_more' => true
    ,'label' => __('Date add more', 'piklist-demo')
    ,'description' => __('Choose a date', 'piklist-demo')
    ,'options' => array(
      'dateFormat' => 'M d, yy'
    )
    ,'attributes' => array(
      'size' => 12
    )
    ,'value' => date('M d, Y', time() + 604800)
    ,'on_post_status' => array(
      'value' => 'lock'
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));
