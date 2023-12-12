<?php
/**
 *
 * WC_PostFinanceCheckout_Cron Class
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   wallee AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * Class WC_PostFinanceCheckout_Cron.
 *
 * @class WC_PostFinanceCheckout_Cron
 */
/**
 * This class handles the cron jobs
 */
class WC_PostFinanceCheckout_Cron {

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action(
			'cron_schedules',
			array(
				__CLASS__,
				'add_custom_cron_schedule',
			),
			5
		);
	}

	/**
	 * Add cron schedule.
	 *
	 * @param  array $schedules schedules.
	 * @return array
	 */
	public static function add_custom_cron_schedule( $schedules ) {
		$schedules['five_minutes'] = array(
			'interval' => 300,
			'display'  => __( 'Every Five Minutes' ),
		);
		return $schedules;
	}

	/**
	 * Activate the cron.
	 */
	public static function activate() {
		if ( ! wp_next_scheduled( 'postfinancecheckout_five_minutes_cron' ) ) {
			wp_schedule_event( time(), 'five_minutes', 'postfinancecheckout_five_minutes_cron' );
		}
	}

	/**
	 * Deactivate the cron.
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( 'postfinancecheckout_five_minutes_cron' );
	}
}
WC_PostFinanceCheckout_Cron::init();
