<?php

add_action('shutdown_action_hook','wpdb_mysql_close');
function wpdb_mysql_close() {
	@mysql_close();
}



remove_action('wp_head', 'wp_generator');


function flush_output() {
	flush();
	usleep(10000);
}
add_action('wp_head', 'flush_output', 9999);

?>