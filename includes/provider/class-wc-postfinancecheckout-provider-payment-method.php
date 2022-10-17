<?php
/**
 *
 * WC_PostFinanceCheckout_Provider_Payment_Method Class
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   wallee AG (http://www.wallee.com/)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * Provider of payment method information from the gateway.
 */
class WC_PostFinanceCheckout_Provider_Payment_Method extends WC_PostFinanceCheckout_Provider_Abstract {

	/**
	 * Construct.
	 */
	protected function __construct() {
		parent::__construct( 'wc_postfinancecheckout_payment_methods' );
	}

	/**
	 * Returns the payment method by the given id.
	 *
	 * @param int $id id.
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentMethod
	 */
	public function find( $id ) {
		return parent::find( $id );
	}

	/**
	 * Returns a list of payment methods.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentMethod[]
	 */
	public function get_all() {
		return parent::get_all();
	}

	/**
	 * Fetch data.
	 *
	 * @return array|\PostFinanceCheckout\Sdk\Model\PaymentMethod[]
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	protected function fetch_data() {
		$method_service = new \PostFinanceCheckout\Sdk\Service\PaymentMethodService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $method_service->all();
	}

	/**
	 * Get id.
	 *
	 * @param mixed $entry entry.
	 * @return int|string
	 */
	protected function get_id( $entry ) {
		/* @var \PostFinanceCheckout\Sdk\Model\PaymentMethod $entry */
		return $entry->getId();
	}
}
