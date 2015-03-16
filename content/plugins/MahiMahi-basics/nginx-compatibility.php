<?php

if ( defined('NGINX') ):

	if ( ! function_exists('wp_redirect') ):
	function wp_redirect($location, $status = 302) {
		$location = apply_filters('wp_redirect', $location, $status);

		if (empty($location)) {
			return false;
		}

		$status = apply_filters('wp_redirect_status', $status, $location);
		if ($status < 300 || $status > 399) {
			$status = 302;
		}

		$location = wp_sanitize_redirect($location);
		header('Location: ' . $location, true, $status);
	}
	endif;

	add_filter('got_rewrite', '__return_true', 999);
endif;
