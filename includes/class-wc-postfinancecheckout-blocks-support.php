<?php

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
		$this->settings['space_id'] = get_option( WooCommerce_PostFinanceCheckout::CK_SPACE_ID );
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
		$dependencies = [];
		$version = '1';

		wp_register_script(
			'WooCommerce_PostFinanceCheckout_blocks_support',
			WooCommerce_PostFinanceCheckout::instance()->plugin_url() . '/assets/js/frontend/blocks/build/index.js',
			$dependencies,
			$version,
			true
		);

		return [ 'WooCommerce_PostFinanceCheckout_blocks_support' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       => 'PostFinanceCheckout',
			'description' => 'PostFinanceCheckout description',
		];
	}

	/**
	 * Endpoint for getting the payment methods.
	 *
	 * This function is used for building the data that is expected by WooCommerce Blocks.
	 * The information is returned directly via a JSON response
	 *
	 * @return void
	 */
	static public function get_payment_methods() {
		$payment_gateways = WC()->payment_gateways()->payment_gateways();

		// From all the payment gateways, only use the ones provided by this module.
		$payment_plugin = array_filter($payment_gateways, fn($key) => str_contains($key, "postfinancecheckout_"), ARRAY_FILTER_USE_KEY);

		// Build the list with the keys expected by WooCommerce Blocks registering functionality.
		$payments_list = array_map(function(WC_PostFinanceCheckout_Gateway $payment_gateway) {
			return [
				'name' => $payment_gateway->id,
				'label' => $payment_gateway->get_title(),
				'ariaLabel' => $payment_gateway->get_title(),
				'description' => $payment_gateway->get_description(),
				'configuration_id' => $payment_gateway->get_payment_configuration_id(),
				'integration_mode' => (get_option( WooCommerce_PostFinanceCheckout::CK_INTEGRATION ) == WC_PostFinanceCheckout_Integration::LIGHTBOX ) ? 'lightbox' : 'iframe',
				'supports' => $payment_gateway->supports,
				'icon' => $payment_gateway->get_icon(),
			];
		}, $payment_plugin);

		// Send the list back to the requester in a JSON.
		wp_send_json(array_values($payments_list));
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
	static public function is_payment_method_available() {
		if (empty($_POST['payment_method']) || empty($_POST['configuration_id'])) {
			wp_send_json(FALSE);
		}

		$configuration_id = $_POST['configuration_id'];
		$available_payment_methods = WC_PostFinanceCheckout_Service_Transaction::instance()->get_possible_payment_methods_for_cart();
		wp_send_json(in_array($configuration_id, $available_payment_methods));
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
	static public function enqueue_portal_scripts() {
		$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
		$transaction = $transaction_service->get_transaction_from_session();

		if (( get_option( WooCommerce_PostFinanceCheckout::CK_INTEGRATION ) == WC_PostFinanceCheckout_Integration::LIGHTBOX )) {
			// Ask the portal for the lighbox's javascript file
			$js_url = $transaction_service->get_lightbox_url_for_transaction( $transaction );
		}
		else {
			// Ask the portal for the iframe's javascript file
			$js_url = $transaction_service->get_javascript_url_for_transaction( $transaction );
		}

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

	/**
 	 * Processes the payment for an order.
 	 *
 	 * This method is a wrapper around the process_payment_transaction method of the payment gateway.
	 * This method is called by the woocommerce_rest_checkout_process_payment_with_context hook when a transaction need to be
	 * processed, trigged by the WooCommerce Blocks checkout block. In here, we call the process transaction function that
	 * we have in the payment gateway, adding the parameters that are needed, like the space_id.
 	 *
 	 * @param PaymentContext $context The payment context containing the necessary information to process the payment.
 	 * @param PaymentResult $result A reference to the PaymentResult object to store the results of the payment processing.
	 *
 	 * @return void
	 *
	 * @see woocommerce_rest_checkout_process_payment_with_context hook.
	 */
	static public function process_payment( PaymentContext $context, PaymentResult &$result ) {
		if ( $result->status ) {
			return;
		}
		$payment_method_object = $context->get_payment_method_instance();

		if ( ! $payment_method_object instanceof \WC_PostFinanceCheckout_Gateway ) {
			return;
		}
		$payment_method_object->validate_fields();

		// We call here the payment processor from our gateway.
		$space_id = get_option( WooCommerce_PostFinanceCheckout::CK_SPACE_ID );
		$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
		$transaction = $transaction_service->get_transaction_from_session();
		$transaction_id = $transaction->getId();
		[$gateway_result, $transaction] = $payment_method_object->process_payment_transaction( $context->order, $transaction_id, $space_id, TRUE, $transaction_service );

		// Build the result object, which will be sent back to the browser's JS that invoked the process in the checkout.
		$result->set_status( isset( $gateway_result['result'] ) && 'success' === $gateway_result['result'] ? 'success' : 'failure' );
		$result->set_payment_details( array_merge( $result->payment_details, $gateway_result ) );
		$result->set_redirect_url( $gateway_result['redirect'] );
	}
}
