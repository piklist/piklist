<?php
/*
Name: Piklist Form
Description: Embed a Piklist form
Shortcode: piklist_form
Icon: dashicons-forms
*/

  $forms = array();
  
  $registered_forms = piklist_form::get('forms');
  foreach ($registered_forms as $form)
  {
    if ($form['add_on'] != 'piklist')
    {
      if (!isset($forms[$form['add_on']]))
      {
        $forms[$form['add_on']] = array(
          'name' => piklist('humanize', $form['add_on'])
          ,'add_on' => $form['add_on']
          ,'forms' => array()
        );
      }
    
      $forms[$form['add_on']]['forms'][$form['id']] = $form['data']['title'] ? $form['data']['title'] : $form['id'];
    }
  }
  
  piklist('field', array(
    'type' => 'select'
    ,'field' => 'add_on'
    ,'label' => __('Add-on', 'piklist')
    ,'required' => true
    ,'choices' => array('' => 'Select Add-on') + piklist($forms, array('add_on', 'name'))
  ));
  
  $fields = array();
  
  foreach ($forms as $data):

    array_push($fields, array(
      'type' => 'radio'
      ,'field' => 'form'
      ,'choices' => $data['forms']
      ,'required' => true
      ,'conditions' => array(
        array(
          'field' => 'add_on'
          ,'value' => $data['add_on']
        )
      )
    ));

  endforeach;
  
  piklist('field', array(
    'type' => 'group'
    ,'fields' => $fields
  ));