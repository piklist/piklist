<?php
/*
Title: User Register
Method: post
Message: User Profile Saved.
*/


/**
 * Piklist forms automatically generate a shortcode:
 *
 * If your form is in a PLUGIN (i.e. wp-content/plugins/my-plugin/parts/forms/my-form.php)
 * Use [piklist_form form="my-form" add_on="my-plugin"]
 *
 * If your form is in a THEME (i.e. wp-content/themes/my-theme/piklist/parts/forms/my-form.php)
 * Use [piklist_form form="my-form" add_on="theme"]
 *
 * The "form" parameter is the file name of your form without ".php".
 *
 */

/**
 * The shortcode for this form is:
 * [piklist_form form="user-register" add_on="piklist-demos"]
 */

?>

<h1><?php _e('Register for this site.', 'piklist-demo'); ?></h1>

<?php

  piklist('field', array(
    'type' => 'text'
    ,'scope' => 'user' // user_login is in the wp_users table, so scope is: user
    ,'field' => 'user_login'
    ,'label' => __('Username', 'piklist-demo')
    ,'required' => true
    ,'attributes' => array(
      'autocomplete' => 'off'
      ,'wrapper_class' => 'user_login'
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'scope' => 'user'// scope needs to be set on EVERY field for front-end forms.
    ,'field' => 'user_email'
    ,'label' => __('Email', 'piklist-demo')
    ,'required' => true
    ,'validate' => array(
      array(
        'type' => 'email_exists'
      )
      ,array(
        'type' => 'email'
      )
      ,array(
        'type' => 'email_domain'
      )
    )
    ,'attributes' => array(
      'wrapper_class' => 'user_email'
    )
  ));

  piklist('field', array(
    'type' => 'password'
    ,'scope' => 'user'
    ,'field' => 'user_pass'
    ,'label' => __('New Password', 'piklist-demo')
    ,'required' => true
    ,'value' => false // Setting to false forces no value to show in form.
    ,'attributes' => array(
      'autocomplete' => 'off'
      ,'wrapper_class' => 'user_pass'
    )
  ));

  piklist('field', array(
    'type' => 'password'
    ,'scope' => 'user'
    ,'field' => 'user_pass_repeat'
    ,'label' => __('Repeat New Password', 'piklist-demo')
    ,'required' => true
    ,'value' => false // Setting to false forces no value to show in form.
    ,'validate' => array(
      array(
        'type' => 'match'
        ,'options' => array(
          'field' => 'user_pass'
        )
      )
    )
    ,'attributes' => array(
      'wrapper_class' => 'user_pass_repeat'
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'scope' => 'user_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'field' => 'first_name'
    ,'label' => __('First name', 'piklist-demo')
    ,'attributes' => array(
      'wrapper_class' => 'first_name'
    )
  ));

  piklist('field', array(
    'type' => 'text'
    ,'scope' => 'user_meta' // scope needs to be set on EVERY field for front-end forms.
    ,'field' => 'last_name'
    ,'label' => __('Last name', 'piklist-demo')
    ,'attributes' => array(
      'wrapper_class' => 'last_name'
    )
  ));


  // Submit button
  piklist('field', array(
    'type' => 'submit'
    ,'field' => 'submit'
    ,'value' => 'Submit'
  ));
