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
 * Class WC_PostFinanceCheckout_Autoloader.
 * This is the autoloader for PostFinance Checkout classes.
 *
 * @class WC_PostFinanceCheckout_Autoloader
 */
class WC_PostFinanceCheckout_Autoloader {

	/**
	 * Path to the includes directory.
	 *
	 * @var string
	 */
	private $include_path = '';

	/**
	 * The Constructor.
	 */
	public function __construct() {
		spl_autoload_register(
			array(
				$this,
				'autoload',
			)
		);
		$this->include_path = WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/';
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class_file class.
	 * @return string
	 */
	private function get_file_name_from_class( $class_file ) {
		$class = preg_replace( '/(?<!^)[A-Z]/', '-$0', $class_file );
		return 'class-' . str_replace( '_', '-', strtolower( $class ) ) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path path.
	 * @return bool successful or not
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;
			return true;
		}
		return false;
	}

	/**
	 * Auto-load WC PostFinanceCheckout classes on demand to reduce memory consumption.
	 *
	 * @param string $class_file class.
	 */
	public function autoload( $class_file ) {
		$class = strtolower( $class_file );

		if ( 0 !== strpos( $class, 'wc_postfinancecheckout' ) ) {
			return;
		}

		$file = $this->get_file_name_from_class( $class );
		$path = '';

		if ( strpos( $class, 'wc_postfinancecheckout_service' ) === 0 ) {
			$path = $this->include_path . 'service/';
		} elseif ( strpos( $class, 'wc_postfinancecheckout_entity' ) === 0 ) {
			$path = $this->include_path . 'entity/';
		} elseif ( strpos( $class, 'wc_postfinancecheckout_provider' ) === 0 ) {
			$path = $this->include_path . 'provider/';
		} elseif ( strpos( $class, 'wc_postfinancecheckout_webhook' ) === 0 ) {
			if ( strpos( $class, 'strategy' ) !== false ) {
				$path = $this->include_path . 'webhook/strategies/';
			} else {
				$path = $this->include_path . 'webhook/';
			}
		} elseif ( strpos( $class, 'wc_postfinancecheckout_exception' ) === 0 ) {
			$path = $this->include_path . 'exception/';
		} elseif ( strpos( $class, 'wc_postfinancecheckout_admin' ) === 0 ) {
			$path = $this->include_path . 'admin/';
		}

		if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

new WC_PostFinanceCheckout_Autoloader();
