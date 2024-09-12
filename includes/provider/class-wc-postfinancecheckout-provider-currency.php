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
 * Provider of currency information from the gateway.
 */
class WC_PostFinanceCheckout_Provider_Currency extends WC_PostFinanceCheckout_Provider_Abstract {

	/**
	 * Construct.
	 */
	protected function __construct() {
		parent::__construct( 'wc_postfinancecheckout_currencies' );
	}

	/**
	 * Returns the currency by the given code.
	 *
	 * @param string $code code.
	 * @return \PostFinanceCheckout\Sdk\Model\RestCurrency
	 */
	public function find( $code ) { //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::find( $code );
	}

	/**
	 * Returns a list of currencies.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\RestCurrency[]
	 */
	public function get_all() { //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::get_all();
	}


	/**
	 * Fetch data.
	 *
	 * @return array|\PostFinanceCheckout\Sdk\Model\RestCurrency[]
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	protected function fetch_data() {
		$currency_service = new \PostFinanceCheckout\Sdk\Service\CurrencyService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $currency_service->all();
	}

	/**
	 * Get id.
	 *
	 * @param mixed $entry entry.
	 * @return string
	 */
	protected function get_id( $entry ) {
		/* @var \PostFinanceCheckout\Sdk\Model\RestCurrency $entry */ //phpcs:ignore
		return $entry->getCurrencyCode();
	}
}
