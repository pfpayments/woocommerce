<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * Webhook processor to handle manual task state transitions.
 */
class WC_PostFinanceCheckout_Webhook_Manual_Task extends WC_PostFinanceCheckout_Webhook_Abstract {

	/**
	 * Updates the number of open manual tasks.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request
	 */
    public function process(WC_PostFinanceCheckout_Webhook_Request $request){
        $manual_task_service = WC_PostFinanceCheckout_Service_Manual_Task::instance();
		$manual_task_service->update();
	}
}