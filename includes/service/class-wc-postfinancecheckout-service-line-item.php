<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
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
     * @throws WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount
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
		foreach ($cart->get_cart() as $values) {
			/**
			 * @var WC_Product $product
			 */
			$product = $values['data'];
			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
            $amount_including_tax = $values['line_subtotal'] + $values['line_subtotal_tax'];
            $discount_including_tax = $values['line_total'] + $values['line_tax'];

            $line_item->setAmountIncludingTax($this->round_amount($discount_including_tax, $currency));
            $line_item->setDiscountIncludingTax($this->round_amount($amount_including_tax - $discount_including_tax, $currency));
			$line_item->setName($product->get_name());
			
			$quantity = empty($values['quantity'])? 1 : $values['quantity'];

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
			$line_item->setUniqueId(WC_PostFinanceCheckout_Unique_Id::get_uuid());
			
			$attributes = $this->get_base_attributes($product->get_id());
			foreach ($values['variation'] as $key => $value){
			    if(strpos($key, 'attribute_') === 0){
			        $taxonomy = substr($key, 10);
			        $attribute_key_cleaned = $this->clean_attribute_key($taxonomy);
			        if(isset($attributes[$attribute_key_cleaned])){
			            $term = get_term_by('slug', $value, $taxonomy, 'display');
			            $attributes[$attribute_key_cleaned]->setValue($this->fix_length($term->name, 512));
			        }
			    }
			}
			if(!empty($attributes)){
			  $line_item->setAttributes($attributes);
			}
			
			$items[] = apply_filters('wc_postfinancecheckout_modify_line_item_product_session', $this->clean_line_item($line_item), $values);
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
		foreach ($cart->get_fees() as $fee) {
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
			$fees[] = apply_filters('wc_postfinancecheckout_modify_line_item_fee_session', $this->clean_line_item($line_item), $fee);
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
				$line_item->setUniqueId(WC_PostFinanceCheckout_Unique_Id::get_uuid());
				
				$shippings[] = apply_filters('wc_postfinancecheckout_modify_line_item_shipping_session', $this->clean_line_item($line_item), $shipping_rate);
			}
		}
		return $shippings;
	}

	/**
	 * Returns the line items from the given cart
	 *
     * @param WC_Order $order
     * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
     * @throws WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount
     */
	public function get_items_from_order(WC_Order $order){
		$raw = $this->get_raw_items_from_order($order);		
		$items = apply_filters('wc_postfinancecheckout_modify_line_item_order', $raw , $order);		
		$total = apply_filters('wc_postfinancecheckout_modify_total_to_check_order', $order->get_total(), $order);		
		return WC_PostFinanceCheckout_Helper::instance()->cleanup_line_items($items, $total, $order->get_currency());
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
            $amount_including_tax = $item->get_subtotal() + $item->get_subtotal_tax();
            $discount_including_tax = $item->get_total() + $item->get_total_tax();

            $line_item->setAmountIncludingTax($this->round_amount($discount_including_tax, $currency));
            $line_item->setDiscountIncludingTax($this->round_amount($amount_including_tax-$discount_including_tax, $currency));
            $quantity = empty($item->get_quantity())? 1: $item->get_quantity();

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
			
			$attributes = $this->get_base_attributes($product->get_id());
			
			// Only for product variation
			if($product->is_type('variation')){
			    $variation_attributes = $product->get_variation_attributes();
			    foreach(array_keys($variation_attributes) as $attribute_key){
			        $taxonomy = str_replace('attribute_', '', $attribute_key );
		            $attribute_key_cleaned = $this->clean_attribute_key($taxonomy);
			        if(isset($attributes[$attribute_key_cleaned])){
			            $term = get_term_by('slug', wc_get_order_item_meta($item->get_id(), $taxonomy, true), $taxonomy, 'display');
			            $attributes[$attribute_key_cleaned]->setValue($this->fix_length($term->name, 512));
			        }
			    }
			}						
			if(!empty($attributes)){
			    $line_item->setAttributes($attributes);
			}
			
			$items[] = apply_filters('wc_postfinancecheckout_modify_line_item_product_order', $this->clean_line_item($line_item), $item);
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
			
			$fees[] = apply_filters('wc_postfinancecheckout_modify_line_item_fee_order', $this->clean_line_item($line_item), $fee);
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
			
			$shippings[] = apply_filters('wc_postfinancecheckout_modify_line_item_shipping_order', $this->clean_line_item($line_item), $shipping);
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
			$quantity = empty($backend_items[$item_id]['qty'])? 1 : $backend_items[$item_id]['qty'];

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
			
			$attributes = $this->get_base_attributes($product->get_id());
			
			// Only for product variation
			if($product->is_type('variation')){
			    $variation_attributes = $product->get_variation_attributes();
			    foreach(array_keys($variation_attributes) as $attribute_key){
			        $taxonomy = str_replace('attribute_', '', $attribute_key );
			        $attribute_key_cleaned = $this->clean_attribute_key($taxonomy);
			        if(isset($attributes[$attribute_key_cleaned])){
			            $term = get_term_by('slug', wc_get_order_item_meta($item->get_id(), $taxonomy, true), $taxonomy, 'display');
			            $attributes[$attribute_key_cleaned]->setValue($this->fix_length($term->name, 512));
			        }
			    }
			}
			if(!empty($attributes)){
			    $line_item->setAttributes($attributes);
			}
			
			$items[] = apply_filters('wc_postfinancecheckout_modify_line_item_product_backend', $this->clean_line_item($line_item), $item);
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
			
			$line_item->setTaxes($this->get_taxes(WC_Tax::get_rates($fee->get_tax_class())));
			
			if ($amount_including_tax < 0) {
				//There are plugins which create fees with a negative values (used as discounts)
			    $line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT);
			}
			else {
			    $line_item->setType(\PostFinanceCheckout\Sdk\Model\LineItemType::FEE);
			}
			
			$line_item->setUniqueId($fee->get_meta('_postfinancecheckout_unique_line_item_id', true));
			
			$fees[] = apply_filters('wc_postfinancecheckout_modify_line_item_fee_backend', $this->clean_line_item($line_item), $fee);
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
			
			$shippings[] = apply_filters('wc_postfinancecheckout_modify_line_item_shipping_backend', $this->clean_line_item($line_item), $shipping);
		}
		return $shippings;
	}

	/**
	 * Cleans the given line item for it to meet the API's requirements.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\LineItemCreate $line_item
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate
	 */
	protected function clean_line_item(\PostFinanceCheckout\Sdk\Model\LineItemCreate $line_item){
		$line_item->setSku($this->fix_length($line_item->getSku(), 200));
		$line_item->setName($this->fix_length($line_item->getName(), 150));
		return $line_item;
	}

	protected function get_taxes(array $rates_or_ids){
		$tax_rates = array();
		
		foreach (array_keys($rates_or_ids) as $rate_id) {
            $tax_rates[] = new \PostFinanceCheckout\Sdk\Model\TaxCreate(array(
                'title' => WC_Tax::get_rate_label($rate_id),
                'rate' => rtrim(WC_Tax::get_rate_percent($rate_id), '%')
            ));
		}
		
		return $tax_rates;
	}
	
	private function get_base_attributes($product_id){
	    
	    $products_to_check = array($product_id);
	    $current_product = wc_get_product($product_id);
	    
	    /**
	     * @var WC_Product $product
	     */
	    while($current_product && $current_product->get_parent_id('edit') != 0){
	        $products_to_check[] = $current_product->get_parent_id('edit');
	        $current_product = wc_get_product($current_product->get_parent_id('edit'));
	    }
	    $products_to_check = array_reverse($products_to_check);
	    $attributes = array();
	    
	    foreach($products_to_check as $id){
	        $product = wc_get_product($id);

	        $product_attributes = $product->get_attributes('edit');
	        
	        foreach($product_attributes as $key => $object){
	            if(is_a($object, 'WC_Product_Attribute')){
	                if($object->is_taxonomy()){
	                    $attribute_options = WC_PostFinanceCheckout_Entity_Attribute_Options::load_by_attribute_id($object->get_id());
	                    if($attribute_options->get_send()){
    	                    $attribute = new \PostFinanceCheckout\Sdk\Model\LineItemAttributeCreate();
    	                    $attribute->setLabel($this->fix_length(wc_attribute_label($key, $product), 512));
    	                    $terms = $object->get_terms();
    	                    $value = array();
    	                    if($terms != null){
    	                        foreach($terms as $term){
    	                            $value[] = get_term_field('name', $term); 
    	                        }
    	                    }
    	                    $attribute->setValue($this->fix_length(implode('|',$value), 512));
    	                    $attributes[$this->clean_attribute_key($key)] = $attribute;
	                    }
	                }
	            }
	        }	        
	    }
	    return $attributes;
	}
	
	private function clean_attribute_key($key){
	    return preg_replace("/[^a-z0-9_]+/i", "_", $key);
	}
}