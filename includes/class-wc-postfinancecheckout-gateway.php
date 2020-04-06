<?php

if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * This class implements the PostFinance Checkout gateways
 */
class WC_PostFinanceCheckout_Gateway extends WC_Payment_Gateway {
	private $payment_method_configuration_id;
	
	//Contains a users saved tokens for this gateway.
	protected $tokens = array();
	//We prefix out private variables as other plugins do strange things
	private $pfc_payment_method_configuration_id;
	private $pfc_payment_method_configuration = null;
	private $pfc_translated_title = null;
	private $pfc_translated_description = null;
	private $pfc_show_description = 'yes';
	private $pfc_show_icon = 'yes';
	private $pfc_image = null;
	

	public function __construct(WC_PostFinanceCheckout_Entity_Method_Configuration $method){
		$this->pfc_payment_method_configuration_id = $method->get_id();
		$this->id = 'postfinancecheckout_' . $method->get_id();
		$this->has_fields = false;
		$this->method_title = $method->get_configuration_name();
		$this->method_description = WC_PostFinanceCheckout_Helper::instance()->translate($method->get_description());
		$this->pfc_image = $method->get_image();
		$this->pfc_image_base = $method->get_image_base();
		$this->icon = WC_PostFinanceCheckout_Helper::instance()->get_resource_url($this->pfc_image, $this->pfc_image_base);
		
		//We set the title and description here, as some plugin access the variables directly.
		$this->title = $method->get_configuration_name();
		$this->description = '';
		
		$this->pfc_translated_title = $method->get_title();
		$this->pfc_translated_description = $method->get_description();
		
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		
		// Define user set variables.
		$this->enabled = $this->get_option('enabled');
		$this->pfc_show_description = $this->get_option('show_description');
		$this->pfc_show_icon = $this->get_option('show_icon');
		
		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(
			$this,
			'process_admin_options' 
		));
		
	
		$this->supports = array(
		    'products',
		    'refunds'
		);
	}

	/**
	 * Returns the payment method configuration.
	 *
	 * @return WC_PostFinanceCheckout_Entity_Method_Configuration
	 */
	public function get_payment_method_configuration(){
		if (is_null($this->pfc_payment_method_configuration)) {
		    $this->pfc_payment_method_configuration = WC_PostFinanceCheckout_Entity_Method_Configuration::load_by_id(
					$this->pfc_payment_method_configuration_id);
		}
		return $this->pfc_payment_method_configuration;
	}

	/**
	 * Return the gateway's title fontend.
	 *
	 * @return string
	 */
	public function get_title(){
		$title = $this->title;
		$translated = WC_PostFinanceCheckout_Helper::instance()->translate($this->pfc_translated_title);
		if (!is_null($translated)) {
			$title = $translated;
		}
		return apply_filters('woocommerce_gateway_title', $title, $this->id);
	}

	/**
	 * Return the gateway's description frontend.
	 *
	 * @return string
	 */
	public function get_description(){
		$description = '';
		if ($this->pfc_show_description == 'yes') {
		    $translated = WC_PostFinanceCheckout_Helper::instance()->translate($this->pfc_translated_description);
			if (!is_null($translated)) {
				$description = $translated;
			}
		}
		return apply_filters('woocommerce_gateway_description', $description, $this->id);
	}

	/**
	 * Return the gateway's icon.
	 * @return string
	 */
	public function get_icon(){
		$icon = '';
		if ($this->pfc_show_icon == 'yes') {
			$space_id = $this->get_payment_method_configuration()->get_space_id();
			$space_view_id = get_option(WooCommerce_PostFinanceCheckout::CK_SPACE_VIEW_ID);
			$language = WC_PostFinanceCheckout_Helper::instance()->get_cleaned_locale();
			
			$url = WC_PostFinanceCheckout_Helper::instance()->get_resource_url($this->pfc_image_base, $this->pfc_image, $language, $space_id, $space_view_id);
			$icon = '<img src="' . WC_HTTPS::force_https_url($url) . '" alt="' . esc_attr($this->get_title()) . '" width="35px" />';
		}
		
		return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields(){
		$this->form_fields = array(
			'enabled' => array(
				'title' => __('Enable/Disable', 'woocommerce'),
				'type' => 'checkbox',
				'label' => sprintf(__('Enable %s', 'woo-postfinancecheckout'), $this->method_title),
				'default' => 'yes' 
			),
			'title_value' => array(
				'title' => __('Title', 'woocommerce'),
				'type' => 'info',
				'value' => $this->get_title(),
				'description' => __('This controls the title which the user sees during checkout.', 'woo-postfinancecheckout') 
			),
			'description_value' => array(
				'title' => __('Description', 'woocommerce'),
				'type' => 'info',
				'value' => !empty($this->get_description()) ? $this->get_description() : __('[not set]', 'woo-postfinancecheckout'),
				'description' => __('Payment method description that the customer will see on your checkout.', 'woo-postfinancecheckout') 
			),
			'show_description' => array(
				'title' => __('Show description', 'woo-postfinancecheckout'),
				'type' => 'checkbox',
				'label' => __('Yes', 'woo-postfinancecheckout'),
				'default' => 'yes',
				'description' => __("Show the payment method's description on the checkout page.", 'woo-postfinancecheckout'),
				'desc_tip' => true 
			),
			'show_icon' => array(
				'title' => __('Show method image', 'woo-postfinancecheckout'),
				'type' => 'checkbox',
				'label' => __('Yes', 'woo-postfinancecheckout'),
				'default' => 'yes',
				'description' => __("Show the payment method's image on the checkout page.", 'woo-postfinancecheckout'),
				'desc_tip' => true 
			) 
		);
	}

	/**
	 * Generate info HTML.
	 *
	 * @param  mixed $key
	 * @param  mixed $data
	 * @return string
	 */
	public function generate_info_html($key, $data){
		$field_key = $this->get_field_key($key);
		$defaults = array(
			'title' => '',
			'class' => '',
			'css' => '',
			'placeholder' => '',
			'desc_tip' => true,
			'description' => '',
			'custom_attributes' => array() 
		);
		
		$data = wp_parse_args($data, $defaults);
		
		ob_start();
		?>
<tr valign="top">
	<th scope="row" class="titledesc">
							<?php echo $this->get_tooltip_html( $data ); ?>
							<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
	</th>
	<td class="forminp">
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php echo wp_kses_post( $data['title'] ); ?></span>
			</legend>
			<div class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php echo $this->get_custom_attribute_html( $data ); ?> >
								<?php echo esc_attr($data['value']); ?>
						</div>
		</fieldset>
	</td>
</tr>
<?php
		
		return ob_get_clean();
	}

	/**
	 * Validate Info Field.
	 *
	 * @param  string $key Field key
	 * @param  string|null $value Posted Value
	 * @return void
	 */
	public function validate_info_field($key, $value){
		return;
	}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available(){
		$is_available = parent::is_available();
		
		if (!$is_available) {
			return false;
		}
		
		//It's not possible to support the rounding on subtotal level and still get valid tax rates and amounts.
		//Therefore the payment methods are disabled, if this option is active
        if ( wc_tax_enabled() && ('yes' === get_option( 'woocommerce_tax_round_at_subtotal' ))){
            if('yes' === get_option( WooCommerce_PostFinanceCheckout::CK_ENFORCE_CONSISTENCY )) {
                $errorMessage = __("'WooCommerce > Settings > PostFinanceCheckout > Enforce Consistency' and 'WooCommerce > Settings > Tax > Rounding' are both enabled. Please disable at least one of them.", 'woo-postfinancecheckout');
                WooCommerce_PostFinanceCheckout::instance()->log($errorMessage, WC_Log_Levels::ERROR);
                return false;
            }
        }
		
		//It is possbile this function is called in the wordpress admin section. There is not a cart, so all active methods are available.
		//If it is not a checkout page the method is availalbe. Some plugins check this, on non checkout pages, without a cart available
		//The active  gateways are  available during order total caluclation, as other plugins could need them.
		if(apply_filters('wc_postfinancecheckout_is_method_available', is_admin() || !is_checkout() || (isset($GLOBALS['_wc_postfinancecheckout_calculating']) && $GLOBALS['_wc_postfinancecheckout_calculating']), $this)) {
		    return $this->get_payment_method_configuration()->get_state() ==  WC_PostFinanceCheckout_Entity_Method_Configuration::STATE_ACTIVE;
		}
		
		if(apply_filters('wc_postfinancecheckout_is_order_pay_endpoint', is_checkout_pay_page())){
		    //We have to use the order and not the cart for this endpoint
		    global $wp;
		    $order = WC_Order_Factory::get_order($wp->query_vars['order-pay'] );
		    if(!$order){
		        return false;
		    }
		    try {
		        $possible_methods = WC_PostFinanceCheckout_Service_Transaction::instance()->get_possible_payment_methods_for_order($order);
		    }
		    catch(WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount $e){
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage().' Order Id: '.$order->get_id(), WC_Log_Levels::ERROR);
		        return false;
		    }
		    catch(Exception $e){
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::DEBUG);
		        return false;
		    }
		}
		else{
		    try {
		        $possible_methods = WC_PostFinanceCheckout_Service_Transaction::instance()->get_possible_payment_methods_for_cart();
		    }
		    catch(WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount $e){
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::ERROR);
		        return false;
		    }
		    catch(Exception $e){
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::DEBUG);
		        return false;
		    }
		}
		
		$possible = false;
		foreach ($possible_methods as $possible_method) {
		    if ($possible_method == $this->get_payment_method_configuration()->get_configuration_id()) {
		        $possible = true;
		        break;
		    }
		}
		if (!$possible) {
		    return false;
		}
		
		return true;
	}

	/**
	 * Check if the gateway has fields on the checkout.
	 *
	 * @return bool
	 */
	public function has_fields(){
		return true;
	}

	public function payment_fields(){
	    
	    parent::payment_fields();
	    $transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
	    try{
	        if(apply_filters('wc_postfinancecheckout_is_order_pay_endpoint', is_checkout_pay_page())){
	            global $wp;
	            $order = WC_Order_Factory::get_order($wp->query_vars['order-pay'] );
	            if(!$order){
	                return false;
	            }
	            $transaction = $transaction_service->get_transaction_from_order($order);
	        }
	        else{
	            $transaction = $transaction_service->get_transaction_from_session();
	        }
            if (!wp_script_is( 'postfinancecheckout-remote-checkout-js', 'enqueued' )) {
                $ajax_url = '';
                if(get_option(WooCommerce_PostFinanceCheckout::CK_INTEGRATION) == WC_PostFinanceCheckout_Integration::LIGHTBOX){
                    $ajax_url = $transaction_service->get_lightbox_url_for_transaction($transaction);
                } else {
                    $ajax_url = $transaction_service->get_javascript_url_for_transaction($transaction);
                }
    	        wp_enqueue_script('postfinancecheckout-remote-checkout-js', $ajax_url, array(
    	            'jquery'
    	        ), null, true);
    	        wp_enqueue_script('postfinancecheckout-checkout-js', WooCommerce_PostFinanceCheckout::instance()->plugin_url() . '/assets/js/frontend/checkout.js',
    	            array(
    	                'jquery',
    	                'postfinancecheckout-remote-checkout-js'
    	            ), null, true);
    	        $localize = array(
    	            'i18n_not_complete' => __('Please fill out all required fields.', 'woo-postfinancecheckout'),
                    'integration' => get_option(WooCommerce_PostFinanceCheckout::CK_INTEGRATION),
    	        );
    	        wp_localize_script('postfinancecheckout-checkout-js', 'postfinancecheckout_js_params', $localize);
                wp_add_inline_script('postfinancecheckout-checkout-js', 'window.onload = function () {  wc_postfinancecheckout_checkout.init(); };');
    	    
            }
            $transaction_nonce = hash_hmac('sha256', $transaction->getLinkedSpaceId().'-'.$transaction->getId(), NONCE_KEY);
            
            ?>
		
            <div id="payment-form-<?php echo $this->id?>">
            	<input type="hidden" id="postfinancecheckout-iframe-possible-<?php echo $this->id?>" name="postfinancecheckout-iframe-possible-<?php echo $this->id?>" value="false" />
            </div>
            <input type="hidden" id="postfinancecheckout-space-id-<?php echo $this->id?>" name="postfinancecheckout-space-id-<?php echo $this->id?>" value="<?php echo $transaction->getLinkedSpaceId(); ?>"  />
        	<input type="hidden" id="postfinancecheckout-transaction-id-<?php echo $this->id?>" name="postfinancecheckout-transaction-id-<?php echo $this->id?>" value="<?php echo $transaction->getId(); ?>"  />
        	<input type="hidden" id="postfinancecheckout-transaction-nonce-<?php echo $this->id?>" name="postfinancecheckout-transaction-nonce-<?php echo $this->id?>" value="<?php echo $transaction_nonce; ?>" />
            <div id="postfinancecheckout-method-configuration-<?php echo $this->id?>"
            	class="postfinancecheckout-method-configuration" style="display: none;"
            	data-method-id="<?php echo $this->id; ?>"
            	data-configuration-id="<?php echo $this->get_payment_method_configuration()->get_configuration_id(); ?>"
            	data-container-id="payment-form-<?php echo $this->id?>" data-description-available="<?php var_export(!empty($this->get_description()));?>"></div>
            <?php
           
	    }
	    catch(Exception $e){
	        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::DEBUG);
	    }
		
	}

	/**
	 * Validate frontend fields.
	 * @return bool
	 */
	public function validate_fields(){
		return true;
	}

	/**
	 * Process Payment.
	 *
	 * @param int $order_id
	 * @return array
	 */
	public function process_payment($order_id){
	    
	    $space_id = $_POST['postfinancecheckout-space-id-'.$this->id];
	    $transaction_id = $_POST['postfinancecheckout-transaction-id-'.$this->id];
	    $transaction_nonce = $_POST['postfinancecheckout-transaction-nonce-'.$this->id];
	    
	    $is_order_pay_endpoint = apply_filters('wc_postfinancecheckout_is_order_pay_endpoint', is_checkout_pay_page());
	    
	    if(hash_hmac('sha256', $space_id.'-'.$transaction_id, NONCE_KEY) != $transaction_nonce){
	        WC()->session->set( 'postfinancecheckout_failure_message', __('The checkout timed out, please try again.', 'woo-postfinancecheckout') );
	        WC()->session->set('reload_checkout', true);
	        return array(
	            'result' => 'failure'
	        );
	    }
	    
	    $existing = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id($order_id);
	    if($existing->get_id()){
	        if($existing->get_space_id() != $space_id && $existing->get_transaction_id() != $transaction_id){
	            WC()->session->set( 'postfinancecheckout_failure_message', __('There was an issue, while processing your order. Please try again or use another payment method.', 'woo-postfinancecheckout') );
	            WC()->session->set('reload_checkout', true);
	            return array(
	                'result' => 'failure'
	            );
	        }
	    }
	    
	    $order = wc_get_order($order_id);
	    $no_iframe = isset($_POST['postfinancecheckout-iframe-possible-'.$this->id]) && $_POST['postfinancecheckout-iframe-possible-'.$this->id] == 'false';
	    
		try {
			$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
			$transaction = $transaction_service->get_transaction($space_id, $transaction_id);
			
			$order->add_meta_data('_postfinancecheckout_pay_for_order', $is_order_pay_endpoint, true);
			$order->add_meta_data('_postfinancecheckout_gateway_id', $this->id, true);
			$order->delete_meta_data('_postfinancecheckout_confirmed');
			$order->save();
			
			if($transaction->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING){
			    $transaction = $transaction_service->confirm_transaction($transaction_id, $space_id, $order, $this->get_payment_method_configuration()->get_configuration_id());
			    $transaction_service->update_transaction_info($transaction, $order);
			}
			
			WC()->session->set( 'order_awaiting_payment', false );
			WC_PostFinanceCheckout_Helper::instance()->destroy_current_cart_id();
			WC()->session->set('postfinancecheckout_space_id', null);
			WC()->session->set('postfinancecheckout_transaction_id', null);
			
			
			$result =array(
			    'result' => 'success',
			    'postfinancecheckout' => 'true'
			);
			if($no_iframe){
			    $result = array(
			        'result' => 'success',
			        'redirect' => $transaction_service->get_payment_page_url($transaction->getLinkedSpaceId(), $transaction->getId())
			    );
			    return $result;
	        }	
			
	        if(apply_filters('wc_postfinancecheckout_gateway_result_send_json', $is_order_pay_endpoint, $order_id)){
			    wp_send_json( $result );
			    exit;
			}
			else{
    			return $result;
			}
		}
		catch (Exception $e) {
			$message = $e->getMessage();
			$cleaned = preg_replace("/^\[[A-Fa-f\d\-]+\] /", "", $message);
			WC()->session->set( 'postfinancecheckout_failure_message', $cleaned);
			$order->update_status( 'failed' );
			$result =array(
			    'result' => 'failure',
			    'reload' => 'true'
			);
			if(apply_filters('wc_postfinancecheckout_gateway_result_send_json', $is_order_pay_endpoint, $order_id)){
			    wp_send_json( $result );
			    exit;
			}
			WC()->session->set('reload_checkout', true);
			return array(
				'result' => 'failure'
			);
		}
	}

	/**
	 * Process refund.
	 *
	 * If the gateway declares 'refunds' support, this will allow it to refund.
	 * a passed in amount.
	 *
	 * @param  int $order_id
	 * @param  float $amount
	 * @param  string $reason
	 * @return boolean True or false based on success, or a WP_Error object.
	 */
	public function process_refund($order_id, $amount = null, $reason = ''){
		if (!isset($GLOBALS['postfinancecheckout_refund_id'])) {
			return new WP_Error('postfinancecheckout_error', __('There was a problem creating the refund.', 'woo-postfinancecheckout'));
		}
		/**
		 * @var WC_Order_Refund $refund
		 */
		$refund = WC_Order_Factory::get_order($GLOBALS['postfinancecheckout_refund_id']);
		$order = WC_Order_Factory::get_order($order_id);
		
		try {
		    WC_PostFinanceCheckout_Admin_Refund::execute_refund($order, $refund);
		}
		catch (Exception $e) {
			return new WP_Error('postfinancecheckout_error', $e->getMessage());
		}
		
		$refund_job_id =  $refund->get_meta('_postfinancecheckout_refund_job_id', true);
		
		$wait = 0;
		while($wait < 5){
		    $refund_job = WC_PostFinanceCheckout_Entity_Refund_Job::load_by_id($refund_job_id);
		    if($refund_job->get_state() == WC_PostFinanceCheckout_Entity_Refund_Job::STATE_FAILURE){
		        return new WP_Error('postfinancecheckout_error', $refund_job->get_failure_reason());
		    }
		    elseif ($refund_job->get_state() == WC_PostFinanceCheckout_Entity_Refund_Job::STATE_SUCCESS){
		        return true;
		    }
		    $wait++;
		    sleep(1);
		}		
		return true;
	}
}