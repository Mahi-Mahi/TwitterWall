<?php

function is_post_type($type, $id = 0) {

	if ( ! is_array($type) )
		$type = array($type);

	return in_array(get_post_type($id), $type);

}
