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
// The plugin class
if (!class_exists("PostTemplatesPlugin")) {

class PostTemplatesPlugin {

	var $current_version = '3.4.7';
	var $options;
	var $post_templates_dao;
	
	/**
	* Constructor
	*/
	function __construct($_post_templates_dao) {
		$this->post_templates_dao = $_post_templates_dao;
		$this->load_options();
		$this->one_time_actions();
	}
	
	/**
	* Function to be called when the plugin is activated
	*/
	function activate() {		
		$dao = $this->post_templates_dao;
		$active_version = $this->options['active_version'];
		$active_db_version = $this->options['active_db_version'];
		
		if (!isset($active_version) || $active_version=='') {
			$active_version = get_option('post_templates_version');
		}
		
		if (!isset($active_db_version) || $active_db_version=='') {
			$active_db_version = get_option('post_templates_db_version');
		}

		if ($active_version==$this->current_version) {
			// do nothing
		} else {
			if ($active_version=='') {			
				$this->add_default_capabilities();
				
				add_option(
					POST_TEMPLATES_PLUGIN_OPTIONS, 
					$this->options, 
					'Post Templates plugin options');
			} else {
				if (($installed_version>='1.1.1') && ($installed_version<='1.2.1')) {
					delete_option('post_templates_view_user_level');
					delete_option('post_templates_admin_user_level');
					delete_option('post_templates_create_user_level');
				} else if ($installed_version=='2.0') {
					// Remove old capabilities that caused problem (no "dash" allowed in capability name, only spaces, letters and digits) 
					$role = get_role('administrator');
					$role->remove_cap('create-templates');
					$role->remove_cap('delete-templates');
					$role->remove_cap('edit-templates');
					$role->remove_cap('view-templates');
					$role->remove_cap('use-templates');
					
					$role = get_role('editor');
					$role->remove_cap('create-templates');
					$role->remove_cap('delete-templates');
					$role->remove_cap('edit-templates');
					$role->remove_cap('view-templates');
					$role->remove_cap('use-templates');
					
					$role = get_role('author');
					$role->remove_cap('view-templates');
					$role->remove_cap('use-templates');
				} 
				
				if ($installed_version<='3.0.0') {
					$this->options['allow_rich_editor'] = get_option('post_templates_allow_rich_editor_option');
					delete_option('post_templates_allow_rich_editor_option');
					delete_option('post_templates_db_version');
					delete_option('post_templates_version');
				}
			
				add_option(
					POST_TEMPLATES_PLUGIN_OPTIONS, 
					$this->options, 
					'Post Templates plugin options');
					
				$this->add_default_capabilities();	
			}
		}
	
		// Update DB structure if necessary
		if ($active_db_version == '') {
			$dao->create_db_structure();
			$dao->insert_sample_data();
		} else if ($active_db_version < $dao->db_version) {
			$dao->update_db_structure($active_db_version);
		}
		
		// Update version number & save new options
		$this->options['active_version'] = $this->current_version;
		$this->options['active_db_version'] = $dao->db_version;
		$this->save_options();
	}
	
	/**
	* Add default capabilities to the roles
	*/
	function add_default_capabilities() {
		$role = get_role('administrator');
		$role->add_cap(POST_TEMPLATES_CREATE_TEMPLATES_CAP);
		$role->add_cap(POST_TEMPLATES_DELETE_TEMPLATES_CAP);
		$role->add_cap(POST_TEMPLATES_EDIT_TEMPLATES_CAP);
		$role->add_cap(POST_TEMPLATES_VIEW_TEMPLATES_CAP);
		$role->add_cap(POST_TEMPLATES_USE_TEMPLATES_CAP);
		
		$role = get_role('editor');
		$role->add_cap(POST_TEMPLATES_CREATE_TEMPLATES_CAP);
		$role->add_cap(POST_TEMPLATES_DELETE_TEMPLATES_CAP);
		$role->add_cap(POST_TEMPLATES_EDIT_TEMPLATES_CAP);
		$role->add_cap(POST_TEMPLATES_VIEW_TEMPLATES_CAP);
		$role->add_cap(POST_TEMPLATES_USE_TEMPLATES_CAP);
		
		$role = get_role('author');
		$role->add_cap(POST_TEMPLATES_VIEW_TEMPLATES_CAP);
		$role->add_cap(POST_TEMPLATES_USE_TEMPLATES_CAP);
	}
	
	/**
	* Add the administration menus
	*/
	function add_admin_menus() {
		global $_registered_pages;

		$_registered_pages[get_plugin_page_hookname('post-template/includes/manage-templates-page.php','')] = true;
		$_registered_pages[get_plugin_page_hookname('post-template/includes/edit-page-template-page.php','')] = true;
		$_registered_pages[get_plugin_page_hookname('post-template/includes/edit-post-template-page.php','')] = true;
		$_registered_pages[get_plugin_page_hookname('post-template/includes/help-page.php','')] = true;
		$_registered_pages[get_plugin_page_hookname('post-template/includes/import-page.php','')] = true;
		$_registered_pages[get_plugin_page_hookname('post-template/includes/options-page.php','')] = true;
	
		if (current_user_can('manage_options')) {
			add_options_page( __('Post Templates', POST_TEMPLATES_I18N_DOMAIN), 
				__('Post Templates', POST_TEMPLATES_I18N_DOMAIN), 0,
				'post-template/includes/options-page.php' );
		}
		
		if (	current_user_can(POST_TEMPLATES_VIEW_TEMPLATES_CAP) 
			|| 	current_user_can(POST_TEMPLATES_CREATE_TEMPLATES_CAP)) {
			
			add_menu_page(__('Templates', POST_TEMPLATES_I18N_DOMAIN), 
				__('Templates', POST_TEMPLATES_I18N_DOMAIN), 
				0, 
				'post-template/includes/manage-templates-page.php', 
				'', 
				WP_CONTENT_URL . '/plugins/post-template/images/menu-icon.png' );
			
			if (current_user_can(POST_TEMPLATES_CREATE_TEMPLATES_CAP)) {
				add_submenu_page( 'post-template/includes/manage-templates-page.php',
					__('New Post Template', POST_TEMPLATES_I18N_DOMAIN), 
					__('New Post Template', POST_TEMPLATES_I18N_DOMAIN), 
					0, 
					'admin.php?page=post-template/includes/edit-post-template-page.php' );
				
				add_submenu_page( 'post-template/includes/manage-templates-page.php',
					__('New Page Template', POST_TEMPLATES_I18N_DOMAIN), 
					__('New Page Template', POST_TEMPLATES_I18N_DOMAIN), 
					0, 
					'admin.php?page=post-template/includes/edit-page-template-page.php' );
			}
		}
	}
	
	/**
	* Send a POST request from the code
	*/
	function send_post_request($url, $referer, $_data) {	 
		// convert variables array to string:
		$data = array();    
		while(list($n,$v) = each($_data)){
			$data[] = "$n=$v";
		}    
		$data = implode('&', $data);
		// format --> test1=a&test2=b etc.
	 
		// parse the given URL
		$url = parse_url($url);
		if ($url['scheme'] != 'http') { 
			return array("", "false");
		}
	 
		// extract host and path:
		$host = $url['host'];
		$path = $url['path'];
	 
		// open a socket connection on port 80
		$fp = fsockopen($host, 80);
		if (!$fp) { 
			return array("", "false");
		}
	 
		// send the request headers:
		fputs($fp, "POST $path HTTP/1.1\r\n");
		fputs($fp, "Host: $host\r\n");
		fputs($fp, "Referer: $referer\r\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Content-length: ". strlen($data) ."\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $data);
	 
		$result = ''; 
		while(!feof($fp)) {
			// receive the results of the request
			$result .= fgets($fp, 128);
		}
	 
		// close the socket connection:
		fclose($fp);
	 
		// split the result header from the content
		$result = explode("\r\n\r\n", $result, 2);
	 
		$header = isset($result[0]) ? $result[0] : '';
		$content = isset($result[1]) ? $result[1] : '';
	 
		// return as array:
		return array($header, $content);
	}
	 
	/**
	* Do all one time actions that need to be done
	*/
	function one_time_actions() {
        $current_time = time();
        $last_register_attempt = empty( $this->options['last_register_attempt'] ) ? 0 : (int) $this->options['last_register_attempt'];
        
        if ( (string) $this->options['registered']!=$this->current_version && ( $current_time - $last_register_attempt > 600 ) ) {	
			$host = "http://www.vincentprat.info/wp_plugins_register.php";
			$params = array(
				'plugin_name' 		=> 'post-templates',
				'plugin_version' 	=> $this->current_version,
				'host' 				=> get_option('siteurl'),
				'valid' 			=> 'true'
			);
			
			$old_err_level = error_reporting(E_ERROR);
			list($header, $content) = $this->send_post_request($host, get_option('siteurl'), $params);
			error_reporting($old_err_level);
			
			if ($content=='true') {
				$this->options['registered'] = $this->current_version;
            }
            $this->options['last_register_attempt'] = time();
            $this->save_options();
		}
	}
	
	/**
	* Enqueue the necessary javascript in the administration interface
	*/
	function add_admin_javascript() {
		if (	0!=preg_match("/manage-templates-page\.php/i", $_SERVER['REQUEST_URI']) ) {
			wp_enqueue_script('admin-forms');
		}
		
		if (	0!=preg_match("/edit-post-template-page\.php/i", $_SERVER['REQUEST_URI']) 
			||	0!=preg_match("/edit-page-template-page\.php/i", $_SERVER['REQUEST_URI']) ) {
			
			wp_enqueue_script('post');
				
			if (user_can_richedit() && $this->options['allow_rich_editor']) {
				add_action( 'admin_head', 'wp_tiny_mce' );
				wp_enqueue_script('post');
				wp_enqueue_script( 'editor' );
					
				if (function_exists('add_thickbox')) {
					// WordPress 2.6
					//--
					add_thickbox(); 
				} else {
					wp_enqueue_script('thickbox');
				}
				
				wp_enqueue_script('word-count');
			}
		}
	}
	
	/**
	* Add a link to templatize under the post/page title in the management screen
	*/
	function add_post_templatize_link($actions, $post) {
		if (current_user_can(POST_TEMPLATES_CREATE_TEMPLATES_CAP)) {
			$actions["Templatize"] = "<a href='admin.php?page=post-template/includes/manage-templates-page.php&action=create-template&post=" . $post->ID 
					. "' title='" . __("Create a template from this post", POST_TEMPLATES_I18N_DOMAIN) 
					. "'>" . __("Templatize", POST_TEMPLATES_I18N_DOMAIN) . "!</a>";
		}
		return $actions;
	}
	
	/**
	* Add a link to templatize under the post/page title in the management screen
	*/
	function add_page_templatize_link($actions, $page) {
		if (current_user_can(POST_TEMPLATES_CREATE_TEMPLATES_CAP)) {
			$actions["Templatize"] = "<a href='admin.php?page=post-template/includes/manage-templates-page.php&action=create-template&post=" . $page->ID 
					. "' title='" . __("Create a template from this page", POST_TEMPLATES_I18N_DOMAIN) 
					. "'>" . __("Templatize", POST_TEMPLATES_I18N_DOMAIN) . "!</a>";
		}
		return $actions;
	}

	/**
	* Add a related link to the post edit page to create a template from current post
	*/
	function add_post_relatedlink() {
		global $post_ID;
		if (isset($post_ID) && current_user_can(POST_TEMPLATES_CREATE_TEMPLATES_CAP)) {
	?>
			<li><a href="admin.php?page=post-template/includes/manage-templates-page.php&action=create-template&post=<?php echo $post_ID; ?>">
				<?php _e('Create a template from this post', POST_TEMPLATES_I18N_DOMAIN); ?></a></li>
	<?php
		}
	}

	/**
	* Add a related link to the page edit page to create a template from current page
	*/
	function add_page_relatedlink() {
		global $post_ID;
		if (isset($post_ID) && current_user_can(POST_TEMPLATES_CREATE_TEMPLATES_CAP)) {
	?>
			<li><a href="admin.php?page=post-template/includes/manage-templates-page.php&action=create-template&post=<?php echo $post_ID; ?>">
				<?php _e('Create a template from this page', POST_TEMPLATES_I18N_DOMAIN); ?></a></li>
	<?php
		}
	}

	/**
	 * Get the currently registered user
	 */
	function get_current_user() {
		if (function_exists('wp_get_current_user')) {
			return wp_get_current_user();
		} else if (function_exists('get_currentuserinfo')) {
			global $userdata;
			get_currentuserinfo();
			return $userdata;
		} else {
			$user_login = $_COOKIE[USER_COOKIE];
			$current_user = $wpdb->get_results("SELECT * FROM $wpdb->users WHERE user_login='$user_login'");
			return $current_user;
		}
	}
	
	/**
	 * Escape single quotes, specialchar double quotes, and fix line endings.
	 */
	function js_escape($text) {
		if (function_exists('js_escape')) {
			return js_escape($text);
		} else {
			$safe_text = str_replace('&&', '&#038;&', $text);
			$safe_text = str_replace('&&', '&#038;&', $safe_text);
			$safe_text = preg_replace('/&(?:$|([^#])(?![a-z1-4]{1,8};))/', '&#038;$1', $safe_text);
			$safe_text = str_replace('<', '&lt;', $safe_text);
			$safe_text = str_replace('>', '&gt;', $safe_text);
			$safe_text = str_replace('"', '&quot;', $safe_text);
			$safe_text = str_replace('&#039;', "'", $safe_text);
			$safe_text = preg_replace("/\r?\n/", "\\n", addslashes($safe_text));
			return safe_text;
		}
	}

	/**
	 * Create a post from a template
	 * @return The ID of the post just created
	 */
	function create_post_from_template($template) {	
		global $wpdb;
		
		$new_post_date = current_time('mysql');
		$new_post_author = $this->get_current_user();
		
		$post_data = array(
			'post_author' => $new_post_author->ID,
			'post_content' => $wpdb->escape($template->content),
			'post_title' => $wpdb->escape($template->title), 
			'post_excerpt' => $wpdb->escape($template->excerpt), 
			'post_status' => 'draft', 
			'post_category' => array(),
			'post_type' => $template->type, 
			'comment_status' => $template->comment_status, 
			'ping_status' => $template->ping_status, 
			'post_password' => $template->password,
			'to_ping' => $template->to_ping, 
			'post_parent' => $template->parent,
			'post_mime_type' => ''
		);
		
		$new_post_id = wp_insert_post($post_data);
		
		// Assign categories and tags to posts
		if ($template->type=='post') {
			$categories = explode(",", $template->categories);
			if (count($categories)!=0) {
				wp_set_post_categories($new_post_id, $categories);
			}	
			
			wp_set_post_tags($new_post_id, $template->tags);
		}
		
		return $new_post_id;
	}
	
	/**
	* Create a template from a post
	*/
	function create_template_from_post($post_id) {
		global $wpdb;
		
		// Try to get the post
		$post = get_post($post_id, OBJECT);
		
		$tag_array = wp_get_post_tags($post_id, "fields=names");
		$cat_array = wp_get_post_categories($post_id, "fields=ids");
		
		$tags = (isset($tag_array) && count($tag_array)!=0) ? implode(",", $tag_array) : "";
		$categories = isset($cat_array) && count($cat_array)!=0 ? implode(",", $cat_array) : "";
		
		if ($post->post_type=='template' || $post->post_type=='post') {
			$type = 'post';
		} else if ($post->post_type=='template-page' || $post->post_type=='page') {
			$type = 'page';
		} else {
			$type = 'unknown';
		}
		
		$new_template_id = $this->post_templates_dao->save_template(
				-1, $type, $post->post_name, 
				$post->post_title, $post->post_content, $post->post_excerpt, 
				$categories, $tags, 
				$post->post_password, 
				$post->comment_status, $post->ping_status, $post->to_ping, 
				$post->post_parent);
				
		return $new_template_id;
	}

	/**
	* Function to output a list of all the categories as a list of checkboxes
	*/
	function list_categories( $selected_categories ) {
		$categories = get_categories('hide_empty=0');
		$this->write_categories( $categories, $selected_categories );
	}

	/**
	* Function to output a list of categories with checkbox selection
	*/
	function write_categories( $categories, $selected_categories, $parent = 0 ) {
		if (!isset($selected_categories) || null==$selected_categories) {
			$selected_categories = array();
		}

		foreach ( $categories as $category ) {
			if ($category->parent == $parent) {
				echo '<li id="category-' . $category->term_id . '">';
				echo '<label for="in-category-' . $category->term_id . '" class="selectit">';
				echo '<input value="' . $category->term_id . '" type="checkbox" name="post_category[]" id="in-category-', $category->term_id, '"';
				
				if (isset($category->term_id) && in_array($category->term_id, $selected_categories)) {
					echo ' checked="checked"';
				}
				
				echo '/> ' . wp_specialchars( apply_filters('the_category', $category->name)) . "</label></li>";
			
				if ($this->has_children_categories($categories, $category->term_id)) {
					echo "<ul>\n";
					$this->write_categories($categories, $selected_categories, $category->term_id);
					echo "</ul>\n";
				}
			}
		}
	}
	
	/**
	* Functions to tell if a category has children
	*/
	function has_children_categories( $categories, $parent_id ) {
		foreach ( $categories as $category ) {
			if ($category->parent == $parent_id) {
				return true;
			}
		}
		return false;
	}
	
	/**
	* Load the options from database (set default values in case options are not set)
	*/
	function load_options() {
		$this->options = get_option(POST_TEMPLATES_PLUGIN_OPTIONS);
		
		if ( !is_array($this->options) ) {
			$this->options = array(
				'active_version'		=> '',
				'active_db_version'		=> '',
				'registered'			=> false,
				'allow_rich_editor'		=> true
			);
		}
	}
	
	/**
	* Save options to database
	*/
	function save_options() {
		update_option(POST_TEMPLATES_PLUGIN_OPTIONS, $this->options);
	}

} // class PostTemplatesPlugin {

} // if (!class_exists("PostTemplatesPlugin")) {


?>