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
 * WC PostFinanceCheckout Admin Refund class
 */
class WC_PostFinanceCheckout_Admin_Refund {
	private static $refundable_states = array(
	    \PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
	    \PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE,
	    \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL 
	);

	public static function init(){
		add_action('woocommerce_order_item_add_action_buttons', array(
			__CLASS__,
			'render_refund_button_state' 
		), 1000);
		
		add_action('woocommerce_create_refund', array(
			__CLASS__,
			'store_refund_in_globals' 
		), 10, 2);
		add_action('postfinancecheckout_five_minutes_cron', array(
			__CLASS__,
			'update_refunds' 
		));
		
		add_action('woocommerce_admin_order_items_after_refunds', array(
			__CLASS__,
			'render_refund_states' 
		), 1000, 1);
		
		add_action('postfinancecheckout_update_running_jobs', array(
			__CLASS__,
			'update_for_order' 
		));
	}

	public static function render_refund_button_state(WC_Order $order){
		$gateway = wc_get_payment_gateway_by_order($order);
		if ($gateway instanceof WC_PostFinanceCheckout_Gateway) {
		    $transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id($order->get_id());
			if (!in_array($transaction_info->get_state(), self::$refundable_states)) {
				echo '<span id="postfinancecheckout-remove-refund" style="dispaly:none;"></span>';
			}
			else {
			    $existing_refund_job = WC_PostFinanceCheckout_Entity_Refund_Job::load_running_refund_for_transaction($transaction_info->get_space_id(), 
						$transaction_info->get_transaction_id());
				if ($existing_refund_job->get_id() > 0) {
					echo '<span class="postfinancecheckout-action-in-progress">' . __('There is a refund in progress.', 'woo-postfinancecheckout') . '</span>';
					echo '<button type="button" class="button postfinancecheckout-update-order">' . __('Update', 'woo-postfinancecheckout') . '</button>';
					echo '<span id="postfinancecheckout-remove-refund" style="dispaly:none;"></span>';
				}
				echo '<span id="postfinancecheckout-refund-restrictions" style="display:none;"></span>';
			}
		}
	}

	public static function render_refund_states($order_id){
	    $refunds = WC_PostFinanceCheckout_Entity_Refund_Job::load_refunds_for_order($order_id);
		if (!empty($refunds)) {
			echo '<tr style="display:none"><td>';
			foreach ($refunds as $refund) {
				echo '<div class="postfinancecheckout-refund-status" data-refund-id="' . $refund->get_wc_refund_id() . '" data-refund-state="' .
						 $refund->get_state() . '" ></div>';
			}
			echo '</td></tr>';
		}
	}

	public static function store_refund_in_globals($refund, $request_args){
		$GLOBALS['postfinancecheckout_refund_id'] = $refund->get_id();
		$GLOBALS['postfinancecheckout_refund_request_args'] = $request_args;
	}

	public static function execute_refund(WC_Order $order, WC_Order_Refund $refund){
		global $wpdb;
		$current_refund_job_id = null;
		$transaction_info = null;
		$refund_service = WC_PostFinanceCheckout_Service_Refund::instance();
		try {
			wc_transaction_query("start");
			$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id($order->get_id());
			if (!$transaction_info->get_id()) {
				throw new Exception(__('Could not load corresponding transaction', 'woo-postfinancecheckout'));
			}
			
			WC_PostFinanceCheckout_Helper::instance()->lock_by_transaction_id($transaction_info->get_space_id(), $transaction_info->get_transaction_id());
			
			if (WC_PostFinanceCheckout_Entity_Refund_Job::count_running_refund_for_transaction($transaction_info->get_space_id(), 
					$transaction_info->get_transaction_id()) > 0) {
				throw new Exception(__('Please wait until the pending refund is processed.', 'woo-postfinancecheckout'));
			}
			$refund_create = $refund_service->create($order, $refund);
			$refund_job = self::create_refund_job($order, $refund, $refund_create);
			$current_refund_job_id = $refund_job->get_id();
			
			$refund->add_meta_data('_postfinancecheckout_refund_job_id', $refund_job->get_id());
			$refund->set_status("pending");
			$refund->save();
			wc_transaction_query("commit");
		}
		catch (Exception $e) {
			wc_transaction_query("rolback");
			throw $e;
		}
		self::send_refund($current_refund_job_id);
	}

	protected static function send_refund($refund_job_id){
		global $wpdb;
		$refund_job = WC_PostFinanceCheckout_Entity_Refund_Job::load_by_id($refund_job_id);
		wc_transaction_query("start");
		WC_PostFinanceCheckout_Helper::instance()->lock_by_transaction_id($refund_job->get_space_id(), $refund_job->get_transaction_id());
		//Reload void job;
		$refund_job = WC_PostFinanceCheckout_Entity_Refund_Job::load_by_id($refund_job_id);
		
		if ($refund_job->get_state() != WC_PostFinanceCheckout_Entity_Refund_Job::STATE_CREATED) {
			//Already sent in the meantime
			wc_transaction_query("rollback");
			return;
		}
		try {
		    $refund_service = WC_PostFinanceCheckout_Service_Refund::instance();
			$executed_refund = $refund_service->refund($refund_job->get_space_id(), $refund_job->get_refund());
			$refund_job->set_state(WC_PostFinanceCheckout_Entity_Refund_Job::STATE_SENT);
			
			if ($executed_refund->getState() == \PostFinanceCheckout\Sdk\Model\RefundState::PENDING) {
			    $refund_job->set_state(WC_PostFinanceCheckout_Entity_Refund_Job::STATE_PENDING);
			}
			$refund_job->save();
			wc_transaction_query("commit");
		}
		catch (\PostFinanceCheckout\Sdk\ApiException $e) {
		    if ($e->getResponseObject() instanceof \PostFinanceCheckout\Sdk\Model\ClientError) {
		        $refund_job->set_state(WC_PostFinanceCheckout_Entity_Refund_Job::STATE_FAILURE);
		        $refund_job->save();
		        wc_transaction_query("commit");
		    }
		    else{
		        $refund_job->save();
		        wc_transaction_query("commit");
		        WooCommerce_PostFinanceCheckout::instance()->log('Error sending refund. '.$e->getMessage(), WC_Log_Levels::INFO);
		        throw new Exception(sprintf(__('There has been an error while sending the refund to the gateway. Error: %s', 'woo-postfinancecheckout'), $e->getMessage()));
		    }
		}
		catch (Exception $e) {
			$refund_job->save();
			wc_transaction_query("commit");
			WooCommerce_PostFinanceCheckout::instance()->log('Error sending refund. '.$e->getMessage(), WC_Log_Levels::INFO);
			throw new Exception(sprintf(__('There has been an error while sending the refund to the gateway. Error: %s', 'woo-postfinancecheckout'), $e->getMessage()));
		}
	}

	public static function update_for_order(WC_Order $order){
	    $data = WC_PostFinanceCheckout_Helper::instance()->get_transaction_id_map_for_order($order);
		$refund_job = WC_PostFinanceCheckout_Entity_Refund_Job::load_running_refund_for_transaction($data['space_id'], $data['transaction_id']);
		
		if ($refund_job->get_state() == WC_PostFinanceCheckout_Entity_Refund_Job::STATE_CREATED) {
			self::send_refund($refund_job->get_id());
		}
	}

	public static function update_refunds(){
	    $to_process = WC_PostFinanceCheckout_Entity_Refund_Job::load_not_sent_job_ids();
		foreach ($to_process as $id) {
			try {
				self::send_refund($id);
			}
			catch (Exception $e) {
				$message = sprintf(__('Error updating refund job with id %d: %s', 'woo-postfinancecheckout'), $id, $e->getMessage());
				WooCommerce_PostFinanceCheckout::instance()->log($message, WC_Log_Levels::ERROR);
			}
		}
	}

	/**
	 * Creates a new refund job for the given order and refund.
	 *
	 * @param WC_Order $order
	 * @param WC_Order_Refund $refund
	 * @param \PostFinanceCheckout\Sdk\Model\RefundCreate $refund_create
	 * @return WC_PostFinanceCheckout_Entity_Refund_Job
	 */
	private static function create_refund_job(WC_Order $order, WC_Order_Refund $refund, \PostFinanceCheckout\Sdk\Model\RefundCreate $refund_create){
	    $data = WC_PostFinanceCheckout_Helper::instance()->get_transaction_id_map_for_order($order);
	    $refund_job = new WC_PostFinanceCheckout_Entity_Refund_Job();
	    $refund_job->set_state(WC_PostFinanceCheckout_Entity_Refund_Job::STATE_CREATED);
		$refund_job->set_wc_refund_id($refund->get_id());
		$refund_job->set_order_id($order->get_id());
		$refund_job->set_space_id($data['space_id']);
		$refund_job->set_transaction_id($refund_create->getTransaction());
		$refund_job->set_external_id($refund_create->getExternalId());
		$refund_job->set_refund($refund_create);
		$refund_job->save();
		return $refund_job;
	}
}
WC_PostFinanceCheckout_Admin_Refund::init();
