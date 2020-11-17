<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
 *
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * Abstract webhook processor.
 */
abstract class WC_PostFinanceCheckout_Webhook_Abstract {
	private static $instances = array();

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
	 * Processes the received webhook request.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request
	 */
	abstract public function process(WC_PostFinanceCheckout_Webhook_Request $request);
}