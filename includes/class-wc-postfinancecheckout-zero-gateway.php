<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_PostFinanceCheckout_Zero_Gateway extends WC_Payment_Gateway {

	public const ZERO_PAYMENT_CONF_ID = 999999;

	public function __construct() {
		$this->id = 'postfinancecheckout_zero';
		$this->method_title = __( 'No Payment Required', 'woo-postfinancecheckout' );
		$this->method_description = __( 'Used when order total is zero.', 'woo-postfinancecheckout' );
		$this->title = __( 'No Payment Required', 'woo-postfinancecheckout' );
		$this->enabled = 'yes';
		$this->has_fields = false;

		$this->init_form_fields();
		$this->init_settings();

		$this->enabled = $this->get_option( 'enabled' );
		$this->title = $this->get_option( 'title' );

		add_action(
		  'woocommerce_update_options_payment_gateways_' . $this->id,
		  array( $this, 'process_admin_options' )
		);

		add_filter(
		  'woocommerce_available_payment_gateways',
		  array( $this, 'hide_gateways_for_zero_order_total' )
		);
	}

	public function init_form_fields() {
		$this->form_fields = array(
		  'enabled' => array(
			'title'   => __( 'Enable/Disable', 'woo-postfinancecheckout' ),
			'type'    => 'checkbox',
			'label'   => __( 'Enable No Payment Required Gateway', 'woo-postfinancecheckout' ),
			'default' => 'yes',
		  ),
		  'title' => array(
			'title'       => __( 'Title', 'woo-postfinancecheckout' ),
			'type'        => 'text',
			'description' => __( 'This controls the title seen by the customer during checkout.', 'woo-postfinancecheckout' ),
			'default'     => __( 'No Payment Required', 'woo-postfinancecheckout' ),
			'desc_tip'    => true,
		  ),
		);
	}

	public function is_available() {
		return ( WC()->cart && WC()->cart->total == 0 ) && $this->enabled === 'yes';
	}

	public function get_payment_configuration_id() {
		// This can be a fake or static ID just to satisfy the interface.
		return self::ZERO_PAYMENT_CONF_ID;
	}

	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		$order->payment_complete();
		$order->add_order_note( __( 'Order completed automatically – no payment needed.', 'woo-postfinancecheckout' ) );

		return array(
		  'result'   => 'success',
		  'redirect' => $this->get_return_url( $order ),
		);
	}

	public function hide_gateways_for_zero_order_total( $available_gateways ) {
		if ( is_admin() ) {
			return $available_gateways;
		}

		$has_subscription = self::cart_has_subscription();
		if ( WC()->cart && WC()->cart->total == 0 && !$has_subscription ) {
			foreach ( $available_gateways as $gateway_id => $gateway ) {
				if ( $gateway_id !== 'postfinancecheckout_zero' ) {
					unset( $available_gateways[ $gateway_id ] );
				}
			}
		}

		return $available_gateways;
	}

	public static function cart_has_subscription() {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return false;
		}

		$subscription_classes = [
		  'WC_Subscription_Product',
		  'WC_Product_Subscription',
		  'WC_Product_Subscription_Variation',
		];

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'];
			foreach ( $subscription_classes as $class ) {
				if ( $product instanceof $class ) {
					return true;
				}
			}
		}

		return false;
	}
}
