<?php
/*
Plugin Name: Twitter Wall
*/

define('TWITTERWALL_DIR', WP_PLUGIN_DIR.'/twitter-wall');
define('TWITTERWALL_PATH', '/'.str_replace(ABSPATH, '', TWITTERWALL_DIR));
define('TWITTERWALL_URL', WP_PLUGIN_URL.'/twitter-wall');




function twitterwall_clean() {
	query_posts(array('post_type' => 'post', 'posts_per_page' => -1));
	while(have_posts()):
		the_post();
		xmpr(get_the_title());
		wp_delete_post(get_the_ID(), true);
	endwhile;
}


function twitterwall_get_posts() {

	xmpr("mazars_get_tweets");

	$social_type = mahi_get_or_create_term('social-type', 'twitter');


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

	$url = 'result_type=recent&entities=true&since_id=' . $last_id_str;

	// $response = $cb->search_tweets($url, true);
	$response = $cb->statuses_userTimeline('screen_name=MazarsFrance');

	// xmpr($response);

	foreach($response as $status):

		if ( ! $post_id = get_meta_post('origin_id', $status->id_str, true)):

			xmpr($status);

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
				'raw_data'			=>	serialize($status),
				'link'				=>	'https://twitter.com/'.$status->user->screen_name.'/status/'.$status->id_str.'/'
			);

			if ( isset($status->entities->media) && ! empty($status->entities->media) ):
				$media = $status->entities->media[0];
				$post_format = 'image';
				$image_url = $media->media_url;
				$metas['image_url'] 	=	$image_url;
			endif;

			$post_id = wp_insert_post($data);


			if ( is_wp_error($post_id) ):

				xmpr($post_id);

			else:

				wp_set_object_terms($post_id, $social_type->term_id, 'social-type', true);

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

