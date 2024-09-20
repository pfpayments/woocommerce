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
 * Handles the management and processing of different webhook strategies.
 *
 * This manager class holds references to different webhook strategies and delegates
 * the processing of incoming webhook requests to the appropriate strategy based on
 * the type of the webhook. Each strategy corresponds to a specific type of webhook
 * and contains the logic needed to handle that specific webhook type.
 */
class WC_PostFinanceCheckout_Webhook_Strategy_Manager {

	/**
	 * Holds instances of webhook strategies.
	 *
	 * @var array Holds instances of webhook strategies.
	 */
	protected $strategies = array();

	/**
	 * The service that provides access to webhook-related functionalities.
	 *
	 * @var WC_PostFinanceCheckout_Service_Webhook
	 */
	private $webhook_service;

	/**
	 * The helper object for utility functions
	 *
	 * @var WC_PostFinanceCheckout_Helper
	 */
	private $helper;

	/**
	 * The single instance of the class.
	 *
	 * @var WC_PostFinanceCheckout_Webhook_Strategy_Manager
	 */
	protected static $_instance = null;

	/**
	 * Instance.
	 *
	 * @return static
	 */
	public static function instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor for the webhook manager.
	 *
	 * Initializes instances of each specific webhook strategy and stores them in an associative array.
	 * Each key in this array corresponds to a webhook type, and each value is an instance of a strategy
	 * class that handles that specific type of webhook.
	 */
	public function __construct() {
		$this->webhook_service = WC_PostFinanceCheckout_Service_Webhook::instance();
		$this->helper = WC_PostFinanceCheckout_Helper::instance();
		$this->configure_strategies_to_handle_webhooks();
	}

	/**
	 * Initializes the list of strategies for handling different types of webhook events.
	 *
	 * This method populates the 'strategies' array with instances of various webhook handling strategies.
	 * Each strategy corresponds to a specific type of webhook event, ensuring that the appropriate
	 * processing logic is applied based on the type of the incoming webhook request.
	 *
	 * @return void
	 */
	private function configure_strategies_to_handle_webhooks() {
		$this->strategies[] = new WC_PostFinanceCheckout_Webhook_Manual_Task_Strategy();
		$this->strategies[] = new WC_PostFinanceCheckout_Webhook_Method_Configuration_Strategy();
		$this->strategies[] = new WC_PostFinanceCheckout_Webhook_Transaction_Strategy();
		$this->strategies[] = new WC_PostFinanceCheckout_Webhook_Delivery_Indication_Strategy();
		$this->strategies[] = new WC_PostFinanceCheckout_Webhook_Transaction_Invoice_Strategy();
		$this->strategies[] = new WC_PostFinanceCheckout_Webhook_Transaction_Completion_Strategy();
		$this->strategies[] = new WC_PostFinanceCheckout_Webhook_Transaction_Void_Strategy();
		$this->strategies[] = new WC_PostFinanceCheckout_Webhook_Refund_Strategy();
		$this->strategies[] = new WC_PostFinanceCheckout_Webhook_Token_Strategy();
		$this->strategies[] = new WC_PostFinanceCheckout_Webhook_Token_Version_Strategy();
	}

	/**
	 * Resolves the appropriate strategy for handling the given webhook request based on webhook type.
	 *
	 * This method fetches the webhook entity using the listener entity ID from the request, checks if a corresponding
	 * strategy exists, and returns the strategy if found.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The incoming webhook request.
	 * @return WC_PostFinanceCheckout_Webhook_Strategy_Interface The strategy to handle the request.
	 * @throws Exception If no strategy can be resolved.
	 */
	private function resolve_strategy( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$webhook_model = $this->webhook_service->get_webhook_entity_for_id( $request->get_listener_entity_id() );

		// Check if the webhook model exists for this listener entity ID.
		if ( is_null( $webhook_model ) ) {
			$entity_id = esc_attr( $request->get_listener_entity_id() );
			throw new Exception( esc_attr( sprintf( 'Could not retrieve webhook model for listener entity id: %s', $entity_id ) ) );
		}

		$webhook_transaction_id = $webhook_model->get_id();

		// Check if the strategy exists for the retrieved transaction ID.
		foreach ( $this->strategies as $strategy ) {
			if ( $strategy->match( $webhook_transaction_id ) ) {
				return $strategy;
			}
		}

		// No strategy found for the transaction ID.
		throw new Exception( esc_attr( sprintf( 'No strategy available for the transaction ID: %s', $webhook_transaction_id ) ) );
	}

	/**
	 * Processes the incoming webhook by delegating to the appropriate strategy.
	 *
	 * This method determines the type of the incoming webhook request and uses it
	 * to look up the corresponding strategy. If a strategy is found, it delegates the
	 * request processing to that strategy. If no strategy is found for the type, it
	 * throws an exception.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request The incoming webhook request object.
	 * @throws Exception If no strategy is available for the webhook type provided in the request.
	 */
	public function process( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$this->helper->start_database_transaction();

		try {
			$this->helper->lock_by_transaction_id( $request->get_space_id(), $request->get_entity_id() );

			$strategy = $this->resolve_strategy( $request );
			$strategy->process( $request );

			$this->helper->commit_database_transaction();
		} catch ( Exception $e ) {
			$this->helper->rollback_database_transaction();
			throw $e;
		}
	}
}
