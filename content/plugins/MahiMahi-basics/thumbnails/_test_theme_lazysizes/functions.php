<?php

add_filter('thumbnail_sizes', 'lazysizes_thumbnail_sizes', 10, 2);
function lazysizes_thumbnail_sizes ($default_args, $name) {

	$sizes = array(

		'large'	=> array('width' => 400, 'height' => 200, 'responsive' => true, 'sizes' => '200,400,800,1200'),

		'list'	=> array('width' => 200, 'height' => 100, 'responsive' => false),

	);
	if ( ! isset($sizes[$name]) )
		return array_merge($default_args, current($sizes));

	return array_merge($default_args, $sizes[$name]);
}


function lazysizes_thumbnail_default($args) {

	$args['crop'] = true;
	$args['sizes'] = '200,400,600';

	return $args;
}
add_filter('thumbnail_default', 'lazysizes_thumbnail_default');


function lazysizes_setup() {

	add_theme_support( 'post-thumbnails' );

	wp_enqueue_style( 'lazysizes-style', get_stylesheet_uri() );

}
add_action( 'after_setup_theme', 'lazysizes_setup' );
