<?php
/*
Title: Post Relationships
Post Type: piklist_demo
Flow: Demo Workflow
Tab: Relate
Sub Tab: Basic
*/

  piklist('field', array(
    'type' => 'checkbox'
    ,'label' => __('Relate Posts', 'piklist-demo')
    ,'choices' => piklist(
      get_posts(array(
        'post_type' => 'post'
        ,'numberposts' => -1
        ,'orderby' => 'title'
        ,'order' => 'ASC'
      ))
      ,array('ID', 'post_title')
    )
    ,'relate' => array(
      'scope' => 'post'
    )
  ));

  // Displaying your related posts is as simple as using WP_Query with one extra parameter, post_belongs
  $related = get_posts(array(
    'post_type' => 'post'
    ,'posts_per_page' => -1
    ,'post_belongs' => $post->ID
    ,'post_status' => 'publish'
    ,'suppress_filters' => false
  ));

?>

  <?php if ($related): ?>

    <h4><?php _e('Related Posts', 'piklist-demo');?></h4>
    
    <p>
      <?php _e('See the code in the file for the query example.'); ?>
    </p>
    
    <ol>
      <?php foreach ($related as $related_post): ?>
        <li><?php _e($related_post->post_title); ?></li>
      <?php endforeach; ?>
    </ol>

  <?php endif;
  

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));