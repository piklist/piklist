<?php
/*
Title: Remove Meta Box "Look"
Post Type: piklist_demo
Order: 30
Meta Box: false
Collapse: false
Tab: Layout
Sub Tab: Meta Boxes
Flow: Demo Workflow
*/

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'text-meta-box-none'
    ,'label' => __('Text', 'piklist-demo')
  ));