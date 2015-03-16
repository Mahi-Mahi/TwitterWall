<?php

function mahi_wpseo_opengraph_image_size($size) {
	return 'full';
}
add_filter('wpseo_opengraph_image_size', 'mahi_wpseo_opengraph_image_size');


function mahi_wpseo_opengraph_image($img) {

	if (function_exists('mahi_dethumbnail')):
		$img = mahi_dethumbnail($img);
		$img = get_static_thumbnail('og', $img, array('width' => 400, 'height' => 400, 'only_src' => true));
	endif;

	return $img;
}
add_filter('wpseo_opengraph_image', 'mahi_wpseo_opengraph_image');

function mahi_pre_update_option_wpseo_titles($value) {

	logr($value);

	logr(array_filter($value));

	logr(get_caller());

	return $value;
}
// add_filter('pre_update_option_wpseo_titles', 'mahi_pre_update_option_wpseo_titles');


function mahi_wpseo_replacements($replacements) {
	global $r;
	$replacements['%%cf_menu_order%%'] = get_post_field('menu_order', $r->ID);
	return $replacements;
}
add_filter('wpseo_replacements', 'mahi_wpseo_replacements');