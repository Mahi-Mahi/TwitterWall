<?php
// Dossier à crée dans template: 
// Pour les styles => editor-styles/ votre-css.css
// Pour les templates => editor-templates/ votre-template.html

class CustomPage extends CustomType {

	var $slug = "page";
	var $post_type_slug = "custom-page";
	var $args = array(
        'label' => 'Custom page',
        'singular_label' => 'Custom page',
        'capability_type' => 'page',
        'supports' => array('title', 'editor', 'custom-fields', 'comments', 'thumbnail'),
		 		'dont_register' => true
				);

	var $fields =	array(
		 'editor-template' => array(
								'type' => 'editor_template',
								'title' => 'Template dans l\'editeur',
								'options' => array(
												array(
													'template' => 'tableau',
													'style' => 'all'
												),
												array(
													'template' => 'column',
													'style' => 'column',
													'blocks' => array(
														  'left-block' => 'Mon block de gauche',
														  'right-block' => 'Mon block de droite',
													 ),
													 'default' => true
												),
									 			array(
													'template' => 'cube',
												),
												array(
													'style' => 'custom-login'
												)
											),
								'position' => 'side'
							)
	);

}

add_action("init", "CustomPageInit");
function CustomPageInit() {
	new CustomPage(); 
}

?>