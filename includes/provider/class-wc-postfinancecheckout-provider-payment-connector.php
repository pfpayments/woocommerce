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
 * Provider of payment connector information from the gateway.
 */
class WC_PostFinanceCheckout_Provider_Payment_Connector extends WC_PostFinanceCheckout_Provider_Abstract {

	/**
	 * Construct.
	 */
	protected function __construct() {
		parent::__construct( 'wc_postfinancecheckout_payment_connectors' );
	}

	/**
	 * Returns the payment connector by the given id.
	 *
	 * @param int $id Id.
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentConnector
	 */
	public function find( $id ) { //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::find( $id );
	}

	/**
	 * Returns a list of payment connectors.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentConnector[]
	 */
	public function get_all() { //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::get_all();
	}

	/**
	 * Fetch data.
	 *
	 * @return array|\PostFinanceCheckout\Sdk\Model\PaymentConnector[]
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	protected function fetch_data() {
		$connector_service = new \PostFinanceCheckout\Sdk\Service\PaymentConnectorService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $connector_service->all();
	}

	/**
	 * Get id.
	 *
	 * @param mixed $entry entry.
	 * @return int|string
	 */
	protected function get_id( $entry ) {
		/* @var \PostFinanceCheckout\Sdk\Model\PaymentConnector $entry */ //phpcs:ignore
		return $entry->getId();
	}
}
