<?php
/*
Title: Text Fields
Post Type: piklist_demo
Order: 10
Collapse: false
Tab: Common
Sub Tab: Basic
Flow: Demo Workflow
*/
?>

<div class="piklist-demo-highlight">
  <?php _e('Text fields are at the core of most forms, and easily created with Piklist. Tooltip help can be added to any field with one line of code.', 'piklist-demo'); ?>
</div>

<?php

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'text'
    ,'scope' => 'post_meta'
    ,'label' => __('Text', 'piklist-demo')
    ,'help' => __('You can easily add tooltips to your fields with the help parameter.', 'piklist-demo')
    ,'attributes' => array(
      'class' => 'regular-text'
    )
  ));

  piklist('field', array(
    'type' => 'textarea'
    ,'field' => 'demo_textarea_large'
    ,'label' => __('Text Area', 'piklist-demo')
    ,'description' => 'class="large-text code" rows="10" columns="50"'
    ,'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'attributes' => array(
      'rows' => 10
      ,'cols' => 50
      ,'class' => 'large-text'
    )
  ));
  
  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));