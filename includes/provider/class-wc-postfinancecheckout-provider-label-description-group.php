<?php
if (!defined('ABSPATH')) {
	exit(); // Exit if accessed directly.
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * Provider of label descriptor group information from the gateway.
 */
class WC_PostFinanceCheckout_Provider_Label_Description_Group extends WC_PostFinanceCheckout_Provider_Abstract {

	protected function __construct(){
		parent::__construct('wc_postfinancecheckout_label_description_groups');
	}

	/**
	 * Returns the label descriptor group by the given code.
	 *
	 * @param int $id
	 * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup
	 */
	public function find($id){
		return parent::find($id);
	}

	/**
	 * Returns a list of label descriptor groups.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup[]
	 */
	public function get_all(){
		return parent::get_all();
	}

	protected function fetch_data(){
	    $label_description_group_service = new \PostFinanceCheckout\Sdk\Service\LabelDescriptionGroupService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		return $label_description_group_service->all();
	}

	protected function get_id($entry){
		/* @var \PostFinanceCheckout\Sdk\Model\LabelDescriptorGroup $entry */
		return $entry->getId();
	}
}