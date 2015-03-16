<?php

twitterwall_get_posts();

$banned_authors = get_posts(array(
	'post_type'		=>	'banned_account',
	'posts_type'	=>	-1
));
$banned_authors = wp_list_pluck($banned_authors, 'post_title');

$words = get_posts(array(
	'post_type'		=>	'banned_word',
	'posts_type'	=>	-1
));
$banned_words = array();
foreach($words as $word)
	$banned_words[] = addslashes($word->post_title);

$banned_words = "#".implode("|", $banned_words)."#";

$posts = array();

$args = array(
	'posts_per_page'	=>	-1,
	'date_query'	=>	array(
		array(
			'after'	=>	date('Y-m-d H:i:s', strtotime('-1 WEEK'))
		)
	)
);

if ( is_local() && empty($_POST)):
	$offset = 5;
	$do_offset = true;
endif;

$updates = 15;

$nb_posts = 15;

query_posts($args);
while(have_posts()):

	the_post();
	$content = strip_tags(get_the_content());
	$embed = strip_tags(apply_filters('the_content', get_the_meta('url')), "<blockquote><a><p>");
	$author = get_the_meta('author');
	$id_str = get_the_meta('origin_id');

	if ( in_array($author, $banned_authors))
		continue;

	if ( preg_match($banned_words, $content) )
		continue;

	if ( $do_offset && $offset-- > 0 )
		continue;

	if ( !empty($_POST) ):
		$posts[] = array(
			'id'		=>	$id_str,
			'content'	=>	$embed
		);
	else:
		?>
		<li data-id="<?php print $id_str ?>">
			<?php
			print $embed;
			?>
		</li>
		<?php
	endif;


	if ( ! empty($_POST) && ! $updates-- )
		break;

	if ( ! $nb_posts-- )
		break;

endwhile;
if ( !empty($_POST) ):
	header('Content-Type: application/json');
	print json_encode(array_slice($posts, 0, 5));
	die();
endif;