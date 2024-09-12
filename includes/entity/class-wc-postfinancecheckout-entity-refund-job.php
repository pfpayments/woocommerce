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
 * This entity holds data about a transaction on the gateway.
 *
 * @method int get_id()
 * @method int get_external_id()
 * @method void set_external_id(int $id)
 * @method string get_state()
 * @method void set_state(string $state)
 * @method int get_space_id()
 * @method void set_space_id(int $id)
 * @method int get_transaction_id()
 * @method void set_transaction_id(int $id)
 * @method int get_order_id()
 * @method void set_order_id(int $id)
 * @method int get_wc_refund_id()
 * @method void set_wc_refund_id(int $id)
 * @method \PostFinanceCheckout\Sdk\Model\RefundCreate get_refund()
 * @method void set_refund( \PostFinanceCheckout\Sdk\Model\RefundCreate  $refund)
 * @method void set_failure_reason(map[string,string] $reasons)
 */
class WC_PostFinanceCheckout_Entity_Refund_Job extends WC_PostFinanceCheckout_Entity_Abstract {
	const POSTFINANCECHECKOUT_STATE_CREATED = 'created';
	const POSTFINANCECHECKOUT_STATE_SENT = 'sent';
	const POSTFINANCECHECKOUT_STATE_PENDING = 'pending';
	const POSTFINANCECHECKOUT_STATE_SUCCESS = 'success';
	const POSTFINANCECHECKOUT_STATE_FAILURE = 'failure';

	/**
	 * Get field definition.
	 *
	 * @return array
	 */
	protected static function get_field_definition() {
		return array(
			'external_id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_STRING,
			'state' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_STRING,
			'space_id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER,
			'transaction_id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER,
			'order_id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER,
			'wc_refund_id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER,
			'refund' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_OBJECT,
			'failure_reason' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_OBJECT,
		);
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected static function get_table_name() {
		return 'postfinancecheckout_refund_job';
	}


	/**
	 * Returns the translated failure reason.
	 *
	 * @param mixed $language language.
	 * @return string|null
	 */
	public function get_failure_reason( $language = null ) {
		$value = $this->get_value( 'failure_reason' );
		if ( empty( $value ) ) {
			return null;
		}

		return WC_PostFinanceCheckout_Helper::instance()->translate( $value, $language );
	}

	/**
	 * Load by external Id.
	 *
	 * @param mixed $space_id space id.
	 * @param mixed $external_id external id.
	 * @return WC_PostFinanceCheckout_Entity_Refund_Job
	 */
	public static function load_by_external_id( $space_id, $external_id ) {
		global $wpdb;

		$table = $wpdb->prefix . self::get_table_name();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE space_id = %d AND external_id = %s",
				$space_id,
				$external_id
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.
		if ( null !== $result ) {
			return new self( $result );
		}
		return new self();
	}

	/**
	 * Count running refund for transaction.
	 *
	 * @param mixed $space_id space id.
	 * @param mixed $transaction_id transaction id.
	 * @return string|null
	 */
	public static function count_running_refund_for_transaction( $space_id, $transaction_id ) {
		global $wpdb;

		$table = $wpdb->prefix . self::get_table_name();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->get_var( //phpcs:ignore
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE space_id = %d AND transaction_id = %d AND state != %s AND state != %s",
				$space_id,
				$transaction_id,
				self::POSTFINANCECHECKOUT_STATE_SUCCESS,
				self::POSTFINANCECHECKOUT_STATE_FAILURE
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.
		return $result;
	}

	/**
	 * Load running refund for transaction.
	 *
	 * @param mixed $space_id Space id.
	 * @param mixed $transaction_id transaction id.
	 * @return WC_PostFinanceCheckout_Entity_Refund_Job
	 */
	public static function load_running_refund_for_transaction( $space_id, $transaction_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::get_table_name();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->get_row( //phpcs:ignore
			$wpdb->prepare(
				"SELECT * FROM $table WHERE space_id = %d AND transaction_id = %d AND state != %s AND state != %s",
				$space_id,
				$transaction_id,
				self::POSTFINANCECHECKOUT_STATE_SUCCESS,
				self::POSTFINANCECHECKOUT_STATE_FAILURE
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.
		if ( null !== $result ) {
			return new self( $result );
		}
		return new self();
	}

	/**
	 * Load refunds for order.
	 *
	 * @param mixed $order_id order id.
	 * @return array
	 */
	public static function load_refunds_for_order( $order_id ) {
		global $wpdb;
		$table = $wpdb->prefix . self::get_table_name();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$db_results = $wpdb->get_results( //phpcs:ignore
			$wpdb->prepare(
				"SELECT * FROM $table WHERE order_id = %d",
				$order_id
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.
		$result = array();
		if ( is_array( $db_results ) ) {
			foreach ( $db_results as $object_values ) {
				$result[] = new self( $object_values );
			}
		}
		return $result;
	}

	/**
	 * Load not sent job Ids.
	 *
	 * @return array
	 */
	public static function load_not_sent_job_ids() {
		global $wpdb;
		$time = new DateTime();
		$time->sub( new DateInterval( 'PT10M' ) );
		$table = $wpdb->prefix . self::get_table_name();
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$db_results = $wpdb->get_results( //phpcs:ignore
			$wpdb->prepare(
				"SELECT id FROM $table WHERE state = %s AND updated_at < %s",
				self::POSTFINANCECHECKOUT_STATE_CREATED,
				$time->format( 'Y-m-d H:i:s' )
			),
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.
		$result = array();
		if ( is_array( $db_results ) ) {
			foreach ( $db_results as $object_values ) {
				$result[] = $object_values['id'];
			}
		}
		return $result;
	}
}
