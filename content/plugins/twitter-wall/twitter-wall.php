<?php
/*
Plugin Name: Twitter Wall
*/

define('TWITTERWALL_DIR', WP_PLUGIN_DIR.'/twitter-wall');
define('TWITTERWALL_PATH', '/'.str_replace(ABSPATH, '', TWITTERWALL_DIR));
define('TWITTERWALL_URL', WP_PLUGIN_URL.'/twitter-wall');



if ( ! is_local() ):
	define( 'ACF_LITE', true );
endif;

require_once(TWITTERWALL_DIR.'/acf.php');
require_once(TWITTERWALL_DIR.'/custom-types/banned-account.php');
require_once(TWITTERWALL_DIR.'/custom-types/banned-word.php');




if( function_exists('acf_add_options_sub_page') ) {
    // acf_add_options_sub_page( 'Hashtag' );
}

if( function_exists('acf_add_options_sub_page') ) {
    acf_add_options_sub_page(array(
        'title' => 'Hashtag',
        'parent' => 'index.php',
        'capability' => 'manage_options'
    ));
}








function twitterwall_cli_init() {
	if ( defined('WP_CLI') && WP_CLI ):
		require_once(TWITTERWALL_DIR.'/wp-cli.php');
	endif;
}
add_action( 'plugins_loaded', 'twitterwall_cli_init' );




function twitterwall_clean() {
	query_posts(array('post_type' => 'post', 'posts_per_page' => -1));
	while(have_posts()):
		the_post();
		xmpr(get_the_title());
		wp_delete_post(get_the_ID(), true);
	endwhile;
}


function twitterwall_get_posts() {

	logr("twitterwall_get_posts");

	$expire = get_option('refresh_tweet_timeout');

	logr("expire : ".date("Y-m-d H:i:s", $expire));

	if ( $expire && $expire > strtotime("-10 seconds") ):
		logr("slow down please");
		return;
	endif;

	logr("do refresh tweets");

	update_option('refresh_tweet_timeout', time());

	$hashtag = get_field('hashtag', 'option');

	if ( ! $hashtag )
		return;

	require_once( TWITTERWALL_DIR . '/lib/codebird/codebird.php');
	\Codebird\Codebird::setConsumerKey(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET); // static, see 'Using multiple Codebird instances'
	$cb = \Codebird\Codebird::getInstance();

	$cb->setToken(TWITTER_OAUTH_TOKEN, TWITTER_OAUTH_TOKEN_SECRET);

	if ( $bearer = get_option('TWITTER_BEARER') ):
		\Codebird\Codebird::setBearerToken($bearer);
	else:
		$reply = $cb->oauth2_token();
		$bearer_token = $reply->access_token;
		add_option('TWITTER_BEARER', $bearer_token);
	endif;

	$url = 'q='.urlencode($hashtag).'&lang=fr&result_type=recent&count=100';

	// xmpr($url);
	$response = $cb->search_tweets($url, true);

	// $response = $cb->statuses_userTimeline('screen_name=MazarsFrance');

	// xmpr($response);

	foreach($response->statuses as $status):

		if ( ! $post_id = get_meta_post('origin_id', $status->id_str, true)):

			// xmpr($status);

			// if ( preg_match("#RT#", $status->text) )
			// 	continue;

			$data = array(
				'post_title'	=>	$status->text,
				'post_content'	=>	$status->text,
				'post_date'		=>	date('Y-m-d H:i:s', strtotime($status->created_at)),
				'post_status'	=>	'publish',
				'post_type'		=>	'post'
			);

			$metas = array(
				'origin_id'			=>	$status->id_str,
				'author'			=>	$status->user->screen_name,
				'raw_data'			=>	serialize($status),
				'url'				=>	'https://twitter.com/'.$status->user->screen_name.'/status/'.$status->id_str.'/'
			);

			if ( isset($status->entities->media) && ! empty($status->entities->media) ):
				$media = $status->entities->media[0];
				$post_format = 'image';
				$image_url = $media->media_url;
				$metas['image_url'] 	=	$image_url;
			endif;

			xmpr($data);

			$post_id = wp_insert_post($data);

				xmpr($post_id);


			if ( is_wp_error($post_id) ):

				xmpr($post_id);

			else:

				if ( $metas['image_url'] ):
					$metas['image_id'] = mahibasics_media_sideload_image($metas['image_url'], $post_id, $data['post_title']);
					wp_update_post(array(
						'ID'			=>	$metas['image_id'],
						'post_parent'	=>	$post_id,
					));
					set_post_thumbnail( $post_id, $metas['image_id'] );
				endif;

				foreach($metas as $k => $v)
					add_post_meta($post_id, $k, $v);

			endif;

		endif;

	endforeach;

}

