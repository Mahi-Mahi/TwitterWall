<?php

function mahimahi_create_page($page, $parent_id = null, $replace = false) {

	xmpr("mahimahi_create_page({$page['title']}, $parent_id");

	if ( ! $page['slug'] )
		$full_slug = sanitize_title($page['title']);

	if ( $parent_id ):
		if ( is_array(get_post_ancestor($page->ID)))
			foreach(get_post_ancestor($page->ID) as $ancestor)
				$full_slug = get_post($ancestor_id)->post_name.'/'.$full_slug;
		$full_slug = get_post($parent_id)->post_name.'/'.$full_slug;
	endif;

	$page_object = get_page_by_path($full_slug);
	$post_id = $page_object->ID;

	if ( $page_object && $replace ):
		mahimahi_remove_page_recurse($page_object->ID);
	endif;

	if ( ! $page_object || $replace ) :
		xmpr($full_slug);
		$post_title = $page['title'];
		$post_name = $page['slug'];
		$post_type = 'page';
		$post_status = 'publish';
		$menu_order = $page['menu_order'];
		if ( $parent_id )
			$post_parent = $parent_id;
		$post_id = wp_insert_post(compact('post_title', 'post_name', 'post_status', 'post_type', 'post_parent', 'menu_order'));
		if ( is_array($page['metas']) )
			foreach($page['metas'] as $k => $v)
				update_post_meta($post_id, $k, $v);
	endif;

	if ( is_array($page['children']))
		foreach($page['children'] as $child):
			$child['menu_order'] = $idx++;
			mahimahi_create_page($child, $post_id, $replace);
		endforeach;

}

function mahimahi_remove_page_recurse($page_id) {
	$children = get_children(array('post_type' => array('page', 'attachment'), 'post_parent' => $page_id));
	if ( ! empty($children) )
		foreach($children as $child)
			mahimahi_remove_page_recurse($child->ID);
	wp_delete_post($page_id, true);
}

function get_post_ancestor($post) {
	$ancestors = get_post_ancestors($post);
	$ancestors = array_reverse($ancestors);
	return current($ancestors);
}

