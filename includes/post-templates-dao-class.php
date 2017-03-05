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
// The class to manage database accesses to the Post Templates tables
if (!class_exists("PostTemplatesDAO")) {


class PostTemplatesDAO {

	var $db_version = '1.1';
	var $templates_table_name = '';

	/**
	* Constructor
	*/
	function __construct() {
		global $wpdb;
		$this->templates_table_name = $wpdb->prefix . "pt_templates";
	}
	
	/**
	* Create the tables necessary to the plugin
	*/
	function create_db_structure() {
		// Create the main template table
		$sql = "CREATE TABLE " . $this->templates_table_name . " (
			template_id BIGINT(20) NOT NULL AUTO_INCREMENT, 
			type ENUM('page', 'post') NOT NULL, 
			title TEXT NOT NULL, 
			name VARCHAR(200) NOT NULL, 
			content LONGTEXT NOT NULL, 
			excerpt TEXT NOT NULL, 
			categories TEXT NOT NULL, 
			tags TEXT NOT NULL, 
			password VARCHAR(20) NOT NULL, 
			comment_status ENUM('open', 'closed', 'registered_only') NOT NULL,
			ping_status ENUM('open', 'closed') NOT NULL,
			to_ping TEXT NOT NULL,
			parent BIGINT(20) NOT NULL,
			PRIMARY KEY  (template_id)  
		); ";
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	    dbDelta($sql);
	}
	
	/**
	* Update the tables necessary to the plugin
	*/
	function update_db_structure($active_db_version) {
		global $wpdb;
		
		if ($active_db_version<'1.1') {
			$wpdb->query("ALTER TABLE " . $this->templates_table_name . " CHANGE `slug` `name` VARCHAR(200) NOT NULL;");
		}
	}
	
	/**
	* Insert sample data
	*/
	function insert_sample_data() {
		$this->save_template(
				-1, 'post', 
				__('my_first_post_template', POST_TEMPLATES_I18N_DOMAIN), 
				__('My First Post Template!', POST_TEMPLATES_I18N_DOMAIN), 
				__('This is the content of your first post template...', POST_TEMPLATES_I18N_DOMAIN), 
				__('This is the excerpt of your first post template...', POST_TEMPLATES_I18N_DOMAIN), 
				'', 
				'sample template,post templates', 
				'', 
				'open', 
				'closed', 
				'', 
				0);
		
		$this->save_template(
				-1, 'page', 
				__('my_first_page_template', POST_TEMPLATES_I18N_DOMAIN), 
				__('My First Page Template!', POST_TEMPLATES_I18N_DOMAIN), 
				__('This is the content of your first page template...', POST_TEMPLATES_I18N_DOMAIN), 
				__('This is the excerpt of your first page template...', POST_TEMPLATES_I18N_DOMAIN), 
				'', 
				'', 
				'', 
				'closed', 
				'closed', 
				'', 
				0);
	}

	/**
	 * Get a template from the database
	 */
	function get_template($id) {
		global $wpdb;
		$template = $wpdb->get_row("SELECT * FROM " . $this->templates_table_name . " WHERE template_id=$id");
		return $this->stripslashes_in_template($template);
	}

	/**
	 * Get a list of templates of a certain type
	 * @param $type Can be 'all', 'post' or 'page'
	 */
	function get_templates($type = 'all', $order_by = 'name asc') {
		global $wpdb;
		if ($type=='all') {
			$templates = $wpdb->get_results("SELECT * FROM " . $this->templates_table_name . " ORDER BY $order_by");
		} else {
			$templates = $wpdb->get_results("SELECT * FROM " . $this->templates_table_name . " WHERE type='$type' ORDER BY $order_by");
		}
		
		foreach ($templates as $template) {
			$this->stripslashes_in_template($template);
		}
		
		return $templates;
	}

	/**
	 * Delete a template from the database
	 */
	function delete_template($id) {
		global $wpdb;
		$wpdb->query("DELETE FROM " . $this->templates_table_name . " WHERE template_id=$id");
	}

	/**
	 * Save a template to the database (return the template id in case of template creation)
	 */
	function save_template(
				$template_id, $template_type, $template_name, 
				$template_title, $template_content, $template_excerpt, 
				$template_categories, $template_tags, 
				$template_password, 
				$template_comment_status, $template_ping_status, $template_to_ping, 
				$template_parent) {

		global $wpdb;
		
		if (!isset($template_parent)) {
			$template_parent = 0;
		}
		
		// Create a title if none is provided
		//--
		if ($template_name=="") {
			if ($template_title!="") {
				$template_name = sanitize_title_with_dashes($template_title);
			} else {
				$template_name = "No name";
			}
		}
		
		if ($template_id==-1) {
			$sql  = "INSERT INTO " . $this->templates_table_name;
	 		$sql .= " (type, title, name, content, excerpt, categories, tags, password, comment_status, ping_status, to_ping, parent ) VALUES ";
			$sql .= " ( ";
			$sql .= "'" . $template_type . "', ";
			$sql .= "'" . $wpdb->escape($template_title) . "', ";
			$sql .= "'" . $wpdb->escape($template_name) . "', ";
			$sql .= "'" . $wpdb->escape($template_content) . "', ";
			$sql .= "'" . $wpdb->escape($template_excerpt) . "', ";
			$sql .= "'" . $wpdb->escape($template_categories) . "', ";
			$sql .= "'" . $wpdb->escape($template_tags) . "', ";
			$sql .= "'" . $wpdb->escape($template_password) . "', ";
			$sql .= "'" . $wpdb->escape($template_comment_status) . "', ";
			$sql .= "'" . $wpdb->escape($template_ping_status) . "', ";
			$sql .= "'" . $wpdb->escape($template_to_ping) . "', ";
			$sql .= 	  $wpdb->escape($template_parent) . " ";
			$sql .= " ) ";
		} else {
			$sql  = "UPDATE " . $this->templates_table_name . " SET ";
			$sql .= "type='" 			. $template_type . "', ";
			$sql .= "title='" 			. $wpdb->escape($template_title) . "', ";
			$sql .= "name='" 			. $wpdb->escape($template_name) . "', ";
			$sql .= "content='" 		. $wpdb->escape($template_content) . "', ";
			$sql .= "excerpt='" 		. $wpdb->escape($template_excerpt) . "', ";
			$sql .= "categories='" 		. $wpdb->escape($template_categories) . "', ";
			$sql .= "tags='" 			. $wpdb->escape($template_tags) . "', ";
			$sql .= "password='" 		. $wpdb->escape($template_password) . "', ";
			$sql .= "comment_status='" 	. $wpdb->escape($template_comment_status) . "', ";
			$sql .= "ping_status='" 	. $wpdb->escape($template_ping_status) . "', ";
			$sql .= "to_ping='" 		. $wpdb->escape($template_to_ping) . "', ";
			$sql .= "parent=" 			. $wpdb->escape($template_parent) . " ";
			$sql .= "WHERE template_id=$template_id ";
		}
		
		$wpdb->query($sql);
		
		if ($template_id==-1) {
			$max_id = $wpdb->get_var("SELECT max(template_id) FROM " . $this->templates_table_name);
			return $max_id;
		} else {
			return $template_id;
		}
	}
	
	/**
	* Remove unnecessary backslashes in the template fields
	*/
	function stripslashes_in_template($template) {
		$template->title 	= stripslashes($template->title);
		$template->content 	= stripslashes($template->content);
		$template->excerpt 	= stripslashes($template->excerpt);
		$template->name 	= stripslashes($template->name);
		$template->tags 	= stripslashes($template->tags);
		return $template;
	}
	
} // class PostTemplatesDAO {

} // if (!class_exists("PostTemplatesDAO")) {

?>