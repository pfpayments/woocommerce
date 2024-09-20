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
 * WC PostFinanceCheckout Admin Order Completion class
 */
class WC_PostFinanceCheckout_Admin_Order_Completion {

	/**
	 * Init.
	 *
	 * @return void
	 */
	public static function init() {
		add_action(
			'woocommerce_order_item_add_line_buttons',
			array(
				__CLASS__,
				'render_execute_completion_button',
			)
		);

		add_action(
			'wp_ajax_woocommerce_postfinancecheckout_execute_completion',
			array(
				__CLASS__,
				'execute_completion',
			)
		);

		add_action(
			'postfinancecheckout_five_minutes_cron',
			array(
				__CLASS__,
				'update_completions',
			)
		);

		add_action(
			'postfinancecheckout_update_running_jobs',
			array(
				__CLASS__,
				'update_for_order',
			)
		);
	}

	/**
	 * Render Execute Completion Button.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 */
	public static function render_execute_completion_button( WC_Order $order ) {
		$gateway = wc_get_payment_gateway_by_order( $order );
		if ( $gateway instanceof WC_PostFinanceCheckout_Gateway ) {
			$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
			if ( TransactionState::AUTHORIZED === $transaction_info->get_state() ) {
				echo '<button type="button" class="button postfinancecheckout-completion-button action-postfinancecheckout-completion-cancel" style="display:none">' .
					esc_html__( 'Cancel', 'woo-postfinancecheckout' ) . '</button>';
				echo '<button type="button" class="button button-primary postfinancecheckout-completion-button action-postfinancecheckout-completion-execute" style="display:none">' .
					esc_html__( 'Execute Completion', 'woo-postfinancecheckout' ) . '</button>';
				echo '<label for="completion_restock_not_completed_items" style="display:none">' .
					esc_html__( 'Restock not completed items', 'woo-postfinancecheckout' ) . '</label>';
				echo '<input type="checkbox" id="completion_restock_not_completed_items" name="restock_not_completed_items" checked="checked" style="display:none">';
				echo '<label for="refund_amount" style="display:none">' . esc_html__( 'Completion Amount', 'woo-postfinancecheckout' ) . '</label>';
			}
		}
	}

	/**
	 * Execute completion.
	 *
	 * @return void
	 * @throws Exception Exception.
	 */
	public static function execute_completion() {
		ob_start();

		check_ajax_referer( 'order-item', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) { // phpcs:ignore
			wp_die( -1 );
		}

		$order_id = isset( $_POST['order_id'] ) ? absint( sanitize_key( wp_unslash( $_POST['order_id'] ) ) ) : 0;
		$completion_amount  = isset( $_POST['completion_amount'] ) ? wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['completion_amount'] ) ), wc_get_price_decimals() ) : 0;
		// phpcs:ignore
		$line_item_qtys = isset( $_POST['line_item_qtys'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['line_item_qtys'] ) ), true ) : array();
		// phpcs:ignore
		$line_item_totals = isset( $_POST['line_item_totals'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['line_item_totals'] ) ), true ) : array();
		// phpcs:ignore
		$line_item_tax_totals = isset( $_POST['line_item_tax_totals'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['line_item_tax_totals'] ) ), true ) : array();
		$restock_not_completed_items = isset( $_POST['restock_not_completed_items'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['restock_not_completed_items'] ) );
		try {

			// Prepare line items which we are completed.
			$line_items = array();
			$item_ids   = array_unique( array_merge( array_keys( $line_item_qtys ), array_keys( $line_item_totals ) ) );
			foreach ( $item_ids as $item_id ) {
				$line_items[ $item_id ] = array(
					'qty' => 0,
					'completion_total' => 0,
					'completion_tax' => array(),
				);
			}
			foreach ( $line_item_qtys as $item_id => $qty ) {
				$line_items[ $item_id ]['qty'] = max( $qty, 0 );
			}
			foreach ( $line_item_totals as $item_id => $total ) {
				$line_items[ $item_id ]['completion_total'] = wc_format_decimal( $total );
			}
			foreach ( $line_item_tax_totals as $item_id => $tax_totals ) {
				$line_items[ $item_id ]['completion_tax'] = array_filter( array_map( 'wc_format_decimal', $tax_totals ) );
			}

			foreach ( array_keys( $line_items ) as $item_id ) {
				if ( isset( $line_items[ $item_id ]['qty'] ) && 0 === $line_items[ $item_id ]['qty'] && 0 === $line_items[ $item_id ]['completion_total'] ) {
					unset( $line_items[ $item_id ] );
				}
			}

			// Validate input first.
			$total_items_sum = 0;
			foreach ( $line_items as $item ) {

				$tax = 0;
				if ( isset( $item['completion_tax'] ) && is_array( $item['completion_tax'] ) ) {
					foreach ( $item['completion_tax'] as $rate_id => $amount ) {

						$percent = WC_Tax::get_rate_percent( $rate_id );
						$rate = rtrim( $percent, '%' );

						$tax_amount = $item['completion_total'] * $rate / 100;
						if ( wc_format_decimal( $tax_amount, wc_get_price_decimals() ) !== wc_format_decimal( $amount, wc_get_price_decimals() ) ) {
							throw new Exception( __( 'The tax rate can not be changed.', 'woo-postfinancecheckout' ) );
						}
					}
					$tax = array_sum( $item['completion_tax'] );
				}
				$total_items_sum += $item['completion_total'] + $tax;
			}

			if ( wc_format_decimal( $completion_amount, wc_get_price_decimals() ) !== wc_format_decimal( $total_items_sum, wc_get_price_decimals() ) ) {
				throw new Exception( __( 'The line item total does not correspond to the total amount to complete.', 'woo-postfinancecheckout' ) );
			}

			WC_PostFinanceCheckout_Helper::instance()->start_database_transaction();
			$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order_id );
			if ( ! $transaction_info->get_id() ) {
				throw new Exception( __( 'Could not load corresponding transaction' ) );
			}

			WC_PostFinanceCheckout_Helper::instance()->lock_by_transaction_id( $transaction_info->get_space_id(), $transaction_info->get_transaction_id() );
			$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction(
				$transaction_info->get_space_id(),
				$transaction_info->get_transaction_id()
			);

			if ( $transaction_info->get_state() !== TransactionState::AUTHORIZED ) {
				throw new Exception( __( 'The transaction is not in a state to be completed.', 'woo-postfinancecheckout' ) );
			}

			if ( WC_PostFinanceCheckout_Entity_Completion_Job::count_running_completion_for_transaction(
				$transaction_info->get_space_id(),
				$transaction_info->get_transaction_id()
			) > 0 ) {
				throw new Exception( __( 'Please wait until the existing completion is processed.', 'woo-postfinancecheckout' ) );
			}
			if ( WC_PostFinanceCheckout_Entity_Void_Job::count_running_void_for_transaction(
				$transaction_info->get_space_id(),
				$transaction_info->get_transaction_id()
			) > 0 ) {
				throw new Exception( __( 'There is a void in process. The order can not be completed.', 'woo-postfinancecheckout' ) );
			}

			$completion_job = new WC_PostFinanceCheckout_Entity_Completion_Job();
			$completion_job->set_items( $line_items );
			$completion_job->set_restock( $restock_not_completed_items );
			$completion_job->set_space_id( $transaction_info->get_space_id() );
			$completion_job->set_transaction_id( $transaction_info->get_transaction_id() );
			$completion_job->set_state( WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_CREATED );
			$completion_job->set_order_id( $order_id );
			$completion_job->set_amount( $completion_amount );
			$completion_job->save();
			$current_completion_id = $completion_job->get_id();
			WC_PostFinanceCheckout_Helper::instance()->commit_database_transaction();
		} catch ( Exception $e ) {
			WC_PostFinanceCheckout_Helper::instance()->rollback_database_transaction();
			wp_send_json_error(
				array(
					'error' => $e->getMessage(),
				)
			);
			return;
		}

		try {
			// the order id is saved for later use
			// e.g. use the order id to check if the order has a discount applied to it.
			WC()->session->set( 'postfinancecheckout_order_id', $order_id );
			self::update_line_items( $current_completion_id );
			self::send_completion( $current_completion_id );

			wp_send_json_success(
				array(
					'message' => __( 'The completion is updated automatically once the result is available.', 'woo-postfinancecheckout' ),
				)
			);
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'error' => $e->getMessage(),
				)
			);
		}
	}

	/**
	 * Update line items.
	 *
	 * @param mixed $completion_job_id completion job id.
	 * @return void
	 * @throws \PostFinanceCheckout\Sdk\ApiException Api exception.
	 * @throws \PostFinanceCheckout\Sdk\Model\ClientError Client Error.
	 */
	protected static function update_line_items( $completion_job_id ) {
		$completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_by_id( $completion_job_id );
		WC_PostFinanceCheckout_Helper::instance()->start_database_transaction();
		WC_PostFinanceCheckout_Helper::instance()->lock_by_transaction_id( $completion_job->get_space_id(), $completion_job->get_transaction_id() );
		// Reload void job.
		$completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_by_id( $completion_job_id );

		if ( $completion_job->get_state() !== WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_CREATED ) {
			// Already updated in the meantime.
			WC_PostFinanceCheckout_Helper::instance()->rollback_database_transaction();
			return;
		}
		try {
			$line_items = WC_PostFinanceCheckout_Service_Line_Item::instance()->get_items_from_backend(
				$completion_job->get_items(),
				$completion_job->get_amount(),
				WC_Order_Factory::get_order( $completion_job->get_order_id() )
			);
			WC_PostFinanceCheckout_Service_Transaction::instance()->update_line_items(
				$completion_job->get_space_id(),
				$completion_job->get_transaction_id(),
				$line_items
			);
			$completion_job->set_state( WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_ITEMS_UPDATED );
			$completion_job->save();
			WC_PostFinanceCheckout_Helper::instance()->commit_database_transaction();
		} catch ( \PostFinanceCheckout\Sdk\ApiException $e ) {
			if ( $e->getResponseObject() instanceof \PostFinanceCheckout\Sdk\Model\ClientError ) {
				$completion_job->set_state( WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_DONE );
				$completion_job->save();
				WC_PostFinanceCheckout_Helper::instance()->commit_database_transaction();
			} else {
				$completion_job->save();
				WC_PostFinanceCheckout_Helper::instance()->commit_database_transaction();
				WooCommerce_PostFinanceCheckout::instance()->log( 'Error updating line items. ' . $e->getMessage(), WC_Log_Levels::INFO );
				throw $e;
			}
		} catch ( Exception $e ) {
			$completion_job->save();
			WC_PostFinanceCheckout_Helper::instance()->commit_database_transaction();
			WooCommerce_PostFinanceCheckout::instance()->log( 'Error updating line items. ' . $e->getMessage(), WC_Log_Levels::INFO );
			throw $e;
		}
	}

	/**
	 * Send Completion.
	 *
	 * @param mixed $completion_job_id completion job id.
	 * @return void
	 *
	 * @throws \PostFinanceCheckout\Sdk\ApiException ClientError.
	 * @throws Exception Exception.
	 */
	protected static function send_completion( $completion_job_id ) {
		$completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_by_id( $completion_job_id );
		WC_PostFinanceCheckout_Helper::instance()->start_database_transaction();
		WC_PostFinanceCheckout_Helper::instance()->lock_by_transaction_id( $completion_job->get_space_id(), $completion_job->get_transaction_id() );
		// Reload void job.
		$completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_by_id( $completion_job_id );

		if ( $completion_job->get_state() !== WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_ITEMS_UPDATED ) {
			// Already sent in the meantime.
			WC_PostFinanceCheckout_Helper::instance()->rollback_database_transaction();
			return;
		}
		try {
			$completion_service = new \PostFinanceCheckout\Sdk\Service\TransactionCompletionService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );

			$completion = $completion_service->completeOnline(
				$completion_job->get_space_id(),
				$completion_job->get_transaction_id()
			);
			$completion_job->set_completion_id( $completion->getId() );
			$completion_job->set_state( WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_SENT );
			$completion_job->save();
			WC_PostFinanceCheckout_Helper::instance()->commit_database_transaction();
		} catch ( \PostFinanceCheckout\Sdk\ApiException $e ) {
			if ( $e->getResponseObject() instanceof \PostFinanceCheckout\Sdk\Model\ClientError ) {
				$completion_job->set_state( WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_DONE );
				$completion_job->save();
				WC_PostFinanceCheckout_Helper::instance()->commit_database_transaction();
			} else {
				$completion_job->save();
				WC_PostFinanceCheckout_Helper::instance()->commit_database_transaction();
				WooCommerce_PostFinanceCheckout::instance()->log( 'Error sending completion. ' . $e->getMessage(), WC_Log_Levels::INFO );
				throw $e;
			}
		} catch ( Exception $e ) {
			$completion_job->save();
			WC_PostFinanceCheckout_Helper::instance()->commit_database_transaction();
			WooCommerce_PostFinanceCheckout::instance()->log( 'Error sending completion. ' . $e->getMessage(), WC_Log_Levels::INFO );
			throw $e;
		}
	}

	/**
	 * Update for order.
	 *
	 * @param WC_Order $order Order.
	 * @return void
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	public static function update_for_order( WC_Order $order ) {

		$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
		$completion_job   = WC_PostFinanceCheckout_Entity_Completion_Job::load_running_completion_for_transaction( $transaction_info->get_space_id(), $transaction_info->get_transaction_id() );

		if ( $completion_job->get_state() === WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_CREATED ) {
			self::update_line_items( $completion_job->get_id() );
			self::send_completion( $completion_job->get_id() );
		} elseif ( $completion_job->get_state() === WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_ITEMS_UPDATED ) {
			self::send_completion( $completion_job->get_id() );
		}
	}

	/**
	 * Update completions.
	 *
	 * @return void
	 */
	public static function update_completions() {
		$to_process = WC_PostFinanceCheckout_Entity_Completion_Job::load_not_sent_job_ids();
		foreach ( $to_process as $id ) {
			try {
				self::update_line_items( $id );
				self::send_completion( $id );
			} catch ( Exception $e ) {
				/* translators: %d: id of transaction, %s: error message */
				$message = sprintf( __( 'Error updating completion job with id %1$d: %2$s', 'woo-postfinancecheckout' ), $id, $e->getMessage() );
				WooCommerce_PostFinanceCheckout::instance()->log( $message, WC_Log_Levels::ERROR );
			}
		}
	}
}
WC_PostFinanceCheckout_Admin_Order_Completion::init();
