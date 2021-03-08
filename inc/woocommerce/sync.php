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

            $data = $this->getData();
            $last_sync_date_str = 'last_customers_sync_date';
            $last_batch_import_slug_str = 'last_customers_batch_import_slug';

            $api_token = $data['api_token'];
            $api_key = $data['api_key'];

            $date = date('Y-m-d H:i:s');

            $wc_customers = new Formaloo_WC_Customers();
            $customers = $wc_customers->get_customers();
            $customers_json = json_encode($customers);
    
            $url = esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1.0/customers/batch/' );
    
            $response = wp_remote_post( $url, array(
                'body'    => $customers_json,
                'headers' => array( 'x-api-key' => $api_key,
                                    'Authorization'=> 'JWT ' . $api_token,
                                    'Content-Type' => 'application/json; charset=utf-8'
                ),
                'data_format' => 'body',                   
            ));

            if (!is_wp_error($response)) {
                $result = json_decode($response['body'], true);
            
                // file_put_contents(__DIR__.'/my_loggg1.txt', ' // ' . $result['status'] . ' // ' . $date . ' // ');
    
                if ($result['status'] == 201) {
                    $data[$last_sync_date_str] = $date;
    
                    $data[$last_batch_import_slug_str] = $result['data']['customer_batch']['slug'];
        
                    update_option('formaloo_data', $data);
                }
            }


        }
    
        function sync_orders() {
            $data = $this->getData();
            $is_initial_sync_str = 'is_initial_sync';
            $last_sync_date_str = 'last_orders_sync_date';
            $last_batch_import_slug_str = 'last_orders_batch_import_slug';

            $api_token = $data['api_token'];
            $api_key = $data['api_key'];

            $date = date('Y-m-d H:i:s');

            $last_sync_date = isset($data[$last_sync_date_str]) ? $data[$last_sync_date_str] : $date;

            $wc_orders = new Formaloo_WC_Orders();
            $orders = $wc_orders->get_orders($data[$is_initial_sync_str], $last_sync_date);
            $orders_json = json_encode($orders);
    
            $url = esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1.0/activities/batch/' );
    
            $response = wp_remote_post( $url, array(
                'body'    => $orders_json,
                'headers' => array( 'x-api-key' => $api_key,
                                    'Authorization'=> 'JWT ' . $api_token,
                                    'Content-Type' => 'application/json; charset=utf-8'
                ),
                'data_format' => 'body',                   
            ));

            if (!is_wp_error($response)) {
                $result = json_decode($response['body'], true);
            
                // file_put_contents(__DIR__.'/my_loggg2.txt', ' // ' . $result['status'] . ' // ' . $date . ' // ');
    
                if ($result['status'] == 201) {
                    if (!isset($data[$is_initial_sync_str])) {
                        $data[$is_initial_sync_str] = false;
                    }
        
                    $data[$last_sync_date_str] = $date;
        
                    $data[$last_batch_import_slug_str] = $result['data']['activity_batch']['slug'];
        
                    update_option('formaloo_data', $data);
                }
            }

        }

    }
