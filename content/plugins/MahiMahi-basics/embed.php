<?php

function mahibasics_add_video_wmode_transparent($html) {

	$html = preg_replace("#(<iframe.*src=\"https?://www.youtube.com/[^\?]+)\"#U", "\\1?wmode=Opaque\"", $html);

	$html = preg_replace("#(<object[^>]+>)#U", "\\1<param name=\"wmode\" value=\"opaque\"></param>", $html);

	$html = preg_replace("#(<embed[^>]+)>#U", "\\1 wmode=\"opaque\" >", $html);

	return $html;
}
add_filter('the_content', 'mahibasics_add_video_wmode_transparent', 99);



function mahi_oembed_get_data( $url, $args = '' ) {
	require_once( 'embed_class.php' );
	$oembed = mahi_oembed_get_object();
	return $oembed->get_data( $url, $args );
}


function mahibasics_wp_embed_register_handler() {
	wp_embed_register_handler( 'bandcamp', '#https?://(.*)bandcamp.com/([^/]+)/(.*)#i', 'wp_embed_handler_bandcamp' );
	wp_embed_register_handler( 'deezer', '#https?://(.*)deezer.com/([^/]+)/(.*)#i', 'wp_embed_handler_deezer' );
}
// add_action('init', 'mahibasics_wp_embed_register_handler');

function wp_embed_handler_bandcamp( $matches, $attr, $url, $rawattr ) {

	if ( preg_match("#https?://bandcamp.com/EmbeddedPlayer#", $url)):
		$embed = '<iframe width="400" height="100" style="position: relative; display: block; width: '.$width.'px; height: '.$height.'px;" src="'.$url.'" allowtransparency="true" frameborder="0"></iframe>';
	else:

		$key = 'bandcamp_'.md5($url);
		if ( $embed = wp_cache_get($key, 'oembed') ):
		else:
			$content = file_get_contents($url);

			if ( preg_match("#<!-- (track|album) id (\d+) -->#", $content, $tmp) ):
				$src = "https://bandcamp.com/EmbeddedPlayer/";
				$src .= $tmp[1]."=".$tmp[2]."/";
				$src .= "size=large/bgcol=EEEEEE/linkcol=000000/transparent=true/artwork=small/";
				$src .= "tracklist=".($tmp[1]=='album'?'true':'false')."/";

				$height = $tmp[1]=='album'?'400':'120';

				$embed = '<iframe style="border: 0; width: 100%; height: '.$height.'px;" src="'.$src.'" seamless></iframe>';

				wp_cache_set($key, $embed, 'oembed', DAY_IN_SECONDS);

			endif;

		endif;
	endif;

	return apply_filters( 'embed_bandcamp', $embed, $matches, $attr, $url, $rawattr );
}


function wp_embed_handler_deezer( $matches, $attr, $url, $rawattr ) {

	// http://www.deezer.com/playlist/472563275
	// http://www.deezer.com/plugins/player?autoplay=false&playlist=true&width=700&height=240&cover=true&type=playlist&id=30595446&title=&app_id=undefined
	// http://www.deezer.com/album/6531275
	// http://www.deezer.com/plugins/player?autoplay=false&playlist=true&width=700&height=240&cover=true&type=album&id=119606&title=&app_id=undefined
	// http://www.deezer.com/track/3135556
	// http://www.deezer.com/plugins/player?autoplay=false&playlist=true&width=700&height=240&cover=true&type=tracks&id=3135556&title=&app_id=undefined

	$key = 'bandcamp_'.md5($url);
	if ( !is_local() && $embed = wp_cache_get($key, 'oembed') ):
	else:

		if ( preg_match("#https?://(www\.)?deezer.com/(track|album|playlist)/(\d+)#", $url, $tmp) ):

			$src = "http://www.deezer.com/plugins/player?autoplay=false&playlist=".($tmp[2]=='track'?'false':'true')."&width=700&height=240&cover=true&type=".$tmp[2]."&id=".$tmp[3]."&title=&app_id=undefined";

			$embed = '<iframe scrolling="no" frameborder="0" allowTransparency="true" src="'.$src.'" width="100%" height="240"></iframe>';

			wp_cache_set($key, $embed, 'oembed', DAY_IN_SECONDS);

		endif;

	endif;

	return apply_filters( 'embed_deezer', $embed, $matches, $attr, $url, $rawattr );
}


function mahi_embed_to_url($url) {
	$host = parse_url($url, PHP_URL_HOST);
	switch($host):
		case 'vimeo.com';
		case 'www.vimeo.com';
		case 'player.vimeo.com';
			if ( preg_match("#moogaloop\.swf(\?|3F)clip_id=(\d+)#", $url, $tmp) ):
				$url = "http://vimeo.com/".$tmp[2];
			endif;
		break;
		case 'dai.ly';
		case 'dailymotion.com';
		case 'www.dailymotion.com';
			if ( preg_match("#dai.ly/([\d\w]+)(_[^\&\?]+)?#", $url, $tmp) ):
				$url = "http://www.dailymotion.com/video/".$tmp[1];
			endif;
			if ( preg_match("#/+swf/(video/)?([\d\w]+(_[^\&\?]+)?)#", $url, $tmp) ):
				$url = "http://www.dailymotion.com/video/".$tmp[2];
			endif;
			if ( preg_match("#/+embed/(video/)?([\d\w]+)#", $url, $tmp) ):
				$url = "http://www.dailymotion.com/video/".$tmp[2];
			endif;
		break;
		case 'youtube.com';
		case 'www.youtube.com';
			if ( preg_match("#/+(v|embed)/([\d\w_\-]+)#", $url, $tmp) ):
				$url = "http://www.youtube.com/watch?v=".$tmp[2];
			endif;
		break;
		default:
			logr($match[1]);
		break;
	endswitch;
	return $url;
}


function mahi_replace_bandcamp_callback($match) {
	logr($match[1]);
	logr($match[2]);
	return $match[1];
}
function mahi_replace_bandcamp($post_id, $post) {
	global $wpdb;

	$post_content = preg_replace_callback("#<iframe[^>]+src=\"(http://bandcamp.com/EmbeddedPlayer[^\"]+)\"[^>]*></iframe>#", 'mahi_replace_bandcamp_callback', $post->post_content);

	$sql = "UPDATE {$wpdb->posts} SET post_content = '".$wpdb->escape($post_content)."' WHERE ID = ". $post->ID ;

	$wpdb->query($sql);

}
// add_action('wp_insert_post', 'mahi_replace_bandcamp', 99, 2);



function mahi_fix_omebed_dailymotion_vevo($src) {
	return preg_replace("#iframe\|http#", "http", $src);
}
add_filter('the_content', 'mahi_fix_omebed_dailymotion_vevo', 9999);






