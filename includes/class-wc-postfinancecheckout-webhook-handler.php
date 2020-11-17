<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
 *
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * This class handles the webhooks of PostFinanceCheckout
 */
class WC_PostFinanceCheckout_Webhook_Handler {
	
	public static function init(){
		add_action('woocommerce_api_postfinancecheckout_webhook', array(
			__CLASS__,
			'process' 
		));
	}
	
	public static function handle_webhook_errors($errno, $errstr, $errfile, $errline){
		$fatal = E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE;
		if($errno & $fatal){
			throw new ErrorException($errstr, $errno, E_ERROR, $errfile, $errline);
		}
		return false;
		
	}

	/**
	 * Process the webhook call.
	 */
	public static function process(){
	    $webhook_service = WC_PostFinanceCheckout_Service_Webhook::instance();
	    
	    //We set the status to 500, so if we encounter a state where the process crashes the webhook is marked as failed.
	    header('HTTP/1.1 500 Internal Server Error');
		$requestBody = trim(file_get_contents("php://input"));
		set_error_handler(array(__CLASS__, 'handle_webhook_errors'));
		try{
		    $request = new WC_PostFinanceCheckout_Webhook_Request(json_decode($requestBody));
			$webhook_model = $webhook_service->get_webhook_entity_for_id($request->get_listener_entity_id());
			if ($webhook_model === null) {
			    WooCommerce_PostFinanceCheckout::instance()->log(sprintf('Could not retrieve webhook model for listener entity id: %s', $request->get_listener_entity_id()), WC_Log_Levels::ERROR);
				echo sprintf('Could not retrieve webhook model for listener entity id: %s', $request->get_listener_entity_id());
				exit();
				
			}
			$webhook_handler_class_name = $webhook_model->get_handler_class_name();
			$webhook_handler = $webhook_handler_class_name::instance();
			$webhook_handler->process($request);
			header('HTTP/1.1 200 OK');
		}
		catch(Exception $e){
		    WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::ERROR);
			echo sprintf($e->getMessage());
			exit();
		}
		exit();
	}
}
WC_PostFinanceCheckout_Webhook_Handler::init();
