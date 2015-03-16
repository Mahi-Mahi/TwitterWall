<?php

function simple_page_nav($args = array()) {

	$simple_page_nav_key = 'simple_page_nav_'.md5(serialize($args));

	$current_user = wp_get_current_user();
	
	$simple_page_nav_key .= '-'.$current_user->ID;

	if (false === ( $output = get_transient($simple_page_nav_key) ) ) :

		if ( $args['depth'] === 0 )
			return;

		$default_args = array(
														'post_parent' => 0,
														'level' => 1,
														'ul' => true
													);

		$args = array_merge($default_args, $args);
		extract($args);

		if ( isset($args['levels'][$args['level']]) )
			extract($args['levels'][$args['level']]);

		$pages = get_posts(array('post_parent' => $post_parent, 'post_type' => 'page', 'numberposts' => 99, 'orderby' => 'menu_order', 'order' => 'ASC'));

		$args['level']++;
		if ( isset($args['depth']) )
			$args['depth']--;

		foreach($pages as $page ):
	
			if ( taxonomy_exists('navigation') && has_term('not_in_sidebar', 'navigation', $page->ID) )
				continue;

			if ( function_exists('simple_page_nav_page_enabled') )
				if ( ! simple_page_nav_page_enabled($page->ID) )
					continue;

			$output .= str_repeat("\t", $level).'<li><a href="'.get_permalink($page->ID).'">'.$page->post_title.'</a>';

			$args['post_parent'] = $page->ID;

			$output .= simple_page_nav($args);

			$output .= str_repeat("\t", $level).'</li>'.PHP_EOL;

		endforeach;
	
		if ( $output && $ul ):
			$classes = array("nav_lvl-".$level);
			if ( isset($ul_class))
				$classes[] = $ul_class;
			$output = '<ul'.(($classes)?' class="'.implode(',', $classes).'"':'').(($ul_id)?' id="'.$ul_id.'"':'').'>'.PHP_EOL.$output.'</ul>'.PHP_EOL;
		endif;

    set_transient($simple_page_nav_key, $output, ( ($current_user->ID) ? 60 : 60 * 60) );

	endif;

	return $output;

}

?>