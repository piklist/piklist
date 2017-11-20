<?php

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'text_required'
    ,'label' => __('Text Required', 'piklist-demo')
    ,'description' => "required => true"
    ,'attributes' => array(
      'class' => 'large-text'
    )
    ,'required' => true
  ));
  
  piklist('field', array(
    'type' => 'select'
    ,'field' => 'validate_select_required'
    ,'label' => __('Custom Required Message', 'piklist-demo')
    ,'description' => "required => custom message"
    ,'choices' => array(
      '1' => 'Choice #1'
      ,'2' => 'Choice #2'
      ,'3' => 'Choice #3'
    )
    ,'attributes' => array(
      'class' => 'large-text'
      ,'multiple' => true
    )
    ,'required' => 'must have at least one option selected'
  ));

  piklist('field', array(
    'type'    => 'group'
    ,'field'   => 'group_required'
    ,'label'   => __('Group Required', 'piklist-demo')
    ,'add_more'=> true
    ,'fields'  => array(
      array(
        'type' => 'text'
        ,'field' => 'name'
        ,'label' => __('Name', 'piklist-demo')
        ,'columns' => 9
      )
      ,array(
        'type' => 'checkbox'
        ,'field' => 'hierarchical'
        ,'label' => __('Type', 'piklist-demo')
        ,'required' => true
        ,'columns' => 3
        ,'choices' => array(
          'true' => 'Hierarchical'
        )
        ,'attributes' => array(
          'placeholder' => __('placeholder', 'piklist-demo')
        )
      )
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'label' => __('File Name', 'piklist-demo')
    ,'field' => 'file_name'
    ,'description' => __('Converts multiple words to a valid file name', 'piklist-demo')
    ,'sanitize' => array(
      array(
        'type' => 'file_name'
      )
    )
    ,'attributes' => array(
      'class' => 'large-text'
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'validate_emaildomain'
    ,'label' => __('Email address', 'piklist-demo')
    ,'description' => __('Validate Email and Email Domain', 'piklist-demo')
    ,'attributes' => array(
      'class' => 'large-text'
    )
    ,'validate' => array(
      array(
        'type' => 'email'
      )
      ,array(
        'type' => 'email_domain'
      )
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'validate_file_exists'
    ,'label' => __('File exists?', 'piklist-demo')
    ,'description' => sprintf(__('Test with: %s', 'piklist-demo'), 'http://wordpress.org/plugins/about/readme.txt')
    ,'attributes' => array(
      'class' => 'large-text'
    )
    ,'validate' => array(
      array(
        'type' => 'file_exists'
      )
    )
  ));


  piklist('field', array(
    'type' => 'text'
    ,'field' => 'validate_image'
    ,'label' => __('Image', 'piklist-demo')
    ,'description' => sprintf(__('Test with: %s', 'piklist-demo'), 'https://piklist.com/wp-content/themes/piklistcom-base/images/piklist-logo@2x.png')
    ,'attributes' => array(
      'class' => 'large-text'
    )
    ,'validate' => array(
      array(
        'type' => 'image'
      )
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
    ,'validate' => array(
      array(
        'type' => 'limit'
        ,'options' => array(
          'min' => 2
          ,'max' => 2
        )
      )
    )
  ));


  piklist('field', array(
    'type' => 'file'
    ,'field' => 'upload_media'
    ,'label' => __('Add File(s)', 'piklist-demo')
    ,'required' => true
    ,'options' => array(
      'modal_title' => __('Add File(s)', 'piklist-demo')
      ,'button' => __('Add', 'piklist-demo')
    )
    ,'attributes' => array(
      'class' => 'large-text'
    )
    ,'validate' => array(
      array(
        'type' => 'limit'
        ,'options' => array(
          'min' => 1
          ,'max' => 1
        )
      )
    )
  ));


    piklist('field', array(
    'type' => 'group'
    ,'field' => 'address_group_add_more'
    ,'add_more' => true
    ,'label' => __('Grouped/Add more with Limit', 'piklist-demo')
    ,'description' => __('No more than 2', 'piklist-demo')
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'group_field_1'
        ,'label' => __('Field 1', 'piklist-demo')
        ,'columns' => 12
      )
      ,array(
        'type' => 'text'
        ,'field' => 'group_field_2'
        ,'label' => __('Field 2', 'piklist-demo')
        ,'columns' => 12
      )
    )
    ,'validate' => array(
      array(
        'type' => 'limit'
        ,'options' => array(
          'min' => 1
          ,'max' => 2
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
