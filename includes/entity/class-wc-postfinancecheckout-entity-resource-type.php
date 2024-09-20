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
 * Defines the different resource types
 */
interface WC_PostFinanceCheckout_Entity_Resource_Type {
	const POSTFINANCECHECKOUT_STRING = 'string';
	const POSTFINANCECHECKOUT_DATETIME = 'datetime';
	const POSTFINANCECHECKOUT_INTEGER = 'integer';
	const POSTFINANCECHECKOUT_BOOLEAN = 'boolean';
	const POSTFINANCECHECKOUT_OBJECT = 'object';
	const POSTFINANCECHECKOUT_DECIMAL = 'decimal';
}
