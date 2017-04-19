<?php
/*
Notice Type: info
Capability: manage_options
Dismiss: true
Page: piklist_demo
Flow: All
Tab: All
*/
?>

  <p>
    <?php _e('Piklist makes it super simple to add admin notices that are dismissable.', 'piklist'); ?>
  </p>

<?php

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Admin notice'
  ));
