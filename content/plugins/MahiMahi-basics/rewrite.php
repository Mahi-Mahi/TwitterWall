<?php

function mahimahi_basics_rewrite_page() {
	global $wp_rewrite;
	?>
	<h2>wp_rewrite->rewrite_rules()</h2>
	<?php
	xmpr($wp_rewrite->rewrite_rules());
	?>
	<?php
}

