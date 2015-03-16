<?php


function mahi_get_avatar($avatar_img, $author_id, $size, $default = null, $alt = null) {

	if ( ! is_numeric($author_id) )
		return $avatar_img;

	if ( ! $attachment_id = get_user_meta($author_id, 'avatar', true) )
		return $avatar_img;

	$avatar_img = get_thumbnail('avatar', null, array('attachment_id' => $attachment_id, 'width' => $size, 'height' => $size, 'alt' => $alt/*, 'default' => $default*/));

	return $avatar_img;
}

add_filter('get_avatar', 'mahi_get_avatar', 10, 4);

