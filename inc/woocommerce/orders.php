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
	public function get_orders($is_initial_sync = null, $last_sync_date = null) {

		$orders = wc_get_orders( array('numberposts' => -1) );

		$orders_arr = array();

		$date_format = 'Y-m-d H:i:s';
		$date = date($date_format);

		$last_date = isset($last_sync_date) ? $last_sync_date : $date;	

		foreach( $orders as $order ){
			
			$order_date = $order->get_date_created()->format($date_format);
			// if is_initial_sync is set then it's not the first sync
			$operator = isset($is_initial_sync) ? '<' : '>';

			$condition = false;

			switch($operator) {
				case '>':
					if($last_date > $order_date) {
						$condition = true;
					}
					break;
				case '<':
					if($last_date < $order_date) {
						$condition = true;
					}
					break;
			}
			
			if ($condition) {
				$user_id = $order->get_user_id();
				$customer = new WC_Customer( $user_id );
				$user_email   = $customer->get_email();
	
				$orders_arr[] = array(
					'action' => 'order',
					'customer' => array(
						'email' => $user_email
					),
					'activity_date' => $order->get_date_created()->format('c'),
					'activity_data' => array(
						'order_id' => $order->get_id(),
						'order_discount_total' => $order->get_discount_total(),
						'order_transaction_id' => $order->get_transaction_id(),
						'order_date_created' => $order->get_date_created(),
						'order_date_completed' => $order->get_date_completed(),
						'order_date_paid' => $order->get_date_paid()
					),
					'monetary_value' => $order->get_total(),
					'relations' => array(
						array(
							'relation_name' => 'order',
							'instance' => array(
								'identifier_value' => $order->get_id(),
								'entity' => array(
									'model_name' => 'order'
								)
							)
						),
					),
				);        
			}
			                        
		}

		return array( 'activities_data' => $orders_arr );
	}

}