<?php	
/*  Copyright 2006-2009 Vincent Prat
*/

//#################################################################
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('You are not allowed to call this page directly.'); 
}
//#################################################################


//#################################################################
// The Importer class for old 1.x plugin version
if (!class_exists("PostTemplates1xImporter")) {

class PostTemplates1xImporter {

	var $imported_templates = 0;
	var $deleted_templates = 0;
	var $parsed_posts = 0;
	
	/*
	* Constructor
	*/
	function __construct() {
	}

	/*
	* Function to actually import the old templates
	*/
	function import($copy_old_templates = false, $delete_old_templates = false) {
		global $wpdb, $post_templates_dao;
		
		$imported_templates = 0;
		$deleted_templates = 0;
		$parsed_posts = 0;

		$posts = $wpdb->get_results("SELECT * FROM " . $wpdb->posts . " WHERE post_type='template' OR post_type='template-page'");		
		foreach ($posts as $post) {
			if ($copy_old_templates) {
				$post_templates_dao->create_template_from_post($post->ID);
				$imported_templates++;
			}
			
			if ($delete_old_templates) {
				wp_delete_post($post->ID);
				$deleted_templates++;
			}
			
			$parsed_posts++;
		}
	}
	
} // class PostTemplates1xImporter {

} // if (!class_exists("PostTemplates1xImporter")) {

