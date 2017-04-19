<?php
/*
Title: Party Invite
Order: 100
Tab: Conditions
Sub Tab: Advanced
Setting: piklist_demo_fields
Flow: Demo Workflow
*/
  
  piklist('field', array(
    'type' => 'html'
    ,'field' => '_message_meal'
    ,'value' => __('We only serve steaks rare.', 'piklist-demo')
    ,'attributes' => array(
      'class' => 'piklist-error-text'
    )
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
      ,'yes' => 'Yes'
      ,'no' => 'No'
      ,'maybe' => 'Maybe'
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
      'chicken' => 'Chicken'
      ,'steak' => 'Steak'
      ,'vegetarian' => 'Vegetarian'
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
      'yes' => 'Yes'
      ,'no' => 'No'
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
    ,'attributes' => array(
      'class' => 'piklist-error-text'
    )
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
        ,'label' => __('Name', 'piklist-demo')
      )
      ,array(
        'type' => 'radio'
        ,'field' => 'guest_one_meal'
        ,'label' => __('Meal choice', 'piklist-demo')
        ,'choices' => array(
          'chicken' => 'Chicken'
          ,'steak' => 'Steak'
          ,'vegetarian' => 'Vegetarian'
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
          'chicken' => 'Chicken'
          ,'steak' => 'Steak'
          ,'vegetarian' => 'Vegetarian'
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
    ,'type' => 'Settings Section'
  ));