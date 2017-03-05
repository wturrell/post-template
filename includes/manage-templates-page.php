<?php	
/*  Copyright 2006-2009 Vincent Prat
*/

if (!current_user_can(POST_TEMPLATES_VIEW_TEMPLATES_CAP)) {
	wp_die(__('You are not allowed to view templates', POST_TEMPLATES_I18N_DOMAIN));
}

global $post_templates_plugin, $post_templates_dao;

// Get the filter type from request
if (!isset($_GET['filter_type']) || $_GET['filter_type']=='') {
	$filter_type = 'all';
} else {
	$filter_type = $_GET['filter_type'];
}

?>

<div class="wrap">

<?php
// If asked to delete something, do it
if (isset($_GET['deleteit']) && isset($_GET['delete'])) {
	$del_count = 0;
	foreach( (array) $_GET['delete'] as $template_id_del ) {
		$post_templates_dao->delete_template($template_id_del);
		$del_count++;
	}
?>
<div id="message" class="updated fade">
	<p><?php echo sprintf(__('Deleted %s templates.', POST_TEMPLATES_I18N_DOMAIN), $del_count); ?></p>
</div>
<?php
}

// If asked to create a post from a template, do it
if (isset($_GET['action']) && $_GET['action']=='create-post' && isset($_GET['template_id'])) {
	$template = $post_templates_dao->get_template($_GET['template_id']);
	$new_post_id = $post_templates_plugin->create_post_from_template($template);
	if ($template->type=='post') {
		echo '<meta content="0; URL=' . get_option('siteurl') . '/wp-admin/post.php?action=edit&post=' . $new_post_id . '" http-equiv="Refresh" />';
	} else {
		echo '<meta content="0; URL=' . get_option('siteurl') . '/wp-admin/page.php?action=edit&post=' . $new_post_id . '" http-equiv="Refresh" />';
	}
	wp_die(__('Redirecting to the post edition page, please wait...', POST_TEMPLATES_I18N_DOMAIN));
}

// if asked to create a template from a post, do it
if (isset($_GET['action']) && $_GET['action']=='create-template' && isset($_GET['post'])) {
	$new_template_id = $post_templates_plugin->create_template_from_post($_GET['post']);
	$template = $post_templates_dao->get_template($new_template_id);
?>
<div id="message" class="updated fade">
	<p>
	<?php 
		echo sprintf(__('Created the template #%s from post #%s.', POST_TEMPLATES_I18N_DOMAIN), $new_template_id, $_GET['post']);
		echo "&nbsp;&nbsp;";
		if ($template->type=='page') { ?>
			<a href="edit.php?page=post-template/includes/edit-page-template-page.php&template_id=<?php echo $new_template_id; ?>"><?php _e('Edit this template', POST_TEMPLATES_I18N_DOMAIN); ?></a>	
	<?php 
		} else { 
	?>
			<a href="edit.php?page=post-template/includes/edit-post-template-page.php&template_id=<?php echo $new_template_id; ?>"><?php _e('Edit this template', POST_TEMPLATES_I18N_DOMAIN); ?></a>
	<?php 
		} 
	?>
	</p>
</div>
<?php
}
?>


<h2><?php echo __("Manage Templates", POST_TEMPLATES_I18N_DOMAIN); ?></h2>

<ul class="subsubsub">
	<li>
		<?php if ($filter_type=='all') { ?>
			<?php _e('All', POST_TEMPLATES_I18N_DOMAIN) ?> |
		<?php } else { ?>
			<a href="<?php echo get_option('siteurl'); ?>/wp-admin/edit.php?page=post-template/includes/manage-templates-page.php&filter_type=all" title="<?php _e('Show all templates', POST_TEMPLATES_I18N_DOMAIN) ?>">
				<?php _e('All', POST_TEMPLATES_I18N_DOMAIN) ?>
			</a> |
		<?php } ?>
	</li>
	<li>
		<?php if ($filter_type=='post') { ?>
			<?php _e('Post Templates', POST_TEMPLATES_I18N_DOMAIN) ?> |
		<?php } else { ?>
			<a href="<?php echo get_option('siteurl'); ?>/wp-admin/edit.php?page=post-template/includes/manage-templates-page.php&filter_type=post" title="<?php _e('Show only post templates', POST_TEMPLATES_I18N_DOMAIN) ?>">
				<?php _e('Post Templates', POST_TEMPLATES_I18N_DOMAIN) ?>
			</a> |
		<?php } ?>
	</li>
	<li>
		<?php if ($filter_type=='page') { ?>
			<?php _e('Page Templates', POST_TEMPLATES_I18N_DOMAIN) ?>
		<?php } else { ?>
			<a href="<?php echo get_option('siteurl'); ?>/wp-admin/edit.php?page=post-template/includes/manage-templates-page.php&filter_type=page" title="<?php _e('Show only page templates', POST_TEMPLATES_I18N_DOMAIN) ?>">
				<?php _e('Page Templates', POST_TEMPLATES_I18N_DOMAIN) ?>
			</a>
		<?php } ?>
	</li>
</ul>

<br class="clear" />

<p><strong><?php _e('Since version 4.0.0, the plugin has been released under a commercial license. New features such as addition of custom fields to the templates have been added. Furthermore, this version is discontinued, which means that no further bug fixes, new features and compatibility fixes for new WordPress versions will be implemented. If you want to buy the latest version of Post Template, please visit the plugin web page.', POST_TEMPLATES_I18N_DOMAIN); ?></strong></p>

<?php 
	if (current_user_can(POST_TEMPLATES_CREATE_TEMPLATES_CAP)) {
?>
<br class="clear" />
<span class="button-secondary"><a href="<?php echo get_option('siteurl'); ?>/wp-admin/edit.php?page=post-template/includes/edit-post-template-page.php" style="text-decoration: none;"><?php _e("Create Post Template", POST_TEMPLATES_I18N_DOMAIN); ?> &raquo;</a></span> 
<span class="button-secondary"><a href="<?php echo get_option('siteurl'); ?>/wp-admin/edit.php?page=post-template/includes/edit-page-template-page.php" style="text-decoration: none;"><?php _e("Create Page Template", POST_TEMPLATES_I18N_DOMAIN); ?> &raquo;</a></span>
<br class="clear" />
<?php
	}
?>
<br class="clear" />

<form id="templates-filter" action="" method="get">
	<input type="hidden" value="post-template/includes/manage-templates-page.php" name="page" />

<div class="tablenav">
	<?php if (current_user_can(POST_TEMPLATES_DELETE_TEMPLATES_CAP)) { ?>
		<input type="submit" value="<?php _e('Delete', POST_TEMPLATES_I18N_DOMAIN); ?>" name="deleteit" class="button-secondary delete" />
	<?php } ?>
<br class="clear" />
</div>

<br class="clear" />

<table class="widefat">
	<thead>
	<tr>	
		<th scope="col" class="check-column"><input type="checkbox" onclick="checkAll(document.getElementById('templates-filter'));" /></th>
		<th scope="col"><?php _e('ID', POST_TEMPLATES_I18N_DOMAIN); ?></th>
		<th scope="col" style="width:35%;color:#EAF3FA;"><?php _e('Name', POST_TEMPLATES_I18N_DOMAIN); ?></th>
		<th scope="col"><?php _e('Categories', POST_TEMPLATES_I18N_DOMAIN); ?></th>
		<th scope="col"><?php _e('Tags', POST_TEMPLATES_I18N_DOMAIN); ?></th>
	<?php if (current_user_can(POST_TEMPLATES_USE_TEMPLATES_CAP)) { ?>
		<th scope="col"></th>
	<?php } ?>   
	</tr>
	</thead>
	
	<tbody>

<?php
	$templates = $post_templates_dao->get_templates($filter_type, 'name asc');
	if (count($templates)==0) {	
?>
	<tr>
		<td scope="row" colspan="2" style="text-align: center"><strong><?php _e('No templates found.', POST_TEMPLATES_I18N_DOMAIN) ?></strong></td>
	</tr>
<?php
	} else {
		foreach ($templates as $template) { 
?>
	<tr>
		<th scope="row" class="check-column"><input type="checkbox" name="delete[]" value="<?php echo $template->template_id; ?>" /></th>
		<td scope="row" style="text-align: left"><?php echo $template->template_id; ?></td>
		<td scope="row" style="text-align: left">
			<?php 
				if ($template->type=='post') {
					$link = "edit.php?page=post-template/includes/edit-post-template-page.php&template_id=" . $template->template_id;
				} else {
					$link = "edit.php?page=post-template/includes/edit-page-template-page.php&template_id=" . $template->template_id;					
				}
				$text = apply_filters('the_title', $template->name);
				$title = __('Edit this template', POST_TEMPLATES_I18N_DOMAIN);
				
				if (current_user_can(POST_TEMPLATES_EDIT_TEMPLATES_CAP)) {
					echo "<a href='$link' class='edit' title='$title'>$text</a>";
				} else {
					echo $text;
				}
			?>
		</td>
		<td scope="row" style="text-align: left">
			<?php 
				if ($template->categories!='') {
					$cats = explode(',', $template->categories);
					$i = 0;
					foreach ($cats as $cat_id) {
						$i++;
						$cat = get_category($cat_id);
						echo '<a href="' . get_option('siteurl') . '/wp-admin/categories.php?action=edit&cat_ID=' . $cat_id . '">';
						echo $cat->cat_name;
						echo '</a>';
						if ($i!=count($cats))
							echo ", ";
					}
				} else {
					_e('No Category', POST_TEMPLATES_I18N_DOMAIN);
				}
			?>
		</td>
		<td scope="row" style="text-align: left">
			<?php 
				if ($template->tags!='') {
					$tags = explode(',', $template->tags);
					$i = 0;
					foreach ($tags as $tag) {
						$i++;
						echo '<a href="' . get_option('siteurl') . '/wp-admin/edit-tags.php">';
						echo $tag;
						echo '</a>';
						if ($i!=count($tags))
							echo ", ";
					}
				} else {
					_e('No Tags', POST_TEMPLATES_I18N_DOMAIN);
				}
			?>
		</td>
		
		<?php if (current_user_can(POST_TEMPLATES_USE_TEMPLATES_CAP)) { ?>
		<td style="width: 200px;">
			<?php 
				if ($template->type=='post') {
					$link = get_option('siteurl') . "/wp-admin/edit.php?page=post-template/includes/manage-templates-page.php&action=create-post&template_id=" . $template->template_id;
					$text = __('New post from template', POST_TEMPLATES_I18N_DOMAIN);
					$title = __('Create a new post from this template', POST_TEMPLATES_I18N_DOMAIN);
				} else {
					$link = get_option('siteurl') . "/wp-admin/edit.php?page=post-template/includes/manage-templates-page.php&action=create-post&template_id=" . $template->template_id;
					$text = __('New page from template', POST_TEMPLATES_I18N_DOMAIN);
					$title = __('Create a new page from this template', POST_TEMPLATES_I18N_DOMAIN);
				}
				echo "<div class='button-secondary'><a href='$link' class='edit' title='$title' style='width: 190px;'>$text &raquo;</a></div>";
			?>
		</td>
		<?php } ?>
	</tr>
<?php 	
		} 
	} 
?>
	</tbody>
</table>

<div class="tablenav">
<br class="clear" />
</div>

</form>

</div>
	