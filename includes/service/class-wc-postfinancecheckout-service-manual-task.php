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
 * This service provides methods to handle manual tasks.
 */
class WC_PostFinanceCheckout_Service_Manual_Task extends WC_PostFinanceCheckout_Service_Abstract {
	const POSTFINANCECHECKOUT_CONFIG_KEY = 'wc_postfinancecheckout_manual_task';

	/**
	 * Returns the number of open manual tasks.
	 *
	 * @return int
	 */
	public function get_number_of_manual_tasks() {
		return get_option( self::POSTFINANCECHECKOUT_CONFIG_KEY, 0 );
	}

	/**
	 * Updates the number of open manual tasks.
	 *
	 * @return int
	 */
	public function update() {
		$number_of_manual_tasks = 0;
		$manual_task_service = new \PostFinanceCheckout\Sdk\Service\ManualTaskService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );

		$space_id = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_ID );
		if ( ! empty( $space_id ) ) {
			$number_of_manual_tasks = $manual_task_service->count(
				$space_id,
				$this->create_entity_filter( 'state', \PostFinanceCheckout\Sdk\Model\ManualTaskState::OPEN )
			);
			update_option( self::POSTFINANCECHECKOUT_CONFIG_KEY, $number_of_manual_tasks );
		}

		return $number_of_manual_tasks;
	}
}
