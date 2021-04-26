<?php
/**
 * Runs only when the plugin is activated.
 * @since 0.1.0
 */

/**
 * Adds a custom cron schedule for every 5 minutes.
 *
 * @param array $schedules An array of non-default cron schedules.
 * @return array Filtered array of non-default cron schedules.
 */
function formaloo_custom_cron_schedule( $schedules ) {
    $schedules[ 'every_5_minutes' ] = array( 'interval' => 5 * MINUTE_IN_SECONDS, 'display' => __( 'Every 5 minutes', 'devhub' ) );
    return $schedules;
}

add_filter( 'cron_schedules', 'formaloo_custom_cron_schedule' );

add_action('formaloo_sync_woocommerce', array('Formaloo_Activation_Class', 'syncHourly'));
add_action('formaloo_refresh_auth_token', array('Formaloo_Activation_Class', 'refreshAuthToken'));

class Formaloo_Activation_Class extends Formaloo_Main_Class {

    static function formalooActivationHook() {
        /* Create transient data */
        set_transient( 'formaloo_admin_notice_activation', true, 0 );
        if ( ! wp_next_scheduled( 'formaloo_sync_woocommerce' ) ) {
            wp_schedule_event( time(), 'hourly', 'formaloo_sync_woocommerce' );
        }
        if ( ! wp_next_scheduled( 'formaloo_refresh_auth_token' ) ) {
            wp_schedule_event( time(), 'every_5_minutes', 'formaloo_refresh_auth_token' );
        }
    }
    
    /**
     * Admin Notice on Activation.
     * @since 0.1.0
     */
    static function formalooAdminNoticeActivationNotice() {
    
        /* Check transient, if available display notice */
        if (get_transient('formaloo_admin_notice_activation')) {
            ?>
            <div class="updated notice is-dismissible">
            <p><?php echo __('Thank you for using the Formaloo plugin!', 'formaloo-form-builder') ?> <a href="<?php echo admin_url( "admin.php?page=formaloo-settings-page" ) ?>"><strong><?php echo __('Get Started by visiting the Settings Page', 'formaloo-form-builder') ?></strong></a>.</p>
            </div>
            <?php
            /* Delete transient, only display this notice once. */
            delete_transient('formaloo_admin_notice_activation');
        }
    }

    static function syncHourly() {
        $data = get_option('formaloo_data', array());
        if (isset($data['api_token'])) {
            $wc_sync = new Formaloo_Woocommerce_Sync();
            $wc_sync->sync_customers();
            $wc_sync->sync_orders();
            $rfm_calculator = new Formaloo_RFM_Calculator();
            $rfm_calculator->update_customers();
        }
    }

    static function refreshAuthToken() {
        new TokenAuthenticator();
    }
    
 }