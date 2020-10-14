<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
 *
 * @author wallee AG (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * Abstract implementation of a entity
 */
abstract class WC_PostFinanceCheckout_Entity_Abstract {
	protected $data = array();

	protected static function get_base_fields(){
		return array(
		    'id' => WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER,
		    'created_at' => WC_PostFinanceCheckout_Entity_Resource_Type::DATETIME,
		    'updated_at' => WC_PostFinanceCheckout_Entity_Resource_Type::DATETIME 
		);
	}

	abstract protected static function get_field_definition();

	abstract protected static function get_table_name();

	protected function get_value($variable_name){
		return isset($this->data[$variable_name]) ? $this->data[$variable_name] : null;
	}

	protected function set_value($variable_name, $value){
		$this->data[$variable_name] = $value;
	}

	protected function has_value($variable_name){
		return array_key_exists($variable_name, $this->data);
	}

	public function __call($name, $arguments){
		$variable_name = substr($name, 4);
		if (0 === strpos($name, 'get_')) {
			return $this->get_value($variable_name);
		}
		elseif (0 === strpos($name, 'set_')) {
			$this->set_value($variable_name, $arguments[0]);
		}
		elseif (0 === strpos($name, 'has_')) {
			return $this->has_value($variable_name);
		}
	}

	public function __construct(){
		$args = func_get_args();
		if (!isset($args[0]) || empty($args[0])) {
			return;
		}
		$this->fill_values_from_db($args[0]);
	}

	protected function fill_values_from_db(array $db_values){
		$fields = array_merge($this->get_base_fields(), $this->get_field_definition());
		foreach ($fields as $key => $type) {
			if (isset($db_values[$key])) {
				$value = $db_values[$key];
				switch ($type) {
				    case WC_PostFinanceCheckout_Entity_Resource_Type::STRING:
						//Do nothing
						break;
				    case WC_PostFinanceCheckout_Entity_Resource_Type::BOOLEAN:
						$value = $value === 'Y';
						break;
				    case WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER:
						$value = intval($value);
						break;
					
				    case WC_PostFinanceCheckout_Entity_Resource_Type::DECIMAL:
						$value = (float) $value;
						break;
					
				    case WC_PostFinanceCheckout_Entity_Resource_Type::DATETIME:
						$value = new DateTime($value);
						break;
					
				    case WC_PostFinanceCheckout_Entity_Resource_Type::OBJECT:
						$value = unserialize($value);
						break;
					default:
						throw new Exception('Unsupported variable type');
				}
				$this->set_value($key, $value);
			}
		}
	}

	public function save(){
		global $wpdb;
		$data_array = array();
		$type_array = array();
		
		foreach ($this->get_field_definition() as $key => $type) {
			$value = $this->get_value($key);
			switch ($type) {
			    case WC_PostFinanceCheckout_Entity_Resource_Type::STRING:
					$type_array[] = "%s";
					break;
				
			    case WC_PostFinanceCheckout_Entity_Resource_Type::BOOLEAN:
					$value = $value ? 'Y' : 'N';
					$type_array[] = "%s";
					break;
				
			    case WC_PostFinanceCheckout_Entity_Resource_Type::INTEGER:
					$type_array[] = "%d";
					break;
				
			    case WC_PostFinanceCheckout_Entity_Resource_Type::DATETIME:
					if ($value instanceof DateTime) {
						$value = $value->format('Y-m-d H:i:s');
					}
					$type_array[] = "%s";
					break;
			    case WC_PostFinanceCheckout_Entity_Resource_Type::OBJECT:
					$value = serialize($value);
					$type_array[] = "%s";
					break;
				
			    case WC_PostFinanceCheckout_Entity_Resource_Type::DECIMAL:
					$value = number_format($value, 8, '.', '');
					$type_array[] = "%s";
					break;
				
				default:
					throw new Exception('Unsupported variable type');
			}
			$data_array[$key] = $value;
		}
		$this->prepare_base_fields_for_storage($data_array, $type_array);
		
		if ($this->get_id() === null) {
			$wpdb->insert($wpdb->prefix . $this->get_table_name(), $data_array, $type_array);
			$this->set_id($wpdb->insert_id);
		}
		else {
			$wpdb->update($wpdb->prefix . $this->get_table_name(), $data_array, array(
				'id' => $this->get_id() 
			), $type_array, array(
				"%d" 
			));
		}
	}
	
	
	protected function prepare_base_fields_for_storage(&$data_array, &$type_array){
	    $data_array['updated_at'] = current_time('mysql');
	    $type_array[] = "%s";
	    if($this->get_id() === null){
	        $data_array['created_at'] = $data_array['updated_at'];
	        $type_array[] = "%s";
	    }
	}

	/**
	 * 
	 * @param int $id
	 * @return static
	 */
	public static function load_by_id($id){
		global $wpdb;
		$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . static::get_table_name() . " WHERE id = %d", $id), ARRAY_A);
		if ($result !== null) {
			return new static($result);
		}
		return new static();
	}

	public static function load_all(){
		global $wpdb;
		//Returns empty array
		$db_results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . static::get_table_name(), ARRAY_A);
		$result = array();
		foreach ($db_results as $object_values) {
			$result[] = new static($object_values);
		}
		return $result;
	}

	public function delete(){
		global $wpdb;
		$wpdb->delete($wpdb->prefix . $this->get_table_name(), array(
			'id' => $this->get_id() 
		), array(
			"%d" 
		));
	}
}