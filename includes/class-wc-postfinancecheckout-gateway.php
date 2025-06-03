<?php
/**
 * Plugin Name: PostFinanceCheckout
 * Author: postfinancecheckout AG
 * Text Domain: postfinancecheckout
 * Domain Path: /languages/
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   postfinancecheckout AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WC_PostFinanceCheckout_Gateway.
 * This class implements the PostFinance Checkout gateways
 *
 * @class WC_PostFinanceCheckout_Gateway
 */
class WC_PostFinanceCheckout_Gateway extends WC_Payment_Gateway {

	/**
	 * Payment method configuration id.
	 *
	 * @var $payment_method_configuration_id payment method configuration id.
	 */
	private $payment_method_configuration_id;

	/**
	 * Contains a users saved tokens for this gateway.
	 *
	 * @var $tokens tokens.
	 */
	protected $tokens = array();

	/**
	 * We prefix out private variables as other plugins do strange things.
	 *
	 * @var $pfc_payment_method_configuration_id pfc payment method configuration id.
	 */
	private $pfc_payment_method_configuration_id;

	/**
	 * The pfc payment method cofiguration
	 *
	 * @var $pfc_payment_method_configuration pfc payment method cofiguration.
	 */
	private $pfc_payment_method_configuration = null;

	/**
	 * The pfc translated title
	 *
	 * @var $pfc_translated_title pfc translated title.
	 */
	private $pfc_translated_title = null;
	/**
	 * The pfc translated description
	 *
	 * @var $pfc_translated_description pfc translated description.
	 */
	private $pfc_translated_description = null;

	/**
	 * Show description?
	 *
	 * @var $pfc_show_description pfc show description?
	 */
	private $pfc_show_description = 'yes';

	/**
	 * Show icon?
	 *
	 * @var $pfc_show_icon pfc show icon?
	 */
	private $pfc_show_icon = 'yes';

	/**
	 * Image
	 *
	 * @var $pfc_image pfc image.
	 */
	private $pfc_image = null;

	/**
	 * Image base
	 *
	 * @var $pfc_image_base
	 */
	private $pfc_image_base = null;

	/**
	 * Check to see if we have made the gateway available already.
	 *
	 * @var $have_already_entered have we already entered.
	 */
	private $have_already_entered = false;

	/**
	 * Constructor.
	 *
	 * @param WC_PostFinanceCheckout_Entity_Method_Configuration $method configuration method.
	 */
	public function __construct( WC_PostFinanceCheckout_Entity_Method_Configuration $method ) {
		$this->payment_method_configuration_id = $method->get_value( 'configuration_id' );
		$this->pfc_payment_method_configuration_id = $method->get_id();
		$this->id = 'postfinancecheckout_' . $method->get_id();
		$this->has_fields = false;
		$this->method_title = $method->get_configuration_name();
		$this->method_description = WC_PostFinanceCheckout_Helper::instance()->translate( $method->get_description() );
		$this->pfc_image = $method->get_image();
		$this->pfc_image_base = $method->get_image_base();
		$this->icon = WC_PostFinanceCheckout_Helper::instance()->get_resource_url( $this->pfc_image, $this->pfc_image_base );

		// We set the title and description here, as some plugin access the variables directly.
		$this->title = $method->get_configuration_name();
		$this->description = '';

		$this->pfc_translated_title = $method->get_title();
		$this->pfc_translated_description = $method->get_description();

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->enabled = $this->get_option( 'enabled' );
		$this->pfc_show_description = $this->get_option( 'show_description' );
		$this->pfc_show_icon = $this->get_option( 'show_icon' );

		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);

		$this->supports = array(
			'products',
			'refunds',
		);
	}

	/**
	 * Returns the payment method configuration.
	 *
	 * @return WC_PostFinanceCheckout_Entity_Method_Configuration
	 */
	public function get_payment_method_configuration() {
		if ( is_null( $this->pfc_payment_method_configuration ) ) {
			$this->pfc_payment_method_configuration = WC_PostFinanceCheckout_Entity_Method_Configuration::load_by_id(
				$this->pfc_payment_method_configuration_id
			);
		}
		return $this->pfc_payment_method_configuration;
	}

	/**
	 * Return the gateway's title fontend.
	 *
	 * @return string
	 */
	public function get_title() {
		$title = $this->title;
		$translated = WC_PostFinanceCheckout_Helper::instance()->translate( $this->pfc_translated_title );
		if ( ! is_null( $translated ) ) {
			$title = $translated;
		}
		//phpcs:ignore
		return apply_filters( 'woocommerce_gateway_title', $title, $this->id );
	}

	/**
	 * Return the gateway's description frontend.
	 *
	 * @return string
	 */
	public function get_description() {
		$description = '';
		if ( 'yes' === $this->pfc_show_description ) {
			$translated = WC_PostFinanceCheckout_Helper::instance()->translate( $this->pfc_translated_description );
			if ( ! is_null( $translated ) ) {
				$description = $translated;
			}
		}
		//phpcs:ignore
		return apply_filters( 'woocommerce_gateway_description', $description, $this->id );
	}

	/**
	 * Return the gateway's icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		$icon = '';
		if ( 'yes' === $this->pfc_show_icon ) {
			$space_id = $this->get_payment_method_configuration()->get_space_id();
			$space_view_id = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_VIEW_ID );
			$language = WC_PostFinanceCheckout_Helper::instance()->get_cleaned_locale();

			$url = WC_PostFinanceCheckout_Helper::instance()->get_resource_url( $this->pfc_image_base, $this->pfc_image, $language, $space_id, $space_view_id );
			$icon = '<img src="' . WC_HTTPS::force_https_url( $url ) . '" alt="' . esc_attr( $this->get_title() ) . '" width="35px" />';
		}
		//phpcs:ignore
		return apply_filters( 'woocommerce_gateway_icon', $icon, $this->id );
	}

	/**
	 * Get the payment configuration id
	 *
	 * @return int
	 */
	public function get_payment_configuration_id() {
		return $this->payment_method_configuration_id;
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title' => esc_html__( 'Enable/Disable', 'woocommerce' ),
				'type' => 'checkbox',
				/* translators: %s: method title */
				'label' => sprintf( esc_html__( 'Enable %s', 'woo-postfinancecheckout' ), $this->method_title ),
				'default' => 'yes',
			),
			'title_value' => array(
				'title' => esc_html__( 'Title', 'woocommerce' ),
				'type' => 'info',
				'value' => $this->get_title(),
				'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'woo-postfinancecheckout' ),
			),
			'description_value' => array(
				'title' => esc_html__( 'Description', 'woocommerce' ),
				'type' => 'info',
				'value' => ! empty( $this->get_description() ) ? esc_attr( $this->get_description() ) : esc_html__( '[not set]', 'woo-postfinancecheckout' ),
				'description' => esc_html__( 'Payment method description that the customer will see on your checkout.', 'woo-postfinancecheckout' ),
			),
			'show_description' => array(
				'title' => esc_html__( 'Show description', 'woo-postfinancecheckout' ),
				'type' => 'checkbox',
				'label' => esc_html__( 'Yes', 'woo-postfinancecheckout' ),
				'default' => 'yes',
				'description' => esc_html__( "Show the payment method's description on the checkout page.", 'woo-postfinancecheckout' ),
				'desc_tip' => true,
			),
			'show_icon' => array(
				'title' => esc_html__( 'Show method image', 'woo-postfinancecheckout' ),
				'type' => 'checkbox',
				'label' => esc_html__( 'Yes', 'woo-postfinancecheckout' ),
				'default' => 'yes',
				'description' => esc_html__( "Show the payment method's image on the checkout page.", 'woo-postfinancecheckout' ),
				'desc_tip' => true,
			),
		);
	}

	/**
	 * Generate info HTML.
	 *
	 * @param  mixed $key key.
	 * @param  mixed $data data.
	 * @return string
	 */
	public function generate_info_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults = array(
			'title' => '',
			'class' => '',
			'css' => '',
			'placeholder' => '',
			'desc_tip' => true,
			'description' => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
<tr valign="top">
	<th scope="row" class="titledesc">
							<?php // phpcs:ignore ?>
							<?php echo esc_html( $this->get_tooltip_html( $data ) ); ?>
							<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
	</th>
	<td class="forminp">
		<fieldset>
			<legend class="screen-reader-text">
				<span><?php echo wp_kses_post( $data['title'] ); ?></span>
			</legend>
			<div class="input-text regular-input <?php echo esc_attr( $data['class'] ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php echo esc_html( $this->get_custom_attribute_html( $data ) ); ?> >
								<?php echo esc_html( $data['value'] ); ?>
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
	 * @param  string      $key Field key++.
	 * @param  string|null $value Posted Value.
	 * @return void
	 */
	public function validate_info_field( $key, $value ) {}

	/**
	 * Check if the gateway is available for use.
	 *
	 * @return bool
	 */
	public function is_available() {
		static $checking = false;
		if ( $checking ) {
			return false; // prevent reentry/loop
		}
		$checking = true;
	
		try {
			// Step 1: Respect parent availability
			if ( ! parent::is_available() ) {
				return false;
			}
	
			// Step 2: Prevent conflict with tax rounding
			if ( wc_tax_enabled() && 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
				if ( 'yes' === get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_ENFORCE_CONSISTENCY ) ) {
					$error_message = esc_html__( "'WooCommerce > Settings > PostFinanceCheckout > Enforce Consistency' and 'WooCommerce > Settings > Tax > Rounding' are both enabled. Please disable at least one of them.", 'woo-postfinancecheckout' );
					WooCommerce_PostFinanceCheckout::instance()->log( $error_message, WC_Log_Levels::ERROR );
					return false;
				}
			}
	
			// Step 3: Use session cache if available
			$gateway_available = WC()->session && WC()->session->has_session()
				? WC()->session->get( 'postfinancecheckout_payment_gateways' )
				: array();
	
			if ( isset( $gateway_available[ $this->wle_payment_method_configuration_id ] ) ) {
				return $gateway_available[ $this->wle_payment_method_configuration_id ];
			}
	
			// Step 4: Allow admin and non-checkout pages to pass
			if ( apply_filters(
				'postfinancecheckout_is_method_available',
				is_admin() || ! is_checkout() || ( isset( $GLOBALS['_postfinancecheckout_calculating'] ) && $GLOBALS['_postfinancecheckout_calculating'] ),
				$this
			) ) {
				return $this->get_payment_method_configuration()->get_state() === WC_PostFinanceCheckout_Entity_Method_Configuration::POSTFINANCECHECKOUT_STATE_ACTIVE;
			}
	
			// Step 5: Handle "order received" page logic
			global $wp;
			if ( is_checkout() && isset( $wp->query_vars['order-received'] ) ) {
				return ! empty( $gateway_available[ $this->wle_payment_method_configuration_id ] );
			}
	
			// Step 6: Handle "order pay" endpoint
			if ( apply_filters( 'wc_postfinancecheckout_is_order_pay_endpoint', is_checkout_pay_page() ) ) {
				$order = WC_Order_Factory::get_order( $wp->query_vars['order-pay'] ?? 0 );
				if ( ! $order ) {
					return false;
				}
				$possible_methods = $this->get_safe_possible_payment_methods_for_order( $order );
			} else {
				$possible_methods = $this->get_safe_possible_payment_methods_for_cart();
			}
	
			// Step 7: Check if this gateway is among the possible ones
			$possible = false;
			foreach ( $possible_methods as $method_id ) {
				if ( $method_id == $this->get_payment_method_configuration()->get_configuration_id() ) {
					$possible = true;
					break;
				}
			}
	
			if ( ! $possible ) {
				return false;
			}
	
			// Step 8: Cache success in session
			if ( WC()->session && WC()->session->has_session() ) {
				$gateway_available[ $this->wle_payment_method_configuration_id ] = true;
				WC()->session->set( 'postfinancecheckout_payment_gateways', $gateway_available );
			}
	
			return true;
	
		} finally {
			$checking = false;
		}
	}

	/**
	 * Safely fetches possible payment methods for the current cart.
	 *
	 * Handles known exceptions to prevent issues during cart calculation
	 * (especially important for avoiding loops with plugins like Germanized).
	 *
	 * @return array|false Array of method configuration IDs if successful, false on failure.
	 */
	protected function get_safe_possible_payment_methods_for_cart() {
		try {
			return WC_PostFinanceCheckout_Service_Transaction::instance()->get_possible_payment_methods_for_cart();
		} catch ( WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount $e ) {
			WooCommerce_PostFinanceCheckout::instance()->log( $e->getMessage(), WC_Log_Levels::ERROR );
		} catch ( \PostFinanceCheckout\Sdk\ApiException $e ) {
			WooCommerce_PostFinanceCheckout::instance()->log( $e->getMessage(), WC_Log_Levels::DEBUG );
			$response_object = $e->getResponseObject();
			$is_client_error = ( $response_object instanceof \PostFinanceCheckout\Sdk\Model\ClientError );
			if ( $is_client_error ) {
				$error_types = array( 'CONFIGURATION_ERROR', 'DEVELOPER_ERROR' );
				if ( in_array( $response_object->getType(), $error_types ) ) {
					$message = esc_attr( $response_object->getType() ) . ': ' . esc_attr( $response_object->getMessage() );
					wc_print_notice( $message, 'error' );
				}
			}
		} catch ( Exception $e ) {
			WooCommerce_PostFinanceCheckout::instance()->log( $e->getMessage(), WC_Log_Levels::DEBUG );
		}
	
		return false;
	}

	/**
	 * Safely fetches possible payment methods for a given WooCommerce order.
	 *
	 * Primarily used on the "order pay" endpoint where cart isn't available.
	 *
	 * @param WC_Order $order WooCommerce order object.
	 * @return array|false Array of method configuration IDs if successful, false on failure.
	 */
	protected function get_safe_possible_payment_methods_for_order( $order ) {
		try {
			return WC_Wallee_Service_Transaction::instance()->get_possible_payment_methods_for_order( $order );
		} catch ( WC_Wallee_Exception_Invalid_Transaction_Amount $e ) {
			WooCommerce_Wallee::instance()->log( $e->getMessage() . ' Order Id: ' . $order->get_id(), WC_Log_Levels::ERROR );
		} catch ( Exception $e ) {
			WooCommerce_Wallee::instance()->log( $e->getMessage(), WC_Log_Levels::DEBUG );
		}
		return false;
	}

	/**
	 * Check if the gateway has fields on the checkout.
	 *
	 * @return bool
	 */
	public function has_fields() {
		return true;
	}

	/**
	 * Load payment fields.
	 *
	 * @return bool
	 */
	public function payment_fields() {

		parent::payment_fields();
		$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
		$woocommerce_data = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php', false, false );
		try {
			if ( apply_filters( 'wc_postfinancecheckout_is_order_pay_endpoint', is_checkout_pay_page() ) ) { //phpcs:ignore
				global $wp;
				$order = WC_Order_Factory::get_order( $wp->query_vars['order-pay'] );
				if ( ! $order ) {
					return false;
				}
				$transaction = $transaction_service->get_transaction_from_order( $order );
			} else {
				$transaction = $transaction_service->get_transaction_from_session();
			}
			if ( ! wp_script_is( 'postfinancecheckout-remote-checkout-js', 'enqueued' ) ) {
				$ajax_url = $transaction_service->get_javascript_url_for_transaction( $transaction );
				// !isset($wp->query_vars['order-pay'])->If you're not in the "re-pay" checkout.
				if (
					( get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_INTEGRATION ) == WC_PostFinanceCheckout_Integration::POSTFINANCECHECKOUT_LIGHTBOX )
					&& ( is_checkout()
					&& ! isset( $wp->query_vars['order-pay'] ) )
				) {
					$ajax_url = $transaction_service->get_lightbox_url_for_transaction( $transaction );
				}
				wp_enqueue_script(
					'postfinancecheckout-remote-checkout-js',
					$ajax_url,
					array(
						'jquery',
					),
					1,
					true
				);
				wp_enqueue_script(
					'postfinancecheckout-checkout-js',
					WooCommerce_PostFinanceCheckout::instance()->plugin_url() . '/assets/js/frontend/checkout.js',
					array(
						'jquery',
						'jquery-blockui',
						'postfinancecheckout-remote-checkout-js',
					),
					1,
					true
				);
				global $wp_version;
				$localize = array(
					'i18n_not_complete' => esc_html__( 'Please fill out all required fields.', 'woo-postfinancecheckout' ),
					'integration' => get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_INTEGRATION ),
					'versions' => array(
						'wordpress' => $wp_version,
						'woocommerce' => $woocommerce_data['Version'],
						'postfinancecheckout' => WC_POSTFINANCECHECKOUT_VERSION,
					),
				);
				wp_localize_script( 'postfinancecheckout-checkout-js', 'postfinancecheckout_js_params', $localize );

			}
			$transaction_nonce = hash_hmac( 'sha256', $transaction->getLinkedSpaceId() . '-' . $transaction->getId(), NONCE_KEY );

			?>

			<div id="payment-form-<?php echo esc_attr( $this->id ); ?>">
				<input type="hidden" id="postfinancecheckout-iframe-possible-<?php echo esc_attr( ( $this->id ) ); ?>" name="postfinancecheckout-iframe-possible-<?php echo esc_attr( $this->id ); ?>" value="false" />
			</div>
			<input type="hidden" id="postfinancecheckout-space-id-<?php echo esc_attr( $this->id ); ?>" name="postfinancecheckout-space-id-<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $transaction->getLinkedSpaceId() ); ?>"  />
			<input type="hidden" id="postfinancecheckout-transaction-id-<?php echo esc_attr( $this->id ); ?>" name="postfinancecheckout-transaction-id-<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $transaction->getId() ); ?>"  />
			<input type="hidden" id="postfinancecheckout-transaction-nonce-<?php echo esc_attr( $this->id ); ?>" name="postfinancecheckout-transaction-nonce-<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $transaction_nonce ); ?>" />
			<div id="postfinancecheckout-method-configuration-<?php echo esc_attr( $this->id ); ?>"
				class="postfinancecheckout-method-configuration" style="display: none;"
				data-method-id="<?php echo esc_attr( $this->id ); ?>"
				data-configuration-id="<?php echo esc_attr( $this->get_payment_method_configuration()->get_configuration_id() ); ?>"
				data-container-id="payment-form-<?php echo esc_attr( $this->id ); ?>" data-description-available="<?php var_export( ! empty( $this->get_description() ) ); //phpcs:ignore ?>"></div>
			<?php

		} catch ( Exception $e ) {
			WooCommerce_PostFinanceCheckout::instance()->log( $e->getMessage(), WC_Log_Levels::DEBUG );
		}
	}

	/**
	 * Validate frontend fields.
	 *
	 * @return bool
	 */
	public function validate_fields() {
		return true;
	}

	/**
	 * Process Payment.
	 *
	 * @param int $order_id order id.
	 * @return array
	 */
	public function process_payment( $order_id ) {

		$space_id = '';
		$transaction_id = '';
		$transaction_nonce = '';

		if ( isset( $_POST[ 'postfinancecheckout-space-id-' . $this->id ] ) ) {
			$space_id = sanitize_text_field( wp_unslash( $_POST[ 'postfinancecheckout-space-id-' . $this->id ] ) );
		}

		if ( isset( $_POST[ 'postfinancecheckout-transaction-id-' . $this->id ] ) ) {
			$transaction_id = sanitize_text_field( wp_unslash( $_POST[ 'postfinancecheckout-transaction-id-' . $this->id ] ) );
		}

		if ( isset( $_POST[ 'postfinancecheckout-transaction-nonce-' . $this->id ] ) ) {
			$transaction_nonce = sanitize_text_field( wp_unslash( $_POST[ 'postfinancecheckout-transaction-nonce-' . $this->id ] ) );
		}

		$is_order_pay_endpoint = apply_filters( 'wc_postfinancecheckout_is_order_pay_endpoint', is_checkout_pay_page() );

		if ( hash_hmac( 'sha256', $space_id . '-' . $transaction_id, NONCE_KEY ) != $transaction_nonce ) {
			WC()->session->set( 'postfinancecheckout_failure_message', esc_html__( 'The checkout timed out, please try again.', 'woo-postfinancecheckout' ) );
			WC()->session->set( 'reload_checkout', true );
			return array(
				'result' => 'failure',
			);
		}

		$existing = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order_id );
		if ( $existing->get_id() ) {
			if ( $existing->get_space_id() !== $space_id && $existing->get_transaction_id() != $transaction_id ) {
				WC()->session->set( 'postfinancecheckout_failure_message', esc_html__( 'There was an issue, while processing your order. Please try again or use another payment method.', 'woo-postfinancecheckout' ) );
				WC()->session->set( 'reload_checkout', true );
				return array(
					'result' => 'failure',
				);
			}
		}

		$order = wc_get_order( $order_id );
		$sanitized_post_data = wp_verify_nonce( isset( $_POST[ 'postfinancecheckout-iframe-possible-' . $this->id ] ) );
		$no_iframe = isset( $sanitized_post_data ) && 'false' == $sanitized_post_data;

		try {
			$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();

			[ $result, $transaction ] = $this->process_payment_transaction( $order, $transaction_id, $space_id, $is_order_pay_endpoint, $transaction_service );


			$integration_mode = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_INTEGRATION );

			$redirect_url = $transaction_service->get_payment_page_url( $transaction->getLinkedSpaceId(), $transaction->getId() );
			if ( WC_PostFinanceCheckout_Integration::POSTFINANCECHECKOUT_PAYMENTPAGE === $integration_mode ) {
				// Get Payment Page URL.
				$transaction_service = WC_PostFinanceCheckout_Service_Transaction::instance();
				$redirect_url = $transaction_service->get_payment_page_url( get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_SPACE_ID ), $transaction->getId() );
				$result = array(
					'result' => 'success',
					'redirect' => $redirect_url,
				);
				return $result;
			}

			if ( $no_iframe || apply_filters( 'wc_postfinancecheckout_gateway_result_send_json', $is_order_pay_endpoint, $order_id ) ) { //phpcs:ignore
				$result = array(
					'result' => 'success',
					'redirect' => $redirect_url,
				);
			}
			return $result;
		} catch ( Exception $e ) {
			$message = $e->getMessage();
			$cleaned = preg_replace( '/^\[[A-Fa-f\d\-]+\] /', '', $message );
			WC()->session->set( 'postfinancecheckout_failure_message', $cleaned );
			apply_filters( 'postfinancecheckout_order_update_status', $order, 'failed' );
			$result = array(
				'result' => 'failure',
				'reload' => 'true',
			);
			if ( apply_filters( 'wc_postfinancecheckout_gateway_result_send_json', $is_order_pay_endpoint, $order_id ) ) { //phpcs:ignore
				wp_send_json( $result );
				exit;
			}
			WC()->session->set( 'reload_checkout', true );
			return array(
				'result' => 'failure',
			);
		}
	}

	/**
	 * Processes the payment transaction.
	 *
	 * Handles the transaction processing for an order by interacting with the transaction service. It updates
	 * the order's metadata based on the transaction state and handles the session cleanup post-transaction.
	 * If the transaction is in a PENDING state, it confirms the transaction and updates the transaction info
	 * in the order. It also sets up the redirect URL upon successful payment and returns the result and transaction.
	 *
	 * @param WC_Order                        $order The WooCommerce order object.
	 * @param int                             $transaction_id The ID of the transaction.
	 * @param int                             $space_id The space ID associated with the transaction.
	 * @param bool                            $is_order_pay_endpoint Flag to determine if the order is being paid for at the order-pay endpoint.
	 * @param WC_PostFinanceCheckout_Service_Transaction $transaction_service The transaction service instance.
	 * @return array An array containing the result of the transaction and the transaction object.
	 *
	 * @throws Throwable Throws an exception if there is an issue processing the transaction.
	 */
	public function process_payment_transaction( $order, $transaction_id, $space_id, $is_order_pay_endpoint, $transaction_service ) {
		try {
			$transaction_service->api_client->addDefaultHeader(
				WC_PostFinanceCheckout_Helper::POSTFINANCECHECKOUT_CHECKOUT_VERSION,
				WC_PostFinanceCheckout_Helper::POSTFINANCECHECKOUT_CHECKOUT_TYPE_LEGACY
			);
			$transaction = $transaction_service->get_transaction( $space_id, $transaction_id );

			$order->add_meta_data( '_postfinancecheckout_pay_for_order', $is_order_pay_endpoint, true );
			$order->add_meta_data( '_postfinancecheckout_gateway_id', $this->id, true );
			$order->delete_meta_data( '_postfinancecheckout_confirmed' );
			$order->save();

			if ( $transaction->getState() == \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING ) {
				$transaction = $transaction_service->confirm_transaction( $transaction_id, $space_id, $order, $this->get_payment_method_configuration()->get_configuration_id() );
				$transaction_service->update_transaction_info( $transaction, $order );
			}

			WC()->session->set( 'order_awaiting_payment', false );
			WC_PostFinanceCheckout_Helper::instance()->destroy_current_cart_id();
			WC()->session->set( 'postfinancecheckout_space_id', null );
			WC()->session->set( 'postfinancecheckout_transaction_id', null );

			// now it is mandatory to send the redirect property in the json response.
			$gateway = wc_get_payment_gateway_by_order( $order );
			$url = apply_filters( 'wc_postfinancecheckout_success_url', $gateway->get_return_url( $order ), $order ); //phpcs:ignore
			$result = array(
				'result' => 'success',
				'postfinancecheckout' => 'true',
				'redirect' => $url,
			);

			return array( $result, $transaction );
		} catch ( Throwable $e ) {
			throw $e;
		}
	}

	/**
	 * Process refund.
	 *
	 * If the gateway declares 'refunds' support, this will allow it to refund.
	 * a passed in amount.
	 *
	 * @param  int    $order_id order id.
	 * @param  float  $amount amount.
	 * @param  string $reason reason.
	 * @return boolean True or false based on success, or a WP_Error object.
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		if ( ! isset( $GLOBALS['postfinancecheckout_refund_id'] ) ) {
			return new WP_Error( 'postfinancecheckout_error', esc_html__( 'There was a problem creating the refund.', 'woo-postfinancecheckout' ) );
		}
		$refund = WC_Order_Factory::get_order( $GLOBALS['postfinancecheckout_refund_id'] );
		$order = WC_Order_Factory::get_order( $order_id );

		try {
			WC_PostFinanceCheckout_Admin_Refund::execute_refund( $order, $refund );
		} catch ( Exception $e ) {
			return new WP_Error( 'postfinancecheckout_error', $e->getMessage() );
		}

		$refund_job_id = $refund->get_meta( '_postfinancecheckout_refund_job_id', true );

		$wait = 0;
		while ( $wait < 5 ) {
			$refund_job = WC_PostFinanceCheckout_Entity_Refund_Job::load_by_id( $refund_job_id );
			if ( $refund_job->get_state() == WC_PostFinanceCheckout_Entity_Refund_Job::POSTFINANCECHECKOUT_STATE_FAILURE ) {
				return new WP_Error( 'postfinancecheckout_error', $refund_job->get_failure_reason() );
			} elseif ( $refund_job->get_state() == WC_PostFinanceCheckout_Entity_Refund_Job::POSTFINANCECHECKOUT_STATE_SUCCESS ) {
				return true;
			}
			++$wait;
			sleep( 1 );
		}
		return true;
	}
}
