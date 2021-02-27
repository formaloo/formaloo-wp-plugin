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

		$data = $this->getData();
		$option_name = 'last_orders_sync_date';

		$orders = wc_get_orders( array('numberposts' => -1) );

		$orders_arr = array();

		$date = date('Y-m-d H:i:s');

		$last_date = isset($data[$option_name]) ? $data[$option_name] : $date;

		foreach( $orders as $order ){
			
			$order_date = $order->get_date_created()->format ('Y-m-d H:i:s');

			if ($last_date < $order_date) {
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
			                        
		}

		$data[$option_name] = $date;
		update_option('formaloo_data', $data);

		return array( 'activities_data' => $orders_arr );
	}

}