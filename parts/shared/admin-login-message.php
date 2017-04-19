
<div class="message piklist-notice-<?php echo $type; ?> piklist-notice">

  <?php if (is_array($notices)): ?>
    
    <?php foreach ($notices as $notice): ?>

      <p><?php echo $notice; ?></p>

    <?php endforeach; ?>
  
  <?php else: ?>
    
    <p>
      <?php echo $notices; ?>
    </p>

  <?php endif; ?>

</div>