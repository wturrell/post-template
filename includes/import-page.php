<?php
/*  Copyright 2006-2009 Vincent Prat
*/

//############################################################################
// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { 
	die('You are not allowed to call this page directly.'); 
}
//############################################################################

//############################################################################
// Stop unauthorised users 
if (!current_user_can('manage_options')) {
	wp_die(__("You are not allowed to use the importers of the Post Templates plugin.", POST_TEMPLATES_I18N_DOMAIN));
}
//############################################################################

require_once(POST_TEMPLATES_DIR . '/includes/1.x-importer-class.php');
?>

<div class="wrap">

<?php 	
global $post_templates_plugin;	
if (isset($_POST['post_templates_import_submit'])) {
	$importer = new PostTemplates1xImporter();
	$importer->import($_POST['copy_old'], $_POST['delete_old']);
?>
<div id="message" class="updated fade">
	<ul>
		<li><?php echo sprintf(__('Parsed %s posts.', POST_TEMPLATES_I18N_DOMAIN), $importer->parsed_posts); ?></li>
		<li><?php echo sprintf(__('Imported %s templates.', POST_TEMPLATES_I18N_DOMAIN), $importer->imported_templates); ?></li>
		<li><?php echo sprintf(__('Deleted %s templates.', POST_TEMPLATES_I18N_DOMAIN), $importer->deleted_templates); ?></li>
	</ul>
</div>
<?php
}
?>

<h2><?php _e("Post Templates Plugin", POST_TEMPLATES_I18N_DOMAIN); ?> <?php echo $post_templates_plugin->options['active_version']; ?> - <?php _e("Importers", POST_TEMPLATES_I18N_DOMAIN); ?></h2>

<ul class="subsubsub">
	<li><a href="options-general.php?page=post-template/includes/options-page.php" ><?php _e("Options", POST_TEMPLATES_I18N_DOMAIN); ?></a> | </li>
	<li><?php _e("Importers", POST_TEMPLATES_I18N_DOMAIN); ?> | </li>
	<li><a href="options-general.php?page=post-template/includes/help-page.php" ><?php _e("Help", POST_TEMPLATES_I18N_DOMAIN); ?></a> | </li>
	<li><a href="http://post-templates.vincentprat.info" target="_blank"><?php _e("Plugin's home page", POST_TEMPLATES_I18N_DOMAIN); ?></a></li>
</ul>
<br class="clear"/>

<div align="center">
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_xclick" />
		<input type="hidden" name="business" value="vpratfr@yahoo.fr" />
		<input type="hidden" name="item_name" value="Vincent Prat - WordPress Plugin" />
		<input type="hidden" name="no_shipping" value="1" />
		<input type="hidden" name="no_note" value="1" />
		<input type="hidden" name="currency_code" value="EUR" />
		<input type="hidden" name="tax" value="0" />
		<input type="hidden" name="lc" value="<?php _e('EN', POST_TEMPLATES_I18N_DOMAIN); ?>" />
		<input type="hidden" name="bn" value="PP-DonationsBF" />
		<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="PayPal" />
		<img alt="" border="0" src="https://www.paypal.com/fr_FR/i/scr/pixel.gif" width="1" height="1" />
	</form>
</div>

<br class="clear"/>

<form name="Import" action="options-general.php?page=post-template/includes/import-page.php" method="post">
	<input type="hidden" name="post_templates_import_submit" value="post_templates_import_submit" />

	<table class="form-table" width="100%" cellspacing="2" cellpadding="5">
		<tr>
			<th scope="row" valign="top">
				<label></label>
			</th>
			<td>				
				<input name="delete_old" type="checkbox" value="delete_old"> <?php echo __('Delete old templates from previous plugin version.', POST_TEMPLATES_I18N_DOMAIN); ?></input>
			</td>
		</tr>
		<tr>
			<th scope="row" valign="top">
				<label></label>
			</th>
			<td>
				<input name="copy_old" type="checkbox" checked="checked" value="copy_old"> <?php echo __('Copy old templates from previous plugin version.', POST_TEMPLATES_I18N_DOMAIN); ?></input>
			</td>
		</tr>
	</table>
	
	<p class="submit">
		<input type="submit" name="Submit" value="<?php echo __('Import', POST_TEMPLATES_I18N_DOMAIN) . ' &raquo;'; ?>" />
	</p>
</form>

</div>
	
