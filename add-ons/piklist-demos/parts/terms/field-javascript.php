<?php
/*
Title: Color / Date Pickers
Order: 40
Taxonomy: piklist_demo_type
Tab: Common
Sub Tab: Basic
Flow: Demo Workflow
*/

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
    ,'type' => 'Term Section'
  ));