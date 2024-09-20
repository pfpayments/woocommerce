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
 * Manages the strategy for processing webhook requests that pertain to payment method configurations.
 *
 * This class extends the base webhook strategy to specifically handle webhooks related to
 * payment method configuration updates. It ensures that payment method configurations are synchronized
 * with the latest changes indicated by incoming webhook requests.
 */
class WC_PostFinanceCheckout_Webhook_Method_Configuration_Strategy extends WC_PostFinanceCheckout_Webhook_Strategy_Base {

	/**
	 * Match function.
	 *
	 * @inheritDoc
	 * @param string $webhook_entity_id The webhook entity id.
	 */
	public function match( string $webhook_entity_id ) {
		return WC_PostFinanceCheckout_Service_Webhook::POSTFINANCECHECKOUT_PAYMENT_METHOD_CONFIGURATION == $webhook_entity_id;
	}

	/**
	 * Processes the incoming webhook request related to payment method configurations.
	 *
	 * This method calls upon the payment method configuration service to synchronize configuration
	 * data based on the webhook information. This could involve updating local data stores to reflect
	 * changes made on the remote server side, ensuring that payment method settings are current.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request object containing necessary data.
	 * @return void
	 * @throws \Exception Throws an exception if the synchronization process encounters a problem.
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$payment_method_configuration_service = WC_PostFinanceCheckout_Service_Method_Configuration::instance();
		$payment_method_configuration_service->synchronize();
	}
}
