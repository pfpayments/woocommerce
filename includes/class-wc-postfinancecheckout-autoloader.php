<?php
if (!defined('ABSPATH')) {
	exit();
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
 * This is the autoloader for PostFinance Checkout classes.
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
	public function __construct(){
		spl_autoload_register(array(
			$this,
			'autoload' 
		));
		$this->include_path = WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/';
	}

	/**
	 * Take a class name and turn it into a file name.
	 *
	 * @param  string $class
	 * @return string
	 */
	private function get_file_name_from_class($class){
		return 'class-' . str_replace('_', '-', $class) . '.php';
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path
	 * @return bool successful or not
	 */
	private function load_file($path){
		if ($path && is_readable($path)) {
			include_once ($path);
			return true;
		}
		return false;
	}

	/**
	 * Auto-load WC PostFinanceCheckout classes on demand to reduce memory consumption.
	 *
	 * @param string $class
	 */
	public function autoload($class){
		$class = strtolower($class);
		
		if (0 !== strpos($class, 'wc_postfinancecheckout')) {
			return;
		}
		
		$file = $this->get_file_name_from_class($class);
		$path = '';
		
		if (strpos($class, 'wc_postfinancecheckout_service') === 0) {
			$path = $this->include_path . 'service/';
		}
		elseif (strpos($class, 'wc_postfinancecheckout_entity') === 0) {
			$path = $this->include_path . 'entity/';
		}
		elseif (strpos($class, 'wc_postfinancecheckout_provider') === 0) {
			$path = $this->include_path . 'provider/';
		}
		elseif (strpos($class, 'wc_postfinancecheckout_webhook') === 0) {
			$path = $this->include_path . 'webhook/';
		}
		elseif (strpos($class, 'wc_postfinancecheckout_exception') === 0) {
		    $path = $this->include_path . 'exception/';
		}
		elseif (strpos($class, 'wc_postfinancecheckout_admin') === 0) {
			$path = $this->include_path . 'admin/';
		}
		
		if (empty($path) || !$this->load_file($path . $file)) {
			$this->load_file($this->include_path . $file);
		}
		
		$this->load_file($this->include_path . $file);
	}
}

new WC_PostFinanceCheckout_Autoloader();
