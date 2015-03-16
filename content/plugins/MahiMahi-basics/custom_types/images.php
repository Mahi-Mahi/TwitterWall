<?php

require_once($_SERVER['DOCUMENT_ROOT'].'/wp-admin/admin.php');

$GLOBALS['body_id'] = 'custom_type_image_iframe custom_type_image_iframe_'.$_GET['target'];

wp_iframe( 'custom_type_image_iframe', $errors );

function custom_type_image_iframe() {

	global $wp_query;

	?>
	<h2 class="imageTitle">Images</h2>
	<p class="descriptionImage">Ci-dessous apparait la liste des images associés à cet élément. Vous pouvez télécharger les images via le module "Image à la une" ou en cliquant sur l'icône "insérer une image" au dessus du champ de texte principal.</p>
	<ul id="<?php print $_GET['target'] ?>_related" class="image_select">
		<?php
		//récupération des attachments lié au post
		$args = array(
					'post_type' => 'attachment',
					'posts_per_page' => 20,
					'post_status' => 'any',
					'post_parent' => $_GET['post_id'],
					);
		query_posts($args);
		if (have_posts()):
		while(have_posts()):
			the_post();
			$class = '';
			if($_GET['attachment_id']==get_the_ID())
				$class = 'selected';
			?>
			<li data-id="<?php the_ID();?>" class="<?php echo $class;?>">
				<?php thumbnail(null, null, array('attachment_id' => get_the_ID(), 'width' => 100, 'height' => 100)) ?>
				<strong><?php the_title();?></strong>
				<a href="#" class="post-edit-link">Sélectionner</a>
			</li>
			<?php
		endwhile;
		else:?>
			<li>Aucune image associé à cet élément</li><?php
		endif;
		?>
	</ul>
	<script type="text/javascript" charset="utf-8">
		function image_select(id, item){
			var url = "<?php print MahiMahiBasics_URL; ?>/custom_types/images.php?post_id=<?php echo $_GET['post_id'];?>&amp;target="+id+"&amp;attachment_id="+item.id+"&amp;TB_iframe=1";
			var win = window.dialogArguments || opener || parent || top;

			jQuery(win.document).find('#'+id).attr('value', item.id);
			jQuery(win.document).find('#'+id+'_img').attr('src', item.src);
			
			//si sélection une image pour la première fois
			if(jQuery(win.document).find('#'+id+'_img:hidden').length){
				jQuery(win.document).find('#'+id+'_img:hidden').show();
				jQuery(win.document).find('#'+id+'_img').siblings('span').hide();
				jQuery(win.document).find('#'+id+'_img').closest('a').siblings('.image-remove-link').show();
			}
			jQuery(win.document).find('#'+id+'_img').closest('.thickbox').attr('href', url);
			jQuery(win.document).find('#TB_overlay').remove();
			jQuery(win.document).find('#TB_window').remove();
		}

		jQuery(document).ready(function(){
			jQuery('ul.image_select li').click(function(){
				var item = {
							id: jQuery(this).data('id'),
							label: jQuery(this).find('strong').text(),
							src: jQuery(this).find('img').attr('src')
							};
				var target = '<?php print $_GET['target'];?>';
				var url = '/wp-content/plugins/MahiMahi-basics/custom_types/ajax-images.php';
				var data = {
								id:<?php echo $_GET['post_id'];?>,
								attach_id:item.id,
								action:'choose',
								field:'<?php echo $_REQUEST['field'];?>'
								};
				jQuery.post(url, data, function(){
					image_select(target, item);
				});
			});
		});
	</script>
	<?php

}

?>