<?php


/*
Clear HomePage cache
*/
function w3tc_clean_cache($uris) {

	if ( ! defined('W3TC_LIB_W3_DIR') )
		return;

	require_once W3TC_LIB_W3_DIR . '/PgCacheFlush.php';

	$w3_pgcache_flush = new W3_PgCacheFlush();

	if ( ! is_array($uris) )
		$uris = array($uris);

	if (count($uris)) {
		$cache = & $w3_pgcache_flush->_get_cache();
		$mobile_groups = $w3_pgcache_flush->_get_mobile_groups();
		$referrer_groups = $w3_pgcache_flush->_get_referrer_groups();
		$encryptions = $w3_pgcache_flush->_get_encryptions();
		$compressions = $w3_pgcache_flush->_get_compressions();

		foreach ($uris as $uri) {
			$w3_pgcache_flush->flush_url(site_url($uri));
			// foreach ($mobile_groups as $mobile_group) {
			// 	foreach ($referrer_groups as $referrer_group) {
			// 		foreach ($encryptions as $encryption) {
			// 			foreach ($compressions as $compression) {
			// 				$page_key = $w3_pgcache_flush->_get_page_key($uri, $mobile_group, $referrer_group, $encryption, $compression);
			// 				$cache->delete($page_key);
			// 			}
			// 		}
			// 	}
			// }
		}

		/**
		 * Purge varnish servers
		 */
		if ( function_exists('w3tc_varnish_flush_url')):
			foreach ($uris as $uri)
				w3tc_varnish_flush_url($uri);
		else:
			if ($w3_pgcache_flush->_config->get_boolean('varnish.enabled')) {
				$varnish = & w3_instance('W3_Varnish');

				foreach ($uris as $uri) {
					$varnish->_purge($uri);
				}
			}
		endif;
	}
}

function varnish_purge($url) {

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PURGE");
	curl_exec($curl);

}



function mahimahi_basics_transient_page() {
	global $wpdb;

	// Fix post_date_gmt
	?>
	<style type="text/css">
	</style>
	<script type="text/javascript">
	jQuery(document).ready(function(){

		jQuery('#check_all_transient').click(function(){
			jQuery('.form-table th input.checkbox').click();
		});

	});
	</script>
	<form action="" method="post">

		<?php

		if ( isset($_POST['delete_transients'])):

			$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' AND option_id IN (".implode(',', $_POST['transient']).") ");

		endif;

		?>

		<h3>Delete Transient</h3>

		<p>
			<label for="transient_pattern">Filter</label>
			<input type="text" id="transient_pattern" name="transient_pattern" value="<?php print $_POST['transient_pattern'] ?>" />

			<input type="submit" name="filter_transient" value="Filter">
		</p>

		<p>
			<input type="submit" name="delete_transients" value="Delete checked transients">

			<table class="form-table">
				<tbody>
					<tr>
					   <th>
							<label for="check_all_transient">
								Check All
								<input type="checkbox" name="check_all_transient" id="check_all_transient">
							</label>
					   </th>
					   <td>
					   </td>
				   </tr>
				<?php

				$sql = "SELECT option_id, option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE '_transient_".$_POST['transient_pattern']."%' ";
				foreach($wpdb->get_results($sql) as $option):
					$option_name = str_replace('_transient_', '', $option->option_name);
					?>
					<tr>
						<th scope="row">
							<input class="checkbox" type="checkbox" id="option_<?php print $option->option_id ?>" name="transient[]" value="<?php print $option->option_id ?>" />
							<label for="option_<?php print $option->option_id ?>"><?php print $option_name ?></label>
						</th>
						<td>
							<input type="text" class="regular-text all-options" value="<?php print str_replace('"', '', substr($option->option_value, 0, 150)) ?>" disabled="disabled" />
						</td>
					</tr>
					<?php
				endforeach;
				?>
				</tbody>
			</table>

			<input type="submit" name="delete_transients" value="Delete checked transients">
		</p>

	</form>
	<hr />
	<?php
}

/*
W3_total_cache PgCache :  cache JSON

patch :
if ($buffer != '' && (w3_is_xml($buffer) || w3_is_json($buffer) || w3_is_jsonp($buffer)))


*/


/**
 * Check if content is JSON
 *
 * @param string $content
 * @return boolean
 */
if ( ! function_exists('w3_is_json')):
function w3_is_json($content) {
	return json_decode($content) !== NULL;
}
endif;

/**
 * Check if content is JSONP
 *
 * @param string $content
 * @return boolean
 */
if ( ! function_exists('w3_is_jsonp')):
function w3_is_jsonp($content) {
	// Extract json
	$stripped = preg_replace("/^\s*\w+\s*\(/", "", $content);
	$stripped = preg_replace("/\)\s*$/", "", $stripped);
	return w3_is_json($stripped);
}
endif;
