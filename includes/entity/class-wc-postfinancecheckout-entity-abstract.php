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
 * Class WC_PostFinanceCheckout_Entity_Abstract.
 * Abstract implementation of a entity
 *
 * @class WC_PostFinanceCheckout_Entity_Abstract
 */
abstract class WC_PostFinanceCheckout_Entity_Abstract {
	/**
	 * Data
	 *
	 * @var $data data.
	 */
	protected $data = array();

	/**
	 * Get
	 */
	protected static function get_base_fields() {
		return array(
			'id' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER,
			'created_at' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_DATETIME,
			'updated_at' => WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_DATETIME,
		);
	}

	/**
	 * Get field definition
	 */
	abstract protected static function get_field_definition();

	/**
	 * Get table name
	 */
	abstract protected static function get_table_name();

	/**
	 * Get Value
	 *
	 * @param mixed $variable_name variable name.
	 */
	public function get_value( $variable_name ) {
		return isset( $this->data[ $variable_name ] ) ? $this->data[ $variable_name ] : null;
	}

	/**
	 * Set Value
	 *
	 * @param mixed $variable_name variable name.
	 * @param mixed $value value name.
	 */
	protected function set_value( $variable_name, $value ) {
		$this->data[ $variable_name ] = $value;
	}

	/**
	 * Has value
	 *
	 * @param mixed $variable_name variable name.
	 *
	 * @return bool array_key_exists.
	 */
	protected function has_value( $variable_name ) {
		return array_key_exists( $variable_name, $this->data );
	}

	/**
	 * Call
	 *
	 * @param mixed $name name.
	 * @param mixed $arguments arguments.
	 */
	public function __call( $name, $arguments ) {
		$variable_name = substr( $name, 4 );
		if ( 0 === strpos( $name, 'get_' ) ) {
			return $this->get_value( $variable_name );
		} elseif ( 0 === strpos( $name, 'set_' ) ) {
			$this->set_value( $variable_name, $arguments[0] );
		} elseif ( 0 === strpos( $name, 'has_' ) ) {
			return $this->has_value( $variable_name );
		}
	}

	/**
	 * Construct
	 */
	public function __construct() {
		$args = func_get_args();
		if ( ! isset( $args[0] ) || empty( $args[0] ) ) {
			return;
		}
		$this->fill_values_from_db( $args[0] );
	}

	/**
	 * Fill fields from db
	 *
	 * @param array $db_values database values.
	 *
	 * @throws Exception Excpetion.
	 */
	protected function fill_values_from_db( array $db_values ) {
		$fields = array_merge( $this->get_base_fields(), $this->get_field_definition() );
		foreach ( $fields as $key => $type ) {
			if ( isset( $db_values[ $key ] ) ) {
				$value = $db_values[ $key ];
				switch ( $type ) {
					case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_STRING:
						// Do nothing.
						break;
					case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_BOOLEAN:
						$value = 'Y' === $value;
						break;
					case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER:
						$value = intval( $value );
						break;

					case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_DECIMAL:
						$value = (float) $value;
						break;

					case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_DATETIME:
						$value = new DateTime( $value );
						break;

					case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_OBJECT:
						$value = unserialize( $value ); //phpcs:ignore
						break;
					default:
						throw new Exception( 'Unsupported variable type' );
				}
				$this->set_value( $key, $value );
			}
		}
	}

	/**
	 * Save
	 *
	 * @throws Exception Exception.
	 */
	public function save() {
		global $wpdb;
		$data_array = array();
		$type_array = array();

		foreach ( $this->get_field_definition() as $key => $type ) {
			$value = $this->get_value( $key );
			switch ( $type ) {
				case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_STRING:
					$type_array[] = '%s';
					break;

				case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_BOOLEAN:
					$value = $value ? 'Y' : 'N';
					$type_array[] = '%s';
					break;

				case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_INTEGER:
					$type_array[] = '%d';
					break;

				case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_DATETIME:
					if ( $value instanceof DateTime ) {
						$value = $value->format( 'Y-m-d H:i:s' );
					}
					$type_array[] = '%s';
					break;
				case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_OBJECT:
					$value = serialize( $value ); //phpcs:ignore
					$type_array[] = '%s';
					break;

				case WC_PostFinanceCheckout_Entity_Resource_Type::POSTFINANCECHECKOUT_DECIMAL:
					$value = number_format( $value, 8, '.', '' );
					$type_array[] = '%s';
					break;

				default:
					throw new Exception( 'Unsupported variable type' );
			}
			$data_array[ $key ] = $value;
		}
		$this->prepare_base_fields_for_storage( $data_array, $type_array );

		if ( $this->get_id() === null ) {
			$wpdb->insert( $wpdb->prefix . $this->get_table_name(), $data_array, $type_array ); //phpcs:ignore
			$this->set_id( $wpdb->insert_id );
		} else {
			$wpdb->update( //phpcs:ignore
				$wpdb->prefix . $this->get_table_name(),
				$data_array,
				array(
					'id' => $this->get_id(),
				),
				$type_array,
				array(
					'%d',
				)
			);
		}
	}


	/**
	 * Prepare base fields for storage
	 *
	 * @param array $data_array data array.
	 * @param array $type_array type array.
	 */
	protected function prepare_base_fields_for_storage( &$data_array, &$type_array ) {
		$data_array['updated_at'] = current_time( 'mysql' );
		$type_array[] = '%s';
		if ( $this->get_id() === null ) {
			$data_array['created_at'] = $data_array['updated_at'];
			$type_array[] = '%s';
		}
	}

	/**
	 * Load by id
	 *
	 * @param int $id id.
	 *
	 * @return static
	 */
	public static function load_by_id( $id ) {
		global $wpdb;
		// phpcs:ignore
		$result = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . static::get_table_name() . ' WHERE id = %d', $id ), ARRAY_A ); //phpcs:ignore
		if ( null !== $result ) {
			return new static( $result );
		}
		return new static();
	}

	/**
	 * Load all
	 *
	 * @return array $result.
	 */
	public static function load_all() {
		global $wpdb;
		// Returns empty array.
		// phpcs:ignore
		$db_results = $wpdb->get_results( "SELECT * FROM ". $wpdb->prefix . static::get_table_name() , ARRAY_A );//phpcs:ignore
		$result = array();
		foreach ( $db_results as $object_values ) {
			$result[] = new static( $object_values );
		}
		return $result;
	}

	/**
	 * Delete.
	 */
	public function delete() {
		global $wpdb;
		$wpdb->delete( //phpcs:ignore
			$wpdb->prefix . $this->get_table_name(),
			array(
				'id' => $this->get_id(),
			),
			array(
				'%d',
			)
		);
	}
}
