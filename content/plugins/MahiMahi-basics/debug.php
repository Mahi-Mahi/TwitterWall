<?php

add_action('init', 'timr_init');
global $timr;
function timr_init() {
	global $timr;
	$timr = microtime(true);
}
function timr($s) {
	global $timr;
	if ( is_local() )
		logr($s." : ".(microtime(true)-$timr));
}

function footer_debug() {
	global $wpdb, $current_user;

	if ( ! is_local() || ! $current_user->ID )
		return;

	/*
	echo "<xmp>";
	echo get_num_queries()." queries. ".timer_stop(1)." seconds.\n";
	print_r($wpdb->queries);
	echo "</xmp>";
	*/

	if (is_array($wpdb->queries)):
		array_qsort($wpdb->queries, '1');
		echo "<xmp>";
		echo "Slow Queries :\n";
		print_r(array_slice(array_reverse($wpdb->queries), 0, 200));
		echo "</xmp>";
	endif;

}
if ( defined('LOG_DB') && defined('FOOTER_DEBUG')):
	add_action('wp_footer', 'footer_debug');
	add_action('admin_footer', 'footer_debug');
endif;

function log_db_request($query) {
	if ( ! preg_match("#LIMIT#", $query)):
		error_log($query);
		if ( defined('LOG_DB_TRACE') )
			error_log(get_caller());
	endif;
	return $query;
}
if ( defined('LOG_DB') )
	add_filter('query', 'log_db_request');

function log_request($wp) {

	if ( ! is_local() && ! defined('LOG_REQUEST') )
		return;

	if ( is_admin() )
		return;

	logr("wp->request");
	logr($wp->request);
	logr("wp->matched_rule");
	logr($wp->matched_rule);
	logr("wp->matched_query");
	logr($wp->matched_query);

	/*
	logr("wp->query_vars");
	logr($wp->query_vars);
	*/
	logr($wp);

	if ( ! empty($_POST)):
		logr("_POST");
		logr($_POST);
	endif;
	$wp->query_vars['debug'] = true;

}
if ( defined('LOG_REQUEST') )
	add_action('parse_request', 'log_request', 9999);


function log_wp_redirect($location, $status) {
	logr("wp_redirect($location, $status)");
	logr(get_caller());
	return $location;
}
if ( defined('LOG_REDIRECT') ):
	add_filter('wp_redirect', 'log_wp_redirect', 9999, 2);
endif;

function local_wp_redirect($location, $status) {
	if ( preg_match("#\.localhost#", $_SERVER['HTTP_HOST']) )
		$location = preg_replace("#\.web-staging.com([^/])#", ".localhost/\\1", $location);
	return $location;
}
add_filter('wp_redirect', 'local_wp_redirect', 9999, 2);




add_action('wp_footer', 'mahidebug_live_reload_wp_footer');
add_action('admin_footer', 'mahidebug_live_reload_wp_footer');
function mahidebug_live_reload_wp_footer() {
	if ( defined('LIVERELOAD') && is_local() ):
		?>
		<script>document.write('<script src="http://' + (location.host || 'localhost').split(':')[0] + ':35729/livereload.js?snipver=1"></' + 'script>')</script>
		<?php
	endif;
}



add_filter('template_redirect', 'mahi_local_image_404_override', 2 );
function mahi_local_image_404_override() {
	global $wp_query;

	if ( $wp_query->is_404 ):
		if ( is_dev() ):
			if ( preg_match("#/wp-content/.*\.(gif|jpg|jpeg|png)$#", $_SERVER['REQUEST_URI']) ):
				header('Status: 404');
				exit();
			endif;
		endif;
	endif;
}

if ( defined('WP_SITEURL') ):
	add_filter( 'site_url', 'mahi_debug_site_url', 99, 4);
	add_filter( 'option_siteurl', 'mahi_debug_site_url', 99);
	add_filter( 'home_url', 'mahi_debug_home', 99, 4);
	add_filter( 'option_home', 'mahi_debug_home', 99);
endif;
function mahi_debug_site_url($url, $path = null, $scheme = null, $blog_id = null ) {
	$url = constant('WP_SITEURL').'/'.$path;
	return $url;
}
function mahi_debug_home($url, $path = null, $scheme = null, $blog_id = null ) {
	$url = constant('WP_HOME').'/'.$path;
	return $url;
}


