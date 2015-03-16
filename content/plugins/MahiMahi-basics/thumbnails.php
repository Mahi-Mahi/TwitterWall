<?php

function mahi_thumbnails_init() {
	if ( defined('MAHI_THUMBNAILS') ):
		require_once(constant('MahiMahiBasics_DIR').'/thumbnails/thumbnails.php');
	else:
		if ( defined('MAHI_THUMBNAILS') ):
			require_once(constant('MahiMahiBasics_DIR').'/deprecated/thumbnails.php');
		endif;
	endif;
}
add_action('plugins_loaded', 'mahi_thumbnails_init');
