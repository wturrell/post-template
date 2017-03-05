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
// Some constants 
//#################################################################

//#################################################################
// The Widget class
if (!class_exists("PostTemplatesDashboardWidget")) {

class PostTemplatesDashboardWidget {
	var $options;
	
	/**
	* Constructor
	*/
	function __construct() {
		$this->load_options();
	}
	
	/**
	 * Function to add the widget to the dashboard 
	 */
	function add_dashboard_widget($widgets) {
		global $wp_registered_widgets;
		if (!isset($wp_registered_widgets['post-template-1'])) {
			return $widgets;
		}
		array_splice($widgets, sizeof($widgets)-1, 0, 'post-template-1');
		return $widgets;
	}
		
	/**
	* Function to register the Widget functions
	*/
	function register_widget() {		
		$name = __('Post Templates', POST_TEMPLATES_I18N_DOMAIN);
		$control_ops = array(
			'width' => 400, 'height' => 350, 
			'id_base' => 'post-template');
		$widget_ops = array(
			'classname' => 'post_template', 
			'description' => __('Widget that allows to create posts and pages from the dashboard.', 
								POST_TEMPLATES_PLUGIN_OPTIONS));

		if (!is_array($this->options)) {
			$this->options = array();
		}
								
		$registered = false;
//		foreach (array_keys($this->options) as $o) {
//			// Old widgets can have null values for some reason
//			//--
//			if (!isset($this->options[$o]['widget_title']))
//				continue;
//			
//			// $id should look like {$id_base}-{$o}
//			//--
//			$id = "post-template-$o";
//			$registered = true;
//			wp_register_sidebar_widget( 
//				$id, $name, 
//				array(&$this, 'render_widget'), 
//				$widget_ops, array( 'number' => $o ) );
//			wp_register_widget_control( 
//				$id, $name, 
//				array(&$this, 'render_control_panel'), 
//				$control_ops, array( 'number' => $o ) );
//		}

		// If there are none, we register the widget's existance with a generic template
		//--
		if (!$registered) {
			wp_register_sidebar_widget( 
				'post-template-1', $name, 
				array(&$this, 'render_widget'), 
				$widget_ops, array( 'number' => 1 ) );
			wp_register_widget_control( 
				'post-template-1', $name, 
				array(&$this, 'render_control_panel'), 
				$control_ops, array( 'number' => 1 ) );
		}
	}
	
	/**
	* Function to render the widget control panel
	*/
	function render_control_panel($widget_args=1) {
		global $wp_registered_widgets;
		static $updated = false;
		
		// Get the widget ID
		//--
		if (is_numeric($widget_args)) {
			$widget_args = array('number' => $widget_args);
		}
		$widget_args = wp_parse_args($widget_args, array('number' => -1));
		extract($widget_args, EXTR_SKIP);
	
		// TODO: hack for dashboard
		$number = 1;
		
		if (!$updated && !empty($_POST['sidebar'])) {
			$sidebar = (string) $_POST['sidebar'];

			$sidebars_widgets = wp_get_sidebars_widgets();
			if ( isset($sidebars_widgets[$sidebar]) )
				$this_sidebar = &$sidebars_widgets[$sidebar];
			else
				$this_sidebar = array();

			foreach ( $this_sidebar as $_widget_id ) {
				// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
				// since widget ids aren't necessarily persistent across multiple updates
				//--
				if (	'post_template' == $wp_registered_widgets[$_widget_id]['classname'] 
					&& 	isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
					
					$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
					if (!in_array( "post-template-$widget_number", $_POST['widget-id'])) // the widget has been removed.
						unset($this->options[$widget_number]);
				}
			}

			foreach ( (array) $_POST['widget_post_template'] as $widget_number => $widget_post_template ) {
				if ( !isset($widget_post_template['templates_to_show']) && isset($this->options[$widget_number]) ) // user clicked cancel
					continue;
					
				$this->options[$widget_number]['widget_title'] 	= strip_tags(stripslashes($widget_post_template['widget_title']));
				$this->options[$widget_number]['templates_to_show']	= $widget_post_template['templates_to_show'];
			}

			$this->save_options();
			$updated = true;
		}

		if ( -1 == $number ) {
			$widget_title 	= '';
			$templates_to_show 	= 0;
		} else {
			$widget_title 	= attribute_escape($this->options[$number]['widget_title']);
			$templates_to_show 	= $this->options[$number]['templates_to_show'];
		}
		
		if ($templates_to_show<0) {
			$templates_to_show = 0;
		}

		// The widget control
		//--
		
	?>
	
<input type="hidden" id="post_template-submit-<?php echo $number; ?>" name="widget_post_template[<?php echo $number; ?>][submit]" value="1" />
<p>
	<label><?php _e('Title:', POST_TEMPLATES_I18N_DOMAIN); ?><br/>
	<input style="width: 250px;" id="post_template-widget_title-<?php echo $number; ?>" name="widget_post_template[<?php echo $number; ?>][widget_title]" type="text" value="<?php echo $widget_title; ?>" /></label>
</p>

<br/>

<p>
	<label><?php _e('Number of templates to show (0 to show all):', POST_TEMPLATES_I18N_DOMAIN); ?>
	<br/>
	<input style="width: 250px;" id="post_template-templates_to_show-<?php echo $number; ?>" name="widget_post_template[<?php echo $number; ?>][templates_to_show]" type="text" value="<?php echo $templates_to_show; ?>" /></label>
</p>

<?php
	}
	
	/**
	* Function to render the widget
	*/
	function render_widget($args, $widget_args=1) {
		global $post_template_plugin, $post_templates_dao;
		
		// Get the options
		//--
		if (!is_array($args)) {
			$args = array();
		}
		
		extract($args, EXTR_SKIP);	
		if (is_numeric($widget_args)) {
			$widget_args = array('number' => $widget_args);
		}
		$widget_args = wp_parse_args($widget_args, array('number' => -1));
		extract($widget_args, EXTR_SKIP);
		
		// TODO: hack for dashboard
		$number = 1;
		
		$title = empty($this->options[$number]['widget_title']) 
					? __('Post Templates', POST_TEMPLATES_I18N_DOMAIN) 
					: $this->options[$number]['widget_title'];

		echo '<!-- Post Templates ' . $post_template_plugin->options['active_version'] . ' -->';	
		echo $before_widget; 
?>
		<?php 
			$templates = $post_templates_dao->get_templates('post');
			if (count($templates)>0) {
		?>
		<form id="create-post-from-template" action="<?php echo get_option('siteurl'); ?>/wp-admin/edit.php" method="get">
			<input type="hidden" name="page" value="post-template/includes/manage-templates-page.php" />
			<input type="hidden" name="action" value="create-post" />
			<p>
			<select name="template_id">
				<?php 
					$count = 0;
					$templates_to_show = $this->options[$number]['templates_to_show'];
					foreach ($templates as $template) {
				?>
				<option value="<?php echo $template->template_id; ?>">
					<?php echo $template->name; ?>
				</option>
				<?php
						$count++;
						if ($templates_to_show!=0 && $count>=$templates_to_show) {
							break;
						}
					}
				?>				
			</select>
			<input type="submit" name="submit" class="button-secondary" value="<?php echo _e('New post from template', POST_TEMPLATES_I18N_DOMAIN); ?>" />
			</p>
		</form>
		<?php
			}
		?>		
		
		<?php 
			$templates = $post_templates_dao->get_templates('page');
			if (count($templates)>0) {
		?>
		<form id="create-page-from-template" action="<?php echo get_option('siteurl'); ?>/wp-admin/edit.php" method="get">
			<input type="hidden" name="page" value="post-template/includes/manage-templates-page.php" />
			<input type="hidden" name="action" value="create-post" />
			<p>
			<select name="template_id">
				<?php 
					$count = 0;
					$templates_to_show = $this->options[$number]['templates_to_show'];
					foreach ($templates as $template) {
				?>
				<option value="<?php echo $template->template_id; ?>">
					<?php echo $template->name; ?>
				</option>
				<?php
						$count++;
						if ($templates_to_show!=0 && $count>=$templates_to_show) {
							break;
						}
					}
				?>				
			</select>
			<input type="submit" name="submit" class="button-secondary" value="<?php echo _e('New page from template', POST_TEMPLATES_I18N_DOMAIN); ?>" />
			</p>
		</form>	
		<?php
			}
		?>			
<?php		
		echo $after_widget;
		
		echo '<!-- Post Templates ' . $post_template_plugin->current_version . ' -->';
	}
	
	/**
	* Load the options from database (set default values in case options are not set)
	*/
	function load_options() {
		$this->options = get_option(POST_TEMPLATES_WIDGET_OPTIONS);
		
		if ( !is_array($this->options) ) {
			$this->options = array();
		}
	}
	
	/**
	* Save options to database
	*/
	function save_options() {
		update_option(POST_TEMPLATES_WIDGET_OPTIONS, $this->options);
	}
	
	/**
	* Helper function to output the checked attribute of a checkbox
	*/
	function render_checked($var) {
		if ($var==1 || $var==true) {
			echo 'checked="checked"';
		}
	}
	
	/**
	* Helper function to output the selected attribute of an option
	*/
	function render_selected($var) {
		if ($var==1 || $var==true) {
			echo 'selected="selected"';
		}
	}
} // class PostTemplatesDashboardWidget

} // if (!class_exists("PostTemplatesDashboardWidget"))
//#################################################################



?>