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

        private $updated_customers = [];
        private $average_recency = 0;
        private $average_frequency = 0;
        private $average_monetary = 0;

        public function __construct() {
            // Fill the class variables here
        }

        // Retrieve all customer from Formaloo CDP
        function get_customers() {
            // TBC
        }

        // Normalize each item in a list to be between 1 to 5 range (RFM score range)
        function normalize_item($input, $average, $min_value, $max_value) {
            $output_value = 0;
            $average_value = 0;
            $min_range = 1;
            $max_range = 5;
            
            $average_value = $input/$average;
            
            $normalized = ($max_range-$min_range)*($average_value-$min_value)/($max_value-$min_value) + $min_range;
            $output_value = (int)round($normalized, 2);

            return $output_value;
        }

        // Chooses a proper tag title based on the user's RFM score ("x x x")
        function choose_tag($rfm_score) {

            [$recency, $frequency, $monetary] = explode(' ', $rfm_score);
            $output_tag_title = '';

            if ($recency == 5 && $frequency == 5 && $monetary == 5) {
                // The Best Customers
                $output_tag_title = 'Core';
            } elseif ($frequency == 5) {
                // Your Most Loyal Customers
                $output_tag_title = 'Loyal';
            } elseif($monetary == 5) {
                // Your Highest Paying Customers
                $output_tag_title = 'Whales';
            } elseif($frequency == 5 && ($monetary == 3 || $monetary == 2)) {
                // Faithful customers
                $output_tag_title = 'Promising';
            } elseif($recency == 5 && $frequency == 2) {
                // Your Newest Customers
                $output_tag_title = 'Rookies';
            } elseif($recency == 2 && $frequency == 2) {
                // Once Loyal, Now Gone
                $output_tag_title = 'Slipping';
            } else {
                // ??
                $output_tag_title = '';
            }
            
            return $output_tag_title;
        }

        // Iterate through all customers and calculates their RFM score
        function calculate_rfm_score() {

            $customers = [];

            foreach ($customers as $customer) {
                // $r = normalize_item(R, $average_recency, min, max);
                // $f = normalize_item(F, $average_frequency, min, max);
                // $m = normalize_item(M, $average_monetary, min, max);
                // $rfm = "{$r} {$f} ${m}";
                // choose_tag($rfm);
            }
        }

        // Creates customers batch list including their IDs and newly created tags for each of them and sends to the server
        function update_customers() {
            // TBC
        }

        // Calculates average for a list of values
        function calculate_average($input_list) {
            $input_list = array_filter($input_list);
            if(count($input_list)) {
                return $average = array_sum($input_list)/count($input_list);
            } else {
                return [];
            }
        }

        // Normalize items in a list to be between 1 to 5 range (RFM score range)
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

    }