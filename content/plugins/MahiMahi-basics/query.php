<?php

function basics_posts_geo_near_distance($origin) {
	return "		6371 * 2 *
			ASIN(
				SQRT(
					POWER(
									SIN(
												(CAST('".number_format($origin->lat, 6, '.', '')."' AS DECIMAL(18,15)) - abs(CAST(geo_meta_lat.meta_value AS DECIMAL(18,15)))) * pi()/180 / 2
											)
									, 2
								)
					+
						COS(CAST('".number_format($origin->lat, 6, '.', '')."' AS DECIMAL(18,15)) * pi()/180 )
						*
						COS( abs(CAST(geo_meta_lat.meta_value AS DECIMAL(18,15))) * pi()/180)
						*
						POWER(
									SIN(
												(CAST('".number_format($origin->lng, 6, '.', '')."' AS DECIMAL(18,15)) - CAST(geo_meta_lng.meta_value AS DECIMAL(18,15))) * pi()/180 / 2
											)
									, 2
								)
				)
			)
		 ";
}

function basics_posts_fields_geo_near($fields, $wp_query) {
	global $wpdb;

	if ( is_admin() && ! isset($wp_query->query['force_admin']) )
		return $fields;

	if ( isset($wp_query->query['geo_nearest']) || isset($wp_query->query['geo_near']) ):

		if ( isset($wp_query->query['geo_nearest']) )
			$origin = $wp_query->query['geo_nearest'];

		if ( isset($wp_query->query['geo_near']) )
			$origin = $wp_query->query['geo_near'];

		// http://www.scribd.com/doc/2569355/Geo-Distance-Search-with-MySQL
		$fields .= ', '.basics_posts_geo_near_distance($origin).' AS geo_near_distance ';
	endif;
	return $fields;
}
add_filter('posts_fields', 'basics_posts_fields_geo_near', 10, 2);

function basics_posts_where_geo_near($where, $wp_query) {
	global $wpdb;

	if ( is_admin() && ! isset($wp_query->query['force_admin']) )
		return $where;

	if ( isset($wp_query->query['geo_nearest']) || isset($wp_query->query['geo_near']) ):
		$where .= " AND geo_meta_lat.meta_key = 'address_lat' ";
		$where .= " AND geo_meta_lng.meta_key = 'address_lng' ";
	endif;

	if ( isset($wp_query->query['geo_near_distance']) ):
		if ( isset($wp_query->query['geo_nearest']) )
			$origin = $wp_query->query['geo_nearest'];

		if ( isset($wp_query->query['geo_near']) )
			$origin = $wp_query->query['geo_near'];
		$where .= " AND ".basics_posts_geo_near_distance($origin)." < CAST('".$wp_query->query['geo_near_distance']."' AS DECIMAL(10,4) )";
	endif;


	return $where;
}
add_filter('posts_where', 'basics_posts_where_geo_near', 10, 2);

function basics_posts_join_geo_near($join, $wp_query) {
	global $wpdb;

	if ( is_admin() && ! isset($wp_query->query['force_admin']) )
		return $join;

	if ( isset($wp_query->query['geo_nearest']) || isset($wp_query->query['geo_near']) ):
		$join .= " LEFT JOIN {$wpdb->postmeta} geo_meta_lat ON {$wpdb->posts}.ID = geo_meta_lat.post_id ";
		$join .= " LEFT JOIN {$wpdb->postmeta} geo_meta_lng ON {$wpdb->posts}.ID = geo_meta_lng.post_id ";
	endif;

	return $join;
}
add_filter('posts_join', 'basics_posts_join_geo_near', 10, 2);

function basics_posts_orderby_geo_near($orderby, $wp_query) {
	global $wpdb;

	if ( is_admin() && ! isset($wp_query->query['force_admin']) )
		return $orderby;

	if ( isset($wp_query->query['geo_nearest']) || isset($wp_query->query['geo_near']) ):

		$orderby = " geo_near_distance ASC ";

	endif;

	return $orderby;
}
add_filter('posts_orderby', 'basics_posts_orderby_geo_near', 10, 2);

function basics_posts_orderby_meta_date($orderby, $wp_query) {
	global $wpdb;

	if ( is_admin() && ! isset($wp_query->query['force_admin']) )
		return $orderby;

	if ( isset($wp_query->query['meta_date_orderby'])):

		$orderby = $wp_query->query['meta_date_orderby'];

	endif;

	return $orderby;
}
add_filter('posts_orderby', 'basics_posts_orderby_meta_date', 10, 2);


function mahi_add_quotes($s){ return "'".$s."'";}

function basics_posts_where_post_names($where, $wp_query) {

	if ( isset($wp_query->query['post_names']) && $wp_query->query['post_names'] ):
		if ( ! is_array($wp_query->query['post_names']) )
			$wp_query->query['post_names'] = array($wp_query->query['post_names']);
		$where .= " AND post_name IN (".implode(',', array_map('mahi_add_quotes', $wp_query->query['post_names'])).") ";
	endif;
	return $where;
}
add_filter('posts_where', 'basics_posts_where_post_names', 10, 2);


function basics_posts_where_postmeta_like($where, $wp_query) {

	if ( isset($wp_query->query['meta_key_like']) && isset($wp_query->query['meta_value_like'])):
		$wp_query->query['meta_like'] = array('key' => $wp_query->query['meta_key_like'], 'values' => $wp_query->query['meta_value_like']);
		if ( isset($wp_query->query['meta_key_like_regexp']) ){
				$wp_query->query['meta_like']['key_regexp'] = $wp_query->query['meta_key_like_regexp'];
				unset($wp_query->query['meta_key_like_regexp']);
		}
		unset($wp_query->query['meta_key_like']);
		unset($wp_query->query['meta_value_like']);
	endif;
	return $where;
}
add_filter('posts_where', 'basics_posts_where_postmeta_like', 10, 2);


function basics_posts_where_postmeta_not_like($where, $wp_query) {

	if ( isset($wp_query->query['meta_key_not_like']) && isset($wp_query->query['meta_value_not_like'])):
		$where .= " AND meta_like.meta_key = '".$wp_query->query['meta_key_not_like']."' AND ";
		if(is_array($wp_query->query['meta_value_not_like'])):
			$i = 0;
			$where .= " (";
			foreach($wp_query->query['meta_value_not_like'] as $meta_value):
				if($i!=0)
					$where .= " AND ";
				$where .= " meta_like.meta_value NOT LIKE '".$meta_value."' ";
				$i++;
			endforeach;
			$where .= " )";
		else:
			$where .= " meta_like.meta_value NOT LIKE '".$wp_query->query['meta_value_not_like']."' ";
		endif;
	endif;
	return $where;
}
add_filter('posts_where', 'basics_posts_where_postmeta_not_like', 10, 2);

function basics_posts_where_meta_like($where, $wp_query) {
	if(isset($wp_query->query['meta_like']) && is_array($wp_query->query['meta_like'])):
		$conditions = array();
		$contitions_operator = 'OR';
		if(isset($wp_query->query['meta_like']['key']))
			$wp_query->query['meta_like'] = array($wp_query->query['meta_like']);
		foreach($wp_query->query['meta_like'] as $k => $meta_like):

			if( $k === 'operator' ){
				$conditions_operator = $meta_like;
			}
			elseif(isset($meta_like['key']) && isset($meta_like['values']) ){
				if ( isset($meta_like['key_regexp']) )
					$operator = "REGEXP";
				else
					$operator = "LIKE";

				if( ! is_array($meta_like['values']))
					$meta_like['values'] = array($meta_like['values']);

				$condition_values = array();
				foreach($meta_like['values'] as $pattern)
					if ( $pattern ):
						$pattern .= (strstr($pattern, '%')?'':'%');
						$condition_values[] = " meta_like_".str_replace('-',	"_",	$meta_like['key']).".meta_value ".$operator." '".$pattern."' ";
					endif;

				if ( $condition_values )
					$conditions[] = " ( meta_like_".str_replace('-',	"_",	$meta_like['key']).".meta_key = '".$meta_like['key']."' AND ( ".implode(' OR ', $condition_values).' ) ) ';
			}

		endforeach;
		if ( $conditions )
			$where .= " AND ( " . implode($contitions_operator, $conditions) . " ) ";
	endif;
	return $where;
}
add_filter('posts_where', 'basics_posts_where_meta_like', 10, 2);

function basics_posts_join_postmeta_like($join, $wp_query) {
	global $wpdb;

	if(isset($wp_query->query['meta_like'])):
		if(isset($wp_query->query['meta_like']['key']))
			$wp_query->query['meta_like'] = array($wp_query->query['meta_like']);
			foreach($wp_query->query['meta_like'] as $k => $meta_like):
				if( is_array($meta_like) ){
					$keys[] = str_replace('-',	"_",	$meta_like['key']);
				}
			endforeach;
			foreach($keys as $key)
				$join .= " LEFT JOIN {$wpdb->postmeta} meta_like_".$key." ON {$wpdb->posts}.ID = meta_like_".$key.".post_id ";
	endif;

	if (isset($wp_query->query['meta_key_not_like']) && isset($wp_query->query['meta_value_not_like']) ):
					$join .= " LEFT JOIN {$wpdb->postmeta} meta_like ON {$wpdb->posts}.ID = meta_like.post_id ";
	endif;

	return $join;
}
add_filter('posts_join', 'basics_posts_join_postmeta_like', 10, 2);

function basics_posts_where_not_post_type($where, $wp_query) {
	/*
	'revision'
	'revision, attachment'
	array('revision', 'attachment')
	*/
	if ( isset($wp_query->query['not_post_type']) ):

		$posts_type = $wp_query->query['not_post_type'];

		if(is_string($posts_type))
			$posts_type = explode(',', $posts_type);

		if(is_array($posts_type)){
			$i = 0;
			while(isset($posts_type[$i])):
				$posts_type[$i] = "'".$posts_type[$i]."'";
				$i++;
			endwhile;
			$posts_type = implode(' AND post_type <> ',$posts_type);
			$posts_type = ' AND post_type <> '.$posts_type;// on fait ça pour le première élément qui n'a pas été modifié
			$where = $where.$posts_type;
		}
	endif;

	return $where;
}
add_filter('posts_where', 'basics_posts_where_not_post_type', 99, 2);

function basics_posts_request($request, $wp_query) {

	if ( isset($wp_query->query['debug']) or isset($_GET['DEBUG']) ):
		logr($wp_query->query);
		logr($request);
	endif;

	if ( ( isset($wp_query->query['debug']) && $wp_query->query['debug'] === 'xmpr' ) ):
		xmpr($wp_query->query);
		xmpr($request);
	endif;

	return $request;
}
add_filter('posts_request', 'basics_posts_request', 99, 2);


function basics_posts_where($where, $wp_query) {
	global $wpdb;

	if ( is_admin() && ! isset($wp_query->query['force_admin']) )
		return $where;

	$custom_where = "";

	if ( isset($wp_query->query['is_meta']) ):
		if ( ! is_array($wp_query->query['is_meta']) )
			$wp_query->query['is_meta'] = array($wp_query->query['is_meta']);

		foreach($wp_query->query['is_meta'] as $is_meta)
			$custom_where .= " AND EXISTS ( SELECT DISTINCT PM.meta_key FROM {$wpdb->postmeta} PM WHERE {$wpdb->posts}.ID = PM.meta_value AND meta_key = '".$is_meta."' )";

	endif;

	if ( isset($wp_query->query['has_meta']) ):
		if ( ! is_array($wp_query->query['has_meta']) )
			$wp_query->query['has_meta'] = array($wp_query->query['has_meta']);

		foreach($wp_query->query['has_meta'] as $has_meta)
			$custom_where .= " AND EXISTS ( SELECT DISTINCT PM.meta_key FROM {$wpdb->postmeta} PM WHERE {$wpdb->posts}.ID = PM.post_id AND meta_key = '".$has_meta."' )";

	endif;

	if ( isset($wp_query->query['has_not_meta']) ):
		if ( ! is_array($wp_query->query['has_not_meta']) )
			$wp_query->query['has_not_meta'] = array($wp_query->query['has_not_meta']);

		foreach($wp_query->query['has_not_meta'] as $has_not_meta)
			$custom_where .= " AND NOT EXISTS ( SELECT DISTINCT PM.meta_key FROM {$wpdb->postmeta} PM WHERE {$wpdb->posts}.ID = PM.post_id AND meta_key = '".$has_not_meta."' )";
	endif;

	if ( isset($wp_query->query['has_thumbnail']) ):
		if ( ! is_array($wp_query->query['has_thumbnail']) )
			$wp_query->query['has_thumbnail'] = array($wp_query->query['has_thumbnail']);
		foreach($wp_query->query['has_thumbnail'] as $has_meta)
			$custom_where .= " AND EXISTS ( SELECT DISTINCT PM.meta_key FROM {$wpdb->postmeta} PM WHERE {$wpdb->posts}.ID = PM.post_id AND meta_key = '_thumbnail_id' )";
	endif;

	if ( isset($wp_query->query['meta_in_key']) && isset($wp_query->query['meta_in_value']) ):
		$safe_meta_values = array();
		foreach( $wp_query->query['meta_in_value'] as $meta_value )
			$safe_meta_values[] = $wpdb->escape($meta_value);
		$custom_where .= " AND meta_in_key.meta_key = '".$wpdb->escape($wp_query->query['meta_in_key'])."' AND meta_in_key.meta_value IN ('".implode("', '", $safe_meta_values)."') ";
	endif;

	if ( isset($wp_query->query['meta_key_in']) && isset($wp_query->query['meta_key_in_value']) ):
		$safe_meta_values = array();
		foreach( $wp_query->query['meta_key_in_value'] as $meta_value )
			$safe_meta_values[] = $wpdb->escape($meta_value);

		$custom_where .= " AND meta_key_in.meta_key = '".$wpdb->escape($wp_query->query['meta_key_in'])."' AND meta_key_in.meta_value IN ('".implode("', '", $safe_meta_values)."') ";
	endif;

	if ( isset($wp_query->query['meta_key_null']) ):
		$custom_where .= " AND {$wpdb->posts}.ID NOT IN ( SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '".$wp_query->query['meta_key_null']."' ) ";
	endif;

	if ( isset($wp_query->query['meta_in']) ):
		if ( isset($wp_query->query['meta_in']['key']) )
			$wp_query->query['meta_in'] = array($wp_query->query['meta_in']);
		foreach($wp_query->query['meta_in'] as $meta_in):
			if ( is_array($meta_in['value']) )
				$meta_in['value'] = implode(',', $meta_in['value']);
			$custom_where .= " AND meta_in_".$meta_in['key'].".meta_key = '".$meta_in['key']."' AND meta_in_".$meta_in['key'].".post_id IN ( ".$meta_in['value']." ) ";
		endforeach;
	endif;

	return $where." /* custom where basics_posts_where */ ".$custom_where;
}
add_filter('posts_where', 'basics_posts_where', 10, 2);

function basics_posts_join($join, $wp_query) {
	global $wpdb;

	if ( is_admin() && ! isset($wp_query->query['force_admin']) )
		return $join;

	$custom_join = "";

	foreach(array('meta_date_key', 'meta_in_key', 'meta_key_in', 'meta_date_before', 'meta_date_after', 'meta_time_before', 'meta_time_after') as $key)
		if ( isset($wp_query->query[$key]) )
			$custom_join .= " LEFT JOIN {$wpdb->postmeta} ".$key." ON {$wpdb->posts}.ID = ".$key.".post_id ";


	if ( isset($wp_query->query['meta_in']) ):
		if ( isset($wp_query->query['meta_in']['key']) )
			$wp_query->query['meta_in'] = array($wp_query->query['meta_in']);
		foreach($wp_query->query['meta_in'] as $meta_in)
			$custom_join .= " LEFT JOIN {$wpdb->postmeta} meta_in_".$meta_in['key']." ON {$wpdb->posts}.ID = meta_in_".$meta_in['key'].".meta_value ";
	endif;

	return $join.$custom_join;
}
add_filter('posts_join', 'basics_posts_join', 10, 2);

function basics_posts_date_where($where, $wp_query) {
	global $wpdb;

	if ( is_admin() && ! isset($wp_query->query['force_admin']) )
		return $where;

	foreach(array('begin-date', 'end-date', 'begin-time', 'end-time') as $key):
		if ( isset($wp_query->query[$key]) ):
			$date = strptime($wp_query->query[$key], "%Y/%m/%d".((strstr($key, 'time'))?' %H:%M:%S':''));
			if ( is_array($date) && extract($date)):
				$date = mktime($tm_hour, $tm_min, $tm_sec, $tm_mon + 1, $tm_mday, $tm_year + 1900);
				$where .= " /* CUSTOM WHERE basics_posts_date_where */ AND post_date ".((strstr($key, 'begin'))?'>=':'<')." '".date('Y-m-d'.((strstr($key, 'time'))?' H:i:s':''), $date)."' ";
			endif;
		endif;
	endforeach;

	foreach(array('meta_date_before', 'meta_date_after', 'meta_time_before', 'meta_time_after') as $key):
		if ( isset($wp_query->query[$key]) ):
			$wp_query->query[$key] = str_replace('-', '/', $wp_query->query[$key]);
			$date = strptime($wp_query->query[$key], "%Y/%m/%d".((strstr($key, 'time'))?' %H:%M:%S':''));
			if ( is_array($date) && extract($date)):
				$date = mktime($tm_hour, $tm_min, $tm_sec, $tm_mon + 1, $tm_mday, $tm_year + 1900);
				$where .= " /* CUSTOM WHERE basics_posts_date_where */ AND ".$key.".meta_key = '".$wp_query->query[$key.'_key']."' AND STR_TO_DATE(REPLACE(".$key.".meta_value, '-', '/'), '%Y/%m/%d".((strstr($key, 'time'))?' %H:%i:%s':'')."') ".((strstr($key, 'after'))?'>=':'<')." STR_TO_DATE('".date('Y/m/d'.((strstr($key, 'time'))?' H:i:s':''), $date)."', '%Y/%m/%d".((strstr($key, 'time'))?' %H:%i:%s':'')."') ";
			endif;
		endif;
	endforeach;

	return $where;
}
add_filter('posts_where', 'basics_posts_date_where', 10, 2);


function basics_posts_date_where_meta($where, $wp_query) {
	global $wpdb;

	if ( isset($wp_query->query['meta_date_key']) ):
		$where .= " AND meta_date_key.meta_key = '".$wp_query->query['meta_date_key']."' ";
	endif;

	if ( isset($wp_query->query['meta_date_begin']) ):
		$wp_query->query['meta_date_begin'] = preg_replace("#-#", "/", $wp_query->query['meta_date_begin']);
		$date = strptime($wp_query->query['meta_date_begin'], "%Y/%m/%d");
		if ( is_array($date) && extract($date)):
			$date = mktime(0, 0, 0, $tm_mon + 1, $tm_mday, $tm_year + 1900);
			$where .= " AND meta_date_key.meta_value >= '".date('Y-m-d', $date)."' ";
		endif;
	endif;

	if ( isset($wp_query->query['meta_date_end']) ):
	$wp_query->query['meta_date_end'] = preg_replace("#-#", "/", $wp_query->query['meta_date_end']);
		$date = strptime($wp_query->query['meta_date_end'], "%Y/%m/%d");
		if ( is_array($date) && extract($date)):
			$date = mktime(0, 0, 0, $tm_mon + 1, $tm_mday, $tm_year + 1900);
			$where .= " AND meta_date_key.meta_value < '".date('Y-m-d', $date)."' ";
		endif;
	endif;

	return $where;
}
add_filter('posts_where', 'basics_posts_date_where_meta', 10, 2);

function basics_query_where_in_taxonomy($where, $wp_query) {
	global $wpdb;

	if ( isset($wp_query->query['not_in_taxonomy']) and taxonomy_exists($wp_query->query['not_in_taxonomy']) ):

		$where .= " AND {$wpdb->posts}.ID NOT IN ( SELECT tr.object_id FROM {$wpdb->term_relationships} AS tr INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = '".$wp_query->query['not_in_taxonomy']."' ) ";

	endif;

	if ( isset($wp_query->query['in_taxonomy']) and taxonomy_exists($wp_query->query['in_taxonomy']) ):

		$where .= " AND {$wpdb->posts}.ID IN ( SELECT tr.object_id FROM {$wpdb->term_relationships} AS tr INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy = '".$wp_query->query['in_taxonomy']."' ) ";

	endif;

	return $where;
}
add_filter('posts_where', 'basics_query_where_in_taxonomy', 10, 2);

function basics_query_posts_distinct_request($distinct, $wp_query) {
	return " DISTINCT ";
}
add_filter('posts_distinct_request', 'basics_query_posts_distinct_request', 10, 2);

function basics_posts_like_where($where, $wp_query) {
	global $wpdb;

	if ( is_admin() and !isset($wp_query->query['force_admin']) )
		return $where;

	$custom_where = "";
	if ( isset($wp_query->query['like']) ):
		$pattern = $wp_query->query['like'];
		$extra_like = apply_filters('basics_posts_extra_like', '', $wp_query->query);
		if ($pattern == '0-9' ):
			$pattern = '^[0-9]';
			$custom_where .= " AND ( {$wpdb->posts}.post_title RLIKE '".$pattern."' OR {$wpdb->posts}.post_name RLIKE '".$pattern."' ".$extra_like." ) ";
		else:
			$pattern .= (strstr($pattern, '%')?'':'%');
			$custom_where .= " AND ( {$wpdb->posts}.post_title LIKE '".$pattern."' OR {$wpdb->posts}.post_name LIKE '".$pattern."' ".$extra_like." ) ";
		endif;
	endif;

	return $where." /* custom where basics_posts_like_where */ ".$custom_where;
}
add_filter('posts_where', 'basics_posts_like_where', 10, 2);

function basics_posts_match_where($where, $wp_query) {
	global $wpdb;

	if ( is_admin() and !isset($wp_query->query['force_admin']) )
		return $where;

	$custom_where = "";
	if ( isset($wp_query->query['like']) ):
		$pattern = $wp_query->query['like'];
		$extra_like = apply_filters('basics_posts_extra_like', '', $wp_query->query);
		if ($pattern == '0-9' ):
			$pattern = '^[0-9]';
			$custom_where .= " AND ( {$wpdb->posts}.post_title RLIKE '".$pattern."' OR {$wpdb->posts}.post_name RLIKE '".$pattern."' ".$extra_like." ) ";
		else:
			$pattern .= (strstr($pattern, '%')?'':'%');
			$custom_where .= " AND ( {$wpdb->posts}.post_title LIKE '".$pattern."' OR {$wpdb->posts}.post_name LIKE '".$pattern."' ".$extra_like.") ";
		endif;
	endif;

	return $where." /* custom where basics_posts_like_where */ ".$custom_where;
}
add_filter('posts_where', 'basics_posts_like_where', 10, 2);



function basics_posts_like_where_content($where, $wp_query) {
	global $wpdb;

	if ( is_admin() and !isset($wp_query->query['force_admin']) )
		return $where;

	$custom_where = "";
	if ( isset($wp_query->query['like_content']) ):
		$pattern = $wp_query->query['like_content'];
		if ($pattern == '0-9' ):
			$pattern = '^[0-9]';
			$custom_where .= " AND ({$wpdb->posts}.post_title RLIKE '".$pattern."' ";
			$custom_where .= " OR {$wpdb->posts}.post_content RLIKE '".$pattern."')";
		else:
			$pattern .= (strstr($pattern, '%')?'':'%');
			$custom_where .= " AND ({$wpdb->posts}.post_title LIKE '".$pattern."' ";
			$custom_where .= " OR {$wpdb->posts}.post_content LIKE '".$pattern."') ";
		endif;
	endif;

	return $where." /* custom where basics_posts_like_where */ ".$custom_where;
}
add_filter('posts_where', 'basics_posts_like_where_content', 10, 2);

function basics_posts_title_where($where, $wp_query) {
	if ( is_admin() and !isset($wp_query->query['force_admin']) )
		return $where;

	$custom_where = "";
	if ( isset($wp_query->query['has_title']) ):
		$pattern = $wp_query->query['has_title'];
		$custom_where .= " AND {$wpdb->posts}.post_title = '".$pattern."' ";
	endif;

	return $where." /* custom where basics_posts_like_where */ ".$custom_where;
}
add_filter('posts_where', 'basics_posts_title_where', 10, 2);

function basics_posts_taxonomies_where($where, &$wp_query) {
	global $wpdb;

	if ( isset($wp_query->query_vars['has_taxonomy']) ):
		if ( ! is_array($wp_query->query_vars['has_taxonomy']))
			$wp_query->query_vars['has_taxonomy'] = array($wp_query->query_vars['has_taxonomy']);
		foreach($wp_query->query_vars['has_taxonomy'] as $taxonomy ):
			if ( taxonomy_exists($taxonomy) ):
				if ( isset($wp_query->query_vars['term_meta_key']) ):
					$term_meta_join = " LEFT JOIN {$wpdb->termmeta} TM ON TT.term_id = TM.term_id ";
					$term_meta_where = " AND TM.meta_key = '".$wp_query->query_vars['term_meta_key']."'";
					if ( isset($wp_query->query_vars['term_meta_value']) ):
						$term_meta_where .= " AND TM.meta_value = '".$wp_query->query_vars['term_meta_value']."'";
					endif;
				endif;
				$where .= " AND EXISTS ( SELECT * FROM {$wpdb->term_relationships} TR LEFT JOIN {$wpdb->term_taxonomy} TT ON TR.term_taxonomy_id = TT.term_taxonomy_id $term_meta_join WHERE {$wpdb->posts}.ID = TR.object_id AND TT.taxonomy = '".$taxonomy."' $term_meta_where ) ";
			endif;
		endforeach;
	endif;

	if ( isset($wp_query->query_vars['has_not_taxonomy']) ):
		if ( ! is_array($wp_query->query_vars['has_not_taxonomy']))
			$wp_query->query_vars['has_not_taxonomy'] = array($wp_query->query_vars['has_not_taxonomy']);
		foreach($wp_query->query_vars['has_not_taxonomy'] as $taxonomy ):
			if ( taxonomy_exists($taxonomy) ):
				$where .= " AND NOT EXISTS ( SELECT * FROM {$wpdb->term_relationships} TR LEFT JOIN {$wpdb->term_taxonomy} TT ON TR.term_taxonomy_id = TT.term_taxonomy_id WHERE {$wpdb->posts}.ID = TR.object_id AND TT.taxonomy = '".$taxonomy."' ) ";
			endif;
		endforeach;
	endif;

	if ( is_array($wp_query->query_vars)):
		foreach ( $wp_query->query_vars as $taxonomy => $slug ):
			if ( $slug && preg_match("#^taxonomy_(.*)$#", $taxonomy, $tmp) && taxonomy_exists($tmp[1])):
				$taxonomy = $tmp[1];
				$term = get_term_by('slug', $slug, $taxonomy);
				if ( $term ):
					$where .= " AND tax_".$taxonomy.".term_taxonomy_id = ".$term->term_taxonomy_id;
				endif;
			endif;
		endforeach;
	endif;

	return $where;
}
add_filter('posts_where', 'basics_posts_taxonomies_where', 10, 2);

function basics_posts_taxonomies_join($join, $wp_query) {
	global $wpdb;

	if ( is_array($wp_query->query_vars)):
		foreach ( $wp_query->query_vars as $taxonomy => $slug ):
			if ( preg_match("#^taxonomy_(.*)$#", $taxonomy, $tmp) && taxonomy_exists($tmp[1])):
				$taxonomy = $tmp[1];
				$term = get_term_by('slug', $slug, $taxonomy);
				if ( $term ):
					$join .= " LEFT JOIN {$wpdb->term_relationships} AS tax_".$taxonomy." ON tax_".$taxonomy.".object_id = {$wpdb->posts}.ID ";
				endif;
			endif;
		endforeach;
	endif;

	return $join;
}
add_filter('posts_join', 'basics_posts_taxonomies_join', 10, 2);


if ( ! function_exists('get_queried_object')):
function get_queried_object() {
	global $wp_query;
	return $wp_query->get_queried_object();
}
endif;

function mahibasics_query_warning($query) {
	if ( preg_match("#^\s*SELECT\s#", $query) && ! preg_match("#LIMIT\s+\d+#", $query) )
		logr("SELECT without LIMIT : ".$query);
	return $query;
}
//add_filter('query', 'mahibasics_query_warning');

// TYPE OR CAT
// 'type_or_cat'		=>	array(array('type' => 'post', 'cat' => 'musique'), array('type' => 'music-review')),

function basics_posts_where_union($where, $wp_query) {
	global $wpdb;

	if ( isset($wp_query->query_vars['type_or_cat']) ):

		$conds = array();
		$wq = new WP_query();
		foreach($wp_query->query_vars['type_or_cat'] as $item):

			$cond = array();

			$wq->parse_tax_query($item);

			$tax_query = $wq->tax_query->get_sql($wpdb->posts, 'ID');
			if ( $tax_query['where'] ):
				$tax_query['where'] = preg_replace("#\s+#", " ", $tax_query['where']);
				$cond[] = ' '.$wpdb->posts.".ID IN ( SELECT object_id FROM {$wpdb->term_relationships} WHERE ".preg_replace("#^\s*AND\s*\((.*)\)\s*$#", "\\1", $tax_query['where']).' )';
			endif;

			foreach($item as $t => $f):
				switch($t):
					case 'post_type':
						if ( is_array($f) )
							$cond[] = " {$wpdb->posts}.post_type IN ('".implode("','", $f)."') ";
						else
							$cond[] = " {$wpdb->posts}.post_type = '".$f."' ";
					break;
				endswitch;
			endforeach;

			$conds[] = ' ( '.implode(' AND ', $cond).' ) ';

		endforeach;

		$where .= " AND ( ".implode(' OR ', $conds).' ) ';

	endif;

	return $where;
}
add_filter('posts_where', 'basics_posts_where_union', 10, 2);

function basics_posts_where_parent_in($where, $wp_query) {
	global $wpdb;

	if ( isset($wp_query->query_vars['post_parent__in']) && $wp_query->query_vars['post_parent__in'] ) {
		$post_parent__in = implode(',', array_map( 'absint', $wp_query->query_vars['post_parent__in'] ));
		$where .= " AND {$wpdb->posts}.post_parent IN ($post_parent__in)";
	}
	return $where;
}
add_filter('posts_where', 'basics_posts_where_parent_in', 10, 2);




// posts-to-posts


function basics_posts_where_has_connection($where, $wp_query) {
	global $wpdb;

	if ( isset($wp_query->query_vars['has_connection']) ):

		if ( isset($wp_query->query_vars['has_connection_type']) ):
			$invite = get_term_by('slug', 'invite', 'people_type');
			$where .= " AND wp_posts.ID IN (SELECT p2p_from FROM {$wpdb->p2p} WHERE p2p_type = '".$wp_query->query_vars['has_connection']."' AND p2p_to IN ( SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id = ".$invite->term_taxonomy_id." ) ) ";
		else:
			$where .= " AND wp_posts.ID IN (SELECT p2p_from FROM {$wpdb->p2p} WHERE p2p_type = '".$wp_query->query_vars['has_connection']."' ) ";
		endif;

	endif;

	return $where;
}
add_filter('posts_where', 'basics_posts_where_has_connection', 10, 2);


