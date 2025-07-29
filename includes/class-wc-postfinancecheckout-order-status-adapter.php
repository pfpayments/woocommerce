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
 * Class WC_PostFinanceCheckout_Order_Status_Adapter.
 *
 * This class handles the mapping and updating of order statuses in WooCommerce
 * based on the transaction statuses in PostFinanceCheckout.
 */
class WC_PostFinanceCheckout_Order_Status_Adapter
{
	/**
	 * Constants for PostFinanceCheckout transaction statuses.
	 */
	const POSTFINANCECHECKOUT_STATUS_PENDING = 'pending';
	const POSTFINANCECHECKOUT_STATUS_CONFIRMED = 'confirmed';
	const POSTFINANCECHECKOUT_STATUS_PROCESSING = 'processing';
	const POSTFINANCECHECKOUT_STATUS_AUTHORIZED = 'authorized';
	const POSTFINANCECHECKOUT_STATUS_COMPLETED = 'completed';
	const POSTFINANCECHECKOUT_STATUS_FAILED = 'failed';
	const POSTFINANCECHECKOUT_STATUS_VOIDED = 'voided';
	const POSTFINANCECHECKOUT_STATUS_FULFILL = 'fulfill';
	const POSTFINANCECHECKOUT_STATUS_DECLINE = 'decline';

	const POSTFINANCECHECKOUT_ORDER_STATUS_MAPPING_PREFIX = 'postfinancecheckout_order_status_mapping_';
	const POSTFINANCECHECKOUT_CUSTOM_ORDER_STATUS_PREFIX = 'postfinancecheckout_custom_order_status_';

	/**
	 * Stores the status mappings loaded from the database.
	 *
	 * @var array $settings Array of status mappings loaded from the database.
	 */
	private $settings;

	/**
	 * WC_PostFinanceCheckout_Order_Status_Adapter constructor.
	 *
	 * Initializes the settings and adds the filter for updating order status.
	 */
	public function __construct()
	{
		$this->initialize_filters();
		$this->initialize_status_mappings();
	}

	/**
	 * Initialise filters and actions.
	 */
	public static function init(): void
	{
		add_action( 'plugins_loaded', array( __CLASS__, 'register_postfinancecheckout_service_status_adapter' ) );
	}

	/**
	 * Registers the PostFinanceCheckout service status adapter.
	 *
	 * This function initializes an instance of the WC_PostFinanceCheckout_Order_Status_Adapter class.
	 * It is hooked to the 'plugins_loaded' action to ensure it runs after all plugins are loaded.
	 *
	 * @return void
	 */
	public static function register_postfinancecheckout_service_status_adapter(): void
	{
		new WC_PostFinanceCheckout_Order_Status_Adapter();
	}

	/**
	 * Initialize filters.
	 */
	private function initialize_filters(): void
	{
		add_filter( 'postfinancecheckout_default_order_status_mappings', array( $this, 'get_default_status_mappings' ) );
		add_filter( 'postfinancecheckout_woocommerce_statuses', array( $this, 'get_all_woocommerce_statuses' ) );
		add_filter( 'postfinancecheckout_order_statuses', array( $this, 'get_postfinancecheckout_statuses' ) );
		add_filter( 'postfinancecheckout_order_update_status', array( $this, 'update_order_status' ), 10, 5 );
		add_filter( 'postfinancecheckout_wc_status_for_transaction', array( $this, 'get_wc_status_for_transaction' ), 10, 1 );
		add_filter( 'wc_order_statuses', array( $this, 'add_order_statuses' ) );
		add_filter( 'woocommerce_valid_order_statuses_for_payment', array( $this, 'valid_order_statuses_for_payment' ) );
		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'get_order_status_on_payment_complete' ), 10, 3 );

		//tests.
		// CPT-based orders.
		add_filter( 'bulk_actions-edit-shop_order', array($this, 'bulk_actions_shop_order'), 20, 1 );
		add_action( 'handle_bulk_actions-edit-shop_order', array($this, 'bulk_process_custom_status'), 20, 3 );
		// HPOS orders.
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', array($this, 'bulk_actions_shop_order'), 20, 1 );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array($this, 'bulk_process_custom_status'), 20, 3 );
	}

	/**
	 * Modifies the bulk actions available in the WooCommerce orders list.
	 *
	 * @param array $bulk_actions The existing bulk actions.
	 * @return array The modified bulk actions.
	 */
	public function bulk_actions_shop_order( $bulk_actions ) {
		return $bulk_actions;
	}

	/**
	 * Handles custom bulk actions for WooCommerce orders.
	 *
	 * @param string $redirect The redirect URL after processing bulk actions.
	 * @param string $doaction The action being performed.
	 * @param array  $object_ids The IDs of the selected orders.
	 * @return string The modified redirect URL.
	 */
	public function bulk_process_custom_status( $redirect, $doaction, $object_ids ) {
		return $redirect;
	}

	/**
	 * Add order statuses.
	 *
	 * @param mixed $order_statuses order statuses.
	 * @return mixed
	 */
	public function add_order_statuses( $order_statuses ) {
		global $wpdb;

		$prefix = self::POSTFINANCECHECKOUT_CUSTOM_ORDER_STATUS_PREFIX . '%'; //We use % as wildcard for LIKE.
		$results = $wpdb->get_results( $wpdb->prepare( "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", $prefix ), ARRAY_A );

		//Build the array with the desired structure.
		foreach ( $results as $row ) {
			$status_label = ucfirst( str_replace( array( 'wc-', '_' ), array( '', ' ' ), $row['option_value'] ) );
			// translators: %s represents the dynamically generated order status label.
			$order_statuses[ $row['option_value'] ] = _x( $status_label, 'Order status', 'woocommerce' ); // phpcs:ignore
		}

		return $order_statuses;
	}


	/**
	 * Add order statuses.
	 *
	 * @param mixed $order_statuses order statuses.
	 * @return mixed
	 */
	public function valid_order_statuses_for_payment( $order_statuses ) {
		$default_mappings = array(
			self::POSTFINANCECHECKOUT_STATUS_PENDING => 'wc-pending',
			self::POSTFINANCECHECKOUT_STATUS_CONFIRMED => 'wc-on-hold',
			self::POSTFINANCECHECKOUT_STATUS_PROCESSING => 'wc-on-hold',
			self::POSTFINANCECHECKOUT_STATUS_COMPLETED => 'wc-processing',
			self::POSTFINANCECHECKOUT_STATUS_FULFILL => 'wc-completed',
		);

		$order_statuses_without_prefix = array_map( function( $status ) {
			return str_replace( 'wc-', '', $status );
		}, array_values( $default_mappings ) );

		return array_merge($order_statuses, $order_statuses_without_prefix);
	}

	/**
	 * Loads the status mappings from the database or initializes them if not present.
	 */
	private function initialize_status_mappings(): void
	{
		$default_mappings = apply_filters( 'postfinancecheckout_default_order_status_mappings', array() );

		$this->settings = array_map( function ( $transaction_status, $default_order_status ) {
			return array(
				'transaction_status' => $transaction_status,
				'order_status' => get_option( self::POSTFINANCECHECKOUT_ORDER_STATUS_MAPPING_PREFIX . $transaction_status, $default_order_status )
			);
		}, array_keys( $default_mappings ), $default_mappings );
	}

	/**
	 * Stores the default order status mappings in the database if they are not already set.
	 *
	 * This method is called from a migration process to ensure that WooCommerce order status
	 * mappings are initialized correctly. If no custom mappings exist, it saves the default
	 * values in the `wp_options` table using the option name pattern:
	 * `postfinancecheckout_order_update_status_<status>`.
	 *
	 * @since 1.0.0
	 * @see apply_filters() Allows developers to modify default status mappings before storing them.
	 *
	 * @return void
	 */
	public function store_default_status_mappings_on_database(): void
	{
		$default_mappings = $this->get_default_status_mappings();

		foreach ( $default_mappings as $key => $value ) {
			$result = update_option( self::POSTFINANCECHECKOUT_ORDER_STATUS_MAPPING_PREFIX . $key, $value );
		}
	}

	/**
	 * Gets the default status mappings.
	 * WooCommerce statuses dont have a constant, so we use the woocommerce key.
	 *
	 * This method defines the default mappings between PostFinanceCheckout transaction
	 * statuses and WooCommerce order statuses. These mappings are saved in the WordPress
	 * `wp_options` table as `postfinancecheckout_order_status_mapping_<status>` when the
	 * plugin is installed or initialized.
	 *
	 * WooCommerce introduced the `OrderInternalStatus` constants in version 9.6.0.
 	 * To maintain compatibility with earlier versions, string values are used as a fallback.
	 * This is the interface to use in next versions Automattic\WooCommerce\Enums\OrderInternalStatus
	 *
	 * Example of saved options in `wp_options`:
	 * - postfinancecheckout_order_status_mapping_pending -> wc-pending -> OrderInternalStatus::PENDING
	 * - postfinancecheckout_order_status_mapping_confirmed -> wc-on-hold -> OrderInternalStatus::ON_HOLD
	 * - postfinancecheckout_order_status_mapping_processing -> wc-on-hold -> OrderInternalStatus::ON_HOLD
	 * - postfinancecheckout_order_status_mapping_authorized -> wc-on-hold -> OrderInternalStatus::ON_HOLD
	 * - postfinancecheckout_order_status_mapping_completed -> wc-on-hold -> OrderInternalStatus::ON_HOLD
	 * - postfinancecheckout_order_status_mapping_failed -> wc-failed -> OrderInternalStatus::FAILED
	 * - postfinancecheckout_order_status_mapping_voided -> wc-cancelled or 'wc-refunded' -> OrderInternalStatus::CANCELLED
	 * - postfinancecheckout_order_status_mapping_fulfill -> wc-processing -> OrderInternalStatus::PROCESSING
	 *
	 * These defaults are used if no custom mappings are provided by the user.
	 *
	 * @return array
	 */
	public function get_default_status_mappings() : array
	{
		return array(
			self::POSTFINANCECHECKOUT_STATUS_PENDING => 'wc-pending',
			self::POSTFINANCECHECKOUT_STATUS_CONFIRMED => 'wc-on-hold',
			self::POSTFINANCECHECKOUT_STATUS_PROCESSING => 'wc-on-hold',
			self::POSTFINANCECHECKOUT_STATUS_AUTHORIZED => 'wc-on-hold',
			self::POSTFINANCECHECKOUT_STATUS_COMPLETED => 'wc-postfi-waiting',
			self::POSTFINANCECHECKOUT_STATUS_FAILED => 'wc-failed',
			self::POSTFINANCECHECKOUT_STATUS_VOIDED => 'wc-cancelled',
			self::POSTFINANCECHECKOUT_STATUS_FULFILL => 'wc-processing',
			self::POSTFINANCECHECKOUT_STATUS_DECLINE => 'wc-cancelled',
		);
	}

	/**
	 * Gets the legacy status mappings.
	 *
	 * Example of saved options in `wp_options`:
	 * - postfinancecheckout_order_status_mapping_confirmed -> postfi-redirected
	 * - postfinancecheckout_order_status_mapping_processing -> postfi-redirected
	 * - postfinancecheckout_order_status_mapping_completed -> postfi-waiting
	 *
	 * These defaults are used if no custom mappings are provided by the user.
	 *
	 * @return array
	 */
	public function get_legacy_default_status_mappings() : array
	{
		return array(
			self::POSTFINANCECHECKOUT_STATUS_CONFIRMED => 'postfi-redirected',
			self::POSTFINANCECHECKOUT_STATUS_PROCESSING => 'postfi-redirected',
			self::POSTFINANCECHECKOUT_STATUS_COMPLETED => 'postfi-waiting',
		);
	}

	/**
	 * Gets the WooCommerce statuses.
	 *
	 * @return array
	 */
	public function get_all_woocommerce_statuses(): array
	{
		return wc_get_order_statuses();
	}

	/**
	 * Gets the PostFinanceCheckout statuses.
	 *
	 * @return array
	 */
	public function get_postfinancecheckout_statuses(): array
	{
		return array(
			self::POSTFINANCECHECKOUT_STATUS_PENDING => ucwords( self::POSTFINANCECHECKOUT_STATUS_PENDING ),
			self::POSTFINANCECHECKOUT_STATUS_CONFIRMED => ucwords( self::POSTFINANCECHECKOUT_STATUS_CONFIRMED ),
			self::POSTFINANCECHECKOUT_STATUS_PROCESSING => ucwords( self::POSTFINANCECHECKOUT_STATUS_PROCESSING ),
			self::POSTFINANCECHECKOUT_STATUS_AUTHORIZED => ucwords( self::POSTFINANCECHECKOUT_STATUS_AUTHORIZED ),
			self::POSTFINANCECHECKOUT_STATUS_COMPLETED => ucwords( self::POSTFINANCECHECKOUT_STATUS_COMPLETED ),
			self::POSTFINANCECHECKOUT_STATUS_FAILED => ucwords( self::POSTFINANCECHECKOUT_STATUS_FAILED ),
			self::POSTFINANCECHECKOUT_STATUS_VOIDED => ucwords( self::POSTFINANCECHECKOUT_STATUS_VOIDED ),
			self::POSTFINANCECHECKOUT_STATUS_FULFILL => ucwords( self::POSTFINANCECHECKOUT_STATUS_FULFILL ),
			self::POSTFINANCECHECKOUT_STATUS_DECLINE => ucwords( self::POSTFINANCECHECKOUT_STATUS_DECLINE ),
		);
	}

	/**
	 * Gets the WooCommerce status corresponding to a PostFinanceCheckout status.
	 *
	 * @param string $status The PostFinanceCheckout transaction status.
	 * @return string|null The corresponding WooCommerce order status or null if not found.
	 */
	private function map_postfinancecheckout_status_to_woocommerce( string $status ): ?string
	{
		if ( empty( $this->settings ) ) {
			return $status; //Return the current status if there are no mappings available.
		}

		//Search in 'transaction_status' first.
		foreach ( $this->settings as $setting ) {
			if ( $setting['transaction_status'] === strtolower( $status ) ) {
				return str_replace( 'wc-', '', $setting['order_status'] ); //Return the mapped WooCommerce order status.
			}
		}

		//Fallback to legacy mappings if no match was found.
		$legacy_mappings = $this->get_legacy_default_status_mappings();
		$transaction_status_key = array_search( $status, $legacy_mappings, true );

		if ( !empty( $transaction_status_key ) ) {
			foreach ( $this->settings as $setting ) {
				if ( $setting['transaction_status'] === $transaction_status_key ) {
					return str_replace( 'wc-', '', $setting['order_status'] ); //Return the mapped WooCommerce order status.
				}
			}
		}
		
		return $status; //Return legacy status or original if not found.
	}

	/**
	 * Updates the status of a WooCommerce order based on the PostFinanceCheckout status.
	 *
	 * @param WC_Order $order $order The WooCommerce order.
	 * @param string|null $status The PostFinanceCheckout transaction status.
	 * @param string|null $default status The PostFinanceCheckout transaction status by default.
	 * @param string $note Optional note to add when updating the status.
	 * @param @param bool $manual Whether this is a manual order status change.
	 */
	public function update_order_status( WC_Order $order, ?string $status, ?string $default_status, string $note = '', bool $manual = false ): void
	{
		// If status is empty.
		if ( $status === null && $order !== null ) {
			$transaction_info = PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
			if ( $transaction_info ) {
				$status = $transaction_info->get_state();
			}
		}

		// Apply a pre-update filter to allow modifications before processing the status.
		$status = apply_filters( 'postfinancecheckout_pre_order_update_status', $status, $order, $note, $manual );
		$new_status = $this->map_postfinancecheckout_status_to_woocommerce( $status );

		if ( !empty( $new_status ) && !empty( $order ) ) {
			$order->update_status( $new_status, $note, $manual );
			// Apply a post-update filter to allow modifications after updating the status.
			apply_filters( 'postfinancecheckout_post_order_update_status', $status, $order, $note, $manual );
		}
	}

	/**
	 * Listens for the WooCommerce payment complete order status filter
	 * and return the order status accordingly when the order is fulfill.
	 *
	 * @param string $status The default order status (processing or completed).
	 * @param int $order_id The WooCommerce order ID.
	 * @param WC_Order $order The WooCommerce order object.
	 * @return string The updated order status.
	 */
	public function get_order_status_on_payment_complete( string $status, int $order_id, WC_Order $order ): string
	{
		// If order consists entirely out of virtual products and their total is 0, change their status to completed
		if ( 'yes' === get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_CHANGE_ORDER_STATUS ) 
		&& $order->get_total() <= 0 && WC_PostFinanceCheckout_Helper::is_order_virtual( $order ) ) {
			return self::POSTFINANCECHECKOUT_STATUS_COMPLETED;
		}
		
		// Check if the transaction status is mapped in WooCommerce
		$mapped_status = $this->map_postfinancecheckout_status_to_woocommerce( \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL );

		if ( ! empty( $mapped_status ) ) {
			return $mapped_status; // Return the mapped WooCommerce status
		}

		return $status; // Return the default status if no mapping exists.
	}

	/**
	 * Retrieves the mapped WooCommerce order status for a given PostFinanceCheckout transaction status.
	 *
	 * This method fetches the corresponding WooCommerce order status that has been mapped
	 * to a transaction status from the PostFinanceCheckout portal. The mapping is stored
	 * in the `wp_options` table using the naming pattern:
	 * `postfinancecheckout_order_status_mapping_<transaction_status>`.
	 *
	 * Expected values for `$postfinancecheckout_status`: Pending, Confirmed, Processing, Failed, Authorized, Voided, Completed, Fulfill, Decline.
	 *
	 * @since 1.0.0
	 * @param string $postfinancecheckout_status The transaction status from PostFinanceCheckout.
	 * @return string|null The mapped WooCommerce order status, or null if no mapping is found.
	 */
	public function get_wc_status_for_transaction( string $postfinancecheckout_status ): ?string
	{
		$status = get_option( self::POSTFINANCECHECKOUT_ORDER_STATUS_MAPPING_PREFIX . strtolower( $postfinancecheckout_status ), null );
		return is_null( $status ) ? null : str_replace( 'wc-', '', $status );
	}
}

WC_PostFinanceCheckout_Order_Status_Adapter::init();
