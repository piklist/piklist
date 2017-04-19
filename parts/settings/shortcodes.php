<?php
/*
Title: Shortcodes
Setting: piklist_core
Order: 30
Flow: Piklist Core Settings
Tab: General
*/
  
  piklist('field', array(
    'type' => 'group'
    ,'field' => 'shortcode_ui'
    ,'label' => __('Allow Shortcode UI', 'piklist')
    ,'add_more' => true
    ,'sortable' => false
    ,'fields' => array(
      array(
        'type' => 'select'
        ,'label' => 'Shortcode'
        ,'field' => 'tag'
        ,'columns' => 4
        ,'choices' => array_merge(array('' => '&mdash; Select &mdash;'), piklist_shortcode::get_shortcodes())
        ,'value' => 'piklist_form'
      )
      ,array(
        'type' => 'checkbox'
        ,'label' => 'Options'
        ,'field' => 'options'
        ,'columns' => 8
        ,'choices' => array(
          'preview' => 'Preview'
        )
        ,'value' => 'preview'
      )
    )
  ));