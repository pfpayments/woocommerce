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

if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

/**
 * This class implements the PostFinance Checkout subscription gateways
 */
class WC_PostFinanceCheckout_Subscription_Gateway {

	/**
	 * Gateway.
	 *
	 * @var WC_PostFinanceCheckout_Gateway $gateway Gateway.
	 */
	private $gateway;

	/**
	 * Constructor
	 *
	 * @param WC_PostFinanceCheckout_Gateway $gateway Gateway.
	 */
	public function __construct( WC_PostFinanceCheckout_Gateway $gateway ) {
		$this->gateway = $gateway;
		add_action(
			'woocommerce_scheduled_subscription_payment_' . $gateway->id,
			array(
				$this,
				'process_scheduled_subscription_payment',
			),
			10,
			2
		);
		// Handle Admin Token Setting.
		add_filter(
			'woocommerce_subscription_payment_meta',
			array(
				$this,
				'add_subscription_payment_meta',
			),
			10,
			2
		);
		add_action(
			'woocommerce_subscription_validate_payment_meta',
			array(
				$this,
				'validate_subscription_payment_meta',
			),
			10,
			2
		);
		// Handle customer payment method change.
		add_filter(
			'woocommerce_subscriptions_update_payment_via_pay_shortcode',
			array(
				$this,
				'update_payment_method',
			),
			10,
			3
		);
		// Handle Pay Failed Renewal.
		add_action(
			'woocommerce_subscription_failing_payment_method_updated_' . $gateway->id,
			array(
				$this,
				'process_subscription_failing_payment_method_updated',
			),
			10,
			2
		);
	}

	/**
	 * Process Scheduled Subscription Payment.
	 *
	 * @param mixed    $amount_to_charge Amount to charge.
	 * @param WC_Order $order Order.
	 * @return void
	 */
	public function process_scheduled_subscription_payment( $amount_to_charge, WC_Order $order ) {
		try {
			$token_data = $this->get_token_data_from_order($order);

			$token_space_id = $token_data['_postfinancecheckout_subscription_space_id'];
			$token_id = $token_data['_postfinancecheckout_subscription_token_id'];

			$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();

			$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
			if ( $transaction_info->get_id() > 0 ) {
				$existing_transaction = $transaction_service->get_transaction( $transaction_info->get_space_id(), $transaction_info->get_id() );
				if ( $existing_transaction->getState() != \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING ) {
					return;
				}
				$transaction_service->update_transaction_by_renewal_order( $order, $amount_to_charge, $token_id, $existing_transaction );
				$transaction_service->process_transaction_without_user_interaction( $existing_transaction->getLinkedSpaceId(), $existing_transaction->getId() );
			} else {
				$create_transaction = $transaction_service->create_transaction_by_renewal_order( $order, $amount_to_charge, $token_id );
				$transaction_service->update_transaction_info( $create_transaction, $order );
				$transaction_service->process_transaction_without_user_interaction( $token_space_id, $create_transaction->getId() );
			}

			$order->add_meta_data( '_postfinancecheckout_gateway_id', $this->gateway->id, true );
			$order->delete_meta_data( '_wc_postfinancecheckout_restocked' );
		} catch ( Exception $e ) {
			$order->update_status( 'failed', $e->getMessage(), 'woo-postfinancecheckout' );
			WooCommerce_PostFinanceCheckout::instance()->log( $e->getMessage() . "\n" . $e->getTraceAsString() );
			return;
		}
	}

	/**
	 * Add subscription payment meta.
	 *
	 * @param mixed $payment_meta Payment meta.
	 * @param mixed $subscription Subscription.
	 * @return mixed
	 */
	public function add_subscription_payment_meta( $payment_meta, $subscription ) {
		$payment_meta[ $this->gateway->id ] = array(
			'post_meta' => array(
				'_postfinancecheckout_subscription_space_id' => array(
					'value' => get_post_meta( $subscription->get_id(), '_postfinancecheckout_subscription_space_id', true ),
					'label' => 'PostFinance Checkout Space Id',
				),
				'_postfinancecheckout_subscription_token_id' => array(
					'value' => get_post_meta( $subscription->get_id(), '_postfinancecheckout_subscription_token_id', true ),
					'label' => 'PostFinance Checkout Token Id',
				),
			),
		);
		return $payment_meta;
	}

	/**
	 * Validate subscription payment meta.
	 *
	 * @param mixed $payment_method_id Payment method id.
	 * @param mixed $payment_meta Payment meta.
	 * @return void
	 * @throws Exception Exception.
	 */
	public function validate_subscription_payment_meta( $payment_method_id, $payment_meta ) {
		if ( $this->gateway->id === $payment_method_id ) {
			if ( ! isset( $payment_meta['post_meta']['_postfinancecheckout_subscription_space_id']['value'] ) || empty( $payment_meta['post_meta']['_postfinancecheckout_subscription_space_id']['value'] ) ) {
				throw new Exception( __( 'The PostFinance Checkout Space Id value is required.', 'woo-postfinancecheckout' ) );
			} elseif ( get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_ID ) != $payment_meta['post_meta']['_postfinancecheckout_subscription_space_id']['value'] ) {
				throw new Exception( __( 'The PostFinance Checkout Space Id needs to be in the same space as configured in the main configuration.', 'woo-postfinancecheckout' ) );
			}
			if ( ! isset( $payment_meta['post_meta']['_postfinancecheckout_subscription_token_id']['value'] ) || empty( $payment_meta['post_meta']['_postfinancecheckout_subscription_token_id']['value'] ) ) {
				throw new Exception( __( 'The PostFinance Checkout Token Id value is required.', 'woo-postfinancecheckout' ) );
			}
		}
	}

	/**
	 * Update payment method.
	 *
	 * @param mixed $update Update.
	 * @param mixed $new_payment_method New payment method.
	 * @param mixed $subscription Subscription.
	 * @return false|mixed
	 */
	public function update_payment_method( $update, $new_payment_method, $subscription ) {
		if ( $this->gateway->id == $new_payment_method ) {
			$update = false;
			add_filter(
				'wc_postfinancecheckout_gateway_result_send_json',
				array(
					$this,
					'gateway_result_send_json',
				),
				10,
				2
			);
		}
		return $update;
	}

	/**
	 * Process subscription failing payment method updated.
	 *
	 * @param mixed $subscription Suncsription.
	 * @param mixed $renewal_order Renewal order.
	 * @return void
	 */
	public function process_subscription_failing_payment_method_updated( $subscription, $renewal_order ) {
		update_post_meta( $subscription->get_id(), '_postfinancecheckout_subscription_space_id', $renewal_order->get_meta( '_postfinancecheckout_subscription_space_id', true ) );
		update_post_meta( $subscription->get_id(), '_postfinancecheckout_subscription_token_id', $renewal_order->get_meta( '_postfinancecheckout_subscription_token_id', true ) );
	}

	/**
	 * Get token data from original order
	 *
	 * @param WC_Order $order Order.
	 * @return array
	 */
	private function get_token_data_from_order($order)
	{
		$order_id = $order->get_id();
		$token_data = [];
		$token_data['_postfinancecheckout_subscription_space_id'] = get_post_meta( $order_id, '_postfinancecheckout_subscription_space_id', true );
		$token_data['_postfinancecheckout_subscription_token_id'] = get_post_meta( $order_id, '_postfinancecheckout_subscription_token_id', true );

		if( ! isset($token_data['_postfinancecheckout_subscription_space_id']) || isset($token_data['_postfinancecheckout_subscription_token_id']) ) {

			$subscriptions = wcs_get_subscriptions_for_renewal_order( $order_id );
			// In theory, each of the array elements should contain the same token and space data
			$subscription_object = array_pop($subscriptions);
			$subscription_meta = $subscription_object->get_meta_data();
			$token_data = [];
			$subscription_keys = [
				'_postfinancecheckout_subscription_space_id',
				'_postfinancecheckout_subscription_token_id'
			];

			foreach( $subscription_meta as $meta_data ) {
				$contained_data = $meta_data->get_data();
				if ( in_array($contained_data['key'], $subscription_keys ) ) {
					$token_data[$contained_data['key']] = $contained_data['value'];
				}
			}
		}

		if( ! isset($token_data['_postfinancecheckout_subscription_space_id']) ) {
			$order->update_status( 'failed', __( 'No Space Id is found.', 'woo-postfinancecheckout' ) );
			throw new Exception('Missing space id details');
		}

		if( ! isset($token_data['_postfinancecheckout_subscription_token_id']) ) {
			$order->update_status( 'failed', __( 'No Token Id is found.', 'woo-postfinancecheckout' ) );
			throw new Exception('Missing token id');
		}

		if ( get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_ID ) != $token_data['_postfinancecheckout_subscription_space_id'] ) {
			$order->update_status( 'failed', __( 'The token space and the configured space are not equal.', 'woo-postfinancecheckout' ) );
			throw new Exception('Token space does not match configured space');
		}

		return $token_data;
	}

	/**
	 * Gateway result send json.
	 *
	 * @param mixed $send Send.
	 * @param mixed $order_id Order id.
	 * @return false
	 */
	public function gateway_result_send_json( $send, $order_id ) {

		add_filter(
			'woocommerce_subscriptions_process_payment_for_change_method_via_pay_shortcode',
			array(
				$this,
				'store_gateway_result_in_globals',
			),
			-10,
			2
		);
		add_filter(
			'wp_redirect',
			array(
				$this,
				'create_json_response',
			),
			-10,
			2
		);
		return false;
	}

	/**
	 * Store gateway result in globals.
	 *
	 * @param mixed $result Result.
	 * @param mixed $subscription Subscription.
	 * @return array|mixed
	 */
	public function store_gateway_result_in_globals( $result, $subscription ) {
		if ( isset( $result['postfinancecheckout'] ) ) {
			$GLOBALS['_wc_postfinancecheckout_subscription_gateway_result'] = $result;
			return array(
				'result' => $result['result'],
				'redirect' => 'wc_postfinancecheckout_subscription_redirect',
			);
		}
		return $result;
	}

	/**
	 * Create json response.
	 *
	 * @param mixed $location Location.
	 * @param mixed $status Status.
	 * @return mixed|void
	 */
	public function create_json_response( $location, $status ) {
		$location = basename($location);
		if ( 'wc_postfinancecheckout_subscription_redirect' == $location && isset( $GLOBALS['_wc_postfinancecheckout_subscription_gateway_result'] ) ) {
			wp_send_json( $GLOBALS['_wc_postfinancecheckout_subscription_gateway_result'] );
			exit;
		}
		return $location;
	}
}