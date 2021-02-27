<?php
    /**
     * Syncs WooCommerce Items
     * @since 0.1.0
     */

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    
    class Formaloo_Woocommerce_Sync extends Formaloo_Main_Class {

        function sync_customers() {
            $wc_customers = new Formaloo_WC_Customers();
            $customers = $wc_customers->get_customers();
            $customers_json = json_encode($customers);
    
            $url = esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1.0/customers/batch/' );
    
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
            
            // $date = date('Y-m-d H:i:s');
            // file_put_contents(__DIR__.'/my_loggg1.txt', ' // ' . $result['status'] . ' // ' . $date . ' // ');

            if ($result['status'] == 201) {
                
            }
        }
    
        function sync_orders() {
            $wc_orders = new Formaloo_WC_Orders();
            $orders = $wc_orders->get_orders();
            $orders_json = json_encode($orders);
    
            $url = esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1.0/activities/batch/' );
    
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
            
            // $date = date('Y-m-d H:i:s');
            // file_put_contents(__DIR__.'/my_loggg2.txt', ' // ' . $result['status'] . ' // ' . $date . ' // ');

            if ($result['status'] == 201) {
                
            }
        }
    }
