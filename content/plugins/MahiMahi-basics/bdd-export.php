<?php
/*
Plugin Name: Bdd Export
*/
if ( file_exists($_SERVER['DOCUMENT_ROOT'].'/wp/wp-load.php') )
	require_once($_SERVER['DOCUMENT_ROOT'].'/wp/wp-load.php');

if ( isset($_GET['bdd_export_key']) ):

	define('BDD_EXPORT_URL', WP_PLUGIN_URL.'/bdd-export/bdd-export.php');

	if ( $_GET['bdd_export_key'] == get_option( 'bdd_export_key' )):

		$dirname = $_SERVER['DOCUMENT_ROOT']."/content/bdd-export/";
		@mkdir($dirname, 0777, true);
		$cmd = "chmod 777 -R ".$dirname;
		exec($cmd);
		$cmd = "chown www-data.www-data -R ".$dirname;
		exec($cmd);
		$filename = constant('DB_NAME')."_".date('Ymd-His').".sql";
		$cmd = " mysqldump -u ".constant('DB_USER')." --password=\"".preg_replace('#\$#', "\\\\$", constant('DB_PASSWORD'))."\" -h ".constant('DB_HOST')." ".constant('DB_NAME')." > ".$dirname.$filename;
		exec($cmd);
		$cmd = " gzip -f ".$dirname.$filename;
		exec($cmd);
		$filename = $filename.".gz";
		header("Content-Type: application/force-download; name=\"" . $filename . "\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize($dirname.$filename));
		header("Content-Disposition: attachment; filename=\"" . $filename . "\"");
		header("Expires: 0");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");	readfile($dirname.$filename);
		exit();

	endif;

endif;

add_action('admin_menu', 'bdd_export_menu');

function bdd_export_menu() {
  add_options_page('BDD Export', 'BDD Export', 'administrator', 'bdd_export_menu_main', 'bdd_export_option');
}

function bdd_export_option() {
	if ( $_POST ):
		update_option( 'bdd_export_key', uniqid() );
	endif;
	echo '<div class="wrap">';
	echo '<form action="" name="bdd_export" method="post">';
	$opt_val = get_option( 'bdd_export_key' );
	echo '<p>';
		echo 'secret key : '.$opt_val;
		echo '</p>';
	echo '<p>';
		echo 'download link : '.constant('BDD_EXPORT_URL').'?bdd_export_key='.$opt_val;
		echo '</p>';
	echo '<p>';
		echo '<input type="submit" name="submit" value="Generate new secret key">';
		echo '</p>';
	echo '</form>';
	echo '</div>';
}

