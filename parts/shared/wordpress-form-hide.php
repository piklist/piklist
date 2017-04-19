
<?php if ($type == 'user'): ?>

  <style type="text/css">

    body.piklist-workflow-active.user-edit-php #your-profile .piklist-meta-box-title,
    body.piklist-workflow-active.profile-php #your-profile .piklist-meta-box-title,
    body.piklist-workflow-active.user-edit-php #your-profile .piklist-form-table,
    body.piklist-workflow-active.profile-php #your-profile .piklist-form-table,
    body.piklist-workflow-active.user-edit-php #your-profile p.submit,
    body.piklist-workflow-active.profile-php #your-profile p.submit {
      display: block;
    }

      body.piklist-workflow-active.user-edit-php #your-profile > *,
      body.piklist-workflow-active.profile-php #your-profile > * {
        display: none;
      }

      body.piklist-workflow-active.user-edit-php #profile-page > h1 {
        display: none;
      }

      body.piklist-workflow-active.user-edit-php #your-profile > .piklist-meta-box,
      body.piklist-workflow-active.profile-php #your-profile > .piklist-meta-box {
        display: block;
      }

  </style>

<?php elseif ($type == 'media'): ?>

  <style type="text/css">

    body.piklist-workflow-active.post_type-attachment .wp_attachment_holder,
    body.piklist-workflow-active.post_type-attachment .wp_attachment_details {
      display: none;
    }

  </style>

<?php elseif ($type == 'term'): ?>

  <style type="text/css">

    body.piklist-workflow-active.edit-tags-php .term-name-wrap,
    body.piklist-workflow-active.edit-tags-php .term-slug-wrap,
    body.piklist-workflow-active.edit-tags-php .term-parent-wrap,
    body.piklist-workflow-active.edit-tags-php .term-description-wrap,
		body.piklist-workflow-active.term-php .term-name-wrap,
    body.piklist-workflow-active.term-php .term-slug-wrap,
    body.piklist-workflow-active.term-php .term-parent-wrap,
    body.piklist-workflow-active.term-php .term-description-wrap  {
      display: none;
    }

  </style>

<?php endif; ?>
