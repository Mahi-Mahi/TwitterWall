<?php

add_action( 'after_setup_theme', 'thumbnail_sizes_setup' );
function  thumbnail_sizes_setup() {
	if ( ! function_exists('thumbnail_sizes') ):
		function thumbnail_sizes($name = null) {
			$sizes = apply_filters('thumbnail_sizes', array());
			if (is_null($name))
				return $sizes;
			else
				return $sizes[$name];
		}
	endif;
}

function thumbnails_optimized() {
	if ( defined('THUMBNAILS_OPTIMIZE') ) {
		return '-optim';
	}
	return '';
}

function update_post_thumbnails($post_id) {
	foreach(get_post_attachments($post_id) as $attachment):
		$pattern = preg_replace("#^.*/wp-content/(.*)(\.\w+)$#", $_SERVER['DOCUMENT_ROOT']."/wp-content/thumbnails/\\1-*\\2", $attachment->guid);
		$files = glob($pattern);
		if ( is_array($files) ):
			foreach($files as $file):
				if (file_exists($file)):
					unlink($file);
				endif;
			endforeach;
		endif;

	endforeach;

}
add_action('wp_insert_post', 'update_post_thumbnails');

function get_post_attachments($post_id = null, $types = null) {
	global $wpdb;

	if ( ! $post_id ):
		global $post;
		$post_id = $post->ID;
	endif;

	if ( ! $types )
		$types = array('image/jpeg', 'image/png', 'image/gif');
	elseif ( ! is_array($types) )
		$types = array($types);

	$types_list = "'".implode("', '", $types)."'";

	$sql = $wpdb->prepare("SELECT * FROM {$wpdb->posts} P WHERE P.post_parent = %d AND P.post_type = 'attachment' AND P.post_mime_type IN ( ".$types_list." ) ORDER BY P.menu_order ASC ", $post_id);
	$images = $wpdb->get_results($sql);

	return $images;
}

function static_thumbnail($size = 'thumbnail', $img_src, $args = null) {

	if ( is_array($args) )
		extract($args);

	extract(thumbnail_sizes($size));

	$img_src = parse_url($img_src, PHP_URL_PATH);

	$imgsrc = preg_replace("#http(s)?://".$_SERVER['HTTP_HOST']."#", '', $img_src);
	if ( file_exists($_SERVER['DOCUMENT_ROOT'].$imgsrc) ) :
		$imagesize = @getimagesize($_SERVER['DOCUMENT_ROOT'].$imgsrc);
		$original_width = $imagesize[0];
		$original_height = $imagesize[1];
	endif;

	if ( $original_width >= $width ):
		if ( isset($args['nocrop']) ) :
			$height = round($width / $original_width * $original_height);
		endif;
	else :
		if ( $original_width ):
			$width = $original_width;
			$height = $original_height;
		endif;
	endif;

	if ( ! preg_match("#^/wp-content/(.*)\.(jpeg|jpg|png|gif)$#i", $img_src, $match) )
		return false;
	$img_src = "/wp-content/thumbnails/".$match[1]."-".$width."x".$height.thumbnails_optimized().".".$match[2];

	$img_class = $img_id = "";

	if ( isset($class) )
		$img_class = 'class="'.$class.'" ';

	if ( isset($style) )
		$img_style = 'style="'.$style.'" ';


	if ( isset($id) )
		$img_id = 'id="'.$id.'" ';

	$img_src = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'].$img_src;

	if ( ! $skip_filters )
		$img_src = apply_filters('mahi_thumbnails', $img_src);

	if ( isset($only_src) )
		return $img_src;

	$img = '<img src="'.$img_src.'" '.$img_id.$img_class.$img_style.' width="'.$width.'" height="'.$height.'"  title="'.esc_attr($title).'" alt="'.esc_attr($alt).'" />';

	return $img;
}

/**
 * get a thumbnail html
 *
 * @param string $size The thumbnail size as defined by thumbnail_sizes function ( usually in themes/functions.php )
 * @param int $post_id Post ID ( optionnal, if not defined : lookup for attachment_id in $args, else will use global $post->ID )
 * @param array $args The tags to set for the post, separated by commas.
 * the optionnal args are :
 * - attachment_id : will use this attachment prior to the _thumbnail_id of current post
 * - default : if no attachement is found, then use this file ( it will also be resize as usual )
 * - nocrop : don't crop the image
 * - class : css class added to the img tag
 * - id : css id added to the img tag
 * - only_src : only return the url of the thumbnail
 * @return mixed Array of affected term IDs. WP_Error or false on failure.
 */

if (! function_exists('get_thumbnail')):
function get_thumbnail($size = 'thumbnail', $post_id = null, $args = null) {
	global $wpdb;

	$img_src = $original_width = $width = $height = $alt = $title = $attachment_id = null;
	$img_style = $img_class = $img_id = $img_itemprop = $img_data_src = $alt = $title = "";

	if ( ! $post_id ):
		$post_id = get_the_ID();
	endif;

	if(function_exists('thumbnail_sizes'))
		extract(thumbnail_sizes($size));

	if ( is_array($args) )
		extract($args);

	if ( isset($attachment_id) ) {

		if ( is_numeric($attachment_id) ) {
			$sql = "SELECT P.* FROM ".$wpdb->posts." P WHERE P.ID = ".$attachment_id." ";
			$res = $wpdb->get_row($sql);
		}
		else
			$res = $attachment_id;

		if ( is_object($res) )
			$img_src = $res->guid;

	}
	elseif ( isset($attachment_name )) {
		$sql = $wpdb->prepare("SELECT P.* FROM {$wpdb->posts} P WHERE P.post_title = %s AND P.post_parent = %d AND P.post_type ='attachment' ", $attachment_name, $post_id);
		$res = $wpdb->get_row($sql);
		$attachment_id = $res->ID;
		$img_src = $res->guid;
	}
	else {
		$sql = "SELECT P.* FROM {$wpdb->posts} P LEFT JOIN {$wpdb->postmeta} PM ON P.ID = PM.meta_value WHERE PM.post_id = ".$post_id." AND PM.meta_key = '_thumbnail_id' ";
		$res = $wpdb->get_row($sql);
		if ( is_object($res) ):
			$attachment_id = $res->ID;
			$img_src = $res->guid;
		endif;
	}

	if ( ! $img_src && isset($args['only_featured']) && ! isset($args['default'])  ):
		// trace('! $img_src && isset($args[only_featured]) && ! isset($args[default])' . get_caller());
		return false;
	endif;

	if ( ! isset($args['only_featured'])&& !isset($args['attachment_id']) ):
		if ( ! $img_src ):
			$sql = "SELECT P.guid FROM {$wpdb->posts} P WHERE P.post_parent = ".$post_id." AND P.post_type = 'attachment' ";
			$img_src= $wpdb->get_var($sql);
			$img_src = preg_replace("#http(s)?://".$_SERVER['HTTP_HOST']."#", '', $img_src);
		endif;
		if ( ! $img_src ):
			$post = get_post($post_id);
			$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
			if ( isset($matches[1][0]) )
				$img_src = $matches[1][0];
		endif;
	endif;

	if ( ! $img_src && ( isset($args['default']) || apply_filters('thumbnail_default', $post_id) ) ):
		$use_default = true;
		$default = isset($args['default']) ? $args['default'] : apply_filters('thumbnail_default', $post_id);
		$img_src = get_bloginfo('stylesheet_directory').$default;
	endif;

	$img_src = parse_url($img_src, PHP_URL_PATH);

	if ( ! empty($attachment_id) ) :

		$imagedata = wp_get_attachment_metadata( $attachment_id );
		$attachment = get_post($attachment_id);

	endif;

	if ( empty($imagedata) ) :
		$imgsrc = preg_replace("#http(s)?://".$_SERVER['HTTP_HOST']."#", '', $img_src);
		if ( file_exists($_SERVER['DOCUMENT_ROOT'].$imgsrc) ) :
			$imagesize = getimagesize($_SERVER['DOCUMENT_ROOT'].$imgsrc);
			$original_width = $imagesize[0];
			$original_height = $imagesize[1];
		endif;
	else:
		$original_width = $imagedata['width'];
		$original_height = $imagedata['height'];
	endif;

	if ( $args['fixheight']):
		if ( $original_height < $height ):
			$height = $original_height;
		endif;
	endif;

	if ( $args['forceheight'] ) :
		$width = round( $height * $original_width / $original_height );
	else:
		if ( $original_width >= $width ):
			if ( $args['nocrop'] ) :
				$height = round($width / $original_width * $original_height);
			endif;
		else :
			if ( $original_width ):
				$width = $original_width;
				if ( ! $args['nocrop'] ) :
					$height = $original_height;
				endif;

			endif;
		endif;
	endif;


	if ( ! isset($use_default) ):

		if ( ! preg_match("#^/(wp-content/uploads|files)/(.*)\.(jpeg|jpg|png|gif)$#i", $img_src, $match) ):
			//trace($img_src.' => ! preg_match("#^/(wp-content/uploads|files)/(.*)\.(jpeg|jpg|png|gif)$#i", $img_src, $match)');
			return false; //'<!-- post #'.$post_id.' : '.$img_src.' : '.__FILE__.' #'.__LINE__.' -->';
		endif;
		$img_src = "/wp-content/thumbnails/".str_replace('wp-content/', '', $match[1])."/".$match[2]."-".$width."x".$height.thumbnails_optimized().".".$match[3];
	else:

		if ( ! preg_match("#^/wp-content/(.*)\.(jpeg|jpg|png|gif)$#i", $img_src, $match) ):
			//trace($img_src.' => ! preg_match("#^/wp-content/(.*)\.(jpeg|jpg|png|gif)$#i", $img_src, $match)');
			return false; // '<!-- '.$img_src.': '.__FILE__.' #'.__LINE__.' -->';
		endif;
		$img_src = "/wp-content/thumbnails/".$match[1]."-".$width."x".$height.thumbnails_optimized().".".$match[2];
	endif;

	if ( isset($class) )
		$img_class = ' class="'.$class.'" ';


	if ( isset($style) )
		$img_style = 'style="'.$style.'" ';

	if ( isset($id) )
		$img_id = ' id="'.$id.'" ';

	if ( isset($itemprop) )
		$img_itemprop = ' itemprop="'.$itemprop.'" ';

	$img_src = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'].$img_src;

	if ( isset($only_src) )
		return $img_src;

	if ( isset($data_src) ):
		$img_data_src = ' data-src="'.$img_src.'" ';
		$img_src = get_bloginfo( 'stylesheet_directory' ).'/images/dot.png';
	endif;

	if ( isset($attachment_id) && (! $alt) )
		$alt = trim(strip_tags( get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ));

	if ( isset($attachment) ):
		if ( ! $alt )
			$alt = trim(strip_tags( $attachment->post_excerpt ));
		if ( ! $alt )
			$alt = trim(strip_tags( $attachment->post_title ));

		if (!$title)
			$title = trim(strip_tags( $attachment->post_title ));
	endif;

	$img_src = apply_filters('mahi_thumbnails', $img_src);

	if ( ! $img_src )
		$img_src = get_bloginfo( 'stylesheet_directory' ).'/images/dot.png';

	if ( $border )
		$border_attr = ' border="0" ';

	$img = '<img src="'.$img_src.'" '.$img_id.$img_class.$img_style.$img_itemprop.$img_data_src.' '.$border_attr.' width="'.($html_width ? $html_width : $width ).'" height="'.($html_height ? $html_height : $height).'"  title="'.esc_attr($title).'" alt="'.esc_attr($alt).'" />';
	return $img;
}
endif;
/*

thumbnail('medium_thumbnail', null, array('class' => 'boutique-img', 'default' => 'images/dossier-illustr.png') );

*/

if (! function_exists('thumbnail')):
function thumbnail($size = 'thumbnail', $post_id = null, $args = null) {
	print get_thumbnail($size, $post_id, $args);
}
endif;


