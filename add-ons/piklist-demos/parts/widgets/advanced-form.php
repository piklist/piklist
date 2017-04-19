<?php

  piklist('field', array(
    'type' => 'colorpicker'
    ,'field' => 'color'
    ,'label' => __('Color Picker', 'piklist-demo')
  ));

  piklist('field', array(
    'type' => 'colorpicker'
    ,'add_more' => true
    ,'field' => 'color_add_more'
    ,'label' => __('Color Picker Add More', 'piklist-demo')
  ));

  piklist('field', array(
    'type' => 'datepicker'
    ,'field' => 'date'
    ,'label' => __('Date', 'piklist-demo')
    ,'description' => __('Choose a date', 'piklist-demo')
    ,'options' => array(
      'dateFormat' => 'M d, yy'
    )
    ,'attributes' => array(
      'size' => 12
    )
    ,'value' => date('M d, Y', time() + 604800)
 
  ));

  piklist('field', array(
    'type' => 'datepicker'
    ,'field' => 'date_add_more'
    ,'add_more' => true
    ,'label' => __('Date Add More', 'piklist-demo')
    ,'description' => __('Choose a date', 'piklist-demo')
    ,'options' => array(
      'dateFormat' => 'M d, yy'
    )
    ,'attributes' => array(
      'size' => 12
    )
    ,'value' => date('M d, Y', time() + 604800)
 
  ));

  piklist('field', array(
    'type' => 'group'
    ,'field' => 'work_order_repair'
    ,'add_more' => true
    ,'label' => __('REPAIR', 'piklist-demo')
    ,'description' => __('Enter TYPE of Work, PRICE and DUE DATE', 'piklist-demo')
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'work'
        ,'columns' => 6
        ,'attributes' => array(
          'placeholder' => __('Type of work', 'piklist-demo')
        )
      )
      ,array(
        'type' => 'number'
        ,'field' => 'price'
        ,'columns' => 2
        ,'attributes' => array(
          'placeholder' => __('$', 'piklist-demo')
        )
      )
      ,array(
        'type' => 'datepicker'
        ,'field' => 'due'
        ,'columns' => 4
        ,'options' => array(
          'dateFormat' => 'M d, yy'
        )
        ,'attributes' => array(
          'placeholder' => __('Due date', 'piklist-demo')
        )
      )
    )
  ));


  piklist('field', array(
    'type' => 'group'
    ,'field' => 'demo_add_more_group_todo'
    ,'label' => __('Todo\'s', 'piklist-demo')
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'select'
        ,'field' => 'user'
        ,'label' => __('Assigned to', 'piklist-demo')
        ,'columns' => 4
        ,'choices' => array(
          'adam' => __('Adam', 'piklist-demo')
          ,'bill' => __('Bill', 'piklist-demo')
          ,'carol' => __('Carol', 'piklist-demo')
          )
        )
        ,array(
          'type' => 'text'
          ,'field' => 'task'
          ,'label' => __('Task', 'piklist-demo')
          ,'columns' => 8
        )
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'label' => __('Content Section', 'piklist-demo')
    ,'description' => __('When an add more field is nested it should be grouped to maintain the data relationships.', 'piklist-demo')
    ,'field' => 'demo_content'
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'csg_title'
        ,'label' => __('Title', 'piklist-demo')
        ,'columns' => 12
        ,'attributes' => array(
          'class' => 'large-text'
        )
      )
      ,array(
        'type' => 'text'
        ,'field' => 'csg_section'
        ,'label' => __('Section', 'piklist-demo')
        ,'columns' => 12
        ,'attributes' => array(
          'class' => 'large-text'
        )
      )
      ,array(
        'type' => 'group'
        ,'field' => 'content'
        ,'add_more' => true
        ,'fields' => array(
          array(
            'type' => 'select'
            ,'field' => 'post_id'
            ,'label' => __('Grade', 'piklist-demo')
            ,'columns' => 12
            ,'choices' => array(
              'a' => 'A'
              ,'b' => 'B'
              ,'c' => 'C'
            )
          )
        )
      )
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'field' => 'ingredient_section'
    ,'label' => __('Ingredients', 'piklist-demo')
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'ingredients_component_title'
        ,'label' => __('Section Title', 'piklist-demo')
        ,'columns' => 12
      )
      ,array(
        'type' => 'group'
        ,'field' => 'ingredient'
        ,'add_more' => true
        ,'fields' => array(
          array(
            'type' => 'text'
            ,'field' => 'ingredient_qty'
            ,'label' => __('Qty', 'piklist-demo')
            ,'columns' => 2
          )
          ,array(
            'type' => 'textarea'
            ,'field' => 'ingredient_description'
            ,'label' => __('Description', 'piklist-demo')
            ,'columns' => 10
            ,'attributes' => array(
              'rows' => 5
            )
          )
        )
      )
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'label' => __('Newsletter Signup', 'piklist-demo')
    ,'description' => __('Add email addresses with topic selectivity', 'piklist-demo')
    ,'field' => 'newsletter_signup'
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'first_name'
        ,'label' => __('First Name', 'piklist-demo')
        ,'columns' => 4
      )
      ,array(
        'type' => 'text'
        ,'field' => 'last_name'
        ,'label' => __('Last Name', 'piklist-demo')
        ,'columns' => 4
      )
      ,array(
        'type' => 'text'
        ,'field' => 'email'
        ,'label' => __('Email Address', 'piklist-demo')
        ,'columns' => 4
      )
      ,array(
        'type' => 'group'
        ,'field' => 'newsletters'
        ,'fields' => array(
          array(
            'type' => 'checkbox'
            ,'field' => 'newsletter_a'
            ,'label' => __('Newsletter A', 'piklist-demo')
            ,'columns' => 4
            ,'value' => 'first'
            ,'choices' => array(
              'first' => __('A-1', 'piklist-demo')
              ,'second' => __('A-2', 'piklist-demo')
              ,'third' => __('A-3', 'piklist-demo')
            )
          )
          ,array(
            'type' => 'checkbox'
            ,'field' => 'newsletter_b'
            ,'columns' => 4
            ,'label' => __('Newsletter B', 'piklist-demo')
            ,'value' => 'second'
            ,'choices' => array(
              'first' => __('B-1', 'piklist-demo')
              ,'second' => __('B-2', 'piklist-demo')
              ,'third' => __('B-3', 'piklist-demo')
            )
          )
          ,array(
            'type' => 'checkbox'
            ,'field' => 'newsletter_c'
            ,'columns' => 4
            ,'label' => __('Newsletter C', 'piklist-demo')
            ,'value' => 'third'
            ,'choices' => array(
              'first' => __('C-1', 'piklist-demo')
              ,'second' => __('C-2', 'piklist-demo')
              ,'third' => __('C-3', 'piklist-demo')
            )
          )
        )
      )
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'field' => 'newsletter_archive_demo'
    ,'label' => __('Newsletter Archives', 'piklist-demo')
    ,'columns' => 12
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'datepicker'
        ,'field' => 'newsletter_archive_title'
        ,'label' => __('Issue Date', 'piklist-demo')
        ,'columns' => 12
      )
      ,array(
        'type' => 'file'
        ,'field' => 'newsletter_file'
        ,'label' => __('Upload or choose an Image', 'piklist-demo')
        ,'columns' => 12
        ,'options' => array(
          'modal_title' => __('Add File(s)', 'piklist-demo')
          ,'button' => __('Add', 'piklist-demo')
        )
      )
      ,array(
        'type' => 'editor'
        ,'field' => 'newsletter_highlights'
        ,'label' => __('Highlights of this issue', 'piklist-demo')
        ,'columns' => 12
        ,'options' => array(
          'wpautop' => true
          ,'media_buttons' => false
          ,'teeny' => false
          ,'dfw' => false
          ,'tinymce' => true
          ,'quicktags' => true
        )
      )
      ,array(
        'type' => 'file'
        ,'field' => 'newsletter_image'
        ,'columns' => 12
        ,'label' => __('Add a cover image', 'piklist-demo')
        ,'options' => array(
          'modal_title' => __('Add File(s)', 'piklist-demo')
          ,'button' => __('Add', 'piklist-demo')
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