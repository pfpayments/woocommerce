<?php
/**
 *
 * WC_PostFinanceCheckout_Webhook_Request Class
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   postfinancecheckout AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * Webhook request.
 */
class WC_PostFinanceCheckout_Webhook_Request {
	/**
	 * Event id.
	 *
	 * @var mixed
	 */
	private $event_id;

	/**
	 * Entity id.
	 *
	 * @var mixed
	 */
	private $entity_id;

	/**
	 * Listener entity id.
	 *
	 * @var mixed
	 */
	private $listener_entity_id;

	/**
	 * Listener entity technical name.
	 *
	 * @var mixed
	 */
	private $listener_entity_technical_name;

	/**
	 * Space id.
	 *
	 * @var mixed
	 */
	private $space_id;

	/**
	 * Webhook listener id.
	 *
	 * @var mixed
	 */
	private $webhook_listener_id;

	/**
	 * Timestamp.
	 *
	 * @var mixed
	 */
	private $timestamp;

	/**
	 * Constructor.
	 *
	 * @param stdClass $model model.
	 */
	public function __construct( $model ) {
		$this->event_id = $model->eventId;
	       	// phpcs:ignore
		$this->entity_id = $model->entityId;
	    	// phpcs:ignore
		$this->listener_entity_id = $model->listenerEntityId;
	    	// phpcs:ignore
		$this->listener_entity_technical_name = $model->listenerEntityTechnicalName;
	    	// phpcs:ignore
		$this->space_id = $model->spaceId;
	    	// phpcs:ignore
		$this->webhook_listener_id = $model->webhookListenerId;
		$this->timestamp = $model->timestamp;
	}

	/**
	 * Returns the webhook event's id.
	 *
	 * @return int
	 */
	public function get_event_id() {
		return $this->event_id;
	}

	/**
	 * Returns the id of the webhook event's entity.
	 *
	 * @return int
	 */
	public function get_entity_id() {
		return $this->entity_id;
	}

	/**
	 * Returns the id of the webhook's listener entity.
	 *
	 * @return int
	 */
	public function get_listener_entity_id() {
		return $this->listener_entity_id;
	}

	/**
	 * Returns the technical name of the webhook's listener entity.
	 *
	 * @return string
	 */
	public function get_listener_entity_technical_name() {
		return $this->listener_entity_technical_name;
	}

	/**
	 * Returns the space id.
	 *
	 * @return int
	 */
	public function get_space_id() {
		return $this->space_id;
	}

	/**
	 * Returns the id of the webhook listener.
	 *
	 * @return int
	 */
	public function get_webhook_listener_id() {
		return $this->webhook_listener_id;
	}

	/**
	 * Returns the webhook's timestamp.
	 *
	 * @return string
	 */
	public function get_timestamp() {
		return $this->timestamp;
	}
}
