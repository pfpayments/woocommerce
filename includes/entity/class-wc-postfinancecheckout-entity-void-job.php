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
 *  
 */
class WC_PostFinanceCheckout_Entity_Void_Job extends WC_PostFinanceCheckout_Entity_Abstract {
	const STATE_CREATED = 'created';
	const STATE_SENT = 'sent';
	const STATE_DONE = 'done';

	protected static function get_field_definition(){
		return array(
		    'void_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
		    'state' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
		    'space_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
		    'transaction_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
		    'order_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
		    'restock' => WC_PostFinanceCheckout_Entity_Resource_Type::BOOLEAN,
		    'failure_reason' => WC_PostFinanceCheckout_Entity_Resource_Type::OBJECT 
		
		);
	}

	protected static function get_table_name(){
		return 'woocommerce_postfinancecheckout_void_job';
	}

	/**
	 * Returns the translated failure reason.
	 *
	 * @param string $locale
	 * @return string
	 */
	public function get_failure_reason($language = null){
		$value = $this->get_value('failure_reason');
		if (empty($value)) {
			return null;
		}
		
		return WC_PostFinanceCheckout_Helper::instance()->translate($value, $language);
	}

	public static function load_by_void($space_id, $void_id){
		global $wpdb;
		$result = $wpdb->get_row(
				$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . self::get_table_name() . " WHERE space_id = %d AND void_id = %d", $space_id, 
						$void_id), ARRAY_A);
		if ($result !== null) {
			return new self($result);
		}
		return new self();
	}

	public static function count_running_void_for_transaction($space_id, $transaction_id){
		global $wpdb;
		$query = $wpdb->prepare(
				"SELECT COUNT(*) FROM " . $wpdb->prefix . self::get_table_name() . " WHERE space_id = %d AND transaction_id = %d AND state != %s", 
				$space_id, $transaction_id, self::STATE_DONE);
		$result = $wpdb->get_var($query);
		return $result;
	}

	public static function load_running_void_for_transaction($space_id, $transaction_id){
		global $wpdb;
		$result = $wpdb->get_row(
				$wpdb->prepare(
						"SELECT * FROM " . $wpdb->prefix . self::get_table_name() . " WHERE space_id = %d AND transaction_id = %d AND state != %s", 
						$space_id, $transaction_id, self::STATE_DONE), ARRAY_A);
		if ($result !== null) {
			return new self($result);
		}
		return new self();
	}

	public static function load_not_sent_job_ids(){
		global $wpdb;
		//Returns empty array
		
		$time = new DateTime();
		$time->sub(new DateInterval('PT10M'));
		$db_results = $wpdb->get_results(
				$wpdb->prepare("SELECT id FROM " . $wpdb->prefix . self::get_table_name() . " WHERE state = %s AND updated_at < %s", 
						self::STATE_CREATED, $time->format('Y-m-d H:i:s')), ARRAY_A);
		$result = array();
		if(is_array($db_results)){
			foreach ($db_results as $object_values) {
				$result[] = $object_values['id'];
			}
		}
		return $result;
	}
}