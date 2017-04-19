<?php
/*
Name: Variable
Description: Embed the value of a global variable
Shortcode: variable
Icon: dashicons-nametag
Inline: true
*/

  piklist('field', array(
    'type' => 'radio'
    ,'field' => 'user'
    ,'label' => __('Current User')
    ,'list' => false
    ,'columns' => 4
    ,'choices' => array(
      'ID' => 'ID'
      ,'display_name' => 'Display Name'
      ,'user_login' => 'Login'
      ,'user_email' => 'Email Address'
      ,'user_url' => 'Url'
    )
  ));