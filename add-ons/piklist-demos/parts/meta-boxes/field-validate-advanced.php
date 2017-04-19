<?php
/*
Title: Validation Fields
Post Type: piklist_demo
Tab: Validation
Sub Tab: Advanced
Flow: Demo Workflow
*/

  piklist('field', array(
    'type' => 'group'
    ,'field' => 'file_section'
    ,'label' => __('File', 'piklist-demo')
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'file_1'
        ,'label' => __('File 1', 'piklist-demo')
        ,'columns' => 12
        ,'sanitize' => array(
          array(
            'type' => 'file_name'
          )
        )
      )
      ,array(
        'type' => 'group'
        ,'field' => 'file_section_2'
        ,'add_more' => true
        ,'fields' => array(
          array(
            'type' => 'text'
            ,'field' => 'file_2'
            ,'label' => __('File 2', 'piklist-demo')
            ,'columns' => 12
            ,'sanitize' => array(
              array(
                'type' => 'file_name'
              )
            )
          )
          ,array(
            'type' => 'group'
            ,'field' => 'file_section_3'
            ,'add_more' => true
            ,'fields' => array(
                array(
                'type' => 'text'
                ,'field' => 'file_2'
                ,'label' => __('File 3', 'piklist-demo')
                ,'columns' => 12
                ,'attributes' => array(
                  'rows' => 5
                )
                ,'sanitize' => array(
                  array(
                    'type' => 'file_name'
                  )
                )
              )
            )
          )
        )
      )
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));