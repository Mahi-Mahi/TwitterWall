<?php

/*

CREATE TABLE `wp_locations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `location` point NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  SPATIAL KEY `location` (`location`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DELIMITER $$
CREATE FUNCTION `distance` (a POINT, b POINT)
	RETURNS double DETERMINISTIC
	BEGIN
	RETURN GLength( LineStringFromWKB( LineString(a,b)));
	END
  $$
DELIMITER ;


http://vinsol.com/blog/2011/08/30/geoproximity-search-with-mysql/


taxonomy_place.php

line 124
>>
		if ( isset($_POST))
			$geo_id = mahi_geoid_add($_POST['lat'], $_POST['lng']);
		update_term_meta($term_id, 'geo_id', $geo_id);


*/

function mahi_geoid_add($lat, $lng, $name = '') {
	global $wpdb;
	$lat = str_replace(' ', '', str_replace(',', '.', $lat));
	$lng = str_replace(' ', '', str_replace(',', '.', $lng));

	$sql = "INSERT INTO wp_locations (location, name) VALUES (POINT(".$lat.", ".$lng."), '".$wpdb->escape($name)."' ) ";
	$wpdb->query($sql);
	$location_id = $wpdb->insert_id;

	return $location_id;
}

function mahi_geoid_getlocation($geoid){
	global $wpdb;
	$sql = "SELECT AsText(location) as location FROM wp_locations WHERE id='".$geoid."'";
	$result = $wpdb->get_results($sql);
	if(!empty($result))
		return $result[0]->location;
	else
		return false;
}

function mahi_geoid_distance($geoid1, $geoid2){
	global $wpdb;
	$sql = "SELECT distance(geoid.location, (SELECT location FROM wp_locations WHERE id='".$geoid2."') )*100 as distance FROM wp_locations geoid WHERE id='".$geoid1."'";
	$result = $wpdb->get_results($sql);
	if(!empty($result))
		return $result[0]->distance;
	else
		return false;
}

function mahi_geoid_update($geoid, $lat, $lng) {
	global $wpdb;
	$lat = str_replace(' ', '', str_replace(',', '.', $lat));
	$lng = str_replace(' ', '', str_replace(',', '.', $lng));

	$sql = "UPDATE wp_locations SET location = POINT(".$lat.", ".$lng.") WHERE id = ".$geoid;
	$wpdb->query($sql);

}

function mahi_geoid_delete($geoid) {
	global $wpdb;

	$sql = "DELETE FROM wp_locations WHERE id = ".$geoid;
	$wpdb->query($sql);

}

function basics_posts_fields_geoid_near($fields, $wp_query) {
	global $wpdb;

	if ( is_admin() )
		return $fields;

	if ( isset($wp_query->query['geoid_nearest']) || isset($wp_query->query['geoid_near']) ):

		if ( isset($wp_query->query['geoid_nearest']) )
			$origin = $wp_query->query['geoid_nearest'];

		if ( isset($wp_query->query['geoid_near']) )
			$origin = $wp_query->query['geoid_near'];

		$fields .= ", distance(geoid.location, POINT(".$origin->lat.", ".$origin->lng.") ) AS geoid_near_distance, Astext(geoid.location) AS location ";
	endif;
	return $fields;
}
add_filter('posts_fields', 'basics_posts_fields_geoid_near', 10, 2);

function basics_posts_where_geoid_near($where, $wp_query) {
	global $wpdb;

	if ( is_admin() )
		return $where;

	if ( isset($wp_query->query['geoid_near_distance']) ):

		if ( isset($wp_query->query['geoid_nearest']) )
			$origin = $wp_query->query['geoid_nearest'];

		if ( isset($wp_query->query['geoid_near']) )
			$origin = $wp_query->query['geoid_near'];

		$where .= " AND distance(geoid.location, POINT(".$origin->lat.", ".$origin->lng.") ) < CAST('".$wp_query->query['geoid_near_distance']."' AS DECIMAL(10,4) )";

		$where .= " AND geoid_meta.meta_key = 'geo_id' ";

	elseif(isset($wp_query->query['geoid_near']) ||  isset($wp_query->query['geoid_nearest'])):

		$where .= " AND geoid_meta.meta_key = 'geo_id' ";

	endif;

	if ( isset($wp_query->query['geoid_nearest']) )
		$where .= " AND geoid.id IS NOT NULL ";

	return $where;
}
add_filter('posts_where', 'basics_posts_where_geoid_near', 10, 2);

function basics_posts_join_geoid_near($join, $wp_query) {
	global $wpdb;

	if ( is_admin() )
		return $join;

	if ( isset($wp_query->query['geoid_nearest']) || isset($wp_query->query['geoid_near']) ):
		$join .= " LEFT JOIN ".$wpdb->prefix."postmeta geoid_meta ON ".$wpdb->posts.".ID = geoid_meta.post_id ";
		$join .= " LEFT JOIN wp_locations geoid ON geoid_meta.meta_value = geoid.id ";
	endif;

	return $join;
}
add_filter('posts_join', 'basics_posts_join_geoid_near', 10, 2);

function basics_posts_orderby_geoid_near($orderby, $wp_query) {
	global $wpdb;

	if ( is_admin() )
		return $orderby;

	if ( isset($wp_query->query['geoid_nearest']) && ! isset($wp_query->query['geoid_near_distance']) ):

		$orderby = " geoid_near_distance ASC ";

	endif;

	return $orderby;
}
add_filter('posts_orderby', 'basics_posts_orderby_geoid_near', 10, 2);

function basics_pre_user_query_geoid_near($request){
	global $wpdb;
	if ( (isset($request->query_vars['geoid_near']) && (!empty($request->query_vars['geoid_near']))) || (isset($request->query_vars['geoid_nearest']) && (!empty($request->query_vars['geoid_nearest'])))):

		$position = "";

		if(isset($request->query_vars['geoid_nearest'])){
			$position = $request->query_vars['geoid_nearest'];
		}
		elseif(isset($request->query_vars['geoid_near'])){
			$position = $request->query_vars['geoid_near'];
		}


		$request->query_fields = " SQL_CALC_FOUND_ROWS DISTINCT {$wpdb->users}.*, distance(geoid.location, ".$position." )*100 AS geoid_near_distance, Astext(geoid.location) AS location";

		if ( isset($request->query_vars['geoid_near_distance']) ):

			$request->query_where .= " AND distance(geoid.location, ".$position." )*100 < CAST('".$request->query_vars['geoid_near_distance']."' AS DECIMAL(10,4) )";

			$request->query_where .= " AND geoid_meta.meta_key = 'geo_id' ";

		endif;

		if ( isset($request->query_where['geoid_nearest']) )
			$request->query_where .= " AND geoid.id IS NOT NULL ";


		$request->query_from .= " LEFT JOIN {$wpdb->usermeta} geoid_meta ON wp_users.ID = geoid_meta.user_id ";
		$request->query_from .= " LEFT JOIN wp_locations geoid ON geoid_meta.meta_value = geoid.id ";
		$request->query_where .= " AND geoid_meta.meta_key = 'geo_id' ";

		$request->query_orderby = " ORDER BY geoid_near_distance ASC ";
	endif;

}
add_action('pre_user_query', "basics_pre_user_query_geoid_near");
