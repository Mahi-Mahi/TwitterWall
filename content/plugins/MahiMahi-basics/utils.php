<?php

function get_page_by_slug($page_slug, $output = OBJECT, $post_type = 'page' ) {
	global $wpdb;
	$page = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type= %s AND post_status = 'publish'", $page_slug, $post_type ) );
	 if ( $page )
		return get_post($page, $output);
	return null;
}

function mahi_is_plugin_active($plugin_name) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	if ( is_plugin_active($plugin_name) )
		return true;
	xmpr("Please activate plugin ".$plugin_name);
	return false;
}

function get_short_language() {
	if ( preg_match("#^(\w+)-#", get_bloginfo('language'), $tmp) )
		return $tmp[1];
	return '';
}

function mahi_clean_slugs($clean_slug) {
	global $wpdb;

	$clean_slug = preg_replace("#%\w{2}#", '-', $clean_slug);

	$bad = array( 'Š','Ž','š','ž','Ÿ','À','Á','Â','Ã','Ä','Å','Ç','È','É','Ê','Ë','Ì','Í','Î','Ï','Ñ','Ò','Ó','Ô','Õ','Ö','Ø','Ù','Ú','Û','Ü','Ý','à','á','â','ã','ä','å','ç','è','é','ê','ë','ì','í','î','ï','ñ','ò','ó','ô','õ','ö','ø','ù','ú','û','ü','ý','ÿ',
	'Þ','þ','Ð','ð','ß','Œ','œ','Æ','æ','µ','"',"'",'“','”',"\n","\r",'_',"‘","’");

	$good = array( 'S','Z','s','z','Y','A','A','A','A','A','A','C','E','E','E','E','I','I','I','I','N','O','O','O','O','O','O','U','U','U','U','Y','a','a','a','a','a','a','c','e','e','e','e','i','i','i','i','n','o','o','o','o','o','o','u','u','u','u','y','y',
	'TH','th','DH','dh','ss','OE','oe','AE','ae','u','','','','','','','-','','');

	// remove strange characters
	$clean_slug = str_replace($bad, $good, $clean_slug);
	$clean_slug = trim($clean_slug);

	// remove any duplicate whitespace, and ensure all characters are alphanumeric
	$bad_reg = array('/\s+/','/[^A-Za-z0-9\-]/');
	$good_reg = array('-','');
	$clean_slug = preg_replace($bad_reg, $good_reg, $clean_slug);

	$clean_slug = preg_replace('/&.+?;/', '', $clean_slug); // kill HTML entities

	$clean_slug = preg_replace("#^\-+#", '', $clean_slug);
	$clean_slug = preg_replace("#\-+$#", '', $clean_slug);
	$clean_slug = preg_replace("#\-+#", '-', $clean_slug);

	return $clean_slug;
}
//add_filter('sanitize_title', 'mahi_clean_slugs');


if ( ! function_exists('get_queried_object')):
	function get_queried_object(){
		global $wp_query;
		return $wp_query->get_queried_object();
	}
endif;

function delete_transient_like($pattern) {
	global $wpdb;
	$sql = "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_".$pattern."%' ";
	foreach($wpdb->get_col($sql) as $option_name)
		if ( preg_match("#^_transient_(.*)#", $option_name, $tmp) && ! preg_match("#^timeout#", $tmp[1]) )
			delete_transient($tmp[1]);
}

function get_current_template() {
	global $wp_query;
	if ( isset($wp_query->post) ):
		$template_name = str_replace('.php','',get_post_meta($wp_query->post->ID,'_wp_page_template',true));
		if ( $template_name ) return $template_name;
		else return false;
	else:
	return false;
	endif;
}

function append_post_meta($post_id, $meta, $value) {
	global $wpdb;

	$wpdb->query($wpdb->prepare("UPDATE {$wpdb->postmeta} SET meta_value = CONCAT(meta_value, %s) WHERE post_id = ".$post_id." AND meta_key = '".$meta."' ", $value));
}

function get_meta_term($meta, $value, $single = false) {
	global $wpdb;

	$sql = $wpdb->prepare(" SELECT term_id FROM wp_termmeta WHERE meta_key = %s AND meta_value = %s ", $meta, $value );
	if ( $single )
		return $wpdb->get_var($sql);
	else
		return $wpdb->get_col($sql);

}

function get_meta_comment($meta, $value, $single = false) {
	global $wpdb;

	$sql = $wpdb->prepare(" SELECT comment_id FROM {$wpdb->commentmeta} WHERE meta_key = %s AND meta_value = %s ", $meta, $value );
	if ( $single )
		return $wpdb->get_var($sql);
	else
		return $wpdb->get_col($sql);

}

function get_meta_user($meta, $value, $single = false) {
	global $wpdb;

	$sql = $wpdb->prepare(" SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s ", $meta, $value );
	if ( $single )
		return $wpdb->get_var($sql);
	else
		return $wpdb->get_col($sql);

}

function get_meta_post($meta, $value, $single = false) {
	global $wpdb;

	$sql = $wpdb->prepare(" SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s ", $meta, $value );
	if ( $single )
		return $wpdb->get_var($sql);
	else
		return $wpdb->get_col($sql);

}

function get_post_meta_sorted($post_id, $key, $single = false) {
	return get_metadata_sorted('post', $post_id, $key, $single);
}

function get_metadata_sorted($meta_type, $object_id, $meta_key = '', $single = false) {

	if ( !$meta_type )
		return false;

	if ( !$object_id = absint($object_id) )
		return false;

	if ( ! $table = _get_meta_table($meta_type) )
		return false;

	$column = esc_sql($meta_type . '_id');

	global $wpdb;

	$sql = $wpdb->prepare("SELECT meta_value FROM $table WHERE $column = $object_id AND meta_key = %s ORDER BY meta_id ASC", $meta_key);

	$values = $wpdb->get_col($sql);

	if ( isset($values) ) {
		if ( $single )
			return maybe_unserialize( $values[0] );
		else
			return array_map('maybe_unserialize', $values);
	}

	if ($single)
		return '';
	else
		return array();
}

function mahimahi_load_cached($url, $expire = 3600) {
	$cache_key = md5($url);
	if ( false === ( $content = get_transient($cache_key) ) ) :
		@mkdir(constant('WP_CONTENT_DIR')."/load_cached/", 0777, true);
		$filename = constant('WP_CONTENT_DIR')."/load_cached/".$cache_key;
		if ( file_exists($filename) && time() - filectime($filename) < $expire ) :
			// txtmtr($filename);
			$content = file_get_contents($filename);
		else:
			$content = wp_remote_get($url, array('timeout' => 30));
			if ( is_wp_error($content)):
				logr($content);
				logr($url);
				return;
			else:
				$content = utf8_encode($content['body']);
				file_put_contents($filename, $content);
			endif;
		endif;
		if ( strlen($content) < get_max_allowed_packet() )
			set_transient($cache_key, $content, $expire);
	endif;
	return $content;
}

function get_max_allowed_packet() {
	global $wpdb;
	$max_allowed_packet = get_option('max_allowed_packet');
	if ( ! $max_allowed_packet ):
		$max_allowed_packet = $wpdb->get_row("show variables like 'max_allowed_packet'");
		add_option('max_allowed_packet', $max_allowed_packet->Value);
	endif;
	return $max_allowed_packet;
}

if (!function_exists('str_getcsv')) :
function str_getcsv($input, $delimiter=',', $enclosure='"', $escape=null, $eol=null) {
  $temp=fopen("php://memory", "rw");
  fwrite($temp, $input);
  fseek($temp, 0);
  $r=fgetcsv($temp, 4096, $delimiter, $enclosure);
  fclose($temp);
  return $r;
}
endif;

function base64_add($args, $k , $v) {
	$args = unserialize(base64_decode($args));
	$args[$k] = $v;
	$args = base64_encode(serialize($args));
	return $args;
}


function base64_remove($args, $k) {
	$args = unserialize(base64_decode($args));
	unset($args[$k]);
	$args = base64_encode(serialize($args));
	return $args;
}

/*
mahi_add_query_args('http://google.com/', 'foo', 'bar')
=> 'http://google.com/foo/bar
mahi_add_query_args('http://google.com/foo/42', 'foo', 'bar')
=> 'http://google.com/foo/bar
mahi_add_query_args('http://google.com/baz/qux', 'foo', 'bar')
=> 'http://google.com/baz/qux/foo/bar
mahi_add_query_args('http://google.com/', array('foo' => 'bar', 'qux' => 'baz'))
=> 'http://google.com/qux/baz/foo/bar
*/
function mahi_add_query_path($url, $args, $v = null) {
	if ( ! $url )
		$url = $_SERVER['REQUEST_URI'];

	if ( ! is_array($args) ):
		$a[$args] = $v;
		$args = $a;
	endif;

	$components = parse_url($url);

	parse_str($components['query'], $query);

	foreach($args as $k => $v)
		if ( $v ):
			if ( preg_match("#/".$k."/([^/]+)#", $components['path']) )
				$components['path'] = preg_replace("#/".$k."/([^/]+)#", "/".$k."/".$v."/", $components['path']);
			else
				$components['path'] .= '/'.$k.'/'.$v.'/';
		else:
			$components['path'] = preg_replace("#/".$k."/([^/]+)#", '', $components['path']);
		endif;

	$components['query'] = http_build_query($query);

	$url = preg_replace("#\?$#", '', http_build_url($url, $components));
	$url = preg_replace("#([^:])//#", "\\1/", $url);

	return $url;
}
function mahi_remove_query_path($url, $args, $v = null) {
	if ( ! $url )
		$url = $_SERVER['REQUEST_URI'];

	if ( ! is_array($args) ):
		$a[$args] = $v;
		$args = $a;
	endif;

	$components = parse_url($url);

	parse_str($components['query'], $query);

	foreach($args as $k => $v)
		$components['path'] = preg_replace("#/".$k."/([^/]+)#", '', $components['path']);

	$components['query'] = http_build_query($query);

	$url = preg_replace("#\?$#", '', http_build_url($url, $components));
	$url = preg_replace("#([^:])//#", "\\1/", $url);

	return $url;
}
/*
mahi_add_query_args('http://google.com/', 'foo', 'bar')
=> 'http://google.com/?foo=bar
mahi_add_query_args('http://google.com/?foo=42', 'foo', 'bar')
=> 'http://google.com/?foo=bar
mahi_add_query_args('http://google.com/?baz=qux', 'foo', 'bar')
=> 'http://google.com/?baz=qux&foo=bar
mahi_add_query_args('http://google.com/', array('foo' => 'bar', 'qux' => 'baz'))
=> 'http://google.com/?qux=baz&foo=bar
*/
function mahi_add_query_args($url, $args, $v = null) {

	if ( ! $url )
		$url = $_SERVER['REQUEST_URI'];

	if ( ! is_array($args) ):
		$a[$args] = $v;
		$args = $a;
	endif;

	$components = parse_url($url);

	parse_str($components['query'], $query);

	foreach($args as $k => $v)
		$query[$k] = $v;

	$components['query'] = http_build_query($query);

	$url = http_build_url($url, $components);

	return $url;
}

/*
mahi_remove_query_args('http://google.com/?qux=baz&foo=bar', 'foo'))
=> 'http://google.com/?qux=baz
mahi_remove_query_args('http://google.com/?qux=baz&foo=bar', array('foo', 'qux'))
=> 'http://google.com/
*/
function mahi_remove_query_args($url, $args) {

	if ( ! $url )
		$url = $_SERVER['REQUEST_URI'];

	if ( ! is_array($args) )
		$args = array($args);

	$components = parse_url($url);

	parse_str($components['query'], $query);

	foreach($args as $k)
		unset($query[$k]);

	$components['query'] = http_build_query($query);

	$url = http_build_url($url, $components);

	return $url;}


if (!function_exists('http_build_url'))
{
	define('HTTP_URL_REPLACE', 1);				// Replace every part of the first URL when there's one of the second URL
	define('HTTP_URL_JOIN_PATH', 2);			// Join relative paths
	define('HTTP_URL_JOIN_QUERY', 4);			// Join query strings
	define('HTTP_URL_STRIP_USER', 8);			// Strip any user authentication information
	define('HTTP_URL_STRIP_PASS', 16);			// Strip any password authentication information
	define('HTTP_URL_STRIP_AUTH', 32);			// Strip any authentication information
	define('HTTP_URL_STRIP_PORT', 64);			// Strip explicit port numbers
	define('HTTP_URL_STRIP_PATH', 128);			// Strip complete path
	define('HTTP_URL_STRIP_QUERY', 256);		// Strip query string
	define('HTTP_URL_STRIP_FRAGMENT', 512);		// Strip any fragments (#identifier)
	define('HTTP_URL_STRIP_ALL', 1024);			// Strip anything but scheme and host

	// Build an URL
	// The parts of the second URL will be merged into the first according to the flags argument.
	//
	// @param	mixed			(Part(s) of) an URL in form of a string or associative array like parse_url() returns
	// @param	mixed			Same as the first argument
	// @param	int				A bitmask of binary or'ed HTTP_URL constants (Optional)HTTP_URL_REPLACE is the default
	// @param	array			If set, it will be filled with the parts of the composed url like parse_url() would return
	function http_build_url($url, $parts=array(), $flags=HTTP_URL_REPLACE, &$new_url=false)
	{
		$keys = array('user','pass','port','path','query','fragment');

		// HTTP_URL_STRIP_ALL becomes all the HTTP_URL_STRIP_Xs
		if ($flags & HTTP_URL_STRIP_ALL)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
			$flags |= HTTP_URL_STRIP_PORT;
			$flags |= HTTP_URL_STRIP_PATH;
			$flags |= HTTP_URL_STRIP_QUERY;
			$flags |= HTTP_URL_STRIP_FRAGMENT;
		}
		// HTTP_URL_STRIP_AUTH becomes HTTP_URL_STRIP_USER and HTTP_URL_STRIP_PASS
		else if ($flags & HTTP_URL_STRIP_AUTH)
		{
			$flags |= HTTP_URL_STRIP_USER;
			$flags |= HTTP_URL_STRIP_PASS;
		}

		// Parse the original URL
		$parse_url = parse_url($url);

		// Scheme and Host are always replaced
		if (isset($parts['scheme']))
			$parse_url['scheme'] = $parts['scheme'];
		if (isset($parts['host']))
			$parse_url['host'] = $parts['host'];

		// (If applicable) Replace the original URL with it's new parts
		if ($flags & HTTP_URL_REPLACE)
		{
			foreach ($keys as $key)
			{
				if (isset($parts[$key]))
					$parse_url[$key] = $parts[$key];
			}
		}
		else
		{
			// Join the original URL path with the new path
			if (isset($parts['path']) && ($flags & HTTP_URL_JOIN_PATH))
			{
				if (isset($parse_url['path']))
					$parse_url['path'] = rtrim(str_replace(basename($parse_url['path']), '', $parse_url['path']), '/') . '/' . ltrim($parts['path'], '/');
				else
					$parse_url['path'] = $parts['path'];
			}

			// Join the original query string with the new query string
			if (isset($parts['query']) && ($flags & HTTP_URL_JOIN_QUERY))
			{
				if (isset($parse_url['query']))
					$parse_url['query'] .= '&' . $parts['query'];
				else
					$parse_url['query'] = $parts['query'];
			}
		}

		// Strips all the applicable sections of the URL
		// Note: Scheme and Host are never stripped
		foreach ($keys as $key)
		{
			if ($flags & (int)constant('HTTP_URL_STRIP_' . strtoupper($key)))
				unset($parse_url[$key]);
		}


		$new_url = $parse_url;

		return
			 ((isset($parse_url['scheme'])) ? $parse_url['scheme'] . '://' : '')
			.((isset($parse_url['user'])) ? $parse_url['user'] . ((isset($parse_url['pass'])) ? ':' . $parse_url['pass'] : '') .'@' : '')
			.((isset($parse_url['host'])) ? $parse_url['host'] : '')
			.((isset($parse_url['port'])) ? ':' . $parse_url['port'] : '')
			.((isset($parse_url['path'])) ? $parse_url['path'] : '')
			.((isset($parse_url['query'])) ? '?' . $parse_url['query'] : '')
			.((isset($parse_url['fragment'])) ? '#' . $parse_url['fragment'] : '')
		;
	}
}

function mahi_sanitize_date($date) {
	$splitted = explode('/',$date);

	if (count($splitted) == 3)
		if (strlen($splitted[0]) == 2)
			return $splitted[2]."-".$splitted[1]."-".$splitted[0];
		else
			return $splitted[0]."-".$splitted[1]."-".$splitted[2];
	else
		return $date;
}

if ( ! function_exists('locale_date')):
function locale_date($format = 'Y-m-d H:i:s') {
	return date($format, time() + get_option('gmt_offset') * 3600);
}
endif;

if ( ! function_exists('empty_image')):
	function empty_image() {
		header('Cache-Control: no-cache');
		header('Content-type: image/jpeg');
		$img = imagecreatetruecolor(120, 20);
		$bg = imagecolorallocate ( $img, 255, 255, 255 );
		imagefilledrectangle($img,0,0,120,20,$bg);
		imagejpeg($img);
		imagedestroy($img);
	}
endif;

if ( ! function_exists('wp_slash')):
function wp_slash( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			if ( is_array( $v ) ) {
				$value[$k] = wp_slash( $v );
			} else {
				$value[$k] = addslashes( $v );
			}
		}
	} else {
		$value = addslashes( $value );
	}

	return $value;
}
endif;
