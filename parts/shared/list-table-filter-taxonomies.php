<?php

	$tax_obj = get_taxonomy($taxonomy_name);
	$tax_name = $tax_obj->labels->all_items;
	$tax_slug = $taxonomy_name;

	$terms = get_terms(array($taxonomy_name), array('hide_empty' => false));

?>

	<?php if (count($terms) > 0) : ?>

		<select name="<?php echo $tax_slug;?>" id="<?php echo $tax_slug; ?>" class="postform" style="max-width:90%;">

			<option value="">Show <?php echo $tax_name;?></option>

			<?php foreach ($terms as $term) : ?>

			<option value=<?php echo $term->slug, isset($_REQUEST[$tax_slug]) ? $term->slug == esc_attr($_REQUEST[$tax_slug]) ? ' selected="selected"' : '' : '';?>><?php echo $term->name;?></option>

			<?php endforeach; ?>

		</select>

<?php endif;
