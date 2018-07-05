<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * This class provides function to download documents from PostFinance Checkout 
 */
class WC_PostFinanceCheckout_Download_Helper {

	/**
	 * Downloads the transaction's invoice PDF document.
	 */
	public static function download_invoice($order_id){
	    $transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id($order_id);
		if ($transaction_info->get_id() != null && in_array($transaction_info->get_state(), 
				array(
				    \PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
				    \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
				    \PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE 
				))) {
			
		    $service = new \PostFinanceCheckout\Sdk\Service\TransactionService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
			$document = $service->getInvoiceDocument($transaction_info->get_space_id(), $transaction_info->get_transaction_id());
			self::download($document);
		}
	}

	/**
	 * Downloads the transaction's packing slip PDF document.
	 */
	public static function download_packing_slip($order_id){
	    $transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id($order_id);
	    if ($transaction_info->get_id() != null && $transaction_info->get_state() == \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL) {
			
	        $service = new \PostFinanceCheckout\Sdk\Service\TransactionService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
			$document = $service->getPackingSlip($transaction_info->get_space_id(), $transaction_info->get_transaction_id());
			self::download($document);
		}
	}

	/**
	 * Sends the data received by calling the given path to the browser and ends the execution of the script
	 *
	 * @param string $path
	 */
	public static function download(\PostFinanceCheckout\Sdk\Model\RenderedDocument $document){
		header('Pragma: public');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="' . $document->getTitle() . '.pdf"');
		header('Content-Description: ' . $document->getTitle());
		echo base64_decode($document->getData());
		exit();
	}
}