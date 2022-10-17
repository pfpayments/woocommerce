<?php
/**
 *
 * WC_PostFinanceCheckout_Webhook_Transaction_Void Class
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   wallee AG (http://www.wallee.com/)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * Webhook processor to handle transaction void state transitions.
 */
class WC_PostFinanceCheckout_Webhook_Transaction_Void extends WC_PostFinanceCheckout_Webhook_Order_Related_Abstract {


	/**
	 * Load entity.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request request.
	 * @return object|\PostFinanceCheckout\Sdk\Model\TransactionVoid
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	protected function load_entity( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$void_service = new \PostFinanceCheckout\Sdk\Service\TransactionVoidService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $void_service->read( $request->get_space_id(), $request->get_entity_id() );
	}

	/**
	 * Get order id.
	 *
	 * @param mixed $void void.
	 * @return int|string
	 */
	protected function get_order_id( $void ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionVoid $void */
		return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction( $void->getTransaction()->getLinkedSpaceId(), $void->getTransaction()->getId() )->get_order_id();
	}

	/**
	 * Get transaction id.
	 *
	 * @param mixed $void void.
	 * @return int
	 */
	protected function get_transaction_id( $void ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionVoid $void */
		return $void->getLinkedTransaction();
	}

	/**
	 * Process order related inner.
	 *
	 * @param WC_Order $order order.
	 * @param mixed    $void void.
	 * @return void
	 */
	protected function process_order_related_inner( WC_Order $order, $void ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionVoid $void */
		switch ( $void->getState() ) {
			case \PostFinanceCheckout\Sdk\Model\TransactionVoidState::FAILED:
				$this->failed( $void, $order );
				break;
			case \PostFinanceCheckout\Sdk\Model\TransactionVoidState::SUCCESSFUL:
				$this->success( $void, $order );
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	/**
	 * Success.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionVoid $void void.
	 * @param WC_Order                                         $order order.
	 * @return void
	 * @throws Exception Exception.
	 */
	protected function success( \PostFinanceCheckout\Sdk\Model\TransactionVoid $void, WC_Order $order ) {
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
		$void_job->set_state( WC_PostFinanceCheckout_Entity_Void_Job::STATE_DONE );

		if ( $void_job->get_restock() ) {
			WC_PostFinanceCheckout_Helper::instance()->maybe_restock_items_for_order( $order );
		}
		$void_job->save();
	}

	/**
	 * Failed.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionVoid $void void.
	 * @param WC_Order                                         $order order.
	 * @return void
	 * @throws Exception Exception.
	 */
	protected function failed( \PostFinanceCheckout\Sdk\Model\TransactionVoid $void, WC_Order $order ) {
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
		$void_job->set_state( WC_PostFinanceCheckout_Entity_Void_Job::STATE_DONE );
		$void_job->save();
	}
}
