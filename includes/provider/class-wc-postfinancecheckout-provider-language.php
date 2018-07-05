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
 * Provider of language information from the gateway.
 */
class WC_PostFinanceCheckout_Provider_Language extends WC_PostFinanceCheckout_Provider_Abstract {

	protected function __construct(){
		parent::__construct('wc_postfinancecheckout_languages');
	}

	/**
	 * Returns the language by the given code.
	 *
	 * @param string $code
	 * @return \PostFinanceCheckout\Sdk\Model\RestLanguage
	 */
	public function find($code){
		return parent::find($code);
	}

	/**
	 * Returns the primary language in the given group.
	 *
	 * @param string $code
	 * @return \PostFinanceCheckout\Sdk\Model\RestLanguage
	 */
	public function find_primary($code){
		$code = substr($code, 0, 2);
		foreach ($this->get_all() as $language) {
			if ($language->getIso2Code() == $code && $language->getPrimaryOfGroup()) {
				return $language;
			}
		}
		
		return false;
	}
	
	public function find_by_iso_code($iso){
		foreach ($this->get_all() as $language) {
			if ($language->getIso2Code() == $iso || $language->getIso3Code() == $iso) {
				return $language;
			}
		}
		return false;
	}

	/**
	 * Returns a list of language.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\RestLanguage[]
	 */
	public function get_all(){
		return parent::get_all();
	}

	protected function fetch_data(){
	    $language_service = new \PostFinanceCheckout\Sdk\Service\LanguageService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		return $language_service->all();
	}

	protected function get_id($entry){
		/* @var \PostFinanceCheckout\Sdk\Model\RestLanguage $entry */
		return $entry->getIetfCode();
	}
}