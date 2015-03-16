<?php
require_once(WP_PLUGIN_DIR.'/custom_types/custom_types.php');

class Test extends CustomType {

	var $slug = "test";
	var $args = array(
        'label' => 'Test',
        'singular_label' => 'Test',
        'capability_type' => 'post',
        'supports' => array('title', 'editor'),
				'rewrite' => true
				);
	var $fields = array(
			'schedule' => array('title' => 'Horaire'),
			'length' => array('title' => 'Durée'),
			'channel' => array(
						'type' => 'post_reference', 
						'title' => 'Chaine', 
						'query' => array('post_type' => 'channel'), 
						'single' => true
					),
			'intervenants' => array(
						'type' => 'post_reference', 
						'title' => 'Intervenants',
						'query' => array('post_type' => array('page', 'post')),
						'ajax' => true
					)	,
			'externe' => array(
						'title' => 'Intervenants Externe'
					)	,
			'organisateurs' => array(
						'type' => 'post_reference', 
						'title' => 'Organisateurs',
						'query' => array('post_type' => array('page', 'post')),
						'ajax' => true
					)

		);

}

add_action("init", "TestInit");
function TestInit() { 
	new Test(); 
}

?>