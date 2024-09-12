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
	<p><?php esc_html_e( 'The PostFinance Checkout payment methods are not available, if the taxes are rounded at subtotal level. Please disable the \'Round tax at subtotal level, instead of rounding per line\' in the tax settings to enable the PostFinance Checkout payment methods.', 'woo-postfinancecheckout' ); ?></p>
</div>
