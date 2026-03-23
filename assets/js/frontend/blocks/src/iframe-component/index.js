import React, {useEffect, useState, useCallback} from 'react';

/**
 * Renders the iFrame provided by the Portal.
 *
 * @param {number} paymentMethodConfigurationId
 * 	 The payment method configuration id as expected by the portal.
 * @returns {JSX.Element} The iFrame component.
 *
 * @see https://github.com/woocommerce/woocommerce/tree/trunk/plugins/woocommerce-blocks/docs/
 */
function IframeComponent({paymentMethodConfigurationId, eventRegistration}) {
	// Get the checkout events.
	// @see woocommerce/packages/woocommerce-blocks/assets/js/base/context/providers/cart-checkout/checkout-events/index.tsx
	const {
		onCheckoutSuccess,
		onCheckoutValidation,
	} = eventRegistration;

	// Loads the isLoading bool variable, and setIsLoading function from React's useState.
	const [isLoading, setIsLoading] = useState(true);
  	const containerId = `payment-method-${paymentMethodConfigurationId}`;

	// The handler that manages the iFrame.
	// The IframeCheckoutHandler was retrieved previously from the portal,
	// in the action 'woocommerce_blocks_enqueue_checkout_block_scripts_after'.
	// It is the ultimate responsible for generating the iframe for this payment method.
	let handler = window.IframeCheckoutHandler(paymentMethodConfigurationId);

	/**
	 * Defines setIframe, which uses useCallback react's hook.
	 *
	 * By using useCallback, we ensure that the annonymous function we pass to it runs
	 * only when its parameters (paymentMethodConfigurationId, containerId) change.
	 *
	 * @returns {void}
	 */
	const setIframe = useCallback(() => {
		handler.create(containerId);

		setIsLoading(false);
	}, [paymentMethodConfigurationId, containerId]);

	// By using useEffect, we ensure that the annonymous function we pass to it runs
	// after the DOM has been updated with the div returned by this component.
	// This way we avoid a potential race condition error that will happen if the handler
	// runs before the div has been rendered on the webpage.
  	useEffect(() => {
		if (document.getElementById(containerId)) {
		    setIframe();
		}

		// Register the onCheckoutSuccess event
		const unsubscribeCheckoutSuccess = onCheckoutSuccess(() => {
			// When the checkout did success, we return a promise
			// that will call the handler's submit method.
			return new Promise((resolve) => {
				handler.submit();
				// The handler's submit should redirect the browser to a succesful or failure
				// page registered preciously by the plugin.
				// We do not want to continue the flow here, so we set a timer for waiting to the
				// redirection from the submit.
				setTimeout(function() {
					// If we did not receive a response from the submit's handler after 30 seconds,
					// we resolve the promise to false, as something wrong happened.
					resolve(false);
				}, 30000);

			});
		});

		// Register the onCheckoutValidationBeforeProcessing event
		const unsubscribeCheckoutValidation = onCheckoutValidation(() => {
			// When the checkout is being validated, we return a promise
			// that will call the handler's validate method.
			let returnPromise = new Promise((resolve) => {
				handler.setValidationCallback(result => {
					if (result.success !== undefined) {
						resolve(result.success);
					}
					else {
						// Handle the undefined success scenario
						console.error('Validation was not successful');
						resolve(false);
					}
			    })
			});

			// Calls the portal for validation. Its response will be handled by the setValidationCallback,
			// which will resolve the promise that we return here.
			handler.validate();
			return returnPromise;
		});

		// Return a cleanup function that unsubscribes from the checkout events we subscribed
		return () => {
			unsubscribeCheckoutSuccess();
			unsubscribeCheckoutValidation();
		};

  	}, [setIframe, containerId,
		onCheckoutSuccess,
		onCheckoutValidation,
	]);

	return (
		<div>
		  {isLoading && <div>Loading payment method...</div>}
		  <div id={containerId}></div>
		</div>
  	);
}

export default IframeComponent;
