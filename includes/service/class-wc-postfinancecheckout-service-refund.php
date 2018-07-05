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
 * This service provides functions to deal with PostFinanceCheckout refunds.
 */
class WC_PostFinanceCheckout_Service_Refund extends WC_PostFinanceCheckout_Service_Abstract {
	
	/**
	 * The refund API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\RefundService
	 */
	private $refund_service;

	/**
	 * Returns the refund by the given external id.
	 *
	 * @param int $space_id
	 * @param string $external_id
	 * @return \PostFinanceCheckout\Sdk\Model\Refund
	 */
	public function get_refund_by_external_id($space_id, $external_id){
	    $query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		$query->setFilter($this->create_entity_filter('externalId', $external_id));
		$query->setNumberOfEntities(1);
		$result = $this->get_refund_service()->search($space_id, $query);
		if ($result != null && !empty($result)) {
			return current($result);
		}
		else {
			throw new Exception('The refund could not be found.');
		}
	}

	/**
	 * Creates a refund request model for the given refund.
	 *
	 * @param WC_Order $order
	 * @param WC_Order_Refund $refund
	 * @return \PostFinanceCheckout\Sdk\Model\RefundCreate
	 */
	public function create(WC_Order $order, WC_Order_Refund $refund){
	    $data = WC_PostFinanceCheckout_Helper::instance()->get_transaction_id_map_for_order($order);
	    $transaction = WC_PostFinanceCheckout_Service_Transaction::instance()->get_transaction($data['space_id'], $data['transaction_id']);
		
		$reductions = $this->get_reductions($order, $refund);
		$reductions = $this->fix_reductions($refund, $transaction, $reductions);
		
		$refund_create = new \PostFinanceCheckout\Sdk\Model\RefundCreate();
		$refund_create->setExternalId(uniqid($refund->get_id() . '-'));
		$refund_create->setReductions($reductions);
		$refund_create->setTransaction($transaction->getId());
		$refund_create->setType(\PostFinanceCheckout\Sdk\Model\RefundType::MERCHANT_INITIATED_ONLINE);
		return $refund_create;
	}

	/**
	 * Returns the fixed line item reductions for the refund.
	 *
	 * If the amount of the given reductions does not match the refund's grand total, the amount to refund is distributed equally to the line items.
	 *
	 * @param WC_Order_Refund $refund
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @param \PostFinanceCheckout\Sdk\Model\LineItemReductionCreate[] $reductions
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemReductionCreate[]
	 */
	protected function fix_reductions(WC_Order_Refund $refund, \PostFinanceCheckout\Sdk\Model\Transaction $transaction, array $reductions){
		$base_line_items = $this->get_base_line_items($transaction);
		
		$helper = WC_PostFinanceCheckout_Helper::instance();
		$reduction_amount = $helper->get_reduction_amount($base_line_items, $reductions);
		$refund_total = $refund->get_total() * -1;
		
		if (wc_format_decimal($reduction_amount) != wc_format_decimal($refund_total)) {
			$fixed_reductions = array();
			$base_amount = $helper->get_total_amount_including_tax($base_line_items);
			$rate = $refund_total / $base_amount;
			foreach ($base_line_items as $line_item) {
			    $reduction = new \PostFinanceCheckout\Sdk\Model\LineItemReductionCreate();
				$reduction->setLineItemUniqueId($line_item->getUniqueId());
				$reduction->setQuantityReduction(0);
				$reduction->setUnitPriceReduction(round($line_item->getAmountIncludingTax() * $rate / $line_item->getQuantity(), 8));
				$fixed_reductions[] = $reduction;
			}
			
			return $fixed_reductions;
		}
		else {
			return $reductions;
		}
	}

	/**
	 * Returns the line item reductions for the refund items.
	 *
	 * @param WC_Order $order 
	 * @param WC_Order_Refund $refund
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemReductionCreate[]
	 */
	protected function get_reductions(WC_Order $order, WC_Order_Refund $refund){
		$reductions = array();
		foreach ($refund->get_items() as $item_id => $item) {
			
			$order_item = $order->get_item($item->get_meta('_refunded_item_id', true));
			
			$order_total = $order_item->get_total() + $order_item->get_total_tax();
			
			$order_quantity = 1;
			if ($order_item->get_quantity() != 0) {
				$order_quantity = $order_item->get_quantity();
			}
			$order_unit_price = $order_total / $order_quantity;
			
			$refund_total = ($item->get_total() + $item->get_total_tax()) * -1;
			$refund_quantity = 1;
			if ($item->get_quantity() != 0) {
				$refund_quantity = $item->get_quantity() * -1;
			}
			$refund_unit_price = $refund_total / $refund_quantity;
			
			$unique_id = $order_item->get_meta('_postfinancecheckout_unique_line_item_id', true);
			
			$reduction = new \PostFinanceCheckout\Sdk\Model\LineItemReductionCreate();
			$reduction->setLineItemUniqueId($unique_id);
			
			//The merchant did not refund complete items, we have to adapt the unit price
			if (wc_format_decimal($order_unit_price) != wc_format_decimal($refund_unit_price)) {
				$reduction->setQuantityReduction(0);
				$reduction->setUnitPriceReduction(round($refund_total / $order_quantity, 8));
			}
			else {
				$reduction->setQuantityReduction($refund_quantity);
				$reduction->setUnitPriceReduction(0);
			}
			$reductions[] = $reduction;
		}
		foreach ($refund->get_fees() as $fee_id => $fee) {
			
			$order_fee = $order->get_item($fee->get_meta('_refunded_item_id', true));
			$unique_id = $order_fee->get_meta('_postfinancecheckout_unique_line_item_id', true);
			
			//Refunds amount are stored as negativ values
			$amount_including_tax = $fee->get_total() + $fee->get_total_tax();
			
			$reduction = new \PostFinanceCheckout\Sdk\Model\LineItemReductionCreate();
			$reduction->setLineItemUniqueId($unique_id);
			$reduction->setQuantityReduction(0);
			$reduction->setUnitPriceReduction($amount_including_tax * -1);
			$reductions[] = $reduction;
		}
		foreach ($refund->get_shipping_methods() as $shipping_id => $shipping) {
			
			$order_shipping = $order->get_item($shipping->get_meta('_refunded_item_id', true));
			$unique_id = $order_shipping->get_meta('_postfinancecheckout_unique_line_item_id', true);
			
			//Refunds amount are stored as negativ values
			$amount_including_tax = $shipping->get_total() + $shipping->get_total_tax();
			
			$reduction = new \PostFinanceCheckout\Sdk\Model\LineItemReductionCreate();
			$reduction->setLineItemUniqueId($unique_id);
			$reduction->setQuantityReduction(0);
			$reduction->setUnitPriceReduction($amount_including_tax * -1);
			$reductions[] = $reduction;
		}
		
		return $reductions;
	}

	/**
	 * Sends the refund to the gateway.
	 *
	 * @param int $spaceId
	 * @param \PostFinanceCheckout\Sdk\Model\RefundCreate $refund
	 * @return \PostFinanceCheckout\Sdk\Model\Refund
	 */
	public function refund($spaceId, \PostFinanceCheckout\Sdk\Model\RefundCreate $refund){
		return $this->get_refund_service()->refund($spaceId, $refund);
	}

	/**
	 * Returns the line items that are to be used to calculate the refund.
	 *
	 * This returns the line items of the latest refund if there is one or else of the completed transaction.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @param \PostFinanceCheckout\Sdk\Model\Refund $refund
	 * @return \PostFinanceCheckout\Sdk\Model\LineItem[]
	 */
	protected function get_base_line_items(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, \PostFinanceCheckout\Sdk\Model\Refund $refund = null){
		$last_successful_refund = $this->get_last_successful_refund($transaction, $refund);
		if ($last_successful_refund) {
			return $last_successful_refund->getReducedLineItems();
		}
		else {
			return $this->get_transaction_invoice($transaction)->getLineItems();
		}
	}

	/**
	 * Returns the transaction invoice for the given transaction.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @throws Exception
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionInvoice
	 */
	protected function get_transaction_invoice(\PostFinanceCheckout\Sdk\Model\Transaction $transaction){
	    $query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		
	    $filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
	    $filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
				    $this->create_entity_filter('state', \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::CANCELED,
				        \PostFinanceCheckout\Sdk\Model\CriteriaOperator::NOT_EQUALS),
					$this->create_entity_filter('completion.lineItemVersion.transaction.id', $transaction->getId()) 
				));
		$query->setFilter($filter);
		
		$query->setNumberOfEntities(1);
		
		$invoice_service = new \PostFinanceCheckout\Sdk\Service\TransactionInvoiceService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		$result = $invoice_service->search($transaction->getLinkedSpaceId(), $query);
		if (!empty($result)) {
			return $result[0];
		}
		else {
			throw new Exception('The transaction invoice could not be found.');
		}
	}

	/**
	 * Returns the last successful refund of the given transaction, excluding the given refund.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @param \PostFinanceCheckout\Sdk\Model\Refund $refund
	 * @return \PostFinanceCheckout\Sdk\Model\Refund
	 */
	protected function get_last_successful_refund(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, \PostFinanceCheckout\Sdk\Model\Refund $refund = null){
	    $query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		
	    $filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
	    $filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND);
		$filters = array(
		    $this->create_entity_filter('state', \PostFinanceCheckout\Sdk\Model\RefundState::SUCCESSFUL),
			$this->create_entity_filter('transaction.id', $transaction->getId()) 
		);
		if ($refund != null) {
		    $filters[] = $this->create_entity_filter('id', $refund->getId(), \PostFinanceCheckout\Sdk\Model\CriteriaOperator::NOT_EQUALS);
		}
		
		$filter->setChildren($filters);
		$query->setFilter($filter);
		
		$query->setOrderBys(array(
		    $this->create_entity_order_by('createdOn', \PostFinanceCheckout\Sdk\Model\EntityQueryOrderByType::DESC) 
		));
		
		$query->setNumberOfEntities(1);
		
		$result = $this->get_refund_service()->search($transaction->getLinkedSpaceId(), $query);
		if (!empty($result)) {
			return $result[0];
		}
		else {
			return false;
		}
	}

	/**
	 * Returns the refund API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\RefundService
	 */
	protected function get_refund_service(){
		if ($this->refund_service == null) {
		    $this->refund_service = new \PostFinanceCheckout\Sdk\Service\RefundService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		}
		
		return $this->refund_service;
	}
}