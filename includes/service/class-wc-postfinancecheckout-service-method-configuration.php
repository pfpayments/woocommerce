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
 * WC_PostFinanceCheckout_Service_Method_Configuration Class.
 */
class WC_PostFinanceCheckout_Service_Method_Configuration extends WC_PostFinanceCheckout_Service_Abstract {

	/**
	 * Updates the data of the payment method configuration.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration $configuration configuration.
	 * @throws Exception Exception.
	 */
	public function update_data( \PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration $configuration ) {
		/* @var WC_PostFinanceCheckout_Entity_Method_Configuration $entity */ //phpcs:ignore
		$entity = WC_PostFinanceCheckout_Entity_Method_Configuration::load_by_configuration( $configuration->getLinkedSpaceId(), $configuration->getId() );
		if ( $entity->get_id() !== null && $this->has_changed( $configuration, $entity ) ) {
			$entity->set_configuration_name( $configuration->getName() );
			$entity->set_title( $configuration->getResolvedTitle() );
			$entity->set_description( $configuration->getResolvedDescription() );
			$entity->set_image( $this->get_resource_path( $configuration->getResolvedImageUrl() ) );
			$entity->set_image_base( $this->get_resource_base( $configuration->getResolvedImageUrl() ) );
			$entity->set_state( $this->get_configuration_state( $configuration ) );
			$entity->save();
		}
	}

	/**
	 * Has changed.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration $configuration configuration.
	 * @param WC_PostFinanceCheckout_Entity_Method_Configuration        $entity entity.
	 * @return bool
	 */
	private function has_changed( \PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration $configuration, WC_PostFinanceCheckout_Entity_Method_Configuration $entity ) {

		if ( $this->get_configuration_state( $configuration ) != $entity->get_state() ) {
			return true;
		}
		if ( $configuration->getName() != $entity->get_configuration_name() ) {
			return true;
		}
		if ( $configuration->getResolvedTitle() != $entity->get_title() ) {
			return true;
		}
		if ( $configuration->getResolvedDescription() != $entity->get_description() ) {
			return true;
		}
		if ( $this->get_resource_path( $configuration->getResolvedImageUrl() ) != $entity->get_image() ) {
			return true;
		}
		if ( $this->get_resource_base( $configuration->getResolvedImageUrl() ) != $entity->get_image_base() ) {
			return true;
		}
		return false;
	}

	/**
	 * Synchronizes the payment method configurations from PostFinance Checkout.
	 */
	public function synchronize() {
		$existing_found = array();
		$space_id = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_ID );

		$existing_configurations = WC_PostFinanceCheckout_Entity_Method_Configuration::load_all();

		if ( ! empty( $space_id ) ) {
			$payment_method_configuration_service = new \PostFinanceCheckout\Sdk\Service\PaymentMethodConfigurationService(
				WC_PostFinanceCheckout_Helper::instance()->get_api_client()
			);
			$configurations = $payment_method_configuration_service->search(
				$space_id,
				new \PostFinanceCheckout\Sdk\Model\EntityQuery()
			);

			foreach ( $configurations as $configuration ) {
				/* @var WC_PostFinanceCheckout_Entity_Method_Configuration $method */ //phpcs:ignore
				$method = WC_PostFinanceCheckout_Entity_Method_Configuration::load_by_configuration( $space_id, $configuration->getId() );
				if ( $method->get_id() !== null ) {
					$existing_found[] = $method->get_id();
				}

				$method->set_space_id( $space_id );
				$method->set_configuration_id( $configuration->getId() );
				$method->set_configuration_name( $configuration->getName() );
				$method->set_state( $this->get_configuration_state( $configuration ) );
				$method->set_title( $configuration->getResolvedTitle() );
				$method->set_description( $configuration->getResolvedDescription() );

				$method->set_image( $this->get_resource_path( $configuration->getResolvedImageUrl() ) );
				$method->set_image_base( $this->get_resource_base( $configuration->getResolvedImageUrl() ) );
				$method->save();
			}
		}
		foreach ( $existing_configurations as $existing_configuration ) {
			if ( ! in_array( $existing_configuration->get_id(), $existing_found, true ) ) {
				$existing_configuration->set_state( WC_PostFinanceCheckout_Entity_Method_Configuration::POSTFINANCECHECKOUT_STATE_HIDDEN );
				$existing_configuration->save();
			}
		}
		delete_transient( 'wc_postfinancecheckout_payment_methods' );
	}


	/**
	 * Returns the payment method for the given id.
	 *
	 * @param int $id id.
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentMethod
	 */
	protected function get_payment_method( $id ) {
		/* @var WC_PostFinanceCheckout_Provider_Payment_Method */ //phpcs:ignore
		$method_provider = WC_PostFinanceCheckout_Provider_Payment_Method::instance();
		return $method_provider->find( $id );
	}

	/**
	 * Returns the state for the payment method configuration.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration $configuration configuration.
	 * @return string
	 */
	protected function get_configuration_state( \PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration $configuration ) {
		switch ( $configuration->getState() ) {
			case \PostFinanceCheckout\Sdk\Model\CreationEntityState::ACTIVE:
				return WC_PostFinanceCheckout_Entity_Method_Configuration::POSTFINANCECHECKOUT_STATE_ACTIVE;
			case \PostFinanceCheckout\Sdk\Model\CreationEntityState::INACTIVE:
				return WC_PostFinanceCheckout_Entity_Method_Configuration::POSTFINANCECHECKOUT_STATE_INACTIVE;
			default:
				return WC_PostFinanceCheckout_Entity_Method_Configuration::POSTFINANCECHECKOUT_STATE_HIDDEN;
		}
	}
}
