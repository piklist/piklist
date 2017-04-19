<?php
/*
Title: Support
Post Type: post
Context: side
Priority: low
Order: 5
*/
  
  piklist('field', array(
    'type'      => 'select',
    'template'  => 'field',
    'choices'   => piklist(get_posts(array(
      'post_type'   => 'page',
      'numberposts' => -1,
      'orderby'     => 'title',
      'order'       => 'ASC'
    )), array('ID', 'post_title')),
    'relate'    => array(
      'scope'     => 'post'
    ),
    'attributes'=> array(
      'class'     => 'select2',
      'multiple'  => 'multiple',
      'style'     => 'min-width: 100%'
    )
  ));