<?php

class TwitterWall extends WP_CLI_Command {

	function clean(){

		twitterwall_clean();

	}

	function get_posts(){

		twitterwall_get_posts();

	}



}

WP_CLI::add_command( 'twitterwall', 'TwitterWall' );
