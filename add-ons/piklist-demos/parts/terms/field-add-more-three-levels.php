<?php
/*
Title: Add More Fields: Three Levels
Order: 1
Taxonomy: piklist_demo_type
Tab: Add more's
Sub Tab: Three Levels
Flow: Demo Workflow
*/

piklist('field', array(
    'type' => 'group'
    ,'field' => 'menu_section'
    ,'label' => __('Menu', 'piklist-demo')
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'menu_title'
        ,'label' => __('Menu Title', 'piklist-demo')
        ,'columns' => 12
        ,'required' => true
      )
      ,array(
        'type' => 'group'
        ,'field' => 'recipe'
        ,'add_more' => true
        ,'fields' => array(
          array(
            'type' => 'text'
            ,'field' => 'recipe_name'
            ,'label' => __('Recipe', 'piklist-demo')
            ,'columns' => 12
          )
          ,array(
            'type' => 'checkbox'
            ,'field' => 'dietary_restrictions'
            ,'label' => __('Restrictions', 'piklist-demo')
            ,'columns' => 12
            ,'list' => false // Make checkboxes appear horizontally
            ,'choices' => array(
              'dairy_free' => 'Dairy Free'
              ,'gluten_free' => 'Gluten Free'
              ,'nut_free' => 'Nut Free'
            )
          )
          ,array(
            'type' => 'group'
            ,'field' => 'ingredient'
            ,'add_more' => true
            ,'fields' => array(
              array(
                'type' => 'number'
                ,'field' => 'ingredient_qty'
                ,'label' => __('Qty', 'piklist-demo')
                ,'columns' => 2
              )
              ,array(
                'type' => 'text'
                ,'field' => 'name'
                ,'label' => __('Description', 'piklist-demo')
                ,'columns' => 10
                ,'attributes' => array(
                  'rows' => 5
                )
              )
            )
          )
        )
      )
    )
  ));


piklist('field', array(
    'type' => 'group'
    ,'field' => 'project_section'
    ,'label' => __('Project', 'piklist-demo')
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'Project_title'
        ,'label' => __('Project', 'piklist-demo')
        ,'columns' => 12
      )
      ,array(
        'type' => 'group'
        ,'field' => 'task'
        ,'add_more' => true
        ,'fields' => array(
          array(
            'type' => 'text'
            ,'field' => 'task_name'
            ,'label' => __('Task', 'piklist-demo')
            ,'columns' => 8
          )
          ,array(
            'type' => 'datepicker'
            ,'field' => 'task_due'
            ,'label' => __('Due', 'piklist-demo')
            ,'columns' => 4
          )
          ,array(
            'type' => 'textarea'
            ,'field' => 'task_description'
            ,'label' => __('Description', 'piklist-demo')
            ,'columns' => 12
          )
          ,array(
            'type' => 'checkbox'
            ,'field' => 'authorized_roles'
            ,'label' => __('Authorized Roles', 'piklist-demo')
            ,'list' => false // Make checkboxes appear horizontally
            ,'columns' => 12
            ,'choices' => piklist_user::available_roles() // Piklist helper function to get all roles.
          )
          ,array(
            'type' => 'group'
            ,'field' => 'sub_task'
            ,'label' => 'Sub Tasks'
            ,'add_more' => true
            ,'fields' => array(
              array(
                'type' => 'text'
                ,'field' => 'sub_task_name'
                ,'label' => __('Sub Task', 'piklist-demo')
                ,'columns' => 8
              )
              ,array(
                'type' => 'datepicker'
                ,'field' => 'sub_task_due'
                ,'label' => __('Due', 'piklist-demo')
                ,'columns' => 4
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
    ,'type' => 'Term Section'
  ));