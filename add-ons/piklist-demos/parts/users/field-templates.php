<?php
/*
Title: Field Templates
Order: 10
Tab: Layout
Sub Tab: Field Templates
Flow: Demo Workflow
*/

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'text_standard'
    ,'label' => __('Default', 'piklist-demo')
    ,'description' => __('Piklist automatically assigning the "post_meta" field template.', 'piklist-demo')
    ,'attributes' => array(
      'class' => 'regular-text'
      ,'placeholder' => __('Enter text here.', 'piklist-demo')
    )
  ));

  ?>

  <div class="piklist-demo-highlight">

      <?php _e('Using the Piklist "field" field template, only shows a field, with no label.', 'piklist-demo');?>

  </div>


  <?php
 
   piklist('field', array(
    'type' => 'text'
    ,'field' => 'text_template'
    ,'label' => __('Text', 'piklist-demo')
    ,'template' => 'field'
    ,'attributes' => array(
      'class' => 'large-text'
      ,'placeholder' => __('I have no label', 'piklist-demo') . ' :('
    )
  ));