<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
global $wpdb;

function is_wp_posts_fields($field){
	global $wp_posts_fields;
	if(empty($wp_posts_fields)):
		$args = array('posts_per_page' => 1);
		$post = get_posts($args);
		$post = get_object_vars($post[0]);
		$wp_posts_fields = array_keys($post);
	endif;

	if(in_array($field, $wp_posts_fields))
		return true;

	return false;
}



$post_type = $_REQUEST['post_type'];
if(isset($_REQUEST['export_type']))
	$export_type = $_REQUEST['export_type'];
else
	$export_type = '';

$object = CustomType::getObject($post_type);
if(isset($_REQUEST['export_type']))
	$export_excel = $object->export_excel[$export_type];
else
	$export_excel = array();

$filename = 'export_'.$post_type.'_'.$export_type.'_'.date('Ymd-his').'.csv';
$path = constant('WP_CONTENT_DIR').'/'.$filename;
$file = fopen($path, 'w');
if($file === false)
	exit();


$args = array(
					'post_type' 		 => $post_type,
					'posts_per_page' => -1,
					);
if(isset($export_excel['query']) && is_array($export_excel['query']))
	$args = array_merge($args, $export_excel['query']);
query_posts($args);
if(!have_posts())
	exit();


$taxonomies = $object->args['taxonomies'];
if(!is_array($taxonomies))
	$taxonomies = array();

$fields = $object->fields;
//récupération des champs à exporter
if(!isset($export_excel['fields'])):
	//default
	$export = array('ID', 'post_title', 'post_date');

	foreach($fields as $field => $data):
		if(!isset($data['type']) || ($data['type'] != 'wysiwyg' && $data['type'] != 'textarea'))
			$export[] = $field;
	endforeach;

	$tax = $object->args['taxonomies'];
	if(!is_array($tax))
		$tax = array();
	$export = array_merge($export, $tax);
else:

	if(!in_array('ID',  $export_excel['fields']))
		$export[] = 'ID';
	if(!in_array('post_title',  $export_excel['fields']))
		$export[] = 'post_title';
	if(!in_array('post_date',  $export_excel['fields']))
		$export[] = 'post_date';

	$export = array_merge($export, $export_excel['fields']);
endif;


//affichage des intitulés
$row = array();
foreach($export as $field):
	if(array_key_exists($field, $fields)):
		$row[] = $fields[$field]['title'];
	else:
		$row[] = $field;
	endif;
endforeach;
fputcsv($file, $row, ';');


//affichage des éléments
while(have_posts()):
	the_post();
	$row = array();

	$post_custom = get_post_custom();
	$export_post = get_object_vars($post);

	foreach($export as $field):
		if(is_wp_posts_fields($field))://wp_posts

			$row[] = $export_post[$field];

		elseif(in_array($field, $taxonomies))://taxonomies

			$terms = get_the_terms(get_the_ID(), $field);
			if(!empty($terms)):
				$tax_terms = array();
				foreach($terms as $term):
						$tax_terms[] = $term->name;
				endforeach;
				$row[] = implode(', ', $tax_terms);
			else:
				$row[] = '';
			endif;

		elseif(array_key_exists($field, $fields))://fields

			if(isset($post_custom[$field]))
				$meta_value = $post_custom[$field][0];
			else
				$meta_value = null;

			switch($fields[$field]['type']):
				case 'user':
					if(!empty($meta_value)):
						$user_data = get_userdata($meta_value);
						$row[] = $meta_value.' - '.($user_data->display_name).' <'.($user_data->user_email).'>';
					else:
					 $row[] = '';
					endif;
					break;

				case 'select':
					if(!empty($meta_value)):
						$sql = " SELECT post_title FROM {$wpdb->posts} WHERE ID = ".$meta_value;
						$title = $wpdb->get_var($sql);
						$row[] = $title;
					else:
						$row[] = '';
					endif;
					break;

				case 'post_reference':
					$data = $fields[$field];
					if(isset($data['ajax'])):

						if(isset($data['storage'])):
							$sql = " SELECT ".$data['storage']['foreign_key']." FROM ".$data['storage']['table']." WHERE ".$data['storage']['primary_key']." = ".get_the_ID();
							$res = $wpdb->get_col($sql);

							if(!empty($res)):
								$sql = " SELECT post_title FROM {$wpdb->posts} WHERE ID IN (".implode(',', $res).")";
								$res = $wpdb->get_col($sql);
							endif;
							$posts = implode(', ', $res);

						else:// recherche dans post_meta
							$meta_value = get_post_meta(get_the_ID(), $field);
							if(!empty($meta_value))
								$posts = implode(', ', $meta_value);
							else
								$posts = array();

						endif;

					else:// no ajax but query
						if(!empty($meta_value)):
							if(is_array($meta_value)):

								$sql = " SELECT post_title FROM {$wpdb->posts} WHERE ID IN (".implode(',', $meta_value).")";
								$res = $wpdb->get_col($sql);
								$posts = implode(', ', $res);

							else:

								$sql = " SELECT post_title FROM {$wpdb->posts} WHERE ID = ".$meta_value;
								$posts = $wpdb->get_var($sql);

							endif;
						endif;
					endif;

					$row[] = $posts;
					break;

				case 'checkbox':
					if(empty($meta_value) || $meta_value=='off')
						$row[] = 'off';
					else
						$row[] = 'on';
					break;

				default:
				case 'text':
					$row[] = $post_custom[$field][0];
					break;

			endswitch;

		else: // meta

			if(isset($post_custom[$field][0]))
				$row[] = $post_custom[$field][0];
			else
				$row[] = '';

		endif;
	endforeach;
	fputcsv($file, $row, ';');
endwhile;

fclose($file);

//téléchargement du fichier
ob_end_clean();
ini_set('zlib.output_compression','Off');
header('Pragma: public');
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");                  // Date in the past
header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');     // HTTP/1.1
header('Cache-Control: pre-check=0, post-check=0, max-age=0');    // HTTP/1.1
header ("Pragma: no-cache");
header("Expires: 0");
header('Content-Transfer-Encoding: none');
header('Content-Type: application/vnd.ms-excel;');                 // This should work for IE & Opera
header("Content-type: application/x-msexcel;");                    // This should work for the rest
header('Content-Disposition: attachment; filename="'.basename($filename).'"');
ob_clean();
flush();
/*Envoi du fichier dont le chemin est passé en paramètre*/
readfile($path);

exit();
?>