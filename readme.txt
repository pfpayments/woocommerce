=== PostFinance Checkout ===
Contributors: postfinancecheckout AG
Tags: woocommerce PostFinance Checkout, woocommerce, PostFinance Checkout, payment, e-commerce, webshop, psp, invoice, packing slips, pdf, customer invoice, processing
Requires at least: 4.7
Tested up to: 6.7
Stable tag: 3.3.13
License: Apache-2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0

Accept payments in WooCommerce with PostFinance Checkout.

== Description ==

Website: [https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html](https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)

The plugin offers an easy and convenient way to accept credit cards and all
other payment methods listed below fast and securely. The payment forms will be fully integrated in your checkout
and for credit cards there is no redirection to a payment page needed anymore. The pages are by default mobile optimized but
the look and feel can be changed according the merchants needs.

This plugin will add support for all PostFinance Checkout payments methods and connect the PostFinance Checkout servers to your WooCommerce webshop.
To use this extension, a PostFinance Checkout account is required. Sign up on [PostFinance Checkout](https://checkout.postfinance.ch/en-ch/user/signup).

== Documentation ==

Additional documentation for this plugin is available [here](https://plugin-documentation.postfinance-checkout.ch/pfpayments/woocommerce/3.3.13/docs/en/documentation.html).

== External Services ==

This plugin includes an internal script to manage device verification within the WooCommerce store environment. 

The script helps ensure session consistency and transaction security.

- **Service Name:** PostFinance Checkout Device Verification Script
- **Purpose:** To track device sessions and enhance security during checkout and payment processing.
- **Data Sent:**
  - **Cookie Name:** `wc_whitelabelname_device_id`
  - **Data Stored in Cookie:** A unique device identifier (hashed value).
  - **When the Cookie is Set:** The cookie is set when the checkout page is accessed and updated during payment processing.
  - **Where the Data is Processed:** All operations occur locally within the WooCommerce store and are not transmitted to external services.
- **Conditions for Use:** The cookie is only set if the customer initiates a checkout session.

No personal data is sent to third-party services; all information remains within the WooCommerce store for internal verification purposes.

== Support ==

Support queries can be issued on the [PostFinance Checkout support site](https://www.postfinance.ch/en/business/support.html).

== Privacy Policy ==

Enquiries about our privacy policy can be made on the [PostFinance Checkout privacy policies site](https://www.postfinance.ch/en/detail/data-protection/general-privacy-policy.html).

== Terms of use ==

Enquiries about our terms of use can be made on the [PostFinance Checkout terms of use site](https://www.postfinance.ch/content/dam/pfch/doc/0_399/00201_en.pdf).

== Installation ==

= Minimum Requirements =

* PHP version 5.6 or greater
* WordPress 4.7 up to 6.6
* WooCommerce 3.0.0 up to 9.8.5

= Automatic installation =

1. Install the plugin via Plugins -> New plugin. Search for 'PostFinance Checkout'.
2. Activate the 'PostFinance Checkout' plugin through the 'Plugins' menu in WordPress
3. Set your PostFinance Checkout credentials at WooCommerce -> Settings -> PostFinance Checkout (or use the *Settings* link in the Plugins overview)
4. You're done, the active payment methods should be visible in the checkout of your webshop.

= Manual installation =

1. Unpack the downloaded package.
2. Upload the directory to the `/wp-content/plugins/` directory
3. Activate the 'PostFinance Checkout' plugin through the 'Plugins' menu in WordPress
4. Set your credentials at WooCommerce -> Settings -> PostFinance Checkout (or use the *Settings* link in the Plugins overview)
5. You're done, the active payment methods should be visible in the checkout of your webshop.


== Changelog ==


= 3.3.13 - July 1st 2025 =
- [Bugfix] Remove pay button for already paid orders
- [Bugfix] Fix error when manually extending a subscription
- [Bugfix] Fix incorrect handling of orders containing 0 amount line items
- [Tested Against] PHP 8.2
- [Tested Against] Wordpress 6.7
- [Tested Against] Woocommerce 9.9.5
- [Tested Against] PHP SDK 4.8.0
