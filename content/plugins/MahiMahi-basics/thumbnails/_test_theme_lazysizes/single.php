<?php
get_header();
?>

<main>

	<article>
		<h1><?php the_title() ?></h1>
		<?php
		thumbnail('large');
		?>
		<xmp>
		<?php
		thumbnail('large');
		?>
		</xmp>
	</article>

	<ul class="related">
		<?php
		query_posts(array());
		while(have_posts()):
			the_post();
			if( has_post_thumbnail()):
				?>
				<li>
					<?php
					thumbnail('list');
					?>
				</li>
				<?php
			endif;
		endwhile;
		?>
	</ul>

</main>

<?php

get_footer();
