<?php
class Formaloo_MCE_Button extends Formaloo_Main_Class {
    // hooks your functions into the correct filters
    function formaloo_add_mce_button() {
        // check user permissions
        if ( !current_user_can( 'edit_posts' ) &&  !current_user_can( 'edit_pages' ) ) {
                return;
        }
        // check if WYSIWYG is enabled
        if ( 'true' == get_user_option( 'rich_editing' ) ) {
            add_filter( 'mce_external_plugins', array($this,'formaloo_add_tinymce_plugin') );
            add_filter( 'mce_buttons', array($this,'formaloo_register_mce_button') );
        }
    }

    // register new button in the editor
    function formaloo_register_mce_button( $buttons ) {
        array_push( $buttons, 'formaloo_mce_button' );
        return $buttons;
    }

    // declare a script for the new button
    // the script will insert the shortcode on the click event
    function formaloo_add_tinymce_plugin( $plugin_array ) {
        $plugin_array['formaloo_mce_button'] = FORMALOO_URL . 'assets/js/formaloo-mce-button.js';
        return $plugin_array;
    }
}