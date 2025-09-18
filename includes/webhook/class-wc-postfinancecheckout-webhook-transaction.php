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
 * Webhook processor to handle transaction state transitions.
 *
 * @deprecated 3.0.12 No longer used by internal code and not recommended.
 * @see WC_PostFinanceCheckout_Webhook_Transaction_Strategy
 */
class WC_PostFinanceCheckout_Webhook_Transaction extends WC_PostFinanceCheckout_Webhook_Order_Related_Abstract {

	/**
	 * Load entity.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request request.
	 * @return object|\PostFinanceCheckout\Sdk\Model\Transaction
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	protected function load_entity( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$transaction_service = new \PostFinanceCheckout\Sdk\Service\TransactionService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $transaction_service->read( $request->get_space_id(), $request->get_entity_id() );
	}

	/**
	 * Get order id.
	 *
	 * @param mixed $transaction transaction.
	 * @return int|string
	 */
	protected function get_order_id( $transaction ) {
		/* @var \PostFinanceCheckout\Sdk\Model\Transaction $transaction */ //phpcs:ignore
		return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction( $transaction->getLinkedSpaceId(), $transaction->getId() )->get_order_id();
	}

	/**
	 * Get transaction id.
	 *
	 * @param mixed $transaction transaction.
	 * @return int
	 */
	protected function get_transaction_id( $transaction ) {
		/* @var \PostFinanceCheckout\Sdk\Model\Transaction $transaction */ //phpcs:ignore
		return $transaction->getId();
	}

	/**
	 * Process order related inner.
	 *
	 * @param WC_Order $order order.
	 * @param mixed $transaction transaction.
	 * @return void
	 * @throws Exception Exception.
	 */
	protected function process_order_related_inner( WC_Order $order, $transaction ) {
		if ( strpos( $order->get_payment_method(), 'postfinancecheckout' ) === false ) {
			return;
		}

		/* @var \PostFinanceCheckout\Sdk\Model\Transaction $transaction */ //phpcs:ignore
		$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
		if ( $transaction->getState() != $transaction_info->get_state() ) {
			switch ( $transaction->getState() ) {
				case \PostFinanceCheckout\Sdk\Model\TransactionState::CONFIRMED:
				case \PostFinanceCheckout\Sdk\Model\TransactionState::PROCESSING:
					$this->confirm( $transaction, $order );
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED:
					$this->authorize( $transaction, $order );
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE:
					$this->decline( $transaction, $order );
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::FAILED:
					$this->failed( $transaction, $order );
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL:
					$this->authorize( $transaction, $order );
					do_action( 'postfinancecheckout_transaction_authorized_send_email', $order->get_id() );
					$this->fulfill( $transaction, $order );
					WC_PostFinanceCheckout_Helper::set_virtual_zero_total_orders_to_complete( $order );
					WC_PostFinanceCheckout_Helper::update_order_status_for_preorder_if_needed( $order );
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::VOIDED:
					$this->voided( $transaction, $order );
					break;
				case \PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED:
					$this->authorize( $transaction, $order );
					$this->waiting( $transaction, $order );
					break;
				default:
					// Nothing to do.
					break;
			}
		}

		WC_PostFinanceCheckout_Service_Transaction::instance()->update_transaction_info( $transaction, $order );

		// This is edge case for deferred payment methods
		$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
		if ($transaction_info->get_state() === \PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED) {
			do_action( 'postfinancecheckout_transaction_authorized_send_email', $order->get_id() );
		}
	}

	/**
	 * Confirm.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction transaction.
	 * @param WC_Order $order order.
	 * @return void
	 */
	protected function confirm( \PostFinanceCheckout\Sdk\Model\Transaction $transaction, WC_Order $order ) {
		if ( ! $order->get_meta( '_postfinancecheckout_confirmed', true ) && ! $order->get_meta( '_postfinancecheckout_authorized', true ) ) {
			do_action( 'wc_postfinancecheckout_confirmed', $transaction, $order );
			$order->add_meta_data( '_postfinancecheckout_confirmed', 'true', true );
			$default_status = apply_filters( 'wc_postfinancecheckout_confirmed_status', 'postfi-redirected', $order );
			apply_filters( 'postfinancecheckout_order_update_status', $order, $transaction->getState(), $default_status );
			wc_maybe_reduce_stock_levels( $order->get_id() );
		}
	}

	/**
	 * Authorize.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction transaction.
	 * @param \WC_Order $order order.
	 */
	protected function authorize( \PostFinanceCheckout\Sdk\Model\Transaction $transaction, WC_Order $order ) {
		if ( ! $order->get_meta( '_postfinancecheckout_authorized', true ) ) {
			do_action( 'wc_postfinancecheckout_authorized', $transaction, $order );
			$order->add_meta_data( '_postfinancecheckout_authorized', 'true', true );
			$default_status = apply_filters( 'wc_postfinancecheckout_authorized_status', 'on-hold', $order );
			apply_filters( 'postfinancecheckout_order_update_status', $order, $transaction->getState(), $default_status );
			wc_maybe_reduce_stock_levels( $order->get_id() );
			if ( isset( WC()->cart ) ) {
				WC()->cart->empty_cart();
			}
		}
	}

	/**
	 * Waiting.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction transaction.
	 * @param WC_Order $order order.
	 * @return void
	 */
	protected function waiting( \PostFinanceCheckout\Sdk\Model\Transaction $transaction, WC_Order $order ) {
		if ( ! $order->get_meta( '_postfinancecheckout_manual_check', true ) ) {
			do_action( 'wc_postfinancecheckout_completed', $transaction, $order );
			$default_status = apply_filters( 'wc_postfinancecheckout_completed_status', 'postfi-waiting', $order );
			apply_filters( 'postfinancecheckout_order_update_status', $order, $transaction->getState(), $default_status );
		}
	}

	/**
	 * Decline.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction transaction.
	 * @param WC_Order $order order.
	 * @return void
	 */
	protected function decline( \PostFinanceCheckout\Sdk\Model\Transaction $transaction, WC_Order $order ) {
		do_action( 'wc_postfinancecheckout_declined', $transaction, $order );
		$default_status = apply_filters( 'wc_postfinancecheckout_decline_status', 'cancelled', $order );
		apply_filters( 'postfinancecheckout_order_update_status', $order, $transaction->getState(), $default_status );
		WC_PostFinanceCheckout_Helper::instance()->maybe_restock_items_for_order( $order );
	}

	/**
	 * Failed.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction transaction.
	 * @param WC_Order $order order.
	 * @return void
	 */
	protected function failed( \PostFinanceCheckout\Sdk\Model\Transaction $transaction, WC_Order $order ) {
		if ( ! $order->has_status( array( 'pending', 'on-hold' ) ) ) {
			return;
		}

		do_action( 'wc_postfinancecheckout_failed', $transaction, $order );
		$valid_order_statuses = array(
			// Default pending status.
			'pending',
			// Custom order statuses mapped.
			apply_filters( 'postfinancecheckout_wc_status_for_transaction', 'confirmed' ),
			apply_filters( 'postfinancecheckout_wc_status_for_transaction', 'failed' )
		);
		if ( in_array( $order->get_status( 'edit' ), $valid_order_statuses ) ) {
			$default_status = apply_filters( 'wc_postfinancecheckout_failed_status', 'failed', $order );
			apply_filters( 'postfinancecheckout_order_update_status', $order, $transaction->getState(), $default_status );
			WC_PostFinanceCheckout_Helper::instance()->maybe_restock_items_for_order( $order );
		}
	}

	/**
	 * Fulfill.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction transaction.
	 * @param WC_Order $order order.
	 * @return void
	 */
	protected function fulfill( \PostFinanceCheckout\Sdk\Model\Transaction $transaction, WC_Order $order ) {
		do_action( 'wc_postfinancecheckout_fulfill', $transaction, $order );
		// Sets the status to procesing or complete depending on items.
		$order->payment_complete( $transaction->getId() );
	}

	/**
	 * Voided.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction transaction.
	 * @param WC_Order $order order.
	 * @return void
	 */
	protected function voided( \PostFinanceCheckout\Sdk\Model\Transaction $transaction, WC_Order $order ) {
		$default_status = apply_filters( 'wc_postfinancecheckout_voided_status', 'cancelled', $order );
		apply_filters( 'postfinancecheckout_order_update_status', $order, $transaction->getState(), $default_status );
		do_action( 'wc_postfinancecheckout_voided', $transaction, $order );
	}
}
