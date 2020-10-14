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
 * This class handles the cron jobs
 */
class WC_PostFinanceCheckout_Cron {

	/**
	 * Hook in tabs.
	 */
	public static function init(){
		add_action('cron_schedules', array(
			__CLASS__,
			'add_custom_cron_schedule' 
		), 5);
	}

	public static function add_custom_cron_schedule($schedules){
		$schedules['five_minutes'] = array(
			'interval' => 300,
			'display' => __('Every Five Minutes') 
		);
		return $schedules;
	}

	public static function activate(){
		if (!wp_next_scheduled('postfinancecheckout_five_minutes_cron')) {
			wp_schedule_event(time(), 'five_minutes', 'postfinancecheckout_five_minutes_cron');
		}
	}

	public static function deactivate(){
		wp_clear_scheduled_hook('postfinancecheckout_five_minutes_cron');
	}
}
WC_PostFinanceCheckout_Cron::init();