<?php

/* Register activation hook. */
register_activation_hook( __FILE__, 'formaloo_admin_notice_activation_hook' );

/**
 * Runs only when the plugin is activated.
 * @since 0.1.0
 */
function formaloo_admin_notice_activation_hook() {

    /* Create transient data */
    set_transient( 'formaloo-admin-notice-activation', true, 5 );
}

/* Add admin notice */
add_action( 'admin_notices', 'formaloo_admin_notice_activation_notice' );

/**
 * Admin Notice on Activation.
 * @since 0.1.0
 */
function formaloo_admin_notice_activation_notice(){

    /* Check transient, if available display notice */
    if( get_transient( 'formaloo-admin-notice-activation' ) ){
        ?>
        <div class="updated notice is-dismissible">
            <p>Thank you for using this plugin! <strong>Please Activate your plugin</strong>.</p>
        </div>
        <?php
        /* Delete transient, only display this notice once. */
        delete_transient( 'formaloo-admin-notice-activation' );
    }
}