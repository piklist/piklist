<div class="wrap">

  <h2><?php echo esc_html($title); ?></h2>
  
  <?php do_action('piklist_admin_page_after_title'); ?>
  
  <?php 
    if ($page_sections):
      foreach ($page_sections as $page_section):
        if ($page_section['data']['position'] == 'before'):
          foreach ($page_section['render'] as $render):
            piklist($render);
          endforeach;
        endif;
      endforeach;
    endif;
  ?>

  <?php if (isset($setting) && !empty($setting)): ?>

    <?php if ($save): ?>
      
      <?php if ($notice): ?>
  
        <?php settings_errors(); ?>
      
      <?php endif; ?>
      
      <form action="<?php echo admin_url('options.php'); ?>" method="post" enctype="multipart/form-data">

        <?php settings_fields($setting); ?>
        
        <?php if ($layout == 'meta-boxes'): ?>
          
          <?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false); ?>
          
          <div id="poststuff">
            
            <div id="post-body" class="metabox-holder columns-2">
            
        <?php endif; ?>
        
        <?php do_action('piklist_pre_render_settings_form'); ?>

    <?php endif; ?>            

    <?php if ($layout == 'meta-boxes'): ?>
      
      <?php piklist_setting::do_settings_sections($setting); ?>
    
    <?php else: ?>
      
      <?php do_settings_sections($setting); ?>
    
    <?php endif; ?>
    
    <?php if ($layout == 'meta-boxes'): ?>
      
      <div id="postbox-container-1" class="postbox-container">
        
        <?php do_meta_boxes($current_screen->id, 'side', $setting); ?>

      </div>
      
      <div id="postbox-container-2" class="postbox-container">
        
        <?php do_meta_boxes($current_screen->id, 'normal', $setting); ?>

        <?php do_meta_boxes($current_screen->id, 'advanced', $setting); ?>
    
      </div>
      
    <?php endif; ?>
      
    <?php if ($save): ?>
    
        <?php if ($layout == 'meta-boxes'): ?>
          
            </div>
            
          </div>
            
        <?php endif; ?>
        
        <?php do_action('piklist_post_render_settings_form'); ?>
        
        <?php do_action('piklist_settings_form'); ?>
       
        <?php if ($layout != 'meta-boxes'): ?>
          
          <?php submit_button(esc_html__($save_text)); ?>
            
        <?php endif; ?>
         
      </form>

    <?php endif; ?>
  
  <?php endif; ?>
  
  <?php 
    if ($page_sections):
      foreach ($page_sections as $page_section):
        if ($page_section['data']['position'] == 'after'):
          foreach ($page_section['render'] as $render):
            piklist($render);
          endforeach;
        endif;
      endforeach;
    endif;
  ?>
  
</div>

<script type="text/javascript">

  (function($)
  {
    $(document).ready(function()
    {
      if (typeof postboxes != 'undefined')
      {
        postboxes.add_postbox_toggles('<?php echo $current_screen->id; ?>');
      }
    });
  })(jQuery);

</script>
  
