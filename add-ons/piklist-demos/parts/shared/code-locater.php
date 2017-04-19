<?php

  $arguments = array(
    'type' => 'html'
    ,'value' => sprintf(__('The code that built this %s can be found here:', 'piklist-demo'), '<strong>' . $type . '</strong>') . '<br><code>' . str_replace(ABSPATH, '', $location) . '</code>'
  );
  
  switch ($type)
  {
    case 'Meta Box':
      
      $arguments['template'] = 'field';
    
      $arguments['attributes'] = array(
        'class' => 'piklist-demo-highlight'
      );
    
    break;
    
    case 'Help tab':
    case 'Admin notice':
    
      $arguments['template'] = 'field';
    
    break;
  }

  piklist('field', $arguments);