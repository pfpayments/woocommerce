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

?>

<div class="error notice notice-error">
	<p>
	<?php
	if ( 1 === $number_of_manual_tasks ) {
		esc_html_e( 'There is a manual task that needs your attention.', 'woo-postfinancecheckout' );
	} else {
		/* translators: %s are replaced with int */
		echo esc_html( sprintf( _n( 'There is %s manual task that needs your attention.', 'There are %s manual tasks that need your attention', $number_of_manual_tasks, 'woo-postfinancecheckout' ), $number_of_manual_tasks ) );
	}
	?>
		</p>
	<p>
		<a href="<?php echo esc_url( $manual_taks_url ); ?>" target="_blank"><?php esc_html_e( 'View', 'woo-postfinancecheckout' ); ?></a>
	</p>
</div>
