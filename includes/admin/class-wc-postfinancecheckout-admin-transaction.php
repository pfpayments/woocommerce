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
 * Class WC_PostFinanceCheckout_Admin_Transaction.
 * WC PostFinanceCheckout Admin Transaction class
 *
 * @class WC_PostFinanceCheckout_Admin_Transaction
 */
class WC_PostFinanceCheckout_Admin_Transaction {

	/**
	 * Init
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
	}

	/**
	 * Add WC Meta boxes.
	 *
	 * @see: https://woo.com/document/high-performance-order-storage/#section-8
	 */
	public static function add_meta_box() {
		if ( empty( $post ) || ! ( $post instanceof WP_Post ) || empty( $post->ID ) || 'shop_order' != $post->post_type ) {
			return;
		}
		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' )
			&& wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';
		add_meta_box(
			'woocommerce-order-postfinancecheckout-transaction',
			__( 'PostFinance Checkout Transaction', 'woocommerce-postfinancecheckout' ),
			array(
				__CLASS__,
				'output',
			),
			$screen,
			'normal',
			'default'
		);
	}

	/**
	 * Output the metabox.
	 *
	 * @param WP_Post|WP_Order $post_or_order_object the post or order object.
	 * This object is provided by woocommerce when using its screen.
	 */
	public static function output( $post_or_order_object ) {
		$order = ( $post_or_order_object instanceof WP_Post ) ? wc_get_order( $post_or_order_object->ID ) : $post_or_order_object;

		$method = wc_get_payment_gateway_by_order( $order );
		if ( ! ( $method instanceof WC_PostFinanceCheckout_Gateway ) ) {
			return;
		}
		$helper = WC_PostFinanceCheckout_Helper::instance();

		$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_order_id( $order->get_id() );
		if ( $transaction_info->get_id() == null ) {
			$transaction_info = WC_PostFinanceCheckout_Entity_Transaction_Info::load_newest_by_mapped_order_id( $order->get_id() );
			if ( $transaction_info->get_id() == null ) {
				return;
			}
		}
		$labels_by_group = self::get_grouped_charge_attempt_labels( $transaction_info );
		?>
<div class="order-postfinancecheckout-transaction-metabox wc-metaboxes-wrapper">
	<div class="postfinancecheckout-transaction-data-column-container">
		<div class="postfinancecheckout-transaction-column">
			<p>
				<strong><?php esc_html_e( 'General Details', 'woo-postfinancecheckout' ); ?></strong>
			</p>
			<table class="form-list" style="margin-bottom: 20px;">
				<tbody>
					<tr>
						<td class="label"><label><?php echo esc_html__( 'Payment Method', 'woo-postfinancecheckout' ); ?></label></td>
						<td class="value"><strong><?php echo esc_html( $method->get_payment_method_configuration()->get_configuration_name() ); ?></strong>
						</td>
					</tr>
			<?php if ( ! empty( $transaction_info->get_image() ) ) : ?>
					<tr>
						<td class="label"></td>
						<td class="value"><img
							src="<?php echo esc_url( $helper->get_resource_url( $transaction_info->get_image_base(), $transaction_info->get_image(), $transaction_info->get_language(), $transaction_info->get_space_id(), $transaction_info->get_space_view_id() ) ); ?>"
							width="50" /><br /></td>
					</tr>
			<?php endif; ?>
					<tr>
						<td class="label"><label><?php esc_html_e( 'Transaction State', 'woo-postfinancecheckout' ); ?></label></td>
						<?php // phpcs:ignore ?>
						<td class="value"><strong><?php echo esc_html( self::get_transaction_state( $transaction_info ) ); ?></strong></td>
					</tr>

			<?php if ( $transaction_info->get_order_id() != null ) : ?>
					<tr>
						<td class="label"><label><?php esc_html_e( 'Merchant Reference', 'woo-postfinancecheckout' ); ?></label></td>
						<?php // phpcs:ignore ?>
						<td class="value"><strong><?php echo esc_html( $transaction_info->get_order_id() ); ?></strong></td>
					</tr>
			<?php endif; ?>

			<?php if ( $transaction_info->get_failure_reason() != null ) : ?>
					<tr>
						<td class="label"><label><?php esc_html_e( 'Failure Reason', 'woo-postfinancecheckout' ); ?></label></td>
						<?php // phpcs:ignore ?>
						<td class="value"><strong><?php echo esc_html( $transaction_info->get_failure_reason() ); ?></strong></td>
					</tr>
			<?php endif; ?>
					<tr>
						<td class="label"><label><?php esc_html_e( 'Authorization Amount', 'woo-postfinancecheckout' ); ?></label></td>
						<?php // phpcs:ignore ?>
						<td class="value"><strong><?php echo (float) wc_price( $transaction_info->get_authorization_amount(), array( 'currency' => $transaction_info->get_currency() ) ); ?></strong></td>
					</tr>
					<tr>
						<td class="label"><label><?php esc_html_e( 'Transaction', 'woo-postfinancecheckout' ); ?></label></td>
						<td class="value"><strong> <a
								href="<?php printf( '%s', esc_url( self::get_transaction_url( $transaction_info ) ) ); ?>"
								target="_blank">
						<?php esc_html_e( 'View in PostFinance Checkout', 'woo-postfinancecheckout' ); ?>
					</a>
						</strong></td>
					</tr>

				</tbody>
			</table>
		</div>


		<?php if ( ! empty( $labels_by_group ) ) : ?>
			<?php foreach ( $labels_by_group as $group ) : ?>
	<div class="postfinancecheckout-transaction-column">
			<div class="postfinancecheckout-payment-label-container"
				id="postfinancecheckout-payment-label-container-<?php echo esc_attr( $group['group']->getId() ); ?>">
				<p class="postfinancecheckout-payment-label-group">
					<strong><?php echo esc_html( $helper->translate( $group['group']->getName() ) ); ?></strong>
				</p>
				<table class="form-list" style="margin-bottom: 20px;">
					<tbody>
					<?php foreach ( $group['labels'] as $label ) : ?>
						<tr>
							<td class="label"><label><?php echo esc_html( $helper->translate( $label['descriptor']->getName() ) ); ?></label></td>
							<td class="value"><strong><?php echo esc_html( $label['value'] ); ?></strong></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				</table>
			</div>
		</div>

	<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>
		<?php
	}

	/**
	 * Returns the translated name of the transaction's state.
	 *
	 * @param WC_PostFinanceCheckout_Entity_Transaction_Info $transaction_info transaction info.
	 * @return string
	 */
	protected static function get_transaction_state( WC_PostFinanceCheckout_Entity_Transaction_Info $transaction_info ) {
		switch ( $transaction_info->get_state() ) {
			case \PostFinanceCheckout\Sdk\Model\TransactionState::AUTHORIZED:
				return esc_html__( 'Authorized', 'woo-postfinancecheckout' );
			case \PostFinanceCheckout\Sdk\Model\TransactionState::COMPLETED:
				return esc_html__( 'Completed', 'woo-postfinancecheckout' );
			case \PostFinanceCheckout\Sdk\Model\TransactionState::CONFIRMED:
				return esc_html__( 'Confirmed', 'woo-postfinancecheckout' );
			case \PostFinanceCheckout\Sdk\Model\TransactionState::DECLINE:
				return esc_html__( 'Decline', 'woo-postfinancecheckout' );
			case \PostFinanceCheckout\Sdk\Model\TransactionState::FAILED:
				return esc_html__( 'Failed', 'woo-postfinancecheckout' );
			case \PostFinanceCheckout\Sdk\Model\TransactionState::FULFILL:
				return esc_html__( 'Fulfill', 'woo-postfinancecheckout' );
			case \PostFinanceCheckout\Sdk\Model\TransactionState::PENDING:
				return esc_html__( 'Pending', 'woo-postfinancecheckout' );
			case \PostFinanceCheckout\Sdk\Model\TransactionState::PROCESSING:
				return esc_html__( 'Processing', 'woo-postfinancecheckout' );
			case \PostFinanceCheckout\Sdk\Model\TransactionState::VOIDED:
				return esc_html__( 'Voided', 'woo-postfinancecheckout' );
			default:
				return esc_html__( 'Unknown State', 'woo-postfinancecheckout' );
		}
	}

	/**
	 * Returns the URL to the transaction detail view in PostFinanceCheckout.
	 *
	 * @param WC_PostFinanceCheckout_Entity_Transaction_Info $info info.
	 * @return string
	 */
	protected static function get_transaction_url( WC_PostFinanceCheckout_Entity_Transaction_Info $info ) {
		return WC_PostFinanceCheckout_Helper::instance()->get_base_gateway_url() . 's/' . $info->get_space_id() . '/payment/transaction/view/' .
				 $info->get_transaction_id();
	}

	/**
	 * Returns the charge attempt's labels by their groups.
	 *
	 * @param WC_PostFinanceCheckout_Entity_Transaction_Info $info info.
	 * @return \PostFinanceCheckout\Sdk\Model\Label[]
	 */
	protected static function get_grouped_charge_attempt_labels( WC_PostFinanceCheckout_Entity_Transaction_Info $info ) {
		try {
			$label_description_provider = WC_PostFinanceCheckout_Provider_Label_Description::instance();
			$label_description_group_provider = WC_PostFinanceCheckout_Provider_Label_Description_Group::instance();

			$labels_by_group_id = array();
			foreach ( $info->get_labels() as $descriptor_id => $value ) {
				$descriptor = $label_description_provider->find( $descriptor_id );
				if ( $descriptor && $descriptor->getCategory() == \PostFinanceCheckout\Sdk\Model\LabelDescriptorCategory::HUMAN ) {
					$labels_by_group_id[ $descriptor->getGroup() ][] = array(
						'descriptor' => $descriptor,
						'value' => $value,
					);
				}
			}

			$labels_by_group = array();
			foreach ( $labels_by_group_id as $group_id => $labels ) {
				$group = $label_description_group_provider->find( $group_id );
				if ( $group ) {
					usort(
						$labels,
						function ( $a, $b ) {
							return $a['descriptor']->getWeight() - $b['descriptor']->getWeight();
						}
					);
					$labels_by_group[] = array(
						'group'  => $group,
						'labels' => $labels,
					);
				}
			}
			usort(
				$labels_by_group,
				function ( $a, $b ) {
					return $a['group']->getWeight() - $b['group']->getWeight();
				}
			);

			return $labels_by_group;
		} catch ( Exception $e ) {
			return array();
		}
	}
}
WC_PostFinanceCheckout_Admin_Transaction::init();
