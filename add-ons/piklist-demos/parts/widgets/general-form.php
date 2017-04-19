<?php

  piklist('field', array(
    'type' => 'editor'
    ,'field' => 'content'
    ,'label' => __('Content', 'piklist-demo')
    ,'description' => __('This is the standard post box, now placed in a Piklist WorkFlow.', 'piklist-demo')
    ,'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'options' => array(
      'wpautop' => true
      ,'media_buttons' => true
      ,'tabindex' => ''
      ,'editor_css' => ''
      ,'editor_class' => ''
      ,'teeny' => false
      ,'dfw' => false
      ,'tinymce' => array(
        'resize' => false
        ,'wp_autoresize_on' => true
      )
      ,'quicktags' => true
    )
  ));

  piklist('field', array(
    'type' => 'editor'
    ,'field' => 'addmore_content'
    ,'label' => __('Teeny Add More', 'piklist-demo')
    ,'add_more' => true
    ,'description' => __('This is the teeny editor with an add more.', 'piklist-demo')
    ,'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'options' => array(
      'drag_drop_upload' => true
      ,'editor_height' => 100
      ,'media_buttons' => false
      ,'teeny' => true
      ,'quicktags' => false
      ,'tinymce' => array(
        'autoresize_min_height' => 100
        ,'toolbar1' => 'bold,italic,bullist,numlist,blockquote,link,unlink,undo,redo'
        ,'resize' => false
        ,'wp_autoresize_on' => true
      )
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Widget'
  ));