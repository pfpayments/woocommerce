<?php
/**
 *
 * WC_PostFinanceCheckout_Entity_Transaction_Info Class
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   wallee AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * This entity holds data about a transaction on the gateway.
 *
 * @method int get_id()
 * @method int get_transaction_id()
 * @method void set_transaction_id(int $id)
 * @method string get_state()
 * @method void set_state(string $state)
 * @method int get_space_id()
 * @method void set_space_id(int $id)
 * @method int get_space_view_id()
 * @method void set_space_view_id(int $id)
 * @method string get_language()
 * @method void set_language(string $language)
 * @method string get_currency()
 * @method void set_currency(string $currency)
 * @method float get_authorization_amount()
 * @method void set_authorization_amount(float $amount)
 * @method string get_image()
 * @method void set_image(string $image)
 * @method string get_image_base()
 * @method void set_image_base(string $image_base)
 * @method object get_labels()
 * @method void set_labels(map[string,string] $labels)
 * @method int get_payment_method_id()
 * @method void set_payment_method_id(int $id)
 * @method int get_connector_id()
 * @method void set_connector_id(int $id)
 * @method int get_order_id()
 * @method void set_order_id(int $id)
 * @method int get_order_mapping_id()
 * @method void set_order_mapping_id(int $id)
 * @method void set_failure_reason(map[string,string] $reasons)
 * @method string get_user_failure_message()
 * @method void set_user_failure_message(string $message)
 */
class WC_PostFinanceCheckout_Entity_Transaction_Info extends WC_PostFinanceCheckout_Entity_Abstract {

	/**
	 * Get field definition.
	 *
	 * @return array
	 */
	protected static function get_field_definition() {
		return array(
			'transaction_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'state' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
			'space_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'space_view_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'language' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
			'currency' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
			'authorization_amount' => WC_PostFinanceCheckout_Entity_Resource_Type::DECIMAL,
			'image' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
			'image_base' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
			'labels' => WC_PostFinanceCheckout_Entity_Resource_Type::OBJECT,
			'payment_method_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'connector_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'order_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'order_mapping_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'failure_reason' => WC_PostFinanceCheckout_Entity_Resource_Type::OBJECT,
			'user_failure_message' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
			'locked_at' => WC_PostFinanceCheckout_Entity_Resource_Type::DATETIME,
		);
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected static function get_table_name() {
		return 'wc_postfinancecheckout_transaction_info';
	}

	/**
	 * Returns the translated failure reason.
	 *
	 * @param mixed $language language.
	 * @return string
	 */
	public function get_failure_reason( $language = null ) {
		$value = $this->get_value( 'failure_reason' );
		if ( empty( $value ) ) {
			return null;
		}
		return WC_PostFinanceCheckout_Helper::instance()->translate( $value, $language );
	}

	/**
	 * Load by order id.
	 *
	 * @param mixed $order_id order id.
	 * @return WC_PostFinanceCheckout_Entity_Transaction_Info
	 */
	public static function load_by_order_id( $order_id ) {
		global $wpdb;
		$result = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %1$s WHERE order_id = %2$d',
				$wpdb->prefix . self::get_table_name(),
				$order_id
			),
			ARRAY_A
		);
		if ( null !== $result ) {
			return new self( $result );
		}
		return new self();
	}

	/**
	 * Load by transaction.
	 *
	 * @param mixed $space_id space id.
	 * @param mixed $transaction_id transaction id.
	 * @return WC_PostFinanceCheckout_Entity_Transaction_Info
	 */
	public static function load_by_transaction( $space_id, $transaction_id ) {
		global $wpdb;
		$result = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %1$s WHERE space_id = %2$d AND transaction_id = %3$d',
				$wpdb->prefix . self::get_table_name(),
				$space_id,
				$transaction_id
			),
			ARRAY_A
		);
		if ( null !== $result ) {
			return new self( $result );
		}
		return new self();
	}


	/**
	 * Load neweest by mapped order id.
	 *
	 * @param mixed $order_id order id.
	 * @return WC_PostFinanceCheckout_Entity_Transaction_Info
	 */
	public static function load_newest_by_mapped_order_id( $order_id ) {
		global $wpdb;
		$result = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %1$s WHERE order_mapping_id = %2$d ORDER BY id DESC',
				$wpdb->prefix . self::get_table_name(),
				$order_id
			),
			ARRAY_A
		);
		if ( null !== $result ) {
			return new self( $result );
		}
		return new self();
	}
}
