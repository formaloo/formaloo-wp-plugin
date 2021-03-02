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
		$last_sync_date_str = 'last_orders_sync_date';
		$is_initial_sync_str = 'is_initial_sync';

		$orders = wc_get_orders( array('numberposts' => -1) );

		$orders_arr = array();

		$date_format = 'Y-m-d H:i:s';

		$date = date($date_format);

		$last_date = isset($data[$last_sync_date_str]) ? $data[$last_sync_date_str] : $date;	

		foreach( $orders as $order ){
			
			$order_date = $order->get_date_created()->format($date_format);
			// if is_initial_sync is set then it's not the first sync
			$operator = isset($data[$is_initial_sync_str]) ? '<' : '>';

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
						'order_total' => $order->get_total(),
						'order_total_discount' => $order->get_total_discount(),
						'order_transaction_id' => $order->get_transaction_id(),
						'order_date_created' => $order->get_date_created(),
						'order_date_completed' => $order->get_date_completed(),
						'order_date_paid' => $order->get_date_paid()
					),
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

		if (!isset($data[$is_initial_sync_str])) {
			$data[$is_initial_sync_str] = 'set_before';
		}
		$data[$last_sync_date_str] = $date;
		update_option('formaloo_data', $data);

		return array( 'activities_data' => $orders_arr );
	}

}