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

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Automattic\WooCommerce\StoreApi\Payments\PaymentContext;
use Automattic\WooCommerce\StoreApi\Payments\PaymentResult;

defined( 'ABSPATH' ) || exit;

/**
 * WC_Blocks_Support class.
 *
 * @extends AbstractPaymentMethodType
 */
final class WC_PostFinanceCheckout_Blocks_Support extends AbstractPaymentMethodType {
	/**
	 * Payment method name defined by payment methods extending this class.
	 *
	 * @var string
	 */
	protected $name = 'postfinancecheckout';

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings['space_id'] = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_ID );
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return true;
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$dependencies = array();
		$version = '3.3.15';

		wp_register_script(
			'WooCommerce_PostFinanceCheckout_blocks_support',
			WooCommerce_PostFinanceCheckout::instance()->plugin_url() . '/assets/js/frontend/blocks/build/index.js',
			$dependencies,
			$version,
			true
		);

		wp_localize_script(
			'WooCommerce_PostFinanceCheckout_blocks_support',
			'postfinancecheckout_block_params',
			array(
				'postfinancecheckout_nonce' => wp_create_nonce( 'postfinancecheckout_nonce_block' ),
			)
		);

		return array( 'WooCommerce_PostFinanceCheckout_blocks_support' );
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return array(
			'title' => 'PostFinanceCheckout',
			'description' => 'PostFinanceCheckout description',
		);
	}

	/**
	 * Endpoint for getting the payment methods.
	 *
	 * This function is used for building the data that is expected by WooCommerce Blocks.
	 * The information is returned directly via a JSON response
	 *
	 * @return array
	 */
	public static function get_payment_methods(): array {
		try {
			$update_transaction = isset( $_POST['updateTransaction'] ) ? (bool) sanitize_key( wp_unslash( $_POST['updateTransaction'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Missing

			if ( true === $update_transaction ) {
				$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
				$transaction_service->load_and_update_transaction_from_session();
			}

			$payment_gateways = WC()->payment_gateways()->payment_gateways();

			$available_payment_methods = WC_PostFinanceCheckout_Service_Transaction::instance()->get_possible_payment_methods_for_cart();

			$payment_plugin = array_filter(
			  $payment_gateways,
			  fn( $key ) => str_contains( $key, 'postfinancecheckout_' ),
			  ARRAY_FILTER_USE_KEY
			);

			$payments_list =
			  array_map(
				function ( $payment_gateway ) use ( $available_payment_methods ) {
					$has_subscription = WC_PostFinanceCheckout_Zero_Gateway::cart_has_subscription();
					$cartTotal = (WC()->cart && WC()->cart->total) ?? 0;
					$isPaymentMethodVisibleOnCheckout = $payment_gateway->get_payment_configuration_id() === WC_PostFinanceCheckout_Zero_Gateway::ZERO_PAYMENT_CONF_ID && $cartTotal == 0;

					if ( !$isPaymentMethodVisibleOnCheckout ) {
						$isPaymentMethodVisibleOnCheckout = in_array( $payment_gateway->get_payment_configuration_id(), $available_payment_methods, true ) && ( $cartTotal > 0 || $has_subscription );
					}

					return array(
					  'name' => $payment_gateway->id,
					  'label' => $payment_gateway->get_title(),
					  'ariaLabel' => $payment_gateway->get_title(),
					  'description' => $payment_gateway->get_description(),
					  'configuration_id' => $payment_gateway->get_payment_configuration_id(),
					  'integration_mode' => get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_INTEGRATION ),
					  'supports' => $payment_gateway->supports,
					  'icon' => $payment_gateway->get_icon(),
					  'isActive' => $isPaymentMethodVisibleOnCheckout
					);
				},
				$payment_plugin
			  );

			return array_values( $payments_list );
		} catch (\Exception $e) {
			return [];
		}
	}

	/**
	 * Send the list back to the requester in a JSON.
	 *
	 * @return void
	 */
	public static function get_payment_methods_json() {
		if ( ! isset( $_POST['postfinancecheckout_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['postfinancecheckout_nonce'] ) ), 'postfinancecheckout_nonce_block' ) ) {  //phpcs:ignore
			wp_send_json_error( 'Invalid request: missing nonce value', 403 );
		}
		$payment_methods = self::get_payment_methods();
		wp_send_json( $payment_methods );
	}

	/**
	 * Endpoint for checking if the payment method is available.
	 *
	 * Returns true if the payment method in the request object belongs to the list of available payment methods,
	 * false otherwise.
	 * The response (true or false) it delivered in a JSON response.
	 *
	 * @return void
	 */
	public static function is_payment_method_available() {

		if ( ! isset( $_POST['postfinancecheckout_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['postfinancecheckout_nonce'] ) ), 'postfinancecheckout_nonce_block' ) ) {  //phpcs:ignore
			wp_send_json_error( 'Invalid nonce', 403 );
		}

		if ( empty( $_POST['payment_method'] ) || empty( $_POST['configuration_id'] ) ) { //phpcs:ignore
			wp_send_json( false );
		}

		$configuration_id = isset( $_POST['configuration_id'] ) ? absint( sanitize_key( wp_unslash( $_POST['configuration_id'] ) ) ) : null; //phpcs:ignore
		$available_payment_methods = WC_PostFinanceCheckout_Service_Transaction::instance()->get_possible_payment_methods_for_cart();
		wp_send_json( in_array( $configuration_id, $available_payment_methods, true ) );
	}

	/**
	 * Enqueues the scripts provided by the portal.
	 *
	 * This function first gets from the portal the URL of the javascript file that is needed for loading
	 * the payment form during the checkout process. Then, it enqueues the URL in the response to the browser,
	 * The javascript file will be loaded later on by the browser when the checkout form is displayed.
	 *
	 * Depending on the integration mode being used, the function will find the URL for the iframe or
	 * for the lightbox.
	 *
	 * @return void
	 */
	public static function enqueue_portal_scripts() {

		try {
			$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
			$transaction = $transaction_service->get_transaction_from_session();

			$js_url = '';
			$zeroPaymentMethod = new WC_PostFinanceCheckout_Zero_Gateway();
			if ( !$zeroPaymentMethod->is_available() || $zeroPaymentMethod->cart_has_subscription() ) {
				switch( get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_INTEGRATION ) ) {
					case WC_PostFinanceCheckout_Integration::POSTFINANCECHECKOUT_IFRAME:
						$js_url = $transaction_service->get_javascript_url_for_transaction( $transaction );
						break;
					case WC_PostFinanceCheckout_Integration::POSTFINANCECHECKOUT_LIGHTBOX:
						$js_url = $transaction_service->get_lightbox_url_for_transaction( $transaction );
						break;
				}
			}

			if ( $js_url ) {
				// Add the JS URL to the response.
				wp_enqueue_script(
					'postfinancecheckout-remote-checkout-js',
					$js_url,
					array(
						'jquery',
					),
					1,
					true
				);
			}
		} catch ( Exception $e ) {
			WooCommerce_PostFinanceCheckout::instance()->log( $e->getMessage(), WC_Log_Levels::DEBUG );
		}
	}

	/**
	 * Processes the payment for an order.
	 *
	 * This method is a wrapper around the process_payment_transaction method of the payment gateway.
	 * This method is called by the woocommerce_rest_checkout_process_payment_with_context hook when a transaction need to be
	 * processed, trigged by the WooCommerce Blocks checkout block. In here, we call the process transaction function that
	 * we have in the payment gateway, adding the parameters that are needed, like the space_id.
	 *
	 * @param PaymentContext $context The payment context containing the necessary information to process the payment.
	 * @param PaymentResult  $result A reference to the PaymentResult object to store the results of the payment processing.
	 *
	 * @return void
	 *
	 * @see woocommerce_rest_checkout_process_payment_with_context hook.
	 */
	public static function process_payment( PaymentContext $context, PaymentResult &$result ) {
		if ( $result->status ) {
			return;
		}
		$payment_method_object = $context->get_payment_method_instance();

		if ( ! $payment_method_object instanceof \WC_PostFinanceCheckout_Gateway ) {
			return;
		}

		$payment_method_object->validate_fields();

		// We call here the payment processor from our gateway.
		$space_id = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_ID );
		$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();

		$transaction_service->api_client->addDefaultHeader(
			WC_PostFinanceCheckout_Helper::POSTFINANCECHECKOUT_CHECKOUT_VERSION,
			WC_PostFinanceCheckout_Helper::POSTFINANCECHECKOUT_CHECKOUT_TYPE_BLOCKS
		);

		$transaction = $transaction_service->get_transaction_from_session();
		$transaction_id = $transaction->getId();
		[$gateway_result, $transaction] = $payment_method_object->process_payment_transaction( $context->order, $transaction_id, $space_id, true, $transaction_service );


		$integration_mode = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_INTEGRATION );
		$redirect_url = $gateway_result['redirect'];
		if ( WC_PostFinanceCheckout_Integration::POSTFINANCECHECKOUT_PAYMENTPAGE === $integration_mode ) {
			$transaction_service->update_transaction_info( $transaction, $context->order );
			$redirect_url = $transaction_service->get_payment_page_url( get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_ID ), $transaction->getId() );
		}

		// Build the result object, which will be sent back to the browser's JS that invoked the process in the checkout.
		$result->set_status( isset( $gateway_result['result'] ) && 'success' == $gateway_result['result'] ? 'success' : 'failure' );
		$result->set_payment_details( array_merge( $result->payment_details, $gateway_result ) );
		$result->set_redirect_url( $redirect_url );
	}
}
