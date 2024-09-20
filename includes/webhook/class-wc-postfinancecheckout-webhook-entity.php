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
 * WC_PostFinanceCheckout_Webhook_Entity
 */
class WC_PostFinanceCheckout_Webhook_Entity {
	/**
	 * Id.
	 *
	 * @var mixed
	 */
	private $id;

	/**
	 * Name.
	 *
	 * @var mixed
	 */
	private $name;

	/**
	 * States.
	 *
	 * @var array
	 */
	private $states;

	/**
	 * Notify every change.
	 *
	 * @var false|mixed
	 */
	private $notify_every_change;

	/**
	 * Handler class name.
	 *
	 * @var mixed.
	 */
	private $handler_class_name;

	/**
	 * Construct.
	 *
	 * @param mixed $id id.
	 * @param mixed $name name.
	 * @param array $states states.
	 * @param mixed $handler_class_name handler class name.
	 * @param mixed $notify_every_change notify every change.
	 */
	public function __construct( $id, $name, array $states, $handler_class_name, $notify_every_change = false ) {
		$this->id = $id;
		$this->name = $name;
		$this->states = $states;
		$this->notify_every_change = $notify_every_change;
		$this->handler_class_name = $handler_class_name;
	}

	/**
	 * Get id.
	 *
	 * @return mixed
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get name.
	 *
	 * @return mixed
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get states.
	 *
	 * @return array
	 */
	public function get_states() {
		return $this->states;
	}

	/**
	 * Is notify every change.
	 *
	 * @return false|mixed
	 */
	public function is_notify_every_change() {
		return $this->notify_every_change;
	}

	/**
	 * Get Handler class name.
	 *
	 * @return mixed
	 * @deprecated This method will be deprecated in a future version as it is no longer necessary for webhook strategies.
	 */
	public function get_handler_class_name() {
		return $this->handler_class_name;
	}
}
