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
 * This entity holds data about a PostFinance Checkout payment method.
 * 
 * @method int get_id()
 * @method string get_state()
 * @method void set_state(string $state)
 * @method int get_space_id()
 * @method void set_space_id(int $id)
 * @method int get_configuration_id()
 * @method void set_configuration_id(int $id)
 * @method string get_configuration_name()
 * @method void set_configuration_name(string $name)
 * @method string[] get_title()
 * @method void set_title(string[] $title)
 * @method string[] get_description()
 * @method void set_description(string[] $description)
 * @method string get_image()
 * @method void set_image(string $image)
 * @method string get_image_base()
 * @method void set_image_base(string $image_base)
 * 
 */
class WC_PostFinanceCheckout_Entity_Method_Configuration extends WC_PostFinanceCheckout_Entity_Abstract {
	const STATE_ACTIVE = 'active';
	const STATE_INACTIVE = 'inactive';
	const STATE_HIDDEN = 'hidden';

	protected static function get_field_definition(){
		return array(
		    'state' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
		    'space_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
		    'configuration_id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
		    'configuration_name' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
		    'title' => WC_PostFinanceCheckout_Entity_Resource_Type::OBJECT,
		    'description' => WC_PostFinanceCheckout_Entity_Resource_Type::OBJECT,
		    'image' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
		    'image_base' => WC_PostFinanceCheckout_Entity_Resource_Type::STRING,
		
		);
	}

	protected static function get_table_name(){
		return 'woocommerce_postfinancecheckout_method_configuration';
	}
	
	public static function load_by_configuration($space_id, $configuration_id){
		global $wpdb;
		$result = $wpdb->get_row(
				$wpdb->prepare("SELECT * FROM " . $wpdb->prefix . self::get_table_name() . " WHERE space_id = %d AND configuration_id = %d", 
						$space_id, $configuration_id), ARRAY_A);
		if ($result !== null) {
			return new self($result);
		}
		return new self();
	}

	public static function load_by_states_and_space_id($space_id, array $states){
		global $wpdb;
		if (empty($states)) {
			return array();
		}
		$replace = "";
		foreach ($states as $value) {
			$replace .= "%s, ";
		}
		$replace = rtrim($replace, ", ");
		
		$values = array_merge(array($space_id), $states);
		
		$query = "SELECT * FROM " . $wpdb->prefix . self::get_table_name() . " WHERE space_id = %d AND state IN (" . $replace . ")";
		$result = array();
		
		$db_results = $wpdb->get_results($wpdb->prepare($query, $values), ARRAY_A);
		if(is_array($db_results)){
			foreach ($db_results as $object_values) {
				$result[] = new static($object_values);
			}
		}
		return $result;
	}
}