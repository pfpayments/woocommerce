<?php
/**
 * Plugin Name: PostFinanceCheckout
 * Author: postfinancecheckout AG
 * Text Domain: postfinancecheckout
 * Domain Path: /languages/
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   postfinancecheckout AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

defined( 'ABSPATH' ) || exit;

/**
 * Webhook processor to handle manual task state transitions.
 *
 * @deprecated 3.0.12 No longer used by internal code and not recommended.
 * @see WC_PostFinanceCheckout_Webhook_Manual_Task_Strategy
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
