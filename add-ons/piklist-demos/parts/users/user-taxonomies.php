<?php
/*
Title: Taxonomies
Capability: manage_options
Order: 30
Tab: Default
Flow: Demo Workflow
*/


  piklist('field', array(
    'type' => 'checkbox'
    ,'scope' => 'taxonomy'
    ,'field' => 'piklist_demo_user_type'
    ,'label' => __('Demo Types', 'piklist-demo')
    ,'description' => __('Terms will appear when they are added to this taxonomy.', 'piklist-demo')
    ,'choices' => piklist(
      get_terms('piklist_demo_user_type', array(
        'hide_empty' => false
      ))
      ,array(
        'term_id'
        ,'name'
      )
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'User Section'
  ));