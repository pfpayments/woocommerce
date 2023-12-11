<?php
/**
 *
 * WC_PostFinanceCheckout_Admin_Notices Class
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
		add_action( 'admin_notices',
		array(
			__CLASS__,
			'blocks_warning',
		) 
	);

}

	/**
	 * Warn about blocks incompatibility
	 */
	public static function blocks_warning() {
		$is_blocks_active = defined( 'WC_BLOCKS_IS_FEATURE_PLUGIN' );
		// it basically is always true but never hurts to be safe
		$is_wc_active = false;
		$woocommerce_data = '';
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_wc_active = true;
			$woocommerce_data = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php', false, false );
			$is_wc_version_gt = version_compare( $woocommerce_data['Version'], "8.0.0", '>' );
		}
		if ($is_blocks_active || ($is_wc_active && $is_wc_version_gt)) {	
			$class = 'notice notice-warning';
			$message = __( 'The PostFinanceCheckout plugin is currently incompatible with the new Woocommerce Blocks Checkout Template (bundled with Woocommerce 8.0.0+). We are working on compatibility. If payment methods do not appear for you when using PostFinanceCheckout with checkout blocks, please replace the checkout page block with the shortcode for the checkout: <pre>[woocommerce_checkout]</pre>', 'sample-text-domain' );

			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
		}
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
		$space_id        = get_option( WooCommerce_PostFinanceCheckout::CK_SPACE_ID );
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
				if ( 'yes' === get_option( WooCommerce_PostFinanceCheckout::CK_ENFORCE_CONSISTENCY ) ) {
					$error_message = __( "'WooCommerce > Settings > PostFinanceCheckout > Enforce Consistency' and 'WooCommerce > Settings > Tax > Rounding' are both enabled. Please disable at least one of them.", 'woo-postfinancecheckout' );
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
