<?php array_push($attributes['class'], 'piklist-field-part'); ?>

<div
  <?php echo piklist_form::attributes_to_string($attributes); ?>
  id="<?php echo piklist_form::get_field_id($arguments); ?>" 
  name="<?php echo piklist_form::get_field_name($arguments); ?>"><?php echo is_array($value) ? implode($value, ' ') : $value; ?></div>
