<?php
  $options = array_merge(
    array(
      'button' =>'Add Media'
      ,'modal_title' =>'Add Media'
      ,'basic' => false
      ,'preview_size' => 'piklist_file_preview'
      ,'textarea_rows' => 5
      ,'save' => 'id'
    )
    ,isset($options) && is_array($options) ? $options : array()
  );
?>

<div class="piklist-field-part">

  <?php if ($options['basic'] || !is_admin()): ?>

    <input
      type="file"
      id="<?php echo piklist_form::get_field_id($arguments); ?>"
      name="<?php echo piklist_form::get_field_name($arguments); ?>"
      <?php echo piklist_form::attributes_to_string($attributes); ?>
    />

  <?php else: ?>

    <a
      href="#"
      class="button piklist-upload-file-button piklist-field-part <?php echo $errors ? 'piklist-error' : null; ?>"
      title="<?php _e($options['modal_title']); ?>"
    >
      <?php _e($options['button']); ?>
    </a>

  <?php endif; ?>

  <div class="piklist-upload-file-preview piklist-field-preview">

  <?php
    $value = is_array($value) ? $value : array($value);
    $attachments = array();
    foreach($value as $attachment)
    {
      if ($attachment)
      {
        if (is_numeric($attachment))
        {
          $attachment_id = absint($attachment);
          $type = get_post_mime_type($attachment_id);

          if (false !== $type)
          {
            $check = piklist_media::image_has_size($attachment_id, $options['preview_size']);
            array_push($attachments, array(
              'id' => $attachment_id
              ,'type' => $type
              ,'data' => piklist_media::image_has_size($attachment_id, $options['preview_size'])
                  ? wp_get_attachment_image_src($attachment_id, $options['preview_size'], false)
                  : wp_get_attachment_image_src($attachment_id, 'thumbnail', false)
            ));
          }
        }
        else
        {
          $attachment_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid = %s", $attachment));

          if (is_readable($attachment))
          {
            $image_data = getimagesize($attachment);
          }
          else
          {
            $image_sizes = piklist_media::image_has_size($attachment_id, $options['preview_size'])
              ? piklist_media::get_image_sizes($options['preview_size'])
              : piklist_media::get_image_sizes('thumbnail');
            $image_data[0] = $image_sizes['width'];
            $image_data[1] = $image_sizes['height'];

            $mime = wp_check_filetype($attachment);
            $image_data['mime'] = $mime['type'];
          }

          array_push($attachments, array(
            'id' => $attachment_id
            ,'type' => $image_data['mime']
            ,'data' => array(
              $attachment
              ,$image_data[0]
              ,$image_data[1]
              ,false
            )
          ));
        }
      }
    }

  ?>

    <?php
      if (empty($attachments)):
    ?>

        <input
          type="hidden"
          id="<?php echo piklist_form::get_field_id($arguments); ?>"
          name="<?php echo piklist_form::get_field_name($arguments); ?>"
          <?php echo piklist_form::attributes_to_string($attributes); ?>
          value="<?php echo isset($options['unset_value']) ? $options['unset_value'] : null; ?>"
        />

    <?php
      else:
        for ($count = count($attachments), $_index = 0; $_index < $count; $_index++):
    ?>

          <input
            type="hidden"
            id="<?php echo piklist_form::get_field_id($arguments); ?>"
            name="<?php echo piklist_form::get_field_name($arguments); ?>"
            value="<?php echo esc_attr($options['save'] == 'url' ? $attachments[$_index]['data'][0] : $attachments[$_index]['id']); ?>"
            <?php echo piklist_form::attributes_to_string($attributes); ?>
          />

    <?php
        endfor;
      endif;
    ?>

    <ul class="attachments">

      <?php
        if (!empty($attachments)):
          foreach ($attachments as $attachment):

            if ($attachment['type']):
              if (in_array($attachment['type'], array('image/jpeg', 'image/png', 'image/gif'))):
                $image = $attachment['data'];
      ?>

                <li class="attachment selected" <?php echo $image[1] ? 'style="width: ' . $image[1] . 'px;"' : null; ?>>
                   <div class="attachment-preview <?php echo (int) $image[1] > (int) $image[2] ? 'landscape' : 'portrait'; ?>">
                     <div class="thumbnail">
                       <div class="centered">
                         <a href="#">
                           <img src="<?php echo esc_attr($image[0]); ?>" width="<?php echo esc_attr($image[1]);?>" />
                         </a>
                       </div>
                     </div>
                     <?php if (version_compare($wp_version, '4.3', '<')): ?>
                       <a class="check" href="#" title="Deselect" tabindex="0" data-attachment-save="<?php echo $options['save']; ?>" data-attachment-id="<?php echo $attachment['id']; ?>" data-attachment-url="<?php echo esc_attr($image[0]); ?>" data-attachments="<?php echo piklist_form::get_field_name($arguments); ?>"><div class="media-modal-icon"></div></a>
                     <?php else: ?>
                       <button type="button" class="button-link check" data-attachment-save="<?php echo $options['save']; ?>" data-attachment-id="<?php echo $attachment['id']; ?>" data-attachment-url="<?php echo esc_attr($image[0]); ?>" data-attachments="<?php echo piklist_form::get_field_name($arguments); ?>"><span class="media-modal-icon"></span><span class="screen-reader-text"><?php _e('Deselect'); ?></span></button>
                     <?php endif; ?>
                   </div>
                 </li>

      <?php
              else:
                $attachment_path = get_attached_file($attachment['id']);
                $file_type = wp_check_filetype($attachment_path, wp_get_mime_types());

                $icon = includes_url() . 'images/media/' . substr($file_type['type'], 0, strpos($file_type['type'], '/')) . '.png';
                $icon = file_exists($icon) ? $icon : includes_url() . 'images/media/document.png';
      ?>

                <li class="attachment selected">
                   <div class="attachment-preview attachment-preview-document landscape type-<?php echo substr($file_type['type'], 0, strpos($file_type['type'], '/')); ?> subtype-<?php echo substr($file_type['type'], strpos($file_type['type'], '/') + 1); ?>">
                     <div class="thumbnail">
                       <div class="centered">
                          <img src="<?php echo $icon; ?>" class="icon" />
                       </div>
                       <div class="filename">
                         <div><?php echo basename($attachment_path); ?></div>
                       </div>
                     </div>
                     <?php if (version_compare($wp_version, '4.3', '<')): ?>
                       <a class="check" href="#" title="Deselect" tabindex="0" data-attachment-save="<?php echo $options['save']; ?>" data-attachment-id="<?php echo $attachment['id']; ?>" data-attachments="<?php echo piklist_form::get_field_name($arguments); ?>"><div class="media-modal-icon"></div></a>
                     <?php else: ?>
                       <button type="button" class="button-link check" data-attachment-save="<?php echo $options['save']; ?>" data-attachment-id="<?php echo $attachment['id']; ?>" data-attachments="<?php echo piklist_form::get_field_name($arguments); ?>"><span class="media-modal-icon"></span><span class="screen-reader-text"><?php _e('Deselect'); ?></span></button>
                     <?php endif; ?>
                   </div>
                 </li>

      <?php
              endif;
            endif;
          endforeach;
        endif;
      ?>

    </ul>

  </div>

</div>
