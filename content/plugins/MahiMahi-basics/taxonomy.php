<?php


function mahi_get_or_create_term($taxonomy, $title) {

	$term = get_term_by('slug', strtolower(sanitize_user($title)), $taxonomy);

	if ( $term = get_term_by('slug', strtolower(sanitize_user($title)), $taxonomy)) :
		// exists
	else:
		// create

		$term = wp_insert_term($title, $taxonomy);
		if ( is_wp_error($term) ):
			if ( isset($term->errors['term_exists']) ):
				$term = array('term_id' => $term->error_data['term_exists']);
			else:
				xmpr($term);
				xmpr($title);
				xmpr($taxonomy);
				xmpr(get_caller());
				die(__FILE__.__LINE__);
			endif;
		endif;
	endif;

	if ( ! is_array($term) )
		$term = array('term_id' => $term->term_id);

	return get_term($term['term_id'], $taxonomy);

}


// FIX for simple term meta : remove metas on term deletion
function mahi_delete_term_simple_term_meta($term_id, $tt_id, $taxonomy) {
	global $wpdb;
	if ( function_exists('is_plugin_active') && is_plugin_active('simple-term-meta/simple-term-meta.php') )
		$wpdb->query("DELETE FROM wp_termmeta WHERE term_id = ".$term_id);
}
add_action('delete_term', 'mahi_delete_term_simple_term_meta', 10, 3);


global $wp_version;
if ( ! function_exists('has_term') && version_compare($wp_version, '3.1', '<') ):
function has_term( $term = '', $taxonomy = '', $post = null ) {
	$post = get_post($post);

	if ( !$post )
		return false;

	$r = is_object_in_term( $post->ID, $taxonomy, $term );
	if ( is_wp_error( $r ) )
		return false;

	return $r;
}
endif;

function get_term_object($term_id, $post_type = 'post') {
	global $wpdb;

	$sql = "SELECT TR.object_id FROM wp_term_taxonomy TT LEFT JOIN wp_term_relationships TR ON TT.term_taxonomy_id = TR.term_taxonomy_id LEFT JOIN wp_posts P ON TR.object_id = P.ID WHERE TT.term_id = $term_id AND P.post_type = '$post_type' ";

	$object_id = $wpdb->get_var($sql);

	return $object_id;
}


function basics_terms_clauses($pieces, $taxonomies, $args) {

	if ( isset($args['only_empty']) ):

		$pieces['where'] .= " AND tt.count = 0 ";

	endif;

	if ( isset($args['meta_key']) && isset($args['meta_value']) ):
		$args['meta_query'][] = array('key' => $args['meta_key'], 'value' => $args['meta_value']);
	endif;

	if ( isset($args['meta_query']) ):
		foreach($args['meta_query'] as $i => $query):
			$pieces['join'] .= " INNER JOIN wp_termmeta AS TM_".$query['key']." ON t.term_id = TM_".$query['key'].".term_id  ";
			if ( $query['regex'] )
				$pieces['where'] .= " AND ( TM_".$query['key'].".meta_key = '".$query['key']."' AND TM_".$query['key'].".meta_value REGEXP '".$query['value']."' ) ";
			else
				$pieces['where'] .= " AND ( TM_".$query['key'].".meta_key = '".$query['key']."' AND TM_".$query['key'].".meta_value = '".$query['value']."' ) ";
		endforeach;
	endif;

	if ( isset($args['orderby_meta_num']) ):
		if ( ! preg_match("# AS TM_".$args['orderby_meta_num']."#", $pieces['join']) ):
			$pieces['join'] .= " INNER JOIN wp_termmeta AS TM_".$args['orderby_meta_num']." ON t.term_id = TM_".$args['orderby_meta_num'].".term_id  ";
			$pieces['where'] .= " AND ( TM_".$args['orderby_meta_num'].".meta_key = '".$args['orderby_meta_num']."' ) ";
			$pieces['orderby'] = " ORDER BY TM_".$args['orderby_meta_num'].".meta_value+0 ";
		endif;
	endif;


	return $pieces;
}
add_filter('terms_clauses', 'basics_terms_clauses', 10, 3);


function basics_terms_clauses_debug($pieces, $taxonomies, $args) {
	global $wpdb;

	foreach ( $pieces as $k => $v )
		$$k = $v;

	$query = "SELECT $fields FROM $wpdb->terms AS t $join WHERE $where $orderby $order $limits";

	if ( isset($args['debug']) ):
		logr($query);
		logr($args);
	endif;

	if ( ( isset($args['debug']) && $args['debug'] === 'xmpr' ) ):
		xmpr($query);
		xmpr($args);
	endif;

	return $pieces;
}
add_filter('terms_clauses', 'basics_terms_clauses_debug', 99, 3);


// TODO : add cache
function get_terms_on_post_type($taxonomy, $post_type, $args = array()) {
	global $wpdb;

	$sql = " SELECT TR.term_taxonomy_id FROM {$wpdb->term_relationships} TR LEFT JOIN {$wpdb->posts} P ON TR.object_id = P.ID WHERE P.post_type = '".$post_type."' AND TR.term_taxonomy_id IN (SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = '".$taxonomy."') GROUP BY TR.term_taxonomy_id ";

	$term_taxonomy_ids = $wpdb->get_col($sql);

	if ( $args['like'] )
		$where .= " AND T.name LIKE '".$args['like']."'";

	if ( $args['orderby'] )
		$orderby = $args['orderby'];
	else
		$orderby = "T.name ASC";

	$sql = " SELECT T.* FROM {$wpdb->terms} T LEFT JOIN {$wpdb->term_taxonomy} TT ON T.term_id = TT.term_id WHERE TT.term_taxonomy_id IN (".implode(',', $term_taxonomy_ids).") ".$where." ORDER BY ".$orderby;

	$terms = $wpdb->get_results($sql);

	return $terms;
}

// patch "Term Menu Order" plugin on WP 3.2
function mahibasics_get_terms_orderby($orderby,$args) {
	if($args['orderby'] == 'menu_order')
		return "menu_order";
	else
		return $orderby;
}
add_filter('get_terms_orderby', 'mahibasics_get_terms_orderby', 10, 2);

/*
function mahi_attachment_fields_to_edit($form_fields, $post) {

	require_once(ABSPATH . 'wp-admin/includes/meta-boxes.php');
	wp_enqueue_script( 'post' );

	logr("mahi_attachment_fields_to_edit");

	foreach($form_fields as $slug => $form_field):
		if ( $form_field['taxonomy'] && $taxonomy = get_taxonomy($slug)):

//			logr(array_keys($form_field));
			logr($taxonomy);

			ob_start();

			post_tags_meta_box($post, array(
					'taxonomy' => $taxonomy->slug
				));

			$html = ob_get_clean();

			logr($html);

			$form_fields[$slug]['input'] = 'html';
			$form_fields[$slug]['html'] = $html;
		endif;
	endforeach;

	return $form_fields;
}
add_filter("attachment_fields_to_edit", "mahi_attachment_fields_to_edit", 99, 2);

*/

