
<div class="wp-tab-panel">
  
  <?php
    piklist('field', array(
      'type' => 'checkbox'
      ,'scope' => 'post_meta'
      ,'field' => '_' . piklist::$prefix . 'relate_post'
      ,'object_id' => $arguments['object_id']
      ,'choices' => piklist(
        get_posts(array(
          'post_type' => $scope
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
  ?>

</div>  