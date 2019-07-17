<?php
if (!defined('ABSPATH')) {
	exit(); // Exit if accessed directly.
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * Provider of currency information from the gateway.
 */
class WC_PostFinanceCheckout_Provider_Currency extends WC_PostFinanceCheckout_Provider_Abstract {

	protected function __construct(){
		parent::__construct('wc_postfinancecheckout_currencies');
	}

	/**
	 * Returns the currency by the given code.
	 *
	 * @param string $code
	 * @return \PostFinanceCheckout\Sdk\Model\RestCurrency
	 */
	public function find($code){
		return parent::find($code);
	}

	/**
	 * Returns a list of currencies.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\RestCurrency[]
	 */
	public function get_all(){
		return parent::get_all();
	}

	protected function fetch_data(){
	    $currency_service = new \PostFinanceCheckout\Sdk\Service\CurrencyService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		return $currency_service->all();
	}

	protected function get_id($entry){
		/* @var \PostFinanceCheckout\Sdk\Model\RestCurrency $entry */
		return $entry->getCurrencyCode();
	}
}