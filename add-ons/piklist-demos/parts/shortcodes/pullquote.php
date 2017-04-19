<?php
/*
Shortcode: pullquote
*/
?>

<section class="pullquote">
  <?php echo esc_html($quote); ?>
  <br/>
  <?php if (!empty($source)): ?>
    <cite><em><?php echo esc_html($source); ?></em></cite>
  <?php endif; ?>
</section>