WooCommerce Blocks integration
==============================

This project provides the functionality needed for integrating the payment methods provided 
by PostFinance Checkout with the WooCommerce Blocks.

See [this documentation](https://github.com/woocommerce/woocommerce/tree/trunk/plugins/woocommerce-blocks/docs/) for more information about the integration process.

This project integrates to the checkout block, provided by WooCommerce Blocks, in 2 separate ways:
- iFrame: The payment method form is displayed in an iFrame rendered in the checkout form directly.
- Ligthbox: The payment method form is displayed in a lightbox, renderd on top of the checkout form.

The woocommerce shop's admin is able to select the integration mode from the settings of this plugin
in the backend.

Project layout
==============
This project consists in the following files:

- blocks
  - build: this folder contains the project built and minimized.
    - index.asset.php: this file is used by the WP Javascrit builder for controlling the version of the project
    - index.js: the project properly built and minimized, ready to be served in the checkout form.
  - node_modules: Dependencies for building the project
  - src
    - iframe-component
      - index.js: Logic for generating the iframe from the portal
    - lightbox-component
      - index.js: Logic for displaying the Lightbox provided by the portal
    - label-component
      - index.js: Logic for displaying the label that is rendered in the checkout's list of payments.
  - package-lock.json: control for project's build dependencies
  - package.json: project's definition as expected by wp-scripts.
  - README.md: this file
  - webpack.config.js: webpack's configuration for building the project (https://developer.woo.com/2020/11/13/tutorial-adding-react-support-to-a-woocommerce-extension/)


How to modify this project
==========================

If you need to modify the source code of this project, you need to build the project. Everything needed is already configured in the package.json file.
If you have not done it before, you need to first install the dependencies needed by the project by running:

`npm install`

Please notice that you need at least npm version 10 or up.

After you have saved your changes, build the project by running:
`npm run build`

The build folder will update with the new version of the project. The next request to the build/index.js file will contain your changes.
