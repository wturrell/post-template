<?php	
/*  Copyright 2006-2009 Vincent Prat
*/

if (!current_user_can(POST_TEMPLATES_CREATE_TEMPLATES_CAP) || !current_user_can(POST_TEMPLATES_EDIT_TEMPLATES_CAP)) {
	wp_die(__("You are not allowed to create or edit templates!", POST_TEMPLATES_I18N_DOMAIN));
}

global $post_templates_plugin, $post_templates_dao;
?>

<div class="wrap">

<?php	
// Check if we have to save the template or rather to load one
if (isset($_POST['saveit'])) {	
	if (!isset($_POST['name']) || $_POST['name']=='') {
?>
<div id="message" class="error fade">
	<p><?php _e('You must give a name to the template. Template has not been saved.', POST_TEMPLATES_I18N_DOMAIN); ?></p>
</div>
<?php
	} else {
		$template_id = $post_templates_dao->save_template(
			$_POST['template_id'], $_POST['type'], $_POST['name'], 
			$_POST['title'], $_POST['content'], $_POST['excerpt'], 
			isset($_POST['post_category']) ? implode(",", $_POST['post_category']) : array(), 
			$_POST['tags_input'], 
			$_POST['password'], 
			$_POST['comment_status']=='open' ? 'open' : 'closed', 
			$_POST['ping_status']=='open' ? 'open' : 'closed', 
			$_POST['to_ping'], 
			$_POST['parent']);
?>
<div id="message" class="updated fade">
	<p><?php echo sprintf(__('Template saved.', POST_TEMPLATES_I18N_DOMAIN), $del_count); ?></p>
</div>
<?php
	}
}

if (!isset($_GET['template_id']) && !isset($template_id)) {
	$template_id = -1;
	
	$template_type = 'post';
	$template_name = '';
	$template_title = '';
	$template_content = '';
	$template_categories = '';
	$template_tags = '';
	$template_password = '';
	$template_comment_status = 'open';
	$template_ping_status = 'open';		
	$template_to_ping = '';
	$template_excerpt = '';
	$template_parent = 0;
} else {
	if (!isset($template_id)) {
		$template_id = $_GET['template_id'];
	}
	
	$template = $post_templates_dao->get_template($template_id);
	
	if (!isset($template) || $template==null) {			
		wp_die(__('Template not found in the database', POST_TEMPLATES_I18N_DOMAIN));
	}
	
	if ($template->type!='post') {
		wp_die(__('You can only edit post templates in this page', POST_TEMPLATES_I18N_DOMAIN));
	}
	
	$template_type = 'post';
	$template_name = $template->name;
	$template_title = $template->title;
	$template_content = $template->content;
	$template_categories = explode(",", $template->categories);
	$template_tags = $template->tags;
	$template_password = $template->password;
	$template_comment_status = $template->comment_status;
	$template_ping_status = $template->ping_status;		
	$template_to_ping = $template->to_ping;
	$template_excerpt = $template->excerpt;
	$template_parent = $template->parent;
}
?>

<h2><?php _e('Write Post Template', POST_TEMPLATES_I18N_DOMAIN) ?></h2>

<form name="post_template" action="" method="post" id="simple">

	<input type="hidden" id="user-id" name="user_ID" value="<?php echo $user_ID; ?>" />
	<input type="hidden" name="page" value="post-template/includes/edit-post-template-page.php" />
	<input type="hidden" name="continue_editing" value="true" />
	<input type="hidden" name="saveit" value="saveit" />
	<input type="hidden" name="template_id" value="<?php echo $template_id; ?>" />
	<input type="hidden" name="type" value="<?php echo $template_type; ?>" />
	<input type="hidden" name="parent" value="<?php echo $template_parent; ?>" />

	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div id="side-info-column" class="inner-sidebar">
			<div id="side-sortables" class="meta-box-sortables ui-sortable" style="position: relative;">
				<div id="submitdiv" class="postbox">
					<div class="handlediv"><br/></div>
					<h3 class="hndle">
						<span><?php _e('Save', POST_TEMPLATES_I18N_DOMAIN) ?></span>
					</h3>
					
					<div class="inside">
						<div id="submitpost" class="submitbox">
							<div id="minor-publishing-actions">
								<ul>
									<li>
										<strong><?php _e('Name (required)', POST_TEMPLATES_I18N_DOMAIN) ?></strong><br/>
										<input name="name" type="text" size="23" id="name" value="<?php echo attribute_escape( $template_name ); ?>" style="width: 267px;" />
									</li>
								</ul>
							</div>
							<div id="major-publishing-actions">
								<div id="publishing-action">
									<input name="save" type="submit" class="button-primary" id="save" tabindex="10" value="<?php _e('Save', POST_TEMPLATES_I18N_DOMAIN) ?>" />
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>					
				</div>
			
				<div id="categorydiv" class="postbox ">
					<div class="handlediv"><br/></div>
					<h3 class="hndle"><?php _e('Categories', POST_TEMPLATES_I18N_DOMAIN) ?></h3>
					<div class="inside">
						<ul id="category-tabs">
							<li class="ui-tabs-selected"><a href="#categories-all" tabindex="3"><?php _e('All Categories', POST_TEMPLATES_I18N_DOMAIN) ?></a></li>
						</ul>
						<div id="categories-all" class="ui-tabs-panel">
							<ul id="categorychecklist" class="list:category categorychecklist form-no-clear"><?php $post_templates_plugin->list_categories($template_categories); ?></ul>
						</div>
					</div>	
				</div>
				
				<div id="relateddiv" class="postbox">
					<div class="handlediv"><br/></div>
					<h3 class="hndle">
						<span><?php _e('Related actions', POST_TEMPLATES_I18N_DOMAIN) ?></span>
					</h3>
					
					<div class="inside">
						<ul>
							<li><a href="admin.php?page=post-template/includes/manage-templates-page.php"><?php _e('Manage Templates', POST_TEMPLATES_I18N_DOMAIN) ?></a></li>
							<li><a href="admin.php?page=post-template/includes/manage-templates-page.php&action=create-post&template_id=<?php echo $template_id; ?>"><?php _e('Create a post from this template', POST_TEMPLATES_I18N_DOMAIN) ?></a></li>
						</ul>
					</div>					
				</div>
			</div>
		</div>
		
		<div id="post-body" class="has-sidebar">
			<div id="post-body-content" class="has-sidebar-content">				
				<div id="titlediv">
					<div id="titlewrap"><input type="text" name="title" size="30" tabindex="1" value="<?php echo attribute_escape( $template_title ); ?>" id="title" /></div>
				</div>
			
				<div id="postdivrich" class="postarea">
					<?php the_editor($template_content); ?>
				</div>
				
				<?php 
					if (!(user_can_richedit() && $post_templates_plugin->options['allow_rich_editor'])) { 
						// Text appears white on white if no rich editor. Fix that with some jQuery.
				?>
				<script type="text/javascript">
					jQuery("document").ready(function(){
						jQuery("#content").css("color", "black");
					});
				</script>
				<?php } ?>
				
				<div id="normal-sortables" class="meta-box-sortables ui-sortable" style="position: relative;">
					<div id="tagsdiv" class="postbox ">
						<div class="handlediv"><br/></div>
						<h3 class="hndle"><?php _e('Tags', POST_TEMPLATES_I18N_DOMAIN); ?></h3>
						<div class="inside">
							<p id="jaxtag"><input type="text" name="tags_input" class="tags-input" id="tags-input" size="40" tabindex="3" value="<?php echo $template_tags; ?>" /></p>
							<div id="tagchecklist"></div>
						</div>
					</div>
	
					<div id="commentstatusdiv" class="postbox">
						<div class="handlediv"><br/></div>
						<h3 class="hndle"><?php _e('Discussion', POST_TEMPLATES_I18N_DOMAIN) ?></h3>
						<div class="inside">
							<p><label for="comment_status" class="selectit">
							<input name="comment_status" type="checkbox" id="comment_status" value="open" <?php checked($template_comment_status, 'open'); ?> /> <?php _e('Allow Comments') ?></label></p>
							<p><label for="ping_status" class="selectit">
							<input name="ping_status" type="checkbox" id="ping_status" value="open" <?php checked($template_ping_status, 'open'); ?> /> <?php _e('Allow Pings') ?></label></p>
						</div>
					</div>
		
					<div id="passworddiv" class="postbox">
						<div class="handlediv"><br/></div>
						<h3 class="hndle"><?php _e('Password Protect This Post', POST_TEMPLATES_I18N_DOMAIN) ?></h3>
						<div class="inside"><input name="password" type="text" size="13" id="password" value="<?php echo attribute_escape( $template_password ); ?>" /></div>
					</div>
					
					
					<div id="postexcerpt" class="postbox">
						<div class="handlediv"><br/></div>
						<h3 class="hndle"><?php _e('Optional Excerpt', POST_TEMPLATES_I18N_DOMAIN) ?></h3>
						<div class="inside"><textarea rows="1" cols="40" name="excerpt" tabindex="6" id="excerpt"><?php echo $template_excerpt ?></textarea></div>
					</div>
		
		
					<div id="trackbacksdiv" class="postbox">
						<div class="handlediv"><br/></div>
						<h3 class="hndle"><?php _e('Trackbacks', POST_TEMPLATES_I18N_DOMAIN) ?></h3>
						<div class="inside"><?php _e('Send trackbacks to:', POST_TEMPLATES_I18N_DOMAIN); ?> 
							<input type="text" name="to_ping" style="width: 415px" id="trackback" tabindex="7" value="<?php echo attribute_escape( str_replace("\n", ' ', $template_to_ping) );?>" /> (<?php _e('Separate multiple URLs with spaces'); ?>)
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</form>	
</div>
