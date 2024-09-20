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
 * Interface WC_PostFinanceCheckout_Webhook_Strategy_Interface
 *
 * Defines a strategy interface for processing webhook requests.
 */
interface WC_PostFinanceCheckout_Webhook_Strategy_Interface {

	/**
	 * Checks if the provided webhook entity ID matches the expected ID.
	 *
	 * This method is intended to verify whether the entity ID from a webhook request matches
	 * a specific ID configured within the WC_PostFinanceCheckout_Service_Webhook. This can be used to validate that the
	 * webhook is relevant and should be processed further.
	 *
	 * @param string $webhook_entity_id The entity ID from the webhook request.
	 * @return bool Returns true if the ID matches the system's criteria, false otherwise.
	 */
	public function match( string $webhook_entity_id );

	/**
	 * Process the webhook request.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request object.
	 * @return mixed The result of the processing.
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request );
}
