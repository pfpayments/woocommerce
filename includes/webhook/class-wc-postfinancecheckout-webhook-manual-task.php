<?php
/**
 *
 * WC_PostFinanceCheckout_Webhook_Manual_Task Class
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   wallee AG (http://www.wallee.com/)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * Webhook processor to handle manual task state transitions.
 */
class WC_PostFinanceCheckout_Webhook_Manual_Task extends WC_PostFinanceCheckout_Webhook_Abstract {

	/**
	 * Updates the number of open manual tasks.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request request.
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$manual_task_service = WC_PostFinanceCheckout_Service_Manual_Task::instance();
		$manual_task_service->update();
	}
}
