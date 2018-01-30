<?php
if ( !function_exists('piklist_build_checkbox_tree') ) {
  function piklist_build_checkbox_tree(&$choices, &$arguments, &$attributes, &$values, &$options, $count = 0) {
    $list = '';
    foreach($choices as $key => $choice)
    {
      $field_arguments = $arguments;
      $field_arguments['index'] = $count;

      $id = piklist_form::get_field_id($field_arguments);
      $name = piklist_form::get_field_name($arguments);
      $attributes = piklist_form::attributes_to_string($attributes);
      $value = esc_attr($key);
      $label = __($choice['display']);
      $checked = in_array($key, $values) ? 'checked="checked"' : '';

      $hidden = 0 === $count ? "
        <input type='hidden' id='$id' name='$name' value='{$options['unset_value']}' />
      " : '';

      $list .= "
        <li>
          <label class='piklist-field-list-item'>
            <input type='checkbox' name='$name' value='$value' $checked />
            $hidden
            <span class='piklist-list-item-label'>$label</span>
          </label>
        </li>
      ";

      $count++;

      if ( !empty($choice['choices']) )
      {
        $list .= '<li>' . piklist_build_checkbox_tree($choice['choices'], $arguments, $attributes, $values, $options, $count) . '</li>';
      }
    }

    return $list ? "<ul>$list</ul>" : '';
  }
}

$options = wp_parse_args(isset($options) ? $options : array(), array(
  'height'      => '400px',
  'scrollable'  => true,
  'unset_value' => null
));

$values = is_array($value) ? $value : array($value);
$tree = piklist_build_checkbox_tree($choices, $arguments, $attributes, $values, $options);

$scrollable = $options['scrollable'] ? 'scrollable' : '';
$height = $options['scrollable'] ? "height: {$options['height']};" : '';

echo "
  <div style='$height' class='checkbox-tree-container $scrollable'>$tree</div>
";
