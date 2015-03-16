<?php

function database_replacement($replace, $by) {
	global $wpdb;

	$sql = "UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, '".$replace."', '".$by."')";
	$wpdb->query($sql);

	$sql = "UPDATE {$wpdb->posts} SET guid = REPLACE(guid, '".$replace."', '".$by."')";
	$wpdb->query($sql);

	$sql = "UPDATE {$wpdb->postmeta} SET meta_value = REPLACE(meta_value, '".$replace."', '".$by."')";
	$wpdb->query($sql);

}

function sup_str_replace($replace,$by)
{
	global $wpdb;
	$i=0;
	$arr_type=get_post_types();
	$args=array('post_type' => $arr_type,
				'posts_per_page' => '-1');
	query_posts($args);
	while(have_posts()):
		the_post();

		xmpr($post->post_title);

		$id=get_the_ID();
		$post=get_post($id);

		$title=get_the_title();
		$content=get_the_content();

		if(strpos($title,$replace)):
			$up_title=mysql_real_escape_string(str_replace($replace,$by,$title));
			$up_title=str_replace("\'","&#39",$up_title);
			$wpdb->query("UPDATE {$wpdb->posts} SET post_title = '".$up_title."'
				WHERE ID=".$id);
			$i++;
		endif;

		if(strpos($content,$replace)):
			$up_content=mysql_real_escape_string(str_replace($replace,$by,$content));
			$up_content=str_replace("\'","&#39",$up_content);
			$wpdb->query("UPDATE {$wpdb->posts} SET post_content = '".$up_content."'
				WHERE ID=".$id);
			$i++;
		endif;

		if(strpos($post->guid,$replace)):
			$up_guid=str_replace($replace,$by,$post->guid);
			$wpdb->query("UPDATE {$wpdb->posts} SET guid = '".$up_guid."'
				WHERE ID=".$id);
			$i++;
		endif;

		$post_meta_arr=get_post_custom_keys();
		foreach($post_meta_arr as $key_indice => $meta_key):
			$meta_values=get_post_meta($id,$meta_key);
			foreach($meta_values as $value):
				if(strpos($value,$replace)):
					$tmp=str_replace($replace,$by,$value);
					update_post_meta($id,$meta_key,$tmp,$value);
					$i++;
				endif;
			endforeach;
		endforeach;

	endwhile;
	return $i;
}

function addition($a,$b)
{
	return ($a+$b);
}

function super_replace_menu()
{
	add_management_page('Super Remplacement','Super Remplacement','manage_options','sup_replace','super_replace_menu_interface');
}

function super_replace_menu_interface()
{
	if(isset($_POST['replace'])&&isset($_POST['by'])):
		$nb_modif=database_replacement($_POST['replace'],$_POST['by']);
//		echo $nb_modif." Modification(s) de <b>".$_POST['replace']."</b> par <b>".$_POST['by']."</b><br/>";
	endif;
?>
<p>
	<strong>Attention :</strong> le remplacement est définitif.<br/>
	La remplacement est sensible à la casse.
</p>

<form action="<?php echo $_SERVER['HTTP_POST']; ?>" method="post">
	<p>
		Remplacez toute les occurences de <input type="text" name="replace"
		value="<?php echo ((isset($_POST['replace']))?$_POST['replace']: ""); ?>"/>
		par <input type="text" name="by"
		value="<?php echo ((isset($_POST['by']))?$_POST['by']: ""); ?>"/> .<br/>
		<input type="submit" value="Commencez le remplacement"/><br/>
	</p>
</form>
<?php
}

add_action('admin_menu','super_replace_menu');

?>