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
 * Class WC_PostFinanceCheckout_Webhook_Refund_Strategy
 *
 * Handles strategy for processing refund-related webhook requests.
 * This class extends the base webhook strategy to specifically manage webhook requests
 * that deal with refund transactions. This includes updating the status of refund jobs within the system,
 * processing related order modifications, and handling state transitions for refunds.
 */
class WC_PostFinanceCheckout_Webhook_Refund_Strategy extends WC_PostFinanceCheckout_Webhook_Strategy_Base {

	/**
	 * Match function.
	 *
	 * @inheritDoc
	 * @param string $webhook_entity_id The webhook entity id.
	 */
	public function match( string $webhook_entity_id ) {
		return WC_PostFinanceCheckout_Service_Webhook::POSTFINANCECHECKOUT_REFUND == $webhook_entity_id;
	}

	/**
	 * Load entity.
	 *
	 * @inheritDoc
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request.
	 */
	protected function load_entity( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$refund_service = new \PostFinanceCheckout\Sdk\Service\RefundService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $refund_service->read( $request->get_space_id(), $request->get_entity_id() );
	}

	/**
	 * Get the order id.
	 *
	 * @inheritDoc
	 * @param \PostFinanceCheckout\Sdk\Model\Refund $object The refund object.
	 */
	protected function get_order_id( $object ) {
		return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction(
			$object->getTransaction()->getLinkedSpaceId(),
			$object->getTransaction()->getId()
		)->get_order_id();
	}

	/**
	 * Processes the incoming webhook request related to refunds.
	 *
	 * This method retrieves the refund details from the API and updates the associated order
	 * based on the refund's state.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request object.
	 * @return void
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {
		/* @var \PostFinanceCheckout\Sdk\Model\Refund $refund */
		$refund = $this->load_entity( $request );
		$order = $this->get_order( $refund );
		if ( false != $order && $order->get_id() ) {
			$this->process_order_related_inner( $order, $refund, $request );
		}
	}

	/**
	 * Performs additional order-related processing based on the refund state.
	 *
	 * @param WC_Order $order The WooCommerce order associated with the refund.
	 * @param \PostFinanceCheckout\Sdk\Model\Refund $refund The transaction refund object.
		 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request object.
	 * @return void
	 */
	protected function process_order_related_inner( WC_Order $order, \PostFinanceCheckout\Sdk\Model\Refund $refund, WC_PostFinanceCheckout_Webhook_Request $request ) {
		/* @var \PostFinanceCheckout\Sdk\Model\Refund $refund */
		switch ( $request->get_state() ) {
			case \PostFinanceCheckout\Sdk\Model\RefundState::FAILED:
				// fallback.
				$this->failed( $refund, $order );
				break;
			case \PostFinanceCheckout\Sdk\Model\RefundState::SUCCESSFUL:
				$this->refunded( $refund, $order );
				// Nothing to do.
			default:
				// Nothing to do.
				break;
		}
	}

	/**
	 * Handles actions to be performed when a refund transaction fails.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Refund $refund refund.
	 * @param WC_Order $order order.
	 * @return void
	 * @throws Exception Exception.
	 */
	protected function failed( \PostFinanceCheckout\Sdk\Model\Refund $refund, WC_Order $order ) {
		$refund_job = WC_PostFinanceCheckout_Entity_Refund_Job::load_by_external_id( $refund->getLinkedSpaceId(), $refund->getExternalId() );
		if ( $refund_job->get_id() ) {
			$refund_job->set_state( WC_PostFinanceCheckout_Entity_Refund_Job::POSTFINANCECHECKOUT_STATE_FAILURE );
			if ( $refund->getFailureReason() != null ) {
				$refund_job->set_failure_reason( $refund->getFailureReason()->getDescription() );
			}
			$refund_job->save();
			$refunds = $order->get_refunds();
			foreach ( $refunds as $wc_refund ) {
				if ( $wc_refund->get_meta( '_postfinancecheckout_refund_job_id', true ) == $refund_job->get_id() ) {
					$wc_refund->set_status( 'failed' );
					$wc_refund->save();
					break;
				}
			}
		}
	}

	/**
	 * Handles actions to be performed when a refund transaction is successful.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Refund $refund refund.
	 * @param WC_Order $order order.
	 * @return void
	 * @throws Exception Exception.
	 */
	protected function refunded( \PostFinanceCheckout\Sdk\Model\Refund $refund, WC_Order $order ) {
		$refund_job = WC_PostFinanceCheckout_Entity_Refund_Job::load_by_external_id( $refund->getLinkedSpaceId(), $refund->getExternalId() );

		if ( $refund_job->get_id() ) {
			$refund_job->set_state( WC_PostFinanceCheckout_Entity_Refund_Job::POSTFINANCECHECKOUT_STATE_SUCCESS );
			$refund_job->save();
			$refunds = $order->get_refunds();
			foreach ( $refunds as $wc_refund ) {
				if ( $wc_refund->get_meta( '_postfinancecheckout_refund_job_id', true ) == $refund_job->get_id() ) {
					$wc_refund->set_status( 'completed' );
					$wc_refund->save();
					break;
				}
			}
		}
	}
}
