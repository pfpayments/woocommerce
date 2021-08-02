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
		if (!(empty($user_id) || empty($user_key))) {
            $errorMessage = '';
		    try{
		        WC_PostFinanceCheckout_Service_Method_Configuration::instance()->synchronize();
		    }
		    catch (\Exception $e) {
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::ERROR);
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getTraceAsString(), WC_Log_Levels::DEBUG);
                $errorMessage = __('Could not update payment method configuration.', 'woo-postfinancecheckout');
                WC_Admin_Settings::add_error($errorMessage);
		    }
		    try{
		        WC_PostFinanceCheckout_Service_Webhook::instance()->install();
		    }
		    catch (\Exception $e) {
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::ERROR);
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getTraceAsString(), WC_Log_Levels::DEBUG);
                $errorMessage = __('Could not install webhooks, please check if the feature is active in your space.', 'woo-postfinancecheckout');
                WC_Admin_Settings::add_error($errorMessage);
		    }
		    try{
		        WC_PostFinanceCheckout_Service_Manual_Task::instance()->update();
		    }
		    catch (\Exception $e) {
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::ERROR);
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getTraceAsString(), WC_Log_Levels::DEBUG);
                $errorMessage = __('Could not update the manual task list.', 'woo-postfinancecheckout');
                WC_Admin_Settings::add_error($errorMessage);
		    }
		    try {
		        do_action('wc_postfinancecheckout_settings_changed');
		    }
		    catch (\Exception $e) {
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getMessage(), WC_Log_Levels::ERROR);
		        WooCommerce_PostFinanceCheckout::instance()->log($e->getTraceAsString(), WC_Log_Levels::DEBUG);
                $errorMessage = $e->getMessage();
                WC_Admin_Settings::add_error($errorMessage);
		    }

            if ( wc_tax_enabled() && ('yes' === get_option( 'woocommerce_tax_round_at_subtotal' ))){
                if('yes' === get_option( WooCommerce_PostFinanceCheckout::CK_ENFORCE_CONSISTENCY )) {
                    $errorMessage = __("'WooCommerce > Settings > PostFinanceCheckout > Enforce Consistency' and 'WooCommerce > Settings > Tax > Rounding' are both enabled. Please disable at least one of them.", 'woo-postfinancecheckout');
                    WC_Admin_Settings::add_error($errorMessage);
                    WooCommerce_PostFinanceCheckout::instance()->log($errorMessage, WC_Log_Levels::ERROR);
                }
            }
		    
		    
		    if(!empty($errorMessage)){
                $errorMessage = __('Please check your credentials and grant the application user the necessary rights (Account Admin) for your space.', 'woo-postfinancecheckout');
		        WC_Admin_Settings::add_error($errorMessage);
		    }			
		    WC_PostFinanceCheckout_Helper::instance()->delete_provider_transients();
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
		            'https://plugin-documentation.postfinance-checkout.ch/pfpayments/woocommerce/1.5.8/docs/en/documentation.html' => __('Documentation', 'woo-postfinancecheckout'),
		            'https://checkout.postfinance.ch/user/signup' => __('Sign Up', 'woo-postfinancecheckout')
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
				'desc' => __("Send the order email of WooCommerce.", 'woo-postfinancecheckout'),
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
				'desc' => __("Allow customers to download the invoice.", 'woo-postfinancecheckout'),
			    'id' => WooCommerce_PostFinanceCheckout::CK_CUSTOMER_INVOICE,
				'type' => 'checkbox',
				'default' => 'yes',
				'css' => 'min-width:300px;' 
			),
			array(
				'title' => __('Packing Slip Download', 'woo-postfinancecheckout'),
				'desc' => __("Allow customers to download the packing slip.", 'woo-postfinancecheckout'),
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
		    ),

            array(
                'title' => __('Integration Options', 'woo-postfinancecheckout'),
                'type' => 'title',
                'id' => 'integration_options'
            ),


            array(
                'title' => __('Integration Type', 'woo-postfinancecheckout'),
                'desc_tip' => __('The integration type controls how the payment form is integrated into the WooCommerce checkout. The Lightbox integration type offers better performance but with a less compelling checkout experience.', 'woo-postfinancecheckout'),
                'id' => WooCommerce_PostFinanceCheckout::CK_INTEGRATION,
                'type' => 'select',
                'css' => 'min-width:300px;',
                'default' => WC_PostFinanceCheckout_Integration::IFRAME,
                'options' => array(
                    WC_PostFinanceCheckout_Integration::IFRAME => __(WC_PostFinanceCheckout_Integration::IFRAME, 'woo-postfinancecheckout'),
                    WC_PostFinanceCheckout_Integration::LIGHTBOX  => __(WC_PostFinanceCheckout_Integration::LIGHTBOX, 'woo-postfinancecheckout'),
                ),
            ),

            array(
                'type' => 'sectionend',
                'id' => 'integration_options'
            ),

            array(
                'title' => __('Line Items Options', 'woo-postfinancecheckout'),
                'type' => 'title',
                'id' => 'line_items_options'
            ),

            array(
                'title' => __('Enforce Consistency', 'woo-postfinancecheckout'),
                'desc' => __("Require that the transaction line items total is matching the order total.", 'woo-postfinancecheckout'),
                'id' => WooCommerce_PostFinanceCheckout::CK_ENFORCE_CONSISTENCY,
                'type' => 'checkbox',
                'default' => 'yes',
                'css' => 'min-width:300px;'
            ),

            array(
                'type' => 'sectionend',
                'id' => 'line_items_options'
            ),


        );
		
		return apply_filters('wc_postfinancecheckout_settings', $settings);
	}
}
