<?php

if ( file_exists( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' ) )
	require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );
else
	require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp/wp-load.php' );

if ( ! $_SERVER['REQUEST_URI'] && $_SERVER['DOCUMENT_URI'] )
	$_SERVER['REQUEST_URI'] = $_SERVER['DOCUMENT_URI'];

if ( isset($_GET['url']) ):
	$_SERVER['REQUEST_URI'] = urldecode($_GET['url']);
	$_GET['DEBUG'] = true;
else:
	$_SERVER['REQUEST_URI'] = urldecode($_SERVER['REQUEST_URI']);
endif;

if ( preg_match("#((/".basename(WP_CONTENT_DIR)."/)tt/([^/]+)/(.*)(@2x)?\.(jpeg|jpg|png|gif))(\?.*)?$#iU", $_SERVER['REQUEST_URI'], $match) ):

	$basepath = $match[1];
	$args = $match[3];
	$img_src = $match[2].$match[4].'.'.$match[6];
	$retina = ($match[5] == '@2x');

else:

	if ( preg_match("#((/".basename(WP_CONTENT_DIR)."/)thumbnails/(.*)-tt-(.*)(@2x)?\.(jpeg|jpg|png|gif))(\?.*)?$#iU", $_SERVER['REQUEST_URI'], $match) ):
		$basepath = $match[1];
		$args = $match[4];
		$img_src = $match[2].$match[3].'.'.$match[6];
		$retina = ($match[5] == '@2x');
	endif;

endif;

if ( empty($match) ):
	logr("no match : ".$_SERVER['REQUEST_URI']);
	status_header( 404 );
	empty_image();
	exit();
endif;

$_SERVER['REQUEST_URI'] = preg_replace("#-force-1#", '', $_SERVER['REQUEST_URI']);

if ( defined('DATA_PATH') ):
	$abspath = constant('DATA_PATH');
	$img_src = str_replace('wp-content/', '', $img_src);
	$img_dest = str_replace('//', '/', $abspath.$basepath);
	$img_dest = str_replace('wp-content/', '', $img_dest);
else:
	// $abspath = constant('ABSPATH');
	$abspath = dirname(constant('WP_CONTENT_DIR'));
	$img_dest = str_replace('//', '/', $abspath.$basepath);
endif;

preg_match_all("#([\w\d\_]+)\-([\d\w\_]+)#", $args, $tmp, PREG_SET_ORDER);
$args = apply_filters('thumbnail_default', array());
foreach($tmp as $t)
	$args[thumbnail_args_short($t[1])] = $t[2];

if ( ! is_file($abspath.$img_src) && is_file($abspath.str_replace(' ', '+', $img_src) ) ):
	$img_src = str_replace(' ', '+', $img_src);
endif;


$img_src = str_replace($abspath, '', apply_filters('mahi_thumbnail_src', $abspath.$img_src, $args) );

if ( ! is_file($abspath.$img_src) ):
	logr("file not found ".$abspath.$img_src);
	do_action('mahi_thumbnail_missing_src', str_replace('//', '/', $abspath.$img_src), $args );
	status_header( 404 );
	empty_image();
	exit();
endif;

$img_dest_dir = dirname($img_dest);

@mkdir($img_dest_dir, 0777, true);

extract($args);

if ( $except_gif ):
	$file_info  = wp_check_filetype( $abspath.$img_src );
	// If $file_info['type'] is false, then we let the editor attempt to figure out the file type, rather than forcing a failure based on extension.
	if ( ( isset( $file_info ) && $file_info['type'] && $file_info['type'] == 'image/gif' ) or preg_match("#\.gif$#", $img_src) ):
		if ( ! $force ):
			copy($abspath.$img_src, $img_dest);
		endif;
		readfile($abspath.$img_src);
		exit();
	endif;
endif;

$image = wp_get_image_editor($abspath.$img_src);


if ( $width && $retina )
	$width = $width * 2;
if ( $height && $retina  )
	$height = $height * 2;

if ( ! is_wp_error( $image ) ):

	list($dest_w, $dest_h) = $image->wp_constrain_dimensions( $width + $max_width, $height + $max_height );

	switch($orientation):
		case 3:
			$image->rotate(180);
		break;
		case 6:
			$image->rotate(-90);
		break;
		case 8:
			$image->rotate(90);
		break;
	endswitch;

	if ( $crop ):
		$image->resize( $width, $height, $crop );
	else:
		$image->scale( $width ? $width : $dest_w, $height ? $height : $dest_h, $bgcolor, $args );
	endif;

	$image->set_progressive();

	if ( ! $force ):

		$res = $image->save( $img_dest );

		if ( is_wp_error( $res ) ):

			if ( ! $_GET['retry'] ):
				require_once(ABSPATH . 'wp-admin/includes/file.php');
				logr("RETRY");
				$image_content = download_url('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'?retry=1');
				rename($image_content, $img_dest);
				readfile($img_dest);
				exit();
			endif;
		endif;

	endif;

	if ( defined('MAHI_THUMB_REDIRECT') ):
		wp_redirect('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	else:
		$image->stream();
	endif;

else:
	 // logr($image);
endif;
