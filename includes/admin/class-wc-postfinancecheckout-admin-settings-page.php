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
 * Adds PostFinanceCheckout settings to WooCommerce Settings Tabs
 */
class WC_PostFinanceCheckout_Admin_Settings_Page extends WC_Settings_Page {

	/**
	 * Adds Hooks to output and save settings
	 */
	public function __construct(){
		$this->id = 'postfinancecheckout';
		$this->label = 'PostFinance Checkout';
		
		add_filter('woocommerce_settings_tabs_array', array(
			$this,
			'add_settings_page' 
		), 20);
		add_action('woocommerce_settings_' . $this->id, array(
			$this,
			'settings_tab' 
		));
		add_action('woocommerce_settings_save_' . $this->id, array(
			$this,
			'save' 
		));
		
		add_action('woocommerce_update_options_' . $this->id, array(
			$this,
			'update_settings' 
		));
		
		add_action('woocommerce_admin_field_postfinancecheckout_links', array(
		    $this,
	  		'output_links'
		));
	}

	public function add_settings_tab($settings_tabs){
		$settings_tabs[$this->id] = 'PostFinance Checkout';
		return $settings_tabs;
	}

	public function settings_tab(){
		woocommerce_admin_fields($this->get_settings());
	}
	
	public function save(){
		$settings = $this->get_settings();
		WC_Admin_Settings::save_fields( $settings );
		
	}

	public function update_settings(){
	    WC_PostFinanceCheckout_Helper::instance()->reset_api_client();
	    $user_id = get_option(WooCommerce_PostFinanceCheckout::CK_APP_USER_ID);
	    $user_key = get_option(WooCommerce_PostFinanceCheckout::CK_APP_USER_KEY);
		if (!empty($user_id) && !empty($user_key)) {
		    $error = '';
		    try{
		        WC_PostFinanceCheckout_Service_Method_Configuration::instance()->synchronize();
		    }
		    catch (Exception $e) {
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::ERROR);
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getTraceAsString(), WC_Log_Levels::DEBUG);
		        $error .= ' '. 
		            __('Could not update payment method configuration.', 'woo-postfinancecheckout');
		    }
		    try{
		        WC_PostFinanceCheckout_Service_Webhook::instance()->install();
		    }
		    catch (Exception $e) {
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::ERROR);
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getTraceAsString(), WC_Log_Levels::DEBUG);
		        $error .= ' '.
		            __('Could not install webhooks, please check if the feature is active in your space.', 'woo-postfinancecheckout');
		    }
		    try{
		        WC_PostFinanceCheckout_Service_Manual_Task::instance()->update();
		    }
		    catch (Exception $e) {
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::ERROR);
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getTraceAsString(), WC_Log_Levels::DEBUG);
		        $error .= ' '.
		            __('Could not update the manual task list.', 'woo-postfinancecheckout');
		    }
		    try {
		        do_action('wc_postfinancecheckout_settings_changed');
		    }
		    catch (Exception $e) {
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::ERROR);
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getTraceAsString(), WC_Log_Levels::DEBUG);
		        $error .= ' '. $e->getMessage();
		    }
		    if(!empty($error)){
		        $error =
		            __('Please check your credentials and grant the application user the necessary rights (Account Admin) for your space.', 'woo-postfinancecheckout') .' '.$error;
		        WC_Admin_Settings::add_error($error);
		        
		    }			
			$this->delete_provider_transients();
		}
		
	}
	

	private function delete_provider_transients(){
		$transients = array(
			'wc_postfinancecheckout_currencies',
			'wc_postfinancecheckout_label_description_groups',
			'wc_postfinancecheckout_label_descriptions',
			'wc_postfinancecheckout_languages',
			'wc_postfinancecheckout_payment_connectors',
			'wc_postfinancecheckout_payment_methods' 
		);
		foreach ($transients as $transient) {
			delete_transient($transient);
		}
	}
	
	
	public function output_links($value){
	    foreach($value['links'] as $url => $text){
	        echo '<a href="'.$url.'" class="page-title-action">'.esc_html($text).'</a>';	        
	    }
	}

	/**
	 * Get settings array
	 *
	 * @return array
	 */
	public function get_settings(){
	    
		$settings = array(
		    array(
		        'links' => array(
		            'https://plugin-documentation.postfinance-checkout.ch/pfpayments/woocommerce/1.1.19/docs/en/documentation.html' => __('Documentation', 'woo-postfinancecheckout'),
		            'https://www.postfinance-checkout.ch/user/signup' => __('Sign Up', 'woo-postfinancecheckout')
		        ),
		        'type' => 'postfinancecheckout_links',
		    ),
		    
			array(
				'title' => __('General Settings', 'woo-postfinancecheckout'),
			    'desc' => 
			        __('Enter your application user credentials and space id, if you don\'t have an account already sign up above.',
			            'woo-postfinancecheckout'),
				'type' => 'title',
				'id' => 'general_options' 
			),
		    
		    array(
		        'title' => __('Space Id', 'woo-postfinancecheckout'),
		        'id' => WooCommerce_PostFinanceCheckout::CK_SPACE_ID,
		        'type' => 'text',
		        'css' => 'min-width:300px;',
		        'desc' => __('(required)', 'woo-postfinancecheckout')
		    ),
			
			array(
				'title' => __('User Id', 'woo-postfinancecheckout'),
				'desc_tip' => __('The user needs to have full permissions in the space this shop is linked to.', 'woo-postfinancecheckout'),
			    'id' => WooCommerce_PostFinanceCheckout::CK_APP_USER_ID,
				'type' => 'text',
				'css' => 'min-width:300px;',
				'desc' => __('(required)', 'woo-postfinancecheckout') 
			),
			
			array(
				'title' => __('Authentication Key', 'woo-postfinancecheckout'),
			    'id' => WooCommerce_PostFinanceCheckout::CK_APP_USER_KEY,
				'type' => 'password',
				'css' => 'min-width:300px;',
				'desc' => __('(required)', 'woo-postfinancecheckout') 
			),
						
			array(
				'type' => 'sectionend',
				'id' => 'general_options' 
			),
			
			array(
				'title' => __('Email Options', 'woo-postfinancecheckout'),
				'type' => 'title',
				'id' => 'email_options' 
			),
			
			array(
				'title' => __('Send Order Email', 'woo-postfinancecheckout'),
				'desc' => __("Send the Woocommerce's order email.", 'woo-postfinancecheckout'),
			    'id' => WooCommerce_PostFinanceCheckout::CK_SHOP_EMAIL,
				'type' => 'checkbox',
				'default' => 'yes',
				'css' => 'min-width:300px;' 
			),
			
			array(
				'type' => 'sectionend',
				'id' => 'email_options' 
			),
			
			array(
				'title' => __('Document Options', 'woo-postfinancecheckout'),
				'type' => 'title',
				'id' => 'document_options' 
			),
			
			array(
				'title' => __('Invoice Download', 'woo-postfinancecheckout'),
				'desc' => __("Allow customer's to download the invoice.", 'woo-postfinancecheckout'),
			    'id' => WooCommerce_PostFinanceCheckout::CK_CUSTOMER_INVOICE,
				'type' => 'checkbox',
				'default' => 'yes',
				'css' => 'min-width:300px;' 
			),
			array(
				'title' => __('Packing Slip Download', 'woo-postfinancecheckout'),
				'desc' => __("Allow customer's to download the packing slip.", 'woo-postfinancecheckout'),
			    'id' => WooCommerce_PostFinanceCheckout::CK_CUSTOMER_PACKING,
				'type' => 'checkbox',
				'default' => 'yes',
				'css' => 'min-width:300px;' 
			),
			
			array(
				'type' => 'sectionend',
				'id' => 'document_options' 
			) ,
		    
		    array(
		        'title' => __('Space View Options', 'woo-postfinancecheckout'),
		        'type' => 'title',
		        'id' => 'space_view_options'
		    ),
		   
		    array(
		        'title' => __('Space View Id', 'woo-postfinancecheckout'),
		        'desc_tip' => __('The Space View Id allows to control the styling of the payment form and the payment page within the space.', 'woo-postfinancecheckout'),
		        'id' => WooCommerce_PostFinanceCheckout::CK_SPACE_VIEW_ID,
		        'type' => 'text',
		        'css' => 'min-width:300px;'
		    ),
		    
		    array(
		        'type' => 'sectionend',
		        'id' => 'space_view_options'
		    ) 
		
		);
		
		return apply_filters('wc_postfinancecheckout_settings', $settings);
	}
}
