
<style type="text/css">
  
  html {
    background-color: #f3f3f3 !important;
  }
  
  #adminmenuback,
  #adminmenuwrap,
  #wpadminbar,
  #screen-meta-links,
  #wphead,
  #wpfooter,
  #footer,
  .update-nag,
  .wrap h2:first-child,
  .wrap h2:nth-child(0),
  .wrap .icon32 {
    display: none !important;
    margin: 0 !important;
  }
  
  #wpcontent {
    margin-left: 0px !important;
    padding-top: 6px !important;
    padding-left: 14px !important;
  }
  
  html.wp-toolbar {
    padding-top: 0px !important;
  }
  
  <?php if (isset($_REQUEST[piklist::$prefix]['embed']) && $_REQUEST[piklist::$prefix]['embed'] == 'true'): ?>
  
    .sidebars-column-1,
    .sidebars-column-2,
    .sidebars-column-3,
    .sidebars-column-4 {
      max-width: none !important;
    }
  
  <?php endif; ?>
  
</style>
