<?php
/*
* Inspired by Taxonomy Filters
* ---
* Copyright (C) 2013-2014, Andrea Landonio - landonio.andrea@gmail.com
* License: GPL v3
*/
?>

  <script type="text/javascript">
    
    (function($)
    {
      $(document).ready(function()
      {
        $('<?php echo "#taxonomy-" . $name; ?>').append('' +
          '<label for="<?php echo "taxonomy_filter_value_" . $name; ?>"><?php _e('Filter', 'piklist');?>:</label>&nbsp;' +
          '<input type="text" id="<?php echo "taxonomy_filter_value_" . $name; ?>" name="<?php echo "taxonomy_filter_value_" . $name; ?>" class="<?php echo "taxonomy_filter_value" ?>" size="14" autocomplete="off"/>&nbsp;' +
          '<input type="button" value="<?php _e('Reset', 'piklist');?>" id="<?php echo "taxonomy_filter_reset_" . $name; ?>" name="<?php echo "taxonomy_filter_reset_" . $name; ?>" class="button <?php echo "taxonomy_filter_reset" ?>"/>'
        );

        $('<?php echo "#taxonomy_filter_value_" . $name; ?>').keyup(function()
        {
          var filter_value = $(this).val();
          var filter_ul_id = '<?php echo "#" . $name . "checklist"; ?>';

          $(filter_ul_id).find("li").each(function()
          {
            $(this).removeClass("filter-exists");
            $(this).parent("ul.children").removeClass("filter-exists");
          });

          $(filter_ul_id).find("input[type='checkbox']").each(function()
          {
            var filter_item = $(this).parent();
            var filter_li = $(this).parent().parent();

            if (filter_item.text().toLowerCase().indexOf(filter_value.toLowerCase()) > -1)
            {
              filter_li.show();
              filter_li.addClass("filter-exists");
              filter_li.parents("ul.children").addClass("filter-exists");
            }
        });

        $(filter_ul_id).find("li:not(.filter-exists)").each(function()
        {
          if ($(this).children("ul.children.filter-exists").length == 0)
          {
            $(this).hide();
          }
          else
          {
            $(this).show();
          }
          });
        });

        $('<?php echo "#taxonomy_filter_reset_" . $name; ?>').click(function()
        {
          $('<?php echo "#" . $name . "checklist"; ?>').find("input[type='checkbox']").each(function()
          {
              $(this).parent().parent().show();
          });

          $('<?php echo "#taxonomy_filter_value_" . $name; ?>').val('');
        });
      })
    })(jQuery);
    
  </script>