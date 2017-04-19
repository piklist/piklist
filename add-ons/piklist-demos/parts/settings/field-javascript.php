<?php
/*
Title: Color / Date Pickers
Order: 40
Tab: Common
Sub Tab: Basic
Setting: piklist_demo_fields
Flow: Demo Workflow
*/
?>


<div class="piklist-demo-highlight">
  <?php _e('WordPress ColorPicker fields are super simple to create. Piklist handles all the Javascript.', 'piklist-demo');?>
</div>

<?php

  piklist('field', array(
    'type' => 'colorpicker'
    ,'field' => 'colorpicker'
    ,'label' => __('Color Picker', 'piklist-demo')
    ,'attributes' => array(
      'class' => 'small-text'
    )
  ));

  piklist('field', array(
    'type' => 'datepicker'
    ,'field' => 'datepicker'
    ,'label' => __('Date Picker', 'piklist-demo')
    ,'attributes' => array(
      'class' => 'text'
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Settings Section'
  ));