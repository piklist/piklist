<?php
/*
Title: Conditional Fields
Order: 90
Tab: Conditions
Sub Tab: Basic
Flow: Demo Workflow
*/

  piklist('field', array(
    'type' => 'select'
    ,'field' => 'show_hide_select'
    ,'label' => __('Select: toggle a field', 'piklist-demo')
    ,'choices' => array(
      'show1' => 'Show first set'
      ,'show2' => 'Show second set'
      ,'hide' => 'Hide all'
    )
    ,'value' => 'hide'
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'show_hide_field_select_1'
    ,'label' => __('Show/Hide Field (Set 1)', 'piklist-demo')
    ,'description' => __('This field is toggled by the Select field above', 'piklist-demo')
    ,'conditions' => array(
      array(
        'field' => 'show_hide_select'
        ,'value' => 'show1'
      )
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'another_show_hide_field_select_1'
    ,'label' => __('Another Show/Hide Field (Set 1)', 'piklist-demo')
    ,'description' => __('This field is also toggled by the Select field above', 'piklist-demo')
    ,'conditions' => array(
      array(
        'field' => 'show_hide_select'
        ,'value' => 'show1'
      )
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'show_hide_field_select_set_2'
    ,'label' => __('Show/Hide Field (Set 2)', 'piklist-demo')
    ,'description' => __('This field is toggled by the Select field above', 'piklist-demo')
    ,'conditions' => array(
      array(
        'field' => 'show_hide_select'
        ,'value' => 'show2'
      )
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'another_show_hide_field_select_set_2'
    ,'label' => __('Another Show/Hide Field (Set 2)', 'piklist-demo')
    ,'description' => __('This field is also toggled by the Select field above', 'piklist-demo')
    ,'conditions' => array(
      array(
        'field' => 'show_hide_select'
        ,'value' => 'show2'
      )
    )
  ));

  piklist('field', array(
    'type' => 'select'
    ,'field' => 'select_show_hide_field_select_set_2'
    ,'label' => __('Select Show/Hide Field (Set 2)', 'piklist-demo')
    ,'description' => __('This field is also toggled by the Select field above', 'piklist-demo')
    ,'choices' => array(
      'a' => 'Choice A'
      ,'b' => 'Choice B'
      ,'c' => 'Choice C'
    )
    ,'conditions' => array(
      array(
        'field' => 'show_hide_select'
        ,'value' => 'show2'
      )
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'checkbox_show_hide_field_select_set_2'
    ,'label' => __('Checkbox Show/Hide Field (Set 2)', 'piklist-demo')
    ,'description' => __('This field is also toggled by the Select field above', 'piklist-demo')
    ,'choices' => array(
      'a' => 'Choice A'
      ,'b' => 'Choice B'
      ,'c' => 'Choice C'
    )
    ,'conditions' => array(
      array(
        'field' => 'show_hide_select'
        ,'value' => 'show2'
      )
    )
  ));

  piklist('field', array(
    'type' => 'radio'
    ,'field' => 'show_hide'
    ,'label' => __('Radio: toggle a field', 'piklist-demo')
    ,'choices' => array(
      'show' => 'Show'
      ,'hide' => 'Hide'
    )
    ,'value' => 'hide'
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'show_hide_field'
    ,'label' => __('Show/Hide Field', 'piklist-demo')
    ,'description' => __('This field is toggled by the Radio field above', 'piklist-demo')
    ,'conditions' => array(
      array(
        'field' => 'show_hide'
        ,'value' => 'show'
      )
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'show_hide_checkbox'
    ,'label' => __('Checkbox: toggle a field', 'piklist-demo')
    ,'choices' => array(
      'show' => 'Show'
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'show_hide_field_checkbox'
    ,'label' => __('Show/Hide Field', 'piklist-demo')
    ,'description' => __('This field is toggled by the Checkbox field above', 'piklist-demo')
    ,'conditions' => array(
      array(
        'field' => 'show_hide_checkbox'
        ,'value' => 'show'
      )
    )
  ));

  piklist('field', array(
    'type' => 'radio'
    ,'field' => 'change'
    ,'label' => __('Update a field', 'piklist-demo')
    ,'choices' => array(
      'hello-world' => 'Hello World'
      ,'clear' => 'Clear'
    )
    ,'value' => 'hello-world'
    ,'conditions' => array(
      array(
        'field' => 'update_field'
        ,'value' => 'hello-world'
        ,'update' => __('Hello World!', 'piklist-demo')
        ,'type' => 'update'
      )
      ,array(
        'field' => 'update_field'
        ,'value' => 'clear'
        ,'update' => ''
        ,'type' => 'update'
      )
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'update_field'
    ,'value' => 'Hello World!'
    ,'label' => __('Update This Field', 'piklist-demo')
    ,'description' => __('This field is updated by the field above', 'piklist-demo')
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'update_multiple'
    ,'label' => __('Update Multiple Fields', 'piklist-demo')
    ,'choices' => array(
      'true' => __('Update', 'piklist-demo')
    )
    ,'conditions' => array(
      array(
        'type' => 'update'
        ,'value' => 'true'
        ,'field' => 'update_multiple_1'
        ,'update' => __('Value 1', 'piklist-demo')
      )
      ,array(
        'type' => 'update'
        ,'value' => ''
        ,'field' => 'update_multiple_1'
        ,'update' => ''
      )
      ,array(
        'type' => 'update'
        ,'value' => 'true'
        ,'field' => 'update_multiple_2'
        ,'update' => __('Value 2', 'piklist-demo')
      )
      ,array(
        'type' => 'update'
        ,'value' => ''
        ,'field' => 'update_multiple_2'
        ,'update' => ''
      )        
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'label' => __('Update 1', 'pilist-demo')
    ,'field' => 'update_multiple_1'
  ));

  piklist('field', array(
    'type' => 'text'
    ,'label' => __('Update 2', 'pilist-demo')
    ,'field' => 'update_multiple_2'
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Media Section'
  ));