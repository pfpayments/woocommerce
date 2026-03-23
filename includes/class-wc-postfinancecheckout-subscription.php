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
 * Class WC_PostFinanceCheckout_Subscription.
 * This class integrates subscriptions
 *
 * @class WC_PostFinanceCheckout_Subscription
 */
final class WC_PostFinanceCheckout_Subscription {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_PostFinanceCheckout_Subscription
	 */
	protected static $_instance = null;

	/**
	 * Main WooCommerce PostFinanceCheckout Instance.
	 *
	 * Ensures only one instance of WooCommerce PostFinanceCheckout is loaded or can be loaded.
	 *
	 * @return WC_PostFinanceCheckout_Subscription - Main instance.
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * WC_PostFinanceCheckout_Subscription Constructor.
	 */
	public function __construct() {
	}

	public function boot() {
		add_filter(
			'wc_postfinancecheckout_enhance_gateway',
			array(
				$this,
				'enhance_gateway',
			)
		);
		add_filter(
			'wc_postfinancecheckout_modify_sesion_create_transaction',
			array(
				$this,
				'update_transaction_from_session',
			)
		);
		add_filter(
			'wc_postfinancecheckout_modify_session_pending_transaction',
			array(
				$this,
				'update_transaction_from_session',
			)
		);
		add_filter(
			'wc_postfinancecheckout_modify_order_create_transaction',
			array(
				$this,
				'update_transaction_from_order',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_modify_order_pending_transaction',
			array(
				$this,
				'update_transaction_from_order',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_modify_confirm_transaction',
			array(
				$this,
				'update_transaction_from_order',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_modify_line_item_order',
			array(
				$this,
				'modify_line_item_method_change',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_modify_total_to_check_order',
			array(
				$this,
				'modify_order_total_method_change',
			),
			12,
			2
		);
		add_action(
			'wc_postfinancecheckout_authorized',
			array(
				$this,
				'update_subscription_data',
			),
			10,
			2
		);
		add_action(
			'wc_postfinancecheckout_fulfill',
			array(
				$this,
				'fulfill_in_progress',
			),
			10,
			2
		);
		add_filter(
			'woocommerce_valid_order_statuses_for_payment',
			array(
				$this,
				'add_valid_order_statuses_for_subscription_completion',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_update_transaction_info',
			array(
				$this,
				'update_transaction_info',
			),
			10,
			3
		);
		add_filter(
			'wc_postfinancecheckout_confirmed_status',
			array(
				$this,
				'ignore_status_update_for_subscription',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_authorized_status',
			array(
				$this,
				'ignore_status_update_for_subscription',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_completed_status',
			array(
				$this,
				'ignore_status_update_for_subscription',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_decline_status',
			array(
				$this,
				'ignore_status_update_for_subscription',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_failed_status',
			array(
				$this,
				'ignore_status_update_for_subscription',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_voided_status',
			array(
				$this,
				'ignore_status_update_for_subscription',
			),
			10,
			2
		);
		add_action(
			'wcs_after_renewal_setup_cart_subscriptions',
			array(
				$this,
				'set_transaction_ids_into_session',
			),
			10,
			2
		);
		add_filter(
			'woocommerce_subscriptions_is_failed_renewal_order',
			array(
				$this,
				'check_failed_renewal_order',
			),
			10,
			3
		);
		// Handle order to cart creation for subscriptions.
		add_filter(
			'wcs_before_renewal_setup_cart_subscriptions',
			array(
				$this,
				'before_renewal_setup_cart',
			),
			10,
			2
		);
		add_filter(
			'wcs_before_parent_order_setup_cart',
			array(
				$this,
				'before_renewal_setup_cart',
			),
			10,
			2
		);
		add_filter(
			'wcs_after_renewal_setup_cart_subscriptions',
			array(
				$this,
				'after_renewal_setup_cart',
			),
			10,
			2
		);
		add_filter(
			'wcs_after_parent_order_setup_cart',
			array(
				$this,
				'after_renewal_setup_cart',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_is_method_available',
			array(
				$this,
				'method_available_for_cart_renewal',
			),
			10,
			1
		);
		add_filter(
			'wc_postfinancecheckout_success_url',
			array(
				$this,
				'update_success_url',
			),
			10,
			2
		);
		add_filter(
			'wc_postfinancecheckout_checkout_failure_url',
			array(
				$this,
				'update_failure_url',
			),
			10,
			2
		);
	}

	/**
	 * Enhance gateway.
	 *
	 * @param WC_PostFinanceCheckout_Gateway $gateway Gateway.
	 * @return WC_PostFinanceCheckout_Gateway
	 */
	public function enhance_gateway( WC_PostFinanceCheckout_Gateway $gateway ) {
		$gateway->supports = array_merge(
			$gateway->supports,
			array(
				'subscriptions',
				'multiple_subscriptions',
				'subscription_cancellation',
				'subscription_suspension',
				'subscription_reactivation',
				'subscription_amount_changes',
				'subscription_date_changes',
				'subscription_payment_method_change',
				'subscription_payment_method_change_customer',
				'subscription_payment_method_change_admin',
				'subscription_payment_method_delayed_change',
			)
		);
		// Create Subscription gateway and register hooks/filters.
		new WC_PostFinanceCheckout_Subscription_Gateway( $gateway );

		return $gateway;
	}

	/**
	 * Update transaction from session.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\AbstractTransactionPending $transaction Transaction.
	 * @return \PostFinanceCheckout\Sdk\Model\AbstractTransactionPending
	 */
	public function update_transaction_from_session( \PostFinanceCheckout\Sdk\Model\AbstractTransactionPending $transaction ) {
		if ( WC_Subscriptions_Cart::cart_contains_subscription() || wcs_cart_contains_failed_renewal_order_payment() ) {
			$transaction->setTokenizationMode( \PostFinanceCheckout\Sdk\Model\TokenizationMode::FORCE_CREATION_WITH_ONE_CLICK_PAYMENT );
		}
		return $transaction;
	}

	/**
	 * Update transaction from order.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\AbstractTransactionPending $transaction Transaction.
	 * @param mixed                                                       $order Order.
	 * @return \PostFinanceCheckout\Sdk\Model\AbstractTransactionPending
	 */
	public function update_transaction_from_order( \PostFinanceCheckout\Sdk\Model\AbstractTransactionPending $transaction, $order ) {
		if ( wcs_order_contains_subscription( $order, array( 'parent', 'resubscribe', 'switch', 'renewal' ) ) ) {
			$transaction->setTokenizationMode( \PostFinanceCheckout\Sdk\Model\TokenizationMode::FORCE_CREATION_WITH_ONE_CLICK_PAYMENT );
		}
		if ( WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment ) {
			$transaction->setTokenizationMode( \PostFinanceCheckout\Sdk\Model\TokenizationMode::FORCE_CREATION_WITH_ONE_CLICK_PAYMENT );
		}
		return $transaction;
	}

	/**
	 * Modify line item method change.
	 *
	 * @param array $line_items Line items.
	 * @param mixed $order Order.
	 * @return array|\PostFinanceCheckout\Sdk\Model\LineItemCreate[] LineItemCreate.
	 */
	public function modify_line_item_method_change( array $line_items, $order ) {
		if ( WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment ) {
			// It is a method change for a subscription -> zero transaction.
			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			$line_item->setAmountIncludingTax( 0 );
			$line_item->setQuantity( 1 );
			$line_item->setName( __( 'Payment Method Change', 'woo-postfinancecheckout' ) );
			$line_item->setShippingRequired( false );
			$line_item->setSku( 'paymentmethodchange' );
			$line_item->setTaxes( array() );
			$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::PRODUCT );
			$line_item->setUniqueId( WC_PostFinanceCheckout_Unique_Id::get_uuid() );
			return array( $line_item );
		}
		return $line_items;
	}

	/**
	 * Modify order total method change.
	 *
	 * @param mixed $total Total.
	 * @param mixed $order Order.
	 * @return int|mixed
	 */
	public function modify_order_total_method_change( $total, $order ) {
		if ( WC_Subscriptions_Change_Payment_Gateway::$is_request_to_change_payment && wcs_is_subscription( $order ) ) {
			$total = 0;
		}
		return $total;
	}

	/**
	 * Update subscription data.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction Transaction.
	 * @param mixed                                        $order Order.
	 * @return void
	 */
	public function update_subscription_data( \PostFinanceCheckout\Sdk\Model\Transaction $transaction, $order ) {
		if ( wcs_order_contains_subscription( $order, array( 'renewal', 'parent', 'resubscribe', 'switch' ) ) ) {
			$order->add_meta_data( '_postfinancecheckout_subscription_space_id', $transaction->getLinkedSpaceId(), true );
			$order->add_meta_data( '_postfinancecheckout_subscription_token_id', $transaction->getToken()->getId(), true );
			$order->save();
			$subscriptions = wcs_get_subscriptions_for_order( $order, array( 'parent', 'switch' ) );
			foreach ( $subscriptions as $subscription ) {
				$subscription->add_meta_data( '_postfinancecheckout_subscription_space_id', $transaction->getLinkedSpaceId(), true );
				$subscription->add_meta_data( '_postfinancecheckout_subscription_token_id', $transaction->getToken()->getId(), true );
				$subscription->save();
			}
		}
		if ( wcs_is_subscription( $order->get_id() ) ) {
			$order->add_meta_data( '_postfinancecheckout_subscription_space_id', $transaction->getLinkedSpaceId(), true );
			$order->add_meta_data( '_postfinancecheckout_subscription_token_id', $transaction->getToken()->getId(), true );
			$order->save();
		}
		if ( wcs_is_subscription( $order->get_id() ) && $this->is_transaction_method_change( $transaction ) ) {
			$gateway_id = $order->get_meta( '_postfinancecheckout_gateway_id', true, 'edit' );
			$meta_data = array(
				'post_meta' => array(
					'_postfinancecheckout_subscription_space_id' => array(
						'value' => $transaction->getLinkedSpaceId(),
						'label' => 'PostFinance Checkout Space Id',
					),
					'_postfinancecheckout_subscription_token_id' => array(
						'value' => $transaction->getToken()->getId(),
						'label' => 'PostFinance Checkout Token Id',
					),
				),
			);

			WC_Subscriptions_Change_Payment_Gateway::update_payment_method( $order, $gateway_id, $meta_data );
			if ( WC_Subscriptions_Change_Payment_Gateway::will_subscription_update_all_payment_methods( $order ) ) {
				WC_Subscriptions_Change_Payment_Gateway::update_all_payment_methods_from_subscription( $order, $gateway_id );
			}
		}
	}

	/**
	 * Is transaction method change.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction Transaction.
	 * @return bool
	 */
	private function is_transaction_method_change( \PostFinanceCheckout\Sdk\Model\Transaction $transaction ) {
		$line_items = $transaction->getLineItems();
		if ( count( $line_items ) != 1 ) {
			return false;
		}
		$line_item = current( $line_items );
		/* @var  \PostFinanceCheckout\Sdk\Model\LineItem $line_item */
		if ( $line_item->getSku() == 'paymentmethodchange' ) {
			return true;
		}
		return false;
	}

	/**
	 * Fulfill in progress.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction Transaction.
	 * @param mixed                                        $order Order.
	 * @return void
	 */
	public function fulfill_in_progress( \PostFinanceCheckout\Sdk\Model\Transaction $transaction, $order ) {
		if ( wcs_order_contains_subscription( $order, array( 'parent', 'resubscribe', 'switch', 'renewal' ) ) ) {
			$GLOBALS['_wc_postfinancecheckout_subscription_fulfill'] = true;
		}
	}

	/**
	 * Add valid order statuses for subscription completion.
	 *
	 * @param mixed $statuses Statuses.
	 * @param mixed $order Order.
	 * @return mixed
	 */
	public function add_valid_order_statuses_for_subscription_completion( $statuses, $order = null ) {
		if ( isset( $GLOBALS['_wc_postfinancecheckout_subscription_fulfill'] ) ) {
			$statuses[] = 'postfi-waiting';
			$statuses[] = 'postfi-manual';
		}
		return $statuses;
	}

	/**
	 * Update transaction info.
	 *
	 * @param WC_PostFinanceCheckout_Entity_Transaction_Info $info Info.
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction     $transaction Transaction.
	 * @param WC_Order                                         $order Oder.
	 * @return WC_PostFinanceCheckout_Entity_Transaction_Info
	 */
	public function update_transaction_info( WC_PostFinanceCheckout_Entity_Transaction_Info $info, \PostFinanceCheckout\Sdk\Model\Transaction $transaction, WC_Order $order ) {
		if ( wcs_is_subscription( $order->get_id() ) ) {
			if ( in_array( $transaction->getState(), array( \PostFinanceCheckout\Sdk\Model\TransactionState::FAILED, \PostFinanceCheckout\Sdk\Model\TransactionState::VOIDED, \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL, \PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE ) ) ) {
				$info->set_order_id( null );
			}
		}
		return $info;
	}

	/**
	 * Ignore status update for subscription.
	 * Do not change the status for subscriptions, this is done by modifying the corresponding orders
	 *
	 * @param mixed    $status Status.
	 * @param WC_Order $order Order.
	 * @return mixed
	 */
	public function ignore_status_update_for_subscription( $status, WC_Order $order ) {
		if ( wcs_is_subscription( $order->get_id() ) ) {
			$status = $order->get_status();
		}
		return $status;
	}

	/**
	 * Set transaction ids into session.
	 *
	 * @param mixed    $subscription Subscription.
	 * @param WC_Order $order Order.
	 * @return void
	 */
	public function set_transaction_ids_into_session( $subscription, WC_Order $order ) {
		$session_handler = WC()->session;
		$existing_transaction = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
		if ( $existing_transaction->get_id() !== null && $existing_transaction->get_state() == \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING ) {
			$session_handler->set( 'postfinancecheckout_transaction_id', $existing_transaction->get_transaction_id() );
			$session_handler->set( 'postfinancecheckout_space_id', $existing_transaction->get_space_id() );
		}
	}

	/**
	 * Check failed renewal order.
	 *
	 * @param mixed $is_failed_renewal_order Is failed renewal order.
	 * @param mixed $order_id Order id.
	 * @param mixed $orders_old_status Orders old status.
	 * @return mixed
	 */
	public function check_failed_renewal_order( $is_failed_renewal_order, $order_id, $orders_old_status ) {
		$order = WC_Order_Factory::get_order( $order_id );
		if ( $order ) {
			$gateway = wc_get_payment_gateway_by_order( $order );
			if ( $gateway instanceof WC_PostFinanceCheckout_Gateway ) {
				if ( 'failed' == $orders_old_status ) {
					update_post_meta( $order_id, '_postfinancecheckout_subscription_failed_renewal', true );
					return $is_failed_renewal_order;
				}
				return get_post_meta( $order_id, '_postfinancecheckout_subscription_failed_renewal', true );
			}
		}
		return $is_failed_renewal_order;
	}

	/**
	 * Before renewal setup.
	 * Create a checkout instead of using the order to pay. Here wet our method as available and ensure we do not create transaction on such order.
	 *
	 * @param mixed $subscriptions Subscriptions.
	 * @param mixed $order Order.
	 * @return void
	 */
	public function before_renewal_setup_cart( $subscriptions, $order ) {
		$GLOBALS['_wc_postfinancecheckout_renewal_cart_setup'] = true;
	}

	/**
	 * After renewal setup.
	 *
	 * @param mixed $subscriptions Subscriptions.
	 * @param mixed $order Order.
	 * @return void
	 */
	public function after_renewal_setup_cart( $subscriptions, $order ) {
		$GLOBALS['_wc_postfinancecheckout_renewal_cart_setup'] = false;
	}

	/**
	 * Method available for cart renewal.
	 *
	 * @param mixed $available Available.
	 * @return bool|mixed
	 */
	public function method_available_for_cart_renewal( $available ) {
		if ( isset( $GLOBALS['_wc_postfinancecheckout_renewal_cart_setup'] ) && $GLOBALS['_wc_postfinancecheckout_renewal_cart_setup'] ) {
			return true;
		}
		return $available;
	}

	/**
	 * Update success url.
	 *
	 * @param mixed $url Url.
	 * @param mixed $order Order.
	 * @return mixed
	 */
	public function update_success_url( $url, $order ) {
		if ( wcs_is_subscription( $order ) ) {
			return $order->get_view_order_url();
		}
		return $url;
	}

	/**
	 * Update failure url.
	 *
	 * @param mixed $url Url.
	 * @param mixed $order Order.
	 * @return mixed
	 */
	public function update_failure_url( $url, $order ) {
		if ( wcs_is_subscription( $order ) ) {
			wc_clear_notices();
			$msg = WC()->session->get( 'postfinancecheckout_failure_message', null );
			if ( ! empty( $msg ) ) {
				WooCommerce_PostFinanceCheckout::instance()->add_notice( (string) $msg, 'error' );
				WC()->session->set( 'postfinancecheckout_failure_message', null );
			}
			return $order->get_view_order_url();
		}
		return $url;
	}
}


