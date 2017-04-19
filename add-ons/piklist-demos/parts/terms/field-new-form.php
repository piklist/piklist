<?php
/*
Title: Text Fields
Order: 10
Taxonomy: piklist_demo_type
New: true
*/

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'checkbox'
    ,'label' => __('Checkbox', 'piklist-demo')
    ,'value' => 'third'
    ,'choices' => array(
      'first' => 'First Choice'
      ,'second' => 'Second Choice'
      ,'third' => 'Third Choice'
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'checkbox_add_more'
    ,'label' => __('Checkbox Add More', 'piklist-demo')
    ,'add_more' => true
    ,'value' => 'third'
    ,'choices' => array(
      'first' => 'First Choice'
      ,'second' => 'Second Choice'
      ,'third' => 'Third Choice'
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'checkbox_inline'
    ,'label' => __('Single Line', 'piklist-demo')
    ,'value' => 'that'
    ,'list' => false
    ,'choices' => array(
      'this' => 'This'
      ,'that' => 'That'
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'field' => 'checkbox_list'
    ,'label' => __('Group Lists', 'piklist-demo')
    ,'list' => false
    ,'fields' => array(
      array(
        'type' => 'checkbox'
        ,'field' => 'checkbox_list_1'
        ,'label' => __('List #1', 'piklist-demo')
        ,'label_position' => 'before'
        ,'value' => 'third'
        ,'choices' => array(
          'first' => 'First Choice'
          ,'third' => 'Third Choice'
        )
        ,'columns' => 6
      )
      ,array(
        'type' => 'checkbox'
        ,'field' => 'checkbox_list_2'
        ,'label' => __('List #2', 'piklist-demo')
        ,'label_position' => 'before'
        ,'value' => 'second'
        ,'choices' => array(
          'first' => 'First Choice'
          ,'second' => 'Second Choice'
          ,'third' => 'Third Choice'
        )
        ,'columns' => 6
      )
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'checkbox_nested'
    ,'label' => __('Nested Field', 'piklist-demo')
    ,'value' => array(
      'first'
      ,'third'
    )
    ,'choices' => array(
      'first' => 'First Choice'
      ,'second' => sprintf(__('Second Choices with a nested %s input.', 'piklist-demo'), '[field=checkbox_nested_text]')
      ,'third' => 'Third Choice'
    )
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'checkbox_nested_text'
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
    ,'type' => 'Term Section'
  ));