<?php
/*
Title: Forms
Setting: piklist_core
Order: 80
Flow: Piklist Core Settings
Tab: General
*/

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'form_validate_js'
    ,'label' => __('Javascript Validation', 'piklist')
    ,'description' => __('Allow forms to use client side validation using the same rules and methods as the built in server side validation.', 'piklist')
    ,'choices' => array(
      'true' => __('Allow', 'piklist')
    )
  ));