<?php
/*
Title: Locked Meta Box (Can not be dragged.)
Post Type: piklist_demo
Order: 20
Lock: true
Collapse: false
Tab: Layout
Sub Tab: Meta Boxes
Flow: Demo Workflow
*/

  piklist('field', array(
    'type' => 'text'
    ,'field' => 'text-meta-box-locked'
    ,'label' => __('Text', 'piklist-demo')
  ));