<?php

/*
Plugin Name: MahiMahi Basics
*/

define('MahiMahiBasics_DIR', constant('WP_PLUGIN_DIR').'/'.basename(dirname(__FILE__)));
define('MahiMahiBasics_PATH', '/'.str_replace(constant('ABSPATH'), '', constant('MahiMahiBasics_DIR')));
//define('MahiMahiBasics_URL', constant('WP_PLUGIN_URL').'/'.basename(dirname(__FILE__))); problème avec SSL (WP PLUGIN URL)
define('MahiMahiBasics_URL', substr( plugin_dir_url(__FILE__) , 0 , -1 ) );

require_once(constant('MahiMahiBasics_DIR').'/functions.php');
require_once(constant('MahiMahiBasics_DIR').'/front.php');
require_once(constant('MahiMahiBasics_DIR').'/query.php');
require_once(constant('MahiMahiBasics_DIR').'/comments.php');
require_once(constant('MahiMahiBasics_DIR').'/page.php');
require_once(constant('MahiMahiBasics_DIR').'/post-template.php');
require_once(constant('MahiMahiBasics_DIR').'/thumbnails.php');
require_once(constant('MahiMahiBasics_DIR').'/category.php');
require_once(constant('MahiMahiBasics_DIR').'/user.php');
require_once(constant('MahiMahiBasics_DIR').'/utils.php');
require_once(constant('MahiMahiBasics_DIR').'/tuning.php');
require_once(constant('MahiMahiBasics_DIR').'/local_replace.php');
require_once(constant('MahiMahiBasics_DIR').'/dashboard.php');
require_once(constant('MahiMahiBasics_DIR').'/widgets.php');
require_once(constant('MahiMahiBasics_DIR').'/embed.php');
require_once(constant('MahiMahiBasics_DIR').'/date.php');
require_once(constant('MahiMahiBasics_DIR').'/nav.php');
require_once(constant('MahiMahiBasics_DIR').'/taxonomy.php');
// require_once(constant('MahiMahiBasics_DIR').'/taxonomy_filter.php');
require_once(constant('MahiMahiBasics_DIR').'/content.php');
require_once(constant('MahiMahiBasics_DIR').'/geolocation.php');
require_once(constant('MahiMahiBasics_DIR').'/upload.php');
require_once(constant('MahiMahiBasics_DIR').'/remove_title_attributes.php');
require_once(constant('MahiMahiBasics_DIR').'/related_on_multiple_taxonomies.php');

require_once(constant('MahiMahiBasics_DIR').'/php-browser-detection/php-browser-detection.php');

require_once(constant('MahiMahiBasics_DIR').'/template_fields.php');

require_once(constant('MahiMahiBasics_DIR').'/sanitize.php');

require_once(constant('MahiMahiBasics_DIR').'/security.php');

require_once(constant('MahiMahiBasics_DIR').'/seo.php');

require_once(constant('MahiMahiBasics_DIR').'/admin.php');
require_once(constant('MahiMahiBasics_DIR').'/tinymce.php');

require_once(constant('MahiMahiBasics_DIR').'/rewrite.php');

require_once(constant('MahiMahiBasics_DIR').'/cache_manager.php');

require_once(constant('MahiMahiBasics_DIR').'/debug.php');


function mahi_cli_init() {
	if ( defined('WP_CLI') && WP_CLI ):
		// define('WP_CLI', true);
		require_once(MahiMahiBasics_DIR.'/wp-cli.php');
	endif;
}
add_action( 'plugins_loaded', 'mahi_cli_init' );


if ( ! defined('PHP_TAB') )
	define('PHP_TAB', "\t");

if ( ! defined('WP_SITEURL' ) )
	define('WP_SITEURL',    'http://' . $_SERVER['HTTP_HOST'] );
if ( ! defined('WP_HOME' ) )
	define('WP_HOME',    'http://' . $_SERVER['HTTP_HOST'] );

function mahi_this_plugin_first() {
	// ensure path to this file is via main wp plugin path
	$wp_path_to_this_file = preg_replace('/(.*)plugins\/(.*)$/', WP_PLUGIN_DIR."/$2", __FILE__);
	$this_plugin = plugin_basename(trim($wp_path_to_this_file));
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search($this_plugin, $active_plugins);
	if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, $this_plugin);
		update_option('active_plugins', $active_plugins);
	}
}
add_action("activated_plugin", "mahi_this_plugin_first");

function mahibasics_restrict_access() {
	if ( is_local() or is_dev() ):
		delete_option('blog_public');
	endif;
}
//add_action('init', 'mahibasics_restrict_access');

function mahibasics_register_scripts() {
	global $wp_version;

	#PAGE IS ADMIN
	if ( is_admin() ) :

		wp_register_script('jquery-utils', constant('MahiMahiBasics_URL').'/js/jquery.utils.js', false, null, false);

		global $wp_version;


		if ( version_compare($wp_version, '3.2', '<=') ):

			# DEREGISTER DEFAULT JQUERY-UI INCLUDES
			wp_deregister_script('jquery-ui-core');
			wp_deregister_script('jquery-ui-dialog');
			wp_deregister_script('jquery-ui-widget');
			wp_deregister_script('jquery-ui-mouse');
			wp_deregister_script('jquery-ui-position');
			wp_deregister_script('jquery-ui-draggable');
			wp_deregister_script('jquery-ui-droppable');
			wp_deregister_script('jquery-ui-selectable');
			wp_deregister_script('jquery-ui-resizable');
			wp_deregister_script('jquery-ui-sortable');
			wp_deregister_script('jquery-ui-tabs');
			wp_deregister_script('jquery-ui-slider');

			# REGISTER JQUERY-UI 1.8 ( from github repo )
			wp_register_script('jquery-ui-core', MahiMahiBasics_URL.'/js/ui/jquery.ui.core.js', array('jquery'), '1.8.16', false);
			wp_register_script('jquery-ui-widget', MahiMahiBasics_URL.'/js/ui/jquery.ui.widget.js', array('jquery-ui-core'), '1.8.16', false);
			wp_register_script('jquery-ui-mouse', MahiMahiBasics_URL.'/js/ui/jquery.ui.mouse.js', array('jquery-ui-widget'), '1.8.16', false);
			wp_register_script('jquery-ui-position', MahiMahiBasics_URL.'/js/ui/jquery.ui.position.js', array('jquery-ui-core'), '1.8.16', false);
			wp_register_script('jquery-ui-draggable', MahiMahiBasics_URL.'/js/ui/jquery.ui.draggable.js', array('jquery-ui-mouse', 'jquery-ui-position'), '1.8.16', false);
			wp_register_script('jquery-ui-droppable', MahiMahiBasics_URL.'/js/ui/jquery.ui.droppable.js', array('jquery-ui-draggable'), '1.8.16', false);
			wp_register_script('jquery-ui-resizable', MahiMahiBasics_URL.'/js/ui/jquery.ui.resizable.js', array('jquery-ui-mouse'), '1.8.16', false);
			wp_register_script('jquery-ui-selectable', MahiMahiBasics_URL.'/js/ui/jquery.ui.selectable.js', array('jquery-ui-mouse'), '1.8.16', false);
			wp_register_script('jquery-ui-sortable', MahiMahiBasics_URL.'/js/ui/jquery.ui.sortable.js', array('jquery-ui-mouse'), '1.8.16', false);
			wp_register_script('jquery-ui-tabs', MahiMahiBasics_URL.'/js/ui/jquery.ui.tabs.js', array('jquery-ui-widget'), '1.8.16', false);
			wp_register_script('jquery-ui-slider', MahiMahiBasics_URL.'/js/ui/jquery.ui.slider.js', array('jquery-ui-widget', 'jquery-ui-mouse'), '1.8.16', false);

		endif;

		if ( !version_compare($wp_version, '3.4', '>=') ):
			wp_register_script('jquery-ui-datepicker', MahiMahiBasics_URL.'/js/ui/jquery.ui.datepicker.js', array('jquery-ui-core'), '1.8.16', false);
		endif;
		wp_register_style('jquery-ui-datepicker', MahiMahiBasics_URL.'/css/ui/themes/base/jquery.ui.datepicker.css');

		if ( defined('CustomTypes_URL')):
			wp_register_script('slideraccess', CustomTypes_URL.'/js/slideraccess.js');
			wp_register_script('datetimepicker', CustomTypes_URL.'/js/datetimepicker.js', array('jquery-ui-datepicker', 'jquery-ui-slider', 'slideraccess'));
			wp_register_style('datetimepicker', CustomTypes_URL.'/css/datetimepicker.css');
			wp_register_style('jquery-ui-button', MahiMahiBasics_URL.'/css/ui/themes/base/jquery.ui.button.css');
		endif;

		wp_register_script('jquery-editable', MahiMahiBasics_URL.'/js/jquery.editable.js', array('jquery'), '1.8.3', false);

		wp_register_script('jquery-ui-autocomplete', MahiMahiBasics_URL.'/js/ui/jquery.ui.autocomplete.js', array('jquery-ui-widget', 'jquery-ui-position'), '1.8.16', false);
		wp_register_style('jquery-ui-autocomplete', MahiMahiBasics_URL.'/css/ui/themes/base/jquery.ui.autocomplete.css');

		// wp_register_script('jquery-tooltip', MahiMahiBasics_URL.'/js/jquery.tooltip.js', array('jquery'), '1.3', false);
		// wp_register_style('jquery-tooltip', MahiMahiBasics_URL.'/css/jquery.tooltip.css');

		wp_register_script('gmaps', '//maps.google.com/maps/api/js?sensor=false&libraries=places&region=FR&language=' . ( function_exists('wpml_get_language') ? preg_replace("#lang\-([a-z]{2})#", "\\1", wpml_get_language()) : 'fr'), false, false, false);
		/* deleted http: */
		wp_register_style('jquery-ui-base', MahiMahiBasics_URL.'/css/ui/themes/base/jquery.ui.base.css');
		wp_register_style('jquery-ui-theme', MahiMahiBasics_URL.'/css/ui/themes/base/jquery.ui.theme.css');

		wp_register_script('addresspicker', MahiMahiBasics_URL.'/js/jquery.ui.addresspicker.js', array('jquery-ui-autocomplete', 'gmaps'));

		if ( defined('CustomTypes_URL')):
			wp_register_script('date', CustomTypes_URL.'/js/date.js', array('jquery-ui-datepicker'));
			wp_register_script('date_fr', CustomTypes_URL.'/js/date_fr.js', array('jquery-ui-datepicker'));
		endif;

		wp_register_script('taxonomy', MahiMahiBasics_URL.'/js/taxonomy.js');

		wp_register_style('mahi-admin', MahiMahiBasics_URL.'/css/admin.css');
		// wp_register_style('chosen', MahiMahiBasics_URL.'/css/chosen.css');
		wp_register_style('select2', MahiMahiBasics_URL.'/css/select2.css');

		// wp_register_script('jquery-chosen', MahiMahiBasics_URL.'/js/chosen.jquery.js');
		wp_register_script('jquery-select2', MahiMahiBasics_URL.'/js/select2.js');
		wp_register_script('mahi-admin', MahiMahiBasics_URL.'/js/admin.js');


	endif;
}
add_action('init', 'mahibasics_register_scripts', 99);

function mahibasics_enqueue_scripts() {
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-widget');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('datetimepicker');
	wp_enqueue_script('jquery-ui-sortable');

	wp_enqueue_script('jquery-ui-autocomplete');

	wp_enqueue_style('wp-jquery-ui-dialog');
	wp_enqueue_style('jquery-ui-theme');
	wp_enqueue_style('jquery-ui-datepicker');
	wp_enqueue_style('datetimepicker');
	wp_enqueue_style('jquery-ui-button');
	wp_enqueue_style('jquery-ui-autocomplete');

	wp_enqueue_style('jquery-tooltip');
	wp_enqueue_script('jquery-tooltip');

	wp_enqueue_script('jquery-utils');

	wp_enqueue_script('taxonomy');

	wp_enqueue_script('jquery-select2');
	wp_enqueue_script('mahi-admin');

	wp_enqueue_style('mahi-admin');
	wp_enqueue_style('select2');

}
add_action('admin_enqueue_scripts', 'mahibasics_enqueue_scripts');

if ( ! defined('GMAP_API_KEY') )
	define('GMAP_API_KEY', 'ABQIAAAA1ayGMcTPxxXRoOhC91BZdRT2yXp_ZAY8_ufC3CFXhHIE1NvwkxSuqvDUoMh3ueb7luBKOZWcM0mToA');


function MahiBasics_init() {
	$plugin_dir = basename(dirname(__FILE__));
	load_plugin_textdomain( 'MahiMahi-basics', false, $plugin_dir );
}
add_action('plugins_loaded', 'MahiBasics_init');


