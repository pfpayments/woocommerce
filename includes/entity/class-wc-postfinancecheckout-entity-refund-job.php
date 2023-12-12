<?php
/**
 *
 * WC_PostFinanceCheckout_Entity_Refund_Job Class
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
	const STATE_CREATED = 'created';
	const STATE_SENT    = 'sent';
	const STATE_PENDING = 'pending';
	const STATE_SUCCESS = 'success';
	const STATE_FAILURE = 'failure';

	/**
	 * Get field definition.
	 *
	 * @return array
	 */
	protected static function get_field_definition() {
		return array(
			'external_id'    => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
			'state'          => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
			'space_id'       => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'transaction_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'order_id'       => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'wc_refund_id'   => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
			'refund'         => WC_PostFinanceCheckout_Entity_Resource_Type::OBJECT,
			'failure_reason' => WC_PostFinanceCheckout_Entity_Resource_Type::OBJECT,
		);
	}

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	protected static function get_table_name() {
		return 'wc_postfinancecheckout_refund_job';
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
		$result = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %1$s WHERE space_id = %2$d AND external_id = "%3$s"',
				$wpdb->prefix . self::get_table_name(),
				$space_id,
				$external_id
			),
			ARRAY_A
		);
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

		$result = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM %1$s WHERE space_id = %2$d AND transaction_id = %3$d AND state != "%4$s" AND state != "%5$s"',
				$wpdb->prefix . self::get_table_name(),
				$space_id,
				$transaction_id,
				self::STATE_SUCCESS,
				self::STATE_FAILURE
			)
		);
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
		$result = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM %1$s WHERE space_id = %2$d AND transaction_id = %3$d AND state != "%4$s" AND state != "%5$s"',
				$wpdb->prefix . self::get_table_name(),
				$space_id,
				$transaction_id,
				self::STATE_SUCCESS,
				self::STATE_FAILURE
			),
			ARRAY_A
		);
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

		$db_results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM %1$s WHERE order_id = %2$d',
				$wpdb->prefix . self::get_table_name(),
				$order_id
			),
			ARRAY_A
		);
		$result     = array();
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
		$db_results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT id FROM %1$s WHERE state = "%2$s" AND updated_at < "%3$s"',
				$wpdb->prefix . self::get_table_name(),
				self::STATE_CREATED,
				$time->format( 'Y-m-d H:i:s' )
			),
			ARRAY_A
		);
		$result     = array();
		if ( is_array( $db_results ) ) {
			foreach ( $db_results as $object_values ) {
				$result[] = $object_values['id'];
			}
		}
		return $result;
	}
}
