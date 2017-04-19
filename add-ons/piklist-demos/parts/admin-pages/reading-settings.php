<?php
/*
Page: piklist_demo_options
*/
?>

<div class="piklist-demo-highlight">

  <h3><?php _e('This settings page is a reproduction of the WordPress', 'piklist-demo'); ?> <em><?php _e('Settings'); ?> &raquo; <?php _e('Reading'); ?></em> <?php _e('Page'); ?></h3>

  <p>
    <strong><?php _e('IMPORTANT', 'piklist-demo'); ?></strong> <?php _e('Changes you make on this page, will effect your site as if you are making changes to the original Reading Settings page.', 'piklist-demo'); ?>
  </p>

  <p>
    <?php _e('Using Piklist you can recreate almost any form with ease. In this case we have recreated the Reading Settings page and even made it a little more responsive!', 'piklist-demo'); ?>
  </p>
  
</div>

<?php

  // Embed a Piklist form
  // From: piklist/add-ons/piklist-demos/parts/forms/reading-settings.php
  piklist('form', array(
    'form' => 'reading-settings'
  ));