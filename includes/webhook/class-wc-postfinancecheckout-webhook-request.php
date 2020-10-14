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
 * Webhook request.
 */
class WC_PostFinanceCheckout_Webhook_Request {
	private $event_id;
	private $entity_id;
	private $listener_entity_id;
	private $listener_entity_technical_name;
	private $space_id;
	private $webhook_listener_id;
	private $timestamp;

	/**
	 * Constructor.
	 *
	 * @param stdClass $model
	 */
	public function __construct($model){
		$this->event_id = $model->eventId;
		$this->entity_id = $model->entityId;
		$this->listener_entity_id = $model->listenerEntityId;
		$this->listener_entity_technical_name = $model->listenerEntityTechnicalName;
		$this->space_id = $model->spaceId;
		$this->webhook_listener_id = $model->webhookListenerId;
		$this->timestamp = $model->timestamp;
	}

	/**
	 * Returns the webhook event's id.
	 *
	 * @return int
	 */
	public function get_event_id(){
		return $this->event_id;
	}

	/**
	 * Returns the id of the webhook event's entity.
	 *
	 * @return int
	 */
	public function get_entity_id(){
		return $this->entity_id;
	}

	/**
	 * Returns the id of the webhook's listener entity.
	 *
	 * @return int
	 */
	public function get_listener_entity_id(){
		return $this->listener_entity_id;
	}

	/**
	 * Returns the technical name of the webhook's listener entity.
	 *
	 * @return string
	 */
	public function get_listener_entity_technical_name(){
		return $this->listener_entity_technical_name;
	}

	/**
	 * Returns the space id.
	 *
	 * @return int
	 */
	public function get_space_id(){
		return $this->space_id;
	}

	/**
	 * Returns the id of the webhook listener.
	 *
	 * @return int
	 */
	public function get_webhook_listener_id(){
		return $this->webhook_listener_id;
	}

	/**
	 * Returns the webhook's timestamp.
	 *
	 * @return string
	 */
	public function get_timestamp(){
		return $this->timestamp;
	}
}