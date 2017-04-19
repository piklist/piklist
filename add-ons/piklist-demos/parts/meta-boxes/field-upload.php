<?php
/*
Title: Upload Fields
Post Type: piklist_demo
Order: 30
Tab: Common
Sub Tab: Basic
Flow: Demo Workflow
*/

  // NOTE: If the post_status of an attachment is anything but inherit or private it will NOT be
  // shown on the Media page in the admin, but it is in the database and can be found using query_posts
  // or get_posts or get_post etc....  
  
?>

<div class="piklist-demo-highlight">
  <?php _e('Piklist comes standard with two upload fields: Basic and Media. The Media field works just like the standard WordPress media field, while the Basic uploader is great for simple forms.', 'piklist-demo');?>
</div>

<?php

  piklist('field', array(
    'type' => 'file'
    ,'field' => 'upload_basic'
    ,'scope' => 'post_meta'
    ,'label' => __('Basic Upload Field', 'piklist-demo')
    ,'options' => array(
      'basic' => true
    )
  ));
  
  piklist('field', array(
    'type' => 'file'
    ,'field' => 'upload_media'
    ,'scope' => 'post_meta'
    ,'label' => __('Media Uploader', 'piklist-demo')
    ,'options' => array(
      'modal_title' => __('Add File(s)', 'piklist-demo')
      ,'button' => __('Add', 'piklist-demo')
    )
  ));
  
  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));