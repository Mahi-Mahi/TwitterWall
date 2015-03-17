<!doctype html>
<?php
if ( !empty($_POST) ):
	get_template_part('loop');
else:
	?>
	<html>
		<head>
	        <meta charset="utf-8">
	        <meta http-equiv="X-UA-Compatible" content="IE=edge">
	        <title>Expo Wave</title>
	        <meta name="description" content="Expo Wave -  #WaveMarseille : Wave, quand l’ingéniosité collective change le monde.">
	        <meta name="viewport" content="width=device-width, initial-scale=1">
	        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
	        <link rel="stylesheet" href="/tweetwall/content/themes/twitter-wall/style.css">
		</head>
		<body>
	        <div class="wrapper">
	            <div class="header"></div><!-- .header -->
	            <div class="container">
	                <div class="main">
	                    <div class="sidebar">
	                        <div class="sidebar__profile">
	                            <img src="/tweetwall/content/themes/twitter-wall/images/profile.png" alt="" />
	                        </div>
	                        <div class="sidebar__edito">
	                        <h1>
	                            <a href="#" target="_blank">
	                                Expo Wave
	                                <span>@expowave</span>
	                            </a>
	                        </h1>
	                        <p>
	                            #WaveMarseille : Wave, quand l’ingéniosité collective change le monde. <br />
	                            Du 20 au 29 mars à La Friche La Belle de Mai.
	                        </p>
	                        <p>
						     	 <i class="fa fa-map-marker"></i> Marseille
	                        </p>
	                        <p>
	                        	<i class="fa fa-link"></i> <a href="http://www.wave-innovation.com/marseille/">www.wave-innovation.com/marseille</a>
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