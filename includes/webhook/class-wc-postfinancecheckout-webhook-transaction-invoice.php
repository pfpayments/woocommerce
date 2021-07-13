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
 * Webhook processor to handle transaction completion state transitions.
 */
class WC_PostFinanceCheckout_Webhook_Transaction_Invoice extends WC_PostFinanceCheckout_Webhook_Order_Related_Abstract {
	/**
	 *
	 * @see WC_PostFinanceCheckout_Webhook_Order_Related_Abstract::load_entity()
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionInvoice
	 */
	protected function load_entity(WC_PostFinanceCheckout_Webhook_Request $request){
		$transaction_invoice_service = new \PostFinanceCheckout\Sdk\Service\TransactionInvoiceService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		return $transaction_invoice_service->read($request->get_space_id(), $request->get_entity_id());
	}

	/**
	 * @param $transaction_invoice
	 * @return \PostFinanceCheckout\Sdk\Model\Transaction
	 * @throws \PostFinanceCheckout\Sdk\ApiException
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException
	 * @throws \PostFinanceCheckout\Sdk\VersioningException
	 */
	protected function load_transaction($transaction_invoice){
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice */
		$transaction_service = new \PostFinanceCheckout\Sdk\Service\TransactionService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		return $transaction_service->read($transaction_invoice->getLinkedSpaceId(), $transaction_invoice->getCompletion()->getLineItemVersion()->getTransaction()->getId());
	}

	protected function get_order_id($transaction_invoice){
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice */
		return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction($transaction_invoice->getLinkedSpaceId(), $transaction_invoice->getCompletion()->getLineItemVersion()->getTransaction()->getId())->get_order_id();
	}

	protected function get_transaction_id($transaction_invoice){
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice */
		return $transaction_invoice->getLinkedTransaction();
	}

	protected function process_order_related_inner(WC_Order $order, $transaction_invoice){
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice */
		switch ($transaction_invoice->getState()) {
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::DERECOGNIZED:
				$this->failed($transaction_invoice, $order);
				break;
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::NOT_APPLICABLE:
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::PAID:
				$this->fulfill($transaction_invoice, $order);
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	protected function fulfill(\PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice, WC_Order $order){
		$transaction = $this->load_transaction($transaction_invoice);
		do_action('wc_postfinancecheckout_fulfill', $transaction , $order);
		//Sets the status to procesing or complete depending on items
		$order->payment_complete($transaction_invoice->getLinkedTransaction());
	}

	// please change to TransactionInvoice
	protected function failed(\PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion, WC_Order $order){
		$completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_by_completion($completion->getLinkedSpaceId(), $completion->getId());
		if (!$completion_job->get_id()) {
			$completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_running_completion_for_transaction($completion->getLinkedSpaceId(),
				$completion->getLinkedTransaction());
			if (!$completion_job->get_id()) {
				return;
			}
			$completion_job->set_completion_id($completion->getId());
		}
		if ($completion->getFailureReason() != null) {
			$completion_job->set_failure_reason($completion->getFailureReason()->getDescription());
		}
		$completion_job->set_state(WC_PostFinanceCheckout_Entity_Completion_Job::STATE_DONE);
		$completion_job->save();
	}
}