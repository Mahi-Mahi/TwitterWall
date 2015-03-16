<?php

function mahi_update_video_views($id = null, $meta = 'video_url' ) {

	if ( ! $id )
		$id = get_the_ID();
	$video_url = get_post_meta($id, $meta, true);

	if ( ! $video_url )
		return;

	if ( get_post_meta($id, 'video_views_timeout', true) < time() ):

		$total = 0;

		$video = mahi_oembed_get_data($video_url);

		switch($video->provider_name):
			case 'YouTube':

				preg_match("#/vi/([^/]+)/#", $video->thumbnail_url, $tmp);

				$video_ID = $tmp[1];

				$JSON = file_get_contents("https://gdata.youtube.com/feeds/api/videos/{$video_ID}?v=2&alt=json");
				$JSON_Data = json_decode($JSON);

				$total = $JSON_Data->{'entry'}->{'yt$statistics'}->{'viewCount'};

			break;
			case 'Dailymotion':

				preg_match("#/www.dailymotion.com/embed/video/([^\"]+)\"#", $video->html, $tmp);

				$video_ID = $tmp[1];

				$JSON = file_get_contents("https://api.dailymotion.com/video/{$video_ID}?fields=views_total");
				$JSON_Data = json_decode($JSON);

				$total = $JSON_Data->{'views_total'};

			break;
			default:
				logr($provider);
			break;
		endswitch;

	endif;

	delete_post_meta($id, 'video_views');
	add_post_meta($id, 'video_views', $total);

	global $post;
	$post_time = strtotime($post->post_date) - time();
	if ( $post_time < 3600 ):
		$timeout = 60 * 15;
	elseif ( $post_time < 3600 * 6):
		$timeout = 60 * 60;
	elseif ( $post_time < 3600 * 12):
		$timeout = 60 * 60 * 2;
	elseif ( $post_time < 3600 * 24):
		$timeout = 60 * 60 * 6;
	elseif ( $post_time < 3600 * 24 * 3):
		$timeout = 60 * 60 * 12;
	elseif ( $post_time < 3600 * 24 * 7):
		$timeout = 60 * 60 * 24 * 2;
	elseif ( $post_time < 3600 * 24 * 30):
		$timeout = 60 * 60 * 24 * 5;
	else:
		$timeout = 60 * 60 * 24 * 10;
	endif;

	delete_post_meta($id, 'video_views_timeout');
	add_post_meta($id, 'video_views_timeout', time() + $timeout);


}

function mahi_update_share_count($id = null) {

	if ( ! $id )
		$id = get_the_ID();

	if ( get_post_meta($id, 'share_count_timeout', true) < time() ):

		$total = 0;

		// facebook
		// http://api.ak.facebook.com/restserver.php?v=1.0&method=links.getStats&urls=
		$url = "http://api.ak.facebook.com/restserver.php?v=1.0&method=links.getStats&urls=".rawurlencode(get_permalink($id));
		$data = simplexml_load_string(wp_remote($url));
		$facebook_share = (int)$data->link_stat->share_count;

		$facebook_like_count = (int)$data->link_stat->like_count;

		$facebook_total_count = (int)$data->link_stat->total_count;

		// twitter
		// http://urls.api.twitter.com/1/urls/count.json?url=
		$url = "http://urls.api.twitter.com/1/urls/count.json?url=".rawurlencode(get_permalink($id));
		$data = json_decode(wp_remote($url));
		$twitter_share = $data->count;


		$url = "https://clients6.google.com/rpc";
		$args = array(
			'method'	=>	'POST',
			'headers'  => array('Content-Type' =>	'application/json'),
			'body'		=> json_encode(array(
				"method"		=>	"pos.plusones.get",
				"id"			=>	"p",
				"params"		=>	array(
					"nolog"		=>	true,
					"id"		=>	get_permalink($id),
					"source"	=>	"widget",
					"userId"	=>	"@viewer",
					"groupId"	=>	"@self"
				),
				"jsonrpc"		=>	"2.0",
				"key"			=>	"p",
				"apiVersion"	=>	"v1"
			))
		);
		$res = wp_remote($url, $args);
		$data = json_decode($res);
		$googleplus_share = $data->result->metadata->globalCounts->count;

		$total = $facebook_total_count + $twitter_share + $googleplus_share;

		delete_post_meta($id, 'share_count');
		add_post_meta($id, 'share_count', $total);

		delete_post_meta($id, 'facebook_like_count');
		add_post_meta($id, 'facebook_like_count', $facebook_like_count);

		delete_post_meta($id, 'facebook_total_count');
		add_post_meta($id, 'facebook_total_count', $facebook_total_count);

		delete_post_meta($id, 'twitter_share_count');
		add_post_meta($id, 'twitter_share_count', $twitter_share);

		delete_post_meta($id, 'googleplus_share_count');
		add_post_meta($id, 'googleplus_share_count', $googleplus_share);


	/*
		$total += $data-

		delete_post_meta($id, 'share_count');
		add_post_meta($id, 'share_count', );
	*/
		global $post;
		$post_time = strtotime($post->post_date) - time();
		if ( $post_time < 3600 ):
			$timeout = 60 * 15;
		elseif ( $post_time < 3600 * 6):
			$timeout = 60 * 60;
		elseif ( $post_time < 3600 * 12):
			$timeout = 60 * 60 * 2;
		elseif ( $post_time < 3600 * 24):
			$timeout = 60 * 60 * 6;
		elseif ( $post_time < 3600 * 24 * 3):
			$timeout = 60 * 60 * 12;
		elseif ( $post_time < 3600 * 24 * 7):
			$timeout = 60 * 60 * 24 * 2;
		elseif ( $post_time < 3600 * 24 * 30):
			$timeout = 60 * 60 * 24 * 5;
		else:
			$timeout = 60 * 60 * 24 * 10;
		endif;

		delete_post_meta($id, 'share_count_timeout');
		add_post_meta($id, 'share_count_timeout', time() + $timeout);


	endif;

}

