<?php
/*
Title: Radio Fields
Post Type: piklist_demo
Order: 20
Tab: Common
Sub Tab: Lists
Flow: Demo Workflow
*/

  piklist('field', array(
    'type' => 'radio'
    ,'field' => 'radio'
    ,'label' => __('Radio', 'piklist-demo')
    ,'value' => 'third'
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => __('Second Choice', 'piklist-demo')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
  ));
  
  piklist('field', array(
    'type' => 'radio'
    ,'field' => 'radio_add_more'
    ,'label' => __('Radio Add More', 'piklist-demo')
    ,'add_more' => true
    ,'value' => 'third'
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => __('Second Choice', 'piklist-demo')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
  ));
  
  piklist('field', array(
    'type' => 'radio'
    ,'field' => 'radio_inline'
    ,'label' => __('Single Line', 'piklist-demo')
    ,'value' => 'no'
    ,'list' => false
    ,'choices' => array(
      'yes' => __('Yes', 'piklist-demo')
      ,'no' => __('No', 'piklist-demo')
    )
  ));
  
  piklist('field', array(
    'type' => 'group'
    ,'field' => 'radio_list'
    ,'label' => __('Group Lists', 'piklist-demo')
    ,'fields' => array(
      array(
        'type' => 'radio'
        ,'field' => 'radio_list_1'
        ,'label' => __('List #1', 'piklist-demo')
        ,'label_position' => 'before'
        ,'value' => 'third'
        ,'choices' => array(
          'first' => __('First Choice', 'piklist-demo')
          ,'third' => __('Third Choice', 'piklist-demo')
        )
        ,'columns' => 6
      )
      ,array(
        'type' => 'radio'
        ,'field' => 'radio_list_2'
        ,'label' => __('List #2', 'piklist-demo')
        ,'label_position' => 'before'
        ,'value' => 'second'
        ,'choices' => array(
          'first' => __('First Choice', 'piklist-demo')
          ,'second' => __('Second Choice', 'piklist-demo')
          ,'third' => __('Third Choice', 'piklist-demo')
        )
        ,'columns' => 6
      )
    )
  ));
  
  piklist('field', array(
    'type' => 'radio'
    ,'field' => 'radio_nested'
    ,'label' => __('Nested Field', 'piklist-demo')
    ,'value' => 'third'
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => sprintf(__('Second Choice with a nested %s input.', 'piklist-demo'), '[field=radio_nested_text]')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'radio_nested_text'
        ,'value' => '12345'
        ,'embed' => true
        ,'attributes' => array(
          'class' => 'small-text'
        )
      )
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));