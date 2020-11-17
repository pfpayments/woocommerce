<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
 *
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * Webhook processor to handle transaction void state transitions.
 */
class WC_PostFinanceCheckout_Webhook_Transaction_Void extends WC_PostFinanceCheckout_Webhook_Order_Related_Abstract {

	/**
	 *
	 * @see WC_PostFinanceCheckout_Webhook_Order_Related_Abstract::load_entity()
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionVoid
	 */
    protected function load_entity(WC_PostFinanceCheckout_Webhook_Request $request){
        $void_service = new \PostFinanceCheckout\Sdk\Service\TransactionVoidService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		return $void_service->read($request->get_space_id(), $request->get_entity_id());
	}

	protected function get_order_id($void){
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionVoid $void */
	    return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction($void->getTransaction()->getLinkedSpaceId(), $void->getTransaction()->getId())->get_order_id();
	}

	protected function get_transaction_id($void){
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionVoid $void */
		return $void->getLinkedTransaction();
	}

	protected function process_order_related_inner(WC_Order $order, $void){
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionVoid $void */
		switch ($void->getState()) {
		    case \PostFinanceCheckout\Sdk\Model\TransactionVoidState::FAILED:
				$this->failed($void, $order);
				break;
		    case \PostFinanceCheckout\Sdk\Model\TransactionVoidState::SUCCESSFUL:
				$this->success($void, $order);
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	protected function success(\PostFinanceCheckout\Sdk\Model\TransactionVoid $void, WC_Order $order){
	    $void_job = WC_PostFinanceCheckout_Entity_Void_Job::load_by_void($void->getLinkedSpaceId(), $void->getId());
		if (!$void_job->get_id()) {
			//We have no void job with this id -> the server could not store the id of the void after sending the request. (e.g. connection issue or crash)
			//We only have on running void which was not yet processed successfully and use it as it should be the one the webhook is for.
		    $void_job = WC_PostFinanceCheckout_Entity_Void_Job::load_running_void_for_transaction($void->getLinkedSpaceId(), $void->getLinkedTransaction());
			if (!$void_job->get_id()) {
				//void not initated in shop backend ignore
				return;
			}
			$void_job->set_void_id($void->getId());
		}
		$void_job->set_state(WC_PostFinanceCheckout_Entity_Void_Job::STATE_DONE);
		
		if ($void_job->get_restock()) {
		    WC_PostFinanceCheckout_Helper::instance()->maybe_restock_items_for_order($order);
		}
		$void_job->save();
	}

	protected function failed(\PostFinanceCheckout\Sdk\Model\TransactionVoid $void, WC_Order $order){
	    $void_job = WC_PostFinanceCheckout_Entity_Void_Job::load_by_void($void->getLinkedSpaceId(), $void->getId());
		if (!$void_job->get_id()) {
			//We have no void job with this id -> the server could not store the id of the void after sending the request. (e.g. connection issue or crash)
			//We only have on running void which was not yet processed successfully and use it as it should be the one the webhook is for.
		    $void_job = WC_PostFinanceCheckout_Entity_Void_Job::load_running_void_for_transaction($void->getLinkedSpaceId(), $void->getLinkedTransaction());
			if (!$void_job->get_id()) {
				//void not initated in shop backend ignore
				return;
			}
			$void_job->set_void_id($void->getId());
		}
		if ($void_job->getFailureReason() != null) {
			$void_job->set_failure_reason($void->getFailureReason()->getDescription());
		}
		$void_job->set_state(WC_PostFinanceCheckout_Entity_Void_Job::STATE_DONE);
		$void_job->save();
	}
}