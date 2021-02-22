<?php
    /**
     * Runs only when the plugin is activated.
     * @since 0.1.0
     */

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    function formaloo_admin_notice_activation_hook() {
        /* Create transient data */
        set_transient( 'formaloo-admin-notice-activation', true, 5 );
    }    
    
    class Formaloo_Activation_Class extends Formaloo_Main_Class {
        
        function start_activation() {
            wp_schedule_event( time(), 'twicedaily', 'sync_customers_and_orders' );
         }

        /**
         * Admin Notice on Activation.
         * @since 0.1.0
         */
        function formaloo_admin_notice_activation_notice(){
        
            /* Check transient, if available display notice */
            if( get_transient( 'formaloo-admin-notice-activation' ) ){
                ?>
                <div class="updated notice is-dismissible">
                <p><?php echo __('Thank you for using the Formaloo plugin!', 'formaloo-form-builder') ?> <a href="<?php echo admin_url( "admin.php?page=formaloo-settings-page" ) ?>"><strong><?php echo __('Get Started by visiting the Settings Page', 'formaloo-form-builder') ?></strong></a>.</p>
                </div>
                <?php
                /* Delete transient, only display this notice once. */
                delete_transient( 'formaloo-admin-notice-activation' );
            }
        }

        function do_this_twicedaily() {
            $this->sync_customers();
            $this->sync_orders();
        }

        function sync_customers() {
            $wc_customers = new Formaloo_WC_Customers();
            $customers = $wc_customers->get_customers();
            $customers_json = json_encode($customers);
    
            $url = esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1.0/customers/batch/?active_business=bcmqr9Qb' );
    
            $data = $this->getData();
            $api_token = $data['api_token'];
            $api_key = $data['api_key'];
    
            $response = wp_remote_post( $url, array(
                'body'    => $customers_json,
                'headers' => array( 'x-api-key' => $api_key,
                                    'Authorization'=> 'JWT ' . $api_token,
                                    'Content-Type' => 'application/json; charset=utf-8'
                ),
                'data_format' => 'body',                   
            ));
            $result = json_decode($response['body'], true);
            
            if ($result['status'] == 201) {
                
            }
        }
    
        function sync_orders() {
            $wc_orders = new Formaloo_WC_Orders();
            $orders_json = $wc_orders->get_orders();
    
            $url = esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1.0/activities?active_business=bcmqr9Qb' );
    
            $data = $this->getData();
            $api_token = $data['api_token'];
            $api_key = $data['api_key'];
    
            $response = wp_remote_post( $url, array(
                'body'    => $orders_json,
                'headers' => array( 'x-api-key' => $api_key,
                                    'Authorization'=> 'JWT ' . $api_token,
                                    'Content-Type' => 'application/json; charset=utf-8'
                ),
                'data_format' => 'body',                   
            ));
            $result = json_decode($response['body'], true);
            
            if ($result['status'] == 201) {
                
            }
        }
    }