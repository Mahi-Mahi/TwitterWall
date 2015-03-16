<?php

define('CustomTypes_DIR', MahiMahiBasics_DIR.'/custom_types');
define('CustomTypes_PATH', '/'.str_replace(ABSPATH, '', CustomTypes_DIR).'/');
define('CustomTypes_URL', MahiMahiBasics_URL.'/custom_types');


function disable_autosave() {
	wp_deregister_script('autosave');
}
//add_action('wp_print_scripts','disable_autosave');


class CustomType {

	var $slug;
	var $plugin;
	var $args = array();
	var $label;
	var $singular_label;
	var $labels = array();
	var $fields = array();

	var $export_excel = false;

	function __construct() {
		global $wp_rewrite, $wp;

		$trace=debug_backtrace();
		$caller=array_shift($trace);
		$file = basename(dirname($caller['file']));
		$this->plugin = $file;

		$default_args = array(
			'plural_slug' => $this->slug.'s',
			'label' => ucfirst($this->slug.'s'),
			'public' => true,
			'show_ui' => true,
			'capability_type' => 'page',
			'hierarchical' => false,
			'query_var' => true,
			'rewrite' => true,
			'supports' => array('title', 'editor', 'thumbnail'),
			'has_archive' => false,
			'export_excel' => false,
			/*
			'publicly_queryable' => true,
			'menu_position' => 20,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'menu_icon' => get_stylesheet_directory_uri() . '/images/super-duper.png',
			'register_meta_box_cb' => 'your_callback_function_name',
			'permalink_epmask' => EP_PERMALINK,
			*/
			);

		$default_labels =	array(
			'name' => ucfirst($this->label),
			'singular_label' => ucfirst($this->singular_label),
			'add_new' => 'Add New',
			'add_new_item' => 'Add new '.(isset($this->labels['singular_label']) ? $this->labels['singular_label'] : ucfirst($this->slug) ),
			'edit_item' => 'Edit '.(isset($this->labels['singular_label']) ? $this->labels['singular_label'] : ucfirst($this->slug) ),
			'new_item' => 'New '.(isset($this->labels['singular_label']) ? $this->labels['singular_label'] : ucfirst($this->slug) ),
			'view_item' => 'View '.(isset($this->labels['singular_label']) ? $this->labels['singular_label'] : ucfirst($this->slug) ),
			'not_found' => 'No '.(isset($this->labels['name']) ? $this->labels['name'] : ucfirst($this->slug).'s' ).' found',
			'not_found_in_trash' => 'No '.(isset($this->labels['name']) ? $this->labels['name'] : ucfirst($this->slug).'s' ).' found in trash',
			'parent_item_colon' => 'Parent '.(isset($this->labels['singular_label']) ? $this->labels['singular_label'] : ucfirst($this->slug) ).' : '
			);

		$this->args = array_merge($default_args, $this->args);

		$this->args = apply_filters('custom_type_args', $this->args);

		$this->args['labels'] = array_merge($default_labels, $this->labels);

		if ( ! isset($this->args['dont_register']) )
			register_post_type( $this->slug, $this->args);

		/*
		if ( $this->args['capability_type'] == 'post' && $this->args['has_archive'] ) {

			$permalink_structure = '/'.$this->args['rewrite']['slug'].'/%year%/%monthnum%/%'.$this->slug.'%';
			$wp_rewrite->add_rewrite_tag("%".$this->slug."%", '([^/]+)', $this->slug."=");
			$wp_rewrite->add_permastruct($this->slug, $permalink_structure, true, EP_ALL);

			add_filter('post_type_link', array(&$this, 'post_type_link'), 10, 4);

		}
		*/

		if ( apply_filters('mahimahi_custom_type_fields_filter', $this->slug) )
			$this->fields = apply_filters('mahimahi_custom_type_fields', $this->fields);

		add_action("admin_init", array(&$this, "admin_init"));

		add_action("admin_init", array(&$this, "admin_init_js"));

		add_action("wp_insert_post", array(&$this, "wp_insert_post"), 10, 2);

		add_action("delete_post", array(&$this, "wp_delete_post"));

		add_action("template_redirect", array(&$this, "template_redirect"));

		add_action('post_edit_form_tag', array(&$this, 'post_edit_form_tag'));

		add_filter('manage_'.$this->slug.'_posts_columns', array(&$this, 'add_admin_columns'));

		add_filter('manage_edit-'.$this->slug.'_sortable_columns', array(&$this, 'add_admin_sortable_columns'));
		add_filter( 'request', array(&$this, 'admin_sortable_column_request') );

		add_action('manage_posts_custom_column', array(&$this, 'manage_admin_columns_value'), 10, 2);
		add_action('manage_pages_custom_column', array(&$this, 'manage_admin_columns_value'), 10, 2);

		add_filter( 'parse_query', array(&$this, 'taxonomy_filter_parse_query') );
		add_action( 'restrict_manage_posts', array(&$this, 'restrict_manage_posts') );

		//ajouter une action pour rajouter un bouton

		add_action('restrict_manage_posts', array(&$this, 'export_excel_button'), 10);

		add_action('admin_enqueue_scripts', array(&$this, 'custom_types_admin_enqueue_scripts'));



		if ( method_exists($this, 'add_action'))
			call_user_func(array($this, 'add_action'));

		if ( method_exists($this, 'add_filter'))
			call_user_func(array($this, 'add_filter'));

	}

	function export_excel_button(){
		global $post_type;
		$export_excel = $this->export_excel;
		if ( $this->slug == $post_type && $export_excel!==false ):
			$path = constant('CustomTypes_URL').'/export_excel.php';

			?>
			<div class="favorite-actions">
				<div class="favorite-first">
					<?php
					$query = 'post_type='.$post_type;
					if(is_array($export_excel)):
						$types = array_keys($export_excel);
						$link = $export_excel[$types[0]];
						?>
						<a href="<?php echo $path.'?'.$query.'&export_type='.$types[0];?>">Exporter <?php echo $link['title'];?></a>
						<?php
					else:
						$types = array();
						?>
						<a href="<?php echo $path.'?'.$query;?>">Exporter</a>
						<?php
					endif;
					?>
				</div>

				<?php
				if(!empty($types)):
					?>
					<div class="favorite-toggle"><br /></div>
					<div class="favorite-inside">
						<?php
						array_shift($types);
						foreach($types as $type):
							?>
							<div class='favorite-action'>
								<a href="<?php echo $path.'?'.$query.$type;?>"><?php echo $export_excel[$type]['title'];?></a>
							</div>
							<?php
						endforeach;
						?>
					</div>
					<?php
				endif;
				?>
			</div>

			<?php
		endif;
	}

	function add_admin_sortable_columns($sortable) {
		foreach($this->fields as $field_slug => $field)
			if ( isset($field['admin_sortable_column']) )
				$sortable[$this->slug.'_'.$field_slug] = $field_slug;
		return $sortable;
	}


	function admin_sortable_column_request( $vars ) {
		if ( isset( $vars['orderby'] ) && $this->fields[$vars['orderby']]['admin_sortable_column'] ) {
			$vars = array_merge( $vars, $this->fields[$vars['orderby']]['admin_sortable_column'] );
		}
		return $vars;
	}



	function add_admin_columns($admin_columns) {

		if ( is_array($this->fields) )
			foreach($this->fields as $field_slug => $field)
				if ( isset($field['admin_column']) )
					$admin_columns[$this->slug.'_'.$field_slug] = $field['title'];

		if ( is_array($this->taxonomies) )
			foreach($this->taxonomies as $taxonomy => $taxonomy_args)
				if ( isset($taxonomy_args['admin_column'] ) )
					$admin_columns[$this->slug.'_taxonomy_'.$taxonomy] = $taxonomy_args['admin_column'];

		return $admin_columns;
	}

	function manage_admin_columns_value($column_name, $id) {
		global $wpdb;

		$column_name = preg_replace("#^".$this->slug."_#", '', $column_name);

		$field = $this->fields[$column_name];

		if ( isset($field['admin_column']) ) :


			if ( method_exists($this, 'admin_column_value_'.$column_name)):
				call_user_func(array($this, 'admin_column_value_'.$column_name), $id);
			else:

				switch($field['type']):

					default:
						echo get_post_meta($id, $column_name, true);
					break;

					case 'checkbox':
						print get_post_meta($id, $column_name, true) ? '<img src="'.constant('CustomTypes_URL').'/images/tick.png" />' : '';
					break;

					case 'post_reference':
						$ids = get_post_meta($id, $column_name);
						$posts = array();
						if ( is_array($ids) )
							foreach($ids as $id)
								$posts[] = get_post($id)->post_title;
						print implode(',', $posts);
					break;

				endswitch;

			endif;

		elseif( preg_match("#^taxonomy_(.*)$#", $column_name, $tmp) ):
			print implode(',', wp_list_pluck(wp_get_object_terms($id, $tmp[1]), 'name'));
		endif;

	}

	function admin_init_js() {
		if ( is_admin() ):
			foreach($this->fields as $field_slug => $field):
				if ( isset($field['type'])):
					switch($field['type']):
						case 'geo':
							wp_enqueue_script('gmaps');
						break;
						case 'address':
							wp_enqueue_script('gmaps');
							wp_enqueue_script('addresspicker');
						break;
						case 'date':
							wp_enqueue_script('date');
							wp_enqueue_script('date_fr');
						break;
						case 'datetime':
							wp_enqueue_script('datetimepicker');
						break;
					endswitch;
				endif;
			endforeach;
		endif;

	}


	function restrict_manage_posts() {
		$screen = get_current_screen();
		global $wp_query;


		if ( $screen->post_type == $this->slug ) {

			if ( is_array($this->args['taxonomies']) ) {

				foreach($this->args['taxonomies'] as $taxonomy) {
					$taxonomy_object = get_taxonomy($taxonomy);

					if ( $taxonomy_object->_builtin )
						continue;

					mahi_wp_dropdown_categories( array(
						'echo'	=>	true,
						'force_select2'	=>	true,
						'show_option_all' => __('Show All ').$taxonomy_object->labels->name,
						'taxonomy' => $taxonomy,
						'name' => $taxonomy_object->query_var,
						'orderby' => 'name',
						'selected' => ( isset( $wp_query->query[$taxonomy_object->query_var] ) ? $wp_query->query[$taxonomy_object->query_var] : '' ),
						'hierarchical' => false,
						'depth' => 3,
						'show_count' => false,
						'hide_empty' => false,
					) );
				}
			}
		}
	}

	function taxonomy_filter_parse_query( $query ) {
		$qv = &$query->query_vars;
		if ( is_array($this->args['taxonomies']) )
			foreach($this->args['taxonomies'] as $taxonomy) {
				$taxonomy_object = get_taxonomy($taxonomy);
				if ( ( $qv[$taxonomy_object->query_var] ) && is_numeric( $qv[$taxonomy_object->query_var] ) ) {
					$term = get_term_by( 'id', $qv[$taxonomy_object->query_var], $taxonomy );
					$qv[$taxonomy_object->query_var] = $term->slug;
				}
			}
		return $query;
	}





	function post_type_link($permalink, $post_id, $leavename) {

		$post = get_post($post_id);
		$rewritecode = array(
			'%year%',
			'%monthnum%',
			'%day%',
			'%hour%',
			'%minute%',
			'%second%',
			$leavename? '' : '%postname%',
			'%post_id%',
			'%category%',
			'%author%',
			$leavename? '' : '%pagename%',
		);

		if ( '' != $permalink && !in_array($post->post_status, array('draft', 'pending', 'auto-draft')) ) {
			$unixtime = strtotime($post->post_date);

			$category = '';
			if ( strpos($permalink, '%category%') !== false ) {
				$cats = get_the_category($post->ID);
				if ( $cats ) {
					usort($cats, '_usort_terms_by_ID'); // order by ID
					$category = $cats[0]->slug;
					if ( $parent = $cats[0]->parent )
						$category = get_category_parents($parent, false, '/', true) . $category;
				}
				// show default category in permalinks, without
				// having to assign it explicitly
				if ( empty($category) ) {
					$default_category = get_category( get_option( 'default_category' ) );
					$category = is_wp_error( $default_category ) ? '' : $default_category->slug;
				}
			}

			$author = '';
			if ( strpos($permalink, '%author%') !== false ) {
				$authordata = get_userdata($post->post_author);
				$author = $authordata->user_nicename;
			}

			$date = explode(" ",date('Y m d H i s', $unixtime));
			$rewritereplace =
			array(
				$date[0],
				$date[1],
				$date[2],
				$date[3],
				$date[4],
				$date[5],
				$post->post_name,
				$post->ID,
				$category,
				$author,
				$post->post_name,
			);
			$permalink = str_replace($rewritecode, $rewritereplace, $permalink);
		} else { // if they're not using the fancy permalink option
		}
		return $permalink;
	}

	function template_redirect() {
		global $wp, $wp_query, $wp_version;

		if ( version_compare( $wp_version, '3.2', '<' ) ):
			if ( isset($wp->query_vars["post_type"]) && $wp->query_vars["post_type"] == $this->slug && isset($this->args['template']) ) {
				if (have_posts()) {
					$type = $this->args['capability_type'] == 'post' ? 'single' : 'page';
					include(TEMPLATEPATH . '/'.$type.'-'.$this->args['template'].'.php');
					die();
				}
				else {
					$wp_query->is_404 = true;
				}
			}
		endif;

	}


	function admin_init() {
		foreach($this->fields as $slug => $field):
			if( isset($field['position']) && !empty($field['position']))
				$position = $field['position'];
			else
				$position = 'normal';
			if ( isset($field['group']) && ! empty($field['group']) )
				add_meta_box($this->slug.'_group_'.sanitize_html_class($field['group']), $field['group'], array(&$this, "meta_box_group"), $this->slug, $position, 'core');
			add_meta_box($this->slug.'_'.$slug.'_div', $field['title'] ? $field['title'] : ucfirst($slug), array(&$this, "meta_box"), $this->slug, $position, 'core');
		endforeach;
	}

	function meta_box_group($post, $box) {
		preg_match("/^".$post->post_type."_group_(?P<field_slug>.*)$/", $box['id'], $match);
		extract($match);

		$instance = $box['callback'][0];

		$groups = array();
		foreach($instance->fields as $slug => $field):
			if ( isset($field['group']) )
				$groups[sanitize_html_class($field['group'])][$slug] = $field;
		endforeach;

		?>
		<script type="text/javascript">
			jQuery('document').ready(function() {
			<?php
				foreach($groups[$field_slug] as $slug => $field):
					?>
					jQuery('#<?php print $box['id'] ?>').addClass('group-<?php print sanitize_html_class($box['title']) ?>');
					jQuery('#<?php print $box['id'] ?> .inside').append(jQuery('#<?php print $instance->slug ?>_<?php print $slug ?>_div.postbox .inside').html());
					jQuery('#<?php print $instance->slug ?>_<?php print $slug ?>_div.postbox').remove();
					jQuery('#<?php print $box['id'] ?> .inside label ').show();

					jQuery("#<?php print $box['id'] ?> .hasDatepicker").removeClass('hasDatepicker');
					jQuery("#<?php print $box['id'] ?> .jquery-date").datepicker({dateFormat: 'yy-mm-dd'});
					jQuery("#<?php print $box['id'] ?> .jquery-datetime").datetimepicker({
							addSliderAccess: true,
							sliderAccessArgs: { touchonly: false }
					});
					<?php
				endforeach;
			?>
			});
		</script>
		<?php
	}

	function meta_box($post, $box) {
		preg_match("/^".$post->post_type."_(?P<field_slug>.*)_div$/", $box['id'], $match);
		extract($match);
		$field = $this->get_field($field_slug);
		$type = $field['type'];
		?>
		<div class="custom-type <?php print isset($field['hidden']) ? 'hidden' : '' ?> <?php print $type ?>" data-slug="<?php print $field_slug ?>">
		<?php
		if ( $field['help']):
			?>
			<div class="help">
				<?php print apply_filters('the_content', $field['help']) ?>
			</div>
			<?php
		endif;

		call_user_func(array(&$this, 'meta_box_'.$type), $post->post_type, $field_slug, $post);
		?>
		</div>
		<?php
	}


	function meta_box_image($post_type, $field_slug, $post) {
		$field = $this->get_field($field_slug);

		if ( $post->ID ):
			$value = get_post_meta($post->ID, $field_slug, true);
			$image = wp_get_attachment_image_src($value);
		else:
			$value = 0;
		endif;
		$size = 'width="'.$field['size'][0].'" height="'.$field['size'][1].'"';
		$id = $post_type."_".$field_slug;
		?>
		<p>
			<span id="reverse_url" class="hidden"><?php print MahiMahiBasics_URL ?>/custom_types/images.php?post_id=<?php print $post->ID; ?>&amp;target=<?php print $id ?>&amp;attachment_id=0&amp;field=<?php echo $field_slug;?>&amp;TB_iframe=1</span>
			<a href="<?php print MahiMahiBasics_URL ?>/custom_types/images.php?post_id=<?php print $post->ID; ?>&amp;target=<?php print $id ?>&amp;attachment_id=<?php echo $value;?>&amp;field=<?php echo $field_slug;?>&amp;TB_iframe=1" class="thickbox">
			<?php
			if(!empty($value)):
				?>
				<img id="<?php echo $id?>_img" src="<?php thumbnail('admin_thumb', null, array('attachment_id' => $value, 'only_src' => true, 'width' => $field['size'][0], 'height' => $field['size'][1])) ?>" alt="image"/>
				<span style="display:none">Pas d'image</span>
				<?php
			else:
				?>
				<img id="<?php echo $id?>_img" style="display:none" src="" <?php echo $size;?>/>
				<span>Pas d'image</span>
				<?php
			endif;
			?>
			</a>
			<br/>
			<a href="javascript:void(0)" data-field="<?php echo $field_slug;?>" data-id="<?php echo $post->ID;?>" data-target="<?php echo $id;?>" class="image-remove-link">Supprimer l'image</a>
			<input type="hidden" id="<?php echo $id;?>" name="<?php echo $id;?>" value="<?php echo $value;?>"/>
		</p>
		<?php
	}

	function meta_box_text($post_type, $field_slug, $post) {
		$field = $this->get_field($field_slug);
		if ( $post->ID && $post->post_status != 'auto-draft') :
			$custom = get_post_custom($post->ID);
			if ( isset($custom[$field_slug][0]) )
				$value = $custom[$field_slug][0];
			else
				$value = '';
		elseif ( isset($field['default']) ):
			$value = $field['default'];
		else:
			$value = '';
		endif;
		$id = $post_type."_".$field_slug;
			?>
			<p>
				<label for="<?php print $id ?>" ><?php print $field['title'] ?>:</label>
				<input id="<?php print $id?>" name="<?php print $id?>" value="<?php echo htmlspecialchars($value); ?>" type="text" class="text" />
				<br />
			</p>
			<?php
	}

	function meta_box_help($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		$value = $custom[$field_slug][0];
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);

		print apply_filters('the_content', $field['content']);

	}

	function meta_box_function($post_type, $field_slug, $post) {
			$field = $this->get_field($field_slug);
			?>
			<div class="custom-type <?php print isset($field['hidden']) ? 'hidden' : '' ?> <?php print $type ?>" data-slug="<?php print $field_slug ?>">
			<?php
			call_user_func(array(&$this, 'meta_box_function_'.$field_slug), $post->post_type, $field_slug, $post);
			?>
			</div>
			<?php
	}

	function meta_box_textarea($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		$value = $custom[$field_slug][0];
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);
		?>
		<p>
			<label for="<?php print $id?>" ><?php print $field['title']?>:</label>
			<textarea id="<?php print $id?>" name="<?php print $id?>" class="code" /><?php echo $value; ?></textarea>
			<br />
		</p>
		<?php
	}

	function meta_box_serialize($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		$values = $custom[$field_slug];
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);
		?>
		<p>
			<label for="<?php print $id?>" ><?php print $field['title']?>:</label>
			<?php
			foreach($values as $value):
				?>
				<xmp><?php print_r(unserialize($value)) ?></xmp>
				<?php
			endforeach;
			?>
			<br />
		</p>
		<?php
	}

	function meta_box_wysiwyg($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		if ( isset($custom[$field_slug][0]) )
			$value = $custom[$field_slug][0];
		else
			$value = '';
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);

		$settings = array(
							'media_buttons' => true,
							'tinymce' => true,
							'quicktags' => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close'),
							'textarea_name' => $id
		);

		?>
		<p>

			<?php
			if ( function_exists('wp_editor') ):

				wp_editor( $value, 'textarea_'.$id, $settings );

			else:

				if ( user_can_richedit() ):
					echo '<a id="edButtonHTML_'.$id.'" class="edButtonHTML hide-if-no-js" onclick="switchEditors.go(\'textarea_'.$id.'\', \'html\');">Éditeur simple</a>
						<a id="edButtonPreview_'.$id.'" class="edButtonPreview active hide-if-no-js" onclick="switchEditors.go(\'textarea_'.$id.'\', \'tmce\');">Éditeur graphique</a><br>';
					echo '<script>jQuery("document").ready(function(){ if ( jQuery("#edButtonPreview").length ) switchEditors.go(\'textarea_'.$id.'\', \'tmce\'); });</script>';
				endif;
				?>
				<textarea id="textarea_<?php print $id?>" name="<?php print $id?>" class="code" /><?php echo $value; ?></textarea>
				<br />
				<?php
			endif;
			?>
		</p>
		<?php
	}

	function meta_box_date($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		$value = $custom[$field_slug][0];
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);
		?>
		<p>
			<label for="<?php print $id?>" ><?php print $field['title']?>:</label>
			<input type="text" id="input_<?php print $id?>" name="<?php print $id?>" class="jquery-date" value="<?php echo $value; ?>" />
			<br />
		</p>
		<?php
	}

	function meta_box_datetime($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		$value = $custom[$field_slug][0];
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);
		?>
		<p>
			<label for="<?php print $id?>" ><?php print $field['title']?>:</label>
			<input type="text" id="input_<?php print $id?>" name="<?php print $id?>" class="jquery-datetime" value="<?php echo $value; ?>" />
			<br />
		</p>
		<?php
	}

	function meta_box_checkbox($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		if ( isset($custom[$field_slug]) )
			$value = $custom[$field_slug][0];
		else
			$value = '';
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);

		?>
		<p>
			<label for="<?php print $id?>" ><?php print $field['title']?>:</label>
			<input type="checkbox" id="checkbox_<?php print $id?>" name="<?php print $id?>" value="1" class="code" <?php if( $value || ( $post->post_status == 'auto-draft' && $field['default'] ) ) echo 'checked="checked"'; ?> />
			<br />
		</p>
		<?php
	}

	function meta_box_checkboxes($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		if ( isset($custom[$field_slug]) )
			$value = $custom[$field_slug];
		else
			$value = array();
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);
		foreach($field['options'] as $k => $v):
			?>
			<p class="checkboxes">
				<label for="<?php print $id?>-<?php print $k ?>" ><?php print $v ?>:</label>
				<input type="checkbox" id="checkbox_<?php print $id ?>-<?php print $k ?>" name="<?php print $id?>[]" value="<?php print $k ?>" class="code" <?php if( in_array($k, $value) ) echo 'checked="checked"'; ?> />
				<br />
			</p>
			<?php
		endforeach;
	}



	function meta_box_user($post_type, $field_slug, $post) {
		$obj_users = get_users_of_blog();

		$users = array('0' => '-');
		foreach($obj_users as $user):
			$users[$user->ID] = $user->display_name.' ('.$user->user_email.')';
		endforeach;
		$meta = get_post_meta($post->ID, $field_slug, true);
		?><p><?php
		show_select(array('name' => $post_type.'_'.$field_slug, 'id' => $post_type.'_'.$field_slug, 'options' => $users, 'selected' => $meta));
		?></p><?php
	}


	function meta_box_select($post_type, $field_slug, $post) {
		global $wpdb;

		$custom = get_post_custom($post->ID);
		if ( isset($custom[$field_slug][0]) )
			$value = $custom[$field_slug][0];
		else
			$value = null;
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);
		$data = array();
		$args = array('class' => "code",
					  'name' => $id,
					  'id' => $id,
					  'selected' => $value
					 );
		?>
		<p>
			<label for="<?php print $id?>" ><?php print $field['title']?>:</label>
				<?php

				if ( isset($field['options']) ) :

					$options = $field['options'];
					$data = array();
					foreach( $options as $option ) :
						$data[$option[0]] = $option[1];
					endforeach;

					$args['options'] = $data;

					show_select($args);

				elseif ( isset($field['data'])) :

					$options = $field['data'];
					if ( is_array($options) ):
						$args['options'] = $options;
						show_select($args);
					endif;

				elseif ( isset($field['query']) ):

					$options = $wpdb->get_results($wpdb->prepare($field['query'], $post->ID));
					if ( is_array($options) ):
						foreach( $options as $v ) :
							$data[$v->ID] = $v->name;
						endforeach;

						$args['options'] = $data;
						show_select($args);
					endif;

				elseif(isset($field['callback'])):
					$args['options'] = call_user_func(array($this, $field['callback']));
					show_select($args);
				endif;
				?>
			<br />
		</p>
		<?php
	}


	function update_post_reference($field, $post_ID, $values) {
		global $wpdb;

		extract($this->fields[$field]['storage']);

		if ( ! is_array($values) )
			$values = array($values);

		if ( empty($values) ):
			$sql = " DELETE FROM ".$table." WHERE ".$primary_key." = ".$post_ID." AND ".$foreign_key." NOT IN (".implode(',', $values).") ";
		else:
			$sql = " DELETE FROM ".$table." WHERE ".$primary_key." = ".$post_ID." ";
		endif;
		$wpdb->query($sql);

		foreach($values as $value):
			if ( ! empty($value) ):
				if ( ! $wpdb->get_var("SELECT ".$primary_key." FROM ".$table." WHERE ".$primary_key." = ".$post_ID." AND ".$foreign_key." = ".$value) ):
					$sql = " INSERT INTO ".$table." (".$primary_key.", ".$foreign_key.") VALUES (".$post_ID.", ".$value.")";
					$wpdb->query($sql);
				endif;
			endif;
		endforeach;

	}


	function add_post_reference($field, $post_ID, $values) {
		global $wpdb;

		extract($this->fields[$field]['storage']);

		if ( ! is_array($values) )
			$values = array($values);

		foreach($values as $value):
			if ( ! $wpdb->get_var("SELECT ".$primary_key." FROM ".$table." WHERE ".$primary_key." = ".$post_ID." AND ".$foreign_key." = ".$value) ):
				$sql = " INSERT INTO ".$table." (".$primary_key.", ".$foreign_key.") VALUES (".$post_ID.", ".$value.")";
				$wpdb->query($sql);
			endif;
		endforeach;

	}

	function remove_post_reference($field, $post_ID, $values) {
		global $wpdb;

		extract($this->fields[$field]['storage']);

		if ( ! is_array($values) )
			$values = array($values);

		foreach($values as $value):
			$sql = " DELETE FROM ".$table." WHERE ".$primary_key." = ".$post_ID." AND ".$foreign_key." = ".$value." ";
			$wpdb->query($sql);
		endforeach;

	}

	function get_post_reference($field, $post_ID = null, $unique = false) {
		global $wpdb, $post;

		if ( ! is_numeric($post_ID) && ! $post_ID = $post->ID )
			return;

		extract($this->fields[$field]['storage']);

		$sql = " SELECT ".$foreign_key." FROM ".$table." WHERE ".$primary_key." = ".$post_ID;

		$res = $wpdb->get_col($sql);

		if ( $unique )
			return $res[0];

		return $res;
	}

	function meta_box_post_reference($post_type, $field_slug, $post) {

		$custom = get_post_custom($post->ID);
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);

		if ( isset($field['ajax']) ):
			if ( isset($field['storage']) ):
				$values = $this->get_post_reference($field_slug, $post->ID);
			else:
				$values = get_post_meta_sorted($post->ID, $field_slug);
			endif;
			if ( ! is_array($values))
				$values = preg_split("/,/", $values, -1, PREG_SPLIT_NO_EMPTY);
			?>
			<div>
				<label for="<?php print $id?>" ><?php print $field['title']?>:</label>

				<div class="post_reference autocomplete <?php print isset($field['suggest']) ? 'suggest' : '' ?>" >
					<input type="text" name="<?php print $id?>_search" id="<?php print $id?>" value="" class="search_field" />
					<?php
					if ( isset($field['suggest']) ):
						?>
						<a href="<?php print MahiMahiBasics_URL ?>/custom_types/suggest.php?post_id=<?php print $post->ID ?>&target=<?php print $id ?>" class="thickbox">suggest</a>
						<?php
					endif;
					?>
					<input type="hidden" class="autocomplete_query" value="<?php print base64_encode(serialize($field['query']))?>" />
					<ul>
					<?php
					switch($field['query']['post_type']):
						default:
						case 'post':
							foreach($values as $value):
								$value = get_post($value);
								if ( is_object($value) ):
									?>
									<li id="post_reference-<?php print $value->ID?>">
										<span class="link" onClick="post_reference_remove(this)" title="delete">[x]</span>&nbsp;
										<input type="hidden" name="<?php print $id?>[]" value="<?php print $value->ID?>" />
										<?php
										if ( isset($field['query']['list_callback']) && function_exists($field['query']['list_callback']) )
											print call_user_func($field['query']['list_callback'], $value);
										else
											print $value->post_title;
										?>
									</li>
									<?php
								endif;
							endforeach;
						break;
						case 'custom':
							foreach($values as $value):
								$value = apply_filters('mahi_post_reference_object', $value);
								?>
								<li id="post_reference-<?php print $value->ID?>">
									<span class="link" onClick="post_reference_remove(this)" title="delete">[x]</span>&nbsp;
									<input type="hidden" name="<?php print $id?>[]" value="<?php print $value->ID?>" />
									<?php
									if ( isset($field['query']['list_callback']) && function_exists($field['query']['list_callback']) )
										print call_user_func($field['query']['list_callback'], $value);
									else
										print $value->post_title;
									?>
								</li>
								<?php
							endforeach;
						break;
					endswitch;
					?>
					</ul>
				</div>
			</div>
			<?php
		else:
			if ( isset($custom[$field_slug][0]) )
				$value = $custom[$field_slug][0];
			$default_query = array('numberposts' => 99, 'orderby' => 'title', 'order' => 'ASC');
			if ( is_array($field['query']) )
				$query = array_merge($default_query, $field['query']);
			else
				$query = $default_query;
				?>
				<p>
					<label for="<?php print $id?>" ><?php print $field['title']?>:</label>
					<select id="<?php print $id?>" name="<?php print $id?>">
						 <option value=""> - </value>
							<?php
							$references = get_posts($query);
							foreach($references as $reference):
								?>
								<option value="<?php print $reference->ID ?>" <?php print $value == $reference->ID ? 'selected' : '' ?>><?php print $reference->post_title?></option>
								<?php 							endforeach;
							?>
					</select>
					<br />
				</p>
				<?php
		endif;
	}


	function meta_box_user_reference($post_type, $field_slug, $post) {

		$custom = get_post_custom($post->ID);
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);

		$field['query']['post_type'] = 'user';

		if ( isset($field['storage']) ):
			$values = $this->get_post_reference($field_slug, $post->ID);
		else:
			$values = get_post_meta_sorted($post->ID, $field_slug);
		endif;
		if ( ! is_array($values))
			$values = preg_split("/,/", $values, -1, PREG_SPLIT_NO_EMPTY);
		?>
		<div>
			<label for="<?php print $id?>" ><?php print $field['title']?>:</label>

			<div class="post_reference autocomplete <?php print isset($field['suggest']) ? 'suggest' : '' ?>" >
				<input type="text" name="<?php print $id?>_search" id="<?php print $id?>" value="" class="search_field" />
				<input type="hidden" class="autocomplete_query" value="<?php print base64_encode(serialize($field['query']))?>" />
				<ul>
				<?php
				foreach($values as $value):
					$value = get_user_by('id', $value);
					if ( is_object($value) ):
						?>
						<li id="post_reference-<?php print $value->ID?>">
							<span class="link" onClick="post_reference_remove(this)" title="delete">[x]</span>&nbsp;
							<input type="hidden" name="<?php print $id?>[]" value="<?php print $value->ID?>" />
							<?php print $value->user_login?>
						</li>
						<?php
					endif;
				endforeach;
				?>
				</ul>
			</div>
		</div>
		<?php
	}


	function meta_box_address($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		$value = $custom[$field_slug][0];
		$value_lat = $custom[$field_slug.'_lat'][0];
		$value_lng = $custom[$field_slug.'_lng'][0];
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);
			?>
	  <div class="address">
		<p>
		  <label><?php print __("Address") ?> :    </label>
		  <input id="addresspicker_<?php print $field_slug ?>" name="addresspicker_<?php print $field_slug ?>" class="addresspicker_input" value="<?php print $value ?>" />
		</p>
		<input type="hidden" id="addresspicker_<?php print $field_slug ?>_lat" name="addresspicker_<?php print $field_slug ?>_lat" value="<?php print $value_lat ?>" >
		<input type="hidden" id="addresspicker_<?php print $field_slug ?>_lng" name="addresspicker_<?php print $field_slug ?>_lng" value="<?php print $value_lng ?>" >
		<input type="hidden" id="addresspicker_<?php print $field_slug ?>_zipcode" name="addresspicker_<?php print $field_slug ?>_zipcode">
		<input type="hidden" id="addresspicker_<?php print $field_slug ?>_locality" name="addresspicker_<?php print $field_slug ?>_locality">
		<input type="hidden" id="addresspicker_<?php print $field_slug ?>_country" name="addresspicker_<?php print $field_slug ?>_country">
		<div id="addresspicker_<?php print $field_slug?>_map" class="addresspicker_map">
		</div>
			</div>
			<?php
	}


	function meta_box_geo($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		$value = $custom[$field_slug][0];
		$value_lat = $custom[$field_slug.'_lat'][0];
		$value_lng = $custom[$field_slug.'_lng'][0];
		$value_components = json_encode(unserialize($custom[$field_slug.'_components'][0]));
		$value_title = $custom[$field_slug.'_title'][0];
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);
			?>
			<p class="geo">
				<input type="text" id="geo_<?php print $id?>_title" name="<?php print $id?>_title" class="code" value="<?php print $value_title ?>" <?php if ( !isset($field['geo_title']) ) {?>style="display:none;"<?php } ?> />
				<input type="text" id="geo_<?php print $id?>" name="<?php print $id?>" class="code formatted" value="<?php print $value ?>" <?php if ( isset($field['geo_title']) ) {?>style="display:none;"<?php } ?> />
				<input type="button" value="add" onclick="geo_set(this)" />
				<div id="geo_<?php print $id ?>_map" style="width: 500px; height: 300px"></div>
				<input type="hidden" id="geo_<?php print $id?>_lat" name="<?php print $id?>_lat" value="<?php print $value_lat ?>" />
				<input type="hidden" id="geo_<?php print $id?>_lng" name="<?php print $id?>_lng" value="<?php print $value_lng ?>" />
				<input type="hidden" id="geo_<?php print $id?>_components" name="<?php print $id?>_components" value="<?php print $value_components ?>" />
			</p>
			<?php
	}

	function meta_box_file($post_type, $field_slug, $post) {
		$field = $this->get_field($field_slug);
		$custom = get_post_custom($post->ID);
		$id = $post_type."_".$field_slug;
		?>
		<p>
			<label for="<?php print $id ?>" ><?php print $field['title'] ?>:</label>
			<input id="<?php print $id?>" name="<?php print $id?>" value="" type="file" class="file" />
		</p>
		<?php
		if ( isset($custom[$field_slug]) ):
			$value = $custom[$field_slug][0];
			$attachment = get_post($value);
			if ( $value && isset($attachment->ID) ):
				?>
				<p>
					<input type="hidden" name="<?php print $id ?>" value="<?php print $value ?>" />
					<a href="<?php print wp_get_attachment_url($value) ?>" target="_blank">
						<?php print wp_get_attachment_image( $value, 'thumbnail', true ); ?>
					</a>
					<button class="remove">remove</button>
				</p>
				<?php
			endif;
		endif;
	}

	function meta_box_file_selector($post_type, $field_slug, $post) {
		$field = $this->get_field($field_slug);
		$custom = get_post_custom($post->ID);
		$id = $post_type."_".$field_slug;
		?>
		<p>
			<label for="<?php print $id ?>" ><?php print $field['title'] ?>:</label>
			<input id="<?php print $id?>" name="<?php print $id?>" value="" type="file" class="file" />
		</p>
		<?php
		if ( isset($custom[$field_slug]) ):
			$value = $custom[$field_slug][0];
			$attachment = get_post($value);
			if ( $value && isset($attachment->ID) ):
				?>
				<p>
					<input type="hidden" name="<?php print $id ?>" value="<?php print $value ?>" />
					<a href="<?php print wp_get_attachment_url($value) ?>" target="_blank">
						<?php print wp_get_attachment_image( $value, 'thumbnail', true ); ?>
					</a>
					<button class="remove">remove</button>
				</p>
				<?php
			endif;
		endif;
	}

	function meta_box_editor_template($post_type, $field_slug, $post) {
		$custom = get_post_custom($post->ID);
		if ( isset($custom[$field_slug]) )
			$value = $custom[$field_slug];
		else
			$value = array();
		$id = $post_type."_".$field_slug;
		$field = $this->get_field($field_slug);
		$dir = get_stylesheet_directory_uri();
		$checked = false;
		foreach($field['options'] as $k => $v):
			if(isset($v['template']) && !isset($v['style']))
				$editorType = 'template';
			else if(!isset($v['template']) && isset($v['style']))
				$editorType = 'style';
			else
				$editorType = 'all';
			if(in_array(($editorType=='style') ? $v['style'] : $v['template'], $value))
					  $checked = true;
		endforeach;
		foreach($field['options'] as $k => $v):
			if(isset($v['template']) && !isset($v['style']))
				$editorType = 'template';
			else if(!isset($v['template']) && isset($v['style']))
				$editorType = 'style';
			else
				$editorType = 'all';
			?>
			<div class="editor_template" style="margin:10px; margin-left: 5px;">
				<input type="radio" editor-type="<?php echo $editorType ?>"
					id="editor_template_<?php print $id ?>-<?php print $k ?>"
					name="<?php print $id?>[]"
					value="<?php print ($editorType=='style') ? $v['style'] : $v['template'] ?>"
					class="code"
					<?php echo (isset($v['template'])) ? 'editor-template="'.$dir.'/editor-templates/'.$v['template'].'.html"' : '' ?>
					<?php echo (isset($v['style'])) ? 'editor-style="'.$dir.'/editor-styles/'.$v['style'].'.css"' : '' ?>
					<?php echo ( in_array(($editorType=='style') ? $v['style'] : $v['template'], $value) ) ? 'checked="checked"' : (!$checked && isset($v['default']) && $v['default']) ? 'checked="checked"' : ''; ?> />
					<label for="editor_template_<?php print $id?>-<?php print $k ?>" >
						<?php print ($editorType=='style') ? $v['style'] : $v['template_title'] ?>
					</label>
				<?php
				if(isset($v['blocks'])){
					echo '<ul style="margin:0px; margin-top:5px; margin-left:30px;">';
						foreach($v['blocks'] as $block => $texte){
							echo '<li class="template-block" block="'.$block.'">'.$texte.'</li>';
						}
					echo '</ul>';
				}
				else{
					echo '<br />';
				}
				?>
			</div>
			<?php
		endforeach;
	}

	function get_field($slug) {
		$default_args = array('type' => 'text');
		return array_merge($default_args, $this->fields[$slug]);
	}

	function post_edit_form_tag( ) {
		foreach ( $this->fields as $field )
			if ( isset($field['type']) && $field['type'] == 'file' )
			return print ' enctype="multipart/form-data"';
	}

	function wp_insert_post_file($post, $slug, $field) {
		require_once(ABSPATH.'/wp-admin/includes/file.php');
		require_once(ABSPATH.'/wp-admin/includes/media.php');
		require_once(ABSPATH.'/wp-admin/includes/image.php');

		$id = $post->post_type."_".$slug;

		if ( isset($_FILES[$id]) ):
			$upload = media_handle_upload($id, $post->ID);
		elseif ( isset($_POST[$id]) ):
			$upload = mahibasics_media_sideload_image($_POST[$id], $post->ID);
		endif;

		if ( is_numeric($upload) ):
			delete_post_meta($post->ID, $slug);
			add_post_meta($post->ID, $slug, $upload);
		elseif ( ! isset($_POST[$id]) || ! is_numeric($_POST[$id]) ):
			delete_post_meta($post->ID, $slug);
		endif;

	}

	function wp_insert_post_default($post, $slug, $field) {

		$key = $post->post_type.'_'.$slug;

		if ( isset($_POST[$key]) )
			$value = $_POST[$key];
		else
			$value = null;

		if ( ! $value && isset($field['default']) && $field['type'] != 'checkbox' )
			$value = $field['default'];

		if ( isset($field['storage']) ):
			// update storage table
			$this->update_post_reference($slug, $post->ID, $value);
		else:
			if (empty($value)) {
				delete_post_meta($post->ID, $slug);
				if ( isset($field['default']) && $field['type'] != 'checkbox' )
					add_post_meta($post->ID, $slug, $field['default']);
				return;
			}

			if ( ! is_array($value) ) {
				delete_post_meta($post->ID, $slug);
				if ( ! update_post_meta($post->ID, $slug, $value) )
					add_post_meta($post->ID, $slug, $value);
			}
			else {
				delete_post_meta($post->ID, $slug);
				foreach ($value as $entry)
					add_post_meta($post->ID, $slug, $entry);
			}
		endif;

	}

	// When a post is inserted or updated
	function wp_insert_post($post_id, $post = null) {

		if ( $post->post_type != $this->slug)
			return;

		if ( ! $_POST || empty($_POST) )
			return;

		// fix for plugin wp-to-twitter when post scheduling
		if ( count($_POST) == 1 && isset($_POST['_jd_wp_twitter']) )
			return;

		if ( isset($_POST['action']) && $_POST['action'] != 'editpost' && !isset($_POST['form2customtype']) )
			return;

		if ( $_POST['action'] == 'autosave' || isset($_POST['_inline_edit']) || isset($_GET['bulk_edit']) || isset($_POST['no_customtype']) )
			return;

		foreach ($this->fields as $slug => $field) :
			if ( ! isset($field['type']))
				$field['type'] = null;

			switch($field['type']):
				case 'serialize':
					return;
				break;
				default:
					$this->wp_insert_post_default($post, $slug, $field);
				break;
				case 'file':
					$this->wp_insert_post_file($post, $slug, $field);
				break;
				case 'address':

					update_post_meta($post_id, $slug, $_POST['addresspicker_'.$slug]);

					update_post_meta($post_id, $slug.'_lat', $_POST['addresspicker_'.$slug.'_lat']);
					update_post_meta($post_id, $slug.'_lng', $_POST['addresspicker_'.$slug.'_lng']);

					if ($_POST['addresspicker_'.$slug.'_zipcode'])
						update_post_meta($post_id, $slug.'_zipcode', $_POST['addresspicker_'.$slug.'_zipcode']);

					if ($_POST['addresspicker_'.$slug.'_locality'])
						update_post_meta($post_id, $slug.'_locality', $_POST['addresspicker_'.$slug.'_locality']);

					if ($_POST['addresspicker_'.$slug.'_country'])
						update_post_meta($post_id, $slug.'_country', $_POST['addresspicker_'.$slug.'_country']);


					// TODO : check for previous geo field ( and convert it )
					delete_post_meta($post_id, $slug.'_title');
					delete_post_meta($post_id, $slug.'_components');

				break;
			endswitch;

			switch($field['type']):
				case 'geo':

					if ( isset($_POST[$this->slug.'_'.$slug.'_lat'])):

					endif;

					if ( ! empty($_POST[$this->slug.'_'.$slug]) && ( empty($_POST[$this->slug.'_'.$slug.'_components']) || empty($_POST[$this->slug.'_'.$slug.'_lat']) || get_post_meta(get_the_ID(), $slug.'_components', true) == 's:2:"N;";' || $_POST[$this->slug.'_'.$slug.'_lat'] == '46.1942196' ) ):
						// post not created from admin ( probably an automated import )

						// cautiously use gmaps api : cache everything, and stop as soon as api rate limit is reached

						$url = "http://maps.google.com/maps/api/geocode/json?address=".urlencode($_POST[$this->slug.'_'.$slug])."&sensor=true";

						$response = mahimahi_load_cached($url);

						$geocode = json_decode($response);

						if ( empty($_POST[$this->slug.'_'.$slug.'_title']))
							$_POST[$this->slug.'_'.$slug.'_title'] = $_POST[$this->slug.'_'.$slug];

						if ( $geocode->results[0]->formatted_address ) :
							$_POST[$this->slug.'_'.$slug.'_title'] = utf8_decode($geocode->results[0]->formatted_address);

							$_POST[$this->slug.'_'.$slug.'_lat'] = $geocode->results[0]->geometry->location->lat;
							$_POST[$this->slug.'_'.$slug.'_lng'] = $geocode->results[0]->geometry->location->lng;

							foreach( $geocode->results[0]->address_components as $address_component)
								if ( in_array($address_component->types[0], array('street_number', 'route', 'postal_code', 'locality', 'country')) )
									$_POST[$this->slug.'_'.$slug.'_components'][$address_component->types[0]] = $address_component->long_name;

							$_POST[$this->slug.'_'.$slug.'_components'] = json_encode($_POST[$this->slug.'_'.$slug.'_components']);
						else:
							logr($geocode);
						endif;

					endif;

					update_post_meta($post_id, $slug.'_title', $_POST[$this->slug.'_'.$slug.'_title']);

					update_post_meta($post_id, $slug.'_lat', $_POST[$this->slug.'_'.$slug.'_lat']);
					update_post_meta($post_id, $slug.'_lng', $_POST[$this->slug.'_'.$slug.'_lng']);
					update_post_meta($post_id, $slug.'_components', serialize(json_decode(stripslashes($_POST[$this->slug.'_'.$slug.'_components']))));

				break;
			endswitch;

		endforeach;

		do_action('customtype_inserted_post', $post_id, $post);

	}

	function wp_delete_post($post_id) {
		global $wpdb;

		$post = get_post($post_id);

		if ( $post->post_type != $this->slug)
			return;

		foreach ($this->fields as $slug => $field) :
			if ( isset($field['storage']) ):
				extract($field['storage']);

				$sql = " DELETE FROM ".$table." WHERE ".$primary_key." = ".$post_id." ";
				$wpdb->query($sql);
			endif;
		endforeach;

	}

	public static function getObject($post_type) {
		$customtype_class = str_replace(' ', '', ucwords(preg_replace("#[-_]+#", ' ', $post_type)));
		if ( ! $customtype_class )
			logr(get_caller());
		if ( ! class_exists($customtype_class))
			return false;
		return new $customtype_class();
	}


	function custom_types_admin_enqueue_scripts() {
				global $pagenow;
				if(is_admin() &&  $pagenow=='post.php' && $_GET['action'] =='edit'):
								global $post;
								if($post->post_type == $this->slug):
												if(file_exists(dirname(dirname(dirname(__FILE__))).'\\'.$this->plugin.'\custom_type_'.$this->slug.'.js'))
																wp_enqueue_script('custom_type_'.$this->slug, plugins_url($this->plugin).'/custom_type_'.$this->slug.'.js', array('jquery'));
								endif;
					endif;
		}
}


function custom_types_admin_enqueue_scripts() {

	wp_enqueue_script('custom_types_admin', CustomTypes_URL.'/js/custom_types.js', array('jquery-ui-datepicker'));

	wp_enqueue_style('custom_types_admin', CustomTypes_URL.'/css/custom_types.css');

	wp_localize_script('custom_types_admin', 'admin_mahi_vars', array(
		'autocomplete_url'	=> CustomTypes_URL."/autocomplete.php?"
	));

}
add_action('admin_enqueue_scripts', 'custom_types_admin_enqueue_scripts');



function custom_types_dbx_post_advanced() {
	wp_enqueue_script('editor');
}
add_action('dbx_post_advanced', 'custom_types_dbx_post_advanced');


function custom_types_posts_search_where($where) {
	global $wp_query, $wpdb;

	if ( ! isset($wp_query->query['search']) )
		return $where;

	$where .= " AND ( post_title LIKE '%".$wpdb->escape($wp_query->query['search'])."%' OR post_content LIKE '%".$wpdb->escape($wp_query->query['search'])."%' ) ";

	return $where;
}
add_filter('posts_where', 'custom_types_posts_search_where');


function add_imported_human($post_id) {
	if ( $_POST && get_post_meta($post_id, 'imported', true) )
		add_post_meta($post_id, 'human', 1, true);
}
add_action('wp_insert_post', 'add_imported_human', 10, 2);

/*
function mahibasics_wp_unique_post_slug_is_bad_hierarchical_slug($false, $slug, $post_type, $post_parent) {
	logr("mahibasics_wp_unique_post_slug_is_bad_hierarchical_slug($false, $slug, $post_type, $post_parent)");
	return false;
}
add_filter('wp_unique_post_slug_is_bad_hierarchical_slug', 'mahibasics_wp_unique_post_slug_is_bad_hierarchical_slug', 10, 4);
*/

/*

	print get_custom_type_class('feature');
	# 'Feature'

	print get_custom_type_class('use-case');
	# 'UseCase'

	print get_custom_type_class('non-existing-post_type');
	# false


*/



function get_custom_type_class($post_type, $return_object = false ) {

	$customtype_class = str_replace(' ', '', ucwords(preg_replace("#[-_]+#", ' ', $post_type)));

	if ( ! class_exists($customtype_class) ):
		return;
	endif;

	if ( $return_object )
		return new $customtype_class();
	else
		return $customtype_class;
}



// add_filter('thumbnail_sizes', 'mahi_custom_types_thumbnail_sizes', 10, 99);
function mahi_custom_types_thumbnail_sizes ($default_args, $name) {
	$sizes = array(

		'admin-thumbnail'  => array('width' => 250),

	);

	return array_merge($default_args, $sizes[$name]);
}



