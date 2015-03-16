<?php

add_action('plugins_loaded', 'mahi_mobile_detect');

function mahi_mobile_detect() {
	if ( function_exists('w3_instance') ):
		$w3_pgcache = & w3_instance('W3_PgCache');
		$mobile_groups = $w3_pgcache->_get_mobile_group();
		if ( $mobile_groups == 'mobile' )
			define('IS_MOBILE', true);
	endif;
}


