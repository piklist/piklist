<?php

  piklist('field', array(
    'type' => 'select'
    ,'field' => 'show_hide_select'
    ,'label' => __('Select: toggle a field', 'piklist-demo')
    ,'choices' => array(
      'show1' => __('Show first set', 'piklist-demo')
      ,'show2' => __('Show second set', 'piklist-demo')
      ,'hide' => __('Hide all', 'piklist-demo')
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
      'a' => __('Choice A', 'piklist-demo')
      ,'b' => __('Choice B', 'piklist-demo')
      ,'c' => __('Choice C', 'piklist-demo')
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
      'a' => __('Choice A', 'piklist-demo')
      ,'b' => __('Choice B', 'piklist-demo')
      ,'c' => __('Choice C', 'piklist-demo')
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
      'show' => __('Show', 'piklist-demo')
      ,'hide' => __('Hide', 'piklist-demo')
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
      'show' => __('Show', 'piklist-demo')
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
      'hello-world' => __('Hello World', 'piklist-demo')
      ,'clear' => __('Clear', 'piklist-demo')
    )
    ,'value' => 'hello-world'
    ,'conditions' => array(
      array(
        'field' => 'update_field'
        ,'value' => 'hello-world'
        ,'update' => 'Hello World!'
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
    'type' => 'html'
    ,'field' => '_message_meal'
    ,'value' => __('We only serve steaks rare.', 'piklist-demo')
    ,'conditions' => array(
      'relation' => 'or'
      ,array(
        'field' => 'guest_meal'
        ,'value' => 'steak'
      )
      ,array(
        'field' => 'guest_one_meal'
        ,'value' => 'steak'
      )
      ,array(
        'field' => 'guest_two_meal'
        ,'value' => 'steak'
      )
    )
  ));

  piklist('field', array(
    'type' => 'select'
    ,'field' => 'attending'
    ,'label' => __('Are you coming to the party?', 'piklist-demo')
    ,'choices' => array(
      '' => ''
      ,'yes' => __('Yes', 'piklist-demo')
      ,'no' => __('No', 'piklist-demo')
      ,'maybe' => __('Maybe', 'piklist-demo')
    )
    ,'conditions' => array(
      array(
        'field' => 'guests'
        ,'value' => array('yes', 'maybe')
        ,'update' => 'yes'
        ,'type' => 'update'
      )
    )
  ));

  piklist('field', array(
    'type' => 'radio'
    ,'field' => 'guest_meal'
    ,'label' => __('Choose meal type', 'piklist-demo')
    ,'choices' => array(
      'chicken' => __('Chicken', 'piklist-demo')
      ,'steak' => __('Steak', 'piklist-demo')
      ,'vegetarian' => __('Vegetarian', 'piklist-demo')
    )
    ,'conditions' => array(
      array(
        'field' => 'attending'
        ,'value' => array('', 'no')
        ,'compare' => '!='
      )
    )
  ));

  piklist('field', array(
    'type' => 'select'
    ,'field' => 'guests'
    ,'label' => __('Are you bringing guests', 'piklist-demo')
    ,'description' => __('Coming to party != (No or empty)', 'piklist-demo')
    ,'choices' => array(
      'yes' => __('Yes', 'piklist-demo')
      ,'no' => __('No', 'piklist-demo')
    )
    ,'conditions' => array(
      array(
        'field' => 'attending'
        ,'value' => array('', 'no')
        ,'compare' => '!='
      )
    )
  ));

  piklist('field', array(
    'type' => 'html'
    ,'field' => '_message_guests'
    ,'value' => __('Sorry, only two guests are allowed.', 'piklist-demo')
    ,'conditions' => array(
      array(
        'field' => 'guests_number'
        ,'value' => '3'
      )
    )
  ));

  piklist('field', array(
    'type' => 'number'
    ,'field' => 'guests_number'
    ,'label' => __('How many guests?', 'piklist-demo')
    ,'description' => __('Coming to party != (No or empty) AND Guests = Yes', 'piklist-demo')
    ,'value' => 1
    ,'attributes' => array(
      'class' => 'small-text'
      ,'step' => 1
      ,'min' => 1
      ,'max' => 3
    )
    ,'conditions' => array(
      array(
        'field' => 'attending'
        ,'value' => array('', 'no')
        ,'compare' => '!='
      )
      ,array(
        'field' => 'guests'
        ,'value' => 'yes'
      )
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'label' => __('Guest One', 'piklist-demo')
    ,'description' => __('Number of guests != empty', 'piklist-demo')
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'guest_one'
        ,'label' => 'Name'
      )
      ,array(
        'type' => 'radio'
        ,'field' => 'guest_one_meal'
        ,'label' => __('Meal choice', 'piklist-demo')
        ,'choices' => array(
          'chicken' => __('Chicken', 'piklist-demo')
          ,'steak' => __('Steak', 'piklist-demo')
          ,'vegetarian' => __('Vegetarian', 'piklist-demo')
        )
      )
    )
    ,'conditions' => array(
      array(
        'field' => 'guests_number'
        ,'value' => array('', '0')
        ,'compare' => '!='
      )
      ,array(
        'field' => 'guests'
        ,'value' => 'yes'
      )
      ,array(
        'field' => 'attending'
        ,'value' => array('', 'no')
        ,'compare' => '!='
      )
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'label' => __('Guest Two', 'piklist-demo')
    ,'description' => __('Number of guests != (empty or 1)', 'piklist-demo')
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'guest_two'
        ,'label' => __('Name', 'piklist-demo')
      )
      ,array(
        'type' => 'radio'
        ,'field' => 'guest_two_meal'
        ,'label' => __('Meal choice', 'piklist-demo')
        ,'choices' => array(
          'chicken' => __('Chicken', 'piklist-demo')
          ,'steak' => __('Steak', 'piklist-demo')
          ,'vegetarian' => __('Vegetarian', 'piklist-demo')
        )
      )
    )
    ,'conditions' => array(
      array(
        'field' => 'guests_number'
        ,'value' => array('', '0', '1')
        ,'compare' => '!='
      )
      ,array(
        'field' => 'attending'
        ,'value' => array('', 'no')
        ,'compare' => '!='
      )
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Widget'
  ));