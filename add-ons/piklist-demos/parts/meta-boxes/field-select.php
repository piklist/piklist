<?php
/*
Title: Select Fields
Post Type: piklist_demo
Order: 30
Tab: Common
Sub Tab: Lists
Flow: Demo Workflow
*/

  piklist('field', array(
    'type' => 'select'
    ,'field' => 'select'
    ,'label' => __('Select', 'piklist-demo')
    ,'value' => 'third'
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => __('Second Choice', 'piklist-demo')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
  ));
  
  piklist('field', array(
    'type' => 'select'
    ,'field' => 'select_add_more'
    ,'add_more' => true
    ,'label' => __('Add More', 'piklist-demo')
    ,'value' => 'third'
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => __('Second Choice', 'piklist-demo')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
  ));
  
  piklist('field', array(
    'type' => 'select'
    ,'field' => 'select_optgroup'
    ,'label' => __('Select with Option Groups', 'piklist-demo')
    ,'value' => 'third'
    ,'choices' => array(
      'Group 1' => array(
        'first' => __('First Choice', 'piklist-demo')
        ,'second' => __('Second Choice', 'piklist-demo')
        ,'third' => __('Third Choice', 'piklist-demo')
      )
      ,'Group 2' => array(
        'first' => __('First Choice', 'piklist-demo')
        ,'second' => __('Second Choice', 'piklist-demo')
        ,'third' => __('Third Choice', 'piklist-demo')
      )
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));