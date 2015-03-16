<?php
require_once(WP_PLUGIN_DIR.'/custom_types/custom_types.php');

class Person extends CustomType {

	var $slug = "person";
	var $args = array(
        'label' => 'Personnes',
        'capability_type' => 'page',
        'supports' => array('title', 'editor', 'thumbnail'),
				'rewrite' => true,
				'template' => 'person',
				'taxonomies' => array('post_tag')
				);
	var $labels = array(
				'name' => 'Personnes',
	      'singular_label' => 'Personne',
				'add_new' => 'Ajouter',
				'add_new_item' => 'Ajouter une personne ',
				'edit_item' => 'Modifier une personne',
				'new_item' => 'Nouvelle Personne',
				'view_item' => 'Voir la personne',
				'not_found' => 'Aucune personne trouvée',
				'not_found_in_trash' => 'Aucune personne trouvée dans la corbeille',
				'parent_item_colon' => 'Personne parente : '
			);
  
	var $fields = array(
		'url' => array('title' => 'URL'),
		'menu-deroulant-statique' => array('title' => 'Menu Déroulant Statique', 'args' => array('data' => array('histoire-moderne' => "Histoire Moderne", 'histoire-comptemporaine' => "Histoire Comptemporaine")))	
		);

}

add_action("init", "PersonInit");
function PersonInit() { 
	new Person(); 
}

?>