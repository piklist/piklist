<?php
/*
Flow: Demo Workflow
Page: post.php, post-new.php, post-edit.php, profile.php, user-edit.php, edit-tags.php, term.php, piklist_demo_fields
Post Type: piklist_demo, attachment
Taxonomy: piklist_demo_type
Header: true
Position: title
Clear: true
*/

// WORKFLOW TABS/BAR: to change a Workflow to a bar, set Layout: Bar

// NOTE: Piklist hides the titles of the user/term pages when workflows are applied there
//       since they are out of context, so here we add our own and super charge them a bit...

if (in_array($pagenow, array('profile.php', 'user-edit.php'))): ?>

  <h2>
    <?php if ($pagenow == 'profile.php'): ?>
      <?php _e('Edit Your Profile'); ?>
    <?php
      else:
        $user = get_user_by('id', (int) $_REQUEST['user_id']);
    ?>
      <?php _e('Edit User: ' . $user->user_login); ?> <a href="user-new.php" class="page-title-action"><?php _e('Add New'); ?></a>
    <?php endif;?>
  </h2>

<?php
  elseif (in_array($pagenow, array('edit-tags.php', 'term.php'))) :
    $taxonomy = get_taxonomy($taxnow);
?>

  <h2><?php echo $taxonomy->labels->edit_item; ?></h2>

<?php endif;
