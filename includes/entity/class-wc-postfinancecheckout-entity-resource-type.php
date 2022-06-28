<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * Defines the different resource types
 */
interface WC_PostFinanceCheckout_Entity_Resource_Type {
	const STRING = 'string';
	const DATETIME = 'datetime';
	const INTEGER = 'integer';
	const BOOLEAN = 'boolean';
	const OBJECT = 'object';
	const DECIMAL = 'decimal';
}