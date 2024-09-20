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
 * Abstract webhook processor for order related entities.
 *
 * @deprecated 3.0.12 No longer used by internal code and not recommended.
 * @see WC_PostFinanceCheckout_Webhook_Strategy_Base
 */
abstract class WC_PostFinanceCheckout_Webhook_Order_Related_Abstract extends WC_PostFinanceCheckout_Webhook_Abstract {

	/**
	 * Processes the received order related webhook request.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request request.
	 * @throws Exception Exception.
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {

		WC_PostFinanceCheckout_Helper::instance()->start_database_transaction();
		$entity = $this->load_entity( $request );
		try {
			WC_PostFinanceCheckout_Helper::instance()->lock_by_transaction_id( $request->get_space_id(), $this->get_transaction_id( $entity ) );
			$order = WC_Order_Factory::get_order( $this->get_order_id( $entity ) );
			if ( false != $order && $order->get_id() ) {
				$this->process_order_related_inner( $order, $entity );
			}
			WC_PostFinanceCheckout_Helper::instance()->commit_database_transaction();
		} catch ( Exception $e ) {
			WC_PostFinanceCheckout_Helper::instance()->rollback_database_transaction();
			throw $e;
		}
	}

	/**
	 * Loads and returns the entity for the webhook request.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request request.
	 * @return object
	 */
	abstract protected function load_entity( WC_PostFinanceCheckout_Webhook_Request $request );

	/**
	 * Returns the order's increment id linked to the entity.
	 *
	 * @param object $entity entity.
	 * @return string
	 */
	abstract protected function get_order_id( $entity );

	/**
	 * Returns the transaction's id linked to the entity.
	 *
	 * @param object $entity entity.
	 * @return int
	 */
	abstract protected function get_transaction_id( $entity );

	/**
	 * Actually processes the order related webhook request.
	 *
	 * This must be implemented
	 *
	 * @param WC_Order $order order.
	 * @param Object   $entity entity.
	 */
	abstract protected function process_order_related_inner( WC_Order $order, $entity );
}
