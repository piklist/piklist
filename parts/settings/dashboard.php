<?php
/*
Title: Dashboard
Setting: piklist_core
Order: 35
Flow: Piklist Core Settings
Tab: General
*/

  piklist('field', array(
    'type' => 'checkbox'
    ,'field' => 'dashboard_at_a_glance'
    ,'label' => __('"At A Glance" Widget', 'piklist')
    ,'choices' => array(
      'true' => __('Use Piklist version', 'piklist')
    )
  ));