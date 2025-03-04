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
 * Webhook processor to handle delivery indication state transitions.
 *
 * @deprecated 3.0.12 No longer used by internal code and not recommended.
 * @see WC_PostFinanceCheckout_Webhook_Delivery_Indication_Strategy
 */
class WC_PostFinanceCheckout_Webhook_Delivery_Indication extends WC_PostFinanceCheckout_Webhook_Order_Related_Abstract {


	/**
	 * Load entity.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request request.
	 * @return object|\PostFinanceCheckout\Sdk\Model\DeliveryIndication DeliveryIndication.
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	protected function load_entity( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$delivery_indication_service = new \PostFinanceCheckout\Sdk\Service\DeliveryIndicationService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $delivery_indication_service->read( $request->get_space_id(), $request->get_entity_id() );
	}

	/**
	 * Get order id.
	 *
	 * @param mixed $delivery_indication delivery indication.
	 * @return int|string
	 */
	protected function get_order_id( $delivery_indication ) {
		/* @var \PostFinanceCheckout\Sdk\Model\DeliveryIndication $delivery_indication */ //phpcs:ignore
		return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction( $delivery_indication->getTransaction()->getLinkedSpaceId(), $delivery_indication->getTransaction()->getId() )->get_order_id();
	}

	/**
	 * Get transaction id.
	 *
	 * @param mixed $delivery_indication delivery indication.
	 * @return int
	 */
	protected function get_transaction_id( $delivery_indication ) {
		/* @var \PostFinanceCheckout\Sdk\Model\DeliveryIndication $delivery_indication */ //phpcs:ignore
		return $delivery_indication->getLinkedTransaction();
	}

	/**
	 * Process order related inner.
	 *
	 * @param WC_Order $order order.
	 * @param mixed $delivery_indication delivery indication.
	 * @return void
	 */
	protected function process_order_related_inner( WC_Order $order, $delivery_indication ) {
		/* @var \PostFinanceCheckout\Sdk\Model\DeliveryIndication $delivery_indication */ //phpcs:ignore
		switch ( $delivery_indication->getState() ) {
			case \PostFinanceCheckout\Sdk\Model\DeliveryIndicationState::MANUAL_CHECK_REQUIRED:
				$this->review( $order );
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	/**
	 * Review.
	 *
	 * @param WC_Order $order order.
	 * @return void
	 */
	protected function review( WC_Order $order ) {
		$order->add_meta_data( '_postfinancecheckout_manual_check', true );
		$status = apply_filters( 'wc_postfinancecheckout_manual_task_status', 'postfi-manual', $order );
		$status = apply_filters( 'postfinancecheckout_order_update_status', $order, $status, esc_html__( 'A manual decision about whether to accept the payment is required.', 'woo-postfinancecheckout' ) );
		$order->update_status( $status, esc_html__( 'A manual decision about whether to accept the payment is required.', 'woo-postfinancecheckout' ) );
	}
}
