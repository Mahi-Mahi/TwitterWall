<?php
class MahiCli extends WP_CLI_Command {

	function prod_to_local( $args, $assoc_args ) {
		list( $name ) = $args;

		if ( ! defined('PROD_BDD_EXPORT') )
			return;

		$cmds = array(
			"wget -O ".constant('DB_NAME').".sql.gz ".constant('PROD_BDD_EXPORT'),
			"gunzip -f ".constant('DB_NAME').".sql.gz",
			"mysql -u root ".constant('DB_NAME')." < ".constant('DB_NAME').".sql",
			"say 'done'"
		);

		foreach($cmds as $cmd):
			xmpr($cmd);
			exec($cmd);
		endforeach;

		// Print a success message
		WP_CLI::success( "MahiCliImport - import - " . $name );

	}

	function flush_rules(){
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
}

WP_CLI::add_command( 'mahi', 'MahiCli' );
