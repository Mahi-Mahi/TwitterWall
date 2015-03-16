<?php
/*
Plugin Name: Mahi Import External Images
*/

function mahi_import_external_image_wp_insert_post($post_id, $post){
	global $import_external_image_post_id;

	$import_external_image_post_id = $post_id;

	$post->post_content = preg_replace_callback("#<img[^>]+>#", 'mahi_import_external_image_wp_insert_post_callback', $post->post_content);

}
add_action('wp_insert_post', 'mahi_import_external_image_wp_insert_post', 10, 2);

function mahi_import_external_image_wp_insert_post_callback($img) {
	global $import_external_image_post_id;

	preg_match_all("#\s+([\w\-]+)=[\"\']([^\"\']+)[\"\']#", $img[0], $matches, PREG_SET_ORDER);

	foreach($matches as $match):
		$attrs[$match[1]] = $match[2];
	endforeach;

	if ( is_array($attrs) ) {

		$hostname = parse_url($attrs['src'], PHP_URL_HOST);

		if ( $hostname && $hostname != $_SERVER['SERVER_NAME'] ):

			logr("get ".$attrs['src']);

			logr($img[0]);

			$attachment_id = mahibasics_media_sideload_image($attrs['src'], $import_external_image_post_id, $alt);

			$attrs['src'] = get_thumbnail(null, null, array('attachment_id' => $attachment_id, 'only_src' => true));

			$img[0] = '<img ';
			foreach($attrs as $k => $v)
				$img[0] .= $k . '="' . $v . '" ';
			$img[0] .= '>';

			logr($img[0]);

		endif;
	}

	return $img[0];
}


function mahi_attach_media_wp_insert_post_callback($img) {

	xmpr($img[0]);

	if ( preg_match("#(/wp-content/)thumbnails/(uploads/.*)-tt-.*(\.\w+)$#", $img[0], $tmp ) ):

		$filename = $tmp[1].$tmp[2].$tmp[3];

		xmpr($filename);

	endif;

}

