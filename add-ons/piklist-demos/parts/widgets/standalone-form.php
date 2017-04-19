<?php

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'text_class_small'
    ,'label' => __('Text', 'piklist-demo')
    ,'value' => 'Lorem'
    ,'help' => __('You can easily add tooltips to your fields with the help parameter.', 'piklist-demo')
    ,'attributes' => array(
      'class' => 'regular-text'
    )
  ));

  // Show the path to this file in the Demos
  // DO NOT use this in your own code
  piklist('shared/code-locater', array(
    'location' => __FILE__
    ,'type' => 'Widget'
  ));