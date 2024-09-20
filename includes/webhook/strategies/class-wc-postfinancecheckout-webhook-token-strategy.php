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
 * Class WC_PostFinanceCheckout_Webhook_Token_Strategy
 *
 * Handles the strategy for processing webhook requests related to tokens.
 * This class extends the base webhook strategy class and is specialized in handling
 * webhook requests that are associated with token updates. Tokens typically represent
 * authentication or authorization tokens used within the system.
 */
class WC_PostFinanceCheckout_Webhook_Token_Strategy extends WC_PostFinanceCheckout_Webhook_Strategy_Base {

	/**
	 * Match function.
	 *
	 * @inheritDoc
	 * @param string $webhook_entity_id The webhook entity id.
	 */
	public function match( string $webhook_entity_id ) {
		return WC_PostFinanceCheckout_Service_Webhook::POSTFINANCECHECKOUT_TOKEN == $webhook_entity_id;
	}

	/**
	 * Processes the incoming webhook request that pertains to tokens.
	 *
	 * This method invokes the token service to update the token identified by the
	 * space ID and entity ID provided in the webhook request. It ensures that token
	 * data is synchronized and up-to-date across the system.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request.
	 * @return void
	 * @throws Exception Throws an exception if there is an issue while processing the token update.
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$token_service = WC_PostFinanceCheckout_Service_Token::instance();
		$token_service->update_token( $request->get_space_id(), $request->get_entity_id() );
	}
}
