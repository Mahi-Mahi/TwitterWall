<?php

add_filter('acf/settings/save_json', 'napoleon_acf_save_json');
add_filter('acf/settings/load_json', 'napoleon_acf_load_json');


function napoleon_acf_load_json($paths) {
	$paths = array(
		TWITTERWALL_DIR.'/acf-json'
	);

	return $paths;
}

function napoleon_acf_save_json($paths) {

	$paths = TWITTERWALL_DIR.'/acf-json';

	return $paths;
}

