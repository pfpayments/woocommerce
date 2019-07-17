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
 * Provider of payment connector information from the gateway.
 */
class WC_PostFinanceCheckout_Provider_Payment_Connector extends WC_PostFinanceCheckout_Provider_Abstract {

	protected function __construct(){
		parent::__construct('wc_postfinancecheckout_payment_connectors');
	}

	/**
	 * Returns the payment connector by the given id.
	 *
	 * @param int $id
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentConnector
	 */
	public function find($id){
		return parent::find($id);
	}

	/**
	 * Returns a list of payment connectors.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentConnector[]
	 */
	public function get_all(){
		return parent::get_all();
	}

	protected function fetch_data(){
	    $connector_service = new \PostFinanceCheckout\Sdk\Service\PaymentConnectorService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		return $connector_service->all();
	}

	protected function get_id($entry){
		/* @var \PostFinanceCheckout\Sdk\Model\PaymentConnector $entry */
		return $entry->getId();
	}
}