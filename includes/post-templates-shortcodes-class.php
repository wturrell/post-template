<?php
/*  Copyright 2006-2009 Vincent Prat
*/

/**
 * Shortcodes for post templates
 * @author Vincent Prat 
 */     
class PostTemplatesShortcodes {
                                  
    /**
    * Constructor is responsible for registering the shortcodes
    */
    function __construct() {
        add_shortcode( 'post-template', array(&$this, 'insert_post_template' ) );         
    }
 
    /**
    * Insert a post template in the content of the post. Syntax: [post-template id="2" /]
    */
    function insert_post_template($atts, $content = '') {
        global $post_templates_dao;
        
        // Extract the ID of the template we are looking for
        //--
        extract(shortcode_atts(array(
            'id'         => 0        
        ), $atts ));
        
        // Output error message for no ID
        //--
        if ($id==0) {
            return "<p style='color: red;'>" . __("You have to supply a template ID in the [post-template] shortcode.", POST_TEMPLATES_I18N_DOMAIN) . "</p>";
        }
        
        // Fetch the template from DB
        //--
        $template = $post_templates_dao->get_template($id);
        if (!isset($template) || $template==null) {            
            return "<p style='color: red;'>" . __('Template not found in the database', POST_TEMPLATES_I18N_DOMAIN) . "</p>";   
        }
        
        // Return the content of the template (forget title, tags, categories, ...)
        //--
        return $template->content;
    }   
}
?>
