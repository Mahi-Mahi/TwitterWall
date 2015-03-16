<?php

require_once( ABSPATH . WPINC . '/class-oembed.php' );
class Mahi_oEmbed extends WP_oEmbed {
	
	function get_data( $url, $args = '' ) {
		$provider = false;

		if ( !isset($args['discover']) )
			$args['discover'] = true;

		foreach ( $this->providers as $matchmask => $data ) {
			list( $providerurl, $regex ) = $data;

			// Turn the asterisk-type provider URLs into regex
			if ( !$regex )
				$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';

			if ( preg_match( $matchmask, $url ) ) {
				$provider = str_replace( '{format}', 'json', $providerurl ); // JSON is easier to deal with than XML
				break;
			}
		}

		if ( !$provider && $args['discover'] )
			$provider = $this->discover( $url );

		if ( !$provider || false === $data = $this->fetch( $provider, $url, $args ) )
			return false;

		return $data;
	}
	
}


function &mahi_oembed_get_object() {
	static $mahi_oembed;

	if ( is_null($mahi_oembed) )
		$mahi_oembed = new Mahi_oEmbed();

	return $mahi_oembed;
}


?>