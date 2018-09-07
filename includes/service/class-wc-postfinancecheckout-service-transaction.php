<?php
if (!defined('ABSPATH')) {
	exit(); // Exit if accessed directly.
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
 * This service provides functions to deal with PostFinance Checkout transactions.
 */
class WC_PostFinanceCheckout_Service_Transaction extends WC_PostFinanceCheckout_Service_Abstract {
	
	/**
	 * Cache for cart transactions.
	 *
	 * @var \PostFinanceCheckout\Sdk\Model\Transaction[]
	 */
	private static $transaction_cache = array();
	
	/**
	 * Cache for possible payment methods by cart.
	 *
	 * @var \PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration[]
	 */
	private static $possible_payment_method_cache = array();
	
	/**
	 * The transaction API service.
	 *
	 * @var \PostFinanceCheckout\Sdk\Service\TransactionService
	 */
	private $transaction_service;
	
	/**
	 * The charge attempt API service.
	 * 
	 * @var \PostFinanceCheckout\Sdk\Service\ChargeAttemptService
	 */
	private $charge_attempt_service;

	/**
	 * Returns the transaction API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\TransactionService
	 */
	protected function get_transaction_service(){
		if ($this->transaction_service === null) {
		    $this->transaction_service = new \PostFinanceCheckout\Sdk\Service\TransactionService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		}
		return $this->transaction_service;
	}

	/**
	 * Returns the charge attempt API service.
	 *
	 * @return \PostFinanceCheckout\Sdk\Service\ChargeAttemptService
	 */
	protected function get_charge_attempt_service(){
		if ($this->charge_attempt_service === null) {
		    $this->charge_attempt_service = new \PostFinanceCheckout\Sdk\Service\ChargeAttemptService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
		}
		return $this->charge_attempt_service;
	}

	/**
	 * Wait for the transaction to be in one of the given states.
	 *
	 * @param WC_Order $order
	 * @param array $states
	 * @param int $maxWaitTime
	 * @return boolean
	 */
	public function wait_for_transaction_state(WC_Order $order, array $states, $max_wait_time = 10){
		$start_time = microtime(true);
		while (true) {
			
		    $transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_newest_by_mapped_order_id($order->get_id());
			if (in_array($transaction_info->get_state(), $states)) {
				return true;
			}
			
			if (microtime(true) - $start_time >= $max_wait_time) {
				return false;
			}
			sleep(1);
		}
	}

	/**
	 * Returns the URL to PostFinance Checkout's JavaScript library that is necessary to display the payment form.
	 *
	 * @return string
	 */
	public function get_javascript_url(){
		$transaction = $this->get_transaction_from_session();
		return $this->get_transaction_service()->buildJavaScriptUrl($transaction->getLinkedSpaceId(), $transaction->getId());
	}
	
	/**
	 * Returns the URL to PostFinance Checkout's JavaScript library that is necessary to display the payment form.
	 *
	 * @return string
	 */
	public function get_payment_page_url($space_id, $transaction_id){
	    return $this->get_transaction_service()->buildPaymentPageUrl($space_id, $transaction_id);
	}

	/**
	 * Returns the transaction with the given id.
	 *
	 * @param int $space_id
	 * @param int $transaction_id
	 * @return \PostFinanceCheckout\Sdk\Model\Transaction
	 */
	public function get_transaction($space_id, $transaction_id){
		return $this->get_transaction_service()->read($space_id, $transaction_id);
	}

	/**
	 * Returns the last failed charge attempt of the transaction.
	 *
	 * @param int $space_id
	 * @param int $transaction_id
	 * @return \PostFinanceCheckout\Sdk\Model\ChargeAttempt
	 */
	public function get_failed_charge_attempt($space_id, $transaction_id){
		$charge_attempt_service = $this->get_charge_attempt_service();
		$query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		$filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
		$filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->create_entity_filter('charge.transaction.id', $transaction_id),
				    $this->create_entity_filter('state', \PostFinanceCheckout\Sdk\Model\ChargeAttemptState::FAILED) 
				));
		$query->setFilter($filter);
		$query->setOrderBys(array(
			$this->create_entity_order_by('failedOn') 
		));
		$query->setNumberOfEntities(1);
		$result = $charge_attempt_service->search($space_id, $query);
		if ($result != null && !empty($result)) {
			return current($result);
		}
		else {
			return null;
		}
	}

	/**
	 * Updates the line items of the given transaction.
	 *
	 * @param int $space_id
	 * @param int $transaction_id
	 * @param \PostFinanceCheckout\Sdk\Model\LineItem[] $line_items
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionLineItemVersion
	 */
	public function update_line_items($space_id, $transaction_id, $line_items){
	    $update_request = new \PostFinanceCheckout\Sdk\Model\TransactionLineItemUpdateRequest();
		$update_request->setTransactionId($transaction_id);
		$update_request->setNewLineItems($line_items);
		return $this->get_transaction_service()->updateTransactionLineItems($space_id, $update_request);
	}

	/**
	 * Stores the transaction data in the database.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @param WC_Order $order
	 * @return WC_PostFinanceCheckout_Entity_Transaction_Info
	 */
	public function update_transaction_info(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, WC_Order $order){
	    $info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction($transaction->getLinkedSpaceId(), $transaction->getId());
		$info->set_transaction_id($transaction->getId());
		$info->set_authorization_amount($transaction->getAuthorizationAmount());
		if ($transaction->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::FAILED){
		    $info->set_order_id(null);
		}
		else{
		    $info->set_order_id($order->get_id());
		}
		$info->set_order_mapping_id($order->get_id());
		$info->set_state($transaction->getState());
		$info->set_space_id($transaction->getLinkedSpaceId());
		$info->set_space_view_id($transaction->getSpaceViewId());
		$info->set_language($transaction->getLanguage());
		$info->set_currency($transaction->getCurrency());
		$info->set_connector_id(
				$transaction->getPaymentConnectorConfiguration() != null ? $transaction->getPaymentConnectorConfiguration()->getConnector() : null);
		$info->set_payment_method_id(
				$transaction->getPaymentConnectorConfiguration() != null && $transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration() !=
						 null ? $transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getPaymentMethod() : null);
		$info->set_image($this->get_resource_path($this->get_payment_method_image($transaction, $order)));
		$info->set_image_base($this->get_resource_base($this->get_payment_method_image($transaction, $order)));
		$info->set_labels($this->get_transaction_labels($transaction));
		if ($transaction->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::FAILED ||
		    $transaction->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE) {
			$failed_charge_attempt = $this->get_failed_charge_attempt($transaction->getLinkedSpaceId(), $transaction->getId());
			if ($failed_charge_attempt != null && $failed_charge_attempt->getFailureReason() != null) {
			    $info->set_failure_reason($failed_charge_attempt->getFailureReason()->getDescription());
			}
			else if($transaction->getFailureReason() != null){
			    $info->set_failure_reason($transaction->getFailureReason()->getDescription());
			}
		}
		$info = apply_filters('wc_postfinancecheckout_update_transaction_info', $info, $transaction, $order);
		$info->save();
		return $info;
	}

	/**
	 * Returns an array of the transaction's labels.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @return string[]
	 */
	protected function get_transaction_labels(\PostFinanceCheckout\Sdk\Model\Transaction $transaction){
		$charge_attempt = $this->get_charge_attempt($transaction);
		if ($charge_attempt != null) {
			$labels = array();
			foreach ($charge_attempt->getLabels() as $label) {
				$labels[$label->getDescriptor()->getId()] = $label->getContentAsString();
			}
			return $labels;
		}
		else {
			return array();
		}
	}

	/**
	 * Returns the successful charge attempt of the transaction.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @return \PostFinanceCheckout\Sdk\Model\ChargeAttempt
	 */
	protected function get_charge_attempt(\PostFinanceCheckout\Sdk\Model\Transaction $transaction){
		$charge_attempt_service = $this->get_charge_attempt_service();
		$query = new \PostFinanceCheckout\Sdk\Model\EntityQuery();
		$filter = new \PostFinanceCheckout\Sdk\Model\EntityQueryFilter();
		$filter->setType(\PostFinanceCheckout\Sdk\Model\EntityQueryFilterType::_AND);
		$filter->setChildren(
				array(
					$this->create_entity_filter('charge.transaction.id', $transaction->getId()),
				    $this->create_entity_filter('state', \PostFinanceCheckout\Sdk\Model\ChargeAttemptState::SUCCESSFUL) 
				));
		$query->setFilter($filter);
		$query->setNumberOfEntities(1);
		$result = $charge_attempt_service->search($transaction->getLinkedSpaceId(), $query);
		if ($result != null && !empty($result)) {
			return current($result);
		}
		else {
			return null;
		}
	}

	/**
	 * Returns the payment method's image.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\Transaction $transaction
	 * @param WC_order $order
	 * @return string
	 */
	protected function get_payment_method_image(\PostFinanceCheckout\Sdk\Model\Transaction $transaction, WC_order $order){
		if ($transaction->getPaymentConnectorConfiguration() == null) {
			$method_instance = wc_get_payment_gateway_by_order($order);
			if ($method_instance != false && ($method_instance instanceof WC_PostFinanceCheckout_Gateway)) {
			    return WC_PostFinanceCheckout_Helper::instance()->get_resource_url($method_instance->get_payment_method_configuration()->get_image_base(), $method_instance->get_payment_method_configuration()->get_image());
			}
			return null;
		}
		if ($transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration() != null) {
			return $transaction->getPaymentConnectorConfiguration()->getPaymentMethodConfiguration()->getResolvedImageUrl();
		}
		return null;
	}

	/**
	 * Returns the payment methods that can be used with the current cart.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\PaymentMethodConfiguration[]
	 */
	public function get_possible_payment_methods(){
	    
	    $transaction = $this->get_transaction_from_session();
	    		
	    if (!isset(self::$possible_payment_method_cache[$transaction->getLinkedSpaceId().'-'.$transaction->getId()]) || self::$possible_payment_method_cache[$transaction->getLinkedSpaceId().'-'.$transaction->getId()] == null) {
			$payment_methods = $this->get_transaction_service()->fetchPossiblePaymentMethods($transaction->getLinkedSpaceId(), $transaction->getId());
    		$method_configuration_service = WC_PostFinanceCheckout_Service_Method_Configuration::instance();
			foreach ($payment_methods as $payment_method) {
				$method_configuration_service->update_data($payment_method);
			}
			
			self::$possible_payment_method_cache[$transaction->getLinkedSpaceId().'-'.$transaction->getId()] = $payment_methods;
		}
		return self::$possible_payment_method_cache[$transaction->getLinkedSpaceId().'-'.$transaction->getId()];
	}

	/**
	 * Update the transaction with the given order's data.
	 *
	 * @param int $transaction_id
	 * @param int $space_id
	 * @param WC_Order $order
	 * @return \PostFinanceCheckout\Sdk\Model\Transaction
	 */
	public function confirm_transaction($transaction_id, $space_id, WC_Order $order){
	    $last = new Exception('Unexpected Error');
		for ($i = 0; $i < 5; $i++) {
			try {
				$transaction = $this->get_transaction_service()->read($space_id, $transaction_id);
				if ($transaction->getState() != \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING) {
				    throw new Exception(__("The checkout expired, please try again", "woo-postfinancecheckout"));
				}
				$pending_transaction = new \PostFinanceCheckout\Sdk\Model\TransactionPending();
				$pending_transaction->setId($transaction->getId());
				$pending_transaction->setVersion($transaction->getVersion());
				$this->assemble_order_transaction_data($order, $pending_transaction);
				$pending_transaction = apply_filters('wc_postfinancecheckout_modify_confirm_transaction', $pending_transaction, $order);
				return $this->get_transaction_service()->confirm($space_id, $pending_transaction);
			}
			catch (\PostFinanceCheckout\Sdk\VersioningException $e) {
				$last = $e;
			}
		}
		throw $last;
	}

	/**
	 * Assemble the transaction data for the given order and invoice.
	 *
	 * @param WC_order $order
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionPending $transaction
	 */
	protected function assemble_order_transaction_data(WC_Order $order, \PostFinanceCheckout\Sdk\Model\AbstractTransactionPending $transaction){
		$transaction->setCurrency($order->get_currency());
		$transaction->setBillingAddress($this->get_order_billing_address($order));
		$transaction->setShippingAddress($this->get_order_shipping_address($order));
		$transaction->setCustomerEmailAddress($this->get_order_email_address($order));
		$transaction->setCustomerId($this->get_customer_id());
		$transaction->setLanguage(WC_PostFinanceCheckout_Helper::instance()->get_cleaned_locale());
		$transaction->setShippingMethod($this->fix_length($order->get_shipping_method(), 200));
		$transaction->setMerchantReference($order->get_id());
		$transaction->setInvoiceMerchantReference($this->fix_length($this->remove_non_ascii($order->get_order_number()), 100));
		$this->set_order_line_items($order, $transaction);
        $this->set_order_return_urls($order, $transaction);
	}
	
	protected function set_order_return_urls(WC_Order $order, \PostFinanceCheckout\Sdk\Model\AbstractTransactionPending $transaction){
	    $transaction->setSuccessUrl(
	        add_query_arg(
	            array(
	                'action' => 'success',
	                'order_key' => $order->get_order_key(),
	                'order_id' => $order->get_id(),
	                'wc-api' => 'postfinancecheckout_return',
	                'utm_nooverride' => '1'
	            ), home_url('/')));
	    
	    $transaction->setFailedUrl(
	        add_query_arg(
	            array(
	                'action' => 'failure',
	                'order_key' => $order->get_order_key(),
	                'order_id' => $order->get_id(),
	                'wc-api' => 'postfinancecheckout_return',
	                'utm_nooverride' => '1'
	            ), home_url('/')));
	}
	
	protected function set_order_line_items(WC_Order $order, \PostFinanceCheckout\Sdk\Model\AbstractTransactionPending $transaction){
	    $transaction->setLineItems(WC_PostFinanceCheckout_Service_Line_Item::instance()->get_items_from_order($order));
	}

	/**
	 * Returns the billing address of the given order.
	 *
	 * @param WC_order $order
	 * @return \PostFinanceCheckout\Sdk\Model\AddressCreate
	 */
	protected function get_order_billing_address(WC_order $order){
	    $address = new \PostFinanceCheckout\Sdk\Model\AddressCreate();
		$address->setCity($this->fix_length($order->get_billing_city(), 100));
		$address->setCountry($order->get_billing_country());
		$address->setFamilyName($this->fix_length($order->get_billing_last_name(), 100));
		$address->setGivenName($this->fix_length($order->get_billing_first_name(), 100));
		$address->setOrganizationName($this->fix_length($order->get_billing_company(), 100));
		$address->setPhoneNumber($order->get_billing_phone());
		$address->setPostalState($order->get_billing_state());
		$address->setPostCode($this->fix_length($order->get_billing_postcode(), 40));
		$address->setStreet($this->fix_length(trim($order->get_billing_address_1() . "\n" . $order->get_billing_address_2()), 300));
		$address->setEmailAddress($this->fix_length($this->get_order_email_address($order), 254));		
		return $address;
	}

	/**
	 * Returns the shipping address of the given order.
	 *
	 * @param WC_order $order
	 * @return \PostFinanceCheckout\Sdk\Model\AddressCreate
	 */
	protected function get_order_shipping_address(WC_order $order){
	    $address = new \PostFinanceCheckout\Sdk\Model\AddressCreate();
		$address->setCity($this->fix_length($order->get_shipping_city(), 100));
		$address->setCountry($order->get_shipping_country());
		$address->setFamilyName($this->fix_length($order->get_shipping_last_name(), 100));
		$address->setGivenName($this->fix_length($order->get_shipping_first_name(), 100));
		$address->setOrganizationName($this->fix_length($order->get_shipping_company(), 100));
		$address->setPostalState($order->get_shipping_state());
		$address->setPostCode($this->fix_length($order->get_shipping_postcode(), 40));
		$address->setStreet($this->fix_length(trim($order->get_shipping_address_1() . "\n" . $order->get_shipping_address_2()), 300));
		$address->setEmailAddress($this->fix_length($this->get_order_email_address($order), 254));
		
		return $address;
	}
	
	/**
	 * Returns the current scustomer's email address.
	 *
	 * @param WC_Order $order
	 * @return string
	 */
	protected function get_order_email_address(WC_Order $order){
		$email = $order->get_billing_email();
		if (!empty($email)) {
			return $email;
		}
		if (is_user_logged_in()) {
			$user = wp_get_current_user();
			return $user->get('user_email');
		}
		return null;
	}

	/**
	 * Returns the transaction for the given session. We work with sessions as the cart is also only stored in the session
	 *
	 * If no transaction exists, a new one is created.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\Transaction
	 */
	protected function get_transaction_from_session(){
	    if(is_wc_endpoint_url( 'order-pay' )){
	        //We have to use the order and not the cart for this endpoint
	        global $wp;
	        $order = WC_Order_Factory::get_order($wp->query_vars['order-pay'] );
	        if(!$order){
	            throw new Exception("Invalid order provided");
	        }
	        if (!isset(self::$transaction_cache[$order->get_id()]) || self::$transaction_cache[$order->get_id()] == null) {
    	        $existing_transaction = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id($order->get_id());
    	        if($existing_transaction->get_id() === null){
    	            wc_transaction_query("start");
    	            try{
    	                WC_PostFinanceCheckout_Helper::instance()->lock_by_transaction_id($existing_transaction->get_space_id(), $existing_transaction->get_transaction_id());
        	            $transaction = $this->create_transaction_by_order($order);
        	            wc_transaction_query("commit");
    	            }catch(Exception $e){
                        wc_transaction_query("rollback");
                        throw $e;
    	            }
    	        }
    	        elseif($existing_transaction->get_state() == \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING) {
    	            $transaction = $this->load_and_update_transaction_for_order($order);
    	        }
    	        else{
    	            throw new Exception('There is already a valid transaction associated with this order.');
    	        }
    	        self::$transaction_cache[$order->get_id()] = $transaction;
	        }
	        return self::$transaction_cache[$order->get_id()];
        }
	    
	    $current_cart_id = WC_PostFinanceCheckout_Helper::instance()->get_current_cart_id();
		if (!isset(self::$transaction_cache[$current_cart_id]) || self::$transaction_cache[$current_cart_id] == null) {
			$session_handler = WC()->session;
			$transaction_id = $session_handler->get('postfinancecheckout_transaction_id', null);
			if ($transaction_id === null) {
				$transaction = $this->create_transaction_from_session();
			}
			else {
				$transaction = $this->load_and_update_transaction_from_session();
			}
			
			self::$transaction_cache[$current_cart_id] = $transaction;
		}
		
		return self::$transaction_cache[$current_cart_id];
	}
	
	/**
	 * Creates a transaction for the given order.
	 *
	 * @param WC_Order $order
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionCreate
	 */
	protected function create_transaction_by_order(WC_Order $order){
	    $space_id = get_option(WooCommerce_PostFinanceCheckout::CK_SPACE_ID);
	    $create_transaction = new \PostFinanceCheckout\Sdk\Model\TransactionCreate();
	    $create_transaction->setCustomersPresence(\PostFinanceCheckout\Sdk\Model\CustomersPresence::VIRTUAL_PRESENT);
	    $space_view_id = get_option(WooCommerce_PostFinanceCheckout::CK_SPACE_VIEW_ID, null);
	    if(!empty($space_view_id)){
	        $create_transaction->setSpaceViewId($space_view_id);
	    }
	    $create_transaction->setAutoConfirmationEnabled(false);
	    if(isset($_COOKIE['wc_postfinancecheckout_device_id'])){
	        $create_transaction->setDeviceSessionIdentifier($_COOKIE['wc_postfinancecheckout_device_id']);
	    }
	    $this->assemble_order_transaction_data($order, $create_transaction);
	    $create_transaction = apply_filters('wc_postfinancecheckout_modify_order_create_transaction', $create_transaction, $order);
	    $transaction = $this->get_transaction_service()->create($space_id, $create_transaction);
	    $this->update_transaction_info($transaction, $order);
	    return $transaction;
	}

	/**
	 * Creates a transaction for the given quote.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionCreate
	 */
	protected function create_transaction_from_session(){
	       
	    $space_id = get_option(WooCommerce_PostFinanceCheckout::CK_SPACE_ID);
		$create_transaction = new \PostFinanceCheckout\Sdk\Model\TransactionCreate();
		$create_transaction->setCustomersPresence(\PostFinanceCheckout\Sdk\Model\CustomersPresence::VIRTUAL_PRESENT);
		$space_view_id = get_option(WooCommerce_PostFinanceCheckout::CK_SPACE_VIEW_ID, null);
		if(!empty($space_view_id)){
		    $create_transaction->setSpaceViewId($space_view_id);
		}
		$create_transaction->setAutoConfirmationEnabled(false);
		if(isset($_COOKIE['wc_postfinancecheckout_device_id'])){
			$create_transaction->setDeviceSessionIdentifier($_COOKIE['wc_postfinancecheckout_device_id']);
		}
		$this->assemble_session_transaction_data($create_transaction);
		$create_transaction = apply_filters('wc_postfinancecheckout_modify_session_create_transaction', $create_transaction);
		$transaction = $this->get_transaction_service()->create($space_id, $create_transaction);
		$this->store_transaction_ids_in_session($transaction);
		return $transaction;
	}
	
	protected function load_and_update_transaction_for_order(WC_Order $order){
	    $last = new \PostFinanceCheckout\Sdk\VersioningException();
	    for ($i = 0; $i < 5; $i++) {
	        try {
	            $existing_transaction = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id($order->get_id());
	            $space_id = $existing_transaction->get_space_id();
	            $transaction = $this->get_transaction($space_id, $existing_transaction->get_transaction_id());
	            if ($transaction->getState() != \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING) {
	                return $this->create_transaction_by_order($order);
	            }
	            $pending_transaction = new \PostFinanceCheckout\Sdk\Model\TransactionPending();
	            $pending_transaction->setId($transaction->getId());
	            $pending_transaction->setVersion($transaction->getVersion());
	            $this->assemble_order_transaction_data($order, $pending_transaction);
	            $pending_transaction = apply_filters('wc_postfinancecheckout_modify_order_pending_transaction', $pending_transaction, $order);
	            return $this->get_transaction_service()->update($space_id, $pending_transaction);
	        }
	        catch (\PostFinanceCheckout\Sdk\VersioningException $e) {
	            $last = $e;
	        }
	    }
	    throw $last;
	}

	/**
	 * Loads the transaction for the given quote and updates it if necessary.
	 *
	 * If the transaction is not in pending state, a new one is created.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\TransactionPending
	 */
	protected function load_and_update_transaction_from_session(){
	    $last = new \PostFinanceCheckout\Sdk\VersioningException();
		for ($i = 0; $i < 5; $i++) {
			try {
				$session_handler = WC()->session;
				$space_id = $session_handler->get('postfinancecheckout_space_id');
				$transaction_id = $session_handler->get('postfinancecheckout_transaction_id');
				$transaction = $this->get_transaction($space_id, $transaction_id);
				if ($transaction->getState() != \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING) {
					return $this->create_transaction_from_session();
				}
				$pending_transaction = new \PostFinanceCheckout\Sdk\Model\TransactionPending();
				$pending_transaction->setId($transaction->getId());
				$pending_transaction->setVersion($transaction->getVersion());
				$this->assemble_session_transaction_data($pending_transaction);
				$pending_transaction = apply_filters('wc_postfinancecheckout_modify_session_pending_transaction', $pending_transaction);
				return $this->get_transaction_service()->update($space_id, $pending_transaction);
			}
			catch (\PostFinanceCheckout\Sdk\VersioningException $e) {
				$last = $e;
			}
		}
		throw $last;
	}

	/**
	 * Assemble the transaction data for the given quote.
	 *
	 * @param \PostFinanceCheckout\Sdk\Model\TransactionPending $transaction
	 */
	protected function assemble_session_transaction_data(\PostFinanceCheckout\Sdk\Model\AbstractTransactionPending $transaction){
		$transaction->setCurrency(get_woocommerce_currency());
		$transaction->setBillingAddress($this->get_session_billing_address());
		$transaction->setShippingAddress($this->get_session_shipping_address());
		$transaction->setCustomerEmailAddress($this->get_session_email_address());
		$transaction->setCustomerId($this->get_customer_id());
		$transaction->setLanguage(WC_PostFinanceCheckout_Helper::instance()->get_cleaned_locale());
		$transaction->setShippingMethod($this->fix_length($this->get_session_shipping_method_name(), 200));
		$transaction->setAllowedPaymentMethodConfigurations(array());
		$this->set_session_line_items($transaction);
	}
	
	protected function set_session_line_items(\PostFinanceCheckout\Sdk\Model\AbstractTransactionPending $transaction){
	    $transaction->setLineItems(WC_PostFinanceCheckout_Service_Line_Item::instance()->get_items_from_session());
	}

	/**
	 * Returns the billing address of the current session.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\AddressCreate
	 */
	protected function get_session_billing_address(){
		$customer = WC()->customer;
		if ($customer == null) {
			return null;
		}
		
		$address = new \PostFinanceCheckout\Sdk\Model\AddressCreate();
		$address->setCity($this->fix_length($customer->get_billing_city(), 100));
		$address->setCountry($customer->get_billing_country());
		$address->setFamilyName($this->fix_length($customer->get_billing_last_name(), 100));
		$address->setGivenName($this->fix_length($customer->get_billing_first_name(), 100));
		$address->setOrganizationName($this->fix_length($customer->get_billing_company(), 100));
		$address->setPhoneNumber($customer->get_billing_phone());
		$address->setPostalState($customer->get_billing_state());
		$address->setPostCode($this->fix_length($customer->get_billing_postcode(), 40));
		$address->setStreet($this->fix_length(trim($customer->get_billing_address_1() . "\n" . $customer->get_billing_address_2()), 300));
		$address->setEmailAddress($this->fix_length($this->get_session_email_address(), 254));
		
		return $address;
	}

	/**
	 * Returns the shipping address of the current session.
	 *
	 * @return \PostFinanceCheckout\Sdk\Model\AddressCreate
	 */
	protected function get_session_shipping_address(){
		$customer = WC()->customer;
		if ($customer == null) {
			return null;
		}
		
		$address = new \PostFinanceCheckout\Sdk\Model\AddressCreate();
		$address->setCity($this->fix_length($customer->get_shipping_city(), 100));
		$address->setCountry($customer->get_shipping_country());
		$address->setFamilyName($this->fix_length($customer->get_shipping_last_name(), 100));
		$address->setGivenName($this->fix_length($customer->get_shipping_first_name(), 100));
		$address->setOrganizationName($this->fix_length($customer->get_shipping_company(), 100));
		$address->setPostalState($customer->get_shipping_state());
		$address->setPostCode($this->fix_length($customer->get_shipping_postcode(), 40));
		$address->setStreet($this->fix_length(trim($customer->get_shipping_address_1() . "\n" . $customer->get_shipping_address_2()), 300));
		$address->setEmailAddress($this->fix_length($this->get_session_email_address(), 254));
		
		return $address;
	}

	/**
	 * Returns the current customer's email address.
	 *
	 * @return string
	 */
	protected function get_session_email_address(){
		
		//if we are in update_order_review, the entered email is in the post_data string,
		//as WooCommerce does not update the email on the customer
		$post_data = array();
		if (isset($_POST['post_data'])) {
			parse_str($_POST['post_data'], $post_data);
		}
		if (!empty($post_data['billing_email'])) {
			return $post_data['billing_email'];
		}
		
		$customer = WC()->customer;
		if ($customer != null) {
			$email = $customer->get_billing_email();
			if (!empty($email)) {
				return $email;
			}
		}
		if (is_user_logged_in()) {
			$user = wp_get_current_user();
			return $user->get('user_email');
		}
		return null;
	}

	/**
	 * Returns the current customer id or null if guest
	 */
	protected function get_customer_id(){
		if (!is_user_logged_in()) {
			return null;
		}
		$current = get_current_user_id();
		if ($current == 0) {
			return null;
		}
		
		return $current;
	}

	protected function get_session_shipping_method_name(){
		$names = array();
		
		$packages = WC()->shipping->get_packages();
		
		foreach ($packages as $i => $package) {
			$chosen_method = isset(WC()->session->chosen_shipping_methods[$i]) ? WC()->session->chosen_shipping_methods[$i] : '';
			if (empty($chosen_method)) {
				$continue;
			}
			foreach ($package['rates'] as $rate) {
				if ($rate->id == $chosen_method) {
					$names[] = $rate->get_label();
					break;
				}
			}
		}
		return implode(", ", $names);
	}

	protected function store_transaction_ids_in_session(\PostFinanceCheckout\Sdk\Model\Transaction $transaction){
		$session_handler = WC()->session;
		$session_handler->set('postfinancecheckout_transaction_id', $transaction->getId());
		$session_handler->set('postfinancecheckout_space_id', $transaction->getLinkedSpaceId());
	}
}