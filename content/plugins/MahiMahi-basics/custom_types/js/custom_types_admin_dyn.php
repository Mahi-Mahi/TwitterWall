<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

header("Content-Type: text/javascript");

if ( defined("GMAP_API_KEY") ):
	?>
	var GMAP_API_KEY = '<?php print constant("GMAP_API_KEY") ?>';
	<?php
endif;

?>