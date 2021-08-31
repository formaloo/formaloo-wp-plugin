<?php
/**
 * WooCommerce Customers Class
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Formaloo_WC_Customers extends Formaloo_Cashback_Page {

	private static $instance;

	private function __construct() {
	}

	public static function getInstance() {
		if (!isset(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Get all customers
	 *
	 * @param array $fields
	 * @param array $filter
	 * @param int $page
	 * @return array
	 */
	public function get_customers( $is_initial_sync = null, $last_sync_date = null, $fields = null, $filter = array(), $page = 1 ) {

		$filter['page'] = $page;

		$query = $this->query_customers( $filter );

		$customers = array();

		$date_format = 'Y-m-d H:i:s';
		$date = date($date_format);

		$last_date = isset($last_sync_date) ? $last_sync_date : $date;	
		
		foreach ( $query->get_results() as $user_id ) {

			$customer = current( $this->get_customer( $user_id, $fields, $is_initial_sync, $last_date ) );

			if (!is_null($customer)) {
				$customers[] = $customer;
			}
		}

		return array( 'customers_data' => $customers );
	}

	/**
	 * Get the customer for the given ID
	 *
	 * @since 2.1
	 * @param int $id the customer ID
	 * @param array $fields
	 * @return array
	 */
	public function get_customer( $id, $fields = null, $is_initial_sync = null, $last_sync_date = null ) {
		$customer = new WP_User( $id );

		$operator = isset($is_initial_sync) ? '<' : '>';

		$condition = false;

		$customer_registered_date = $customer->user_registered;

		switch($operator) {
			case '>':
				if($last_sync_date > $customer_registered_date) {
					$condition = true;
				}
				break;
			case '<':
				if($last_sync_date < $customer_registered_date) {
					$condition = true;
				}
				break;
		}

		if ($condition) {

			if (!empty($customer->user_email) or !empty($customer->billing_phone)) {
				
				$customer_data = array(
					'full_name'        => $customer->first_name . ' ' . $customer->last_name,
					'tags' => array(
						array(
							'title' => $customer->roles[0]
						)
					),
					'customer_data' => array(
						'Username'         => $customer->user_login,
						'Orders Count'     => wc_get_customer_order_count( $customer->ID ),
						'Total Spent'      => wc_format_decimal( wc_get_customer_total_spent( $customer->ID ), 2 ),
						'Avatar URL'       => $this->get_avatar_url( $customer->customer_email ),
						'User Registered'  => $customer->user_registered,
						'Shipping Address' => array(
							'First Name' => $customer->shipping_first_name,
							'Last Name'  => $customer->shipping_last_name,
							'Company'    => $customer->shipping_company,
							'Address 1'  => $customer->shipping_address_1,
							'Address 2'  => $customer->shipping_address_2,
							'City'       => $customer->shipping_city,
							'State'      => $customer->shipping_state,
							'Postcode'   => $customer->shipping_postcode,
							'Country'    => $customer->shipping_country,
						),
					),

				);

				if (!empty($customer->user_email)) {
					$customer_data['email'] = $customer->user_email;
				}

				if (!empty($customer->billing_phone)) {
					$customer_data['phone_number'] = $customer->billing_phone;
				}

				return array( 'customer' => apply_filters( 'woocommerce_api_customer_response', $customer_data, $customer, $fields) );
				
			}
		}

	}

	/**
	 * Helper method to get customer user objects
	 *
	 * Note that WP_User_Query does not have built-in pagination so limit & offset are used to provide limited
	 * pagination support
	 *
	 * The filter for role can only be a single role in a string.
	 *
	 * @param array $args request arguments for filtering query
	 * @return WP_User_Query
	 */
	private function query_customers( $args = array() ) {

		$query = new WP_User_Query(
			array(
				'fields'  => 'ID',
				'role'    => 'customer',
				'orderby' => 'registered',
			)
		);

		return $query;
	}

	/**
	 * Wrapper for @see get_avatar() which doesn't simply return
	 * the URL so we need to pluck it from the HTML img tag
	 *
	 * Kudos to https://github.com/WP-API/WP-API for offering a better solution
	 *
	 * @param string $email the customer's email
	 * @return string the URL to the customer's avatar
	 */
	private function get_avatar_url( $email ) {
		$avatar_html = get_avatar( $email );

		// Get the URL of the avatar from the provided HTML
		preg_match( '/src=["|\'](.+)[\&|"|\']/U', $avatar_html, $matches );

		if ( isset( $matches[1] ) && ! empty( $matches[1] ) ) {
			return esc_url_raw( $matches[1] );
		}

		return null;
    }
    
}