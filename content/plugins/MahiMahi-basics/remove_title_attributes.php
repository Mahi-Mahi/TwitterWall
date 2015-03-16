<?php

// REMOVE TITLE ATTRIBUTES (Page Lists, Category Lists, Archives Lists, Tag Clouds, Category Links)

if ( ! defined('wp_list_pages_remove_title_attributes')) :
	// Page Lists
	function wp_list_pages_remove_title_attributes($output) {
		$output = preg_replace('` title="(.+)"`', '', $output);
		return $output;
	}
	add_filter('wp_list_pages', 'wp_list_pages_remove_title_attributes');

	// Category lists
	function wp_list_categories_remove_title_attributes($output) {
		$output = preg_replace('` title="(.+)"`', '', $output);
		return $output;
	}
	add_filter('wp_list_categories', 'wp_list_categories_remove_title_attributes');

	// Archives
	function get_archives_link_remove_title_attributes($link_html) {
		$link_html = preg_replace("` title='(.+)'`", "", $link_html);
		return $link_html;
	}
	add_filter('get_archives_link', 'get_archives_link_remove_title_attributes');

	// Tag clouds
	function wp_tag_cloud_remove_title_attributes($return) {
		$return = preg_replace("` title='(.+)'`", "", $return);
		return $return;
	}
	add_filter('wp_tag_cloud', 'wp_tag_cloud_remove_title_attributes');

	// Post category links
	function the_category_remove_title_attributes($thelist) {
		$thelist = preg_replace('` title="(.+)"`', '', $thelist);
		return $thelist;
	}
//	add_filter('the_category', 'the_category_remove_title_attributes');
endif;

?>