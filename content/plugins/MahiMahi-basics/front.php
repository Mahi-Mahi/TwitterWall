<?php

define('WP_CONTENT_PATH', str_replace($_SERVER['DOCUMENT_ROOT'], '', constant('WP_CONTENT_DIR') ) );

if ( defined('BLOG_PUBLIC') ):
	function mahi_option_blog_public($value) {
		return true;
	}
	add_filter('default_option_blog_public', 'mahi_option_blog_public');
	add_filter('option_blog_public', 'mahi_option_blog_public');
endif;

function mahi_bwp_minify_cache_dir($s) {
	$s = constant('WP_CONTENT_DIR').'/minify/';
	@mkdir($s);
	return $s;
}
add_filter('bwp_minify_cache_dir', 'mahi_bwp_minify_cache_dir');

function mahi_bwp_minify_min_dir($s) {
	$s = '/min/';
	return $s;
}
add_filter('bwp_minify_min_dir', 'mahi_bwp_minify_min_dir');
function mahi_bwp_minify_rewrite() {
	if ( class_exists('BWP_MINIFY') ):
		global $wp_rewrite;
		$wp_rewrite->add_external_rule( 'min/(.*)$', constant('WP_CONTENT_PATH') . '/plugins/bwp-minify/min/' );
	endif;
}
add_action( 'generate_rewrite_rules', 'mahi_bwp_minify_rewrite');

function mahi_bwp_get_minify_src($src) {
	$src = preg_replace("#http:/[^/]+/#", '/', $src);
	if ( defined('ASSETS_VERSION') )
		$src .= "&".constant('ASSETS_VERSION');
	return $src;
}
add_filter('bwp_get_minify_src', 'mahi_bwp_get_minify_src');

function mahi_bwp_minify_script_ignore_ads($a) { $a[]='ads'; return $a; }
add_filter('bwp_minify_script_ignore', 'mahi_bwp_minify_script_ignore_ads');

function mahi_bwp_minify_script_ignore_modernizr($a) { $a[]='modernizr'; return $a; }
add_filter('bwp_minify_script_ignore', 'mahi_bwp_minify_script_ignore_modernizr');


/*
var scriptURL = 'http://examples.kevinchisholm.com/utils/script/script.php?sleep=2';

function loadScript(url,callback){
    if(!url || !(typeof url === 'string')){return};
    var script = document.createElement('script');
    //if this is IE8 and below, handle onload differently
    if(typeof document.attachEvent === "object"){
        script.onreadystatechange = function(){
            //once the script is loaded, run the callback
            if (script.readyState === 'loaded'){
                if (callback){callback()};
            };
        };
    } else {
        //this is not IE8 and below, so we can actually use onload
        script.onload = function(){
            //once the script is loaded, run the callback
            if (callback){callback()};
        };
    };
    //create the script and add it to the DOM
    script.src = url;
    document.getElementsByTagName('head')[0].appendChild(script);
};

loadScript(scriptURL,function(){
    console.log('the remote script has finished loading');
});
*/
function mahi_async_script_loader($tag, $string, $type, $media){
	if ( defined('MAHI_ASYNC_SCRIPT_DISABLED') )
		return $tag;
	if ( $type == 'script' ):
		preg_match("#src=['\"](.*)['\"]#", $tag, $tmp);
		ob_start();
		?>
		<script>
			<?php
			if ( ! defined('MAHI_ASYNC_FUNCTION') ):
				define('MAHI_ASYNC_FUNCTION', true);
				?>
				function loadScript(e,t){if(e&&"string"==typeof e){var n=document.createElement("script")
				"object"==typeof document.attachEvent?n.onreadystatechange=function(){"loaded"===n.readyState&&t&&t()}:n.onload=function(){t&&t()},n.src=e,document.getElementsByTagName("head")[0].appendChild(n)}}
				<?php
			endif;
			?>
			loadScript('<?php print $tmp[1] ?>');
		</script>
		<?php
		$tag = ob_get_clean();
	endif;
	return $tag;
}

if ( defined('MAHI_ASYNC_SCRIPT') ):
	add_filter('bwp_get_minify_tag', 'mahi_async_script_loader', 10, 4);
endif;

function mahi_bwp_host($tag, $string, $type, $media) {

	if ( ! defined('MAHI_BWP_HOST') )
		return $tag;

	$tag = preg_replace("#((href|src)=['\"])(.*)(['\"])#", "\\1http://".$_SERVER['SERVER_NAME']."\\3\\4", $tag);

	return $tag;
}

add_filter('bwp_get_minify_tag', 'mahi_bwp_host', 10, 4);

function mahi_favicon_rewrite() {
	global $wp_rewrite;

	if ( ! defined('NGINX') ):
		foreach( array(
			'favicon.ico' => preg_replace("#^http://[^/]+/#", '', get_bloginfo( 'stylesheet_directory' )).'/favicon.ico',
			'apple-touch-icon.png' => preg_replace("#^http://[^/]+/#", '', get_bloginfo( 'stylesheet_directory' )).'/apple-touch-icon.png',
			'apple-touch-icon-144x144-precomposed.png' => preg_replace("#^http://[^/]+/#", '', get_bloginfo( 'stylesheet_directory' )).'/apple-touch-icon-144x144-precomposed.png',
			'apple-touch-icon-precomposed.png' => preg_replace("#^http://[^/]+/#", '', get_bloginfo( 'stylesheet_directory' )).'/apple-touch-icon-precomposed.png',
			'apple-touch-icon-114x114-precomposed.png' => preg_replace("#^http://[^/]+/#", '', get_bloginfo( 'stylesheet_directory' )).'/apple-touch-icon-114x114-precomposed.png',
			'apple-touch-icon-72x72-precomposed.png' => preg_replace("#^http://[^/]+/#", '', get_bloginfo( 'stylesheet_directory' )).'/apple-touch-icon-72x72-precomposed.png',
			'apple-touch-icon-57x57-precomposed.png' => preg_replace("#^http://[^/]+/#", '', get_bloginfo( 'stylesheet_directory' )).'/apple-touch-icon-57x57-precomposed.png'
		) as $regexp => $dest) :
			$wp_rewrite->add_external_rule($regexp, $dest);
		endforeach;
	endif;
}
add_action( 'generate_rewrite_rules', 'mahi_favicon_rewrite');

function mahi_body_class_sanitize_title($classes) {
	$classes[] = sanitize_title_with_dashes($_SERVER['HTTP_HOST']);
	return $classes;
}


add_filter('body_class', 'mahi_body_class_sanitize_title');




function mahi_fix_home_url($url) {
	$url = preg_replace("#(http://[^/]+)?/+#", "\\1/", $url);
	return $url;
}
add_filter( 'home_url', 'mahi_fix_home_url', 99);





function mahi_has_query_var($var) {
	global $wp_query;
	return isset($wp_query->query[$var]);
}

function mahi_body_class($classes, $class) {
	if ( defined('body_class') )
		$classes[] = constant('body_class');
	return $classes;
}
add_filter('body_class', 'mahi_body_class', 10, 2);

function mahibasics_fix_the_content($content) {
	$content = preg_replace("#<span class=\"Apple-style-span[^>]+>(.*)</span>#Um", "\\1", $content);
	$content = preg_replace("#<p>\s*</p>#Um", "", $content);
	return $content;
}
add_filter('the_content', 'mahibasics_fix_the_content', 99);

function the_date_time($the_date, $d, $before, $after) {

	$the_date = $before.'<time class="published updated" datetime="'.get_the_date( 'Y-m-d' ).'"><span>'.get_the_date( $d ).'</span></time>'.$after;

	return $the_date;
}
add_filter('the_date', 'the_date_time', 10, 4);

function basics_the_date( $d = '', $before = '', $after = '', $echo = true ) {
	global $currentday, $previousday;
	$the_date = '';
	if ( $currentday != $previousday ) {
		$the_date .= $before;
		$the_date .= get_the_date( $d );
		$the_date .= $after;
		$previousday = $currentday;

		$the_date = apply_filters('the_date', $the_date, $d, $before, $after);

		if ( $echo )
			echo $the_date;
		else
			return $the_date;
		return true;
	}
	return false;
}

function mahi_the_date($d = '', $post_date = null) {
	print mahi_get_the_date($d, $post_date);
}
function mahi_get_the_date($d = '', $post_date = null) {
	$the_date = '';

	if ( ! $post_date )
		$post_date = get_post_field('post_date', get_the_ID());

	if ( '' == $d )
		$the_date .= mysql2date(get_option('date_format'), $post_date);
	else
		$the_date .= mysql2date($d, $post_date);

	return apply_filters('get_the_date', $the_date, $d);
}

function is_category_child($category) {
	if ( is_category($category) )
		return true;
	$current_category = get_queried_object();
	while($current_category->parent):
		$current_category = get_term($current_category->parent, 'category');
		if ( $current_category->slug == $category )
			return true;
	endwhile;
	return false;
}

function the_main_category($post_id = null, $ancestor = false) {
	print get_the_main_category($post_id, false, $ancestor);
}

function get_the_main_category($post_id = null, $object = false, $ancestor = false) {
	return get_the_main_term($post_id, $object, $ancestor, 'category');
}
function get_the_main_term($post_id = null, $object = false, $ancestor = false, $taxonomy = 'category', $child = false) {
	if ( ! $post_id )
		$post_id = get_the_ID();

	if ( $result = apply_filters('MahiMahi_basics_the_main_category_before', false, $post_id, $object, $ancestor) )
		return $result;

	$categories = get_the_terms( $post_id, $taxonomy );
	if ( empty($categories))
		return false;

	if ( $child ):
		foreach($categories as $cat):
			if ( $cat->parent > 0 ):
				$category = $cat;
				break;
			endif;
		endforeach;
	else:
		$category = current($categories);
	endif;

	if ( $ancestor )
		while($category->parent)
			$category = get_term($category->parent, $taxonomy);

	if ( $object )
		return $category;

	return '<a href="' . get_category_link( $category->term_id ) . '" rel="category">' . $category->name . '</a>';
}

function the_term($taxonomy, $post_id = null, $link = false, $slug = false) {
	if ( ! $post_id )
		$post_id = get_the_ID();

	$term = get_the_term($taxonomy, $post_id);
	if ( ! $term )
		return null;

	if ( $link ):
		$link = get_term_link( $term, $taxonomy );
		if ( is_wp_error( $link ) )
			return $link;
		return '<a href="' . $link . '" rel="tag">' . $term->name . '</a>';
	endif;

	if ( $slug )
		return $term->slug;

	return $term->name;

}

function get_the_term($taxonomy, $post_id = null) {
	if ( ! $post_id )
		$post_id = get_the_ID();

	$terms = get_the_terms($post_id, $taxonomy);
	if ( empty($terms) )
		return null;

	$term = current($terms);
	return $term;
}

function create_link($s, $target = true) {
	$s = preg_replace_callback("@(\s*)(https?://[^\s]+)@",  create_function('$matches', 'return $matches[1]."<a href=\"".$matches[2]."\" '.($target ? 'target=\"'.$target.'\"' : '').'>".$matches[2]."</a>";') , $s );
	$s = preg_replace_callback("@(\s+)(www.[^\s]+)@",  create_function('$matches', 'return $matches[1]."<a href=\"http://".$matches[2]."\" '.($target ? 'target=\"'.$target.'\"' : '').'>".$matches[2]."</a>";') , $s );
	return $s;
}
add_filter('create_link', 'create_link');


if ( ! function_exists('single_tax_title') ) {

	function single_tax_title($prefix = '', $display = true ) {
		global $wp_query;
		if ( !is_tax() )
			return;

		$tag = $wp_query->get_queried_object();

		if ( ! $tag )
			return;

		$my_tag_name = apply_filters('single_tag_title', $tag->name);
		if ( !empty($my_tag_name) ) {
			if ( $display )
				echo $prefix . $my_tag_name;
			else
				return $my_tag_name;
		}
	}

}

function the_meta_content($meta, $post_id = null) {
	print apply_filters('the_content', get_the_meta($meta, $post_id));
}

function get_the_meta($meta, $post_id = null, $single = true, $default = null) {

	if ( ! $post_id )
		$post_id = get_the_ID();

	$res = get_post_meta($post_id, $meta, $single);

	if ( ! $res && $default != null )
		$res = $default;

	return $res;
}


function head_link_icon() {
	/*
	/favicon.ico
	/apple-touch-icon.png
	*/
	global $wp_rewrite;

	$wp_rewrite->non_wp_rules = array( 'favicon.ico' => preg_replace("#^http://[^/]+/#", '', get_bloginfo( 'stylesheet_directory' )).'/favicon.ico' );
}

function mahibasics_archives_list($args = '') {
	global $wpdb, $wp_locale;

	$defaults = array(
		'post_type' => 'post',
		'type' => 'monthly', 'limit' => '',
		'format' => 'html', 'before' => '',
		'after' => '', 'show_post_count' => false, 'show_post_list' => false,
		'no_link_on_month' => false,
		'echo' => 1
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

	if ( '' == $type )
		$type = 'monthly';

	if ( '' != $limit ) {
		$limit = absint($limit);
		$limit = ' LIMIT '.$limit;
	}

	// this is what will separate dates on weekly archive links
	$archive_week_separator = '&#8211;';

	// over-ride general date format ? 0 = no: use the date format set in Options, 1 = yes: over-ride
	$archive_date_format_over_ride = 0;

	// options for daily archive (only if you over-ride the general date format)
	$archive_day_date_format = 'Y/m/d';

	// options for weekly archive (only if you over-ride the general date format)
	$archive_week_start_date_format = 'Y/m/d';
	$archive_week_end_date_format	= 'Y/m/d';

	if ( !$archive_date_format_over_ride ) {
		$archive_day_date_format = get_option('date_format');
		$archive_week_start_date_format = get_option('date_format');
		$archive_week_end_date_format = get_option('date_format');
	}

	//filters
	$where = apply_filters('getarchives_where', "WHERE post_type = '".$post_type."' AND post_status = 'publish'", $r );
	$join = apply_filters('getarchives_join', "", $r);

	$output = '';

	if ( 'monthly' == $type ) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date DESC $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'wp_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'wp_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		if ( $arcresults ) {
			$afterafter = $after;
			foreach ( (array) $arcresults as $arcresult ) {
				$url = get_month_link( $arcresult->year, $arcresult->month );
				/* translators: 1: month name, 2: 4-digit year */
				$text = sprintf(__('%1$s %2$d'), $wp_locale->get_month($arcresult->month), $arcresult->year);
				if ( $show_post_count ):
					$after = '&nbsp;('.$arcresult->posts.')' . $afterafter;
				elseif($show_post_list):
					$after = "";
					$my_query = array('posts_per_page' => -1, 'post_type' => $post_type, 'year' => $arcresult->year, 'monthnum' => $arcresult->month);
					query_posts($my_query);
					if( ( have_posts())):
						$after .= "<ul>";
						while(have_posts()):
							the_post();
							$after .= "<li>";
								$after .= '<a href="'.get_permalink().'">';
									$after .= get_the_title();
								$after .= "</a>";
							$after .= "</li>";
						endwhile;
						$after .= "</ul>";
					endif;
					wp_reset_query();

				endif;
				if ( $no_link_on_month )
					$output .= $before . "<li><span>" . $text . "</span>" . $after . "</li>";
				else
					$output .= get_archives_link($url, $text, $format, $before, $after);
			}
		}
	} elseif ('yearly' == $type) {
		$query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date) ORDER BY post_date DESC $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'wp_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'wp_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		if ($arcresults) {
			$afterafter = $after;
			foreach ( (array) $arcresults as $arcresult) {
				$url = get_year_link($arcresult->year);
				$text = sprintf('%d', $arcresult->year);
				if ($show_post_count)
					$after = '&nbsp;('.$arcresult->posts.')' . $afterafter;
				$output .= get_archives_link($url, $text, $format, $before, $after);
			}
		}
	} elseif ( 'daily' == $type ) {
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `dayofmonth`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY post_date DESC $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'wp_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'wp_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		if ( $arcresults ) {
			$afterafter = $after;
			foreach ( (array) $arcresults as $arcresult ) {
				$url	= get_day_link($arcresult->year, $arcresult->month, $arcresult->dayofmonth);
				$date = sprintf('%1$d-%2$02d-%3$02d 00:00:00', $arcresult->year, $arcresult->month, $arcresult->dayofmonth);
				$text = mysql2date($archive_day_date_format, $date);
				if ($show_post_count)
					$after = '&nbsp;('.$arcresult->posts.')'.$afterafter;
				$output .= get_archives_link($url, $text, $format, $before, $after);
			}
		}
	} elseif ( 'weekly' == $type ) {
		$week = _wp_mysql_week( '`post_date`' );
		$query = "SELECT DISTINCT $week AS `week`, YEAR( `post_date` ) AS `yr`, DATE_FORMAT( `post_date`, '%Y-%m-%d' ) AS `yyyymmdd`, count( `ID` ) AS `posts` FROM `$wpdb->posts` $join $where GROUP BY $week, YEAR( `post_date` ) ORDER BY `post_date` DESC $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'wp_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'wp_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		$arc_w_last = '';
		$afterafter = $after;
		if ( $arcresults ) {
				foreach ( (array) $arcresults as $arcresult ) {
					if ( $arcresult->week != $arc_w_last ) {
						$arc_year = $arcresult->yr;
						$arc_w_last = $arcresult->week;
						$arc_week = get_weekstartend($arcresult->yyyymmdd, get_option('start_of_week'));
						$arc_week_start = date_i18n($archive_week_start_date_format, $arc_week['start']);
						$arc_week_end = date_i18n($archive_week_end_date_format, $arc_week['end']);
						$url  = sprintf('%1$s/%2$s%3$sm%4$s%5$s%6$sw%7$s%8$d', home_url(), '', '?', '=', $arc_year, '&amp;', '=', $arcresult->week);
						$text = $arc_week_start . $archive_week_separator . $arc_week_end;
						if ($show_post_count)
							$after = '&nbsp;('.$arcresult->posts.')'.$afterafter;
						$output .= get_archives_link($url, $text, $format, $before, $after);
					}
				}
		}
	} elseif ( ( 'postbypost' == $type ) || ('alpha' == $type) ) {
		$orderby = ('alpha' == $type) ? "post_title ASC " : "post_date DESC ";
		$query = "SELECT * FROM $wpdb->posts $join $where ORDER BY $orderby $limit";
		$key = md5($query);
		$cache = wp_cache_get( 'wp_get_archives' , 'general');
		if ( !isset( $cache[ $key ] ) ) {
			$arcresults = $wpdb->get_results($query);
			$cache[ $key ] = $arcresults;
			wp_cache_set( 'wp_get_archives', $cache, 'general' );
		} else {
			$arcresults = $cache[ $key ];
		}
		if ( $arcresults ) {
			foreach ( (array) $arcresults as $arcresult ) {
				if ( $arcresult->post_date != '0000-00-00 00:00:00' ) {
					$url  = get_permalink($arcresult);
					$arc_title = $arcresult->post_title;
					if ( $arc_title )
						$text = strip_tags(apply_filters('the_title', $arc_title));
					else
						$text = $arcresult->ID;
					$output .= get_archives_link($url, $text, $format, $before, $after);
				}
			}
		}
	}
	if ( $echo )
		echo $output;
	else
		return $output;
}


function get_the_term_list_raw( $id = 0, $taxonomy, $before = '', $sep = '', $after = '', $meta_field = 'name' ) {
	$terms = get_the_terms( $id, $taxonomy );

	if ( is_wp_error( $terms ) )
		return $terms;

	if ( empty( $terms ) )
		return false;

	foreach ( $terms as $term ) {
		if ($meta_field == 'name') :
			$term_list[] = $term->name;
		else :
			$term_list[] = get_term_meta($term->term_id,$meta_field,true);
		endif;

	}

	return $before . join( $sep, $term_list ) . $after;
}



/*
* auto no-follow in content link
*
* add_filter('the_content', 'auto_nofollow');
*
*/

function auto_nofollow($content) {
	//return stripslashes(wp_rel_nofollow($content));

	return preg_replace_callback('/<a>]+/', 'auto_nofollow_callback', $content);
}

function auto_nofollow_callback($matches) {
	$link = $matches[0];
	$site_link = get_bloginfo('url');

	if (strpos($link, 'rel') === false) {
		$link = preg_replace("%(href=S(?!$site_link))%i", 'rel="nofollow" $1', $link);
	} elseif (preg_match("%href=S(?!$site_link)%i", $link)) {
		$link = preg_replace('/rel=S(?!nofollow)S*/i', 'rel="nofollow"', $link);
	}
	return $link;
}


function mahi_get_featured($key, $start = null, $offset = null, $shuffle = false) {
	$selection = get_option('featured-'.$key);
	if ( ! is_array($selection) )
		return array();
	$featured = array();

	foreach($selection as $selected)
		if ( $selected['post_type'] ):
			if ( get_post_status($selected['ID']) == 'publish' ):
				if (
					( ! $selected['date'] || strtotime($selected['date']) < strtotime(locale_date()) )
					&&
					( ! $selected['end_date'] || strtotime($selected['end_date']) > strtotime(locale_date()) )
				 ):
					$featured[] = $selected;
				endif;
			endif;
		else:
			$featured[] = $selected;
		endif;
	if ( $shuffle )
		shuffle($featured);

	$featured = apply_filters('mahi_get_featured', $featured, $key);

	if ( $start !== null && $offset )
		$featured = array_slice($featured, $start, $offset);

	return $featured;
}

/*
 * Replacement for get_adjacent_post()
 *
 * This supports only the custom post types you identify and does not
 * look at categories anymore. This allows you to go from one custom post type
 * to another which was not possible with the default get_adjacent_post().
 * Orig: wp-includes/link-template.php
 *
 * @param string $direction: Can be either 'prev' or 'next'
 * @param multi $post_types: Can be a string or an array of strings
 */
function mahi_get_adjacent_post($direction = 'prev', $post_types = 'post') {
	global $post, $wpdb;

	if(empty($post)) return NULL;
	if(!$post_types) return NULL;

	if(is_array($post_types)){
		$txt = '';
		for($i = 0; $i <= count($post_types) - 1; $i++){
			$txt .= "'".$post_types[$i]."'";
			if($i != count($post_types) - 1) $txt .= ', ';
		}
		$post_types = $txt;
	}

	$current_post_date = $post->post_date;

	$join = '';
	$in_same_cat = FALSE;
	$excluded_categories = '';
	$adjacent = $direction == 'prev' ? 'previous' : 'next';
	$op = $direction == 'prev' ? '<' : '>';
	$order = $direction == 'prev' ? 'DESC' : 'ASC';

	$join  = apply_filters( "get_{$adjacent}_post_join", $join, $in_same_cat, $excluded_categories );
	$where = apply_filters( "get_{$adjacent}_post_where", $wpdb->prepare("WHERE p.post_date $op %s AND p.post_type IN({$post_types}) AND p.post_status = 'publish'", $current_post_date), $in_same_cat, $excluded_categories );
	$sort  = apply_filters( "get_{$adjacent}_post_sort", "ORDER BY p.post_date $order LIMIT 1" );

	$query = "SELECT p.* FROM $wpdb->posts AS p $join $where $sort";

	$query_key = 'adjacent_post_' . md5($query);
	$result = wp_cache_get($query_key, 'counts');
	if ( false !== $result )
		return $result;

	$result = $wpdb->get_row("SELECT p.* FROM $wpdb->posts AS p $join $where $sort");
	if ( null === $result )
		$result = '';

	wp_cache_set($query_key, $result, 'counts');
	return $result;
}

function get_adjacent_image($prev = true) {
	$post = get_post();
	$attachments = array_values( get_children( array( 'post_parent' => $post->post_parent, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => 'ASC', 'orderby' => 'menu_order ID' ) ) );

	foreach ( $attachments as $k => $attachment )
		if ( $attachment->ID == $post->ID )
			break;

	$k = $prev ? $k - 1 : $k + 1;

	$attachment_id = null;
	if ( isset( $attachments[ $k ] ) ) {
		$attachment_id = $attachments[ $k ]->ID;
	}
	return $attachment_id;
}


if(!function_exists('get_the_author_posts_link')):
function get_the_author_posts_link($deprecated = '') {
	if ( !empty( $deprecated ) )
		_deprecated_argument( __FUNCTION__, '2.1' );

	global $authordata;
	if ( !is_object( $authordata ) )
		return false;
	$link = sprintf(
		'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
		get_author_posts_url( $authordata->ID, $authordata->user_nicename ),
		esc_attr( sprintf( __( 'Posts by %s' ), get_the_author() ) ),
		get_the_author()
	);
	return apply_filters( 'the_author_posts_link', $link );
}
endif;

function mahi_get_the_excerpt($id = null, $words = 55, $characters = null, $elispsis = '[&hellip;]') {

	$text = get_post_field('post_excerpt', $id);
	if ( $text )
		return $text;

	$text = get_post_field('post_content', $id);

	$text = strip_shortcodes( $text );

	$text = apply_filters('the_content', $text);
	$text = str_replace(']]>', ']]&gt;', $text);
	$excerpt_more = apply_filters('excerpt_more', ' ' . $elispsis);
	if ( $words ):
		$excerpt_length = apply_filters('excerpt_length', $words);
		$text = wp_trim_words( $text, $excerpt_length, $excerpt_more );
	endif;

	if ( $characters ):
		$text = neat_trim(strip_tags($text), $characters, $excerpt_more);
	endif;

	return apply_filters('wp_trim_excerpt', $text, get_the_content(null, null, $id));
}


function get_found_posts(){
	global $wp_query;
	return $wp_query->found_posts;
}


function mahi_gallery_get_attachment($post_parent, $post_name) {
	global $wpdb;

	$sql = $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_parent = %s AND post_type = 'attachment' AND post_name = %s", $post_parent, $post_name);
	$id = $wpdb->get_var( $sql );

	if ( ! $id && is_numeric($post_name) ):
		return $post_name;
	endif;

	return $id;

}

function mahi_get_gallery_permalink($post_id, $attachment_id) {
	if ( defined('GALLERY_SLUG') )
		$gallery_slug = constant('GALLERY_SLUG');
	else
		$gallery_slug = 'galerie';
	return get_permalink($post_id) . $gallery_slug . '/' . get_post_field('post_name', $attachment_id);
}





