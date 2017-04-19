<?php
/*
Notice Type: warning
Capability: manage_options
Flow: All
Tab: All
*/
?>

	<p>
		<strong><?php _e('Piklist Demos are activated.', 'piklist'); ?></strong>
	</p>

	<p>
		<?php printf(__('Demos should only be used as a resource, and %1$sturned off%2$s during normal website operation.', 'piklist'), '<a href="' . network_admin_url() . '/admin.php?page=piklist-core-addons">','</a>');?>
	</p>
