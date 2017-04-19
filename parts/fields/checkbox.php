
<?php if ($list): ?>
  
  <<?php echo isset($list_type) ? $list_type : 'ul'; ?> class="piklist-field-list">
  
<?php endif; ?>
  
<?php if ($choices): ?>
  
  <?php 
    $values = array_keys($choices);
    for ($_index = 0; $_index < count($choices); $_index++):
      $checked = '';
      if (!is_array($value) && $value == $values[$_index]):
        $checked = 'checked="checked"';
      elseif (is_array($value)):
        foreach ($value as $_value):
          if ($_value != '' && $values[$_index] == $_value):
            $checked = 'checked="checked"';
            break;
          endif;
        endforeach;
      endif;
      $_arguments = $arguments;
      $_arguments['index'] = $_index;
  ?>
  
    <?php echo $list || $list_item_type ? ('<' . ($list_item_type ? $list_item_type : 'li') . '>') : ''; ?>
    
      <label class="piklist-field-list-item">
        
        <input 
          type="checkbox"
          id="<?php echo piklist_form::get_field_id($_arguments); ?>" 
          name="<?php echo piklist_form::get_field_name($arguments); ?>"
          value="<?php echo esc_attr($values[$_index]); ?>"
          <?php echo $checked; ?>
          <?php echo piklist_form::attributes_to_string($attributes); ?>
        />

        <?php if ($_index == 0): ?>
        
          <input 
            type="hidden"
            id="<?php echo piklist_form::get_field_id($_arguments); ?>" 
            name="<?php echo piklist_form::get_field_name($arguments); ?>"
            value="<?php echo isset($options['unset_value']) ? $options['unset_value'] : null; ?>"
          />
      
        <?php endif; ?>
        
        <span class="piklist-list-item-label">
          <?php _e($choices[$values[$_index]]); ?>
        </span>
    
      </label>
  
    <?php echo $list || $list_item_type ? ('</' . ($list_item_type ? $list_item_type : 'li') . '>') : ''; ?>

  <?php endfor; ?>
  
<?php endif; ?>
  
<?php if ($list): ?>

  </<?php echo isset($list_type) ? $list_type : 'ul'; ?>>

<?php endif; ?>