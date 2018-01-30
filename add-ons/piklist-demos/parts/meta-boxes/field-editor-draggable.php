<?php
/*
Title: Draggable Editor
Post Type: piklist_demo
Order: 110
Flow: Demo Workflow
Tab: Common
Sub Tab: Editor
*/

  piklist('field', array(
    'type' => 'editor'
    ,'field' => 'post_content_full'
    ,'scope' => 'post_meta'
    ,'template' => 'field'
    ,'value' => sprintf(__('You can remove the left label when displaying the editor by defining %1$s in the field parameters. This will make it look like the default WordPress editor. To learn about replacing the WordPress editor %2$sread our Tutorial%3$s.', 'piklist-demo'), '<code>\'template\'=>\'field\'</code>', '<a href="https://piklist.com/user-guide/tutorials/replacing-wordpress-post-editor/">', '</a>')
    ,'options' => array(
      'wpautop' => true
      ,'media_buttons' => true
      ,'shortcode_buttons' => true
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
      ,'drag_drop_upload' => true
    )
    ,'on_post_status' => array(
      'value' => 'lock'
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));