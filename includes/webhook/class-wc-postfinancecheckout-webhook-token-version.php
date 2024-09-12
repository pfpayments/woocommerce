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
 * Webhook processor to handle token version state transitions.
 *
 * @deprecated 3.0.12 No longer used by internal code and not recommended.
 * @see WC_PostFinanceCheckout_Webhook_Token_Version_Strategy
 */
class WC_PostFinanceCheckout_Webhook_Token_Version extends WC_PostFinanceCheckout_Webhook_Abstract {

	/**
	 * Process.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request request.
	 * @return void
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$token_service = WC_PostFinanceCheckout_Service_Token::instance();
		$token_service->update_token_version( $request->get_space_id(), $request->get_entity_id() );
	}
}
