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

		$query = new WC_Order_Query( array(
			'limit' => -1
		) );
		$orders = $query->get_orders();

		$orders_arr = array();

		$date_format = 'Y-m-d H:i:s';
		$date = date($date_format);

		$last_date = isset($last_sync_date) ? $last_sync_date : $date;	

		// if is_initial_sync is set then it's not the first sync
		$operator = isset($is_initial_sync) ? '<' : '>';
		
		foreach( $orders as $order ) {
			
			$order_date = $order->get_date_created()->format($date_format);

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
				$user_email = $customer->get_email();
				$user_phone = $customer->get_billing_phone();

				if (!empty($user_email) or !empty($user_phone)) {

					$customer_key = 'email';
					$customer_value = '';
					
					if (empty($user_email)) {
						$customer_key = 'phone_number';
						$customer_value = $user_phone;
					} else {
						$customer_value = $user_email;
					}
					
					$orders_arr[] = array(
								'action' => 'order',
								'customer' => array(
									$customer_key => $customer_value
								),
								'activity_date' => $order->get_date_created()->format('c'),
								'activity_data' => array(
									'order_code' => $order->get_id(),
									'Order Discount Total' => $order->get_discount_total(),
									'Order Transaction ID' => $order->get_transaction_id(),
									'Order Date Created' => $order->get_date_created(),
									'Order Date Completed' => $order->get_date_completed(),
									'Order Date Paid' => $order->get_date_paid()
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

		}

		return array( 'activities_data' => $orders_arr );

	}

}