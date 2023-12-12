<?php
/**
 *
 * WC_PostFinanceCheckout_Webhook_Method_Configuration Class
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   wallee AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * Webhook processor to handle payment method configuration state transitions.
 */
class WC_PostFinanceCheckout_Webhook_Method_Configuration extends WC_PostFinanceCheckout_Webhook_Abstract {

	/**
	 * Synchronizes the payment method configurations on state transition.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request request.
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$payment_method_configuration_service = WC_PostFinanceCheckout_Service_Method_Configuration::instance();
		$payment_method_configuration_service->synchronize();
	}
}
