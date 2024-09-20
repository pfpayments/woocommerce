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
 * WC PostFinanceCheckout Admin Notices class
 */
class WC_PostFinanceCheckout_Admin_Notices {

	/**
	 * Init.
	 *
	 * @return void
	 */
	public static function init() {
		add_action(
			'admin_notices',
			array(
				__CLASS__,
				'manual_tasks_notices',
			)
		);
		add_action(
			'admin_notices',
			array(
				__CLASS__,
				'round_subtotal_notices',
			)
		);
	}

	/**
	 * Manual task notices.
	 *
	 * @return void
	 */
	public static function manual_tasks_notices() {
		$number_of_manual_tasks = WC_PostFinanceCheckout_Service_Manual_Task::instance()->get_number_of_manual_tasks();
		if ( 0 === (int) $number_of_manual_tasks ) {
			return;
		}
		$manual_taks_url = self::get_manual_tasks_url();
		require_once WC_POSTFINANCECHECKOUT_ABSPATH . '/views/admin-notices/manual-tasks.php';
	}

	/**
	 * Returns the URL to check the open manual tasks.
	 *
	 * @return string
	 */
	protected static function get_manual_tasks_url() {
		$manual_task_url = WC_PostFinanceCheckout_Helper::instance()->get_base_gateway_url();
		$space_id = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_ID );
		if ( ! empty( $space_id ) ) {
			$manual_task_url .= '/s/' . $space_id . '/manual-task/list';
		}

		return $manual_task_url;
	}

	/**
	 * Round subtotal notices.
	 *
	 * @return void
	 */
	public static function round_subtotal_notices() {
		$screen = get_current_screen();
		if ( 'woocommerce_page_wc-settings' === $screen->id ) {
			if ( wc_tax_enabled() && ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) ) {
				if ( 'yes' === get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_ENFORCE_CONSISTENCY ) ) {
					$error_message = esc_html__( "'WooCommerce > Settings > PostFinanceCheckout > Enforce Consistency' and 'WooCommerce > Settings > Tax > Rounding' are both enabled. Please disable at least one of them.", 'woo-postfinancecheckout' );
					WooCommerce_PostFinanceCheckout::instance()->log( $error_message, WC_Log_Levels::ERROR );
					require_once WC_POSTFINANCECHECKOUT_ABSPATH . '/views/admin-notices/round-subtotal-warning.php';
				}
			}
		}
	}

	/**
	 * Migration failed notices.
	 *
	 * @return void
	 */
	public static function migration_failed_notices() {
		require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'views/admin-notices/migration-failed.php';
	}

	/**
	 * Plugin deactivated.
	 *
	 * @return void
	 */
	public static function plugin_deactivated() {
		require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'views/admin-notices/plugin-deactivated.php';
	}
}
WC_PostFinanceCheckout_Admin_Notices::init();
