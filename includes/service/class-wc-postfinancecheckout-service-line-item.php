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
 * This service provides methods to handle line items.
 */
class WC_PostFinanceCheckout_Service_Line_Item extends WC_PostFinanceCheckout_Service_Abstract {

	/**
	 * Returns the line items from the given cart
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 * @throws WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount.
	 */
	public function get_items_from_session() {
		$currency = get_woocommerce_currency();
		$cart = WC()->cart;
		$cart->calculate_totals();

		$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
		$packages = WC()->shipping->get_packages();

		$items = $this->create_product_line_items_from_session( $cart, $currency );
		$fees = $this->create_fee_lines_items_from_session( $cart, $currency );
		$shipping = $this->create_shipping_line_items_from_session( $packages, $chosen_methods, $currency );
		$coupons = $this->create_coupons_line_items_from_session();
		$combined = array_merge( $items, $fees, $shipping, $coupons );

		return WC_PostFinanceCheckout_Helper::instance()->cleanup_line_items( $combined, $cart->total, $currency );
	}

	/**
	 * Translate item language.
	 *
	 * @param mixed $items items.
	 * @return array
	 */
	private function translate_item_language( $items ) {
		if ( ! class_exists( 'TRP_Translate_Press' ) ) {
			return array();
		}
		if ( empty( $items ) ) {
			return array();
		}

		$translations = array();
		$strings = array();
		$postfinancecheckout_trp_language = '';

		// TRP_LANGUAGE is a global variable that belongs to the plugin "Translate Multilingual sites – TranslatePress".
		if ( isset( $GLOBALS['TRP_LANGUAGE'] ) ) {
			$postfinancecheckout_trp_language = $GLOBALS['TRP_LANGUAGE'];
		}

		// have to get all the item names so we can search for them in the TRP query.
		// items can be cart products or order items.
		foreach ( $items as $item ) {
			$strings[] = $item->get_name();
		}

		$trp = TRP_Translate_Press::get_trp_instance();

		if ( is_null( $trp ) ) {
			return array();
		}

		$query = $trp->get_component( 'query' );
		// we get the translations from the dictionary.
		$dictionary = $query->get_string_rows( '', $strings, $postfinancecheckout_trp_language ); //phpcs:ignore
		if ( is_array( $dictionary ) ) {
			foreach ( $dictionary as $row ) {
				$translations[ $row->original ] = $row->translated;
			}
		}

		return $translations;
	}

	/**
	 * Creates the line items for the products
	 *
	 * @param WC_Cart $cart cart.
	 * @param mixed   $currency currency.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_product_line_items_from_session( WC_Cart $cart, $currency ) {
		$items = array();
		foreach ( $cart->get_cart() as $values ) {
			/**
			 * Product.
			 *
			 * @var WC_Product $product product.
			 */
			$product = $values['data'];
			$amount_including_tax = $values['line_subtotal'] + $values['line_subtotal_tax'];

			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			$line_item->setAmountIncludingTax( $this->round_amount( $amount_including_tax, $currency ) );
			$line_item->setName( $this->fix_length( $product->get_name(), 150 ) );

			$quantity = empty( $values['quantity'] ) ? 1 : $values['quantity'];

			$line_item->setQuantity( $quantity );
			$line_item->setShippingRequired( ! $product->get_virtual() );

			$sku = $product->get_sku();
			if ( empty( $sku ) ) {
				$sku = $product->get_name();
			}
			$sku = str_replace(
				array(
					"\n",
					"\r",
				),
				'',
				$sku
			);
			$line_item->setSku( $sku, 200 );
			$line_item->setTaxes( $this->get_taxes( WC_Tax::get_rates( $product->get_tax_class() ) ) );
			$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::PRODUCT );
			$line_item->setUniqueId( WC_PostFinanceCheckout_Unique_Id::get_uuid() );

			$attributes = $this->get_base_attributes( $product->get_id() );
			foreach ( $values['variation'] as $key => $value ) {
				if ( strpos( $key, 'attribute_' ) === 0 ) {
					$taxonomy = substr( $key, 10 );
					$attribute_key_cleaned = $this->clean_attribute_key( $taxonomy );
					if ( isset( $attributes[ $attribute_key_cleaned ] ) ) {
						$term = get_term_by( 'slug', $value, $taxonomy, 'display' );
						$attributes[ $attribute_key_cleaned ]->setValue( $this->fix_length( $term->name, 512 ) );
					}
				}
			}
			if ( ! empty( $attributes ) ) {
				$line_item->setAttributes( $attributes );
			}

			$items[] = apply_filters( 'wc_postfinancecheckout_modify_line_item_product_session', $this->clean_line_item( $line_item ), $values ); //phpcs:ignore
		}
		return $items;
	}

	/**
	 * Returns the line items for fees.
	 *
	 * @param WC_Cart $cart cart.
	 * @param mixed   $currency currency.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_fee_lines_items_from_session( WC_Cart $cart, $currency ) {
		$fees = array();
		foreach ( $cart->get_fees() as $fee ) {
			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();

			$amount_including_tax = $fee->amount + $fee->tax;

			$line_item->setAmountIncludingTax( $this->round_amount( $amount_including_tax, $currency ) );
			$line_item->setName( $this->fix_length( $fee->name, 150 ) );
			$line_item->setQuantity( 1 );
			$line_item->setShippingRequired( false );

			$sku = $fee->name;
			$sku = str_replace(
				array(
					"\n",
					"\r",
				),
				'',
				$sku
			);
			$line_item->setSku( $sku, 200 );

			$line_item->setTaxes( $this->get_taxes( WC_Tax::get_rates( $fee->tax_class ) ) );

			if ( $amount_including_tax < 0 ) {
				// There are plugins which create fees with a negative values (used as discounts).
				$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT );
				$line_item->setUniqueId( 'discount-' . $fee->id );
			} else {
				$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::FEE );
				$line_item->setUniqueId( 'fee-' . $fee->id );
			}
			$fees[] = apply_filters( 'wc_postfinancecheckout_modify_line_item_fee_session', $this->clean_line_item( $line_item ), $fee ); //phpcs:ignore
		}
		return $fees;
	}

	/**
	 * Returns the line items for the shipping costs.
	 *
	 * @param object[] $packages packages.
	 * @param string[] $chosen_methods chosen methods.
	 * @param String   $currency currency.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_shipping_line_items_from_session( $packages, $chosen_methods, $currency ) {
		$shippings = array();

		foreach ( $packages as $package_key => $package ) {
			if ( isset( $chosen_methods[ $package_key ], $package['rates'][ $chosen_methods[ $package_key ] ] ) ) {
				$shipping_rate = $package['rates'][ $chosen_methods[ $package_key ] ];

				$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();

				$amount_including_tax = $shipping_rate->cost + $shipping_rate->get_shipping_tax();

				$line_item->setAmountIncludingTax( $this->round_amount( $amount_including_tax, $currency ) );
				$line_item->setName( $this->fix_length( $shipping_rate->get_label(), 150 ) );
				$line_item->setQuantity( 1 );
				$line_item->setShippingRequired( false );

				$sku = $shipping_rate->get_label();
				$sku = str_replace(
					array(
						"\n",
						"\r",
					),
					'',
					$sku
				);
				$line_item->setSku( $sku );

				$line_item->setTaxes( $this->get_taxes( WC_Tax::get_shipping_tax_rates() ) );

				$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::SHIPPING );
				$line_item->setUniqueId( WC_PostFinanceCheckout_Unique_Id::get_uuid() );

				$shippings[] = apply_filters( 'wc_postfinancecheckout_modify_line_item_shipping_session', $this->clean_line_item( $line_item ), $shipping_rate ); //phpcs:ignore
			}
		}
		return $shippings;
	}

	/**
	 * Returns the line items for coupons.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_coupons_line_items_from_session() {
		$coupons = array();
		$cart = WC()->cart;

		if ( empty( $cart->get_applied_coupons() ) ) {
			return $coupons;
		}

		$discount = $cart->get_discount_total() + $cart->get_discount_tax();
		$line_items = $this->create_coupon_line_items( current( $cart->get_coupons() ), $discount );
		if ( is_array( $line_items ) ) {
			$coupons = array_merge( $coupons, $line_items );
		}
		return $coupons;
	}

	/**
	 * Create coupon line item
	 *
	 * @param WC_Coupon|WC_Order_Item_Coupon $coupon The coupon object.
	 * @param float $total_discount_amount The amount of the coupon.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate|null The line item created or null if the coupon is not valid.
	 */
	private function create_coupon_line_items( $coupon, float $total_discount_amount = 0 ) {
		if ( ! $coupon instanceof WC_Coupon && ! $coupon instanceof WC_Order_Item_Coupon ) {
			return array();
		}

		$coupon = new WC_Coupon( $coupon->get_code() );
		$sku = $this->fix_length( $coupon->get_discount_type(), 150 );
		$sku = str_replace( array( "\n", "\r" ), '', $sku );

		// Calculate the proportional discount amounts for each tax rate.
		$discounts = $this->calculate_discount_rates_proportionally( $total_discount_amount );

		$line_items = array();

		foreach ( $discounts as $discount ) {
			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			$line_item->setAmountIncludingTax( $discount['amount'] * -1 );
			$line_item->setName( sprintf( '%s: %s (%s%% tax)', WC_PostFinanceCheckout_Packages_Coupon_Discount::POSTFINANCECHECKOUT_COUPON, $coupon->get_code(), $discount['rate_id'] ) );
			$line_item->setQuantity( 1 );
			$line_item->setShippingRequired( false );
			$line_item->setSku( $sku, 200 );
			$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT );
			$line_item->setUniqueId( 'coupon-' . $coupon->get_id() . '-' . $discount['rate_id'] );

			$tax_rate = new \PostFinanceCheckout\Sdk\Model\TaxCreate(
				array(
					'title' => 'Discount Tax: ' . $discount['rate_id'],
					'rate' => $discount['rate_id'],
				)
			);

			$line_item->setTaxes( array( $tax_rate ) );
			$line_items[] = $line_item;
		}

		return $line_items;
	}

	/**
	 * Calculate discount rates
	 *
	 * @param float $total_discount_amount total_discount_amount.
	 * @return array
	 */
	private function calculate_discount_rates_proportionally( float $total_discount_amount ): array {
		$cart = WC()->cart;
		$tax_totals = array();
		$total_amount = 0;

		foreach ( $cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'];
			$tax_class = $product->get_tax_class();
			$tax_rates_class = WC_Tax::get_rates( $tax_class );

			foreach ( $tax_rates_class as $rate ) {
				$rate_id = $rate['rate'];
				$line_total_with_tax = $cart_item['line_total'] + $cart_item['line_tax'];

				if ( ! isset( $tax_totals[ $rate_id ] ) ) {
					$tax_totals[ $rate_id ] = array(
						'total' => 0,
						'rate_percentage' => $rate_id,
					);
				}

				$tax_totals[ $rate_id ]['total'] += $line_total_with_tax;
				$total_amount += $line_total_with_tax;
			}
		}

		$discounts = array();

		foreach ( $tax_totals as $rate_id => $data ) {
				$proportional_discount_amount = $total_discount_amount * ( $data['total'] / $total_amount );

				$discounts[] = array(
					'rate_id' => $rate_id,
					'amount' => $proportional_discount_amount,
					'rate_percentage' => $rate_id,
				);
		}

		return $discounts;
	}
	/**
	 * Returns the line items from the given cart
	 *
	 * @param WC_Order $order order.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 * @throws WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount.
	 */
	public function get_items_from_order( WC_Order $order ) {
		$raw = $this->get_raw_items_from_order( $order );
		$items = apply_filters( 'wc_postfinancecheckout_modify_line_item_order', $raw, $order ); //phpcs:ignore
		$total = apply_filters( 'wc_postfinancecheckout_modify_total_to_check_order', $order->get_total(), $order ); //phpcs:ignore
		return WC_PostFinanceCheckout_Helper::instance()->cleanup_line_items( $items, $total, $order->get_currency() );
	}

	/**
	 * Get raw items from order.
	 *
	 * @param WC_Order $order oder.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 * @throws Exception Exception.
	 */
	public function get_raw_items_from_order( WC_Order $order ) {
		$items = $this->create_product_line_items_from_order( $order );
		$fees = $this->create_fee_lines_items_from_order( $order );
		$shipping = $this->create_shipping_line_items_from_order( $order );
		$coupons = $this->create_coupons_line_items_from_order( $order );
		return array_merge( $items, $fees, $shipping, $coupons );
	}

	/**
	 * Creates the line items for the products
	 *
	 * @param WC_Order $order order.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 * @throws Exception Exception.
	 */
	protected function create_product_line_items_from_order( WC_Order $order ) {
		$items = array();
		$currency = $order->get_currency();
		$order_items = $order->get_items();
		$cart_items = array();
		$translations = array();

		foreach ( $order_items as $cart_item ) {
			$cart_items[] = $cart_item;
		}

		$translations = $this->translate_item_language( $order_items );

		foreach ( $order_items as $item ) {
			/**
			 * Item.
			 *
			 * @var WC_Order_Item_Product $item
			 */

			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			$amount_including_tax = $item->get_subtotal() + $item->get_subtotal_tax();

			$line_item->setAmountIncludingTax( $this->round_amount( $amount_including_tax, $currency ) );
			$quantity = empty( $item->get_quantity() ) ? 1 : $item->get_quantity();

			$line_item->setQuantity( $quantity );

			$product = $item->get_product();
			$product_name = $item->get_name();
			$name = isset( $translations[ $product_name ] ) && ! empty( $translations[ $product_name ] ) ? $translations[ $product_name ] : $product_name;
			$sku = null;
			if ( is_bool( $product ) ) {
				$line_item->setName( $this->fix_length( $name, 150 ) );
				$line_item->setShippingRequired( true );
				$sku = $item->get_name();
			} else {
				$line_item->setName( $this->fix_length( $name, 150 ) );
				$line_item->setShippingRequired( ! $product->get_virtual() );
				$sku = $product->get_sku();
				if ( empty( $sku ) ) {
					$sku = $product->get_name();
				}
			}

			$sku = str_replace(
				array(
					"\n",
					"\r",
				),
				'',
				$sku
			);
			$line_item->setSku( $sku, 200 );

			$line_item->setTaxes( $this->get_taxes( WC_Tax::get_rates( $item->get_tax_class() ) ) );

			$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::PRODUCT );
			$line_item->setUniqueId( $item->get_meta( '_postfinancecheckout_unique_line_item_id', true ) );

			if ( ! is_bool( $product ) ) {
				$attributes = $this->get_base_attributes( $product->get_id() );

				// Only for product variation.
				if ( $product->is_type( 'variation' ) ) {
					$variation_attributes = $product->get_variation_attributes();
					foreach ( array_keys( $variation_attributes ) as $attribute_key ) {
						$taxonomy = str_replace( 'attribute_', '', $attribute_key );
						$attribute_key_cleaned = $this->clean_attribute_key( $taxonomy );
						if ( isset( $attributes[ $attribute_key_cleaned ] ) ) {
							$term = get_term_by( 'slug', wc_get_order_item_meta( $item->get_id(), $taxonomy, true ), $taxonomy, 'display' );
							$attributes[ $attribute_key_cleaned ]->setValue( $this->fix_length( $term->name, 512 ) );
						}
					}
				}
				if ( ! empty( $attributes ) ) {
					$line_item->setAttributes( $attributes );
				}
			}

			$items[] = apply_filters( 'wc_postfinancecheckout_modify_line_item_product_order', $this->clean_line_item( $line_item ), $item ); //phpcs:ignore
		}
		return $items;
	}

	/**
	 * Returns the line items for fees.
	 *
	 * @param WC_Order $order order.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_fee_lines_items_from_order( WC_Order $order ) {
		$fees = array();
		$currency = $order->get_currency();

		foreach ( $order->get_fees() as $fee ) {
			/**
			 * Fee.
			 *
			 * @var WC_Order_Item_Fee $fee
			 */

			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();

			$amount_including_tax = $fee->get_total() + $fee->get_total_tax();

			$line_item->setAmountIncludingTax( $this->round_amount( $amount_including_tax, $currency ) );
			$line_item->setName( $this->fix_length( $fee->get_name(), 150 ) );
			$line_item->setQuantity( 1 );
			$line_item->setShippingRequired( false );

			$sku = $fee->get_name();
			$sku = str_replace(
				array(
					"\n",
					"\r",
				),
				'',
				$sku
			);
			$line_item->setSku( $sku, 200 );

			$line_item->setTaxes( $this->get_taxes( WC_Tax::get_rates( $fee->get_tax_class() ) ) );

			if ( $amount_including_tax < 0 ) {
				// There are plugins which create fees with a negative values (used as discounts).
				$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT );
			} else {
				$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::FEE );
			}

			$line_item->setUniqueId( $fee->get_meta( '_postfinancecheckout_unique_line_item_id', true ) );

			$fees[] = apply_filters( 'wc_postfinancecheckout_modify_line_item_fee_order', $this->clean_line_item( $line_item ), $fee ); //phpcs:ignore
		}
		return $fees;
	}

	/**
	 * Returns the line items for the shipping costs.
	 *
	 * @param WC_Order $order order.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 */
	protected function create_shipping_line_items_from_order( WC_Order $order ) {
		$shippings = array();
		$currency = $order->get_currency();

		foreach ( $order->get_shipping_methods() as $shipping ) {
			/**
			 * Shipping.
			 *
			 * @var WC_Order_Item_Shipping $shipping
			 */

			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();

			$amount_including_tax = $shipping->get_total() + $shipping->get_total_tax();

			$line_item->setAmountIncludingTax( $this->round_amount( $amount_including_tax, $currency ) );
			$line_item->setName( $this->fix_length( $shipping->get_method_title(), 150 ) );
			$line_item->setQuantity( 1 );
			$line_item->setShippingRequired( false );
			$sku = $shipping->get_method_title();
			$sku = str_replace(
				array(
					"\n",
					"\r",
				),
				'',
				$sku
			);
			$line_item->setSku( $sku );
			$taxes = $shipping->get_taxes();
			$line_item->setTaxes( $this->get_taxes( $taxes['total'] ) );

			$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::SHIPPING );
			$line_item->setUniqueId( $shipping->get_meta( '_postfinancecheckout_unique_line_item_id', true ) );

			$shippings[] = apply_filters( 'wc_postfinancecheckout_modify_line_item_shipping_order', $this->clean_line_item( $line_item ), $shipping ); //phpcs:ignore
		}
		return $shippings;
	}

	/**
	 * Creates line items for coupons from the order.
	 *
	 * @param WC_Order $order The order object.
	 * @return array An array of coupon line items.
	 */
	protected function create_coupons_line_items_from_order( WC_Order $order ) {
		$coupons = array();
		if ( empty( $order->get_coupons() ) ) {
			return $coupons;
		}

		// all wp coupons available.
		$discount = 0;
		foreach ( $order->get_coupons() as $coupon ) {
			/** @var WC_Order_Item_Coupon $coupon */ //phpcs:ignore
			$discount += (float) $coupon->get_discount() + (float) $coupon->get_discount_tax();
		}

		$line_items = $this->create_coupon_line_items( current( $order->get_coupons() ), $discount );
		foreach ( $line_items as $line_item ) {
			$coupons[] = $this->clean_line_item( $line_item );
		}
		return $coupons;
	}

	/**
	 * Get items from backend.
	 *
	 * @param array $backend_items backend items.
	 * @param mixed $amount amount.
	 * @param WC_Order $order order.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 * @throws WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount.
	 */
	public function get_items_from_backend( array $backend_items, $amount, WC_Order $order ) {
		$items = $this->create_product_line_items_from_backend( $backend_items, $order );
		$fees = $this->create_fee_lines_items_from_backend( $backend_items, $order );
		$shipping = $this->create_shipping_line_items_from_backend( $backend_items, $order );
		$coupons = $this->create_coupons_line_items_from_order( $order );
		$combined = array_merge( $items, $fees, $shipping, $coupons );

		return WC_PostFinanceCheckout_Helper::instance()->cleanup_line_items( $combined, $amount, $order->get_currency() );
	}

	/**
	 * Creates the line items for the products
	 *
	 * @param array $backend_items backend items.
	 * @param WC_Order $order order.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 * @throws Exception Exception.
	 */
	protected function create_product_line_items_from_backend( array $backend_items, WC_Order $order ) {
		$items = array();
		$currency = $order->get_currency();
		foreach ( $order->get_items() as $item_id => $item ) {
			if ( ! isset( $backend_items[ $item_id ] ) ) {
				continue;
			}
			/**
			 * Item.
			 *
			 * @var WC_Order_Item_Product $item
			 */

			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();

			$tax = 0;
			$discounts = 0;
			if ( isset( $backend_items[ $item_id ]['completion_tax'] ) ) {
				$tax = array_sum( $backend_items[ $item_id ]['completion_tax'] );
			}

			// At this point, if there is a discount applied by coupon, the price already has the discount applied,
			// and to be able to send the discount to the portal, it is necessary to restore the discounted amount,
			// the original price must be restored before being applied, otherwise it would be discounting twice in the portal.
			$item_data_coupon = $item->get_meta( '_postfinancecheckout_coupon_discount_line_item_discounts' );
			if ( ! empty( $item_data_coupon ) ) {
				$discount_tax = $item->get_subtotal_tax() - $item->get_total_tax();
				$discount_amount = $item->get_subtotal() - $item->get_total();
				$discounts = $discount_tax + $discount_amount;
			}

			$amount_including_tax = $backend_items[ $item_id ]['completion_total'] + $tax + $discounts;

			$line_item->setAmountIncludingTax( $this->round_amount( $amount_including_tax, $currency ) );
			$quantity = empty( $backend_items[ $item_id ]['qty'] ) ? 1 : $backend_items[ $item_id ]['qty'];

			$line_item->setQuantity( $quantity );

			$product = $item->get_product();
			$line_item->setName( $this->fix_length( $product->get_name(), 150 ) );
			$line_item->setShippingRequired( ! $product->get_virtual() );

			$sku = $product->get_sku();
			if ( empty( $sku ) ) {
				$sku = $product->get_name();
			}
			$sku = str_replace(
				array(
					"\n",
					"\r",
				),
				'',
				$sku
			);
			$line_item->setSku( $sku, 200 );

			$line_item->setTaxes( $this->get_taxes( WC_Tax::get_rates( $item->get_tax_class() ) ) );

			$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::PRODUCT );
			$line_item->setUniqueId( $item->get_meta( '_postfinancecheckout_unique_line_item_id', true ) );

			$attributes = $this->get_base_attributes( $product->get_id() );

			// Only for product variation.
			if ( $product->is_type( 'variation' ) ) {
				$variation_attributes = $product->get_variation_attributes();
				foreach ( array_keys( $variation_attributes ) as $attribute_key ) {
					$taxonomy = str_replace( 'attribute_', '', $attribute_key );
					$attribute_key_cleaned = $this->clean_attribute_key( $taxonomy );
					if ( isset( $attributes[ $attribute_key_cleaned ] ) ) {
						$term = get_term_by( 'slug', wc_get_order_item_meta( $item->get_id(), $taxonomy, true ), $taxonomy, 'display' );
						$attributes[ $attribute_key_cleaned ]->setValue( $this->fix_length( $term->name, 512 ) );
					}
				}
			}
			if ( ! empty( $attributes ) ) {
				$line_item->setAttributes( $attributes );
			}

			$items[] = apply_filters( 'wc_postfinancecheckout_modify_line_item_product_backend', $this->clean_line_item( $line_item ), $item ); //phpcs:ignore
		}
		return $items;
	}


	/**
	 * Returns the line items for fees.
	 *
	 * @param array $backend_items backend items.
	 * @param WC_Order $order order.
	 * @return array
	 */
	protected function create_fee_lines_items_from_backend( array $backend_items, WC_Order $order ) {
		$fees = array();
		$currency = $order->get_currency();

		foreach ( $order->get_fees() as $fee_id => $fee ) {

			if ( ! isset( $backend_items[ $fee_id ] ) ) {
				continue;
			}

			$tax = 0;
			if ( isset( $backend_items[ $fee_id ]['completion_tax'] ) ) {
				$tax = array_sum( $backend_items[ $fee_id ]['completion_tax'] );
			}

			if ( 0 == $backend_items[ $fee_id ]['completion_total'] + $tax ) {
				continue;
			}
			/**
			 * Item.
			 *
			 * @var WC_Order_Item_Product $item
			 */

			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();

			$amount_including_tax = $backend_items[ $fee_id ]['completion_total'] + $tax;

			$line_item->setAmountIncludingTax( $this->round_amount( $amount_including_tax, $currency ) );
			$line_item->setName( $this->fix_length( $fee->get_name(), 150 ) );
			$line_item->setQuantity( 1 );
			$line_item->setShippingRequired( false );

			$sku = $fee->get_name();
			$sku = str_replace(
				array(
					"\n",
					"\r",
				),
				'',
				$sku
			);
			$line_item->setSku( $sku, 200 );

			$line_item->setTaxes( $this->get_taxes( WC_Tax::get_rates( $fee->get_tax_class() ) ) );

			if ( $amount_including_tax < 0 ) {
				// There are plugins which create fees with a negative values (used as discounts).
				$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT );
			} else {
				$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::FEE );
			}

			$line_item->setUniqueId( $fee->get_meta( '_postfinancecheckout_unique_line_item_id', true ) );

			$fees[] = apply_filters( 'wc_postfinancecheckout_modify_line_item_fee_backend', $this->clean_line_item( $line_item ), $fee ); //phpcs:ignore
		}
		return $fees;
	}


	/**
	 * Returns the line items for the shipping costs.
	 *
	 * @param array $backend_items backend items.
	 * @param WC_Order $order order.
	 * @return array
	 */
	protected function create_shipping_line_items_from_backend( array $backend_items, WC_Order $order ) {
		$shippings = array();
		$currency = $order->get_currency();

		foreach ( $order->get_shipping_methods() as $shipping_id => $shipping ) {
			if ( ! isset( $backend_items[ $shipping_id ] ) ) {
				continue;
			}
			$tax = 0;
			if ( isset( $backend_items[ $shipping_id ]['completion_tax'] ) ) {
				$tax = array_sum( $backend_items[ $shipping_id ]['completion_tax'] );
			}
			if ( 0 == $backend_items[ $shipping_id ]['completion_total'] + $tax ) {
				continue;
			}
			/**
			 * Shipping.
			 *
			 * @var WC_Order_Item_Shipping $shipping
			 */

			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();

			$amount_including_tax = $backend_items[ $shipping_id ]['completion_total'] + $tax;

			$line_item->setAmountIncludingTax( $this->round_amount( $amount_including_tax, $currency ) );
			$line_item->setName( $this->fix_length( $shipping->get_method_title(), 150 ) );
			$line_item->setQuantity( 1 );
			$line_item->setShippingRequired( false );
			$sku = $shipping->get_method_title();
			$sku = str_replace(
				array(
					"\n",
					"\r",
				),
				'',
				$sku
			);
			$line_item->setSku( $sku );

			$taxes = $shipping->get_taxes();
			$line_item->setTaxes( $this->get_taxes( $taxes['total'] ) );

			$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::SHIPPING );
			$line_item->setUniqueId( $shipping->get_meta( '_postfinancecheckout_unique_line_item_id', true ) );

			$shippings[] = apply_filters( 'wc_postfinancecheckout_modify_line_item_shipping_backend', $this->clean_line_item( $line_item ), $shipping ); //phpcs:ignore
		}
		return $shippings;
	}


	/**
	 * Cleans the given line item for it to meet the API's requirements.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\LineItemCreate $line_item line item.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate
	 */
	protected function clean_line_item( \PostFinanceCheckout\Sdk\Model\LineItemCreate $line_item ) {
		$line_item->setSku( $this->fix_length( $line_item->getSku(), 200 ) );
		$line_item->setName( $this->fix_length( $line_item->getName(), 150 ) );
		return $line_item;
	}

	/**
	 * Get taxes.
	 *
	 * @param array $rates_or_ids rates or ids.
	 * @return array
	 */
	protected function get_taxes( array $rates_or_ids ) {
		$tax_rates = array();

		foreach ( array_keys( $rates_or_ids ) as $rate_id ) {
			$tax_rates[] = new \PostFinanceCheckout\Sdk\Model\TaxCreate(
				array(
					'title' => WC_Tax::get_rate_label( $rate_id ),
					'rate' => rtrim( WC_Tax::get_rate_percent( $rate_id ), '%' ),
				)
			);
		}

		return $tax_rates;
	}

	/**
	 * Get base attributes.
	 *
	 * @param mixed $product_id product id.
	 * @return array
	 */
	private function get_base_attributes( $product_id ) {

		$products_to_check = array( $product_id );
		$current_product = wc_get_product( $product_id );

		/**
		 * Product.
		 *
		 * @var WC_Product $product
		 */
		while ( $current_product && $current_product->get_parent_id( 'edit' ) != 0 ) {
			$products_to_check[] = $current_product->get_parent_id( 'edit' );
			$current_product = wc_get_product( $current_product->get_parent_id( 'edit' ) );
		}
		$products_to_check = array_reverse( $products_to_check );
		$attributes = array();

		foreach ( $products_to_check as $id ) {
			$product = wc_get_product( $id );

			$product_attributes = $product->get_attributes( 'edit' );

			// code block to do check for ean code for invoice.
			$wpm_gtin_code_key = '_wpm_gtin_code';
			$wpm_gtin_code = $product->get_meta( $wpm_gtin_code_key );

			if ( $wpm_gtin_code ) {
				$wpm_gtin_attribute = new \PostFinanceCheckout\Sdk\Model\LineItemAttributeCreate();
				$wpm_gtin_attribute->setLabel( 'EAN' );
				$wpm_gtin_attribute->setValue( $wpm_gtin_code );
				$attributes['ean_code'] = $wpm_gtin_attribute;
			}

			foreach ( $product_attributes as $key => $object ) {
				if ( is_a( $object, 'WC_Product_Attribute' ) ) {
					if ( $object->is_taxonomy() ) {
						$attribute_options = WC_PostFinanceCheckout_Entity_Attribute_Options::load_by_attribute_id( $object->get_id() );
						if ( $attribute_options->get_send() ) {
							$attribute = new \PostFinanceCheckout\Sdk\Model\LineItemAttributeCreate();
							$label = wc_attribute_label( $key, $product );
							if ( empty( $label ) ) {
								$label = 'attribute';
							}
							$attribute->setLabel( $this->fix_length( $label, 512 ) );

							$terms = $object->get_terms();
							$value = array();
							if ( null != $terms || ! empty( $terms ) ) {
								foreach ( $terms as $term ) {
									$value[] = get_term_field( 'name', $term );
								}
							} else {
								continue;
							}

							$attribute->setValue( $this->fix_length( implode( '|', $value ), 512 ) );
							$attributes[ $this->clean_attribute_key( $key ) ] = $attribute;
						}
					}
				}
			}
		}

		return $attributes;
	}

	/**
	 * Clean attribute key.
	 *
	 * @param mixed $key key.
	 * @return array|string|string[]|null
	 */
	private function clean_attribute_key( $key ) {
		return preg_replace( '/[^a-z0-9_]+/i', '_', $key );
	}
}
