<?php
/*  
Title: Reading Settings
Method: post
Message: Each option has been updated.
Logged in: true
*/
  
  $choices = get_posts(array(
                         'post_type' => 'page'
                         ,'orderby' => 'post_title'
                         ,'order' => 'ASC'
                         ,'posts_per_page' => -1
                       ), 'objects');
                 
  $choices = piklist($choices, array('ID', 'post_title'));

  foreach ($choices as $value => $choice):
    if ($choice === ''):
      $choices[$value] = sprintf(__('#%d (no title)'), $value);
    endif;
  endforeach;
  
  $choices = array_replace(array('' => '&mdash; Select &mdash;'), $choices);

  piklist('field', array(
    'type' => 'group'
    ,'scope' => 'option'
    ,'label' => 'Front page displays'
    ,'template' => 'form_table'
    ,'position' => 'start'
    ,'fields' => array(
      array(
        'type' => 'radio'
        ,'field' => 'show_on_front'
        ,'value' => 'posts'
        ,'choices' => array(
          'posts' => 'Your latest posts'
          ,'page' => 'A <a href="edit.php?post_type=page">static page</a> (select below)'
        )
      )
      ,array(
        'type' => 'select'
        ,'label' => 'Front page'
        ,'field' => 'page_on_front'
        ,'choices' => $choices
        ,'columns' => 6
        ,'conditions' => array(
          array(
            'type' => 'disabled'
            ,'field' => 'show_on_front'
            ,'value' => 'posts'
          )
        )
      )
      ,array(
        'type' => 'select'
        ,'label' => 'Posts page'
        ,'field' => 'page_for_posts'
        ,'choices' => $choices
        ,'columns' => 6
        ,'conditions' => array(
          array(
            'type' => 'disabled'
            ,'field' => 'show_on_front'
            ,'value' => 'posts'
          )
        )
      )
    )
  ));

  piklist('field', array(
    'type' => 'number'
    ,'scope' => 'option'
    ,'field' => 'posts_per_page'
    ,'label' => 'Blog pages show at most'
    ,'description' => 'posts'
    ,'template' => 'form_table_inline_description'
    ,'attributes' => array(
      'class' => 'small-text'
    )
  ));

  piklist('field', array(
    'type' => 'number'
    ,'scope' => 'option'
    ,'field' => 'posts_per_rss'
    ,'label' => 'Syndication feeds show the most recent'
    ,'description' => 'items'
    ,'template' => 'form_table_inline_description'
    ,'attributes' => array(
      'class' => 'small-text'
    )
  ));

  piklist('field', array(
    'type' => 'radio'
    ,'scope' => 'option'
    ,'field' => 'rss_use_excerpt'
    ,'label' => 'For each article in a feed, show'
    ,'template' => 'form_table'
    ,'choices' => array(
       '0' => 'Full text'
       ,'1' => 'Summary'
    )
  ));

  piklist('field', array(
    'type' => 'checkbox'
    ,'scope' => 'option'
    ,'field' => 'blog_public'
    ,'label' => 'Search Engine Visibility'
    ,'template' => 'form_table'
    ,'position' => 'end'
    ,'description' => 'It is up to search engines to honor this request.'
    ,'choices' => array(
      0 => 'Discourage search engines from indexing this site'
    )
    ,'options' => array(
      'unset_value' => 1
    )
  ));

  piklist('field', array(
    'type' => 'submit'
    ,'field' => 'submit'
    ,'value' => 'Save Changes'
    ,'template' => 'submit'
    ,'attributes' => array(
      'class' => 'button button-primary'
    )
  ));
  
  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Form'
  ));