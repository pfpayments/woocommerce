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
 * Class WC_PostFinanceCheckout_Return_Handler.
 * This class handles the customer returns
 *
 * @class WC_PostFinanceCheckout_Return_Handler
 */
class WC_PostFinanceCheckout_Return_Handler {

	/**
	 * Initialise
	 */
	public static function init() {
		add_action(
			'woocommerce_api_postfinancecheckout_return',
			array(
				__CLASS__,
				'process',
			)
		);
	}

	/**
	 * Process
	 */
	public static function process() {
		if ( isset( $_GET['action'] ) && isset( $_GET['order_key'] ) && isset( $_GET['order_id'] ) ) { //phpcs:ignore
			$order_key = sanitize_text_field( wp_unslash( $_GET['order_key'] ) ); //phpcs:ignore
			$order_id = absint( wp_unslash( $_GET['order_id'] ) ); //phpcs:ignore
			$order = WC_Order_Factory::get_order( $order_id );
			$action = sanitize_text_field( wp_unslash( $_GET['action'] ) );
			if ( $order->get_id() === $order_id && $order->get_order_key() === $order_key ) {
				switch ( $action ) {
					case 'success':
						self::process_success( $order );
						break;
					case 'failure':
						self::process_failure( $order );
						break;
					default:
				}
			}
		}
		wp_redirect( home_url( '/' ) ); //phpcs:ignore
		exit();
	}

	/**
	 * Process Success
	 *
	 * @param WC_Order $order docorderument.
	 */
	protected static function process_success( WC_Order $order ) {
		$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();

		$transaction_service->wait_for_transaction_state(
			$order,
			array(
				\PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED,
				\PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
				\PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
			),
			5
		);
		$gateway = wc_get_payment_gateway_by_order( $order );
		$url = apply_filters( 'wc_postfinancecheckout_success_url', $gateway->get_return_url( $order ), $order ); //phpcs:ignore
		wp_redirect( $url ); //phpcs:ignore
		exit();
	}

	/**
	 * Process Failure
	 *
	 * @param WC_Order $order order.
	 */
	protected static function process_failure( WC_Order $order ) {
		$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
		$transaction_service->wait_for_transaction_state(
			$order,
			array(
				\PostFinanceCheckout\Sdk\Model\TransactionState::FAILED,
			),
			5
		);
		$transaction = WC_PostFinanceCheckout_Entity_Transaction_Info::load_newest_by_mapped_order_id( $order->get_id() );
		if ( $transaction->get_state() == \PostFinanceCheckout\Sdk\Model\TransactionState::FAILED ) {
			WC()->session->set( 'order_awaiting_payment', $order->get_id() );
		}
		$user_message = $transaction->get_user_failure_message();
		$failure_reason = $transaction->get_failure_reason();
		if ( empty( $user_message ) && null !== $failure_reason ) {
			$user_message = $failure_reason;
		}
		if ( ! empty( $user_message ) ) {
			WC()->session->set( 'postfinancecheckout_failure_message', $user_message );
		}

		// The order-pay functionality does not work currently with our plugin, so we don't
		// redirect the user there.
		// Otherwise we would get the url using:
		// apply_filters( 'wc_postfinancecheckout_pay_failure_url', $order->get_checkout_payment_url( false ), $order ).

		$url = apply_filters( 'wc_postfinancecheckout_checkout_failure_url', wc_get_checkout_url(), $order ); //phpcs:ignore
		wp_redirect( $url ); //phpcs:ignore

		exit();
	}
}
WC_PostFinanceCheckout_Return_Handler::init();
