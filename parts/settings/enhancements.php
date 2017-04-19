<?php
/*
Title: Enhancements
Setting: piklist_core
Order: 40
Flow: Piklist Core Settings
Tab: General
*/

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'meta_queries'
    ,'label' => __('Accelerate meta queries', 'piklist')
    ,'description' => __('May conflict with certain plugins', 'piklist')
    ,'help' => __('Allow Piklist to speed up all meta queries in WordPress or any plugin.', 'piklist')
    ,'choices' => array(
      'true' => __('Allow', 'piklist')
    )
  ));