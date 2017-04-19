<?php
  
  $fields = piklist('field', array(
    'type' => 'hidden'
    ,'scope' => false
    ,'field' => 'role'
    ,'value' => $user_roles[0]
    ,'return' => true
  ));

  $fields .= piklist('field', array(
    'type' => 'checkbox'
    ,'scope' => false
    ,'field' => 'roles'
    ,'template' => 'field'
    ,'choices' => $roles
    ,'value' => $user_roles
    ,'return' => true
  ));
  
?>

<script type="text/javascript">

  (function($)
  {
    $(document).ready(function()
    {
      var field = $('select[name="role"]');
      
      field
        .parents('.form-field')
        .removeClass('form-field');
      
      field.replaceWith('<?php echo preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', '', $fields)); ?>');
    });
  })(jQuery);

</script>