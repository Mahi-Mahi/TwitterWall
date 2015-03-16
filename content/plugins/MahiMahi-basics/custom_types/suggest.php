<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-admin/admin.php');
$taxonomies = get_taxonomies();
$main_terms = wp_get_object_terms($_GET['post_id'], $taxonomies, array('fields' => 'ids'));
?>
<h2>Articles ayant des tags communs avec l'article en cours</h2>
<ul id="<?php print $_GET['target'] ?>_related" class="suggest">
	<?php
	$related = get_post_meta($_GET['post_id'], preg_replace("#^[^_]+_#", '', $_GET['target']));
	query_posts( array('post__in' => mahi_get_related_on_taxonomies($_GET['post_id'], 15, array('category', 'post_tag'), true)));
	while(have_posts()):
		the_post();
		if ( in_array(get_the_ID(), $related) )
			continue;
		$related_terms = wp_get_object_terms(get_the_ID(), $taxonomies, array('fields' => 'ids'));
		$common_terms = array_intersect($main_terms, $related_terms);
		$common_terms = get_terms($taxonomies, array('include' => $common_terms, 'fields' => 'all'));
		$tags = implode(', ', wp_list_pluck($common_terms, 'name'));
		?>
		<li>
			<a href="#" onClick="post_reference_add(jQuery('#<?php print $_GET['target'] ?>'), {id:<?php the_ID() ?>,label:'<?php the_title_attribute() ?>'});jQuery(this).parent().remove();">add</a>
			<span title="tags comuns : <?php print $tags ?>"><?php the_title() ?></span>
		</li>
		<?php
	endwhile;
	?>
</ul>
<script type="text/javascript" charset="utf-8">
	jQuery(document).ready(function(){
		jQuery('ul.suggest span').tooltip();
	});
</script>