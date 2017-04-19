<?php
/*
Title: Editor Examples
Post Type: piklist_demo
Order: 100
Lock: true
Flow: Demo Workflow
Tab: Common
Sub Tab: Editor
*/

  piklist('field', array(
    'type' => 'editor'
    ,'field' => 'post_content'
    ,'scope' => 'post'
    ,'required' => true
    ,'label' => __('Post Content', 'piklist-demo')
    ,'description' => __('This is a replacement for the post_content editor', 'piklist-demo')
    ,'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'options' => array(
      'wpautop' => true
      ,'media_buttons' => true
      ,'shortcode_buttons' => true
      ,'teeny' => false
      ,'dfw' => false
      ,'tinymce' => array(
        'resize' => false
        ,'wp_autoresize_on' => true
      )
      ,'quicktags' => true
      ,'drag_drop_upload' => true
    )
    ,'on_post_status' => array(
      'value' => 'lock'
    )
  ));
  
  piklist('field', array(
    'type' => 'editor'
    ,'field' => 'post_content_add_more'
    ,'label' => __('Post Content Add More', 'piklist-demo')
    ,'add_more' => true
    ,'description' => __('This is the teeny editor used in an add more repeater field.', 'piklist-demo')
    ,'value' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'options' => array(
      'media_buttons' => false
      ,'teeny' => true
      ,'textarea_rows' => 5
      ,'drag_drop_upload' => false
      ,'tinymce' => array(
        'resize' => false
        ,'wp_autoresize_on' => true
      )
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));