<?php
/*  
Title: Post Submit
Method: post
Message: Data saved in Piklist Demos, under the Validation tab.
Logged in: true
*/

/**
 * Piklist forms automatically generate a shortcode:
 *
 * If your form is in a PLUGIN (i.e. wp-content/plugins/my-plugin/parts/forms/my-form.php)
 * 
 * Use [piklist_form form="my-form" add_on="my-plugin"]
 *
 * If your form is in a THEME (i.e. wp-content/themes/my-theme/piklist/parts/forms/my-form.php)
 * Use [piklist_form form="my-form" add_on="theme"]
 */

/** 
 * The shortcode for this form is:
 * [piklist_form form="new-post-with-validation" add_on="piklist-demos"]
 */

/**
 * The fields in this form are exactly like the fields in piklist-demos/parts/meta-boxes/field-validate.php
 * Only the 'scope' paramater needed to be added.
 */

  // Where to save this form
  piklist('field', array(
    'type' => 'hidden'
    ,'scope' => 'post'
    ,'field' => 'post_type'
    ,'value' => 'piklist_demo'
  ));


  piklist('field', array(
    'type' => 'text'
    ,'scope' => 'post' // post_title is in the wp_posts table, so scope is: post
    ,'field' => 'post_title'
    ,'label' => __('Title', 'piklist-demo')
    ,'attributes' => array(
      'wrapper_class' => 'post_title'
      ,'style' => 'width: 100%'
    )
  ));

  // Allows user to choose their own post status.
  $statuses = piklist_cpt::get_post_statuses_for_type('piklist_demo', false);

  piklist('field', array(
    'type' => 'select'
    ,'scope' => 'post'
    ,'field' => 'post_status'
    ,'label' => __('Post Status', 'piklist-demo')
    ,'choices' => $statuses
    ,'attributes' => array(
      'wrapper_class' => 'post_status'
    )
  ));

  /**
   * To automatically set the post status:
   *** Remove the field above since it's letting the user choose their status
   *** Uncomment this field
   *** Set your default post status by changing the "value" parameter.
   */
  // piklist('field', array(
  //   'type' => 'hidden'
  //   ,'scope' => 'post'
  //   ,'field' => 'post_status'
  //   ,'value' => 'pending'
  // ));
  // 


  piklist('field', array(
    'type' => 'checkbox'
    ,'scope' => 'taxonomy'
    ,'field' => 'piklist_demo_type'
    ,'label' => __('Demo Types', 'piklist-demo')
    ,'description' => sprintf(__('Terms will appear when they are added to %1$s the Demo taxonomy %2$s.','piklist-demo'), '<a href="' . network_admin_url() . 'edit-tags.php?taxonomy=piklist_demo_type&post_type=piklist_demo">', '</a>')
    ,'choices' => piklist(
      get_terms('piklist_demo_type', array(
        'hide_empty' => false
      ))
      ,array(
        'term_id'
        ,'name'
      )
    )
  ));

  
  piklist('field', array(
    'type' => 'text'
    ,'scope' => 'post_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'field' => 'validate_text_required'
    ,'label' => __('Text Required', 'piklist-demo')
    ,'description' => "required => true"
    ,'attributes' => array(
      'wrapper_class' => 'validate_text_required'
      ,'placeholder' => __('Enter text or this page won\'t save.', 'piklist-demo')
    )
    ,'required' => true
    ,'validate' => array(
      array(
        'type' => 'limit'
        ,'options' => array(
          'min' => 2
          ,'max' => 6
          ,'count' => 'characters'
        )
      )
    )
  ));

  piklist('field', array(
    'type'    => 'group'
    ,'scope' => 'post_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'field'   => 'validate_group_required'
    ,'label'   => __('Group Required', 'piklist-demo')
    ,'description' =>__('Only the checkbox is required', 'piklist-demo')
    ,'attributes' => array(
      'wrapper_class' => 'validate_group_required'
    )
    ,'add_more'=> true
    ,'fields'  => array(
      array(
        'type' => 'text'
        ,'field' => 'name'
        ,'label' => 'Name'
        ,'columns' => 8
        ,'attributes' => array(
          'wrapper_class' => 'validate_group_required-name'
          ,'placeholder' => 'Name'
        )
      )
      ,array(
        'type' => 'checkbox'
        ,'field' => 'hierarchical'
        ,'label' => 'Type'
        ,'required' => true
        ,'columns' => 4
        ,'choices' => array(
          'true' => 'Hierarchical'
        )
        ,'attributes' => array(
          'wrapper_class' => 'validate_group_required-hierarchical'
        )
      )
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'scope' => 'post_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'label' => __('File Name', 'piklist-demo')
    ,'field' => 'sanitize_file_name'
    ,'description' => __('Converts multiple words to a valid file name', 'piklist-demo')
    ,'sanitize' => array(
      array(
        'type' => 'file_name'
      )
    )
    ,'attributes' => array(
      'wrapper_class' => 'sanitize_file_name'
      ,'class' => 'large-text'
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'scope' => 'post_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'field' => 'validate_emaildomain'
    ,'label' => __('Email address', 'piklist-demo')
    ,'description' => __('Validate Email and Email Domain', 'piklist-demo')
    ,'attributes' => array(
      'wrapper_class' => 'validate_emaildomain'
      ,'class' => 'large-text'
    )
    ,'validate' => array(
      array(
        'type' => 'email'
      )
      ,array(
        'type' => 'email_domain'
      )
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'scope' => 'post_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'field' => 'validate_file_exists'
    ,'label' => __('File exists?', 'piklist-demo')
    ,'description' => sprintf(__('Test with: %s', 'piklist-demo'), 'http://wordpress.org/plugins/about/readme.txt')
    ,'attributes' => array(
      'wrapper_class' => 'validate_file_exists'
      ,'class' => 'large-text'
    )
    ,'validate' => array(
      array(
        'type' => 'file_exists'
      )
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'scope' => 'post_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'field' => 'validate_image'
    ,'label' => __('Image')
    ,'description' => sprintf(__('Test with: %s', 'piklist-demo'), 'https://piklist.com/wp-content/themes/piklistcom-base/images/piklist-logo@2x.png')
    ,'attributes' => array(
      'wrapper_class' => 'validate_image'
      ,'class' => 'large-text'
    )
    ,'validate' => array(
      array(
        'type' => 'image'
      )
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'scope' => 'post_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'field' => 'validate_checkbox_limit'
    ,'label' => __('Checkbox', 'piklist-demo')
    ,'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
    ,'value' => 'third'
    ,'choices' => array(
      'first' => __('First Choice', 'piklist-demo')
      ,'second' => __('Second Choice', 'piklist-demo')
      ,'third' => __('Third Choice', 'piklist-demo')
    )
    ,'required' => true
    ,'validate' => array(
      array(
        'type' => 'limit'
        ,'options' => array(
          'min' => 2
          ,'max' => 2
        )
      )
    )
    ,'attributes' => array(
      'wrapper_class' => 'validate_checkbox_limit'
    )
  ));

  piklist('field', array(
    'type' => 'file'
    ,'scope' => 'post_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'field' => 'validate_upload_media_limit'
    ,'label' => __('Add File(s)', 'piklist-demo')
    ,'description' => __('No more than one file is allowed', 'piklist-demo')
    ,'required' => true
    ,'options' => array(
      'modal_title' => __('Add File(s)', 'piklist-demo')
      ,'button' => __('Add', 'piklist-demo')
    )
    ,'attributes' => array(
      'wrapper_class' => 'validate_upload_media_limit'
      ,'class' => 'large-text'
    )
    ,'validate' => array(
      array(
        'type' => 'limit'
        ,'options' => array(
          'min' => 0
          ,'max' => 1
        )
      )
    )
  ));

  piklist('field', array(
    'type' => 'group'
    ,'scope' => 'post_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'field' => 'validate_group_add_more_limit'
    ,'add_more' => true
    ,'label' => __('Grouped/Add more with Limit', 'piklist-demo')
    ,'description' => __('No more than two add mores are allowed', 'piklist-demo')
    ,'attributes' => array(
      'wrapper_class' => 'validate_group_add_more_limit'
    )
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'group_field_1'
        ,'label' => __('Field 1', 'piklist-demo')
        ,'columns' => 12
        ,'attributes' => array(
          'class' => 'validate_group_add_more_limit-group_field_1'
        )
      )
      ,array(
        'type' => 'text'
        ,'field' => 'group_field_2'
        ,'label' => __('Field 2', 'piklist-demo')
        ,'columns' => 12
        ,'attributes' => array(
          'class' => 'validate_group_add_more_limit-group_field_2'
        )
      )
    )
    ,'validate' => array(
      array(
        'type' => 'limit'
        ,'options' => array(
          'min' => 1
          ,'max' => 2
        )
      )
    )
  ));

  // Submit button
  piklist('field', array(
    'type' => 'submit'
    ,'field' => 'submit'
    ,'value' => 'Submit'
  ));