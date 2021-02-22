<?php
/**
 * WooCommerce Orders Class
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Formaloo_WC_Orders extends Formaloo_Cashback_Page {

	/**
	 * Get all orders
	*
	 * @return array
	 */
	public function get_orders() {

		$orders = wc_get_orders( array('numberposts' => -1) );

		$orders_arr = array();

		foreach( $orders as $order ){
			
			$user_id = $order->get_user_id();
			$customer = new WC_Customer( $user_id );
			$user_email   = $customer->get_email();

			$orders_arr[] = array(
				'action' => 'order',
				'customer' => array(
					'email' => $user_email
				),
				'activity_data' => array(
					'order_status' => $order->get_status(), 
					'order_total' => $order->get_total(),
					'order_total_discount' => $order->get_total_discount(),
					'order_shipping_method' => $order->get_shipping_method(),
					'order_currency' => $order->get_currency()
				),
				'relations' => array(
					array(
						'relation_name' => 'order',
						'instance' => array(
							'identifier_value' => $order->get_id(),
							'entity"' => array(
								'model_name' => 'order'
							)
						)
					),
				),
			);                                
		}

		return json_encode($orders_arr);
	}

}