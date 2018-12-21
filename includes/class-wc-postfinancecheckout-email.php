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
 * This class handles the email settings fo PostFinance Checkout.
 */
class WC_PostFinanceCheckout_Email {

	/**
	 * Register email hooks
	 */
	public static function init(){
		add_filter('woocommerce_email_enabled_new_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_cancelled_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_failed_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_customer_on_hold_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_customer_processing_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_customer_completed_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_customer_partially_refunded_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		add_filter('woocommerce_email_enabled_customer_refunded_order', array(
			__CLASS__,
			'send_email_for_order' 
		), 10, 2);
		
		add_filter('woocommerce_before_resend_order_emails', array(
			__CLASS__,
			'before_resend_email' 
		), 10, 1);
		add_filter('woocommerce_after_resend_order_emails', array(
			__CLASS__,
			'after_resend_email' 
		), 10, 2);
		
		add_filter('woocommerce_germanized_order_email_customer_confirmation_sent', array(
		    __CLASS__,
		    'germanized_send_order_confirmation'
		), 10, 2);
		
		add_filter('woocommerce_germanized_order_email_admin_confirmation_sent', array(
		    __CLASS__,
		    'germanized_send_order_confirmation'
		), 10, 2);
		
		
		
		add_filter( 'woocommerce_email_actions', array( __CLASS__, 'add_email_actions' ), 10, 1 );
		add_filter( 'woocommerce_email_classes', array( __CLASS__, 'add_email_classes' ), 100, 1 );
		
	}

	public static function send_email_for_order($enabled, $order){
		if (!($order instanceof WC_Order)) {
			return $enabled;
		}
		if (isset($GLOBALS['_postfinancecheckout_resend_email']) && $GLOBALS['_postfinancecheckout_resend_email']) {
			return $enabled;
		}
		$gateway = wc_get_payment_gateway_by_order($order);
		if ($gateway instanceof WC_PostFinanceCheckout_Gateway) {
		    $send = get_option(WooCommerce_PostFinanceCheckout::CK_SHOP_EMAIL, "yes");
		    if ($send != "yes") {
		        return false;
		    }
		}		
		return $enabled;
	}

	public static function before_resend_email($order){
		$GLOBALS['_postfinancecheckout_resend_email'] = true;
	}

	public static function after_resend_email($order, $email){
		unset($GLOBALS['_postfinancecheckout_resend_email']);
	}
	
	public static function add_email_actions($actions){
	    
		$to_add = array(
			'woocommerce_order_status_postfinancecheckout-redirected_to_processing',
			'woocommerce_order_status_postfinancecheckout-redirected_to_completed',
			'woocommerce_order_status_postfinancecheckout-redirected_to_on-hold',
			'woocommerce_order_status_postfinancecheckout-redirected_to_postfinancecheckout-waiting',
			'woocommerce_order_status_postfinancecheckout-redirected_to_postfinancecheckout-manual',
			'woocommerce_order_status_postfinancecheckout-manual_to_cancelled',
			'woocommerce_order_status_postfinancecheckout-waiting_to_cancelled',
			'woocommerce_order_status_postfinancecheckout-manual_to_processing',
			'woocommerce_order_status_postfinancecheckout-waiting_to_processing'
		);
		
		if(class_exists('woocommerce_wpml')){
		    global $woocommerce_wpml;
		    if($woocommerce_wpml != null){
    			//Add hooks for WPML, for email translations
    			$notifications_all =  array(
    				'woocommerce_order_status_postfinancecheckout-redirected_to_processing_notification',
    				'woocommerce_order_status_postfinancecheckout-redirected_to_completed_notification',
    				'woocommerce_order_status_postfinancecheckout-redirected_to_on-hold_notification',
    				'woocommerce_order_status_postfinancecheckout-redirected_to_postfinancecheckout-waiting_notification',
    				'woocommerce_order_status_postfinancecheckout-redirected_to_postfinancecheckout-manual_notification',
    			);
    			$notifications_customer = array(
    				'woocommerce_order_status_postfinancecheckout-manual_to_processing_notification',
    				'woocommerce_order_status_postfinancecheckout-waiting_to_processing_notification',
    				'woocommerce_order_status_on-hold_to_processing_notification',
    				'woocommerce_order_status_postfinancecheckout-manual_to_cancelled_notification',
    				'woocommerce_order_status_postfinancecheckout-waiting_to_cancelled_notifcation'
    			);
		
    			$wpmlInstance = $woocommerce_wpml;
    			$emailHandler = $wpmlInstance->emails;
    			foreach($notifications_all as $new_action){
    				add_action( $new_action, array(
    					$emailHandler,
    					'refresh_email_lang'
    				), 9 );
    				add_action( $new_action, array(
    					$emailHandler,
    					'new_order_admin_email'
    				), 9 );
    			}
    			foreach($notifications_customer as $new_action){
    				add_action( $new_action, array(
    					$emailHandler,
    					'refresh_email_lang'
    				), 9 );
    			}
			}
		}		
		$actions = array_merge($actions, $to_add);
		return $actions;
	}
	
	
	public static function check_germanized_pay_email_trigger($order_id, $order = false){
	    if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
	        $order = wc_get_order( $order_id );
	    }
	    $gateway = wc_get_payment_gateway_by_order($order);
	    if ($gateway instanceof WC_PostFinanceCheckout_Gateway) {
	        
	        $send = get_option(WooCommerce_PostFinanceCheckout::CK_SHOP_EMAIL, "yes");
	        if ($send != "yes") {
	            return;
	        }	   
	        $mails = WC()->mailer()->get_emails();
	        if(isset($mails['WC_GZD_Email_Customer_Paid_For_Order'])){
	            $mails['WC_GZD_Email_Customer_Paid_For_Order']->trigger($order_id);
	        }
	    }	    
	}
	
	public static function add_email_classes($emails){
	    
	    //Germanized has a special email flow.
	    if(isset($emails['WC_GZD_Email_Customer_Paid_For_Order'])){
	        add_action( 'woocommerce_order_status_postfinancecheckout-redirected_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
	        add_action( 'woocommerce_order_status_postfinancecheckout-manual_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
	        add_action( 'woocommerce_order_status_postfinancecheckout-waiting_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
	        add_action( 'woocommerce_order_status_on-hold_to_processing_notification', array( __CLASS__, 'check_germanized_pay_email_trigger' ), 10, 2 );
	    }	    
	    if( function_exists('wc_gzd_send_instant_order_confirmation') && wc_gzd_send_instant_order_confirmation()){
	        return $emails;
	    }    
    
		foreach($emails as $key => $emailObject){
			switch($key){
				case 'WC_Email_New_Order':
					add_action( 'woocommerce_order_status_postfinancecheckout-redirected_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfinancecheckout-redirected_to_completed_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfinancecheckout-redirected_to_on-hold_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfinancecheckout-redirected_to_postfinancecheckout-waiting_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfinancecheckout-redirected_to_postfinancecheckout-manual_notification', array( $emailObject, 'trigger' ), 10, 2 );
					
					break;
					
				case 'WC_Email_Cancelled_Order':
					add_action( 'woocommerce_order_status_postfinancecheckout-manual_to_cancelled_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfinancecheckout-waiting_to_cancelled_notification', array( $emailObject, 'trigger' ), 10, 2 );
					break;
					
				case 'WC_Email_Customer_On_Hold_Order':
					add_action( 'woocommerce_order_status_postfinancecheckout-redirected_to_on-hold_notification', array( $emailObject, 'trigger' ), 10, 2 );
					break;
					
				
				case 'WC_Email_Customer_Processing_Order':
					add_action( 'woocommerce_order_status_postfinancecheckout-redirected_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfinancecheckout-manual_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
					add_action( 'woocommerce_order_status_postfinancecheckout-waiting_to_processing_notification', array( $emailObject, 'trigger' ), 10, 2 );
					break;
					
				case 'WC_Email_Customer_Completed_Order':
					//Order complete are always send independent of the source status
					break;
					
				case 'WC_Email_Failed_Order':
				case 'WC_Email_Customer_Refunded_Order':
				case 'WC_Email_Customer_Invoice':
					//Do nothing for now
					break;
			}
		}
		
		return $emails;
	}
	
	public static function germanized_send_order_confirmation($email_sent, $order_id){
	    $order = WC_Order_Factory::get_order($order_id);
	    if (!($order instanceof WC_Order)) {
	        return $email_sent;
	    }
	    $gateway = wc_get_payment_gateway_by_order($order);
	    if ($gateway instanceof WC_PostFinanceCheckout_Gateway) {
	        $send = get_option(WooCommerce_PostFinanceCheckout::CK_SHOP_EMAIL, "yes");
	        if ($send != "yes") {
	            return true;
	        }
	    }
	    return $email_sent;
	}
}

WC_PostFinanceCheckout_Email::init();
