
<select
  id="<?php echo piklist_form::get_field_id($arguments); ?>"
  name="<?php echo piklist_form::get_field_name($arguments); ?>"
  <?php echo piklist_form::attributes_to_string($attributes); ?>
>
  <?php
    foreach ($choices as $choice_value => $choice):
      $choice_value = (string) $choice_value;
      if (is_array($choice)):
  ?>
        <optgroup label="<?php _e($choice_value); ?>">
        <?php foreach ($choice as $optgroup_choice_value => $optgroup_choice): ?>
          <option value="<?php echo esc_attr($optgroup_choice_value); ?>" <?php echo (is_array($value) ? in_array($optgroup_choice_value, $value) : $value == $optgroup_choice_value) ? 'selected="selected"' : ''; ?>><?php _e($optgroup_choice); ?></option>
        <?php endforeach; ?>
        </optgroup>
      <?php else: ?>
        <option value="<?php echo esc_attr($choice_value); ?>" <?php echo (is_array($value) ? in_array($choice_value, $value) : $value == $choice_value) ? 'selected="selected"' : ''; ?>><?php _e($choice); ?></option>
      <?php endif; ?>
  <?php endforeach; ?>
</select>
