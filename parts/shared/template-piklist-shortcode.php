<script type="text/html" id="tmpl-piklist-shortcode">
  <div class="piklist-shortcode <# if ( data.options.icon ) { #>piklist-shortcode-dashicon<# } #> mceItem">
    <# if ( data.options.icon ) { #>
      <div class="dashicons {{ data.options.icon }}"></div>
    <# } #>
    <strong>{{ data.options.name }}</strong>
    <# if ( data.options.description ) { #>
      <em>{{ data.options.description }}</em>
    <# } #>
  </div>
</script>
