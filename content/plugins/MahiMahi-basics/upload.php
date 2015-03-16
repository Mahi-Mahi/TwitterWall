<?php

add_filter('sanitize_file_name', 'remove_accents' );

function mahibasics_media_sideload_image($file, $post_id, $desc = null, $post_data = null) {

	if ( ! empty($file) ) {
		require_once(ABSPATH.'/wp-admin/includes/file.php');
		require_once(ABSPATH.'/wp-admin/includes/media.php');
		require_once(ABSPATH.'/wp-admin/includes/image.php');
		// Download file to temp location
		$tmp = download_url( $file, 5 );

		// Set variables for storage
		// fix file filename for query strings
//		preg_match('/[^\?]+\.(jpg|JPG|jpe|JPE|jpeg|JPEG|gif|GIF|png|PNG))?/', $file, $matches);
		$file_array['name'] = preg_replace("#[^\w\d\-\_\.]#", '-', basename($file));
		if ( ! preg_match("#\.(jpe?g|png|gif)#i", $file_array['name']) )
			$file_array['name'] .= '.png';
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			// logr('Source url : '.$file);
			// logr($tmp->errors);
			if ( is_string($file_array['tmp_name']) && file_exists($file_array['tmp_name']) )
				unlink($file_array['tmp_name']);
			return null;
		}

		if ( is_array($desc) )
			$desc = $desc['post_excerpt'];

		// do the validation and storage stuff
		$id = media_handle_sideload( $file_array, $post_id, $desc, $post_data );

		// If error storing permanently, unlink
		if ( is_wp_error($id) ) {
			// logr('Source url : '.$file);
			// logr($tmp->errors);
			if ( file_exists($file_array['tmp_name']) )
				unlink($file_array['tmp_name']);
			return null;
		}
		return $id;
	}

}


function mahi_insert_attachment($file, $post_id = null, $post_data = array()) {
	// logr("mahi_insert_attachment($file, $post_id");
	if ( $post_data['filename'] )
		$basename = $post_data['filename'];
	else
		$basename = basename($file);

	$time = current_time('mysql');
	if ( $post = get_post($post_id) ) {
		if ( substr( $post->post_date, 0, 4 ) > 0 )
			$time = $post->post_date;
	}

	global $cud_post_id;
	$cud_post_id = $post_id;

	$uploads = wp_upload_dir($time);
	// A writable uploads dir will pass this test. Again, there's no point overriding this one.
	if ( $uploads['error'] === true ):
		xmpr("ERROR");
		xmpr($_FILES);
		xmpr($uploads['error']);
		return $uploads['error'];
	endif;

	$filename = wp_unique_filename( $uploads['path'], $basename);

	// Move the file to the uploads dir
	$new_file = $uploads['path'] . "/$filename";

// logr($new_file);

	copy($file, $new_file);

	// Set correct file permissions
	$stat = stat( dirname( $new_file ));
	$perms = $stat['mode'] & 0000666;
	@ chmod( $new_file, $perms );

	// Compute the URL
	$url = $uploads['url'] . "/$filename";

	$mime_type = get_mime_type($file);
	$title = $basename;
	$content = '';

	// use image exif/iptc data for title and caption defaults if possible
	require_once(ABSPATH.'/wp-admin/includes/image.php');
	require_once(ABSPATH.'/wp-admin/includes/media.php');
	if ( $image_meta = @wp_read_image_metadata($file) ) {
		if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
			$title = $image_meta['title'];
		if ( trim( $image_meta['caption'] ) )
			$content = $image_meta['caption'];
	}

	// Construct the attachment array
	$attachment = array_merge( array(
		'post_mime_type' => $mime_type,
		'guid' => $url,
		'post_parent' => $post_id,
		'post_title' => $title,
		'post_content' => $content,
		'post_data'	=>	$post->post_date
	), $post_data );

	// Save the data
	$id = wp_insert_attachment($attachment, $new_file, $post_id);

	if ( !is_wp_error($id) ) {
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $new_file ) );
	}

	return $id;

}

function mahi_wp_check_filetype_and_ext($args, $file, $filename, $mimes) {

	if ( ! $args['type'] ):

		$image_type = exif_imagetype($file);
		if ( $image_type ):
			$ext = image_type_to_extension($image_type, false);
			$type = image_type_to_mime_type($image_type);
			$proper_filename = preg_replace("#\.[^\.]*$#", '.'.$ext, $filename);
			$args = compact('ext', 'type', 'proper_filename' );
		else:
			if ( $ext = array_search(get_mime_type($file), get_allowed_mime_types())):
				$ext = preg_replace("#^([^\|]+).*#", "\\1", $ext);
				$type = get_mime_type($file);
				$proper_filename = $filename.'.'.$ext;
				$args = compact('ext', 'type', 'proper_filename' );
			endif;
		endif;

	endif;

	return $args;
}
add_filter( 'wp_check_filetype_and_ext', 'mahi_wp_check_filetype_and_ext', 10, 4 );


if ( !function_exists('image_type_to_extension') ) {

    function image_type_to_extension ($type, $dot = true) {
        $e = array ( 1 => 'gif', 'jpeg', 'png', 'swf', 'psd', 'bmp', 'tiff', 'tiff', 'jpc', 'jp2', 'jpf', 'jb2', 'swc', 'aiff', 'wbmp', 'xbm');

        // We are expecting an integer.
        $type = (int)$type;
        if (!$type) {
            trigger_error( '...come up with an error here...', E_USER_NOTICE );
            return null;
        }

        if ( !isset($e[$type]) ) {
            trigger_error( '...come up with an error here...', E_USER_NOTICE );
            return null;
        }

        return ($dot ? '.' : '') . $e[$type];
    }

}

?>