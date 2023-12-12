<?php
/**
 *
 * WC_PostFinanceCheckout_Entity_Void_Job Class
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
 * @method int get_void_id()
 * @method void set_void_id(int $id)
 * @method string get_state()
 * @method void set_state(string $state)
 * @method int get_space_id()
 * @method void set_space_id(int $id)
 * @method int get_transaction_id()
 * @method void set_transaction_id(int $id)
 * @method int get_order_id()
 * @method void set_order_id(int $id)
 * @method boolean get_restock()
 * @method void set_restock(boolean $items)
 * @method void set_failure_reason(map[string,string] $reasons)
 */
class WC_PostFinanceCheckout_Entity_Void_Job extends WC_PostFinanceCheckout_Entity_Abstract {
	const STATE_CREATED = 'created';
	const STATE_SENT = 'sent';
	const STATE_DONE = 'done';

	/**
	 * Get field definition.
	 *
	 * @return array
	 */
	protected static function get_field_definition() {
		return array(
			'void_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'state' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
			'space_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'transaction_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'order_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'restock' => WC_PostFinanceCheckout_Entity_Resource_Type::BOOLEAN,
			'failure_reason' => WC_PostFinanceCheckout_Entity_Resource_Type::OBJECT,

		);
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected static function get_table_name() {
		return 'wc_postfinancecheckout_void_job';
	}


	/**
	 * Get failure reason.
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
	 * Load by void.
	 *
	 * @param mixed $space_id space id.
	 * @param mixed $void_id void id.
	 * @return WC_PostFinanceCheckout_Entity_Void_Job
	 */
	public static function load_by_void( $space_id, $void_id ) {
		global $wpdb;
		$result = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %1$s WHERE space_id = %2$d AND void_id = %3$d',
				$wpdb->prefix . self::get_table_name(),
				$space_id,
				$void_id
			),
			ARRAY_A
		);
		if ( null !== $result ) {
			return new self( $result );
		}
		return new self();
	}

	/**
	 * Count running void for transaction.
	 *
	 * @param mixed $space_id space id.
	 * @param mixed $transaction_id transaction id.
	 * @return string|null
	 */
	public static function count_running_void_for_transaction( $space_id, $transaction_id ) {
		global $wpdb;

		$result = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %1$s WHERE space_id = %2$d AND transaction_id = %3$d AND state != "%4$s"',
				$wpdb->prefix . self::get_table_name(),
				$space_id,
				$transaction_id,
				self::STATE_DONE
			)
		);
		return $result;
	}

	/**
	 * Load running void for transaction.
	 *
	 * @param mixed $space_id space id.
	 * @param mixed $transaction_id transaction id.
	 * @return WC_PostFinanceCheckout_Entity_Void_Job
	 */
	public static function load_running_void_for_transaction( $space_id, $transaction_id ) {
		global $wpdb;
		$result = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %1$s WHERE space_id = %2$d AND transaction_id = %3$d AND state != "%4$s"',
				$wpdb->prefix . self::get_table_name(),
				$space_id,
				$transaction_id,
				self::STATE_DONE
			),
			ARRAY_A
		);
		if ( null !== $result ) {
			return new self( $result );
		}
		return new self();
	}

	/**
	 * Load not sent job ids
	 *
	 * @return array
	 */
	public static function load_not_sent_job_ids() {
		global $wpdb;
		// Returns empty array.

		$time = new DateTime();
		$time->sub( new DateInterval( 'PT10M' ) );
		$db_results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id FROM %1$s WHERE state = "%2$s" AND updated_at < "%3$s"',
				$wpdb->prefix . self::get_table_name(),
				self::STATE_CREATED,
				$time->format( 'Y-m-d H:i:s' )
			),
			ARRAY_A
		);
		$result = array();
		if ( is_array( $db_results ) ) {
			foreach ( $db_results as $object_values ) {
				$result[] = $object_values['id'];
			}
		}
		return $result;
	}
}
