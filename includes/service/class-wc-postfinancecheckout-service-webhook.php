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
 * This service handles webhooks.
 */
class WC_PostFinanceCheckout_Service_Webhook extends WC_PostFinanceCheckout_Service_Abstract {

	const POSTFINANCECHECKOUT_MANUAL_TASK = 1487165678181;
	const POSTFINANCECHECKOUT_PAYMENT_METHOD_CONFIGURATION = 1472041857405;
	const POSTFINANCECHECKOUT_TRANSACTION = 1472041829003;
	const POSTFINANCECHECKOUT_DELIVERY_INDICATION = 1472041819799;
	const POSTFINANCECHECKOUT_TRANSACTION_INVOICE = 1472041816898;
	const POSTFINANCECHECKOUT_TRANSACTION_COMPLETION = 1472041831364;
	const POSTFINANCECHECKOUT_TRANSACTION_VOID = 1472041867364;
	const POSTFINANCECHECKOUT_REFUND = 1472041839405;
	const POSTFINANCECHECKOUT_TOKEN = 1472041806455;
	const POSTFINANCECHECKOUT_TOKEN_VERSION = 1472041811051;

	/**
	 * The webhook listener API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\WebhookListenerService
	 */
	private $webhook_listener_service;

	/**
	 * The webhook url API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\WebhookUrlService
	 */
	private $webhook_url_service;


	/**
	 * Webhook entities.
	 *
	 * @var array
	 */
	private $webhook_entities = array();

	/**
	 * Construct.
	 *
	 * Constructor to register the webhook entites.
	 */
	public function __construct() {
		$this->init_webhook_entities();
	}

	/**
	 * Initializes webhook entities with their specific configurations.
	 */
	private function init_webhook_entities() {
		$this->webhook_entities[ self::POSTFINANCECHECKOUT_MANUAL_TASK ] = new WC_PostFinanceCheckout_Webhook_Entity(
			self::POSTFINANCECHECKOUT_MANUAL_TASK,
			'Manual Task',
			array(
				\PostFinanceCheckout\Sdk\Model\ManualTaskState::DONE,
				\PostFinanceCheckout\Sdk\Model\ManualTaskState::EXPIRED,
				\PostFinanceCheckout\Sdk\Model\ManualTaskState::OPEN,
			),
			'WC_PostFinanceCheckout_Webhook_Manual_Task'
		);
		$this->webhook_entities[ self::POSTFINANCECHECKOUT_PAYMENT_METHOD_CONFIGURATION ] = new WC_PostFinanceCheckout_Webhook_Entity(
			self::POSTFINANCECHECKOUT_PAYMENT_METHOD_CONFIGURATION,
			'Payment Method Configuration',
			array(
				\PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE,
				\PostFinanceCheckout\Sdk\Model\CreationEntityState::DELETED,
				\PostFinanceCheckout\Sdk\Model\CreationEntityState::DELETING,
				\PostFinanceCheckout\Sdk\Model\CreationEntityState::INACTIVE,
			),
			'WC_PostFinanceCheckout_Webhook_Method_Configuration',
			true
		);
		$this->webhook_entities[ self::POSTFINANCECHECKOUT_TRANSACTION ] = new WC_PostFinanceCheckout_Webhook_Entity(
			self::POSTFINANCECHECKOUT_TRANSACTION,
			'Transaction',
			array(
				\PostFinanceCheckout\Sdk\Model\TransactionState::CONFIRMED,
				\PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED,
				\PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE,
				\PostFinanceCheckout\Sdk\Model\TransactionState::FAILED,
				\PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
				\PostFinanceCheckout\Sdk\Model\TransactionState::VOIDED,
				\PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
				\PostFinanceCheckout\Sdk\Model\TransactionState::PROCESSING,
			),
			'WC_PostFinanceCheckout_Webhook_Transaction'
		);
		$this->webhook_entities[ self::POSTFINANCECHECKOUT_DELIVERY_INDICATION ] = new WC_PostFinanceCheckout_Webhook_Entity(
			self::POSTFINANCECHECKOUT_DELIVERY_INDICATION,
			'Delivery Indication',
			array(
				\PostFinanceCheckout\Sdk\Model\DeliveryIndicationState::MANUAL_CHECK_REQUIRED,
			),
			'WC_PostFinanceCheckout_Webhook_Delivery_Indication'
		);

		$this->webhook_entities[ self::POSTFINANCECHECKOUT_TRANSACTION_INVOICE ] = new WC_PostFinanceCheckout_Webhook_Entity(
			self::POSTFINANCECHECKOUT_TRANSACTION_INVOICE,
			'Transaction Invoice',
			array(
				\PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::NOT_APPLICABLE,
				\PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::PAID,
				\PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::DERECOGNIZED,
			),
			'WC_PostFinanceCheckout_Webhook_Transaction_Invoice'
		);

		$this->webhook_entities[ self::POSTFINANCECHECKOUT_TRANSACTION_COMPLETION ] = new WC_PostFinanceCheckout_Webhook_Entity(
			self::POSTFINANCECHECKOUT_TRANSACTION_COMPLETION,
			'Transaction Completion',
			array(
				\PostFinanceCheckout\Sdk\Model\TransactionCompletionState::FAILED,
				\PostFinanceCheckout\Sdk\Model\TransactionCompletionState::SUCCESSFUL,
			),
			'WC_PostFinanceCheckout_Webhook_Transaction_Completion'
		);

		$this->webhook_entities[ self::POSTFINANCECHECKOUT_TRANSACTION_VOID ] = new WC_PostFinanceCheckout_Webhook_Entity(
			self::POSTFINANCECHECKOUT_TRANSACTION_VOID,
			'Transaction Void',
			array(
				\PostFinanceCheckout\Sdk\Model\TransactionVoidState::FAILED,
				\PostFinanceCheckout\Sdk\Model\TransactionVoidState::SUCCESSFUL,
			),
			'WC_PostFinanceCheckout_Webhook_Transaction_Void'
		);

		$this->webhook_entities[ self::POSTFINANCECHECKOUT_REFUND ] = new WC_PostFinanceCheckout_Webhook_Entity(
			self::POSTFINANCECHECKOUT_REFUND,
			'Refund',
			array(
				\PostFinanceCheckout\Sdk\Model\RefundState::FAILED,
				\PostFinanceCheckout\Sdk\Model\RefundState::SUCCESSFUL,
			),
			'WC_PostFinanceCheckout_Webhook_Refund'
		);
		$this->webhook_entities[ self::POSTFINANCECHECKOUT_TOKEN ] = new WC_PostFinanceCheckout_Webhook_Entity(
			self::POSTFINANCECHECKOUT_TOKEN,
			'Token',
			array(
				\PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE,
				\PostFinanceCheckout\Sdk\Model\CreationEntityState::DELETED,
				\PostFinanceCheckout\Sdk\Model\CreationEntityState::DELETING,
				\PostFinanceCheckout\Sdk\Model\CreationEntityState::INACTIVE,
			),
			'WC_PostFinanceCheckout_Webhook_Token'
		);
		$this->webhook_entities[ self::POSTFINANCECHECKOUT_TOKEN_VERSION ] = new WC_PostFinanceCheckout_Webhook_Entity(
			self::POSTFINANCECHECKOUT_TOKEN_VERSION,
			'Token Version',
			array(
				\PostFinanceCheckout\Sdk\Model\TokenVersionState::ACTIVE,
				\PostFinanceCheckout\Sdk\Model\TokenVersionState::OBSOLETE,
			),
			'WC_PostFinanceCheckout_Webhook_Token_Version'
		);
	}

	/**
	 * Installs the necessary webhooks in PostFinance Checkout.
	 */
	public function install() {
		$space_id = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_ID );
		if ( ! empty( $space_id ) ) {
			$webhook_url = $this->get_webhook_url( $space_id );
			if ( null == $webhook_url ) {
				$webhook_url = $this->create_webhook_url( $space_id );
			}
			$existing_listeners = $this->get_webhook_listeners( $space_id, $webhook_url );
			foreach ( $this->webhook_entities as $webhook_entity ) {
				/* @var WC_PostFinanceCheckout_Webhook_Entity $webhook_entity */ //phpcs:ignore
				$exists = false;
				foreach ( $existing_listeners as $existing_listener ) {
					if ( $existing_listener->getEntity() == $webhook_entity->get_id() ) {
						$exists = true;
					}
				}
				if ( ! $exists ) {
					$this->create_webhook_listener( $webhook_entity, $space_id, $webhook_url );
				}
			}
		}
	}

	/**
	 * Get the webhook entity for a specific ID or throws an exception if not found.
	 *
	 * @param mixed $id The ID of the webhook entity to retrieve.
	 * @return WC_PostFinanceCheckout_Webhook_Entity The webhook entity associated with the given ID.
	 * @throws Exception If the webhook entity cannot be found.
	 */
	public function get_webhook_entity_for_id( $id ) {
		if ( ! isset( $this->webhook_entities[ $id ] ) ) {
			throw new Exception( sprintf( 'Could not retrieve webhook model for listener entity id: %s', esc_attr( $id ) ) );
		}
		return $this->webhook_entities[ $id ];
	}

	/**
	 * Create a webhook listener.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Entity $entity entity.
	 * @param int $space_id space id.
	 * @param \PostFinanceCheckout\Sdk\Model\WebhookUrl $webhook_url webhook url.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\WebhookListenerCreate
	 * @throws \Exception Exception.
	 */
	protected function create_webhook_listener( WC_PostFinanceCheckout_Webhook_Entity $entity, $space_id, \PostFinanceCheckout\Sdk\Model\WebhookUrl $webhook_url ) {
		$webhook_listener = new \PostFinanceCheckout\Sdk\Model\WebhookListenerCreate();
		$webhook_listener->setEntity( $entity->get_id() );
		$webhook_listener->setEntityStates( $entity->get_states() );
		$webhook_listener->setName( 'Woocommerce ' . $entity->get_name() );
		$webhook_listener->setState( \PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE );
		$webhook_listener->setUrl( $webhook_url->getId() );
		$webhook_listener->setNotifyEveryChange( $entity->is_notify_every_change() );
		$webhook_listener->setEnablePayloadSignatureAndState( true );
		return $this->get_webhook_listener_service()->create( $space_id, $webhook_listener );
	}

	/**
	 * Returns the existing webhook listeners.
	 *
	 * @param int $space_id space id.
	 * @param \PostFinanceCheckout\Sdk\Model\WebhookUrl $webhook_url webhook url.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\WebhookListener[]
	 * @throws \Exception Exception.
	 */
	protected function get_webhook_listeners( $space_id, \PostFinanceCheckout\Sdk\Model\WebhookUrl $webhook_url ) {
		$query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		$filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
		$filter->setType( \PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND );
		$filter->setChildren(
			array(
				$this->create_entity_filter( 'state', \PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE ),
				$this->create_entity_filter( 'url.id', $webhook_url->getId() ),
			)
		);
		$query->setFilter( $filter );
		return $this->get_webhook_listener_service()->search( $space_id, $query );
	}

	/**
	 * Creates a webhook url.
	 *
	 * @param int $space_id space id.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\WebhookUrlCreate
	 * @throws \Exception Exception.
	 */
	protected function create_webhook_url( $space_id ) {
		$webhook_url = new \PostFinanceCheckout\Sdk\Model\WebhookUrlCreate();
		$webhook_url->setUrl( $this->get_url() );
		$webhook_url->setState( \PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE );
		$webhook_url->setName( 'Woocommerce' );
		return $this->get_webhook_url_service()->create( $space_id, $webhook_url );
	}

	/**
	 * Returns the existing webhook url if there is one.
	 *
	 * @param int $space_id space id.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\WebhookUrl
	 * @throws \Exception Exception.
	 */
	protected function get_webhook_url( $space_id ) {
		$query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		$filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
		$filter->setType( \PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND );
		$filter->setChildren(
			array(
				$this->create_entity_filter( 'state', \PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE ),
				$this->create_entity_filter( 'url', $this->get_url() ),
			)
		);
		$query->setFilter( $filter );
		$query->setNumberOfEntities( 1 );
		try {
			$result = $this->get_webhook_url_service()->search( $space_id, $query );
			if ( ! empty( $result ) ) {
				return $result[0];
			} else {
				return null;
			}
		} catch ( \Exception $e ) {
			WooCommerce_PostFinanceCheckout::instance()->log( $e->getMessage(), WC_Log_Levels::ERROR );
		}
	}

	/**
	 * Returns the webhook endpoint URL.
	 *
	 * @return string
	 */
	protected function get_url() {
		return add_query_arg( 'wc-api', 'postfinancecheckout_webhook', home_url( '/' ) );
	}

	/**
	 * Returns the webhook listener API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\WebhookListenerService
	 * @throws \Exception Exception.
	 */
	protected function get_webhook_listener_service() {
		if ( null == $this->webhook_listener_service ) {
			$this->webhook_listener_service = new \PostFinanceCheckout\Sdk\Service\WebhookListenerService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		}
		return $this->webhook_listener_service;
	}

	/**
	 * Returns the webhook url API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\WebhookUrlService
	 * @throws \Exception Exception.
	 */
	protected function get_webhook_url_service() {
		if ( null == $this->webhook_url_service ) {
			$this->webhook_url_service = new \PostFinanceCheckout\Sdk\Service\WebhookUrlService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		}
		return $this->webhook_url_service;
	}
}
