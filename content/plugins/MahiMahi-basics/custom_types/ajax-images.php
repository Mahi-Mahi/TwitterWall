<?php 
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

if(!isset($_REQUEST['action']))
	exit();
	
switch($_REQUEST['action']):
	case 'delete':
		$post_id = $_REQUEST['post_id'];
		$field = $_REQUEST['field'];
		delete_post_meta($post_id, $field);
		exit();
		break;
	case 'choose':
		$post_id = $_REQUEST['id'];
		$field = $_REQUEST['field'];
		$meta_value = $_REQUEST['attach_id'];
		update_post_meta($post_id, $field, $meta_value);
		exit();
		break;
endswitch;

?>