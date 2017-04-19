<?php
/*
Title: Add More Fields: Two Levels
Setting: piklist_demo_fields
Order: 1
Tab: Add more's
Sub Tab: Two Levels
Flow: Demo Workflow
*/

  piklist('field', array(
    'type' => 'group'
    ,'field' => 'ingredient_section'
    ,'label' => __('Ingredients', 'piklist-demo')
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'ingredients_component_title'
        ,'label' => __('Ingredient Title', 'piklist-demo')
        ,'columns' => 12
      )
      ,array(
        'type' => 'checkbox'
        ,'field' => 'ingredient_type'
        ,'columns' => 12
        ,'list' => false
        ,'label' => __('Meal Type', 'piklist-demo')
        ,'choices' => array(
          'appetizer' => __('Appetizer', 'piklist-demo')
          ,'entree' => __('Entree', 'piklist-demo')
          ,'main_course' => __('Main Course', 'piklist-demo')
        )
      )
      ,array(
        'type' => 'group'
        ,'field' => 'ingredient'
        ,'add_more' => true
        ,'fields' => array(
          array(
            'type' => 'text'
            ,'field' => 'ingredient_qty'
            ,'label' => __('Qty', 'piklist-demo')
            ,'columns' => 2
          )
          ,array(
            'type' => 'textarea'
            ,'field' => 'ingredient_description'
            ,'label' => __('Description', 'piklist-demo')
            ,'columns' => 10
            ,'attributes' => array(
              'rows' => 5
            )
          )
        )
      )
    )
  ));
  
  piklist('field', array(
    'type' => 'group'
    ,'field' => 'module_group'
    ,'label' => __('Page Modules', 'piklist-demo')
    ,'description' => __('Add more \'s within a hide/show condition', 'piklist-demo')
    ,'value' => 'none'
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'select'
        ,'field' => 'module_select'
        ,'label' => __('Select a Module', 'piklist-demo')
        ,'columns' => 12
        ,'choices' => array(
          'none' => __('Select a Module to add', 'piklist-demo')
          ,'module' => __('Editor', 'piklist-demo')
          ,'repeating_module' => __('Repeating Textarea', 'piklist-demo')
        )
      )
      ,array(
        'type' => 'editor'
        ,'field' => 'module_editor'
        ,'columns' => 12
        ,'options' => array(
          'wpautop' => true
          ,'media_buttons' => false
          ,'tabindex' => ''
          ,'editor_css' => ''
          ,'editor_class' => true
          ,'teeny' => false
          ,'dfw' => false
          ,'tinymce' => true
          ,'quicktags' => true
        )
        ,'conditions' => array(
          array(
            'field' => 'module_group:module_select'
            ,'value' => 'module'
          )
        )
      )
      ,array(
        'type' => 'textarea'
        ,'field' => 'module_title'
        ,'label' => __('Module title:', 'piklist-demo')
        ,'columns' => 12
        ,'add_more' => true
        ,'attributes' => array(
          'class' => 'large-text'
          , 'rows' => 2
        )
        ,'conditions' => array(
          array(
            'field' => 'module_group:module_select'
            ,'value' => 'repeating_module'
          )
        )
      )
      ,array(
        'type' => 'textarea'
        ,'field' => 'module_text'
        ,'label' => __('Module text:', 'piklist-demo')
        ,'columns' => 12
        ,'add_more' => true
        ,'attributes' => array(
          'class' => 'large-text'
          , 'rows' => 3
        )
        ,'conditions' => array(
          array(
            'field' => 'module_group:module_select'
            ,'value' => 'repeating_module'
          )
        )
      )
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'field' => 'guide_section'
    ,'label' => __('Upload Repeater', 'piklist-demo')
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'file'
        ,'field' => 'image'
        ,'label'=> __('Image', 'piklist-demo')
        ,'columns' => 12
      )
      ,array(
        'type' => 'textarea'
        ,'field' => 'description'
        ,'label' => __('Information Section', 'piklist-demo')
        ,'add_more' => true
        ,'columns' => 12
      )
    )
  ));
  
  piklist('field', array(
    'type' => 'group'
    ,'label' => __('Content Section (Grouped)', 'piklist-demo')
    ,'description' => __('When an add more field is nested it should be grouped to maintain the data relationships.', 'piklist-demo')
    ,'field' => 'demo_content'
    ,'add_more' => true
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'title'
        ,'label' => __('Section Title', 'piklist-demo')
        ,'columns' => 12
        ,'attributes' => array(
          'class' => 'large-text'
        )
      )
      ,array(
        'type' => 'text'
        ,'field' => 'tagline'
        ,'label' => __('Section Tagline', 'piklist-demo')
        ,'columns' => 12
        ,'attributes' => array(
          'class' => 'large-text'
        )
      )
      ,array(
        'type' => 'group'
        ,'field' => 'content'
        ,'add_more' => true
        ,'fields' => array(
          array(
            'type' => 'select'
            ,'field' => 'post_id'
            ,'label' => __('Content Title', 'piklist-demo')
            ,'columns' => 12
            ,'choices' => piklist(
              get_posts(
                 array(
                  'post_type' => 'post'
                  ,'orderby' => 'post_date'
                 )
                 ,'objects'
               )
               ,array(
                 'ID'
                 ,'post_title'
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
    ,'type' => 'Settings Section'
  ));