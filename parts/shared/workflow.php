
<div class="piklist-workflow">

  <?php 
    foreach ($workflows as $tab):
      if ($tab['data']['header']):
        piklist::render($tab['part'], array(
          'data' => $tab
        ));
      endif;
    endforeach;
  ?>
    
  <div class="piklist-workflow-tabs-container">
    
    <ul class="piklist-workflow-tabs">

      <?php 
        foreach ($workflows as $tab):
          if (!$tab['data']['header']):
            if ($layout == 'bar'):
              ?><li><a class="<?php echo $tab['data']['active'] ? 'piklist-workflow-tab-current' : null; ?>" <?php echo $tab['url'] ? 'href="' . esc_url($tab['url']) . '"' : null; ?>><?php _e($tab['data']['title']); ?></a></li><?php
            else:
              ?><a class="piklist-workflow-tab <?php echo $tab['data']['active'] ? 'piklist-workflow-tab-active' : null; ?>" <?php echo $tab['url'] ? 'href="' . esc_url($tab['url']) . '"' : null; ?>><?php _e($tab['data']['title']); ?></a><?php
            endif;
          endif;
        endforeach;
      ?>
  
    </ul>

    <?php do_action('piklist_workflow_flow_append', $tab['data']['flow_slug']); ?>
    
  </div>

  <?php if (isset($active['parts'])): ?>

    <div class="piklist-workflow-tabs-container">
  
      <ul class="piklist-workflow-tabs-sub">
    
        <?php foreach ($active['parts'] as $order => $part): ?>
      
          <li class="piklist-workflow-tabs-sub"><a <?php echo $part['url'] ? 'href="' . esc_url($part['url']) . '"' : null; ?> class="<?php echo $part['data']['active'] ? 'current' : null; ?>"><?php _e($part['data']['title']); ?></a> <?php echo $part === end($parts) ? null : '|'; ?></li>

        <?php endforeach; ?>

      </ul>
    
    </div>

  <?php endif; ?>
  
  <?php
    do_action('piklist_pre_render_workflow', $active);
  
    piklist::render($active['part'], array(
      'data' => $active
    ));
  
    do_action('piklist_post_render_workflow', $active);
  ?>

</div>