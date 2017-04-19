<?php
/*
Title: ColorPicker Fields
Post Type: piklist_demo
Order: 60
Tab: Common
Sub Tab: Basic
Flow: Demo Workflow
*/
?>

<p class="piklist-demo-highlight">
  <?php _e('WordPress ColorPicker fields are super simple to create. Piklist handles all the Javascript.', 'piklist-demo');?>
</p>

<?php

  piklist('field', array(
    'type' => 'colorpicker'
    ,'field' => 'color'
    ,'label' => __('Color Picker', 'piklist-demo')
    ,'on_post_status' => array(
      'value' => 'lock'
    )
  ));

  piklist('field', array(
    'type' => 'colorpicker'
    ,'add_more' => true
    ,'field' => 'color_add_more'
    ,'label' => __('Color Picker add more', 'piklist-demo')
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
