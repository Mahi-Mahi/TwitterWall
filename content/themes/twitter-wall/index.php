<?php
if ( !empty($_POST) ):
	get_template_part('loop');
else:
	?>
	<!doctype html>
	<html>
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<title><?php the_field('title', 'option') ?></title>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
			<link rel="stylesheet" href="/tweetwall/content/themes/twitter-wall/style.css">
			<meta http-equiv="refresh" content="30">
			<style>
			.header {
				background: url('<?php print get_post(get_field('banner', 'option'))->guid ?>') left top no-repeat;
				background-size: contain;
			}
			</style>
		</head>
		<body>
			<div class="wrapper">
				<div class="header"></div><!-- .header -->
				<div class="container">
					<div class="main">
						<div class="sidebar">
							<div class="sidebar__profile">
								<img src="<?php print get_post(get_field('logo', 'option'))->guid ?>" alt="" />
							</div>
							<div class="sidebar__edito">
							<h1>
								<a href="#" target="_blank">
									<?php the_field('title', 'option') ?>
									<span><?php the_field('twitter_name', 'option') ?></span>
								</a>
							</h1>
							<p>
								<span><?php the_field('hashtag', 'option') ?></span> : <?php print get_field('description', 'option') ?>
							</p>
							<p>
								 <i class="fa fa-map-marker"></i> <?php the_field('city', 'option') ?>
							</p>
							<p>
								<i class="fa fa-link"></i> <a href="<?php the_field('link', 'option') ?>"><?php print preg_replace("#^http://(.*)/?$#", "\\1", get_field('link', 'option')) ?></a>
							</p>
							</div>
						</div><!-- .sidebar -->
						<div class="content">
							<ul class="unstyled">
							<?php
							get_template_part('loop')
							?>
							</ul>
						</div><!-- .content -->
					</div><!-- .main -->
				</div><!-- .container -->
			</div><!-- .wrapper -->
			<script src="/tweetwall/content/themes/twitter-wall/js/vendor/jquery-2.1.3.min.js"></script>
			<script src="/tweetwall/content/themes/twitter-wall/js/main.js"></script>
			<script src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
		</body>
	</html>
	<?php
endif;