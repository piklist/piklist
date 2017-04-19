<?php

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'text_class_small'
    ,'label' => __('Text', 'piklist-demo')
    ,'value' => 'Lorem'
    ,'help' => __('You can easily add tooltips to your fields with the help parameter.', 'piklist-demo')
    ,'attributes' => array(
      'class' => 'regular-text'
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'text_columns_element'
    ,'label' => __('Columns Element', 'piklist-demo')
    ,'description' => 'columns="6"'
    ,'value' => 'Lorem'
    ,'columns' => 6
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'text_add_more'
    ,'add_more' => true
    ,'label' => __('Text Add More', 'piklist-demo')
    ,'description' => 'add_more="true"'
    ,'value' => 'Lorem'
  ));

  piklist('field', array(
    'type' => 'number'
    ,'field' => 'number'
    ,'label' => __('Number', 'piklist-demo')
    ,'description' => 'ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'value' => 5
    ,'attributes' => array(
      'class' => 'small-text'
      ,'step' => 1
      ,'min' => 0
      ,'max' => 10
    )
  ));

  piklist('field', array(
    'type' => 'textarea'
    ,'field' => 'demo_textarea_large'
    ,'label' => __('Large Code', 'piklist-demo')
    ,'description' => 'class="large-text code" rows="10" columns="50"'
    ,'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'attributes' => array(
      'rows' => 10
      ,'cols' => 50
      ,'class' => 'large-text code'
    )
  ));

  piklist('field', array(
    'type' => 'file'
    ,'field' => 'upload_media'
    ,'label' => __('Add File(s)', 'piklist-demo')
    ,'description' => __('This is the uploader seen in the admin by default.', 'piklist-demo')
    ,'options' => array(
      'modal_title' => __('Add File(s)', 'piklist-demo')
      ,'button' => __('Add', 'piklist-demo')
    )
    ,'validate' => array(
      array(
        'type' => 'limit'
        ,'options' => array(
          'min' => 0
          ,'max' => 2
        )
      )
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'field' => 'slides'
    ,'add_more' => true
    ,'label' => __('Slide Images', 'piklist-demo')
    ,'description' => __('Add the slides for the slideshow.  You can add as many slides as you want, and they can be drag-and-dropped into the order that you would like them to appear.', 'piklist-demo')
    ,'fields'  => array(
      array(
        'type' => 'file'
        ,'field' => 'image'
        ,'label' => __('Slides', 'piklist-demo')
        ,'columns' => 12
        ,'validate' => array(
          array(
            'type' => 'limit'
            ,'options' => array(
              'min' => 1
              ,'max' => 1
            )
          )
        )
      )
      ,array(
        'type' => 'text'
        ,'field' => 'url'
        ,'label' => __('URL', 'piklist-demo')
        ,'columns' => 12
      )
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Widget'
  ));