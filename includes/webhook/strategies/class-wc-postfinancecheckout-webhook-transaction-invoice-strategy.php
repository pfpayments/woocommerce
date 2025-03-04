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
 * Handles strategy for processing transaction invoice-related webhook requests.
 * This class extends the base webhook strategy to manage webhook requests specifically
 * dealing with transaction invoices. It focuses on updating order states based on the invoice details
 * retrieved from the webhook data.
 */
class WC_PostFinanceCheckout_Webhook_Transaction_Invoice_Strategy extends WC_PostFinanceCheckout_Webhook_Strategy_Base {

	/**
	 * Match function.
	 *
	 * @inheritDoc
	 * @param string $webhook_entity_id The webhook entity id.
	 */
	public function match( string $webhook_entity_id ) {
		return WC_PostFinanceCheckout_Service_Webhook::POSTFINANCECHECKOUT_TRANSACTION_INVOICE == $webhook_entity_id;
	}

	/**
	 * Load the entity.
	 *
	 * @inheritDoc
	 * @param WC_PostFinanceCheckout_Webhook_Request $request webhook request.
	 */
	protected function load_entity( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$transaction_invoice_service = new \PostFinanceCheckout\Sdk\Service\TransactionInvoiceService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $transaction_invoice_service->read( $request->get_space_id(), $request->get_entity_id() );
	}

	/**
	 * Get the order ID from the object.
	 *
	 * @inheritDoc
	 * @param object $object transaction entity object.
	 */
	protected function get_order_id( $object ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionInvoice $object */
		return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction(
			$object->getLinkedSpaceId(),
			$object->getCompletion()->getLineItemVersion()->getTransaction()->getId()
		)->get_order_id();
	}

	/**
	 * Processes the incoming webhook request pertaining to transaction invoices.
	 *
	 * This method retrieves the transaction invoice details from the API and updates the associated
	 * WooCommerce order based on the state of the invoice.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request object.
	 * @return void
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice */
		$transaction_invoice = $this->load_entity( $request );
		$order = $this->get_order( $transaction_invoice );
		if ( false != $order && $order->get_id() ) {
			$this->process_order_related_inner( $order, $transaction_invoice, $request );
		}
	}

	/**
	 * Additional processing on the order based on the state of the transaction invoice.
	 *
	 * @param WC_Order $order The WooCommerce order linked to the invoice.
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice The transaction invoice object.
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The webhook request object.
	 * @return void
	 */
	protected function process_order_related_inner( WC_Order $order, \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice, WC_PostFinanceCheckout_Webhook_Request $request ) {
		switch ( $request->get_state() ) {
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::DERECOGNIZED:
				$order->add_order_note( __( 'Invoice Not Settled', 'woo-postfinancecheckout' ) );
				break;
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::NOT_APPLICABLE:
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::PAID:
				$order->add_order_note( __( 'Invoice Settled', 'woo-postfinancecheckout' ) );
				break;
			default:
				// Nothing to do.
				break;
		}
	}
}
