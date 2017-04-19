
<form 
  method="<?php echo strtolower($data['method']); ?>" 
  action="<?php echo $data['filter'] ? home_url() . $data['action'] : null; ?>" 
  enctype="multipart/form-data"
  id="<?php echo $id; ?>"
  autocomplete="off"
  data-piklist-form="true"
  class="piklist-form <?php echo is_admin() ? 'hidden' : null; ?>"
>
  <?php
    do_action('piklist_notices', $id);
  
    foreach ($render as $form):

      piklist::render($form, $data);

    endforeach;
      
    piklist('field', array(
      'type' => 'hidden'
      ,'scope' => piklist::$prefix
      ,'field' => 'form_id'
      ,'value' => $id
    ));
    
    if ($data['filter']):

      piklist('field', array(
        'type' => 'hidden'
        ,'scope' => piklist::$prefix
        ,'field' => 'filter'
        ,'value' => 'true'
      ));

    endif;
    
    if ($data['redirect']):

      piklist('field', array(
        'type' => 'hidden'
        ,'scope' => piklist::$prefix
        ,'field' => 'redirect'
        ,'value' => $data['redirect']
      ));

    endif;
    
    if (piklist_admin::hide_ui()):

      piklist('field', array(
        'type' => 'hidden'
        ,'scope' => piklist::$prefix
        ,'field' => 'admin_hide_ui'
        ,'value' => 'true'
      ));

    endif;
  
    piklist_form::save_fields(); 
  ?>  
</form>