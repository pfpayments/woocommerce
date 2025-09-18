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

if ( class_exists( 'WP_CLI' ) && ! class_exists( 'WC_PostFinanceCheckout_Commands' ) ) {

    /**
     * Class WC_PostFinanceCheckout_Commands.
     * This class contains custom commands for PostFinance Checkout.
     *
     * @class WC_PostFinanceCheckout_Commands
     */
    class WC_PostFinanceCheckout_Commands {

        /**
         * Register commands.
         */
        public static function init() {
            WP_CLI::add_command(
                'postfinancecheckout webhooks install',
                array(
                    __CLASS__,
                    'webhooks_install'
                )
            );
            WP_CLI::add_command(
                'postfinancecheckout payment-methods sync',
                array(
                    __CLASS__,
                    'payment_methods_sync'
                )
            );
        }

        /**
         * Create webhook URL and webhook listeners in the portal for PostFinance Checkout.
         *
         * ## EXAMPLE
         *
         *     $ wp postfinancecheckout webhooks install
         *
         * @param array $args WP-CLI positional arguments.
         * @param array $assoc_args WP-CLI associative arguments.
         */
        public static function webhooks_install( $args, $assoc_args ) {
            try {
                WC_PostFinanceCheckout_Helper::instance()->reset_api_client();
                WC_PostFinanceCheckout_Service_Webhook::instance()->install();
                WP_CLI::success( "Webhooks installed." );
            } catch ( \Exception $e ) {
                WooCommerce_PostFinanceCheckout::instance()->log( $e->getMessage(), WC_Log_Levels::ERROR );
                WP_CLI::error( "Failed to install webhooks: " . $e->getMessage() );
            }
        }

        /**
         * Synchronizes payment methods in the PostFinance Checkout from the portal.
         *
         * ## EXAMPLE
         *
         *     $ wp postfinancecheckout payment-methods sync
         *
         * @param array $args WP-CLI positional arguments.
         * @param array $assoc_args WP-CLI associative arguments.
         */
        public static function payment_methods_sync( $args, $assoc_args ) {
            try {
                WC_PostFinanceCheckout_Helper::instance()->reset_api_client();
                WC_PostFinanceCheckout_Service_Method_Configuration::instance()->synchronize();
                WC_PostFinanceCheckout_Helper::instance()->delete_provider_transients();
                WP_CLI::success( "Payment methods synchronized." );
            } catch ( \Exception $e ) {
                WooCommerce_PostFinanceCheckout::instance()->log( $e->getMessage(), WC_Log_Levels::ERROR );
                WP_CLI::error( "Failed to synchronize payment methods: " . $e->getMessage() );
            }
        }
    }
}

WC_PostFinanceCheckout_Commands::init();