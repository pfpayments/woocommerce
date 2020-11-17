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
 * Webhook processor to handle refund state transitions.
 */
class WC_PostFinanceCheckout_Webhook_Refund extends WC_PostFinanceCheckout_Webhook_Order_Related_Abstract {

	/**
	 *
	 * @see WC_PostFinanceCheckout_Webhook_Order_Related_Abstract::load_entity()
	 * @return \PostFinanceCheckout\Sdk\Model\Refund
	 */
    protected function load_entity(WC_PostFinanceCheckout_Webhook_Request $request){
        $refund_service = new \PostFinanceCheckout\Sdk\Service\RefundService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		return $refund_service->read($request->get_space_id(), $request->get_entity_id());
	}

	protected function get_order_id($refund){
		/* @var \PostFinanceCheckout\Sdk\Model\Refund $refund */
	    return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction($refund->getTransaction()->getLinkedSpaceId(), $refund->getTransaction()->getId())->get_order_id();
	}

	protected function get_transaction_id($refund){
		/* @var \PostFinanceCheckout\Sdk\Model\Refund $refund */
		return $refund->getTransaction()->getId();
	}

	protected function process_order_related_inner(WC_Order $order, $refund){
		/* @var \PostFinanceCheckout\Sdk\Model\Refund $refund */
		switch ($refund->getState()) {
		    case \PostFinanceCheckout\Sdk\Model\RefundState::FAILED:
				$this->failed($refund, $order);
				break;
		    case \PostFinanceCheckout\Sdk\Model\RefundState::SUCCESSFUL:
				$this->refunded($refund, $order);
			default:
				// Nothing to do.
				break;
		}
	}

	protected function failed(\PostFinanceCheckout\Sdk\Model\Refund $refund, WC_Order $order){
	    $refund_job = WC_PostFinanceCheckout_Entity_Refund_Job::load_by_external_id($refund->getLinkedSpaceId(), $refund->getExternalId());
		if ($refund_job->get_id()) {
		    $refund_job->set_state(WC_PostFinanceCheckout_Entity_Refund_Job::STATE_FAILURE);
			if ($refund->getFailureReason() != null) {
				$refund_job->set_failure_reason($refund->getFailureReason()->getDescription());
			}
			$refund_job->save();
			$refunds = $order->get_refunds();
			foreach($refunds as $wc_refund){
			    if($wc_refund->get_meta('_postfinancecheckout_refund_job_id', true) == $refund_job->get_id()){
			        $wc_refund->set_status("failed");
			        $wc_refund->save();
			        break;
			    }			    
			}
		}
	}

	protected function refunded(\PostFinanceCheckout\Sdk\Model\Refund $refund, WC_Order $order){
	    $refund_job = WC_PostFinanceCheckout_Entity_Refund_Job::load_by_external_id($refund->getLinkedSpaceId(), $refund->getExternalId());
		
		if ($refund_job->get_id()) {
		    $refund_job->set_state(WC_PostFinanceCheckout_Entity_Refund_Job::STATE_SUCCESS);
			$refund_job->save();
			$refunds = $order->get_refunds();
			foreach($refunds as $wc_refund){
			    if($wc_refund->get_meta('_postfinancecheckout_refund_job_id', true) == $refund_job->get_id()){
			        $wc_refund->set_status("completed");
			        $wc_refund->save();
			        break;
			    }
			}
		}
	}
}