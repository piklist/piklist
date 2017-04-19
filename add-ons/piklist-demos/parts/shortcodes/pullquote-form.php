<?php
/*
Name: Pullquote
Description: Embed a quote in your content
Shortcode: pullquote
Icon: dashicons-editor-quote
*/

  piklist('field', array(
    'type' => 'textarea'
    ,'field' => 'quote'
    ,'label' => __('Quote')
    ,'attributes' => array(
      'class' => 'large-text'
      ,'rows' => 5
    )
    ,'required' => true
  ));

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'source'
    ,'label' => __('Source')
    ,'attributes' => array(
      'class' => 'large-text'
    )
  ));