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
 * Class WC_PostFinanceCheckout_Packages_Coupon_Discount.
 *
 * This class handles the required unique ids.
 */
class WC_PostFinanceCheckout_Packages_Coupon_Discount {

	const POSTFINANCECHECKOUT_COUPON = 'Coupon';

	/**
	 * Register item coupon discount functions hooks.
	 */
	public static function init() {
		add_filter(
			'woocommerce_checkout_create_order_line_item',
			array(
				__CLASS__,
				'copy_total_coupon_discount_meta_data_to_order_item',
			),
			10,
			4
		);
		add_filter(
			'wc_postfinancecheckout_packages_coupon_discount_totals_including_tax',
			array(
				__CLASS__,
				'get_coupons_discount_totals_including_tax',
			),
			10,
			1
		);
		add_filter(
			'wc_postfinancecheckout_packages_coupon_has_coupon_discounts_applied',
			array(
				__CLASS__,
				'has_coupon_discounts_applied',
			),
			10,
			1
		);
		add_filter(
			'wc_postfinancecheckout_packages_coupon_discounts_applied_by_cart',
			array(
				__CLASS__,
				'get_coupon_discounts_applied_by_cart',
			),
			10,
			1
		);
		add_filter(
			'wc_postfinancecheckout_packages_coupon_percentage_discounts_by_item',
			array(
				__CLASS__,
				'get_coupon_percentage_discount_by_item',
			),
			10,
			1
		);
		add_filter(
			'wc_postfinancecheckout_packages_coupon_process_line_items_with_coupons',
			array(
				__CLASS__,
				'process_line_items_with_coupons',
			),
			10,
			3
		);
	}

	/**
	 * Store cart item total coupon discounts to order item.
	 *
	 * @param WC_Order_Item_Product $item item.
	 * @param mixed                 $cart_item_key cart_item_key.
	 * @param mixed                 $values values.
	 * @param WC_Order              $order order.
	 *
	 * @return WC_Order_Item_Product $item item
	 */
	public static function copy_total_coupon_discount_meta_data_to_order_item( WC_Order_Item_Product $item, $cart_item_key, $values, WC_Order $order = null ) { //phpcs:ignore
		$coupon_discount = apply_filters( 'wc_postfinancecheckout_packages_coupon_percentage_discounts_by_item', $cart_item_key ); //phpcs:ignore
		$item->add_meta_data( '_postfinancecheckout_coupon_discount_line_item_discounts', $coupon_discount );
		return $item;
	}

	/**
	 * Get the total coupons discounts amount applied to the cart.
	 *
	 * @param string $currency The currency code.
	 * @return float|int The total coupons' discounts' amount including tax.
	 */
	public static function get_coupons_discount_totals_including_tax( $currency ) {
		$coupons_discount_total = 0;

		// guard clause if the order doesn't exists, nothing to do here.
		$session = WC()->session;
		if ( ! class_exists( 'WC_Session' ) || ! ( $session instanceof WC_Session ) ) {
			return $coupons_discount_total;
		}

		$order_id = $session->get( 'postfinancecheckout_order_id' );
		$cart = WC()->cart;
		if ( ! class_exists( 'WC_Cart' ) || ! ( $cart instanceof WC_Cart ) ) {
			return $coupons_discount_total;
		}
		if ( $cart->get_cart_contents_count() == 0 && ! is_null( $order_id ) ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$coupons_discount_total += $order->get_total_discount();
			}
		}

		// guard clause if the cart is empty, nothing to do here. This applies to subscription renewals.
		if ( empty( $cart->get_cart_contents_count() ) ) {
			return $coupons_discount_total;
		}

		foreach ( $cart->get_coupon_discount_totals() as $coupon_discount_total ) {
			$coupons_discount_total += WC_PostFinanceCheckout_Helper::instance()->round_amount( $coupon_discount_total, $currency );
		}

		return $coupons_discount_total;
	}

	/**
	 * Check if the cart has any coupons.
	 *
	 * @param string $currency currency.
	 * @return bool
	 */
	public static function has_coupon_discounts_applied( $currency ) {
		$discount = apply_filters( 'wc_postfinancecheckout_packages_coupon_discount_totals_including_tax', $currency ); //phpcs:ignore
		return $discount > 0;
	}

	/**
	 * Get coupons with their discount applied per item based on quantity.
	 *
	 * @param WC_Cart $cart The WooCommerce cart object.
	 * @return array|array[] An array containing coupons with their discount applied per item.
	 */
	public static function get_coupon_discounts_applied_by_cart( WC_Cart $cart ) {
		if ( empty( $cart->get_coupons() ) ) {
			return array();
		}

		$cart_cloned = clone $cart;
		$wp_discount = new WC_Discounts( $cart );

		foreach ( $cart_cloned->get_coupons() as $code => $coupon ) {
			$wc_coupon = new WC_Coupon( $code );
			$wp_discount->apply_coupon( $wc_coupon );
		}

		return $wp_discount->get_discounts();
	}

	/**
	 * Get percentage coupon discount per item based on quantity.
	 *
	 * @param string $item_key The item key.
	 * @return int|mixed The coupon discount for the item.
	 */
	public static function get_coupon_percentage_discount_by_item( string $item_key ) {
		$coupon_discounts_applied = apply_filters( 'wc_postfinancecheckout_packages_coupon_discounts_applied_by_cart', WC()->cart ); //phpcs:ignore
		$coupon_percentage_discount = 0;
		foreach ( $coupon_discounts_applied as $discounts ) {
			if ( ! empty( $discounts[ $item_key ] ) ) {
				$coupon_percentage_discount += $discounts[ $item_key ];
			}
		}
		return $coupon_percentage_discount;
	}

	/**
	 * Calculate total line items if there is a coupon
	 *
	 * @param array  $line_items The array of line items.
	 * @param float  $expected_sum The expected sum.
	 * @param string $currency The current currency.
	 * @return array<mixed> An array containing the effective sum and cleaned line items.
	 */
	public static function process_line_items_with_coupons( array $line_items, float $expected_sum, string $currency ) {
		$line_item_coupons = array();
		$exclude_discounts = true;
		$amount = WC_PostFinanceCheckout_Helper::instance()->get_total_amount_including_tax( $line_items, $exclude_discounts );
		$effective_sum = WC_PostFinanceCheckout_Helper::instance()->round_amount( $amount, $currency );
		// compare the difference with a small tolerance to handle floating point inaccuracies.
		$result_amount = $expected_sum - $effective_sum;

		// coupon line items rounding again.
		foreach ( $line_items as $line_item ) {
			if ( $line_item->getType() == \PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT ) {
				// if there is a difference, a penny, then the coupon is readjust.
				$line_item_coupons[] = clone $line_item;
				$item_amount = $line_item->getAmountIncludingTax() + $result_amount;
				$line_item->setAmountIncludingTax( WC_PostFinanceCheckout_Helper::instance()->round_amount( $item_amount, $currency ) );
			}
		}

		// if there is another difference, like a penny, create a new line item for adjustment.
		$amount_rounded = WC_PostFinanceCheckout_Helper::instance()->get_total_amount_including_tax( $line_items, $exclude_discounts );
		if ( ! self::compare_numbers( $expected_sum, $amount_rounded ) ) {
			$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
			$amount_mismatch = $expected_sum - $amount_rounded;
			$line_item->setAmountIncludingTax( WC_PostFinanceCheckout_Helper::instance()->round_amount( $amount_mismatch, $currency ) );
			$line_item->setName( esc_html__( 'Coupon adjustment', 'woo-postfinancecheckout' ) );
			$line_item->setQuantity( 1 );
			$line_item->setSku( 'coupon adjustment' );
			$line_item->setUniqueId( 'coupon adjustment' );
			$line_item->setShippingRequired( false );
			$line_item->setType( \PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT );
			$line_items[] = $line_item;

			// readjustment of the total amount of items
			// ($amount_mismatch * -1) this changes the sign of the floating number so that it can be subtracted.
			$amount_rounded = $amount_rounded - ( $amount_mismatch * -1 );
		}

		return array(
			// format number with two decimal places.
			'effective_sum' => sprintf( '%.2f', $amount_rounded ),
			'line_items_cleaned' => $line_items,
			'line_item_coupons' => $line_item_coupons,
		);
	}


	/**
	 * Compare whether two floating numbers are equal
	 *
	 * @param float $first_value The first floating-point number to compare.
	 * @param float $second_value The second floating-point number to compare.
	 * @param int   $precision (Optional) The precision to use for rounding. Default is 6.
	 * @return bool Returns true if the two numbers are equal within the specified precision, false otherwise.
	 */
	private static function compare_numbers( float $first_value, float $second_value, int $precision = 6 ) {
		$multiplier = pow( 10, $precision );
		$first_value_rounded = round( $first_value * $multiplier );
		$second_value_rounded = round( $second_value * $multiplier );
		return $first_value_rounded === $second_value_rounded;
	}
}

WC_PostFinanceCheckout_Packages_Coupon_Discount::init();
