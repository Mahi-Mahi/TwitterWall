<?php

if ( file_exists($_SERVER['DOCUMENT_ROOT'].'/wp-config.php') )
	require_once( $_SERVER['DOCUMENT_ROOT'].'/wp-config.php' );
else
	require_once( $_SERVER['DOCUMENT_ROOT'].'/wp/wp-config.php' );

global $wpdb;

if ( class_exists('SitePress')):
	add_filter('icl_current_language', 'mahi_custom_types_autocomplete_icl_current_language');
	function mahi_custom_types_autocomplete_icl_current_language($current_language) {
		return $_GET['lang'] ? $_GET['lang'] : $current_language;
	}
	$show_lang = 1;
endif;


$my_query = unserialize(base64_decode($_GET['query']));

if ( ! is_array($my_query) )
	$my_query = array();

$default_query = array('ignore_sticky_posts' => true, 'post_type' => 'post', 'posts_per_page' => 20, 'force_admin' => true, 'like' => '%'.$_GET['term'].'%');

$my_query = array_merge($default_query, $my_query);

// $my_query['debug'] = true;

$my_query = apply_filters('mahi_post_reference_autocomplete_query', $my_query);

switch($my_query['post_type']):

	case 'custom':
		$res = apply_filters('mahi_post_reference_autocomplete', array(), $my_query);
	break;

	case 'user':
		//list users
		$global_users = array();

		foreach( array('administrator', 'author', 'editor') as $role):
			$args = array('role' => $role);
			$users = get_users($args);
			$global_users = array_merge($global_users,$users);
		endforeach;

		foreach($global_users as $user):
			$res[] = array('id' => $user->ID, 'value' => $user->user_login, 'label' => $user->user_login);
		endforeach;

	break;

	default:
		query_posts($my_query);

		$res = array();

		while ( have_posts() ) :
			the_post();

			$title = $post->post_title;

			// Full CMS
			if ( $my_query['show_lang'] ):
				$lang = get_post_meta($post->ID, 'lang', true);
				if ( $lang && $lang != 'fr' )
					$title .= ' - '.$lang;
			endif;

			// WPML
			if ($show_lang):
				$sql = "SELECT language_code FROM {$wpdb->prefix}icl_translations WHERE element_type = 'post_".$post->post_type."' AND element_id = ".$post->ID." ";
				$language = $wpdb->get_var($sql);
			endif;

			// blogo
			if ( $my_query['show_serie'] ):
				$series = get_the_terms( $post->ID, 'serie' );
				if ( ! empty($series) ):
					$serie = current($series);
					$title .= " - ".$serie->name;
				endif;
			endif;

			if ( isset($my_query['list_callback']) && function_exists($my_query['list_callback']) )
				$title = call_user_func($my_query['list_callback'], $post);

			$res[] = apply_filters('mahi_post_reference_autocomplete_result', array('id' => $post->ID, 'value' => $post->post_name, 'label' => $title . ( $show_lang ? ' - '. $language : '' ) ), $my_query );

		endwhile;

	break;

endswitch;

print json_encode($res);

exit();
