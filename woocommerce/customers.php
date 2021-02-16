<?php
/**
 * WooCommerce Customers Class
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Formaloo_WC_Customers extends Formaloo_Cashback_Page {

	/**
	 * Get all customers
	 *
	 * @param array $fields
	 * @param array $filter
	 * @param int $page
	 * @return array
	 */
	public function get_customers( $fields = null, $filter = array(), $page = 1 ) {

		$filter['page'] = $page;

		$query = $this->query_customers( $filter );

		$customers = array();

		foreach ( $query->get_results() as $user_id ) {

			$customers[] = current( $this->get_customer( $user_id, $fields ) );
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
	public function get_customer( $id, $fields = null ) {
		global $wpdb;

		$customer = new WP_User( $id );

		// Get info about user's last order
		$last_order = $wpdb->get_row( "SELECT id, post_date_gmt
						FROM $wpdb->posts AS posts
						LEFT JOIN {$wpdb->postmeta} AS meta on posts.ID = meta.post_id
						WHERE meta.meta_key = '_customer_user'
						AND   meta.meta_value = {$customer->ID}
						AND   posts.post_type = 'shop_order'
						AND   posts.post_status IN ( '" . implode( "','", array_keys( wc_get_order_statuses() ) ) . "' )
						ORDER BY posts.ID DESC
					" );

		$customer_data = array(
			// 'created_at'       => $customer->user_registered,
			'email'            => $customer->user_email,
			'full_name'        => $customer->first_name . ' ' . $customer->last_name,
			'phone_number'	   => $customer->billing_phone,
			'customer_data' => array(
				'username'         => $customer->user_login,
				'role'             => $customer->roles[0],
				'last_order_id'    => is_object( $last_order ) ? $last_order->id : null,
				'last_order_date'  => is_object( $last_order ) ? $last_order->post_date_gmt : null,
				'orders_count'     => wc_get_customer_order_count( $customer->ID ),
				'total_spent'      => wc_format_decimal( wc_get_customer_total_spent( $customer->ID ), 2 ),
				'avatar_url'       => $this->get_avatar_url( $customer->customer_email ),
				'billing_address'  => array(
					'first_name' => $customer->billing_first_name,
					'last_name'  => $customer->billing_last_name,
					'company'    => $customer->billing_company,
					'address_1'  => $customer->billing_address_1,
					'address_2'  => $customer->billing_address_2,
					'city'       => $customer->billing_city,
					'state'      => $customer->billing_state,
					'postcode'   => $customer->billing_postcode,
					'country'    => $customer->billing_country,
					'email'      => $customer->billing_email,
					'phone'      => $customer->billing_phone,
				),
				'shipping_address' => array(
					'first_name' => $customer->shipping_first_name,
					'last_name'  => $customer->shipping_last_name,
					'company'    => $customer->shipping_company,
					'address_1'  => $customer->shipping_address_1,
					'address_2'  => $customer->shipping_address_2,
					'city'       => $customer->shipping_city,
					'state'      => $customer->shipping_state,
					'postcode'   => $customer->shipping_postcode,
					'country'    => $customer->shipping_country,
				),
			),
			
		);

		return array( 'customer' => apply_filters( 'woocommerce_api_customer_response', $customer_data, $customer, $fields) );
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

		// default users per page
		$users_per_page = get_option( 'posts_per_page' );

		// Set base query arguments
		$query_args = array(
			'fields'  => 'ID',
			'role'    => 'customer',
			'orderby' => 'registered',
			'number'  => $users_per_page,
		);

		// Custom Role
		if ( ! empty( $args['role'] ) ) {
			$query_args['role'] = $args['role'];
		}

		// Search
		if ( ! empty( $args['q'] ) ) {
			$query_args['search'] = $args['q'];
		}

		// Limit number of users returned
		if ( ! empty( $args['limit'] ) ) {
			if ( $args['limit'] == -1 ) {
				unset( $query_args['number'] );
			} else {
				$query_args['number'] = absint( $args['limit'] );
				$users_per_page       = absint( $args['limit'] );
			}
		} else {
			$args['limit'] = $query_args['number'];
		}

		// Page
		$page = ( isset( $args['page'] ) ) ? absint( $args['page'] ) : 1;

		// Offset
		if ( ! empty( $args['offset'] ) ) {
			$query_args['offset'] = absint( $args['offset'] );
		} else {
			$query_args['offset'] = $users_per_page * ( $page - 1 );
		}

		// Created date
		if ( ! empty( $args['created_at_min'] ) ) {
			$this->created_at_min = $args['created_at_min'];
		}

		if ( ! empty( $args['created_at_max'] ) ) {
			$this->created_at_max = $args['created_at_max'];
		}

		// Order (ASC or DESC, ASC by default)
		if ( ! empty( $args['order'] ) ) {
			$query_args['order'] = $args['order'];
		}

		// Orderby
		if ( ! empty( $args['orderby'] ) ) {
			$query_args['orderby'] = $args['orderby'];

			// Allow sorting by meta value
			if ( ! empty( $args['orderby_meta_key'] ) ) {
				$query_args['meta_key'] = $args['orderby_meta_key'];
			}
		}

		$query = new WP_User_Query( $query_args );

		// Helper members for pagination headers
		$query->total_pages = ( $args['limit'] == -1 ) ? 1 : ceil( $query->get_total() / $users_per_page );
		$query->page = $page;

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