<?php

function mahi_suppress_qtrans_excludeUntranslatedPosts($where, $wp_query) {
	global $wpdb;

	if ( is_admin() && ! isset($wp_query->query['force_admin']) )
		return $where;

	if ( isset($wp_query->query['show_untranslated_posts']) || $wp_query->query['post_type'] == 'attachment' ):
		$where = preg_replace("# AND ".$wpdb->posts."\.post_content LIKE '%<!\-\-\:\w+\-\->%'#mi", "", $where);
	endif;

	return $where;

}
add_filter('posts_where_request', 'mahi_suppress_qtrans_excludeUntranslatedPosts', 99, 2);
