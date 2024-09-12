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
 * Class WC_PostFinanceCheckout_Admin.
 * WC PostFinanceCheckout Admin class
 *
 * @class WC_PostFinanceCheckout_Admin
 */
class WC_PostFinanceCheckout_Admin {

	/**
	 * The single instance of the class.
	 *
	 * @var WC_PostFinanceCheckout_Admin
	 */
	protected static $instance = null;

	/**
	 * Main WooCommerce PostFinanceCheckout Admin Instance.
	 *
	 * Ensures only one instance of WC PostFinanceCheckout Admin is loaded or can be loaded.
	 *
	 * @return WC_PostFinanceCheckout_Admin - Main instance.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * WC PostFinanceCheckout Admin Constructor.
	 */
	protected function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	private function includes() {
		require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/admin/class-wc-postfinancecheckout-admin-document.php';
		require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/admin/class-wc-postfinancecheckout-admin-transaction.php';
		require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/admin/class-wc-postfinancecheckout-admin-notices.php';
		require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/admin/class-wc-postfinancecheckout-admin-order-completion.php';
		require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/admin/class-wc-postfinancecheckout-admin-order-void.php';
		require_once WC_POSTFINANCECHECKOUT_ABSPATH . 'includes/admin/class-wc-postfinancecheckout-admin-refund.php';
	}

	/**
	 * Initialise the hooks
	 */
	private function init_hooks() {
		add_action(
			'plugins_loaded',
			array(
				$this,
				'loaded',
			),
			0
		);

		add_filter(
			'woocommerce_get_settings_pages',
			array(
				$this,
				'add_settings',
			)
		);

		add_filter(
			'plugin_action_links_' . WC_POSTFINANCECHECKOUT_PLUGIN_BASENAME,
			array(
				$this,
				'plugin_action_links',
			)
		);

		add_filter(
			'woocommerce_hidden_order_itemmeta',
			array(
				$this,
				'hide_postfinancecheckout_order_item_meta',
			),
			10,
			1
		);

		add_action(
			'woocommerce_order_item_add_action_buttons',
			array(
				$this,
				'render_authorized_action_buttons',
			),
			1
		);

		add_action(
			'wp_ajax_woocommerce_postfinancecheckout_update_order',
			array(
				$this,
				'update_order',
			)
		);
		add_action(
			'admin_init',
			array(
				$this,
				'handle_woocommerce_active',
			)
		);

		add_action(
			'woocommerce_admin_order_actions',
			array(
				$this,
				'remove_not_wanted_order_actions',
			),
			10,
			2
		);

		add_action(
			'woocommerce_after_edit_attribute_fields',
			array(
				$this,
				'display_attribute_options_edit',
			),
			10,
			0
		);

		add_action(
			'woocommerce_after_add_attribute_fields',
			array(
				$this,
				'display_attribute_options_add',
			),
			10,
			0
		);
	}

	/**
	 * Handle plugin deactivation
	 */
	public function handle_woocommerce_active() {
		// WooCommerce plugin not activated.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			// Deactivate myself.
			add_action(
				'admin_notices',
				array(
					'WC_PostFinanceCheckout_Admin_Notices',
					'plugin_deactivated',
				)
			);
		}
	}

	/**
	 * Render authorized aciton buttons
	 *
	 * @param WC_Order $order order.
	 */
	public function render_authorized_action_buttons( WC_Order $order ) {
		$gateway = wc_get_payment_gateway_by_order( $order );
		if ( $gateway instanceof WC_PostFinanceCheckout_Gateway ) {
			$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
			if ( $transaction_info->get_state() === \PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED ) {
				if ( WC_PostFinanceCheckout_Entity_Completion_Job::count_running_completion_for_transaction(
					$transaction_info->get_space_id(),
					$transaction_info->get_transaction_id()
				) > 0 || WC_PostFinanceCheckout_Entity_Void_Job::count_running_void_for_transaction(
					$transaction_info->get_space_id(),
					$transaction_info->get_transaction_id()
				) > 0 ) {
					echo '<span class="postfinancecheckout-action-in-progress">' . esc_html__( 'There is a completion/void in progress.', 'woo-postfinancecheckout' ) . '</span>';
					echo '<button type="button" class="button postfinancecheckout-update-order">' . esc_html__( 'Update', 'woo-postfinancecheckout' ) . '</button>';
				} else {
					echo '<button type="button" class="button postfinancecheckout-void-show">' . esc_html__( 'Void', 'woo-postfinancecheckout' ) . '</button>';
					echo '<button type="button" class="button button-primary postfinancecheckout-completion-show">' . esc_html__( 'Completion', 'woo-postfinancecheckout' ) . '</button>';
				}
			}
		}
	}

	/**
	 * Remove unwanted order actions
	 *
	 * @param array $actions actions.
	 * @param WC_Order $order order.
	 * @return array
	 */
	public function remove_not_wanted_order_actions( array $actions, WC_Order $order ) {
		$gateway = wc_get_payment_gateway_by_order( $order );
		if ( $gateway instanceof WC_PostFinanceCheckout_Gateway ) {
			if ( $order->has_status( 'on-hold' ) ) {
				unset( $actions['processing'] );
				unset( $actions['complete'] );
			}
		}
		return $actions;
	}

	/**
	 * Init WooCommerce PostFinanceCheckout when plugins are loaded.
	 */
	public function loaded() {
		add_action(
			'admin_enqueue_scripts',
			array(
				$this,
				'enque_script_and_css',
			)
		);
	}

	/**
	 * Enqueue the script and css files
	 */
	public function enque_script_and_css() {
		$screen = get_current_screen();
		$post_type = $screen ? $screen->post_type : '';
		if ( 'shop_order' == $post_type ) {
			wp_enqueue_style(
				'woo-postfinancecheckout-admin-styles',
				WooCommerce_PostFinanceCheckout::instance()->plugin_url() . '/assets/css/admin.css',
				array(),
				true
			);
			wp_enqueue_script(
				'postfinancecheckout-admin-js',
				WooCommerce_PostFinanceCheckout::instance()->plugin_url() . '/assets/js/admin/management.js',
				array(
					'jquery',
					'wc-admin-meta-boxes',
				),
				true,
				true
			);

			$localize = array(
				'i18n_do_void'  => esc_html__( 'Are you sure you wish to process this void? This action cannot be undone.', 'woo-postfinancecheckout' ),
				'i18n_do_completion' => esc_html__( 'Are you sure you wish to process this completion? This action cannot be undone.', 'woo-postfinancecheckout' ),
			);
			wp_localize_script( 'postfinancecheckout-admin-js', 'postfinancecheckout_admin_js_params', $localize );
		}
	}

	/**
	 * Hide postfinancecheckout order item meta
	 *
	 * @param array $arr array.
	 * @return array
	 */
	public function hide_postfinancecheckout_order_item_meta( $arr ) {
		$arr[] = '_postfinancecheckout_unique_line_item_id';
		$arr[] = '_postfinancecheckout_coupon_discount_line_item_id';
		$arr[] = '_postfinancecheckout_coupon_discount_line_item_key';
		$arr[] = '_postfinancecheckout_coupon_discount_line_item_discounts';
		return $arr;
	}

	/**
	 * Update the order
	 */
	public function update_order() {
		ob_start();

		check_ajax_referer( 'order-item', 'security' );

		if ( ! current_user_can( 'edit_shop_orders' ) ) {// phpcs:ignore
			wp_die( -1 );
		}

		if ( ! isset( $_POST['order_id'] ) ) {
			return;
		}
		$order_id = absint( sanitize_key( wp_unslash( $_POST['order_id'] ) ) );
		$order = WC_Order_Factory::get_order( $order_id );
		try {
			do_action( 'postfinancecheckout_update_running_jobs', $order );
		} catch ( Exception $e ) {
			wp_send_json_error(
				array(
					'error' => $e->getMessage(),
				)
			);
		}
		wp_send_json_success();
	}

	/**
	 * Add WooCommerce PostFinanceCheckout Settings Tab
	 *
	 * @param array $integrations integrations.
	 * @return array
	 */
	public function add_settings( $integrations ) {
		$integrations[] = new WC_PostFinanceCheckout_Admin_Settings_Page();
		return $integrations;
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed $links Plugin Action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=postfinancecheckout' ) . '" aria-label="' .
					esc_html__( 'View Settings', 'woo-postfinancecheckout' ) . '">' . esc_html__( 'Settings', 'woo-postfinancecheckout' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Store attribute options
	 *
	 * @param mixed $product product.
	 * @param mixed $data_storage data storage.
	 */
	public function store_attribute_options( $product, $data_storage ) { //phpcs:ignore
		global $postfinancecheckout_attributes_options;
		if ( ! empty( $postfinancecheckout_attributes_options ) ) {
			$product->add_meta_data( '_postfinancecheckout_attribute_options', $postfinancecheckout_attributes_options, true );
		}
	}

	/**
	 * Display attribute options edit screen
	 */
	public function display_attribute_options_edit() {
		if ( ! isset( $_GET['edit'] ) ) {// phpcs:ignore
			return;
		} else {
			$edit = esc_url_raw( wp_unslash( $_GET['edit'] ) );// phpcs:ignore
		}
		$edit = absint( $edit );
		$checked = false;
		$attribute_options = WC_PostFinanceCheckout_Entity_Attribute_Options::load_by_attribute_id( $edit );
		if ( $attribute_options->get_id() > 0 && $attribute_options->get_send() ) {
			$checked = true;
		}
		echo esc_html(
			'<tr class="form-field form-required">
					<th scope="row" valign="top">
							<label for="postfinancecheckout_attribute_option_send">'
			) . esc_html__( 'Send attribute to PostFinance Checkout.', 'woo-postfinancecheckout' ) . esc_html(
						'</label>
					</th>
						<td>
								<input name="postfinancecheckout_attribute_option_send" id="postfinancecheckout_attribute_option_send" type="checkbox" value="1" '
			) . esc_attr( checked( $checked, true, false ) ) . esc_html(
							'/>
							<p class="description">'
			) . esc_html__( 'Should this product attribute be sent to PostFinance Checkout as line item attribute?', 'woo-postfinancecheckout' ) . esc_html(
							'</p>
						</td>
				</tr>'
			);
	}

	/**
	 * Display attribute options add screen
	 */
	public function display_attribute_options_add() {
		echo esc_html(
			'<div class="form-field">
				<label for="postfinancecheckout_attribute_option_send"><input name="postfinancecheckout_attribute_option_send" id="postfinancecheckout_attribute_option_send" type="checkbox" value="1">'
		) . esc_html__( 'Send attribute to PostFinance Checkout.', 'woo-postfinancecheckout' ) . esc_html(
				'</label>
				<p class="description">'
		) . esc_html__( 'Should this product attribute be sent to PostFinance Checkout as line item attribute?', 'woo-postfinancecheckout' ) .
		esc_html(
				'</p>
			</div>'
		);
	}
}

WC_PostFinanceCheckout_Admin::instance();
