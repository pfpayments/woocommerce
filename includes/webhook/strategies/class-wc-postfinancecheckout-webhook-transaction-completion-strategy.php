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
 * Class WC_PostFinanceCheckout_Webhook_Transaction_Completion_Strategy
 *
 * Handles strategy for processing transaction completion-related webhook requests.
 * This class extends the base webhook strategy to manage webhook requests specifically
 * dealing with transaction completions. It focuses on updating order states based on the transaction completion details
 * retrieved from the webhook data.
 */
class WC_PostFinanceCheckout_Webhook_Transaction_Completion_Strategy extends WC_PostFinanceCheckout_Webhook_Strategy_Base {

	/**
	 * Match function.
	 *
	 * @inheritDoc
	 * @param string $webhook_entity_id The webhook entity id.
	 */
	public function match( string $webhook_entity_id ) {
		return WC_PostFinanceCheckout_Service_Webhook::POSTFINANCECHECKOUT_TRANSACTION_COMPLETION == $webhook_entity_id;
	}

	/**
	 * Load the entity
	 *
	 * @inheritDoc
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request.
	 */
	protected function load_entity( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$transaction_invoice_service = new \PostFinanceCheckout\Sdk\Service\TransactionCompletionService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $transaction_invoice_service->read( $request->get_space_id(), $request->get_entity_id() );
	}

	/**
	 * Get the order ID.
	 *
	 * @inheritDoc
	 * @param object $object The webhook request.
	 */
	protected function get_order_id( $object ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionCompletion $object */
		return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction(
			$object->getLineItemVersion()->getTransaction()->getLinkedSpaceId(),
			$object->getLineItemVersion()->getTransaction()->getId()
		)->get_order_id();
	}

	/**
	 * Processes the incoming webhook request pertaining to transaction completions.
	 *
	 * This method retrieves the transaction completion details from the API and updates the associated
	 * WooCommerce order based on the state of the completion.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request object.
	 * @return void
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion */
		$completion = $this->load_entity( $request );
		$order = $this->get_order( $completion );
		if ( false != $order && $order->get_id() ) {
			$this->process_order_related_inner( $order, $completion, $request );
		}
	}

	/**
	 * Additional processing on the order based on the state of the transaction completion.
	 *
	 * @param WC_Order $order The WooCommerce order linked to the completion.
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion The transaction completion object.
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request.
	 * @return void
	 */
	protected function process_order_related_inner( WC_Order $order, \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion, WC_PostFinanceCheckout_Webhook_Request $request ) {
		switch ( $request->get_state() ) {
			case \PostFinanceCheckout\Sdk\Model\TransactionCompletionState::FAILED:
				$this->failed( $order, $completion );
				break;
			case \PostFinanceCheckout\Sdk\Model\TransactionCompletionState::SUCCESSFUL:
				$this->success( $order, $completion );
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	/**
	 * Handles successful transaction completion.
	 *
	 * @param WC_Order $order The associated WooCommerce order.
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion The transaction completion data.
	 * @return void
	 */
	protected function success( WC_Order $order, \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion ) {
		$completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_by_completion( $completion->getLinkedSpaceId(), $completion->getId() );
		if ( ! $completion_job->get_id() ) {
			// We have no completion job with this id -> the server could not store the id of the completion after sending the request. (e.g. connection issue or crash)
			// We only have on running completion which was not yet processed successfully and use it as it should be the one the webhook is for.
			$completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_running_completion_for_transaction(
				$completion->getLinkedSpaceId(),
				$completion->getLinkedTransaction()
			);
			if ( ! $completion_job->get_id() ) {
				// completion not initiated in shop backend ignore.
				return;
			}
			$completion_job->set_completion_id( $completion->getId() );
		}
		$completion_job->set_state( WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_DONE );

		if ( $completion_job->get_restock() ) {
			$this->restock_non_completed_items( (array) $completion_job->get_items(), $order );
		}
		$this->adapt_order_items( (array) $completion_job->get_items(), $order );
		$completion_job->save();
	}

	/**
	 * Restock non completed items.
	 *
	 * @param array $completed_items completed items.
	 * @param WC_Order $order order.
	 * @return void
	 */
	private function restock_non_completed_items( array $completed_items, WC_Order $order ) {
		if ( 'yes' === get_option( 'woocommerce_manage_stock' ) && $order && count( $order->get_items() ) > 0 ) {
			foreach ( $order->get_items() as $item_id => $item ) {
				$product = $item->get_product();
				if ( $item->is_type( 'line_item' ) && $product && $product->managing_stock() ) {

					$changed_qty = $item->get_quantity();
					if ( isset( $completed_items[ $item_id ] ) ) {
						$changed_qty = $changed_qty - $completed_items[ $item_id ]['qty'];
					}
					if ( $changed_qty > 0 ) {
						$item_name = esc_attr( $product->get_formatted_name() );
						$new_stock = wc_update_product_stock( $product, $changed_qty, 'increase' );
						$old_stock = $new_stock - $changed_qty;

						$order->add_order_note(
							/* translators: %1$s, %2$s, %3$s are replaced with "string" */
							sprintf( __( '%1$s stock increased from %2$s to %3$s.', 'woo-postfinancecheckout' ), $item_name, $old_stock, $new_stock )
						);
						do_action( 'wc_postfinancecheckout_restock_not_completed_item', $product->get_id(), $old_stock, $new_stock, $order, $product );
					}
				}
			}
		}
	}

	/**
	 * Adapt order items.
	 *
	 * @param array $completed_items completed items.
	 * @param WC_Order $order order.
	 * @return void
	 */
	private function adapt_order_items( array $completed_items, WC_Order $order ) {
		foreach ( $order->get_items() as $item_id => $item ) {
			if ( ! isset( $completed_items[ $item_id ] ) ||
					 $completed_items[ $item_id ]['completion_total'] + array_sum( $completed_items[ $item_id ]['completion_tax'] ) == 0 ) {
				$order_item = $order->get_item( $item_id );
				$order_item->delete( true );
				continue;
			}
			$old_total = $item->get_total();
			$subtotal = $item->get_subtotal();
			$ratio = $old_total / $completed_items[ $item_id ]['completion_total'];
			if ( 0 != $ratio ) {
				$subtotal = $subtotal / $ratio;
			}
			$old_taxes = $item->get_taxes();
			$new_taxes = array(
				'total' => array(),
				'subtotal' => array(),
			);
			foreach ( array_keys( $old_taxes['total'] ) as $id ) {
				$old_tax = $old_taxes['total'][ $id ];
				$subtax = $old_taxes['subtotal'][ $id ];
				if ( 0 != $completed_items[ $item_id ]['completion_tax'][ $id ] ) {
					$ration = $old_tax / $completed_items[ $item_id ]['completion_tax'][ $id ];
					if ( 0 != $ration ) {
						$subtax = $subtax / $ratio;
					}
				}
				$new_taxes['total'][ $id ] = wc_format_decimal( $completed_items[ $item_id ]['completion_tax'][ $id ], wc_get_price_decimals() );
				$new_taxes['subtotal'][ $id ] = wc_format_decimal( $subtax, wc_get_price_decimals() );
			}

			$item->set_props(
				array(
					'quantity' => $completed_items[ $item_id ]['qty'],
					'total' => wc_format_decimal( $completed_items[ $item_id ]['completion_total'], wc_get_price_decimals() ),
					'subtotal' => wc_format_decimal( $subtotal, wc_get_price_decimals() ),
					'taxes' => $new_taxes,
				)
			);
			$item->save();
		}
		foreach ( $order->get_fees() as $fee_id => $fee ) {
			if ( ! isset( $completed_items[ $fee_id ] ) ||
					 $completed_items[ $fee_id ]['completion_total'] + array_sum( $completed_items[ $fee_id ]['completion_tax'] ) == 0 ) {
				$order_fee = $order->get_item( $fee_id );
				$order_fee->delete();
				continue;
			}
			$fee->set_props(
				array(
					'total' => $completed_items[ $fee_id ]['completion_total'],
					'taxes' => array(
						'total' => $completed_items[ $fee_id ]['completion_tax'],
					),
				)
			);
			$fee->save();
		}
		foreach ( $order->get_shipping_methods() as $shipping_id => $shipping ) {

			if ( ! isset( $completed_items[ $shipping_id ] ) ||
					 $completed_items[ $shipping_id ]['completion_total'] + array_sum( $completed_items[ $shipping_id ]['completion_tax'] ) == 0 ) {
				$order_shipping = $order->get_item( $shipping_id );
				$order_shipping->delete();
				continue;
			}
			$shipping->set_props(
				array(
					'total' => $completed_items[ $shipping_id ]['completion_total'],
					'taxes' => array(
						'total' => $completed_items[ $shipping_id ]['completion_tax'],
					),
				)
			);

			$shipping->save();
		}
		$order->save();
		$order = WC_Order_Factory::get_order( $order->get_id() );
		$order->update_taxes();
		$order->calculate_totals( false );
	}

	/**
	 * Handles failed transaction completion.
	 *
	 * @param WC_Order $order The associated WooCommerce order.
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion The transaction completion data that failed.
	 * @return void
	 */
	protected function failed( WC_Order $order, \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion ) {
		$completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_by_completion( $completion->getLinkedSpaceId(), $completion->getId() );
		if ( ! $completion_job->get_id() ) {
			$completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_running_completion_for_transaction(
				$completion->getLinkedSpaceId(),
				$completion->getLinkedTransaction()
			);
			if ( ! $completion_job->get_id() ) {
				return;
			}
			$completion_job->set_completion_id( $completion->getId() );
		}
		if ( $completion->getFailureReason() != null ) {
			$completion_job->set_failure_reason( $completion->getFailureReason()->getDescription() );
		}
		$completion_job->set_state( WC_PostFinanceCheckout_Entity_Completion_Job::POSTFINANCECHECKOUT_STATE_DONE );
		$completion_job->save();
	}
}
