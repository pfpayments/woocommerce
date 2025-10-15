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

use PostFinanceCheckout\Sdk\Model\TransactionState;

defined( 'ABSPATH' ) || exit;

/**
 * Class WC_PostFinanceCheckout_Email.
 *
 * @class WC_PostFinanceCheckout_Email
 */
class WC_PostFinanceCheckout_Email {

	/**
	 * Allow on hold emails being sent when germanized plugin is installed.
	 */
	private static $allow_on_hold_emails = false;

	/**
	 * Register email hooks.
	 */
	public static function init() {
		add_action(
			'postfinancecheckout_transaction_authorized_send_email',
			array(
				__CLASS__,
		  		'send_on_hold_email_when_authorized'
			),
			10,
			1
		);
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
			'woocommerce_germanized_send_instant_order_confirmation',
			array(
				__CLASS__,
				'gzd_block_send_instant_order_confirmation',
			),
			10,
			2
		);
		add_filter(
			'woocommerce_gzd_disable_on_hold_email',
			array(
				__CLASS__,
				'gzd_allow_on_hold_email',
			),
			10,
			1
		);

		add_filter( 'woocommerce_email_actions', array( __CLASS__, 'add_email_actions' ), 10, 1 );
		add_filter( 'woocommerce_email_classes', array( __CLASS__, 'add_email_classes' ), 100, 1 );
        add_filter(
            'woocommerce_email_enabled_customer_on_hold_order',
            array( __CLASS__, 'disable_pending_payment_email' ),
            10,
            2
        );
	}

	/**
	 * @param $order_id
	 * @return void
	 */
	public static function send_on_hold_email_when_authorized( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		self::send_on_hold_email( $order, '_postfinancecheckout_on_hold_email_sent', true );
	}

	/**
	 * Trigger the on-hold email when an order enters the status mapped to "authorized".
	 *
	 * @param int      $order_id order id.
	 * @param WC_Order $order order instance.
	 * @return void
	 */
	public static function maybe_send_on_hold_email_for_manual_status( $order_id, $order = null ) {
		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$authorized_status = self::get_transaction_mapped_status( 'authorized' );
		if ( $authorized_status && $order->get_status() !== $authorized_status ) {
			return;
		}

		$gateway = wc_get_payment_gateway_by_order( $order );
		if ( ! ( $gateway instanceof WC_PostFinanceCheckout_Gateway ) ) {
			return;
		}

		$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
		if ( ! $transaction_info || TransactionState::AUTHORIZED !== $transaction_info->get_state() ) {
			return;
		}

		self::send_on_hold_email( $order );
	}

	/**
	 * Send the customer on-hold email (and optionally the admin new order email).
	 *
	 * @param WC_Order $order order instance.
	 * @param string   $meta_key optional meta key to prevent duplicate sends.
	 * @param bool     $send_new_order_email whether the admin new order email should be sent.
	 * @return void
	 */
	private static function send_on_hold_email( WC_Order $order, $meta_key = '', $send_new_order_email = false ) {
		if ( $meta_key && get_post_meta( $order->get_id(), $meta_key, true ) ) {
			return;
		}

		self::$allow_on_hold_emails = true;
		$emails = WC()->mailer()->get_emails();
		if ( isset( $emails['WC_Email_Customer_On_Hold_Order'] ) ) {
			$emails['WC_Email_Customer_On_Hold_Order']->trigger( $order->get_id() );
			if ( $meta_key ) {
				update_post_meta( $order->get_id(), $meta_key, true );
			}
		}

		if ( $send_new_order_email && isset( $emails['WC_Email_New_Order'] ) ) {
			$emails['WC_Email_New_Order']->trigger( $order->get_id() );
		}
		self::$allow_on_hold_emails = false;
	}

	/**
	 * Resolve the WooCommerce order status slug mapped to a transaction status.
	 *
	 * @param string $transaction_status transaction status key.
	 * @return string|null
	 */
	private static function get_transaction_mapped_status( string $transaction_status ) {
		$status = apply_filters( 'postfinancecheckout_wc_status_for_transaction', $transaction_status );
		if ( empty( $status ) ) {
			$defaults = apply_filters( 'postfinancecheckout_default_order_status_mappings', array() );
			$key = strtolower( $transaction_status );
			$status = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
		}

		if ( empty( $status ) ) {
			return null;
		}

		if ( strpos( $status, 'wc-' ) === 0 ) {
			$status = substr( $status, 3 );
		}

		return $status ? $status : null;
	}

	/**
	 * Add an action to a list if it is not already present.
	 *
	 * @param array  $actions existing actions.
	 * @param string $action action to append.
	 * @return void
	 */
	private static function add_unique_action( array &$actions, $action ) {
		if ( empty( $action ) || in_array( $action, $actions, true ) ) {
			return;
		}

		$actions[] = $action;
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
				return;
			}

			if ( ! self::is_authorized_on_hold_order( $order ) ) {
				return;
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

	$authorized_status = self::get_transaction_mapped_status( 'authorized' );
	$fulfill_status = self::get_transaction_mapped_status( 'fulfill' );

	if ( $authorized_status && $fulfill_status ) {
		self::add_unique_action( $to_add, 'woocommerce_order_status_' . $fulfill_status . '_to_' . $authorized_status );
		self::add_unique_action( $to_add, 'woocommerce_order_status_' . $authorized_status . '_to_' . $fulfill_status );
	}

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

				if ( $authorized_status && $fulfill_status ) {
					self::add_unique_action( $notifications_customer, 'woocommerce_order_status_' . $fulfill_status . '_to_' . $authorized_status . '_notification' );
					self::add_unique_action( $notifications_customer, 'woocommerce_order_status_' . $authorized_status . '_to_' . $fulfill_status . '_notification' );
				}

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
			$manual_status = $authorized_status;
			$processing_status = $fulfill_status;
			add_filter(
				'pllwc_order_email_actions',
				function ( $actions ) use ( $manual_status, $processing_status ) {
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

					if ( $manual_status && $processing_status ) {
						WC_PostFinanceCheckout_Email::add_unique_action( $all, 'woocommerce_order_status_' . $processing_status . '_to_' . $manual_status );
						WC_PostFinanceCheckout_Email::add_unique_action( $all, 'woocommerce_order_status_' . $processing_status . '_to_' . $manual_status . '_notification' );
						WC_PostFinanceCheckout_Email::add_unique_action( $all, 'woocommerce_order_status_' . $manual_status . '_to_' . $processing_status );
						WC_PostFinanceCheckout_Email::add_unique_action( $all, 'woocommerce_order_status_' . $manual_status . '_to_' . $processing_status . '_notification' );

						WC_PostFinanceCheckout_Email::add_unique_action( $customers, 'woocommerce_order_status_' . $processing_status . '_to_' . $manual_status . '_notification' );
						WC_PostFinanceCheckout_Email::add_unique_action( $customers, 'woocommerce_order_status_' . $manual_status . '_to_' . $processing_status . '_notification' );
					}

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

			if ( ! self::is_authorized_on_hold_order( $order ) ) {
				return;
			}

			$mails = WC()->mailer()->get_emails();
			if ( isset( $mails['WC_Email_Customer_Processing_Order'] ) ) {
				$mails['WC_Email_Customer_Processing_Order']->trigger( $order_id );
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

		$authorized_status = self::get_transaction_mapped_status( 'authorized' );
		$fulfill_status = self::get_transaction_mapped_status( 'fulfill' );

		// Germanized has a special email flow.
		if ( isset( $emails['WC_GZD_Email_Customer_Paid_For_Order'] ) ) {
			$email_object = $emails['WC_GZD_Email_Customer_Paid_For_Order'];
			add_action( 'woocommerce_order_status_postfi-redirected_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_postfi-manual_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
			add_action( 'woocommerce_order_status_postfi-waiting_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
			if ( $authorized_status && $fulfill_status ) {
				add_action( 'woocommerce_order_status_' . $authorized_status . '_to_' . $fulfill_status . '_notification', array( $email_object, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_status_' . $authorized_status . '_to_' . $fulfill_status . '_notification', array( __CLASS__, 'check_germanized_pay_email_trigger' ), 10, 2 );
			}
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
				if ( $authorized_status && $fulfill_status ) {
					add_action( 'woocommerce_order_status_' . $fulfill_status . '_to_' . $authorized_status . '_notification', array( __CLASS__, 'maybe_send_on_hold_email_for_manual_status' ), 10, 2 );
				}
				break;

			case 'WC_Email_Customer_Processing_Order':
				add_action( 'woocommerce_order_status_postfi-redirected_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_status_postfi-manual_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
				add_action( 'woocommerce_order_status_postfi-waiting_to_processing_notification', array( $email_object, 'trigger' ), 10, 2 );
				if ( $authorized_status && $fulfill_status ) {
					add_action( 'woocommerce_order_status_' . $authorized_status . '_to_' . $fulfill_status . '_notification', array( $email_object, 'trigger' ), 10, 2 );
				}
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
	 * Block confirmation email being sent when germanized plugin is installed.
	 *
	 * @param mixed $email_sent email sent.
	 * @param mixed $order order.
	 * @return bool
	 */
	public static function gzd_block_send_instant_order_confirmation ( $email_sent, $order ) {
		if ( ! ( $order instanceof WC_Order ) ) {
			return $email_sent;
		}
		$gateway = wc_get_payment_gateway_by_order( $order );
		if ( $gateway instanceof WC_PostFinanceCheckout_Gateway ) {
			return false;
		}
		return $email_sent;
	}

	/**
	 * Toggle to allow "On Hold" emails being sent when germanized plugin is installed.
	 *
	 * @param mixed $disable if email sending is disabled.
	 * @return bool
	 */
	public static function gzd_allow_on_hold_email( $disable ) {
		return self::$allow_on_hold_emails ? false : $disable;
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

		if ( ! self::is_authorized_on_hold_order( $order ) ) {
			return;
		}
		return $email_sent;
	}

	/**
	 * @param WC_Order $order
	 * @return bool
	 */
	private static function is_authorized_on_hold_order( WC_Order $order ) {
		if ( $order->get_status() !== 'on-hold' ) {
			return true;
		}

		$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
		if ( ! $transaction_info || ( $transaction_info->get_state() !== TransactionState::AUTHORIZED ) ) {
			return false;
		}

		return true;
	}

    /**
     * This prevents WooCommerce from sending the "customer on hold" email
     * when the order was placed using any PostFinanceCheckout payment method
     * and the merchant has disabled this behavior in the plugin settings.
     *
     * @param bool     $enabled Whether the email is enabled.
     * @param WC_Order $order   WooCommerce order object.
     *
     * @return bool
     */
    public static function disable_pending_payment_email( $enabled, $order ) {
        if ( ! $enabled || ! is_a( $order, 'WC_Order' ) ) {
            return $enabled;
        }

        $disable = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_DISABLE_PENDING_EMAIL, 'no' );
        $gateway = wc_get_payment_gateway_by_order( $order );

        if ( $gateway instanceof WC_PostFinanceCheckout_Gateway && $disable === 'yes' ) {
            return false;
        }

        return $enabled;
    }
}

WC_PostFinanceCheckout_Email::init();
