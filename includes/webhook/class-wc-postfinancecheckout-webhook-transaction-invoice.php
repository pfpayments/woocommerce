<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
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
	 *
	 * @param \WC_PostFinanceCheckout_Webhook_Request $request
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionInvoice
	 * @throws \PostFinanceCheckout\Sdk\ApiException
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException
	 * @throws \PostFinanceCheckout\Sdk\VersioningException
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
				$order->add_order_note(__('Invoice Not Settled'));
				break;
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::NOT_APPLICABLE:
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::PAID:
				$order->add_order_note(__('Invoice Settled'));
				break;
			default:
				// Nothing to do.
				break;
		}
	}
}
