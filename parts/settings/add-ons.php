<?php
/*
Title: Extend Piklist
Setting: piklist_core_addons
Tab Order: 0
*/
?>

  <p>
    <?php printf(__('%1$sAdd-ons are Piklist plugins%2$s that are included with Piklist core, another Piklist plugin or your theme. They allow you to turn on additional functionality.', 'piklist'), '<a href="https://docs.piklist.com/getting-started/piklist-add-ons/">', '</a>');?>
  </p>

<?php

  piklist('field', array(
    'type' => 'add-ons'
    ,'field' => 'add-ons'
    ,'template' => 'field'
    ,'label' => __('Plugin Add-ons', 'piklist')
    ,'choices' => piklist(piklist_add_on::$available_add_ons, array('add_on', 'name'))
  ));
