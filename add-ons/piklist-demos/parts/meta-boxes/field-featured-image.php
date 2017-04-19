<?php
/*
Title: Featured Image(s)
Post Type: piklist_demo
Order: 40
Priority: default
Context: side
Collapse: true
Tab: All
Flow: Demo Workflow
*/
?>

<p class="piklist-demo-highlight">
  <?php _e('With Piklist you can easily replicate the WordPress Featured Image field, with an added bonus. Piklist allows you to use multiple featured images.', 'piklist-demo');?>
</p>

<?php
  
  piklist('field', array(
    'type' => 'file'
    ,'field' => '_thumbnail_id' // Use this field to match WordPress featured image field name.
    ,'scope' => 'post_meta'
    ,'options' => array(
      'title' => __('Set featured image(s)', 'piklist-demo')
      ,'button' => __('Set featured image(s)', 'piklist-demo')
    )
  ));
  
  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));