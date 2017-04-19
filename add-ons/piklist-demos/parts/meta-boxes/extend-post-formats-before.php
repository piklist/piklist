<?php
/*
Title: Post Formats... extended by Piklist
Post Type: piklist_demo
Flow: Demo Workflow
Tab: Extend
Meta Box: true
Extend: formatdiv
Extend Method: before
*/
?>

<div class="piklist-demo-highlight">

  <p>
    <?php _e('This is the default WordPress Post Formats meta box, extended by Piklist, changing three things: ', 'piklist-demo');?>
  </p>

  <ol>
    <li><?php _e('The title of the meta-box was updated.', 'piklist-demo');?></li>
    <li><?php _e('Location was moved from the sidebar to under this Workflow tab.', 'piklist-demo');?></li>
    <li><?php _e('What you are reading right now was added to the beginning of the meta box.', 'piklist-demo');?></li>
  </ol>

</div>