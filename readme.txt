=== WooCommerce PostFinance Checkout ===
Contributors: customwebgmbh
Tags: woocommerce PostFinance Checkout, woocommerce, PostFinance Checkout, payment, e-commerce, webshop, psp, invoice, packing slips, pdf, customer invoice, processing
Requires at least: 4.7
Tested up to: 5.0.0
Stable tag: 1.1.14
License: Apache 2
License URI: http://www.apache.org/licenses/LICENSE-2.0

Accept payments in WooCommerce with PostFinance Checkout.

== Description ==

Website: [https://www.postfinance.ch](https://www.postfinance.ch)

The WooCommerce plugin offers an easy and convenient way to accept credit cards and all 
other payment methods listed below fast and securely. The payment forms will be fully integrated in your checkout 
and for credit cards there is no redirection to a payment page needed anymore. The pages are by default mobile optimized but 
the look and feel can be changed according the merchants needs. 

This plugin will add support for all PostFinance Checkout payments methods to your WooCommerce webshop.
To use this extension, a PostFinance Checkout account is required. Sign up on [PostFinance Checkout](https://www.postfinance-checkout.ch/user/signup).

== Documentation ==

Additional documentation for this plugin is available [here](https://plugin-documentation.postfinance-checkout.ch/pfpayments/woocommerce/1.1.14/docs/en/documentation.html).

== Installation ==

= Minimum Requirements =

* PHP version 5.6 or greater
* WordPress 4.4 or greater
* WooCommerce 3.0.0 or greater

= Automatic installation =

1. Install the plugin via Plugins -> New plugin. Search for 'Woocommerce PostFinance Checkout'.
2. Activate the 'WooCommerce PostFinance Checkout' plugin through the 'Plugins' menu in WordPress
3. Set your PostFinance Checkout credentials at WooCommerce -> Settings -> PostFinance Checkout (or use the *Settings* link in the Plugins overview)
4. You're done, the active payment methods should be visible in the checkout of your webshop.

= Manual installation =

1. Unpack the downloaded package.
2. Upload the directory to the `/wp-content/plugins/` directory
3. Activate the 'WooCommerce PostFinance Checkout' plugin through the 'Plugins' menu in WordPress
4. Set your wallee credentials at WooCommerce -> Settings -> PostFinance Checkout (or use the *Settings* link in the Plugins overview)
5. You're done, the active payment methods should be visible in the checkout of your webshop.


== Changelog ==

 
= 1.1.14 - November 21, 2018 =

* Fixes - Improved available payment method caching.
* Fixes - Fixes Refund failure message not displayed.
* Feature - Added ability to use custom 'Date Of Birth' or 'Gender' field in checkout.
