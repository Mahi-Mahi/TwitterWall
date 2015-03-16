<?php
function mahi_bp_get_notifications_count_for_user( $user_id, $component = null, $status = 'is_new' ) {

	global $bp, $wpdb;

	$is_new = ( 'is_new' == $status ) ? ' AND is_new = 1 ' : '';

	$component = ( $component ) ? " AND component_name = '{$component}' " : '';

	return $wpdb->get_var( $wpdb->prepare( "SELECT count(*) FROM {$bp->core->table_name_notifications} WHERE user_id = %d {$is_new} {$component}", $user_id ) );
}
