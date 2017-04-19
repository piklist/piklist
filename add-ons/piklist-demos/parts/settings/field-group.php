<?php
/*
Title: Groups
Order: 80
Tab: Layout
Sub Tab: Field Groups
Setting: piklist_demo_fields
Flow: Demo Workflow
*/

  piklist('field', array(
    'type' => 'group'
    ,'field' => 'address_group'
    ,'label' => __('Address (Grouped)', 'piklist-demo')
    ,'list' => false
    ,'description' => __('A grouped field with a key set. Data is not searchable, since it is saved in an array.', 'piklist-demo')
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'address_1'
        ,'label' => __('Street Address', 'piklist-demo')
        ,'columns' => 12
        ,'attributes' => array(
          'placeholder' => 'Street Address'
        )
      )
      ,array(
        'type' => 'text'
        ,'field' => 'address_2'
        ,'label' => __('PO Box, Suite, etc.', 'piklist-demo')
        ,'columns' => 12
        ,'attributes' => array(
          'placeholder' => 'PO Box, Suite, etc.'
        )
      )
      ,array(
        'type' => 'text'
        ,'field' => 'city'
        ,'label' => __('City', 'piklist-demo')
        ,'columns' => 5
        ,'attributes' => array(
          'placeholder' => 'City'
        )
      )
      ,array(
        'type' => 'select'
        ,'field' => 'state'
        ,'label' => __('State', 'piklist-demo')
        ,'columns' => 4
        ,'choices' => piklist_demo_get_states()
      )
      ,array(
        'type' => 'text'
        ,'field' => 'postal_code'
        ,'label' => __('Postal Code', 'piklist-demo')
        ,'columns' => 3
        ,'attributes' => array(
          'placeholder' => 'Postal Code'
        )
      )
    )
  ));
  
  piklist('field', array(
    'type' => 'group'
    ,'field' => 'address_group_add_more'
    ,'add_more' => true
    ,'label' => __('Address (Grouped/Add more)', 'piklist-demo')
    ,'description' => __('A grouped field using add more.', 'piklist-demo')
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'address_1'
        ,'label' => __('Street Address', 'piklist-demo')
        ,'columns' => 12
      )
      ,array(
        'type' => 'text'
        ,'field' => 'address_2'
        ,'label' => __('PO Box, Suite, etc.', 'piklist-demo')
        ,'columns' => 12
      )
      ,array(
        'type' => 'text'
        ,'field' => 'city'
        ,'label' => __('City', 'piklist-demo')
        ,'columns' => 5
      )
      ,array(
        'type' => 'select'
        ,'field' => 'state'
        ,'label' => __('State', 'piklist-demo')
        ,'columns' => 4
        ,'choices' => piklist_demo_get_states()
      )
      ,array(
        'type' => 'text'
        ,'field' => 'postal_code'
        ,'label' => __('Postal Code', 'piklist-demo')
        ,'columns' => 3
      )
    )
  ));
  
  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Settings Section'
  ));