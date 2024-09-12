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
 * Shows the document downloads buttons and handles the downloads in the order overview.
 */
class WC_PostFinanceCheckout_Admin_Document {

	/**
	 * Init WC_PostFinanceCheckout_Admin_Document.
	 *
	 * @return void
	 */
	public static function init() {
		add_action(
			'add_meta_boxes',
			array(
				__CLASS__,
				'add_meta_box',
			),
			40
		);
		add_action(
			'woocommerce_admin_order_actions_end',
			array(
				__CLASS__,
				'add_buttons_to_overview',
			),
			12,
			1
		);
		add_action(
			'admin_init',
			array(
				__CLASS__,
				'download_document',
			)
		);
	}

	/**
	 * Add buttons to overview.
	 *
	 * @param WC_Order $order Wc Order.
	 * @return void
	 */
	public static function add_buttons_to_overview( WC_Order $order ) {
		$method = wc_get_payment_gateway_by_order( $order );
		if ( ! ( $method instanceof WC_PostFinanceCheckout_Gateway ) ) {
			return;
		}
		$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );

		if ( $transaction_info->get_id() === null ) {
			return;
		}
		if ( in_array(
			$transaction_info->get_state(),
			array(
				\PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
				\PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
				\PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE,
			),
			true
		) ) {

			$url   = wp_nonce_url(
				add_query_arg(
					array(
						'post' => $order->get_id(),
						'refer' => 'overview',
						'postfinancecheckout_admin' => 'download_invoice',
					),
					admin_url( 'post.php' )
				),
				'download_invoice',
				'nonce'
			);
			$title = esc_attr( __( 'Invoice', 'woo-postfinancecheckout' ) );
			printf( '<a class="button tips postfinancecheckout-action-button  postfinancecheckout-button-download-invoice" href="%1s" data-tip="%2s">%2s</a>', esc_url( $url ), esc_textarea( $title ), esc_textarea( $title ) );
		}
		if ( $transaction_info->get_state() === \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL ) {
			$url   = wp_nonce_url(
				add_query_arg(
					array(
						'post' => $order->get_id(),
						'refer' => 'overview',
						'postfinancecheckout_admin' => 'download_packing',
					),
					admin_url( 'post.php' )
				),
				'download_packing',
				'nonce'
			);
			$title = esc_attr( __( 'Packing Slip', 'woo-postfinancecheckout' ) );
			printf( '<a class="button tips postfinancecheckout-action-button postfinancecheckout-button-download-packingslip" href="%1s" data-tip="%2s">%2s</a>', esc_url( $url ), esc_textarea( $title ), esc_textarea( $title ) );
		}
	}

	/**
	 * Add WC Meta boxes.
	 *
	 * @see: https://woo.com/document/high-performance-order-storage/#section-8
	 */
	public static function add_meta_box() {
		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' )
			&& wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';
		add_meta_box(
			'woocommerce-order-postfinancecheckout-documents',
			__( 'PostFinance Checkout Documents', 'woo-postfinancecheckout' ),
			array(
				__CLASS__,
				'output',
			),
			$screen,
			'side',
			'default'
		);
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post|WP_Order $post_or_order_object The post or object.
	 * This object is provided by woocommerce when using its screen.
	 */
	public static function output( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		$method = wc_get_payment_gateway_by_order( $order );
		if ( ! ( $method instanceof WC_PostFinanceCheckout_Gateway ) ) {
			return;
		}
		$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
		if ( $transaction_info->get_id() === null ) {
			return;
		}
		if ( in_array(
			$transaction_info->get_state(),
			array(
				\PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED,
				\PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL,
				\PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE,
			),
			true
		) ) {

			?>
<ul class="woocommerce-order-admin-postfinancecheckout-downloads">
	<li><a
		href="
			<?php

			echo esc_url(
				wp_nonce_url(
					add_query_arg(
						array(
							'post' => $order->get_id(),
							'refer' => 'edit',
							'postfinancecheckout_admin' => 'download_invoice',
						),
						admin_url( 'post.php' )
					),
					'download_invoice',
					'nonce'
				)
			);
			?>
			"
		class="postfinancecheckout-admin-download postfinancecheckout-admin-download-invoice button"><?php esc_attr_e( 'Invoice', 'woo-postfinancecheckout' ); ?></a></li>

					<?php if ( $transaction_info->get_state() === \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL ) : ?>
						<li><a
		href="
						<?php

						echo esc_url(
							wp_nonce_url(
								add_query_arg(
									array(
										'post'  => $order->get_id(),
										'refer' => 'edit',
										'postfinancecheckout_admin' => 'download_packing',
									),
									admin_url( 'post.php' ),
									true
								),
								'download_packing',
								'nonce'
							)
						);
						?>
				"
		class="postfinancecheckout-admin-download postfinancecheckout-admin-download-packingslip button"><?php esc_attr_e( 'Packing Slip', 'woo-postfinancecheckout' ); ?></a></li>
					<?php endif; ?>
					</ul>
			<?php
		}
	}

	/**
	 * Admin pdf actions callback.
	 * Within admin by default only administrator and shop managers have permission to view, create, cancel invoice.
	 */
	public static function download_document() {
		if ( ! self::is_download_request() ) {
			return;
		}

		// sanitize data and verify nonce.
		$action = isset( $_GET['postfinancecheckout_admin'] ) ? sanitize_key( wp_unslash( $_GET['postfinancecheckout_admin'] ) ) : null;
		$nonce  = isset( $_GET['nonce'] ) ? sanitize_key( wp_unslash( $_GET['nonce'] ) ) : null;
		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die( 'Invalid request.' );
		}

		// validate allowed user roles.
		$user = wp_get_current_user();
		$allowed_roles = apply_filters(
			'wc_postfinancecheckout_allowed_roles_to_download_documents',
			array(
				'administrator',
				'shop_manager',
			)
		);
		if ( ! array_intersect( $allowed_roles, $user->roles ) ) {
			wp_die( 'Access denied' );
		}

		$order_id = isset( $_GET['post'] ) ? intval( wp_unslash( $_GET['post'] ) ) : null;
		try {
			switch ( $action ) {
				case 'download_invoice':
					WC_PostFinanceCheckout_Download_Helper::download_invoice( $order_id );
					break;
				case 'download_packing':
					WC_PostFinanceCheckout_Download_Helper::download_packing_slip( $order_id );
					break;
			}
		} catch ( Exception $e ) {
			$message = $e->getMessage();
			$cleaned = preg_replace( '/^\[[A-Fa-f\d\-]+\] /', '', $message );
			wp_die( esc_html__( 'Could not fetch the document from PostFinance Checkout.', 'woo-postfinancecheckout' ) . ' ' . esc_textarea( $cleaned ) );
		}

		$refer = isset( $_GET['refer'] ) ? sanitize_key( wp_unslash( $_GET['refer'] ) ) : 0;

		if ( 'edit' === $refer ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'post' => $order_id,
						'action' => 'edit',
					),
					admin_url( 'post.php' )
				)
			);
		} else {
			wp_safe_redirect(
				add_query_arg(
					array(
						'post_type' => 'shop_order',
					),
					admin_url( 'edit.php' )
				)
			);
		}
		exit();
	}

	/**
	 * Check if request is PDF action.
	 *
	 * @return bool
	 */
	private static function is_download_request() {
		return ( isset( $_GET['post'] ) && isset( $_GET['postfinancecheckout_admin'] ) && isset( $_GET['nonce'] ) ); // phpcs:ignore
	}
}

WC_PostFinanceCheckout_Admin_Document::init();
