<?php

/* Lazy Sizes */

add_action('after_setup_theme', 'mahi_lazy_init');
function mahi_lazy_init() {
	if ( defined('MAHI_LAZYSIZES') ):
		add_action('init', 'lazysizes_wp_head');
	endif;

	if ( defined('MAHI_LAZYLOAD') ):
		add_action('init', 'lazyload_wp_head');
	endif;

}


function lazysizes_wp_head() {
	if ( is_admin() )
		return;
	wp_register_script('respimage', constant('MahiMahiBasics_PATH').'/thumbnails/respimage.js', array(), null, true);
	wp_register_script('picturefill', constant('MahiMahiBasics_PATH').'/thumbnails/picturefill.js', array(), null, true);
	wp_enqueue_script('lazysizes', constant('MahiMahiBasics_PATH').'/thumbnails/lazysizes.js', array('respimage'), null, true);
}



/* Lazy Load  */


if ( ! defined('MAHI_LAZYLOAD_GREY') ):
	if ( defined('MAHI_LAZYLOAD_V2') ):
		define('MAHI_LAZYLOAD_GREY', constant('MahiMahiBasics_PATH').'/thumbnails/b.gif');
	else:
		define('MAHI_LAZYLOAD_GREY', constant('MahiMahiBasics_PATH').'/thumbnails/grey.gif');
	endif;
endif;


function lazyload_wp_head() {
	if ( is_admin() )
		return;

	if ( ! defined('MAHI_LAZYSIZES') ):
		if ( defined('MAHI_LAZYLOAD_V2') ):
			wp_enqueue_script('in-viewport', constant('MahiMahiBasics_PATH').'/thumbnails/in-viewport.js');
			wp_enqueue_script('lazyload', constant('MahiMahiBasics_PATH').'/thumbnails/lazyload.js', array());
		else:
			wp_enqueue_script('jquery-lazyload', constant('MahiMahiBasics_PATH').'/thumbnails/jquery.lazyload.js', array( 'jquery' ), false, true);
			wp_enqueue_script('mahi-lazyload', constant('MahiMahiBasics_PATH').'/thumbnails/mahi.lazyload.js', array( 'jquery-lazyload' ), false, true);
		endif;
	endif;
}


// replace admin thumbnail

function mahi_thumbnail_filter_image_sizes( $sizes) {
    unset( $sizes['thumbnail']);
    unset( $sizes['medium']);
    unset( $sizes['large']);
    return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'mahi_thumbnail_filter_image_sizes');

function mahi_get_intermediate_image_sizes() {

	// make thumbnails and other intermediate sizes
	global $_wp_additional_image_sizes;

	$sizes = array();
	foreach ( get_intermediate_image_sizes() as $s ) {
		$sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => true );
		if ( isset( $_wp_additional_image_sizes[$s]['width'] ) )
			$sizes[$s]['width'] = intval( $_wp_additional_image_sizes[$s]['width'] ); // For theme-added sizes
		else
			$sizes[$s]['width'] = get_option( "{$s}_size_w" ); // For default sizes set in options
		if ( isset( $_wp_additional_image_sizes[$s]['height'] ) )
			$sizes[$s]['height'] = intval( $_wp_additional_image_sizes[$s]['height'] ); // For theme-added sizes
		else
			$sizes[$s]['height'] = get_option( "{$s}_size_h" ); // For default sizes set in options
		if ( isset( $_wp_additional_image_sizes[$s]['crop'] ) )
			$sizes[$s]['crop'] = intval( $_wp_additional_image_sizes[$s]['crop'] ); // For theme-added sizes
		else
			$sizes[$s]['crop'] = get_option( "{$s}_crop" ); // For default sizes set in options
	}

	return $sizes;
}

function mahi_thumbnail_get_size_args($size) {
	$sizes = mahi_get_intermediate_image_sizes();

	if ( !is_array($size) ) :
		if ( isset($sizes[$size]))
			$size = array($sizes[$size]['width'], $sizes[$size]['height'], $sizes[$size]['crop']);
		else
			$size = array(null, null, 1);
	endif;
	return $size;
}

function mahi_thumbnail_image_downsize($false, $id, $size) {

	$size = mahi_thumbnail_get_size_args($size);

	$args = array('attachment_id' => $id, 'only_src' => true, 'width' => $size[0], 'height' => $size[1], 'crop' => false, 'fill' => false, 'nosize' => true, 'bgcolor' => 'FFFFFF', 'lazyload' => false);

	$img_url = get_thumbnail(null, null, $args);

	return array( $img_url, $size[0], $size[1], true );
}
add_filter('image_downsize', 'mahi_thumbnail_image_downsize', 10, 3);

function mahi_thumbnails_post_thumbnail_html($html, $post_id, $post_thumbnail_id, $size, $attr){
	if ( preg_match_all("#(width|height)-(\d+)#", $html, $tmp, PREG_SET_ORDER) )
		$html = preg_replace("#^<img\s#", '<img '.$tmp[0][1].'="'.$tmp[0][2].'" '.$tmp[1][1].'="'.$tmp[1][2].'" ', $html);
	return $html;
}
add_filter('post_thumbnail_html', 'mahi_thumbnails_post_thumbnail_html', 10, 5);

/*

patterns :

	/wp-content/thumbnails/[path]/[filename][args].[fileext]

	/wp-content/[prefix]/[path]/[args]/[filename].[fileext]

// RewriteRule ^wp-content/(tt-[^/]+)/(.+)(\.\w+)$ /wp-content/$1/$3-$2$4


*/






// RewriteRule ^wp-content/thumbnails/(.+)$ /wp-content/plugins/MahiMahi-basics/thumbnails/thumb.php [QSA,L]
function mahi_thumbnail_rewrite() {
	global $wp_rewrite;
	$wp_rewrite->add_external_rule( basename(WP_CONTENT_DIR).'/thumbnails/(.+)$', basename(WP_CONTENT_DIR).'/plugins/MahiMahi-basics/thumbnails/thumb.php' );
}
// add_action( 'generate_rewrite_rules', 'mahi_thumbnail_rewrite');


function thumbnails_add_htaccess($rules) {

	$new_rules = <<<EOF


# BEGIN Thumbnails

<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^wp-content/thumbnails/(.+)$ /wp-content/plugins/MahiMahi-basics/thumbnails/thumb.php [QSA,L]
</IfModule>

# END Thumbnails

EOF;

	return $new_rules . PHP_EOL . $rules;
}
//add_filter('mod_rewrite_rules', 'thumbnails_add_htaccess');

function thumbnails_wp_image_editors($r) {
	require_once('class-mahi-image-editor-gd.php');
	require_once('class-mahi-image-editor-imagick.php');
	$r = array( 'Mahi_Image_Editor_Imagick', 'Mahi_Image_Editor_GD' );
	return $r;
}
add_filter('wp_image_editors', 'thumbnails_wp_image_editors');

add_filter( 'jpeg_quality', 'thumbnails_jpeg_quality', 10, 2);
function thumbnails_jpeg_quality($quality, $context = null) {
//	logr("thumbnails_jpeg_quality($quality, $context");
	return 90;
}

/*
Short Args

*/

function thumbnail_args_short($abbr = null, $arg = null) {

	$args = array(
		'w'	=>	'width',
		'h'	=>	'height',
		'i'	=>	'attachment_id',
		't'	=>	'format',
		'c' =>	'crop',
		'f'	=>	'fill',
		'b' =>	'bgcolor',
		'e' =>	'force', // erase
	);

	if ( $abbr ):
		if ( defined('MAHI_THUMBNAIL_SHORT_ARGS') ):
			return $args[$abbr];
		else:
			return $abbr;
		endif;
	endif;

	if ( $arg ):
		if ( defined('MAHI_THUMBNAIL_SHORT_ARGS') ):
			foreach($args as $k => $v)
				if ( $v == $arg ):
					return $k;
				endif;
		else:
			return $arg;
		endif;
	endif;

	return $args;

}

function thumbnail_args($format, $args) {

	// Get defaults and compile args
	$default_args = array(
			'width'			=> null,
			'height'		=> null,
			'attachment_id'	=> null,
			'lazyload'		=> null,
			'max_width'		=> null,
			'max_height'	=> null,
			'fill'			=> null,
			'crop'			=> null,
			'bgcolor'		=> '000000',
			'responsive'	=> null,
			'default_img'	=> null,
			'alt'			=>	""
		);

	if ( is_local() )
		$default_args['format'] = preg_replace("#\-#", '_', $format);

	// DEFAULT ARGS
	$default_args = apply_filters('thumbnail_default', $default_args);

	// FORMAT ARGS
	$format_args = apply_filters('thumbnail_sizes', $default_args, $format);

	// FILTER + CURRENT THUMBNAIL ARGS
	$r = array_merge( apply_filters('thumbnail_args', $format_args, $format), $args );

	if ( is_feed() || defined('IS_FEED') )
		$r['lazyload'] = false;

	return $r;
}

function get_thumbnail($format = 'thumbnail', $post_id = null, $args = array()) {

	$r = thumbnail_args($format, $args);

	$k = array_keys($r);

	extract($r);

	if ( ! $post_id )
		$post_id = get_the_ID();

	/**
	* get the image
	* order :
	*  - attachement_id
	*  - featured
	*/

	global $cud_post_id;
	if ( ! $cud_post_id )
		$cud_post_id = $post_id;

	$uploads = wp_upload_dir();

	if ( ! $attachment_id )
		$attachment_id = thumbnail_get_post($post_id);

	if ( $attachment_id ):

		$file = get_post_meta( $attachment_id, '_wp_attached_file', true) ;

		$img_src = $uploads['basedir'].'/'.$file;

		/*
		$metas = wp_get_attachment_metadata($attachment_id);

		$orig_w = $metas['width'];
		$orig_h = $metas['height'];
		*/

	else:

		if ( ! $only_featured ):

			if ( ! $only_content && $attachment = get_children(array('post_type' => 'attachment', 'post_parent' => $post_id, 'posts_per_page' => 1, 'post_mime_type' => 'image')) ):

				$img_src = preg_replace("#http://[^/]+/#", '', current($attachment)->guid);

			else:

				$post_content = get_post_field('post_content', $post_id);

				if ( preg_match("#<img\s(.*)src=\"([^\"]+)\"#", $post_content, $matches) ):

					if ( preg_match("#^data:#", $img_src ) )
						return false;

					$img_src = preg_replace("#".site_url()."/#", '', $matches[2]);

				endif;

			endif;

			if ( preg_match("#http://#", $img_src)):
				return get_thumbnail_img_tag($format, $img_src, $r);
				// require_once(ABSPATH . 'wp-admin/includes/file.php');
				// $img_src = download_url($img_src);
				// logr($img_src);
			endif;
			$img_src = $_SERVER['DOCUMENT_ROOT'].'/'.$img_src;

		endif;

	endif;

	$r = compact($k);

	if ( ! $img_src && $default_img )
		$img_src = /*$_SERVER['DOCUMENT_ROOT'].'/'. */ $default_img;

	$img_src = preg_replace("#^.*wp-content/#", WP_CONTENT_DIR.'/', $img_src);

	$img_src = apply_filters('mahi_thumbnail_src', $img_src, $r);

	$img_src = mahi_dethumbnail($img_src);

	return _static_thumbnail($format, $img_src, $r);

}


function get_thumbnail_credits($post_id = null) {
	if ( ! $post_id )
		$post_id = get_the_ID();

	$attachment_id = thumbnail_get_post($post_id);

	if ( ! $attachment_id && $attachment = get_children(array('post_type' => 'attachment', 'post_parent' => $post_id, 'posts_per_page' => 1, 'post_mime_type' => 'image')) )
		$attachment_id = $attachment->ID;

	if ( ! $attachment_id )
		return false;

	return get_post_field('post_excerpt', $attachment_id);
	// return get_post_meta($attachment_id, '_wp_attachment_image_alt', true);

}

function thumbnail_remote_src($img_src, $r) {
	extract($r);
	if ( $img_src ) :
		if ( ! is_file($img_src) ):
			if ( $remote_src ):
				$remote_src = str_replace(WP_CONTENT_DIR.'/', $remote_src.'wp-content/', $img_src);
				@mkdir(dirname($img_src), 0775, true);
				// logr("wget ".$remote_src);
				$content = @file_get_contents($remote_src);
				@file_put_contents($img_src, $content );
			else:
				if ( defined('THUMBNAIL_LOG') ):
					logr('thumbnail - file not found : '.$img_src);
					logr('-> '.$_SERVER['REQUEST_URI']);
					logr(get_caller());
				endif;
				return $img_src;
			endif;
		endif;
	endif;
	return $img_src;
}
add_filter('mahi_thumbnail_src', 'thumbnail_remote_src', 99, 2);


function thumbnail_default_img($img_src, $r) {
	extract($r);
	if ( $img_src ) :
		if ( ! is_file($img_src) && $default_img ):

			$img_src = preg_replace("#^(.*)".basename(WP_CONTENT_DIR)."#", WP_CONTENT_DIR, $default_img);
			$img_src = preg_replace("#/+#", '/', $img_src);

		endif;
	endif;
	return $img_src;
}
add_filter('mahi_thumbnail_src', 'thumbnail_default_img', 99, 2);


function thumbnail_img_src_cleanup($img_src, $r) {
	$img_src = preg_replace("#\?.*$#", '', $img_src);
	return $img_src;
}
add_filter('mahi_thumbnail_src', 'thumbnail_img_src_cleanup', 10, 2);

function thumbnail_img_src_remove_host($img_src, $r) {

	$img_src = preg_replace("#^http://[^/]+/#", $_SERVER['DOCUMENT_ROOT'].'/', $img_src);

	return $img_src;
}
add_filter('mahi_thumbnail_src', 'thumbnail_img_src_remove_host', 10, 2);


function thumbnail($format = 'thumbnail', $post_id = null, $args = array()) {
	print get_thumbnail($format, $post_id, $args);
}

function thumbnail_get_post($post_id) {

	// get feature image attachment id

	$attachment_id = get_post_thumbnail_id($post_id);

// TODO : get first media of post
// TODO : get first media in post_content

	return $attachment_id;
}

function thumbnail_extra_args($r) {
	$extra_args = array();
	foreach($r as $k => $v)
		if ( $v != null && $v != '' )
			if ( ! in_array($k, array('responsive', 'default_img', 'skip_filters', 'class', 'alt', 'title', 'style', 'only_featured', 'only_content', 'itemprop', 'host', 'only_src', 'nosize', 'remote_src', 'src', 'sizes', 'generate', 'attachment_id', 'exif_rotate') ) )
				$extra_args[] = thumbnail_args_short(null, $k).'-'.$v;
	$extra_args = '-tt-'.implode('-', $extra_args);
	return $extra_args;
}

function thumbnail_img_url($img_path, $extra_args, $r = array()) {

	$img_url = preg_replace("#^.*(/".basename(WP_CONTENT_DIR)."/)(.+)/(.+)\.(\w+)$#", "\\1thumbnails/\\2/\\3".$extra_args.".\\4", $img_path);

	$img_url = preg_replace("#(\w)//(\w)#", "$1/$2", $img_url);

	$img_url = preg_replace("#/thumbnails/thumbnails/#", "/thumbnails/", $img_url);

	$img_url = preg_replace("#^([^/])#", "/$1", $img_url);

	if ( $r['host'] )
		$img_url = request_scheme().'://'.$_SERVER['HTTP_HOST'].$img_url;

	if ( ! $skip_filters )
		$img_url = apply_filters('mahi_thumbnails', $img_url, $img_path, $extra_args, $r);

	return $img_url;
}

function static_thumbnail($format = 'thumbnail', $img_src, $args = array()) {
	print get_static_thumbnail($format, $img_src, $args);
}

function get_static_thumbnail($format = 'thumbnail', $img_src, $args = array()) {

	$r = thumbnail_args($format, $args);

	$img_src = apply_filters('mahi_thumbnail_src', $img_src, $r);

	return _static_thumbnail($format, $img_src, $r);
}


/*
function mahi_thumbnail_img_path($img_path, $img_src, $r) {

	if ( ! is_file($img_path) ):

		if ( $r['default_img'] )
			return $r['default_img'];

	endif;

	return $img_path;
}
add_filter('mahi_thumbnail_img_path', 'mahi_thumbnail_img_path', 99, 3);
*/

function _static_thumbnail($format = 'thumbnail', $img_src, $r) {

	$k = array_keys($r);
	extract($r);

	if ( ! $orig_w || ! $orig_h ):

		list($orig_w, $orig_h) = @getimagesize($img_src);

	endif;

	$img_path = str_ireplace(WP_CONTENT_DIR, '/'.basename(WP_CONTENT_DIR).'/', $img_src);
	$img_path = preg_replace("#^.*(/(wp-)?content/.*)$#", "\\1", $img_path);
	$img_path = preg_replace("#/+#", "/", $img_path);
	$img_path = apply_filters('mahi_thumbnail_img_path', $img_path, $img_src, $r);

	if ( $format == 'original' ):
		$width = $orig_w;
		$height = $orig_h;
		$img_url = $img_path;

		if ( $r['host'] ):
			$img_url = 'http://'.$_SERVER['HTTP_HOST'].$img_url;
		endif;

	else:
		if ( $width && $height ):

			$dest_w = $width;
			$dest_h = $height;

		else:

			list($dest_w, $dest_h) = wp_constrain_dimensions( $orig_w, $orig_h, $width + $max_width, $height + $max_height);

			if ( ! $width )
				$width = $dest_w;
			if ( ! $height )
				$height = $dest_h;

		endif;

		if ( $exif_rotate ):

			$exif = @exif_read_data($img_src, 'Orientation');
			$k[] = 'orientation';
			$orientation = $exif['Orientation'];

			switch($r['orientation']):
				case 6:
				case 8:
					// $old_height = $height;
					// $height = $width;
					// $width = $old_height;
				break;
			endswitch;

		endif;

		if ( $orig_w < $width && ! $r['fill'] ):
			$r['crop'] = false;
			$width = $orig_w;
		endif;
		if ( $orig_h < $height )
			$r['crop'] = false;

	//	$extra_args = '-'.base64_encode( serialize( compact( array_keys( $r ) ) ) );

		if ( $get_size ):
			return compact(array('width', 'height'));
		endif;

		$r = compact($k);
		$extra_args = thumbnail_extra_args($r);

		$img_url = thumbnail_img_url($img_path, $extra_args, $r);

		if ( $force ):
			$dest_file = str_replace('//', '/', $_SERVER['DOCUMENT_ROOT'].'/'.$img_url);
			@unlink($dest_file);
		endif;

	endif;

	if ( $r['only_src'])
		return $img_url;

	$img_tag = get_thumbnail_img_tag($format, $img_url, $r);

	$img_tag = apply_filters('mahi_thumbnail_img_tag', $img_tag, $format, $img_src, $r);

	return $img_tag;

}


function get_thumbnail_img_tag($format, $img_url, $r) {

	extract($r);

	foreach(array('class', 'id', 'title', 'itemprop', 'alt', 'style') as $k)
		if ( isset(${$k}) )
			${$k.'_attr'} = ' '.$k.'="'.esc_attr(${$k}).'"';

	if( is_array($data) )
		foreach($data as $k => $v)
			$datas .= ' data-'.$k.'="'.$v."'";

	if ( ! $nosize )
		$size = ' width="'.$width.'" height="'.$height.'" ';

	$img_tag = '<img src="'.$img_url.'" '.$size.$datas.$class_attr.$id_attr.$title_attr.$alt_attr.$style_attr.' />';

	/* Lazy Load */
	if ( defined('MAHI_LAZYLOAD') && $r['lazyload'] !== false && ! is_feed() && ! is_admin() ):

		$data['src'] = $img_url;

		if ( is_array($r['responsive'])):
			foreach($r['responsive'] as $k => $v):
				$extra_args = thumbnail_extra_args(array_merge($r, $v));
				$img_url = thumbnail_img_url($img_path, $extra_args, $r);
				$data[$k.'-src'] = $img_url;
			endforeach;
		endif;

		$class .= ' lazy';
		$img_url = constant('MAHI_LAZYLOAD_GREY');

		foreach(array('class', 'id', 'alt', 'title') as $k)
			if ( ${$k} )
				${$k.'_attr'} = ' '.$k.'="'.esc_attr(${$k}).'"';

		if( is_array($data) )
			foreach($data as $k => $v)
				$datas .= ' data-'.$k.'="'.$v.'"';

		if ( defined('MAHI_LAZYLOAD_V2') ):
			$img_tag = '<img src="'.$img_url.'" width="'.$width.'" height="'.$height.'" '.$datas.$class_attr.$id_attr.$title_attr.$alt_attr.' onload="lzld(this)" />'. '<noscript>'.$img_tag.'</noscript>';
		else:
			$img_tag = '<img src="'.$img_url.'" width="'.$width.'" height="'.$height.'" '.$datas.$class_attr.$id_attr.$title_attr.$alt_attr.' />'. '<noscript>'.$img_tag.'</noscript>';
		endif;

	endif;

	$with_container = false;

	// Lazy Sizes */
	if ( defined('MAHI_LAZYSIZES') && ! is_feed() && ! is_admin() ):
		$class .= ' lazyload';

		$lqip_size = 100;
		$data['src'] = $img_url;

		if ( ! $width && is_dev() ):
			// xmpr("$format, $img_url, ".serialize($r));
		endif;

		$img_url = preg_replace("#-width-(\d+)#", "-width-".$lqip_size, $img_url);
		$img_url = preg_replace("#-height-(\d+)#", "-height-".round($lqip_size / $width * $height), $img_url);

		if ( $r['responsive'] ):

			$srcset = array();

			$sizes = array();
			foreach(explode(',', $r['sizes']) as $size):
				$sizes[$size] = preg_replace("#-width-(\d+)#", "-width-".$size, $img_url);
				$sizes[$size] = preg_replace("#-height-(\d+)#", "-height-".round($size / $width * $height), $sizes[$size]);
			endforeach;
			$img_url = current($sizes);

			foreach($sizes as $size => $src)
				$srcset[] = $src.' '.$size.'w';

			$data['sizes'] = 'auto';
			$data['srcset'] = implode(', ', $srcset);
			$with_container = true;
		endif;

		foreach(array('class', 'id', 'alt', 'title') as $k)
			if ( ${$k} )
				${$k.'_attr'} = ' '.$k.'="'.esc_attr(${$k}).'"';

		$datas = "";
		if( is_array($data) )
			foreach($data as $k => $v)
				$datas .= ' data-'.$k.'="'.$v.'"';

		$img_tag = '<img src="'.$img_url.'" '.($responsive ? '' : 'width="'.$width.'" height="'.$height.'" ' ).' '.$datas.$class_attr.$id_attr.$title_attr.$alt_attr.' />';

	endif;

	if ( $with_link )
		$img_tag = '<a href="'.$img_path.'" title="'.esc_attr($alt).'">'.$img_tag.'</a>';

	if ( $with_container )
		$img_tag = '<div class="img_container">'.$img_tag.'</div>';

	return $img_tag;
}

function get_user_thumbnail($format, $user_id = null, $args = array()) {

	if ( ! function_exists('bp_loggedin_user_id'))
		return;

	if ( ! $user_id )
		$user_id = bp_loggedin_user_id();

	$r = thumbnail_args($format, $args);

	$img_src = apply_filters( 'bp_get_displayed_user_avatar', bp_core_fetch_avatar( array( 'item_id' => $user_id, 'type' => 'full', 'html' => false ) ) );

	$src = parse_url($img_src);
	switch($src['host']):
		case 'gravatar.com':
			$avatar_dir = 'avatars';
			$avatar_folder_dir = apply_filters( 'bp_core_avatar_folder_dir', bp_core_avatar_upload_path() . '/' . $avatar_dir . '/' . $user_id, $item_id, 'user', $avatar_dir );

			$avatar_src = $avatar_folder_dir.md5(bp_core_get_user_email( $user_id ));

			if ( is_local() ):
				$avatar_src = $r['default_img'];
			else:
				if ( ( ! is_file($avatar_src) || $r['force'] === true ) ):
					require_once(ABSPATH . 'wp-admin/includes/file.php');
					$avatar_tmp = download_url($img_src);
					if ( is_wp_error($avatar_tmp) ):
						copy($_SERVER['DOCUMENT_ROOT'].$r['default_img'], $avatar_src);
					else:
						rename($avatar_tmp, $avatar_src);
					endif;
				endif;
			endif;

			// logr(compact('img_src', 'avatar_src'));
			$img_src = $avatar_src;

		break;
		default:
			$img_src = $src['path'];
			$img_src = $_SERVER['DOCUMENT_ROOT'].$img_src;
		break;
	endswitch;

	return get_static_thumbnail($format, $img_src, $r);

}

function user_thumbnail($format, $user_id = null, $args = array()) {
	print get_user_thumbnail($format, $user_id, $args);

}


function thumbnail_js() {
	$_REQUEST['args']['lazyload'] = false;
	print get_thumbnail($_REQUEST['format'], $_REQUEST['post_ID'], $_REQUEST['args']);
	exit();
}
add_action('wp_ajax_thumbnail_js', 'thumbnail_js');
add_action('wp_ajax_nopriv_thumbnail_js', 'thumbnail_js');

function mahi_dethumbnail($img) {
	$img = preg_replace("#(/(wp-)?content/)thumbnails/(.+)-tt-(.*)(\.\w+)$#", "\\1\\3\\5", $img);
	return $img;
}
