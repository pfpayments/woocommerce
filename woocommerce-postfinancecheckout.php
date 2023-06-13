<?php
/**
 * Plugin Name: PostFinance Checkout
 * Plugin URI: https://wordpress.org/plugins/woo-postfinancecheckout
 * Description: Process WooCommerce payments with PostFinance Checkout.
 * Version: 2.1.18
 * License: Apache2
 * License URI: http://www.apache.org/licenses/LICENSE-2.0
 * Author: wallee AG
 * Author URI: https://www.wallee.com
 * Requires at least: 4.7
 * Tested up to: 6.2
 * WC requires at least: 3.0.0
 * WC tested up to: 7.7.0
 *
 * Text Domain: postfinancecheckout
 * Domain Path: /languages/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly.
}

if ( ! class_exists( 'WooCommerce_PostFinanceCheckout' ) ) {

	/**
	 * Main WooCommerce PostFinanceCheckout Class
	 *
	 * @class WooCommerce_PostFinanceCheckout
	 */
	final class WooCommerce_PostFinanceCheckout {

		const CK_SPACE_ID = 'wc_postfinancecheckout_space_id';
		const CK_SPACE_VIEW_ID = 'wc_postfinancecheckout_space_view_id';
		const CK_APP_USER_ID = 'wc_postfinancecheckout_application_user_id';
		const CK_APP_USER_KEY = 'wc_postfinancecheckout_application_user_key';
		const CK_CUSTOMER_INVOICE = 'wc_postfinancecheckout_customer_invoice';
		const CK_CUSTOMER_PACKING = 'wc_postfinancecheckout_customer_packing';
		const CK_SHOP_EMAIL = 'wc_postfinancecheckout_shop_email';
		const CK_INTEGRATION = 'wc_postfinancecheckout_integration';
		const CK_ORDER_REFERENCE = 'wc_postfinancecheckout_order_reference';
		const CK_ENFORCE_CONSISTENCY = 'wc_postfinancecheckout_enforce_consistency';
		const WC_MAXIMUM_VERSION = '7.7.0';

		/**
		 * WooCommerce PostFinanceCheckout version.
		 *
		 * @var string
		 */
		private $version = '2.1.18';

		/**
		 * The single instance of the class.
		 *
		 * @var WooCommerce_PostFinanceCheckout
		 */
		protected static $_instance = null;

		/**
		 * Logger.
		 *
		 * @var mixed $logger logger.
		 */
		private $logger = null;

		/**
		 * Main WooCommerce PostFinanceCheckout Instance.
		 *
		 * Ensures only one instance of WooCommerce PostFinanceCheckout is loaded or can be loaded.
		 *
		 * @return WooCommerce_PostFinanceCheckout - Main instance.
		 */
		public static function instance() {
			if ( null === self::$_instance ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * WooCommerce PostFinanceCheckout Constructor.
		 */
		protected function __construct() {
			$this->define_constants();
			$this->includes();
			$this->init_hooks();
		}

		/**
		 * Get version.
		 */
		public function get_version() {
			return $this->version;
		}

		/**
		 * Define WC PostFinanceCheckout Constants.
		 */
		protected function define_constants() {
			$this->define( 'WC_POSTFINANCECHECKOUT_PLUGIN_FILE', __FILE__ );
			$this->define( 'WC_POSTFINANCECHECKOUT_ABSPATH', dirname( __FILE__ ) . '/' );
			$this->define( 'WC_POSTFINANCECHECKOUT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'WC_POSTFINANCECHECKOUT_VERSION', $this->version );
			$this->define( 'WC_POSTFINANCECHECKOUT_REQUIRED_PHP_VERSION', '5.6' );
			$this->define( 'WC_POSTFINANCECHECKOUT_REQUIRED_WP_VERSION', '4.7' );
			$this->define( 'WC_POSTFINANCECHECKOUT_REQUIRED_WC_VERSION', '3.0' );
			$this->define( 'WC_POSTFINANCECHECKOUT_REQUIRED_WC_MAXIMUM_VERSION', self::WC_MAXIMUM_VERSION );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 */
		protected function includes() {
			/**
			 * Class autoloader.
			 */
			require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-autoloader.php' );
			require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'postfinancecheckout-sdk/autoload.php' );

			require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-migration.php' );
			require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-email.php' );
			require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-return-handler.php' );
			require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-webhook-handler.php' );
			require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-unique-id.php' );
			require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-customer-document.php' );
			require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-cron.php' );
			require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/packages/coupon/class-wc-postfinancecheckout-packages-coupon-discount.php' );

			if ( is_admin() ) {
				require_once( WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/admin/class-wc-postfinancecheckout-admin.php' );
			}
		}

		/**
		 * Init hooks.
		 *
		 * @return void
		 */
		protected function init_hooks() {
			register_activation_hook(
				__FILE__,
				array(
					'WC_PostFinanceCheckout_Migration',
					'install_db',
				)
			);
			register_activation_hook(
				__FILE__,
				array(
					'WC_PostFinanceCheckout_Cron',
					'activate',
				)
			);
			register_deactivation_hook(
				__FILE__,
				array(
					'WC_PostFinanceCheckout_Cron',
					'deactivate',
				)
			);

			/*add_action(
				'woocommerce_thankyou',
				array(
					$this,
					'secure_redirect_order_confirmed',
					)
			);*/

			add_action(
				'plugins_loaded',
				array(
					$this,
					'loaded',
				),
				0
			);
			add_action(
				'init',
				array(
					$this,
					'register_order_statuses',
				)
			);
			add_action(
				'init',
				array(
					$this,
					'set_device_id_cookie',
				)
			);
			add_action(
				'wp_enqueue_scripts',
				array(
					$this,
					'enqueue_javascript_script',
				)
			);
			add_action(
				'wp_enqueue_scripts',
				array(
					$this,
					'enqueue_stylesheets',
				)
			);
			add_filter(
				'script_loader_tag',
				array(
					$this,
					'set_js_async',
				),
				20,
				3
			);
		}


		/**
		 * Secirity check in the thank you page.
		 *
		 * Note: If for some reason order status is still pending, it will redirect you to the payment form.
		 *
		 */

		 public function secure_redirect_order_confirmed($order_id) {
			$order = wc_get_order( $order_id );
			$wc_service_transaction = WC_PostFinanceCheckout_Service_Transaction::instance();
			$sdk_service_transaction = new \PostFinanceCheckout\Sdk\Service\TransactionService(WC_PostFinanceCheckout_Helper::instance()->get_api_client());
			$wc_transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id($order_id);

			if (property_exists($wc_transaction_info,'get_transaction_id')) {
			  $state = $sdk_service_transaction->read(get_option(self::CK_SPACE_ID), $wc_transaction_info->get_transaction_id())->getState();
			  if ($state == \PostFinanceCheckout\Sdk\Model\TransactionState::CONFIRMED) {
				  wp_redirect($wc_service_transaction->get_payment_page_url(get_option(self::CK_SPACE_ID), $wc_transaction_info->get_transaction_id()));
				  exit;
			  }
			}
		}

		/**
		 * Load Localization files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 *      - WP_LANG_DIR/woo-postfinancecheckout/woo-postfinancecheckout-LOCALE.mo
		 */
		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'woo-postfinancecheckout' );

			load_textdomain( 'woo-postfinancecheckout', WP_LANG_DIR . '/woo-postfinancecheckout/woo-postfinancecheckout' . $locale . '.mo' );
			load_plugin_textdomain( 'woo-postfinancecheckout', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Init WooCommerce PostFinanceCheckout when plugins are loaded.
		 */
		public function loaded() {

			// Set up localisation.
			$this->load_plugin_textdomain();

			add_filter(
				'woocommerce_payment_gateways',
				array(
					$this,
					'add_gateways',
				)
			);
			add_filter(
				'wc_order_statuses',
				array(
					$this,
					'add_order_statuses',
				)
			);
			add_filter(
				'wc_order_is_editable',
				array(
					$this,
					'order_editable_check',
				),
				10,
				2
			);
			add_filter(
				'woocommerce_before_calculate_totals',
				array(
					$this,
					'before_calculate_totals',
				),
				10
			);
			add_filter(
				'woocommerce_after_calculate_totals',
				array(
					$this,
					'after_calculate_totals',
				),
				10
			);
			add_filter(
				'woocommerce_valid_order_statuses_for_payment_complete',
				array(
					$this,
					'valid_order_status_for_completion',
				),
				10,
				2
			);
			add_filter(
				'woocommerce_form_field_args',
				array(
					$this,
					'modify_form_fields_args',
				),
				10,
				3
			);
			add_action(
				'woocommerce_checkout_update_order_review',
				array(
					$this,
					'update_additional_customer_data',
				)
			);
			add_action(
				'woocommerce_before_checkout_form',
				array(
					$this,
					'register_checkout_error_msg',
				),
				5,
				0
			);

			add_action(
				'before_woocommerce_pay',
				array(
					$this,
					'show_checkout_error_msg',
				),
				5,
				0
			);

			add_action(
				'woocommerce_attribute_added',
				array(
					$this,
					'woocommerce_attribute_added',
				),
				10,
				2
			);

			add_action(
				'woocommerce_attribute_updated',
				array(
					$this,
					'woocommerce_attribute_updated',
				),
				10,
				3
			);

			add_action(
				'woocommerce_attribute_deleted',
				array(
					$this,
					'woocommerce_attribute_deleted',
				),
				10,
				3
			);

			add_action(
				'woocommerce_rest_insert_product_attribute',
				array(
					$this,
					'woocommerce_rest_insert_product_attribute',
				),
				10,
				3
			);

			add_action(
				'woocommerce_cart_item_removed',
				array(
					$this,
					'after_remove_product_from_cart',
				),
				10,
				2
			);

			add_filter(
				'woocommerce_rest_prepare_product_attribute',
				array(
					$this,
					'woocommerce_rest_prepare_product_attribute',
				),
				10,
				3
			);

			add_filter(
				'nocache_headers',
				array(
					$this,
					'add_cache_no_store',
				),
				10,
				1
			);

			add_filter(
				'woocommerce_valid_order_statuses_for_payment',
				array(
					$this,
					'valid_order_statuses_for_payment',
				),
				10,
				2
			);
		}

		/**
		 * After remove product from cart.
		 *
		 * @param mixed $removed_cart_item_key removed product from cart.
		 * @param mixed $cart cart.
		 * @return void
		 */
		public function after_remove_product_from_cart( $removed_cart_item_key, $cart ) {
			$line_item = $cart->removed_cart_contents[ $removed_cart_item_key ];
			$product = wc_get_product( $line_item['product_id'] );
			if ( $this->is_product_type_subscription( $product ) ) {
				// create new transaction in portal by clearing transaction cache.
				$service_transaction = WC_PostFinanceCheckout_Service_Transaction::instance();
				$service_transaction->clear_transaction_cache();
			}
		}

		/**
		 * Is product type subscription.
		 *
		 * @param mixed $product product.
		 * @return bool
		 */
		public function is_product_type_subscription( $product ) {
			if ( class_exists( 'WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $product ) ) {
				return true;
			}
			return false;
		}

		/**
		 * Register order statuses.
		 *
		 * @return void
		 */
		public function register_order_statuses() {
			register_post_status(
				'wc-postfi-redirected',
				array(
					'label' => 'Processing',
					'public' => true,
					'exclude_from_search' => false,
					'show_in_admin_all_list' => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: replaces string */
					'label_count' => _n_noop( 'PostFinance Checkout Processing <span class="count">(%s)</span>', 'PostFinance Checkout Processing <span class="count">(%s)</span>' ),
				)
			);
			register_post_status(
				'wc-postfi-waiting',
				array(
					'label' => 'Waiting',
					'public' => true,
					'exclude_from_search' => false,
					'show_in_admin_all_list' => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: replaces string */
					'label_count' => _n_noop( 'Waiting <span class="count">(%s)</span>', 'Waiting <span class="count">(%s)</span>' ),
				)
			);
			register_post_status(
				'wc-postfi-manual',
				array(
					'label' => 'Manual Decision',
					'public' => true,
					'exclude_from_search' => false,
					'show_in_admin_all_list' => true,
					'show_in_admin_status_list' => true,
					/* translators: %s: replaces string */
					'label_count' => _n_noop( 'Manual Decision <span class="count">(%s)</span>', 'Manual Decision <span class="count">(%s)</span>' ),
				)
			);
		}

		/**
		 * Add order statuses.
		 *
		 * @param mixed $order_statuses order statuses.
		 * @return mixed
		 */
		public function add_order_statuses( $order_statuses ) {
			$order_statuses['wc-postfi-redirected'] = _x( 'Redirected', 'Order status', 'woocommerce' );
			$order_statuses['wc-postfi-waiting'] = _x( 'Waiting', 'Order status', 'woocommerce' );
			$order_statuses['wc-postfi-manual'] = _x( 'Manual Decision', 'Order status', 'woocommerce' );

			return $order_statuses;
		}

		/**
		 * Valid order statuses for payment.
		 *
		 * @param mixed $statuses statuses.
		 * @param mixed $order order.
		 * @return mixed
		 */
		public function valid_order_statuses_for_payment( $statuses, $order = null ) {
			$statuses[] = 'postfi-redirected';

			return $statuses;
		}

		/**
		 * Set device id cookie.
		 *
		 * @return void
		 */
		public function set_device_id_cookie() {
			$value = WC_PostFinanceCheckout_Unique_Id::get_uuid();
			if ( isset( $_COOKIE['wc_postfinancecheckout_device_id'] ) && ! empty( $_COOKIE['wc_postfinancecheckout_device_id'] ) ) {
				$value = sanitize_text_field( wp_unslash( $_COOKIE['wc_postfinancecheckout_device_id'] ) );
			}
			setcookie( 'wc_postfinancecheckout_device_id', $value, time() + YEAR_IN_SECONDS, '/' );
		}

		/**
		 * Set JS async.
		 *
		 * @param mixed $tag tag.
		 * @param mixed $handle handle.
		 * @param mixed $src src.
		 * @return array|mixed|string|string[]
		 */
		public function set_js_async( $tag, $handle, $src ) {
			$async_script_handles = array( 'postfinancecheckout-device-id-js' );
			foreach ( $async_script_handles as $async_handle ) {
				if ( $async_handle == $handle ) {
					return str_replace( ' src', ' async="async" src', $tag );
				}
			}
			return $tag;
		}

		/**
		 * Enqueue javascript script.
		 *
		 * @return void
		 * @throws Exception Exception.
		 */
		public function enqueue_javascript_script() {
			if ( is_cart() || is_checkout() ) {
				$unique_id = isset( $_COOKIE['wc_postfinancecheckout_device_id'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['wc_postfinancecheckout_device_id'] ) ) : null;
				$space_id = get_option( self::CK_SPACE_ID );
				$script_url = WC_PostFinanceCheckout_Helper::instance()->get_base_gateway_url() . 's/' .
						$space_id . '/payment/device.js?sessionIdentifier=' .
						$unique_id;
				wp_enqueue_script( 'postfinancecheckout-device-id-js', $script_url, array(), $this->get_version(), true );
			}
		}

		/**
		 * Enqueue stylesheets.
		 *
		 * @return void
		 */
		public function enqueue_stylesheets() {
			if ( is_checkout() ) {
				wp_enqueue_style( 'postfinancecheckout-checkout-css', $this->plugin_url() . '/assets/css/checkout.css', array(), $this->get_version() );
			}
		}

		/**
		 * Order editable check.
		 *
		 * @param mixed         $allowed allowed.
		 * @param WC_Order|null $order order.
		 * @return false|mixed
		 */
		public function order_editable_check( $allowed, WC_Order $order = null ) {
			if ( null == $order ) {
				return $allowed;
			}
			if ( $order->get_meta( '_postfinancecheckout_authorized', true ) ) {
				return false;
			}
			return $allowed;
		}

		/**
		 * Valid order status for completion.
		 *
		 * @param mixed         $statuses statuses.
		 * @param WC_Order|null $order order.
		 * @return mixed
		 */
		public function valid_order_status_for_completion( $statuses, WC_Order $order = null ) {
			$statuses[] = 'postfi-waiting';
			$statuses[] = 'postfi-manual';
			$statuses[] = 'postfi-redirected';

			return $statuses;
		}

		/**
		 * Before calculate totals.
		 *
		 * @param mixed $cart cart.
		 * @return void
		 */
		public function before_calculate_totals( $cart ) {
			$GLOBALS['_wc_postfinancecheckout_calculating'] = true;
		}

		/**
		 * After calculate totals.
		 *
		 * @param mixed $cart cart.
		 * @return void
		 */
		public function after_calculate_totals( $cart ) {
			unset( $GLOBALS['_wc_postfinancecheckout_calculating'] );
		}


		/**
		 * Add gateways.
		 *
		 * @param mixed $methods methods.
		 * @return mixed
		 */
		public function add_gateways( $methods ) {
			$space_id = get_option( self::CK_SPACE_ID );
			$method_configurations = WC_PostFinanceCheckout_Entity_Method_Configuration::load_by_states_and_space_id(
				$space_id,
				array(
					WC_PostFinanceCheckout_Entity_Method_Configuration::STATE_ACTIVE,
					WC_PostFinanceCheckout_Entity_Method_Configuration::STATE_INACTIVE,
				)
			);
			try {
				foreach ( $method_configurations as $configuration ) {
					$gateway = new WC_PostFinanceCheckout_Gateway( $configuration );
					$methods[] = apply_filters( 'wc_postfinancecheckout_enhance_gateway', $gateway );
				}
			} catch ( \PostFinanceCheckout\Sdk\ApiException $e ) {
				if ( $e->getCode() === 401 ) {
					// Ignore it because we simply are not allowed to access the API.
						return $methods;
				} else {
					$this->log( $e->getMessage(), WC_Log_Levels::CRITICAL );
				}
			}
			return $methods;
		}

		/**
		 * Modify form fields args.
		 *
		 * @param mixed $arguments arguments.
		 * @param mixed $key key.
		 * @param mixed $value calue.
		 * @return array
		 */
		public function modify_form_fields_args( $arguments, $key, $value = null ) {
			if ( 'billing_company' == $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'billing_email' == $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'billing_phone' == $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'billing_first_name' == $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'billing_last_name' == $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'shipping_first_name' == $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'shipping_last_name' == $key ) {
				$arguments['class'][] = 'address-field';
			}

			return $arguments;
		}

		/**
		 * Update additional customer data.
		 *
		 * @param mixed $arguments arguments.
		 * @return void
		 */
		public function update_additional_customer_data( $arguments ) {
			$post_data = array();
			if ( ! empty( $arguments ) ) {
				wp_parse_str( $arguments, $post_data );
			}

			WC()->customer->set_props(
				array(
					'billing_first_name' => isset( $post_data['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_first_name'] ) ) : null,
					'billing_last_name' => isset( $post_data['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_last_name'] ) ) : null,
					'billing_company' => isset( $post_data['billing_company'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_company'] ) ) : null,
					'billing_phone' => isset( $post_data['billing_phone'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_phone'] ) ) : null,
					'billing_email' => isset( $post_data['billing_email'] ) && is_email( wp_unslash( $post_data['billing_email'] ) ) ? sanitize_email( wp_unslash( $post_data['billing_email'] ) ) : null,
				)
			);

			if ( wc_ship_to_billing_address_only() || ! isset( $post_data['ship_to_different_address'] ) || '0' == $post_data['ship_to_different_address'] ) {
				WC()->customer->set_props(
					array(
						'shipping_first_name' => isset( $post_data['billing_first_name'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_first_name'] ) ) : null,
						'shipping_last_name' => isset( $post_data['billing_last_name'] ) ? sanitize_text_field( wp_unslash( $post_data['billing_last_name'] ) ) : null,
					)
				);
			} else {
				WC()->customer->set_props(
					array(
						'shipping_first_name' => isset( $post_data['shipping_first_name'] ) ? sanitize_text_field( wp_unslash( $post_data['shipping_first_name'] ) ) : null,
						'shipping_last_name' => isset( $post_data['shipping_last_name'] ) ? sanitize_text_field( wp_unslash( $post_data['shipping_last_name'] ) ) : null,
					)
				);
			}

			// Handle custom created fields (Date of Birth / gender).
			$billing_date_of_birth = '';
			$custom_billing_date_of_birth_field_name = apply_filters( 'wc_postfinancecheckout_billing_date_of_birth_field_name', '' );

			if ( ! empty( $custom_billing_date_of_birth_field_name ) && ! empty( $post_data[ $custom_billing_date_of_birth_field_name ] ) ) {
				$billing_date_of_birth = sanitize_text_field( wp_unslash( $post_data[ $custom_billing_date_of_birth_field_name ] ) );
			} elseif ( ! empty( $post_data['billing_date_of_birth'] ) ) {
				$billing_date_of_birth = sanitize_text_field( wp_unslash( $post_data['billing_date_of_birth'] ) );
			} elseif ( ! empty( $post_data['_billing_date_of_birth'] ) ) {
				$billing_date_of_birth = sanitize_text_field( wp_unslash( $post_data['_billing_date_of_birth'] ) );
			}

			$billing_gender = '';
			$custom_billing_gender_field_name = apply_filters( 'wc_postfinancecheckout_billing_gender_field_name', '' );

			if ( ! empty( $custom_billing_gender_field_name ) && ! empty( $post_data[ $custom_billing_gender_field_name ] ) ) {
				$billing_gender = sanitize_text_field( wp_unslash( $post_data[ $custom_billing_gender_field_name ] ) );
			} elseif ( ! empty( $post_data['billing_gender'] ) ) {
				$billing_gender = sanitize_text_field( wp_unslash( $post_data['billing_gender'] ) );
			} elseif ( ! empty( $post_data['_billing_gender'] ) ) {
				$billing_gender = sanitize_text_field( wp_unslash( $post_data['_billing_gender'] ) );
			}

			if ( ! empty( $billing_date_of_birth ) ) {
				WC()->customer->add_meta_data( '_postfinancecheckout_billing_date_of_birth', $billing_date_of_birth, true );
			}
			if ( ! empty( $billing_gender ) ) {
				WC()->customer->add_meta_data( '_postfinancecheckout_billing_gender', $billing_gender, true );
			}

			if ( ! empty( $post_data['ship_to_different_address'] ) && ! wc_ship_to_billing_address_only() ) {
				$shipping_date_of_birth = '';
				$custom_shipping_date_of_birth_field_name = apply_filters( 'wc_postfinancecheckout_shipping_date_of_birth_field_name', '' );

				if ( ! empty( $custom_shipping_date_of_birth_field_name ) && ! empty( $post_data[ $custom_shipping_date_of_birth_field_name ] ) ) {
					$shipping_date_of_birth = sanitize_text_field( wp_unslash( $post_data[ $custom_shipping_date_of_birth_field_name ] ) );
				} elseif ( ! empty( $post_data['shipping_date_of_birth'] ) ) {
					$shipping_date_of_birth = sanitize_text_field( wp_unslash( $post_data['shipping_date_of_birth'] ) );
				} elseif ( ! empty( $post_data['_shipping_date_of_birth'] ) ) {
					$shipping_date_of_birth = sanitize_text_field( wp_unslash( $post_data['_shipping_date_of_birth'] ) );
				}

				$shipping_gender = '';
				$custom_shipping_gender_field_name = apply_filters( 'wc_postfinancecheckout_shipping_gender_field_name', '' );

				if ( ! empty( $custom_shipping_gender_field_name ) && ! empty( $post_data[ $custom_shipping_gender_field_name ] ) ) {
					$shipping_gender = sanitize_text_field( wp_unslash( $post_data[ $custom_shipping_gender_field_name ] ) );
				} elseif ( ! empty( $post_data['shipping_gender'] ) ) {
					$shipping_gender = sanitize_text_field( wp_unslash( $post_data['shipping_gender'] ) );
				} elseif ( ! empty( $post_data['_shipping_gender'] ) ) {
					$shipping_gender = sanitize_text_field( wp_unslash( $post_data['_shipping_gender'] ) );
				}

				if ( ! empty( $shipping_date_of_birth ) ) {
					WC()->customer->add_meta_data( '_postfinancecheckout_shipping_date_of_birth', $shipping_date_of_birth, true );
				}
				if ( ! empty( $shipping_gender ) ) {
					WC()->customer->add_meta_data( '_postfinancecheckout_shipping_gender', $shipping_gender, true );
				}
			} else {
				if ( ! empty( $billing_date_of_birth ) ) {
					WC()->customer->add_meta_data( '_postfinancecheckout_shipping_date_of_birth', $billing_date_of_birth, true );
				}
				if ( ! empty( $billing_gender ) ) {
					WC()->customer->add_meta_data( '_postfinancecheckout_shipping_gender', $billing_gender, true );
				}
			}
		}

		/**
		 * Register checkout error msg.
		 *
		 * @return void
		 */
		public function register_checkout_error_msg() {
			$msg = WC()->session->get( 'postfinancecheckout_failure_message', null );
			if ( ! empty( $msg ) ) {
				$this->add_notice( (string) $msg, 'error' );
				WC()->session->set( 'postfinancecheckout_failure_message', null );
			}
		}

		/**
		 * Show checkout error msg.
		 *
		 * @return void
		 */
		public function show_checkout_error_msg() {
			$this->register_checkout_error_msg();
			wc_print_notices();
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param  string      $name name.
		 * @param  string|bool $value value.
		 */
		protected function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Log.
		 *
		 * @param mixed $message message.
		 * @param mixed $level level.
		 * @return void
		 */
		public function log( $message, $level = WC_Log_Levels::WARNING ) {
			if ( null == $this->logger ) {
				$this->logger = new WC_Logger();
			}

			$this->logger->log(
				$level,
				$message,
				array(
					'source' => 'woo-postfinancecheckout',
				)
			);

			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Woocommerce PostFinanceCheckout: ' . $message );
			}
		}


		/**
		 * Add notice.
		 *
		 * @param mixed $message message.
		 * @param mixed $type type.
		 * @return void
		 */
		public function add_notice( $message, $type = 'notice' ) {
			$type = in_array(
				$type,
				array(
					'notice',
					'error',
					'success',
				)
			) ? $type : 'notice';
			wc_add_notice( $message, $type );
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}


		/**
		 * Update attribute options.
		 *
		 * @param mixed $attribute_id attribute id.
		 * @param mixed $send send.
		 * @return void
		 * @throws Exception Exception.
		 */
		protected function update_attribute_options( $attribute_id, $send ) {
			$attribute_options = WC_PostFinanceCheckout_Entity_Attribute_Options::load_by_attribute_id( $attribute_id );
			$attribute_options->set_attribute_id( $attribute_id );
			$attribute_options->set_send( $send );
			$attribute_options->save();
		}

		/**
		 * Woocommerce attribute added.
		 *
		 * @param mixed $attribute_id attribute id.
		 * @param mixed $data data.
		 * @return void
		 * @throws Exception Exception.
		 * 
		 * @see woocommerce_rest_insert_product_attribute
		 * 	 Edit through REST API is handled in woocommerce_rest_insert_product_attribute, as we can not get the rest request object otherwise.
		 */
		public function woocommerce_attribute_added( $attribute_id, $data ) {
			if ( did_action( 'product_page_product_attributes' ) ) {
				// edit through backend form, check POST data.
				$option_set = isset( $_POST['postfinancecheckout_attribute_option_send'] );
				$attribute_option_send = wp_unslash( $option_set ) ?? false;
				$send = wp_verify_nonce( $attribute_option_send ) ? 1 : 0;
				$this->update_attribute_options( $attribute_id, $send );
			}
		}

		/**
		 * Woocommerce attribute updated
		 *
		 * @param mixed $attribute_id attribute id.
		 * @param mixed $data data.
		 * @param mixed $old_slug old slug.
		 * @return void
		 * @throws Exception Exception.
		 */
		public function woocommerce_attribute_updated( $attribute_id, $data, $old_slug ) {
			$this->woocommerce_attribute_added( $attribute_id, $data );
		}

		/**
		 * Woocommerce attribute deleted.
		 *
		 * @param mixed $attribute_id attribute id.
		 * @param mixed $name name.
		 * @param mixed $taxonomy_name taxonomy name.
		 * @return void
		 */
		public function woocommerce_attribute_deleted( $attribute_id, $name, $taxonomy_name ) {
			$attribute_options = WC_PostFinanceCheckout_Entity_Attribute_Options::load_by_attribute_id( $attribute_id );
			$attribute_options->delete();
		}

		/**
		 * Woocommerce rest insert product attribute.
		 *
		 * @param mixed $attribute attribute.
		 * @param mixed $request request.
		 * @param mixed $create create.
		 * @return void
		 * @throws Exception Exception.
		 */
		public function woocommerce_rest_insert_product_attribute( $attribute, $request, $create ) {
			if ( isset( $request['postfinancecheckout_attribute_option_send'] ) ) {
				if ( $request['postfinancecheckout_attribute_option_send'] ) {
					$this->update_attribute_options( $attribute->attribute_id, true );
				} else {
					$this->update_attribute_options( $attribute->attribute_id, false );
				}
			}
		}

		/**
		 * Add cahce no store.
		 *
		 * @param mixed $headers headers.
		 * @return mixed
		 */
		public function add_cache_no_store( $headers ) {
			if ( is_checkout() && isset( $headers['Cache-Control'] ) && stripos( $headers['Cache-Control'], 'no-store' ) === false ) {
				$headers['Cache-Control'] .= ', no-store ';
			}
			return $headers;
		}


		/**
		 * Woocommerce rest prepare product attribute.
		 *
		 * @param mixed $response response.
		 * @param mixed $item item.
		 * @param mixed $request request.
		 * @return mixed
		 */
		public function woocommerce_rest_prepare_product_attribute( $response, $item, $request ) {

			$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
			if ( 'view' == $context || 'edit' == $context ) {
				$data = $response->get_data();
				$attribute_options = WC_PostFinanceCheckout_Entity_Attribute_Options::load_by_attribute_id( $item->attribute_id );
				$data['postfinancecheckout_attribute_option_send'] = $attribute_options->get_id() > 0 && $attribute_options->get_send();
				$response->set_data( $data );
			}
			return $response;
		}

	}
}

WooCommerce_PostFinanceCheckout::instance();
