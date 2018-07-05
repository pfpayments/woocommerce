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
 * This service provides methods to handle line items.
 */
class WC_PostFinanceCheckout_Service_Line_Item extends WC_PostFinanceCheckout_Service_Abstract {

	/**
	 * Returns the line items from the given cart
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	public function get_items_from_session(){
		$currency = get_woocommerce_currency();
		$cart = WC()->cart;
		$cart->calculate_totals();
		
		$chosen_methods = WC()->session->get('chosen_shipping_methods');
		$packages = WC()->shipping->get_packages();
		
		$items = $this->create_product_line_items_from_session($cart, $currency);
		$fees = $this->create_fee_lines_items_from_session($cart, $currency);
		$shipping = $this->create_shipping_line_items_from_session($packages, $chosen_methods, $currency);
		$combined = array_merge($items, $fees, $shipping);
		$all = WC_PostFinanceCheckout_Helper::instance()->cleanup_line_items($combined, $cart->total, $currency);
		return $all;
	}

	/**
	 * Creates the line items for the products
	 *
	 * @param WC_Cart $cart
	 * @param $currency
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_product_line_items_from_session(WC_Cart $cart, $currency){
		$items = array();
		foreach ($cart->get_cart() as $cart_item_key => $values) {
			/**
			 * @var WC_Product $product
			 */
			$product = $values['data'];
			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			
			$amount_including_tax = $values['line_total'] + $values['line_tax'];
			
			$line_item->setAmountIncludingTax($this->round_amount($amount_including_tax, $currency));
			$line_item->setName($product->get_name());
			
			$quantity = 1;
			if ($values['quantity'] != 0) {
				$quantity = $values['quantity'];
			}
			$line_item->setQuantity($quantity);
			$line_item->setShippingRequired(!$product->get_virtual());
			
			$sku = $product->get_sku();
			if (empty($sku)) {
				$sku = $product->get_name();
			}
			$sku = str_replace(array(
				"\n",
				"\r" 
			), '', $sku);
			$line_item->setSku($sku, 200);
			
			$line_item->setTaxes($this->get_taxes(WC_Tax::get_rates($product->get_tax_class())));
			
			$line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::PRODUCT);
			
			$unique_id = hash('sha256', rand());
			
			if (isset($values['_postfinancecheckout_unique_line_item_id'])) {
				$unique_id = $values['_postfinancecheckout_unique_line_item_id'];
			}
			$line_item->setUniqueId($unique_id);
			
			$items[] = $this->clean_line_item($line_item);
		}
		return $items;
	}

	/**
	 * Returns the line items for fees.
	 *
	 * @param WC_Cart $cart
	 * @param $currency
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_fee_lines_items_from_session(WC_Cart $cart, $currency){
		$fees = array();
		foreach ($cart->get_fees() as $fee_key => $fee) {
		    $line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			
			$amount_including_tax = $fee->amount + $fee->tax;
			
			$line_item->setAmountIncludingTax($this->round_amount($amount_including_tax, $currency));
			$line_item->setName($fee->name);
			$line_item->setQuantity(1);
			$line_item->setShippingRequired(false);
			
			$sku = $fee->name;
			$sku = str_replace(array(
				"\n",
				"\r" 
			), '', $sku);
			$line_item->setSku($sku, 200);
			
			$line_item->setTaxes($this->get_taxes(WC_Tax::get_rates($fee->tax_class)));
			
			if ($amount_including_tax < 0) {
				//There are plugins which create fees with a negative values (used as discounts)
			    $line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT);
				$line_item->setUniqueId('discount-' . $fee->id);
			}
			else {
			    $line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::FEE);
				$line_item->setUniqueId('fee-' . $fee->id);
			}
			$fees[] = $this->clean_line_item($line_item);
		}
		return $fees;
	}

	/**
	 * Returns the line items for the shipping costs.
	 * 
	 * @param object[] $packages
	 * @param string[] $chosen_methods
	 * @param String $currency
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_shipping_line_items_from_session($packages, $chosen_methods, $currency){
		$shippings = array();
		
		foreach ($packages as $package_key => $package) {
			if (isset($chosen_methods[$package_key], $package['rates'][$chosen_methods[$package_key]])) {
				$shipping_rate = $package['rates'][$chosen_methods[$package_key]];
				
				$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
				
				$amount_including_tax = $shipping_rate->cost + $shipping_rate->get_shipping_tax();
				
				$line_item->setAmountIncludingTax($this->round_amount($amount_including_tax, $currency));
				$line_item->setName($shipping_rate->get_label());
				$line_item->setQuantity(1);
				$line_item->setShippingRequired(false);
				
				$sku = $shipping_rate->get_label();
				$sku = str_replace(array(
					"\n",
					"\r" 
				), '', $sku);
				$line_item->setSku($sku);
				
				$line_item->setTaxes($this->get_taxes(WC_Tax::get_shipping_tax_rates()));
				
				$line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::SHIPPING);
				$meta_data = $shipping_rate->get_meta_data();
				$unique_id = hash('sha256', rand());
				
				if (isset($meta_data['_postfinancecheckout_unique_line_item_id'])) {
					$unique_id = $meta_data['_postfinancecheckout_unique_line_item_id'];
				}
				$line_item->setUniqueId($unique_id);
				
				$shippings[] = $this->clean_line_item($line_item);
			}
		}
		return $shippings;
	}

	/**
	 * Returns the line items from the given cart
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	public function get_items_from_order(WC_Order $order){
		$raw = $this->get_raw_items_from_order($order);
		return WC_PostFinanceCheckout_Helper::instance()->cleanup_line_items($raw, $order->get_total(), $order->get_currency());
	}
	
	public function get_raw_items_from_order(WC_Order $order){
	    $items = $this->create_product_line_items_from_order($order);
	    $fees = $this->create_fee_lines_items_from_order($order);
	    $shipping = $this->create_shipping_line_items_from_order($order);
	    $combined = array_merge($items, $fees, $shipping);
	    return $combined;
	}

	/**
	 * Creates the line items for the products
	 *
	 * @param WC_Order $order
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_product_line_items_from_order(WC_Order $order){
		$items = array();
		$currency = $order->get_currency();
		foreach ($order->get_items() as $item) {
			/**
			 * @var WC_Order_Item_Product $item
			 */
			
		    $line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			
			$amount_including_tax = $item->get_total() + $item->get_total_tax();
			
			$line_item->setAmountIncludingTax($this->round_amount($amount_including_tax, $currency));
			$quantity = 1;
			if ($item->get_quantity() != 0) {
				$quantity = $item->get_quantity();
			}
			$line_item->setQuantity($quantity);
			
			$product = $item->get_product();
			$line_item->setName($product->get_name());
			$line_item->setShippingRequired(!$product->get_virtual());
			
			$sku = $product->get_sku();
			if (empty($sku)) {
				$sku = $product->get_name();
			}
			$sku = str_replace(array(
				"\n",
				"\r" 
			), '', $sku);
			$line_item->setSku($sku, 200);
			
			$line_item->setTaxes($this->get_taxes(WC_Tax::get_rates($item->get_tax_class())));
			
			$line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::PRODUCT);
			$line_item->setUniqueId($item->get_meta('_postfinancecheckout_unique_line_item_id', true));
			
			$items[] = $this->clean_line_item($line_item);
		}
		return $items;
	}

	/**
	 * Returns the line items for fees.
	 *
	 * @param WC_Order $order
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_fee_lines_items_from_order(WC_Order $order){
		$fees = array();
		$currency = $order->get_currency();
		
		foreach ($order->get_fees() as $fee) {
			/**
			 * @var WC_Order_Item_Fee $fee
			 */
			
		    $line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			
			$amount_including_tax = $fee->get_total() + $fee->get_total_tax();
			
			$line_item->setAmountIncludingTax($this->round_amount($amount_including_tax, $currency));
			$line_item->setName($fee->get_name());
			$line_item->setQuantity(1);
			$line_item->setShippingRequired(false);
			
			$sku = $fee->get_name();
			$sku = str_replace(array(
				"\n",
				"\r" 
			), '', $sku);
			$line_item->setSku($sku, 200);
			
			$line_item->setTaxes($this->get_taxes(WC_Tax::get_rates($fee->get_tax_class())));
			
			if ($amount_including_tax < 0) {
				//There are plugins which create fees with a negative values (used as discounts)
			    $line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT);
			}
			else {
			    $line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::FEE);
			}
			
			$line_item->setUniqueId($fee->get_meta('_postfinancecheckout_unique_line_item_id', true));
			
			$fees[] = $this->clean_line_item($line_item);
		}
		return $fees;
	}

	/**
	 * Returns the line items for the shipping costs.
	 *
	 * @param WC_Order $order
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_shipping_line_items_from_order(WC_Order $order){
		$shippings = array();
		$currency = $order->get_currency();
		
		foreach ($order->get_shipping_methods() as $shipping) {
			/**
			 * @var WC_Order_Item_Shipping $shipping
			 */
			
		    $line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			
			$amount_including_tax = $shipping->get_total() + $shipping->get_total_tax();
			
			$line_item->setAmountIncludingTax($this->round_amount($amount_including_tax, $currency));
			$line_item->setName($shipping->get_method_title());
			$line_item->setQuantity(1);
			$line_item->setShippingRequired(false);
			$sku = $shipping->get_method_title();
			$sku = str_replace(array(
				"\n",
				"\r" 
			), '', $sku);
			$line_item->setSku($sku);
			$taxes = $shipping->get_taxes();
			$line_item->setTaxes($this->get_taxes($taxes['total']));
			
			$line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::SHIPPING);
			$line_item->setUniqueId($shipping->get_meta('_postfinancecheckout_unique_line_item_id', true));
			
			$shippings[] = $this->clean_line_item($line_item);
		}
		return $shippings;
	}

	public function get_items_from_backend(array $backend_items, $amount, WC_Order $order){
		$items = $this->create_product_line_items_from_backend($backend_items, $order);
		$fees = $this->create_fee_lines_items_from_backend($backend_items, $order);
		$shipping = $this->create_shipping_line_items_from_backend($backend_items, $order);
		$combined = array_merge($items, $fees, $shipping);
		
		return WC_PostFinanceCheckout_Helper::instance()->cleanup_line_items($combined, $amount, $order->get_currency());
	}

	/**
	 * Creates the line items for the products
	 *
	 * @param WC_Order $order
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_product_line_items_from_backend(array $backend_items, WC_Order $order){
		$items = array();
		$currency = $order->get_currency();
		foreach ($order->get_items() as $item_id => $item) {
			if (!isset($backend_items[$item_id])) {
				continue;
			}
			/**
			 * @var WC_Order_Item_Product $item
			 */
			
			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			
			$tax = 0;
			if(isset($backend_items[$item_id]['completion_tax'])){
				$tax = array_sum($backend_items[$item_id]['completion_tax']);
			}
			
			$amount_including_tax = $backend_items[$item_id]['completion_total'] + $tax;
			
			$line_item->setAmountIncludingTax($this->round_amount($amount_including_tax, $currency));
			$quantity = 1;
			if ($backend_items[$item_id]['qty'] != 0) {
				$quantity = $backend_items[$item_id]['qty'];
			}
			$line_item->setQuantity($quantity);
			
			$product = $item->get_product();
			$line_item->setName($product->get_name());
			$line_item->setShippingRequired(!$product->get_virtual());
			
			$sku = $product->get_sku();
			if (empty($sku)) {
				$sku = $product->get_name();
			}
			$sku = str_replace(array(
				"\n",
				"\r" 
			), '', $sku);
			$line_item->setSku($sku, 200);
			
			$line_item->setTaxes($this->get_taxes(WC_Tax::get_rates($item->get_tax_class())));
			
			$line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::PRODUCT);
			$line_item->setUniqueId($item->get_meta('_postfinancecheckout_unique_line_item_id', true));
			
			$items[] = $this->clean_line_item($line_item);
		}
		return $items;
	}

	/**
	 * Returns the line items for fees.
	 *
	 * @param WC_Order $order
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_fee_lines_items_from_backend(array $backend_items, WC_Order $order){
		$fees = array();
		$currency = $order->get_currency();
		
		foreach ($order->get_fees() as $fee_id => $fee) {
			
			if (!isset($backend_items[$fee_id])) {
				continue;
			}
			
			$tax = 0;
			if(isset($backend_items[$fee_id]['completion_tax'])){
				$tax = array_sum($backend_items[$fee_id]['completion_tax']);
			}
			
			if ($backend_items[$fee_id]['completion_total'] + $tax == 0) {
				continue;
			}
			/**
			 * @var WC_Order_Item_Product $item
			 */
			
			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			
			$amount_including_tax = $backend_items[$fee_id]['completion_total'] + $tax;
			
			$line_item->setAmountIncludingTax($this->round_amount($amount_including_tax, $currency));
			$line_item->setName($fee->get_name());
			$line_item->setQuantity(1);
			$line_item->setShippingRequired(false);
			
			$sku = $fee->get_name();
			$sku = str_replace(array(
				"\n",
				"\r" 
			), '', $sku);
			$line_item->setSku($sku, 200);
			
			$line_item->setTaxes($this->get_taxes($tax_rates = WC_Tax::get_rates($fee->get_tax_class())));
			
			if ($amount_including_tax < 0) {
				//There are plugins which create fees with a negative values (used as discounts)
			    $line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT);
			}
			else {
			    $line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::FEE);
			}
			
			$line_item->setUniqueId($fee->get_meta('_postfinancecheckout_unique_line_item_id', true));
			
			$fees[] = $this->clean_line_item($line_item);
		}
		return $fees;
	}

	/**
	 * Returns the line items for the shipping costs.
	 *
	 * @param WC_Order $order
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_shipping_line_items_from_backend(array $backend_items, WC_Order $order){
		$shippings = array();
		$currency = $order->get_currency();
		
		foreach ($order->get_shipping_methods() as $shipping_id => $shipping) {
			if (!isset($backend_items[$shipping_id])) {
				continue;
			}
			$tax = 0;
			if(isset($backend_items[$shipping_id]['completion_tax'])){
				$tax = array_sum($backend_items[$shipping_id]['completion_tax']);
			}
			if ($backend_items[$shipping_id]['completion_total'] + $tax == 0) {
				continue;
			}
			/**
			 * @var WC_Order_Item_Shipping $shipping
			 */
			
			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			
			$amount_including_tax = $backend_items[$shipping_id]['completion_total'] + $tax;
			
			$line_item->setAmountIncludingTax($this->round_amount($amount_including_tax, $currency));
			$line_item->setName($shipping->get_method_title());
			$line_item->setQuantity(1);
			$line_item->setShippingRequired(false);
			$sku = $shipping->get_method_title();
			$sku = str_replace(array(
				"\n",
				"\r" 
			), '', $sku);
			$line_item->setSku($sku);
			
			$taxes = $shipping->get_taxes();
			$line_item->setTaxes($this->get_taxes($taxes['total']));
			
			$line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::SHIPPING);
			$line_item->setUniqueId($shipping->get_meta('_postfinancecheckout_unique_line_item_id', true));
			
			$shippings[] = $this->clean_line_item($line_item);
		}
		return $shippings;
	}

	/**
	 * Cleans the given line item for it to meet the API's requirements.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\LineItemCreate $lineItem
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate
	 */
	protected function clean_line_item(\PostFinanceCheckout\Sdk\Model\LineItemCreate $line_item){
		$line_item->setSku($this->fix_length($line_item->getSku(), 200));
		$line_item->setName($this->fix_length($line_item->getName(), 150));
		return $line_item;
	}

	protected function get_taxes(array $rates_or_ids){
		$tax_rates = array();
		
		foreach ($rates_or_ids as $rate_id => $rate_info) {
		    $tax = new \PostFinanceCheckout\Sdk\Model\TaxCreate();
			$tax->setTitle(WC_Tax::get_rate_label($rate_id));
			$percent = WC_Tax::get_rate_percent($rate_id);
			$number = rtrim($percent, '%');
			$tax->setRate($number);
			$tax_rates[] = $tax;
		}
		
		return $tax_rates;
	}
}