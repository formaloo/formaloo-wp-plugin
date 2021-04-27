<?php
    /**
     * Retrieve all customers from Formaloo CDP, calculates the RFM score for each customer 
     * based on their activity, and updates with server occasionally.
     * @since 2.1.0.0
     */

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }
    
    class Formaloo_RFM_Calculator extends Formaloo_Main_Class {

        private $tags = [
            1  => 'Champion',
            2  => 'Loyal Customer',
            3  => 'Potential Loyalist',
            4  => 'New Customer',
            5  => 'Promising',
            6  => 'Need Attention',
            7  => 'At Risk (About to leave)',
            8  => 'Can\'t lose them',
            9  => 'Hibernate',
            10 => 'Lost',
            11 => 'SME'
        ];

        // Retrieve all customer from Formaloo CDP with their gamification data
        function get_customers() {

            $customers = [];

            $data = $this->getData();
            $api_token = $data['api_token'];
            $api_key = $data['api_key'];

            $url = esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1.0/customers/' );

            $page = 1;
            $page_count = 1;

            while ($page_count > 0) {

                $query_args = [
                    'gamification_data' => 1,
                    'page' => $page,
                ];

                $query = http_build_query($query_args, NULL, '&', PHP_QUERY_RFC3986);

                $final_url = $url . '?' . $query;

                $response = wp_remote_get( $final_url ,
                    array( 'timeout' => 120,
                            'headers' => array( 'x-api-key' => $api_key,
                                                'Authorization'=> 'JWT ' . $api_token ) 
                ));

                if (!is_wp_error($response)) {
                    $result = json_decode($response['body'], true);

                    if ($page == 1) {
                        $page_count = $result['data']['page_count'];
                    }

                    $customers = array_merge(array_values($customers), array_values($result['data']['customers'])); 

                    $page++;
                    $page_count--;
                }
            
            }

            return $customers;

        }

        // Normalize each item in a list to be between 1 to 5 range (RFM score range)
        function normalize_item($input, $min_value, $max_value) {
            $output_value = 0;
            $average_value = 0;
            $min_range = 1;
            $max_range = 5;
            
            // $average_value = $input/$average;
            
            $normalized = (($max_range-$min_range)*(($input-$min_value)/($max_value-$min_value))) + $min_range;
            $output_value = (int)round($normalized, 2);

            return $output_value;
        }

        // Iterate through all customers and calculates their RFM score
        function calculate_rfm_score($customer) {
            $gamification_data = $customer['gamification_data'];
            $r = $this->normalize_item(round($gamification_data['R'], 2), round($gamification_data['MIN_R'], 2), round($gamification_data['MAX_R'], 2));
            $f = $this->normalize_item(round($gamification_data['F'], 2), round($gamification_data['MIN_F'], 2), round($gamification_data['MAX_F'], 2));
            $m = $this->normalize_item(round($gamification_data['TM'], 2), round($gamification_data['MIN_TM'], 2), round($gamification_data['MAX_TM'], 2));
            $rfm = "{$r} {$f} ${m}";
            return $rfm;       
        }

        // Chooses a proper tag title based on the user's RFM score ("x x x")
        function choose_tag($customer) {

            $output_tag_title = '';
            $rfm_score = $this->calculate_rfm_score($customer);
            [$recency, $frequency, $monetary] = array_map('intval', explode(' ', $rfm_score));
            $gamification_data = $customer['gamification_data'];
            $months_registered = $this->get_interval_in_month($customer['created_at'], date('c'));
            $at_risk = $this->is_customer_at_risk($customer);

            if ($recency == 5 && $frequency == 5 && $monetary == 5) {
                $output_tag_title = $this->tags[1];
            } elseif ($frequency == 5) {
                $output_tag_title = $this->tags[2];
            } elseif ($months_registered <= 3 && $gamification_data['AMV'] > $gamification_data['ATM']) {
                $output_tag_title = $this->tags[3];
            } elseif ($recency == 5 && $frequency == 2) {
                $output_tag_title = $this->tags[4];
            } elseif ($frequency == 5 && ($monetary == 3 || $monetary == 2)) {
                $output_tag_title = $this->tags[5];
            } elseif ($customer['level'] < $gamification_data['PREVIOUS_LEVEL']) {
                $output_tag_title = $this->tags[6];
            } elseif ($at_risk) {
                $output_tag_title = $this->tags[7];
            } elseif ($recency == 2 && $frequency == 2) {
                $output_tag_title = $this->tags[8];
            } elseif ($recency < 3 && $frequency < 3 && $monetary < 3) {
                $output_tag_title = $this->tags[9];
            } elseif ($recency == 1 && $frequency == 1 && $monetary == 1) {
                $output_tag_title = $this->tags[10];
            } else {
                $sum = $recency + $frequency + $monetary;
                if ($sum >= 9) {
                    $output_tag_title = $this->tags[5];
                } else {
                    $output_tag_title = $this->tags[6];
                }
            }
            
            return $output_tag_title;
        }

        function is_customer_at_risk($customer) {
            $at_risk = False;

            foreach ($customer['tags'] as $tag) {
                if ($tag['title'] == 'Need Attention') {
                    $months_created = $this->get_interval_in_month($tag['created_at'], date('c'));
                    if ($months_created >= 3) {
                        $at_risk = True;
                    }
                }
            }

            return $at_risk;
        }

        // Creates customers batch list including their IDs and
        // newly created tags for each of themand sends to the server
        function create_customers_batch() {

            $updated_customers = [];
            $customers = $this->get_customers();

            foreach ($customers as $customer) {

                $updated_tags = [];
      
                foreach($customer['tags'] as $tag) {
                   if (!in_array($tag['title'], $this->tags, True)) {
                       $updated_tags[] = [
                            'title' => $tag['title'],
                       ];
                   }
                }

                $updated_tags[] = [
                    'title' => $this->choose_tag($customer),
                ];

                $updated_customers[] = [
                    'email' => $customer['email'],
                    'tags'  => $updated_tags,
                ];
            }

            $updated_customers_json = json_encode(array( 'customers_data' => $updated_customers ));

            return $updated_customers_json;
        }

        function update_customers() {

            $data = $this->getData();
            $api_token = $data['api_token'];
            $api_key = $data['api_key'];

            $url = esc_url( FORMALOO_PROTOCOL . '://api.' . FORMALOO_ENDPOINT . '/v1.0/customers/batch/' );

            $customers_batch = $this->create_customers_batch();
                
            $response = wp_remote_post( $url, array(
                'body'    => $customers_batch,
                'headers' => array( 'x-api-key' => $api_key,
                                    'Authorization'=> 'JWT ' . $api_token,
                                    'Content-Type' => 'application/json; charset=utf-8'
                ),
                'data_format' => 'body',                   
            ));

            if (!is_wp_error($response) && ($response['response']['code'] === 200 || $response['response']['code'] === 201)) {
                // TBC
            }
        }

        function get_interval_in_month($from, $to) {
            $month_in_year = 12;
            $date_from = getdate(strtotime($from));
            $date_to = getdate(strtotime($to));
            return ($date_to['year'] - $date_from['year']) * $month_in_year -
                ($month_in_year - $date_to['mon']) +
                ($month_in_year - $date_from['mon']);
        }

        // Calculates average for a list of values
        /*
        function calculate_average($input_list) {
            $input_list = array_filter($input_list);
            if(count($input_list)) {
                return $average = array_sum($input_list)/count($input_list);
            } else {
                return [];
            }
        }
        */

        // Normalize items in a list to be between 1 to 5 range (RFM score range)
        /*
        function normalize_items($input_list) {
            $output_list = [];
            $min_range = 1;
            $max_range = 5;
            // Should use the stored averages came from the server
            $average = calculate_average($input_list);
            
            $average_list = [];
            foreach ($input_list as $i) {
                $average_list[] = $i/$average;
            }
            
            foreach ($average_list as $i) {
                $normalized = ($max_range-$min_range)*($i-min($average_list))/(max($average_list)-min($average_list)) + $min_range;
                $output_list[] = (int)round($normalized, 2);
            }

            return $output_list;
        }
        */

    }