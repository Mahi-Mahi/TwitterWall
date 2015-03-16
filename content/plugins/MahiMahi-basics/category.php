<?php

function get_category_ancestor($category) {
	if ( ! isset($category->term_id) )
		$category = &get_category( $category );
	if ( $category->parent )
		return get_category_ancestor($category->parent);
	else
		return $category;
}


function get_category_ancestors($category) {
	if ( ! isset($category->term_id) )
		$category = &get_category( $category );
	if ( $category->parent )
		$ancestors = get_category_ancestors($category->parent);
	else
		$ancestors = array();
	$ancestors[] = $category;
	return $ancestors;
}


function in_sub_category( $category, $_post = null ) {
	if ( empty( $category ) )
		return false;

	if ( $_post ) {
		$_post = get_post( $_post );
	} else {
		$_post =& $GLOBALS['post'];
	}

	if ( !$_post )
		return false;

	$category = get_term_by('slug', $category, 'category');
	$childs = get_terms('category', array('fields' => 'ids', 'child_of' => $category->term_id, 'hide_empty' => true));
	if ( ! count($childs) )
		return $false;

	$r = is_object_in_term( $_post->ID, 'category', $childs );
	if ( is_wp_error( $r ) )
		return false;
	return $r;
}


?>