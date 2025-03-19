<?php
/**
 * Plugin Name: PostFinance Checkout
 * Plugin URI: https://wordpress.org/plugins/woo-postfinance-checkout
 * Description: Process WooCommerce payments with PostFinance Checkout.
 * Version: 3.3.7
 * Author: postfinancecheckout AG
 * Author URI: https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html
 * Text Domain: postfinancecheckout
 * Domain Path: /languages/
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 8.0.0
 * WC tested up to 9.7.0
 * License: Apache-2.0
 * License URI: http://www.apache.org/licenses/LICENSE-2.0
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

		const POSTFINANCECHECKOUT_CK_SPACE_ID = 'wc_postfinancecheckout_space_id';
		const POSTFINANCECHECKOUT_CK_SPACE_VIEW_ID = 'wc_postfinancecheckout_space_view_id';
		const POSTFINANCECHECKOUT_CK_APP_USER_ID = 'wc_postfinancecheckout_application_user_id';
		const POSTFINANCECHECKOUT_CK_APP_USER_KEY = 'wc_postfinancecheckout_application_user_key';
		const POSTFINANCECHECKOUT_CK_CUSTOMER_INVOICE = 'wc_postfinancecheckout_customer_invoice';
		const POSTFINANCECHECKOUT_CK_CUSTOMER_PACKING = 'wc_postfinancecheckout_customer_packing';
		const POSTFINANCECHECKOUT_CK_SHOP_EMAIL = 'wc_postfinancecheckout_shop_email';
		const POSTFINANCECHECKOUT_CK_INTEGRATION = 'wc_postfinancecheckout_integration';
		const POSTFINANCECHECKOUT_CK_ORDER_REFERENCE = 'wc_postfinancecheckout_order_reference';
		const POSTFINANCECHECKOUT_CK_ENFORCE_CONSISTENCY = 'wc_postfinancecheckout_enforce_consistency';
		const POSTFINANCECHECKOUT_UPGRADE_VERSION = '3.1.1';
		const WC_MAXIMUM_VERSION = '9.7.0';

		/**
		 * WooCommerce PostFinanceCheckout version.
		 *
		 * @var string
		 */
		private $version = '3.3.7';

		/**
		 * The single instance of the class.
		 *
		 * @var WooCommerce_PostFinanceCheckout
		 */
		protected static $instance = null;

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
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
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
			$this->define( 'WC_POSTFINANCECHECKOUT_ABSPATH', __DIR__ . '/' );
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
			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-autoloader.php';
			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'postfinancecheckout-sdk/autoload.php';

			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-migration.php';
			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-email.php';
			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-return-handler.php';
			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-webhook-handler.php';
			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-unique-id.php';
			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-customer-document.php';
			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-cron.php';
			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/class-wc-postfinancecheckout-order-status-adapter.php';
			require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/packages/coupon/class-wc-postfinancecheckout-packages-coupon-discount.php';

			if ( is_admin() ) {
				require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/admin/class-wc-postfinancecheckout-admin.php';
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
					'WooCommerce_PostFinanceCheckout',
					'migrate_plugin_data_on_activation'
				)
			);

			register_activation_hook(
				__FILE__,
				array(
					$this,
					'plugin_activate',
				)
			);
			register_deactivation_hook(
				__FILE__,
				array(
					$this,
					'plugin_deactivate',
				)
			);
			register_uninstall_hook(
				__FILE__,
				array(
					'WooCommerce_PostFinanceCheckout',
					'plugin_uninstall',
				)
			);
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

			// Hook to run migration after plugin update.
			add_action(
				'upgrader_process_complete',
				array(
					$this,
					'migrate_plugin_data_after_update',
				),
				10,
				2
			);

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

			// Endpoints needed for supporting Woocommerce Blocks checkout block.
			add_action(
				'wp_ajax_is_payment_method_available',
				array(
					'WC_PostFinanceCheckout_Blocks_Support',
					'is_payment_method_available',
				)
			);
			add_action(
				'wp_ajax_nopriv_is_payment_method_available',
				array(
					'WC_PostFinanceCheckout_Blocks_Support',
					'is_payment_method_available',
				)
			);
			add_action(
				'wp_ajax_get_payment_methods',
				array(
					'WC_PostFinanceCheckout_Blocks_Support',
					'get_payment_methods_json',
				)
			);
			add_action(
				'wp_ajax_nopriv_get_payment_methods',
				array(
					'WC_PostFinanceCheckout_Blocks_Support',
					'get_payment_methods_json',
				)
			);
			add_action(
				'woocommerce_blocks_enqueue_checkout_block_scripts_after',
				array(
					'WC_PostFinanceCheckout_Blocks_Support',
					'enqueue_portal_scripts',
				)
			);
			add_action(
				'woocommerce_rest_checkout_process_payment_with_context',
				array(
					'WC_PostFinanceCheckout_Blocks_Support',
					'process_payment',
				),
				10,
				2
			);

			add_action(
				'wp_ajax_postfinancecheckout_custom_order_status_save_changes',
				array(
					$this,
					'custom_order_status_save_changes',
				),
				20,
				2
			);

			add_action(
				'wp_ajax_postfinancecheckout_custom_order_status_delete',
				array(
					$this,
					'custom_order_status_delete',
				),
				20,
				2
			);
			// Clear the permalinks after the post type has been registered.
		}

		/**
		 * Activation hook.
		 * Fired when the plugin is activated.
		 */
		public static function plugin_activate() {
			// Clear the permalinks after the post type has been registered.
			flush_rewrite_rules();
		}

		/**
		 * Deactivation hook.
		 * Fired when the plugin is deactivated.
		 */
		public static function plugin_deactivate() {
			// Get the plugin version.
			$old_plugin_prefix = WC_PostFinanceCheckout_Migration::POSTFINANCECHECKOUT_DEPRECATED_PLUGIN_PREFIX;
			$plugin_current_version = self::get_installed_plugin_version( $old_plugin_prefix . 'postfinancecheckout' ); // The slug of the old plugin.

			// Check if the plugin version is lower than 3.1.0.
			if ( version_compare( $plugin_current_version, self::POSTFINANCECHECKOUT_UPGRADE_VERSION, '<' ) ) {
				// Start output buffering to prevent "headers already sent" errors.
				ob_start();
			}

			// Hook to run migration after plugin update.
			add_action(
				'upgrader_process_complete',
				array(
					'WooCommerce_PostFinanceCheckout',
					'migrate_plugin_data_after_update',
				),
				10,
				2
			);

			// Clear the permalinks to remove our post type's rules from the database.
			flush_rewrite_rules();
		}

		/**
		 * Uninstall hook.
		 * Fired when the plugin is uninstalled.
		 */
		public static function plugin_uninstall() {
			// code to run on plugin uninstall.
			// delete the registered options.
		}

		/**
		 * Function to get the installed version of a plugin.
		 *
		 * @param string $plugin_slug The slug of the plugin.
		 * @return string|null The version of the plugin or null if not found.
		 */
		public static function get_installed_plugin_version( $plugin_slug ) {
			if (!function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$all_plugins = get_plugins();
			foreach ( $all_plugins as $plugin_path => $plugin_info ) {
				if ( strpos( $plugin_path, $plugin_slug ) !== false ) {
					return $plugin_info[ 'Version' ];
				}
			}

			return null;
		}

		/**
		 * Security check in the thank you page.
		 *
		 * Note: If for some reason order status is still pending, it will redirect you to the payment form.
		 *
		 * @param int $order_id Order id.
		 */
		public function secure_redirect_order_confirmed( $order_id ) {
			$order = wc_get_order( $order_id );
			$wc_service_transaction = WC_PostFinanceCheckout_Service_Transaction::instance();
			$sdk_service_transaction = new \PostFinanceCheckout\Sdk\Service\TransactionService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
			$wc_transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order_id );

			if ( property_exists( $wc_transaction_info, 'get_transaction_id' ) ) {
				$state = $sdk_service_transaction->read( get_option( self::POSTFINANCECHECKOUT_CK_SPACE_ID ), $wc_transaction_info->get_transaction_id() )->getState();
				if ( \PostFinanceCheckout\Sdk\Model\TransactionState::CONFIRMED === $state ) {
					wp_redirect($wc_service_transaction->get_payment_page_url(get_option(self::POSTFINANCECHECKOUT_CK_SPACE_ID), $wc_transaction_info->get_transaction_id())); //phpcs:ignore
					exit;
				}
			}
		}

		/**
		 * Migrates settings from the old plugin version to the new version 4.0.0.
		 *
		 * This function checks if the settings from the old plugin version exist.
		 * If they do, it transfers these settings to the new plugin's options
		 * and deletes the old settings from the database.
		 *
		 * This ensures that users retain their configurations when updating
		 * from the old plugin version to the new version.
		 *
		 * @return void
		 */
		public static function migrate_plugin_data_on_activation() {
			$old_option_prefix = WC_PostFinanceCheckout_Migration::POSTFINANCECHECKOUT_DEPRECATED_TABLE_PREFIX;
			$old_plugin_prefix = WC_PostFinanceCheckout_Migration::POSTFINANCECHECKOUT_DEPRECATED_PLUGIN_PREFIX;

			$plugin_slug = $old_plugin_prefix . 'postfinancecheckout'; // The slug of the old plugin.
			$installed_version = self::get_installed_plugin_version( $plugin_slug );

			/**
			 * Check if the installed version is 3.1.1 or higher.
			 * If the version is 3.1.1 or higher, do not run the migration.
			 */
			if ( version_compare( $installed_version, self::POSTFINANCECHECKOUT_UPGRADE_VERSION, '>=' ) ) {
				return;
			}

			global $wpdb;
			$options_to_migrate = [
				$old_option_prefix . WC_PostFinanceCheckout_Migration::POSTFINANCECHECKOUT_CK_DB_VERSION,
				$old_option_prefix . WC_PostFinanceCheckout_Service_Manual_Task::POSTFINANCECHECKOUT_CONFIG_KEY,
				$old_option_prefix . self::POSTFINANCECHECKOUT_CK_SPACE_ID,
				$old_option_prefix . self::POSTFINANCECHECKOUT_CK_SPACE_VIEW_ID,
				$old_option_prefix . self::POSTFINANCECHECKOUT_CK_APP_USER_ID,
				$old_option_prefix . self::POSTFINANCECHECKOUT_CK_APP_USER_KEY,
				$old_option_prefix . self::POSTFINANCECHECKOUT_CK_CUSTOMER_INVOICE,
				$old_option_prefix . self::POSTFINANCECHECKOUT_CK_CUSTOMER_PACKING,
				$old_option_prefix . self::POSTFINANCECHECKOUT_CK_SHOP_EMAIL,
				$old_option_prefix . self::POSTFINANCECHECKOUT_CK_INTEGRATION,
				$old_option_prefix . self::POSTFINANCECHECKOUT_CK_ORDER_REFERENCE,
				$old_option_prefix . self::POSTFINANCECHECKOUT_CK_ENFORCE_CONSISTENCY,
				$old_option_prefix . self::WC_MAXIMUM_VERSION,
			];

			// If the old plugin options exist, perform the migration.
			foreach ( $options_to_migrate as $option_name ) {
				$option_value = get_option( $option_name );
				if ( false !== $option_value ) {
					// Rename the options to the new prefix 'postfinancecheckout_'.
					$new_option_name = str_replace( $old_option_prefix . 'postfinancecheckout_', 'postfinancecheckout_', $option_name );
					if ( false !== get_option( $new_option_name ) ) {
						// Update the option if it already exists.
						update_option( $new_option_name, $option_value );
					} else {
						// Add the option if it doesn't exist.
						add_option( $new_option_name, $option_value );
					}
					// Delete the old option.
					//delete_option( $option_name );.
				}
			}
		}

		/**
		 * Function to migrate plugin data after update
		 * This function is triggered after the plugin is updated
		 *
		 * @param object $upgrader_object The upgrader object.
		 * @param array $options The options array.
		 */
		public static function migrate_plugin_data_after_update( $upgrader_object, $options ) {
			// Check if the plugin was just updated.
			if ( 'update' == $options['action'] && 'plugin' == $options['type'] ) {
				$plugin_basename = plugin_basename( __FILE__ );
				foreach ( $options['plugins'] as $plugin ) {
					if ( $plugin == $plugin_basename ) {
						self::migrate_plugin_data_on_activation();
						break;
					}
				}
			}
		}


		/**
		 * Load Localization files.
		 *
		 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
		 *
		 * Locales found in:
		 * - WP_LANG_DIR/woo-postfinancecheckout/woo-postfinancecheckout-LOCALE.mo
		 */
		public function load_plugin_textdomain() {
			$locale = is_admin() && function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
			$locale = apply_filters( 'plugin_locale', $locale, 'woo-postfinancecheckout' );

			load_textdomain( 'woo-postfinancecheckout', WP_LANG_DIR . '/woo-postfinancecheckout/woo-postfinancecheckout' . $locale . '.mo' );
			load_plugin_textdomain( 'woo-postfinancecheckout', false, plugin_basename( __DIR__ ) . '/languages' );
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
			add_filter(
				'woocommerce_cart_needs_payment',
				function($value, $object) {
					return true;
				}, 10, 2
			);
			add_filter(
			 	'woocommerce_order_needs_payment',
			 	function($value, $order) {
					$order_data = $order->get_data();
					if (substr_count($order_data['payment_method'], "postfinancecheckout")) {
						// If the order is using our payment method, we want to process it
						// even if the value of the transaction is 0, which woocommerce by default
						// process it without payment gateway.
						return true;
					}
					return $value;
				}, 10, 2
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

			// woocommerce_after_checkout_form is used by the legacy checkout.
			add_action(
				'woocommerce_after_checkout_form',
				array(
					$this,
					'show_checkout_error_msg',
				),
				5,
				0
			);

			// pre_render_block is used by the new Woocomerce Blocks.
			add_filter(
				'pre_render_block',
				array(
					$this,
					'pre_render_block',
				),
				5,
				2
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

			add_action(
				'before_woocommerce_init',
				function () {
					if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
						\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
					}
				}
			);

			add_filter('the_content', function($content) {
				if (is_checkout()) {
					// When in checkout, we inject the list of payment methods in the HTML.
					// The goal here is to speed up the process of registering the payment methods.
					$payment_methods = WC_PostFinanceCheckout_Blocks_Support::get_payment_methods();
					$json_data = json_encode( $payment_methods );
					$content .= '<div id="postfinancecheckout-payment-methods" data-json="' . esc_attr( $json_data ) . '"></div>';
				}

				return $content;
			});
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
					'label_count' => _n_noop( 'PostFinance Checkout Processing <span class="count">(%s)</span>', 'PostFinance Checkout Processing <span class="count">(%s)</span>', 'woo-postfinancecheckout' ),
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
					'label_count' => _n_noop( 'Waiting <span class="count">(%s)</span>', 'Waiting <span class="count">(%s)</span>', 'woo-postfinancecheckout' ),
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
					'label_count' => _n_noop( 'Manual Decision <span class="count">(%s)</span>', 'Manual Decision <span class="count">(%s)</span>', 'woo-postfinancecheckout' ),
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
		public function valid_order_statuses_for_payment( $statuses, $order = null ) { //phpcs:ignore
			$statuses[] = 'postfi-redirected';

			return $statuses;
		}

		/**
		 * Handles AJAX request to save order status changes.
		 *
		 * This method validates the nonce for security, checks if the required fields are present,
		 * and returns either an error response or a success response with updated order statuses.
		 *
		 * @return void Outputs a JSON response and terminates execution.
		 */
		public function custom_order_status_save_changes() {
			// Validate nonce for security.
			if ( ! isset( $_POST['postfinancecheckout_order_statuses_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['postfinancecheckout_order_statuses_nonce'] ) ), 'postfinancecheckout_order_statuses_nonce' ) ) {  //phpcs:ignore
				wp_send_json_error( 'Invalid request: missing nonce value', 403 );
			}

			// Ensure required fields are present.
			if ( ! isset( $_POST['changes'] ) || empty( $_POST['changes'] ) ) {
				wp_send_json_error( 'missing_fields' );
				wp_die();
			}

			// Sanitize input properly.
			$changes = sanitize_text_field( wp_unslash( $_POST['changes'] ) );

			// Get the first key inside the 'changes' array.
			$first_key = key( $changes );
			$data = $_POST['changes'][$first_key];

			if ( isset( $data['key'] ) ) {
				$label = str_replace( ['-', '_'], ' ', sanitize_text_field( $data['key'] ) );
				
				// Normalize the key by replacing spaces and dashes with underscores.
				$sanitized_key_underscore = str_replace( [' ', '-'], '_', WC_PostFinanceCheckout_Order_Status_Adapter::POSTFINANCECHECKOUT_CUSTOM_ORDER_STATUS_PREFIX . $first_key );
				$sanitized_key_hyphen = str_replace( [' ', '_'], '-', $first_key );

				// Construct the full option name with the required prefix.
				$custom_order_value = 'wc-' . $sanitized_key_hyphen;
				$custom_order_name = $sanitized_key_underscore;
				// Save to wp_options.
				update_option( $custom_order_name, $custom_order_value );

				register_post_status( $custom_order_value, array(
					'label' => $label,
					'public' => true,
					'show_in_admin_status_list' => true,
					'show_in_admin_all_list' => true,
					'exclude_from_search' => false,
					/* translators: %s: replaces string */
					'label_count' => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>' ) // phpcs:ignore
				) );

				// Send a success response with the saved order status details.
				wp_send_json_success([
					'message'      => 'Success',
					'order_status' => [
						'key'   => $custom_order_value,  // The full key with the prefix.
						'label' => ucfirst( $label ), // As received from the form data.
						'type'  => __( 'custom' , 'woo-postfinancecheckout' )
					]
				]);
			} else {
				wp_send_json_error( 'invalid_data' );
			}
		}

		/**
		 * Handles AJAX request to delete a custom order status.
		 *
		 * This method verifies the nonce for security, checks if the required key is provided,
		 * deletes the corresponding order status from the WordPress options, and returns a success
		 * or error response in JSON format.
		 *
		 * @return void Outputs a JSON response and terminates execution.
		 */
		public function custom_order_status_delete() {
			global $wpdb;

			// Verify nonce.
			if ( ! isset( $_POST['postfinancecheckout_order_statuses_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['postfinancecheckout_order_statuses_nonce'] ) ), 'postfinancecheckout_order_statuses_nonce' ) ) {
				wp_send_json_error( 'Invalid request: missing nonce value', 403 );
			}

			// Check if key is provided.
			if ( empty( $_POST['key'] ) ) {
				wp_send_json_error( 'Missing status key' );
			}

			$sanitized_key = sanitize_text_field( wp_unslash( $_POST['key'] ) );

			// Check if this order status is being used in wp_options with key prefix "postfinancecheckout_order_status_mapping_".
			$like_pattern = WC_PostFinanceCheckout_Order_Status_Adapter::POSTFINANCECHECKOUT_ORDER_STATUS_MAPPING_PREFIX . '%';
			$is_used = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value = %s", $like_pattern, $sanitized_key ) );

			if ( $is_used > 0 ) {
				// The status is in use, so do not delete it.
				wp_send_json_error( ['message' => __( 'Cannot delete: this status is currently in use.', 'woo-postfinancecheckout' ), 'key' => ucfirst( $sanitized_key )], 400 );
			}

			 // Get the custom order status in wp_options with key prefix "postfinancecheckout_order_status_mapping_".
			$like_pattern = WC_PostFinanceCheckout_Order_Status_Adapter::POSTFINANCECHECKOUT_CUSTOM_ORDER_STATUS_PREFIX . '%';
			$custom_order_status_name = $wpdb->get_var( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value = %s", $like_pattern, $sanitized_key ) );

			// Remove from wp_options.
			delete_option( $custom_order_status_name );

			// Return success response.
			wp_send_json_success( ['message' => __( 'Deleted successfully', 'woo-postfinancecheckout' ), 'key' => ucfirst( $sanitized_key )] );
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
		public function set_js_async( $tag, $handle, $src ) { //phpcs:ignore
			$async_script_handles = array( 'postfinancecheckout-device-id-js' );
			foreach ( $async_script_handles as $async_handle ) {
				if ( $async_handle === $handle ) {
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
				$space_id = get_option( self::POSTFINANCECHECKOUT_CK_SPACE_ID );
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
			if ( is_null( $order ) ) {
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
		public function valid_order_status_for_completion( $statuses, WC_Order $order = null ) { //phpcs:ignore
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
		public function before_calculate_totals( $cart ) { //phpcs:ignore
			$GLOBALS['postfinancecheckout_calculating'] = true;
		}

		/**
		 * After calculate totals.
		 *
		 * @param mixed $cart cart.
		 * @return void
		 */
		public function after_calculate_totals( $cart ) { //phpcs:ignore
			unset( $GLOBALS['postfinancecheckout_calculating'] );
		}


		/**
		 * Add gateways.
		 *
		 * @param mixed $methods methods.
		 * @return mixed
		 */
		public function add_gateways( $methods ) {
			$space_id = get_option( self::POSTFINANCECHECKOUT_CK_SPACE_ID );
			$method_configurations = WC_PostFinanceCheckout_Entity_Method_Configuration::load_by_states_and_space_id(
				$space_id,
				array(
					WC_PostFinanceCheckout_Entity_Method_Configuration::POSTFINANCECHECKOUT_STATE_ACTIVE,
					WC_PostFinanceCheckout_Entity_Method_Configuration::POSTFINANCECHECKOUT_STATE_INACTIVE,
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
		public function modify_form_fields_args( $arguments, $key, $value = null ) { //phpcs:ignore
			if ( 'billing_company' === $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'billing_email' === $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'billing_phone' === $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'billing_first_name' === $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'billing_last_name' === $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'shipping_first_name' === $key ) {
				$arguments['class'][] = 'address-field';
			}
			if ( 'shipping_last_name' === $key ) {
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

			if ( wc_ship_to_billing_address_only() || ! isset( $post_data['ship_to_different_address'] ) || '0' === $post_data['ship_to_different_address'] ) {
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
		 * @return true
		 */
		public function register_checkout_error_msg() {
			if ( ! isset( WC()->session ) ) return false;
			$msg = WC()->session->get( 'postfinancecheckout_failure_message', null );
			if ( ! empty( $msg ) ) {
				$this->add_notice( (string) $msg, 'error' );
				WC()->session->set( 'postfinancecheckout_failure_message', null );
			}

			return ! empty( $msg );
		}

		/**
		 * Show checkout error msg.
		 *
		 * @return void
		 */
		public function show_checkout_error_msg() {
			if ( $this->register_checkout_error_msg() ) {
				wc_print_notices();
			}
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param string      $name name.
		 * @param string|bool $value value.
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
			if ( is_null( $this->logger ) ) {
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
				error_log( 'Woocommerce PostFinanceCheckout: ' . $message ); //phpcs:ignore
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
				),
				true
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
		 * Edit through REST API is handled in woocommerce_rest_insert_product_attribute, as we can not get the rest request object otherwise.
		 */
		public function woocommerce_attribute_added( $attribute_id, $data ) { //phpcs:ignore
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
		public function woocommerce_attribute_updated( $attribute_id, $data, $old_slug ) { //phpcs:ignore
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
		public function woocommerce_attribute_deleted( $attribute_id, $name, $taxonomy_name ) { //phpcs:ignore
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
		public function woocommerce_rest_insert_product_attribute( $attribute, $request, $create ) { //phpcs:ignore
			if ( isset( $request['postfinancecheckout_attribute_option_send'] ) ) {
				if ( $request['postfinancecheckout_attribute_option_send'] ) {
					$this->update_attribute_options( $attribute->attribute_id, true );
				} else {
					$this->update_attribute_options( $attribute->attribute_id, false );
				}
			}
		}

		/**
		 * Add cache no store.
		 *
		 * @param mixed $headers headers.
		 * @return mixed
		 */
		public function add_cache_no_store( $headers ) {
			if ( class_exists( 'WooCommerce' ) ) {
				if ( is_checkout() && isset( $headers['Cache-Control'] ) && stripos( $headers['Cache-Control'], 'no-store' ) === false ) {
					$headers['Cache-Control'] .= ', no-store ';
				}
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
			if ( 'view' === $context || 'edit' === $context ) {
				$data = $response->get_data();
				$attribute_options = WC_PostFinanceCheckout_Entity_Attribute_Options::load_by_attribute_id( $item->attribute_id );
				$data['postfinancecheckout_attribute_option_send'] = $attribute_options->get_id() > 0 && $attribute_options->get_send();
				$response->set_data( $data );
			}
			return $response;
		}

		/**
		 * Displays error messages, if there are, when rendering the woocommerce/checkout block.
		 *
		 * @return void
		 */
		public function pre_render_block() {
			$args = func_get_args();
			if ( count( $args )
				&& ! empty( $args[1] )
				&& ! empty( $args[1]['blockName'] )
				&& 'woocommerce/checkout' === wp_unslash( $args[1]['blockName'] )
			) {
				$this->show_checkout_error_msg();
			}
		}
	}

	add_action( 'woocommerce_blocks_loaded', 'WC_PostFinanceCheckout_Blocks_Support' );

	function WC_PostFinanceCheckout_Blocks_Support() { //phpcs:ignore
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
			require_once __DIR__ . '/includes/class-wc-postfinancecheckout-blocks-support.php';

			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function ( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
					$payment_method_registry->register( new WC_PostFinanceCheckout_Blocks_Support() );
				},
			);
		}
	}
}

WooCommerce_PostFinanceCheckout::instance();
