<?php
/*
Title: Piklist Demos
Capability: manage_options
Page: piklist_demo,piklist_demo_page_piklist_demo_fields,profile.php
Flow: All
Tab: All
*/
?>

<p>
  <?php _e('Piklist Demos are designed to show off Piklist features and demonstrate how to use them.', 'piklist-demo');?>
</p>

<?php
  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Help tab'
  ));