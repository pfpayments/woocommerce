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
 * Abstract implementation of a provider.
 */
abstract class WC_PostFinanceCheckout_Provider_Abstract {
	/**
	 * Instances.
	 *
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Cache key.
	 *
	 * @var string
	 */
	private $cache_key;


	/**
	 * Data.
	 *
	 * @var mixed
	 */
	private $data;

	/**
	 * Constructor.
	 *
	 * @param string $cache_key cache key.
	 */
	protected function __construct( $cache_key ) {
		$this->cache_key = $cache_key;
	}

	/**
	 * Instance.
	 *
	 * @return static
	 */
	public static function instance() {
		$class = get_called_class();
		if ( ! isset( self::$instances[ $class ] ) ) {
			self::$instances[ $class ] = new $class();
		}
		return self::$instances[ $class ];
	}

	/**
	 * Fetch the data from the remote server.
	 *
	 * @return array
	 */
	abstract protected function fetch_data();

	/**
	 * Returns the id of the given entry.
	 *
	 * @param mixed $entry entry.
	 * @return string
	 */
	abstract protected function get_id( $entry );

	/**
	 * Returns a single entry by id.
	 *
	 * @param string $id Id.
	 * @return mixed
	 */
	public function find( $id ) {
		if ( null == $this->data ) {
			$this->load_data();
		}

		if ( isset( $this->data[ $id ] ) ) {
			return $this->data[ $id ];
		} else {
			return false;
		}
	}

	/**
	 * Returns all entries.
	 *
	 * @return array
	 */
	public function get_all() {
		if ( null == $this->data ) {
			$this->load_data();
		}
		if ( ! is_array( $this->data ) ) {
			return array();
		}
		return $this->data;
	}

	/**
	 * Load data.
	 *
	 * @return void
	 */
	private function load_data() {
		$cached_data = get_transient( $this->cache_key );
		if ( false !== $cached_data && is_array( $cached_data ) ) {
			$this->data = $cached_data;
		} else {
			$this->data = array();
			try {
				foreach ( $this->fetch_data() as $entry ) {
					$this->data[ $this->get_id( $entry ) ] = $entry;
				}
				set_transient( $this->cache_key, $this->data, WEEK_IN_SECONDS );
			} catch ( \PostFinanceCheckout\Sdk\ApiException $e ) {
				return;
			} catch ( \PostFinanceCheckout\Sdk\Http\ConnectionException $e ) {
				return;
			}
		}
	}
}
