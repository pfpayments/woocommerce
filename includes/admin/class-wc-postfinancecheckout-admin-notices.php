<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * WC PostFinanceCheckout Admin Notices class
 */
class WC_PostFinanceCheckout_Admin_Notices {

	public static function init(){
		add_action('admin_notices', array(
			__CLASS__,
			'manual_tasks_notices' 
		));
	}

	public static function manual_tasks_notices(){
	    $number_of_manual_tasks = WC_PostFinanceCheckout_Service_Manual_Task::instance()->get_number_of_manual_tasks();
		if ($number_of_manual_tasks == 0) {
			return;
		}
		$manual_taks_url = self::get_manual_tasks_url();
		require_once WC_POSTFINANCECHECKOUT_ABSPATH.'/views/admin-notices/manual-tasks.php';
	}

	/**
	 * Returns the URL to check the open manual tasks.
	 *
	 * @return string
	 */
	protected static function get_manual_tasks_url(){
	    $manual_task_url = WC_PostFinanceCheckout_Helper::instance()->get_base_gateway_url();
	    $space_id = get_option(WooCommerce_PostFinanceCheckout::CK_SPACE_ID);
		if (!empty($space_id)) {
			$manual_task_url .= '/s/' . $space_id . '/manual-task/list';
		}
		
		return $manual_task_url;
	}

	public static function migration_failed_notices(){
	    require_once WC_POSTFINANCECHECKOUT_ABSPATH.'views/admin-notices/migration-failed.php';
	}
	
	public static function plugin_deactivated(){
	    require_once WC_POSTFINANCECHECKOUT_ABSPATH.'views/admin-notices/plugin-deactivated.php';
	}
}
WC_PostFinanceCheckout_Admin_Notices::init();
