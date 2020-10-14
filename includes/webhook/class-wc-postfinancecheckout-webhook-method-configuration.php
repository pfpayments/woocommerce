<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
 *
 * @author wallee AG (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * Webhook processor to handle payment method configuration state transitions.
 */
class WC_PostFinanceCheckout_Webhook_Method_Configuration extends WC_PostFinanceCheckout_Webhook_Abstract {

	/**
	 * Synchronizes the payment method configurations on state transition.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request
	 */
    public function process(WC_PostFinanceCheckout_Webhook_Request $request){
        $payment_method_configuration_service = WC_PostFinanceCheckout_Service_Method_Configuration::instance();
		$payment_method_configuration_service->synchronize();
	}
}