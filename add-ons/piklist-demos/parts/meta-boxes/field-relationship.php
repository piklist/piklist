<?php
/*
Title: Post Relationships
Post Type: piklist_demo
Flow: Demo Workflow
Tab: Relate
Sub Tab: Advanced
*/
?>

<div class="piklist-demo-highlight">

  <?php _e('This form will allow you to create brand new Post, User and Comment, that are all related to this Demo Post.', 'piklist-demo');?>
  
</div>

<?php
  
  piklist('field', array(
    'type' => 'group'
    ,'scope' => 'post'
    ,'label' => 'Related Posts'
    ,'relate' => array(
      'scope' => 'post'
    )
    ,'add_more'=> true
    ,'sortable'=> false
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'field' => 'post_title'
        ,'label' => 'Post Title'
        ,'columns' => 12
      )
      ,array(
        'type' => 'textarea'
        ,'field' => 'post_content'
        ,'label' => 'Post Content'
        ,'columns' => 12
        ,'attributes' => array(
          'rows' => 5
        )
      )
      ,array(
        'type' => 'hidden'
        ,'field' => 'post_status'
        ,'value' => 'publish'
      )
    )
  ));
  
  // Display related posts
  $related = get_posts(array(
    'post_type' => 'post'
    ,'posts_per_page' => -1
    ,'post_belongs' => $post->ID
    ,'post_status' => 'publish'
    ,'suppress_filters' => false
  ));

  if ($related): 
?>

    <h4><?php _e('Related Posts', 'piklist-demo');?></h4>

    <ol>
      <?php foreach ($related as $related_post): ?>
        <li><?php _e($related_post->post_title); ?></li>
      <?php endforeach; ?>
   </ol>

   <hr />

<?php 
  endif;
  
  piklist('field', array(
    'type' => 'group'
    ,'scope' => 'user'
    ,'label' => 'User'
    ,'relate' => array(
      'scope' => 'post'
    )
    ,'fields' => array(
      array(
        'type' => 'text'
        ,'label' => 'Login'
        ,'field' => 'user_login'
        ,'columns' => 6
      )
      ,array(
        'type' => 'text'
        ,'label' => 'Email'
        ,'field' => 'user_email'
        ,'columns' => 6
      )
      ,array(
        'type' => 'text'
        ,'label' => 'Password'
        ,'field' => 'user_pass'
        ,'columns' => 12
      )
    )
  ));

  // Display related users
  $related = get_users(array(
    'order' => 'DESC'
    ,'user_belongs' => $post->ID
    ,'user_relate' => 'post'
  ));

  if ($related): 
?>

    <h4><?php _e('Related Users', 'piklist-demo');?></h4>

    <ol>
      <?php foreach ($related as $related_user): ?>
        <li><?php _e($related_user->user_login); ?></li>
      <?php endforeach; ?>
   </ol>

    <hr />

<?php 
  endif;
  
  // Display related users
  $related = get_comments(array(
    'order' => 'DESC'
    ,'comment_belongs' => $post->ID
    ,'comment_relate' => 'post'
  ));

  if ($related): 
?>

    <h4><?php _e('Related Comments', 'piklist-demo');?></h4>

    <?php foreach ($related as $related_comment): ?>
      
      <?php echo wpautop($related_comment->comment_content); ?>
      
      <p>
        <small><?php echo $related_comment->comment_date; ?></small>
      </p>
    
    <?php endforeach; ?>

<?php 
  endif;
  
  piklist('field', array(
    'type' => 'group'
    ,'scope' => 'comment'
    ,'label' => 'Notes'
    ,'new' => true
    ,'relate' => array(
      'scope' => 'post'
    )
    ,'fields' => array(
      array(
        'type' => 'hidden'
        ,'field' => 'comment_type'
        ,'value' => 'note'
      )
      ,array(
        'type' => 'textarea'
        ,'field' => 'comment_content'
        ,'columns' => 12
        ,'attributes' => array(
          'rows' => 10
        )
      )
    )
  ));
  
  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Meta Box'
  ));