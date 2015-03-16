<?php
/*
<p><!--more-->   =>   <!--more--><p>

si balise ouvrante suivi de more alors => more suivi de balise ouvrante



*/
function basics_fix_more($data) {

	$data['post_content'] = preg_replace("#<([\w\d]+)>\s*<!--more-->#", "<!--more--><\\1>" , $data['post_content']);

	return $data;
}
add_filter('wp_insert_post_data', 'basics_fix_more');


