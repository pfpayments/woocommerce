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
 * This entity holds data about a token on the gateway.
 *
 * @method int get_id()
 * @method int get_token_id()
 * @method void set_token_id(int $id)
 * @method string get_state()
 * @method void set_state(string $state)
 * @method int get_space_id()
 * @method void set_space_id(int $id)
 * @method string get_name()
 * @method void set_name(string $name)
 * @method int get_customer_id()
 * @method void set_customer_id(int $id)
 * @method int get_payment_method_id()
 * @method void set_payment_method_id(int $id)
 * @method int get_connector_id()
 * @method void set_connector_id(int $id)
 */
class WC_PostFinanceCheckout_Entity_Token_Info extends WC_PostFinanceCheckout_Entity_Abstract {

	/**
	 * Get field definition.
	 *
	 * @return array
	 */
	protected static function get_field_definition() {
		return array(
			'token_id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER,
			'state' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_STRING,
			'space_id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER,
			'name' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_STRING,
			'customer_id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER,
			'payment_method_id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER,
			'connector_id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER,
		);
	}

	/**
	 * Get tble name.
	 *
	 * @return string
	 */
	protected static function get_table_name() {
		return 'postfinancecheckout_token_info';
	}

	/**
	 * Load by token.
	 *
	 * @param mixed $space_id space id.
	 * @param mixed $token_id token id.
	 * @return WC_PostFinanceCheckout_Entity_Token_Info
	 */
	public static function load_by_token( $space_id, $token_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::get_table_name();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table WHERE space_id = %d AND token_id = %d",
				$space_id,
				$token_id
			),
			ARRAY_A
		);
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		if ( null !== $result ) {
			return new self( $result );
		}
		return new self();
	}
}
