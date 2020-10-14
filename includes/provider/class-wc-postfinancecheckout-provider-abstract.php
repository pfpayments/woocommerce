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
 * Abstract implementation of a provider.
 */
abstract class WC_PostFinanceCheckout_Provider_Abstract {
	private static $instances = array();
	private $cache_key;
	private $data;

	/**
	 * Constructor.
	 *
	 * @param string $cache_key
	 */
	protected function __construct($cache_key){
		$this->cache_key = $cache_key;
	}

	/**
	 * @return static
	 */
	public static function instance(){
		$class = get_called_class();
		if (!isset(self::$instances[$class])) {
			self::$instances[$class] = new $class();
		}
		return self::$instances[$class];
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
	 * @param mixed $entry
	 * @return string
	 */
	abstract protected function get_id($entry);

	/**
	 * Returns a single entry by id.
	 *
	 * @param string $id
	 * @return mixed
	 */
	public function find($id){
		if ($this->data == null) {
			$this->load_data();
		}
		
		if (isset($this->data[$id])) {
			return $this->data[$id];
		}
		else {
			return false;
		}
	}

	/**
	 * Returns all entries.
	 *
	 * @return array
	 */
	public function get_all(){
		if ($this->data == null) {
			$this->load_data();
		}
		if(!is_array($this->data)){
			return array();
		}		
		return $this->data;
	}

	private function load_data(){
		$cached_data = get_transient($this->cache_key);
		if ($cached_data !== false && is_array($cached_data)) {
			$this->data = $cached_data;
		}
		else {
			$this->data = array();
			try{
				foreach ($this->fetch_data() as $entry) {
					$this->data[$this->get_id($entry)] = $entry;
				}
				set_transient($this->cache_key, $this->data, WEEK_IN_SECONDS);
			}
			catch(\PostFinanceCheckout\Sdk\ApiException $e){}
			catch(\PostFinanceCheckout\Sdk\Http\ConnectionException $e){}
			
		}
	}
}