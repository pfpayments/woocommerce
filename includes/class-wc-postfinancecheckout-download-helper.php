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
 * Class WC_PostFinanceCheckout_Download_Helper.
 * This class provides function to download documents from PostFinance Checkout
 *
 * @class WC_PostFinanceCheckout_Download_Helper
 */
class WC_PostFinanceCheckout_Download_Helper {

	/**
	 * Downloads the transaction's invoice PDF document.
	 *
	 * @param int $order_id order id.
	 */
	public static function download_invoice( $order_id ) {
		$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order_id );
		if ( ! is_null( $transaction_info->get_id() ) && in_array(
			$transaction_info->get_state(),
			array(
				\PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
				\PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
				\PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE,
			),
			true
		) ) {

			$service = new \PostFinanceCheckout\Sdk\Service\TransactionService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
			$document = $service->getInvoiceDocument( $transaction_info->get_space_id(), $transaction_info->get_transaction_id() );
			self::download( $document );
		}
	}

	/**
	 * Downloads the transaction's packing slip PDF document.
	 *
	 * @param int $order_id order id.
	 */
	public static function download_packing_slip( $order_id ) {
		$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order_id );
		if ( ! is_null( $transaction_info->get_id() ) && $transaction_info->get_state() == \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL ) {

			$service = new \PostFinanceCheckout\Sdk\Service\TransactionService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
			$document = $service->getPackingSlip( $transaction_info->get_space_id(), $transaction_info->get_transaction_id() );
			self::download( $document );
		}
	}

	/**
	 * Sends the data received by calling the given path to the browser and ends the execution of the script
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\RenderedDocument $document document.
	 */
	public static function download( \PostFinanceCheckout\Sdk\Model\RenderedDocument $document ) {
		header( 'Pragma: public' );
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Content-type: application/pdf' );
		header( 'Content-Disposition: attachment; filename="' . esc_html( $document->getTitle() ) . '.pdf"' );
		header( 'Content-Description: ' . esc_html( $document->getTitle() ) );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$data_safe = base64_decode( $document->getData() );
		// The following line outputs binary PDF data, escaping is not applied as it's not HTML content.
		echo $data_safe; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit();
	}
}
