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
 * Provider of language information from the gateway.
 */
class WC_PostFinanceCheckout_Provider_Language extends WC_PostFinanceCheckout_Provider_Abstract {

	/**
	 * Construct.
	 */
	protected function __construct() {
		parent::__construct( 'wc_postfinancecheckout_languages' );
	}

	/**
	 * Returns the language by the given code.
	 *
	 * @param string $code code.
	 * @return \PostFinanceCheckout\Sdk\Model\RestLanguage
	 */
	public function find( $code ) { //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::find( $code );
	}

	/**
	 * Returns the primary language in the given group.
	 *
	 * @param string $code code.
	 * @return \PostFinanceCheckout\Sdk\Model\RestLanguage
	 */
	public function find_primary( $code ) {
		$code = substr( $code, 0, 2 );
		foreach ( $this->get_all() as $language ) {
			if ( $language->getIso2Code() == $code && $language->getPrimaryOfGroup() ) {
				return $language;
			}
		}

		return false;
	}

	/**
	 * Find by iso code.
	 *
	 * @param mixed $iso iso.
	 * @return false|\PostFinanceCheckout\Sdk\Model\RestLanguage
	 */
	public function find_by_iso_code( $iso ) {
		foreach ( $this->get_all() as $language ) {
			if ( $language->getIso2Code() == $iso || $language->getIso3Code() == $iso ) {
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
	public function get_all() { //phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::get_all();
	}

	/**
	 * Fetch data.
	 *
	 * @return array|\PostFinanceCheckout\Sdk\Model\RestLanguage[]
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	protected function fetch_data() {
		$language_service = new \PostFinanceCheckout\Sdk\Service\LanguageService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $language_service->all();
	}

	/**
	 * Get id.
	 *
	 * @param mixed $entry entry.
	 * @return string
	 */
	protected function get_id( $entry ) {
		/* @var \PostFinanceCheckout\Sdk\Model\RestLanguage $entry */ //phpcs:ignore
		return $entry->getIetfCode();
	}
}
