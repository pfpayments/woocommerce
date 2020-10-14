<?php
if (!defined('ABSPATH')) {
	exit(); // Exit if accessed directly.
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
 * Provider of payment method information from the gateway.
 */
class WC_PostFinanceCheckout_Provider_Payment_Method extends WC_PostFinanceCheckout_Provider_Abstract {

	protected function __construct(){
		parent::__construct('wc_postfinancecheckout_payment_methods');
	}

	/**
	 * Returns the payment method by the given id.
	 *
	 * @param int $id
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentMethod
	 */
	public function find($id){
		return parent::find($id);
	}

	/**
	 * Returns a list of payment methods.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentMethod[]
	 */
	public function get_all(){
		return parent::get_all();
	}

	protected function fetch_data(){
	    $method_service = new \PostFinanceCheckout\Sdk\Service\PaymentMethodService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		return $method_service->all();
	}

	protected function get_id($entry){
		/* @var \PostFinanceCheckout\Sdk\Model\PaymentMethod $entry */
		return $entry->getId();
	}
}