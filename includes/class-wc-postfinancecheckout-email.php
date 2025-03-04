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
 * Class WC_PostFinanceCheckout_Email.
 *
 * @class WC_PostFinanceCheckout_Email
 */
class WC_PostFinanceCheckout_Email {

	/**
	 * Register email hooks.
	 */
	public static function init() {
		add_filter(
			'woocommerce_email_enabled_new_order',
			array(
				__CLASS__,
				'send_email_for_order',
			),
			10,
			2
		);
		add_filter(
			'woocommerce_email_enabled_cancelled_order',
			array(
				__CLASS__,
				'send_email_for_order',
			),
			10,
			2
		);
		add_filter(
			'woocommerce_email_enabled_failed_order',
			array(
				__CLASS__,
				'send_email_for_order',
			),
			10,
			2
		);
		add_filter(
			'woocommerce_email_enabled_customer_on_hold_order',
			array(
				__CLASS__,
				'send_email_for_order',
			),
			10,
			2
		);
		add_filter(
			'woocommerce_email_enabled_customer_processing_order',
			array(
				__CLASS__,
				'send_email_for_order',
			),
			10,
			2
		);
		add_filter(
			'woocommerce_email_enabled_customer_completed_order',
			array(
				__CLASS__,
				'send_email_for_order',
			),
			10,
			2
		);
		add_filter(
			'woocommerce_email_enabled_customer_partially_refunded_order',
			array(
				__CLASS__,
				'send_email_for_order',
			),
			10,
			2
		);
		add_filter(
			'woocommerce_email_enabled_customer_refunded_order',
			array(
				__CLASS__,
				'send_email_for_order',
			),
			10,
			2
		);

		add_filter(
			'woocommerce_before_resend_order_emails',
			array(
				__CLASS__,
				'before_resend_email',
			),
			10,
			1
		);
		add_filter(
			'woocommerce_after_resend_order_emails',
			array(
				__CLASS__,
				'after_resend_email',
			),
			10,
			2
		);

		add_filter(
			'woocommerce_germanized_order_email_customer_confirmation_sent',
			array(
				__CLASS__,
				'germanized_send_order_confirmation',
			),
			10,
			2
		);

		add_filter(
			'woocommerce_germanized_order_email_admin_confirmation_sent',
			array(
				__CLASS__,
				'germanized_send_order_confirmation',
			),
			10,
			2
		);

		add_filter( 'woocommerce_email_actions', array( __CLASS__, 'add_email_actions' ), 10, 1 );
		add_filter( 'woocommerce_email_classes', array( __CLASS__, 'add_email_classes' ), 100, 1 );
	}

	/**
	 * Sends emails.
	 *
	 * @param mixed $enabled enabled.
	 * @param mixed $order order.
	 * @return false|mixed
	 */
	public static function send_email_for_order( $enabled, $order ) {
		if ( ! ( $order instanceof WC_Order ) ) {
			return $enabled;
		}
		if ( isset( $GLOBALS['postfinancecheckout_resend_email'] ) && $GLOBALS['postfinancecheckout_resend_email'] ) {
			return $enabled;
		}
		$gateway = wc_get_payment_gateway_by_order( $order );
		if ( $gateway instanceof WC_PostFinanceCheckout_Gateway ) {
			$send = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SHOP_EMAIL, 'yes' );
			if ( 'yes' !== $send ) {
				return false;
			}
		}
		return $enabled;
	}

	/**
	 * Sets resend email.
	 *
	 * @param mixed $order order.
	 * @return void
	 */
	public static function before_resend_email( $order ) { //phpcs:ignore
		$GLOBALS['postfinancecheckout_resend_email'] = true;
	}

	/**
	 * After email sent.
	 *
	 * @param mixed $order order.
	 * @param mixed $email email.
	 * @return void
	 */
	public static function after_resend_email( $order, $email ) { //phpcs:ignore
		unset( $GLOBALS['postfinancecheckout_resend_email'] );
	}

	/**
	 * Add actions to email.
	 *
	 * @param mixed $actions email actions.
	 * @return mixed
	 */
	public static function add_email_actions( $actions ) {

		$to_add = array(
			'woocommerce_order_status_postfi-redirected_to_processing',
			'woocommerce_order_status_postfi-redirected_to_completed',
			'woocommerce_order_status_postfi-redirected_to_on-hold',
			'woocommerce_order_status_postfi-redirected_to_postfinancecheckout-waiting',
			'woocommerce_order_status_postfi-redirected_to_postfinancecheckout-manual',
			'woocommerce_order_status_postfi-manual_to_cancelled',
			'woocommerce_order_status_postfi-waiting_to_cancelled',
			'woocommerce_order_status_postfi-manual_to_processing',
			'woocommerce_order_status_postfi-waiting_to_processing',
		);

		if ( class_exists( 'woocommerce_wpml' ) ) {
			global $woocommerce_wpml; //phpcs:ignore
			if ( ! is_null( $woocommerce_wpml ) ) { //phpcs:ignore
				// Add hooks for WPML, for email translations.
				$notifications_all = array(
					'woocommerce_order_status_postfi-redirected_to_processing_notification',
					'woocommerce_order_status_postfi-redirected_to_completed_notification',
					'woocommerce_order_status_postfi-redirected_to_on-hold_notification',
					'woocommerce_order_status_postfi-redirected_to_postfinancecheckout-waiting_notification',
					'woocommerce_order_status_postfi-redirected_to_postfinancecheckout-manual_notification',
				);
				$notifications_customer = array(
					'woocommerce_order_status_postfi-manual_to_processing_notification',
					'woocommerce_order_status_postfi-waiting_to_processing_notification',
					'woocommerce_order_status_on-hold_to_processing_notification',
					'woocommerce_order_status_postfi-manual_to_cancelled_notification',
					'woocommerce_order_status_postfi-waiting_to_cancelled_notifcation',
				);

				$wpml_instance = $woocommerce_wpml; //phpcs:ignore
				$email_handler = $wpml_instance->emails;
				foreach ( $notifications_all as $new_action ) {
					add_action(
						$new_action,
						array(
							$email_handler,
							'refresh_email_lang',
						),
						9
					);
					add_action(
						$new_action,
						array(
							$email_handler,
							'new_order_admin_email',
						),
						9
					);
				}
				foreach ( $notifications_customer as $new_action ) {
					add_action(
						$new_action,
						array(
							$email_handler,
							'refresh_email_lang',
						),
						9
					);
				}
			}
		}

		if ( class_exists( 'PLLWC' ) ) {
			add_filter(
				'pllwc_order_email_actions',
				function ( $actions ) {
					$all = array(
						'woocommerce_order_status_postfi-redirected_to_processing',
						'woocommerce_order_status_postfi-redirected_to_completed',
						'woocommerce_order_status_postfi-redirected_to_on-hold',
						'woocommerce_order_status_postfi-redirected_to_postfinancecheckout-waiting',
						'woocommerce_order_status_postfi-redirected_to_postfinancecheckout-manual',
						'woocommerce_order_status_postfi-manual_to_cancelled',
						'woocommerce_order_status_postfi-waiting_to_cancelled',
						'woocommerce_order_status_postfi-manual_to_processing',
						'woocommerce_order_status_postfi-waiting_to_processing',
						'woocommerce_order_status_postfi-redirected_to_processing_notification',
						'woocommerce_order_status_postfi-redirected_to_completed_notification',
						'woocommerce_order_status_postfi-redirected_to_on-hold_notification',
						'woocommerce_order_status_postfi-redirected_to_postfinancecheckout-waiting_notification',
						'woocommerce_order_status_postfi-redirected_to_postfinancecheckout-manual_notification',
					);

					$customers = array(
						'woocommerce_order_status_postfi-manual_to_processing_notification',
						'woocommerce_order_status_postfi-waiting_to_processing_notification',
						'woocommerce_order_status_on-hold_to_processing_notification',
						'woocommerce_order_status_postfi-manual_to_cancelled_notification',
						'woocommerce_order_status_postfi-waiting_to_cancelled_notifcation',
					);

					$actions = array_merge( $actions, $all, $customers );
					return $actions;
				}
			);
		}

		$actions = array_merge( $actions, $to_add );
		return $actions;
	}

	/**
	 * Check Germanized pay email trigger.
	 *
	 * @param mixed $order_id order id.
	 * @param mixed $order order.
	 * @return void
	 */
	public static function check_germanized_pay_email_trigger( $order_id, $order = false ) {
		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}
		$gateway = wc_get_payment_gateway_by_order( $order );
		if ( $gateway instanceof WC_PostFinanceCheckout_Gateway ) {

			$send = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SHOP_EMAIL, 'yes' );
			if ( 'yes' !== $send ) {
				return;
			}
			$mails = WC()->mailer()->get_emails();
			if ( isset( $mails['WC_GZD_Email_Customer_Paid_For_Order'] ) ) {
				$mails['WC_GZD_Email_Customer_Paid_For_Order']->trigger( $order_id );
			}
		}
	}

	/**
	 * Add email classes.
	 *
	 * @param mixed $emails emails.
	 * @return mixed
	 */
	public static function add_email_classes( $emails ) {

		// Germanized has a special email flow.
		if ( isset( $emails['WC_GZD_Email_Customer_Paid_For_Order'] ) ) {
			$email_object = $emails['WC_GZD_Email_Customer_Paid_For_Order'];
			add_action( 'woocommerce_order_status_postfi-redirected_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_postfi-manual_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_postfi-waiting_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_on-hold_to_processing_notification', array( __CLASS__, 'check_germanized_pay_email_trigger' ), 10, 2 );
		}
		if ( function_exists( 'wc_gzd_send_instant_order_confirmation' ) && wc_gzd_send_instant_order_confirmation() ) {
			return $emails;
		}

		foreach ( $emails as $key => $email_object ) {
			switch ( $key ) {
				case 'WC_Email_New_Order':
					add_action( 'woocommerce_order_status_postfi-redirected_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfi-redirected_to_completed_notification', array( $email_object, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfi-redirected_to_on-hold_notification', array( $email_object, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfi-redirected_to_postfinancecheckout-waiting_notification', array( $email_object, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfi-redirected_to_postfinancecheckout-manual_notification', array( $email_object, 'trigger' ), 10, 2 );

					break;

				case 'WC_Email_Cancelled_Order':
					add_action( 'woocommerce_order_status_postfi-manual_to_cancelled_notification', array( $email_object, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfi-waiting_to_cancelled_notification', array( $email_object, 'trigger' ), 10, 2 );
					break;

				case 'WC_Email_Customer_On_Hold_Order':
					add_action( 'woocommerce_order_status_postfi-redirected_to_on-hold_notification', array( $email_object, 'trigger' ), 10, 2 );
					break;

				case 'WC_Email_Customer_Processing_Order':
					add_action( 'woocommerce_order_status_postfi-redirected_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfi-manual_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfi-waiting_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
					break;

				case 'WC_Email_Customer_Completed_Order':
					// Order complete are always send independent of the source status.
					break;

				case 'WC_Email_Failed_Order':
				case 'WC_Email_Customer_Refunded_Order':
				case 'WC_Email_Customer_Invoice':
					// Do nothing for now.
					break;
			}
		}

		return $emails;
	}

	/**
	 * Germanized send order confirmation.
	 *
	 * @param mixed $email_sent email sent.
	 * @param mixed $order_id order id.
	 * @return bool|mixed
	 */
	public static function germanized_send_order_confirmation( $email_sent, $order_id ) {
		$order = WC_Order_Factory::get_order( $order_id );
		if ( ! ( $order instanceof WC_Order ) ) {
			return $email_sent;
		}
		$gateway = wc_get_payment_gateway_by_order( $order );
		if ( $gateway instanceof WC_PostFinanceCheckout_Gateway ) {
			$send = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SHOP_EMAIL, 'yes' );
			if ( 'yes' !== $send ) {
				return true;
			}
		}
		return $email_sent;
	}
}

WC_PostFinanceCheckout_Email::init();
