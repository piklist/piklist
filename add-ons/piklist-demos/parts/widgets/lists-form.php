<?php

  piklist('field', array(
    'type' => 'select'
    ,'field' => 'multiselect'
    ,'label' => __('Multiselect', 'piklist-demo')
    ,'value' => 'third'
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => __('Second Choice', 'piklist-demo')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
    ,'attributes' => array(
      'multiple' => 'multiple'
    )
  ));

  piklist('field', array(
    'type' => 'select'
    ,'field' => 'multiselect_add_more'
    ,'label' => __('Multiselect Add More', 'piklist-demo')
    ,'value' => 'third'
    ,'add_more' => true
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => __('Second Choice', 'piklist-demo')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
    ,'attributes' => array(
      'multiple' => 'multiple' // This changes a select field into a multi-select field
    )
  ));

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
    ,'label' => __('Select Add More', 'piklist-demo')
    ,'add_more' => true
    ,'value' => 'third'
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => __('Second Choice', 'piklist-demo')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'checkbox'
    ,'label' => __('Checkbox', 'piklist-demo')
    ,'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'value' => 'third'
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => __('Second Choice', 'piklist-demo')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'checkbox_add_more'
    ,'label' => __('Checkbox Add More', 'piklist-demo')
    ,'add_more' => true
    ,'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'value' => 'third'
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => __('Second Choice', 'piklist-demo')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'checkbox_inline'
    ,'label' => __('Single Line', 'piklist-demo')
    ,'value' => 'that'
    ,'list' => false
    ,'choices' => array(
      'this' => __('This', 'piklist-demo')
      ,'that' => __('That', 'piklist-demo')
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'field' => 'checkbox_list'
    ,'label' => __('Group Lists', 'piklist-demo')
    ,'list' => false
    ,'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'fields' => array(
      array(
        'type' => 'checkbox'
        ,'field' => 'checkbox_list_1'
        ,'label' => __('List #1', 'piklist-demo')
        ,'label_position' => 'before'
        ,'value' => 'second'
        ,'choices' => array(
          'first' => __('1-1 Choice', 'piklist-demo')
          ,'second' => __('1-2 Choice', 'piklist-demo')
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
          'first' => __('2-1 Choice', 'piklist-demo')
          ,'second' => __('2-2 Choice', 'piklist-demo')
          ,'third' => __('2-3 Choice', 'piklist-demo')
        )
        ,'columns' => 6
      )
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'checkbox_nested'
    ,'label' => __('Nested Field', 'piklist-demo')
    ,'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'value' => array(
      'first'
      ,'third'
    )
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => sprintf(__('Second Choice with a nested %s input.', 'piklist-demo'), '[field=checkbox_nested_text]')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'checkbox_nested_text'
        ,'value' => '123'
        ,'embed' => true
        ,'attributes' => array(
          'class' => 'small-text'
        )
      )
    )
  ));

  piklist('field', array(
    'type' => 'radio'
    ,'field' => 'radio'
    ,'label' => __('Radio', 'piklist-demo')
    ,'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
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
    ,'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'value' => 'second'
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
    ,'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'fields' => array(
      array(
        'type' => 'radio'
        ,'field' => 'radio_list_1'
        ,'label' => __('List #1', 'piklist-demo')
        ,'label_position' => 'before'
        ,'value' => 'second'
        ,'choices' => array(
          'first' => __('1-1 Choice', 'piklist-demo')
          ,'second' => __('1-2 Choice', 'piklist-demo')
        )
        ,'columns' => 6
      )
      ,array(
        'type' => 'radio'
        ,'field' => 'radio_list_2'
        ,'label' => 'List #2'
        ,'label_position' => 'before'
        ,'value' => 'second'
        ,'choices' => array(
          'first' => __('2-1 Choice', 'piklist-demo')
          ,'second' => __('2-2 Choice', 'piklist-demo')
          ,'third' => __('2-3 Choice', 'piklist-demo')
        )
        ,'columns' => 6
      )
    )
  ));

  piklist('field', array(
    'type' => 'radio'
    ,'field' => 'radio_nested'
    ,'label' => __('Nested Field', 'piklist-demo')
    ,'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
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
        ,'value' => '123'
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
    ,'type' => 'Widget'
  ));