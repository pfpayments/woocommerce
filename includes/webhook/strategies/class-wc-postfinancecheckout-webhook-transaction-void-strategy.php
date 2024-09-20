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
 * Class WC_PostFinanceCheckout_Webhook_Transaction_Void_Strategy
 *
 * Handles strategy for processing transaction void webhook requests.
 * This class extends the base webhook strategy to specifically manage webhook requests
 * that deal with transaction voids. Transaction voids are crucial for reverting transactions
 * that have been initiated but not yet completed, ensuring accurate financial records and operations.
 */
class WC_PostFinanceCheckout_Webhook_Transaction_Void_Strategy extends WC_PostFinanceCheckout_Webhook_Strategy_Base {

	/**
	 * Match function
	 *
	 * @inheritDoc
	 *
	 * @param string $webhook_entity_id The webhook entity.
	 */
	public function match( string $webhook_entity_id ) {
		return WC_PostFinanceCheckout_Service_Webhook::POSTFINANCECHECKOUT_TRANSACTION_VOID == $webhook_entity_id;
	}

	/**
	 * Loads the entity
	 *
	 * @inheritDoc
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request.
	 */
	protected function load_entity( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$void_service = new \PostFinanceCheckout\Sdk\Service\TransactionVoidService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $void_service->read( $request->get_space_id(), $request->get_entity_id() );
	}

	/**
	 * Get order id
	 *
	 * @inheritDoc
	 *
	 * @param object $object The order object.
	 */
	protected function get_order_id( $object ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionVoid $object */
		return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction(
			$object->getTransaction()->getLinkedSpaceId(),
			$object->getTransaction()->getId()
		)->get_order_id();
	}

	/**
	 * Processes the incoming webhook request related to transaction voids.
	 *
	 * This method checks if the corresponding order exists and if so, it further processes the order
	 * based on the transaction void state obtained from the webhook request.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request.
	 * @return void
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionVoid $void_transaction */
		$void = $this->load_entity( $request );
		$order = $this->get_order( $void );
		if ( false != $order && $order->get_id() ) {
			$this->process_order_related_inner( $order, $void, $request );
		}
	}

	/**
	 * Processes additional order-related operations based on the transaction void's state.
	 *
	 * @param WC_Order $order The WooCommerce order associated with the void request.
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionVoid $void The transaction void object.
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request object.
	 * @return void
	 */
	protected function process_order_related_inner( WC_Order $order, \PostFinanceCheckout\Sdk\Model\TransactionVoid $void, WC_PostFinanceCheckout_Webhook_Request $request ) {

		switch ( $request->get_state() ) {
			case \PostFinanceCheckout\Sdk\Model\TransactionVoidState::FAILED:
				$this->failed( $order, $void );
				break;
			case \PostFinanceCheckout\Sdk\Model\TransactionVoidState::SUCCESSFUL:
				$this->success( $order, $void );
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	/**
	 * Successfully processes a transaction void.
	 *
	 * @param WC_Order $order The order to process.
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionVoid $void The transaction void.
	 * @return void
	 */
	protected function success( WC_Order $order, \PostFinanceCheckout\Sdk\Model\TransactionVoid $void ) {
		$void_job = WC_PostFinanceCheckout_Entity_Void_Job::load_by_void( $void->getLinkedSpaceId(), $void->getId() );
		if ( ! $void_job->get_id() ) {
			// We have no void job with this id -> the server could not store the id of the void after sending the request. (e.g. connection issue or crash)
			// We only have on running void which was not yet processed successfully and use it as it should be the one the webhook is for.
			$void_job = WC_PostFinanceCheckout_Entity_Void_Job::load_running_void_for_transaction( $void->getLinkedSpaceId(), $void->getLinkedTransaction() );
			if ( ! $void_job->get_id() ) {
				// void not initiated in shop backend ignore.
				return;
			}
			$void_job->set_void_id( $void->getId() );
		}
		$void_job->set_state( WC_PostFinanceCheckout_Entity_Void_Job::POSTFINANCECHECKOUT_STATE_DONE );

		if ( $void_job->get_restock() ) {
			WC_PostFinanceCheckout_Helper::instance()->maybe_restock_items_for_order( $order );
		}
		$void_job->save();
	}

	/**
	 * Handles a failed transaction void.
	 *
	 * @param WC_Order $order The order linked to the failed void.
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionVoid $void The transaction void.
	 * @return void
	 */
	protected function failed( WC_Order $order, \PostFinanceCheckout\Sdk\Model\TransactionVoid $void ) {
		$void_job = WC_PostFinanceCheckout_Entity_Void_Job::load_by_void( $void->getLinkedSpaceId(), $void->getId() );

		if ( ! $void_job->get_id() ) {
			// We have no void job with this id -> the server could not store the id of the void after sending the request. (e.g. connection issue or crash)
			// We only have on running void which was not yet processed successfully and use it as it should be the one the webhook is for.
			$void_job = WC_PostFinanceCheckout_Entity_Void_Job::load_running_void_for_transaction( $void->getLinkedSpaceId(), $void->getLinkedTransaction() );
			if ( ! $void_job->get_id() ) {
				// void not initiated in shop backend ignore.
				return;
			}
			$void_job->set_void_id( $void->getId() );
		}
		if ( $void_job->getFailureReason() != null ) {
			$void_job->set_failure_reason( $void->getFailureReason()->getDescription() );
		}
		$void_job->set_state( WC_PostFinanceCheckout_Entity_Void_Job::POSTFINANCECHECKOUT_STATE_DONE );
		$void_job->save();
	}
}
