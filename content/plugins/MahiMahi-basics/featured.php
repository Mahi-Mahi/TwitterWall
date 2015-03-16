<?php

function mahi_featured_menu() {

	foreach(apply_filters('mahi_featured', array()) as $featured => $args):
		if ( $args['parent'] )
			add_submenu_page($args['parent'], 	$args['title'], $args['title'], 'edit_others_posts', 'mahi_featured_admin_'.$featured, create_function('', "mahi_featured_admin('".$featured."');"));
		else
			add_dashboard_page( 				$args['title'], $args['title'], 'edit_others_posts', 'mahi_featured_admin_'.$featured, create_function('', "mahi_featured_admin('".$featured."');"));
	endforeach;

}
add_action('admin_menu', 'mahi_featured_menu');



function mahi_featured_admin($featured = null, $exclude = null) {

	mahimahi_basics_get_header();

	if ( ! $featured )
		$featured = 'homepage';

	$featured_args = apply_filters('mahi_featured', array());
	$featured_title = $featured_args[$featured];

	if ( $_POST ):

		$selected = array();
		if ( isset($_POST['clear_homepage']) ):
			w3tc_clean_cache('');
		elseif ( isset($_POST['featured']) ):
			$selection = array();
			foreach($_POST['featured'] as $post_id):
				if ( $post_id ):
					$selection[] = apply_filters('featured_save',
										array(
											'ID'		=>	$post_id,
											'title'		=>	stripslashes($_POST['title_'.$post_id]),
											'taxonomy'	=>	isset($_POST['taxonomy_'.$post_id]) ? $_POST['taxonomy_'.$post_id] : null,
											'date'		=>	$_POST['date_'.$post_id],
											'end_date'	=>	$_POST['end_date_'.$post_id],
											'excerpt'	=>	stripslashes($_POST['excerpt_'.$post_id]),
											'url'		=> $_POST['url_'.$post_id]
										),
										$featured
									);
				endif;
			endforeach;

			update_option('featured-'.$featured, $selection);

		endif;

	endif;

	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'jquery-ui-droppable' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'jquery-editable' );
	wp_enqueue_script( 'datetimepicker' );
	// wp_enqueue_style( '/wp-admin/css/colors-fresh.css' );

	$displayed_items = 10;
	$selection = array_slice(get_option('featured-'.$featured, array()), 0, 100);

	$exclude = wp_list_pluck($selection, 'ID');
	?>
	<div class="wrap">
		<h2>Featured</h2>
		<div id="nav-menus-frame">
			<form method="post" id="featured_form" name="featured_<?php print $featured ?>">
			<input type="hidden" name="featured[]" value="" />
			<div style="float:right;margin-bottom:15px;">
				<string>Vide le cache</string>
				<input type="submit" name="clear_homepage" value="homepage" />
			</div>
			<div id="menu-settings-column" class="metabox-holder">

				<div class="postbox">
					<h3><span>Rechercher</span></h3>
					<div class="inside">
						<div class="customlinkdiv">
							<div class="featured_tools">
								<input type="hidden" class="featured" value="<?php print $featured ?>" />
								<input type="text" class="search" id="featured_autocomplete" placeholder="rechercher..." />
							</div>
						</div><!-- .customlinkdiv -->
					</div><!-- .inside -->
				</div><!-- .postbox -->

				<ul id="available_list">
					<?php
					mahi_featured_available_list($featured, $exclude, $featured_args['query']);
					?>
				</ul>

			</div><!-- #menu-settings-column -->
			<div id="menu-management-liquid">
				<div id="menu-management">
					<div class="menu-edit">
						<div id="nav-menu-header" class="clearfix">
							<div class="major-publishing-actions">
								<div class="publishing-action">
									<div class="featured_tools">
										<input type="submit" class="save button-primary" value="Enregistrer" />
									</div>
								</div><!-- .publishing-action -->
							</div><!-- .major-publishing-actions -->
						</div><!-- #nav-menu-header -->
						<div id="post-body">
							<div id="post-body-content">
								<ul id="featured_list">
									<?php
									$idx = 1;
									if ( is_array($selection) ):
										foreach($selection as $selected):
											if ( $selected['taxonomy'] )
												mahi_featured_term_item($selected, $idx, $featured);
											else
												mahi_featured_post_item($selected, $idx, $featured);
											$exclude[] = $selected['ID'];
											$idx++;
										endforeach;
									endif;
									?>
								</ul>
							</div><!-- #post-body-content -->
						</div><!-- #post-body -->
						<div id="nav-menu-footer" class="clearfix">
							<div class="major-publishing-actions">
								<div class="publishing-action">
									<div class="featured_tools">
										<input type="submit" class="save button-primary" value="Enregistrer" />
									</div>
								</div><!-- .publishing-action -->
							</div><!-- .major-publishing-actions -->
						</div><!-- #nav-menu-footer -->
					</div><!-- .menu-edit -->
				</div><!-- #menu-management -->
			</div><!-- #menu-management-liquid -->
			</form>
		</div><!-- #nav-menus-frame -->

	<script type="text/javascript">
	jQuery(function() {
		setEditable = function() {
			jQuery('#featured_list h3').editable(function(value, settings) {
				jQuery(this).parent().parent().find('input.title').val(value);
				return(value);
			}, {
					onblur: 'submit'
			});
			jQuery('#featured_list blockquote').editable(function(value, settings) {
				jQuery(this).parent().find('textarea.excerpt').html(value);
				return(value);
			}, {
					type: 'textarea',
					onblur: 'submit'
			});
		}
		setEditable();
		jQuery('#featured_list, #available_list').sortable({
			connectWith: '#featured_list, #available_list',
			cursor: 'move',
			stop: function() {
				setEditable();
				jQuery('#featured_list li').each(function(i) {
					jQuery(this).find('em.idx').text(i+1);
				});
			}
		});
		jQuery('.featured_tools input.save').on('click', function(){
			jQuery('#available_list input').remove();
			jQuery('#featured_form').submit();
		});

		refreshAvailable = function() {
			data = {action: 'featured_available_list', featured: jQuery('.featured_tools input.featured').val(), exclude: jQuery('#featured_list li input.ids').serialize(), q: jQuery('.featured_tools input.search').val()};
			jQuery.post(ajaxurl, data, function(response) {
				jQuery('#available_list').html(response);
			});
		};
		jQuery('.featured_tools input.search').data('timeout', null).keyup(function(){
			clearTimeout(jQuery(this).data('timeout'));
			jQuery(this).data('timeout', setTimeout(function(){
				refreshAvailable();
			}, 500));
		});
		jQuery('.featured-header .remove').on('click', function(){
			console.log(jQuery(this).parents('li'));
			jQuery(this).parents('li').remove();
			refreshAvailable();
		});
	});
	</script>
	<style type="text/css">
		.clearfix:before, .clearfix:after { content: ""; display: table; }
		.clearfix:after { clear: both; }
		.clearfix { zoom: 1; }
		.nav-menus-php, .major-publishing-actions, .publishing-action { float:right; line-height:23px; margin:5px 0 1px; text-align:right: }
		#featured_autocomplete { width:258px; }
		#available_list li, #featured_list > li { background:#f1f1f1; border:1px solid #dfdfdf; margin:0 0 1em 0; padding:1em; }
		#available_list li:hover { cursor:move; }
		#featured_list { min-height: 600px; }
		#featured_list > li { margin:0 1em 1em 1em; }
		#featured_list > li li { margin:0 1em 1em 1em; }
		.featured-header { position:relative; max-width: 1000px; overflow: hidden; }
		.featured-header em, .featured-header h3, .featured-header form { display:inline; }
		.featured-header em { font-size:2em; padding:0 .2em 0 0; }
		#available_list em { display:none; }
		#available_list h3 { padding:0 1.5em 0 0; }
		#available_list blockquote { max-height:3em; overflow:hidden; }
		#available_list .featured-header small { clear:both; display:block; padding:0 0 0 1em; }
		.featured-header .post-edit-link { position:absolute; right:20px; top:0; }
		.featured-header .remove { position:absolute; right:0; top:0; }
		#available_list .post-edit-link, #available_list .remove, #available_list label { display:none; }
		#available_list .fake_button { display: none; }
		.fake_button button { float: right; }
		.jquery-datetime { width: 100px; }
		.remove { cursor: pointer; }
	</style>
	<?php
}

function mahi_featured_available_list($featured, $exclude, $q = null) {

	$featured_args = apply_filters('mahi_featured', array());

	$featured_args = $featured_args[$featured];

	$args = array(
		'posts_per_page'	=>	10,
		'post__not_in'		=>	$exclude,
		'force_admin'		=>	true
	);

	if ( $featured_args['query'] )
		$args = array_merge($args, $featured_args['query']);

	if ( $q ):
		$args['like'] =	'%'.$q.'%';
	endif;

	if ( $args['post_type'] == 'term' && $args['taxonomy'] ):

		$taxonomy_args = $featured_args['query'];

		if ( $q )
			$taxonomy_args['name__like'] = $q;

		$terms = get_terms($args['taxonomy'], $taxonomy_args);

		foreach($terms as $term)
			mahi_featured_term_item($term);

	else:
		query_posts($args);
		while(have_posts()):
			the_post();
			mahi_featured_post_item(null, null, $featured);
		endwhile;
		wp_reset_query();
	endif;
}

function mahi_featured_term_item($selected, $idx = null, $featured = null) {
	if ( is_object($selected) ):
		$term = $selected;
		$term_id = $term->term_id;
		$selected = array();
	else:
		$term_id = $selected['ID'];
		$term = get_term($term_id, $selected['taxonomy']);
	endif;
	$title = $selected['title'] ? $selected['title'] : $term->name;
	$url = get_post_meta($term_id, 'url', true);
	$activation_date = $selected['date'] ? $selected['date'] : '';
	$activation_end_date = $selected['end_date'] ? $selected['end_date'] : '';

	$excerpt = $selected['excerpt'];
	?>
	<li>
		<input type="hidden" name="featured[]" class="ids" value="<?php print $term_id ?>">
		<input type="hidden" class="type" name="taxonomy_<?php print $term_id ?>" value="<?php print $term->taxonomy ?>">
		<input type="hidden" class="title" name="title_<?php print $term_id ?>" value="<?php print esc_attr($title) ?>">
		<input type="hidden" class="url" name="url_<?php print $term_id ?>" value="<?php print esc_attr($url) ?>">
		<textarea style="display:none;" class="excerpt" name="excerpt_<?php print $term_id ?>"><?php print esc_attr($excerpt) ?></textarea>
		<div class="featured-header">
			<em class="idx"><?php print $idx ?></em>
			<h3 class="title"><?php print stripslashes($title) ?></h3>
			<small>(<?php print get_taxonomy($term->taxonomy)->labels->singular_name ?>)</small>
			<small>- (url : <?php print $url; ?>)</small>
			<a class="remove" alt="supprimer">X</a>
		</div><!-- .featured-header -->
		<blockquote class="excerpt"><?php print stripslashes($excerpt) ?></blockquote>
		<label>
			<strong>Date d'activation</strong>
			<input type="text" class="jquery-datetime" name="date_<?php print $id ?>" value="<?php print $activation_date ?>" />
		</label>
	</li>
	<?php
}

function mahi_featured_post_item($selected = null, $idx = null, $featured = null) {
	if ( $selected ) :
		$post = get_post($selected['ID']);
		if ( ! $post )
			return false;
		$id = $post->ID;
	else:
		$id = get_the_ID();
	endif;

	$post = get_post($id);

	$selected = apply_filters('mahi_featured_selected', $selected, $post);

	$url = get_post_meta($id, 'url', true);
	$default_title = get_the_title($id);
	$title = $selected['title'] ? $selected['title'] : $default_title;
	$activation_date = $selected['date'] ? $selected['date'] : '';
	$activation_end_date = $selected['end_date'] ? $selected['end_date'] : '';
	$publish_date = date('Y-m-d H:i:s', strtotime($post->post_date ? $post->post_date : get_the_date('Y-m-d H:i:s')));
	if ( is_post_type(array('lab-event', 'event')) ):
		if (is_post_type('lab-event')) $meta_key = 'event_date';
		if (is_post_type('event')) $meta_key = 'start_date';

		$publish_date = date('Y-m-d', strtotime(get_post_meta($id, $meta_key, true)));
		if ( strtotime(get_post_meta($id, 'end_date', true)) ):
			$publish_date = $publish_date." - ".date('Y-m-d', strtotime(get_post_meta($id, 'end_date', true)));
		endif;
	endif;
	$excerpt = $selected['excerpt'] ? $selected['excerpt'] : get_the_excerpt();
	?>
	<li>
		<input type="hidden" name="featured[]" class="ids" value="<?php print $id ?>">
		<input type="hidden" class="title" name="title_<?php print $id ?>" value="<?php print esc_attr($title) ?>">
		<input type="hidden" class="url" name="url_<?php print $id ?>" value="<?php print esc_attr($url) ?>">
		<textarea style="display:none;" class="excerpt" name="excerpt_<?php print $id ?>"><?php print esc_attr($excerpt) ?></textarea>
		<div class="featured-header">
			<em class="idx"><?php print $idx ?></em>
			<h3 class="title"><?php print stripslashes($title) ?></h3>
			<small>(<?php print get_post_type_object(get_post_type($id))->labels->singular_name ?> - <?php print $publish_date ?><?php print get_post_status($id) == 'publish' ? '' : ' ('.get_post_status($id).')' ?>)</small>
			<small>- (url : <?php print $url; ?>)</small>
			<?php edit_post_link( 'Editer', '', '', $id ) ?>
			<a class="remove">X</a>
		</div><!-- .featured-header -->
		<blockquote class="excerpt"><?php print stripslashes($excerpt) ?></blockquote>
		<div class="fake_button">
			<button type="button">OK</button>
		</div>
		<label>
			<strong>Date d'activation</strong>
			<input type="text" class="jquery-datetime" name="date_<?php print $id ?>" value="<?php print $activation_date ?>" />
		</label>
		<label>
			<strong>Date de fin d'activation</strong>
			<input type="text" class="jquery-datetime" name="end_date_<?php print $id ?>" value="<?php print $activation_end_date ?>" />
		</label>
		<?php
		do_action('featured_fields', $selected, $idx, $featured);
		?>
	</li>
	<?php
}

add_action('wp_ajax_featured_available_list', 'mahi_featured_available_list_callback');
function mahi_featured_available_list_callback() {
	parse_str(urldecode($_POST['exclude']), $exclude);
	$exclude = array_merge(array('featured' => null), $exclude);
	mahi_featured_available_list($_POST['featured'], $exclude['featured'], $_POST['q']);
	die();
}
