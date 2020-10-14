<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
 *
 * @author wallee AG (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * Webhook processor to handle transaction completion state transitions.
 */
class WC_PostFinanceCheckout_Webhook_Transaction_Completion extends WC_PostFinanceCheckout_Webhook_Order_Related_Abstract {

	/**
	 *
	 * @see WC_PostFinanceCheckout_Webhook_Order_Related_Abstract::load_entity()
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionCompletion
	 */
    protected function load_entity(WC_PostFinanceCheckout_Webhook_Request $request){
        $completion_service = new \PostFinanceCheckout\Sdk\Service\TransactionCompletionService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		return $completion_service->read($request->get_space_id(), $request->get_entity_id());
	}

	protected function get_order_id($completion){
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion */
	    return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction($completion->getLineItemVersion()->getTransaction()->getLinkedSpaceId(), $completion->getLineItemVersion()->getTransaction()->getId())->get_order_id();
	}

	protected function get_transaction_id($completion){
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion */
		return $completion->getLinkedTransaction();
	}

	protected function process_order_related_inner(WC_Order $order, $completion){
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion */
		switch ($completion->getState()) {
		    case \PostFinanceCheckout\Sdk\Model\TransactionCompletionState::FAILED:
				$this->failed($completion, $order);
				break;
		    case \PostFinanceCheckout\Sdk\Model\TransactionCompletionState::SUCCESSFUL:
				$this->success($completion, $order);
				break;
			default:
				// Nothing to do.
				break;
		}
	}

	protected function success(\PostFinanceCheckout\Sdk\Model\TransactionCompletion $completion, WC_Order $order){
	    $completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_by_completion($completion->getLinkedSpaceId(), $completion->getId());
		if (!$completion_job->get_id()) {
			//We have no completion job with this id -> the server could not store the id of the completion after sending the request. (e.g. connection issue or crash)
			//We only have on running completion which was not yet processed successfully and use it as it should be the one the webhook is for.
		    $completion_job = WC_PostFinanceCheckout_Entity_Completion_Job::load_running_completion_for_transaction($completion->getLinkedSpaceId(), 
					$completion->getLinkedTransaction());
			if (!$completion_job->get_id()) {
				//completion not initated in shop backend ignore
				return;
			}
			$completion_job->set_completion_id($completion->getId());
		}
		$completion_job->set_state(WC_PostFinanceCheckout_Entity_Completion_Job::STATE_DONE);
		
		if ($completion_job->get_restock()) {
			$this->restock_non_completed_items($completion_job->get_items(), $order);
		}
		$this->adapt_order_items($completion_job->get_items(), $order);
		$completion_job->save();
	}

	private function restock_non_completed_items(array $completed_items, WC_Order $order){
		if ('yes' === get_option('woocommerce_manage_stock') && $order && sizeof($order->get_items()) > 0) {
			foreach ($order->get_items() as $item_id => $item) {
				if ($item->is_type('line_item') && ($product = $item->get_product()) && $product->managing_stock()) {
					
					$changed_qty = $item->get_quantity();
					if (isset($completed_items[$item_id])) {
						$changed_qty = $changed_qty - $completed_items[$item_id]['qty'];
					}
					if ($changed_qty > 0) {
						$item_name = $product->get_formatted_name();
						$new_stock = wc_update_product_stock($product, $changed_qty, 'increase');
						$old_stock = $new_stock - $changed_qty;
						
						$order->add_order_note(
								sprintf(__('%1$s stock increased from %2$s to %3$s.', 'woo-postfinancecheckout'), $item_name, $old_stock, $new_stock));
						do_action('wc_postfinancecheckout_restock_not_completed_item', $product->get_id(), $old_stock, $new_stock, $order, $product);
					}
				}
			}
		}
	}

	private function adapt_order_items(array $completed_items, WC_Order $order){
		foreach ($order->get_items() as $item_id => $item) {
			if (!isset($completed_items[$item_id]) ||
					 $completed_items[$item_id]['completion_total'] + array_sum($completed_items[$item_id]['completion_tax']) == 0) {
				$order_item = $order->get_item($item_id);
				$order_item->delete(true);
				continue;
			}
			$old_total = $item->get_total();
			$subtotal = $item->get_subtotal();
			$ratio = $old_total / $completed_items[$item_id]['completion_total'];
			if ($ratio != 0) {
				$subtotal = $subtotal / $ratio;
			}
			$old_taxes = $item->get_taxes();
			$new_taxes = array(
				'total' => array(),
				'subtotal' => array() 
			);
			foreach (array_keys($old_taxes['total']) as $id) {
				$old_tax = $old_taxes['total'][$id];
				$subtax = $old_taxes['subtotal'][$id];
				if ($completed_items[$item_id]['completion_tax'][$id] != 0) {
					$ration = $old_tax / $completed_items[$item_id]['completion_tax'][$id];
					if ($ration != 0) {
						$subtax = $subtax / $ratio;
					}
				}
				$new_taxes['total'][$id] = wc_format_decimal($completed_items[$item_id]['completion_tax'][$id], wc_get_price_decimals());
				$new_taxes['subtotal'][$id] = wc_format_decimal($subtax, wc_get_price_decimals());
			}
			
			$item->set_props(
					array(
						'quantity' => $completed_items[$item_id]['qty'],
						'total' => wc_format_decimal($completed_items[$item_id]['completion_total'], wc_get_price_decimals()),
						'subtotal' => wc_format_decimal($subtotal, wc_get_price_decimals()),
						'taxes' => $new_taxes 
					));
			$item->save();
		}
		foreach ($order->get_fees() as $fee_id => $fee) {
			if (!isset($completed_items[$fee_id]) ||
					 $completed_items[$fee_id]['completion_total'] + array_sum($completed_items[$fee_id]['completion_tax']) == 0) {
				$order_fee = $order->get_item($fee_id);
				$order_fee->delete();
				continue;
			}
			$fee->set_props(
					array(
						'total' => $completed_items[$fee_id]['completion_total'],
						'taxes' => array(
							'total' => $completed_items[$fee_id]['completion_tax'] 
						) 
					));
			$fee->save();
		}
		foreach ($order->get_shipping_methods() as $shipping_id => $shipping) {
			
			if (!isset($completed_items[$shipping_id]) ||
					 $completed_items[$shipping_id]['completion_total'] + array_sum($completed_items[$shipping_id]['completion_tax']) == 0) {
				$order_shipping = $order->get_item($shipping_id);
				$order_shipping->delete();
				continue;
			}
			$shipping->set_props(
					array(
						'total' => $completed_items[$shipping_id]['completion_total'],
						'taxes' => array(
							'total' => $completed_items[$shipping_id]['completion_tax'] 
						) 
					));
			
			$shipping->save();
		}
		$order->save();
		$order = WC_Order_Factory::get_order($order->get_id());
		$order->update_taxes();
		$order->calculate_totals(false);
	}

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