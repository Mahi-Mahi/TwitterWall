<?php

define('RELATED_TIMEOUT', 3600 * 5);

function mahi_get_related_on_taxonomies($post_id = null, $nb_related = 10, $taxonomies = null, $force = false) {
	global $post, $wpdb;


	if ( !$post_id )
		$post_id = $post->ID;

	if ( ! $post_id )
		return array();

	if ( ! $taxonomies ):
		$taxonomies = get_taxonomies();
	endif;

	if ( ! is_array($taxonomies) ):
		$taxonomies = array($taxonomies);
	endif;

	$key = implode('-', $taxonomies);


	$related_timeout = get_post_meta($post_id, 'mahi_related_on_taxonomies_'.$key.'_timeout', true);
	$related = get_post_meta($post_id, 'mahi_related_on_taxonomies_'.$key.'', true);


	if ( !$force && $related_timeout > time() && is_array($related) )
		return array_slice($related, 0, $nb_related);

	$terms = wp_get_object_terms($post_id, $taxonomies, array('fields' => 'tt_ids'));

	if ( empty($terms) )
		return array();

	$sql = " SELECT DISTINCT {$wpdb->term_relationships}.object_id, count(*) AS nb_match FROM {$wpdb->term_relationships} LEFT JOIN {$wpdb->posts} ON {$wpdb->term_relationships}.object_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_status = 'publish' AND {$wpdb->term_relationships}.term_taxonomy_id IN (".implode(',', $terms).") AND {$wpdb->term_relationships}.object_id <> ".$post_id." GROUP BY {$wpdb->term_relationships}.object_id ORDER BY nb_match DESC LIMIT 0,".$nb_related;
	$related = $wpdb->get_col($sql);

	delete_post_meta($post_id, 'mahi_related_on_taxonomies_'.$key.'');
	delete_post_meta($post_id, 'mahi_related_on_taxonomies_'.$key.'_timeout');
	foreach($related as $related_id)
		add_post_meta($post_id, 'mahi_related_on_taxonomies_'.$key.'', $related_id);

	update_post_meta($post_id, 'mahi_related_on_taxonomies_'.$key.'_timeout', time() + constant('RELATED_TIMEOUT'));

	return $related;

}

function mahi_clean_related_on_taxonomies($post_id) {
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id = ".$post_id." AND meta_key LIKE 'mahi_related_on_taxonomies_%' ");
}
add_action('wp_insert_post', 'mahi_clean_related_on_taxonomies');

