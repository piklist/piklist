<?php
/*
Capability: manage_options
Sidebar: true
Page: piklist_demo,piklist_demo_page_piklist_demo_fields,profile.php
Flow: All
Tab: All
*/

?>

<p>
  
  <h4><?php _e('Help with Piklist is just a click away.', 'piklist-demo')?></h4>

  <ol>
    <li><a href="https://piklist.com/user-guide/tutorials?#utm_source=wpadmin&utm_medium=piklistdemos&utm_campaign=piklistplugin" target="_blank"><?php _e('Tutorials', 'piklist-demo'); ?></a></li>
    <li><a href="https://piklist.com/user-guide/docs?#utm_source=wpadmin&utm_medium=piklistdemos&utm_campaign=piklistplugin" target="_blank"><?php _e('Documentation', 'piklist-demo'); ?></a></li>
    <li><a href="https://piklist.com/support?#utm_source=wpadmin&utm_medium=piklistdemos&utm_campaign=piklistplugin" target="_blank"><?php _e('Support Forum', 'piklist-demo'); ?></a></li>
    <li><a href="<?php echo network_admin_url(); ?>admin.php?page=piklist-core-settings&tab=add-ons"><?php _e('Disable Piklist Demos', 'piklist-demo'); ?></a></li>
  </ol>

</p>