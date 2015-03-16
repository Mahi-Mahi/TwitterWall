<?php

// do_action_ref_array( 'pre_user_query', array( &$this ) );

function mahibasics_pre_user_query($user_query) {

	if ($user_query->query_vars['orderby'] == 'rand' )
		$user_query->query_orderby = 'ORDER BY RAND()';


	global $wpdb;
	if ( isset( $query->query_vars['orderby'] ) && 'meta_key' == $query->query_vars['orderby'] )
		$query->query_orderby = str_replace( 'user_login', "$wpdb->usermeta.meta_value", $query->query_orderby );

}
add_action('pre_user_query', 'mahibasics_pre_user_query');

function get_current_user_role() {
	global $wp_roles;
	$current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$role = array_shift($roles);
	return $wp_roles->role_names[$role];
}

function mahi_remove_user_contactmethods($contact_methods) {
	return array();
}
add_filter( 'user_contactmethods', 'mahi_remove_user_contactmethods', 1 );


