<?php

if ( ! defined( 'WP_AUTO_UPDATE_CORE' ) )
	define( 'WP_AUTO_UPDATE_CORE', false );

// for WordPress 3.3 : http://wpengineer.com/2272/how-to-add-and-deactivate-the-new-feature-pointer-in-wordpress-3-3/
add_filter( 'show_wp_pointer_admin_bar', '__return_false' );

// Disables the post format UI on the edit post screen.
add_filter( 'enable_post_format_ui', '__return_false' );

//Disable WPseo informations in list
add_filter( 'wpseo_use_page_analysis', '__return_false' );


/*
LOCAL : always green
DEV / STAGING  : always classic ( blue)
PROD  : unchanged ( so must be gray ( fresh) )
*/

function mahibasics_admin_color($color) {
	if ( is_local() ):
		return 'green';
	elseif ( is_dev() ):
		return 'classic';
	else:
		return $color;
	endif;
}
// add_filter('get_user_option_admin_color', 'mahibasics_admin_color');

wp_admin_css_color( 'green', __( 'Local' ), constant('MahiMahiBasics_URL').'/css/admin-colors.css' ,
	array( '#00FF00', '#cfdfe9', '#d1e5ee', '#eff8ff' )
	);
// array( '#5589aa', '#cfdfe9', '#d1e5ee', '#eff8ff' )

// Remove WordPress Core Message Update
add_filter( 'pre_site_transient_update_core', create_function( '$a', "return new StdClass;" ) );


function mahibasics_admin_init() {
	global $user_login;
	get_currentuserinfo();
	if (!current_user_can('update_plugins')) { // checks to see if current user can update plugins
		add_action( 'init', create_function( '$a', "remove_action( 'init', 'wp_version_check' );" ), 2 );
		add_filter( 'pre_option_update_core', create_function( '$a', "return null;" ) );
	}

}
// add_action('admin_init', 'mahibasics_admin_init');

function mahibasics_admin_remove_seo_menu() {
	global $wp_admin_bar;
	$wp_admin_bar->remove_menu('wpseo-menu');
}
add_action('admin_bar_menu', 'mahibasics_admin_remove_seo_menu', 96);


function mahibasics_show_admin_bar() {
	return isset($_GET['admin_bar']) || defined('SHOW_ADMIN_BAR');
}
add_filter('show_admin_bar', 'mahibasics_show_admin_bar');

function mahimahi_basics_remove_dashboard_widgets() {
	global $wp_meta_boxes;

  	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);

	if ( ! defined('DASHBOARD_RECENT_COMMENTS') )
  		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);

  	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);

	if ( ! defined('DASHBOARD_INCOMING_LINKS') )
  		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);

  	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);

	if ( ! defined('DASHBOARD_RIGHT_NOW') )
  		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);

	unset($wp_meta_boxes['dashboard']['normal']['core']['yoast_db_widget']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['BADashboardPendingReview']);

	unset($wp_meta_boxes['dashboard']['normal']['core']['w3tc_latest']);

}
add_action('wp_dashboard_setup', 'mahimahi_basics_remove_dashboard_widgets', 99 );

function mahimahi_basics_admin_menu() {

	add_menu_page( 'MahiMahi-Basics', 'MahiMahi-Basics', 'administrator', 'mahimahi-basics', 'mahimahi_basics_admin_page' );
 	add_submenu_page('mahimahi-basics', 'MahiMahi-Basics Rewrite Debug', 'Rewrite Debug', 'administrator', 'mahimahi_basics_rewrite', 'mahimahi_basics_rewrite_page');
 	add_submenu_page('mahimahi-basics', 'MahiMahi-Basics Transient Debug', 'Transient Debug', 'administrator', 'mahimahi_basics_transient', 'mahimahi_basics_transient_page');

}

if (!defined('MAHI_HIDDEN')):
	add_action('admin_menu', 'mahimahi_basics_admin_menu');
endif;


function mahimahi_basics_admin_page() {
	global $wpdb;
	?>

	<!-- Fix post_date_gmt -->
	<form action="" method="post">
		<h3>Fix post_date_gmt</h3>
		<p>
			if post_date_gmt is null => post_date_gmt = post_date
		</p>
		<input type="submit" name="fix_post_date_gmt" value="Fix Post Date GMT">
	</form>

	<?php
	if ( isset($_POST['fix_post_date_gmt'])):
		$sql = " UPDATE {$wpdb->posts} SET post_date_gmt = post_date WHERE ( post_date_gmt IS NULL OR post_date_gmt = '1999-11-30 00:00:00' ) ";
		$wpdb->query($sql);
	endif;
	?>
	<hr />

	<!-- Fix Sanitize -->
	<form action="" method="post">
		<h3>Fix sanitize Title/file Name</h3>
		<input type="submit" name="fix_sanitize" value="Fix Sanitize">
		<?php wp_nonce_field ('fix_sanitize', 'fix_sanitize_security');?>
	</form>

	<?php
	if (isset($_POST['fix_sanitize']) && wp_verify_nonce($_POST['fix_sanitize_security'],'fix_sanitize')):

		$args = array(
			'posts_per_page'   => 2500,
			'post_type'		   => 'post', //get_post_types('', 'names'),
			'orderby'          => 'post_date',
			'order'            => 'DESC',
			'meta_query'       => array(
									array(
										'key' => 'sanitized',
										'value' => '0',
										'compare' => 'NOT EXISTS'
									)
								));

		$posts_array = get_posts( $args );


		foreach ($posts_array as $post):
			// applique sanitize
			$newName = apply_filters('sanitize_title', $post->post_name);

			if ( $newName != $post->post_name ):

				$postModified = array(
					'ID'		=> $post->ID,
					'post_name' => $newName
				);
				wp_update_post($postModified);

				xmpr(array(
						'ID' 		=> $post->ID,
						'oldName'	=>	$post->post_name,
						'newName'	=>	$newName
					));
			endif;

			// add meta sanitized
			add_post_meta($post->ID, 'sanitized', 1, false);

		endforeach;

	endif;

	?>
	<?php
}

function mahi_login_headerurl($url) {
	return 'http://'.$_SERVER['HTTP_HOST'];
}
add_filter('login_headerurl', 'mahi_login_headerurl');


add_filter( 'sanitize_file_name_chars', 'mahi_add_chars_to_filter', 10, 2 );
function mahi_add_chars_to_filter ( $special_chars, $filename_raw ) {

	/*logr($filename_raw);
	for($i = 0; $i < strlen($filename_raw); $i++):
		logr($filename_raw[$i]."[".ord($filename_raw[$i])."]");
	endfor;*/

	foreach ( array('é', 'è', 'ê', 'ë', 'ç', 'ù', 'ü', 'û', 'â', 'à', 'î', 'ï', 'ô', 'œ'
						, chr(128), chr(129), chr(153), chr(160), chr(162), chr(165), chr(167), chr(168), chr(169)
						, chr(170), chr(171), chr(174), chr(175), chr(180), chr(185), chr(187), chr(188), chr(195)
						, chr(204), chr(226) ) as $char)
		array_unshift($special_chars, $char);

	return $special_chars;
}


// FONCTION ADMIN CUSTOMISE PAGE

function mahimahi_basics_get_header(){
	global $submenu;
	$menu = $submenu[get_admin_page_parent()];
	?>
	<div id="icon-themes" class="icon32"><br /></div>
	<h2 class="nav-tab-wrapper">
		<?php
		foreach($menu as $tab){
			if(isset($tab[3]))
				echo '<a class="nav-tab '.(($tab[2]==$_GET['page']) ? 'nav-tab-active' : '').'" title="'.$tab[3].'" href="'.get_admin_url().'admin.php?page='.$tab[2].'">'.$tab[3].'</a>';
		}
		?>
	</h2>
	<?php
}

function mahimahi_basics_add_custom_admin_box($title, $content){
	?>
	<div class="metabox-holder wrap">
		<div class="postbox">
			<div class="group" style="display: block;">
				<h3 class="hndle"><?php echo $title; ?></h3>
				<div class="inside">
					<?php echo $content; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function mahimahi_basics_start_custom_admin_box($title =  "&nbsp;"){
	?>
	<div class="metabox-holder wrap">
		<div class="postbox">
			<div class="group" style="display: block;">
				<h3 class="hndle"><?php echo $title; ?></h3>
				<div class="inside">
	<?php
}

function mahimahi_basics_end_custom_admin_box(){
	?>
				</div>
			</div>
		</div>
	</div>
	<?php
}




//add_filter('manage_posts_columns', 'posts_columns_attachment_count', 5);
//add_action('manage_posts_custom_column', 'posts_custom_columns_attachment_count', 5, 2);
function posts_columns_attachment_count($defaults){
    $defaults['wps_post_attachments'] = __('Attachments');
    return $defaults;
}
function posts_custom_columns_attachment_count($column_name, $id){
        if($column_name === 'wps_post_attachments'){
        $attachments = get_children(array('post_parent'=>$id));
        $count = count($attachments);
        if($count !=0){echo $count;}
    }
}


define('MAHI_DROPDOWN_CATS_MAX', 300);

function mahi_wp_dropdown_cats($output, $args = null) {
	if ( is_array($args) ):
		if ( ! $args['name'] )
			return '';
		if ( substr_count($output, '</option>') > MAHI_DROPDOWN_CATS_MAX || $args['force_select2'] ):
			$output = preg_replace("#\s*<option class[^>]+>[^<]+</option>\s*#", '', $output);
			$output = preg_replace("#<select#", '<select class="select2"', $output);
			if( $args['selected'] )
				$selected = get_term($args['selected'], $args['taxonomy']);
			$output = '<input type="hidden" class="bigdrop" id="bigdrop-'.$args['taxonomy'].'" name="'.$args['name'].'" data-title="'.$args['show_option_all'].'" style="width:250px;" data-taxonomy="'.$args['taxonomy'].'" data-selected="'.$selected->term_id.'" data-selected-title="'.$selected->name.'" />';
			return $output;
		endif;
	endif;

	return $output;
}
add_filter('wp_dropdown_cats', 'mahi_wp_dropdown_cats', 10, 2);



function mahi_wp_dropdown_categories( $args = '' ) {

	$r = wp_parse_args( $args );

	extract($r);

	$r['echo'] = false;

	if ( ! $args['force_select2'] ):

		$categories = get_terms( $taxonomy, $r );
		if ( count($categories) < MAHI_DROPDOWN_CATS_MAX):

			if ( is_local() )
				$r['number'] = 5;
			$output = wp_dropdown_categories($r);

		else:

			$r['force_select2'] = true;

		endif;

	endif;

	$output = apply_filters( 'wp_dropdown_cats', $output, $r );

	if ( $echo )
		echo $output;

	return $output;
}



add_action('wp_ajax_mahi_wp_dropdown', 'mahi_wp_dropdown_callback');

function mahi_wp_dropdown_callback() {
	global $wpdb; // this is how you get access to the database

	@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

	foreach(get_terms($_GET['taxonomy'], array(
		'number' 	=> $_GET['page_limit'],
		'offset'	=> $_GET['page_limit'] * ($_GET['page'] - 1),
		'search'	=>	$_GET['q']
	)) as $term):
		$t = new StdClass;
		$t->id = $term->term_id;
		$t->text = $term->name;
		$terms[] = $t;
	endforeach;

	print json_encode($terms);

	die(); // this is required to return a proper result
}

global $shortcode_scripts;
function mahi_javascript_shortcode( $atts, $content ){
	global $shortcode_scripts;
	$shortcode_scripts[] = preg_replace("(&lsquo;|&rsquo;)", "'", strip_tags(convert_smart_quotes(utf8_decode(html_entity_decode($content)))));
	// logr($content);
	// return "<script>".convert_smart_quotes(html_entity_decode($content))."</script>";
}
add_shortcode( 'javascript', 'mahi_javascript_shortcode' );

function mahi_javascript_shortcode_footer() {
	global $shortcode_scripts;
	if ( count($shortcode_scripts) ):
		?>
		<script>
		/* Shortcode Javascripts */
		<?php
		print implode(PHP_EOL, $shortcode_scripts);
		?>
		</script>
		<?php
	endif;
}
add_action("wp_footer", "mahi_javascript_shortcode_footer", 99);
