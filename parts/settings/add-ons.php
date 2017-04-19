<?php
/*
Title: Extend Piklist
Setting: piklist_core_addons
Tab Order: 0
*/
?>

  <p>
    <?php _e('Add-ons are Piklist plugins that are included with Piklist core, or another Piklist plugin. They allow you to turn on additional functionality.', 'piklist'); ?>
  </p>
  
<?php

  piklist('field', array(
    'type' => 'add-ons'
    ,'field' => 'add-ons'
    ,'template' => 'field'
    ,'label' => __('Plugin Add-ons', 'piklist')
    ,'choices' => piklist(piklist_add_on::$available_add_ons, array('add_on', 'name'))
  ));