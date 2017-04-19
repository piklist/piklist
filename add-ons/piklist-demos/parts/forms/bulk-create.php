<?php
/*  
Title: Bulk Create
Method: post
Message: Demo's have been created
Logged in: true
*/
  
  piklist('field', array(
    'type' => 'group'
    ,'scope' => 'post'
    ,'label' => 'Demo Posts'
    ,'relate' => array(
      'scope' => 'post'
    )
    ,'add_more'=> true
    ,'sortable'=> false
    ,'position' => 'wrap'
    ,'template' => 'form_table'
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'post_title'
        ,'label' => 'Post Title'
        ,'columns' => 12
      )
      ,array(
        'type' => 'textarea'
        ,'field' => 'post_content'
        ,'label' => 'Post Content'
        ,'columns' => 12
        ,'attributes' => array(
          'rows' => 5
        )
      )
      ,array(
        'type' => 'text'
        ,'scope' => 'post_meta'
        ,'field' => 'text'
        ,'label' => 'Meta Field - Text'
        ,'columns' => 6
      )
      ,array(
        'type' => 'checkbox'
        ,'scope' => 'taxonomy'
        ,'field' => 'piklist_demo_type'
        ,'label' => 'Demo Types'
        ,'columns' => 6
        ,'choices' => piklist(
          get_terms('piklist_demo_type', array(
            'hide_empty' => false
          ))
          ,array(
            'term_id'
            ,'name'
          )
        )
      )
      ,array(
        'type' => 'hidden'
        ,'field' => 'post_type'
        ,'value' => 'piklist_demo'
      )
    )
  ));

  piklist('field', array(
    'type' => 'submit'
    ,'field' => 'submit'
    ,'value' => 'Create Demos'
    ,'template' => 'submit'
    ,'attributes' => array(
      'class' => 'button button-primary'
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Form'
  ));