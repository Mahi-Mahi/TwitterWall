<?php

function tf_admin_init() {

	if ( ! function_exists('tf_template_fields') || ! function_exists('tf_fields'))
		return;
	
	if ( ! isset($_GET['post']) )
		return;
	
	$post = get_post($_GET['post']);
	
	if ( ! $post->ID )
		return;
			
	$template_fields = tf_template_fields(get_post_meta($post->ID, '_wp_page_template', true));

	if ( is_array($template_fields) )
	foreach($template_fields as $slug):

		$field = tf_fields($slug);

		$field['slug'] = $slug;

		if ( !isset($field['type']) )
			$field['type'] = 'text';

		add_meta_box('tf_'.$slug, $field['title'] ? $field['title'] : ucfirst($slug), "tf_meta_box_".(($field['type'])?$field['type']:'text'), 'page', 'normal', 'high', $field);	

	endforeach;
	
}
add_action("admin_init", "tf_admin_init");


function tf_meta_box_text($post, $context) {

	if ( ! function_exists('tf_template_fields') || ! function_exists('tf_fields'))
		return;

	$template_fields = tf_template_fields(get_post_meta($post->ID, '_wp_page_template', true));

	if ( ! is_array($template_fields) )
		return;

	$field = $context['args'];
	$field_slug = $field['slug'];

	if ( ! tf_fields($field_slug) )
		return;
	
	$id = "tf_".$field_slug;
	
	$value = get_post_meta($post->ID, $field_slug, true);
	
	?>
	<p>
		<input type="text" id="text_<?php print $id?>" name="<?php print $id?>" class="text" value="<?php echo $value; ?>" />
		<br />
	</p>
	<?php		
}


function tf_meta_box_wysiwyg($post, $context) {

	if ( ! function_exists('tf_template_fields') || ! function_exists('tf_fields'))
		return;

	$template_fields = tf_template_fields(get_post_meta($post->ID, '_wp_page_template', true));

	if ( ! is_array($template_fields) )
		return;

	$field = $context['args'];
	$field_slug = $field['slug'];

	if ( ! tf_fields($field_slug) )
		return;
	
	$id = "tf_".$field_slug;
	
	$value = get_post_meta($post->ID, $field_slug, true);
	
	?>
	<p>
		<?php
		if ( user_can_richedit() ):
			echo '<a id="edButtonHTML_'.$id.'" class="edButtonHTML hide-if-no-js" onclick="switchEditors.go(\'textarea_'.$id.'\', \'html\');">Éditeur simple</a>
				<a id="edButtonPreview_'.$id.'" class="edButtonPreview active hide-if-no-js" onclick="switchEditors.go(\'textarea_'.$id.'\', \'tinymce\');">Éditeur graphique</a><br>';
			echo '<script>jQuery("document").ready(function(){ if ( jQuery("#edButtonPreview").length ) switchEditors.go(\'textarea_'.$id.'\', \'tinymce\'); });</script>';
		endif; 
		?>
		<textarea id="textarea_<?php print $id?>" name="<?php print $id?>" class="code" /><?php echo $value; ?></textarea>
		<br />
	</p>
	<?php		
}

function tf_meta_box_post_reference($post, $context) {

	if ( ! function_exists('tf_template_fields') || ! function_exists('tf_fields'))
		return;

	$template_fields = tf_template_fields(get_post_meta($post->ID, '_wp_page_template', true));

	if ( ! is_array($template_fields) )
		return;

	$field = $context['args'];
	$field_slug = $field['slug'];

	if ( ! tf_fields($field_slug) )
		return;
	
	$id = "tf_".$field_slug;
	
	$values = get_post_meta($post->ID, $field_slug);

	if ( ! is_array($values)) 
		$values = preg_split("/,/", $values, -1, PREG_SPLIT_NO_EMPTY);

	?>
	<div>
		<label for="<?php print $id?>" ><?php print $field['title']?>:</label>

		<div class="post_reference autocomplete" >
			<input type="text" name="<?php print $id?>_search" id="<?php print $id?>" value="" class="search_field" />
			<input type="button" value="ajouter" onclick="post_reference_add(this, <?php echo (isset($field['single']))? 'true' : 'false'; ?>)" />
			<input type="hidden" class="autocomplete_query" value="<?php print base64_encode(serialize($field['query']))?>" />
			<ul>
			<?php 						
			foreach($values as $value):
				$value = get_post($value);
				?>
				<li id="post_reference-<?php print $value->ID?>">
					<span class="link" onClick="post_reference_remove(this)" title="delete">[x]</span>&nbsp;
					<input type="hidden" name="<?php print $id?>[]" value="<?php print $value->ID?>" />
					<?php print $value->post_title?>
				</li>
				<?php 						
			endforeach;
			?>
			</ul>
		</div>
	</div>
	<?php
	
}



function tf_wp_insert_post($post_id, $post = null) {

	if ( ! function_exists('tf_template_fields') || ! function_exists('tf_fields'))
		return;

	$template_fields = tf_template_fields(get_post_meta($post->ID, '_wp_page_template', true));

	if ( ! is_array($template_fields) )
		return;

	foreach ($template_fields as $slug) {

		$field = tf_fields($slug);

		$id = 'tf_'.$slug;
		$value = $_POST[$id];

		switch($field['type']):
			default:
		
				tf_wp_insert_post_default($slug, $post, $value);

			break;
			
		endswitch;
		
	}

}
add_action("wp_insert_post", "tf_wp_insert_post", 10, 2);


function tf_wp_insert_post_default($slug, $post, $value) {

	if ( ! function_exists('tf_template_fields') || ! function_exists('tf_fields'))
		return;

	$field = tf_fields($slug);
	$post_id = $post->ID;

	if (empty($value))
		delete_post_meta($post_id, $slug);

	if ( ! is_array($value) ) {
		delete_post_meta($post_id, $slug);
		add_post_meta($post_id, $slug, $value);
	}
	else {
			delete_post_meta($post_id, $slug);

		foreach ($value as $entry)
				add_post_meta($post_id, $slug, $entry);
	}
	
}


?>