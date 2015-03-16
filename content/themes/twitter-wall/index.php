<?php
if ( !empty($_POST) ):
	get_template_part('loop');
else:
	?>
	<html>
		<head>
		</head>
		<body>
			<ul>
				<?php
				get_template_part('loop')
				?>
			</ul>

			<script src="/content/themes/twitter-wall/js/vendor/jquery-2.1.3.min.js"></script>
			<script src="/content/themes/twitter-wall/js/main.js"></script>
			<script src="//platform.twitter.com/widgets.js" charset="utf-8"></script>

		</body>
	</html>
	<?php
endif;