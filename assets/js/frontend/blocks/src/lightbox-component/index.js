import React, {useEffect, useState, useCallback} from 'react';

/**
 * Renders a ligthbox in the checkout form.
 *
 * @param {number} paymentMethodConfigurationId
 * 	 The payment method configuration id as expected by the portal.
 * @returns void
 *
 * @see https://github.com/woocommerce/woocommerce/tree/trunk/plugins/woocommerce-blocks/docs/
 */
function LightboxComponent({paymentMethodConfigurationId, eventRegistration}) {
	const { onCheckoutSuccess } = eventRegistration;

	// By using useEffect hook, we ensure to run our code after the component is mounted.
	useEffect(
		() => {
			/**
			 * Process the payment by calling the LightboxCheckoutHandler, which will take care
			 * of displaying the Ligthbox.
			 *
			 * @returns void
			 */
			const processPayment = () => {
				// By returning this promise, we ensure that the payment is processed asynchronously.
				return new Promise(
					(resolve, reject) => {
                    	// Invoke the lightbox, and provide an errorCallback handler.
						window.LightboxCheckoutHandler.startPayment(
							paymentMethodConfigurationId,
							function (response, xhr) {
								// If there is an error, reject the Promise
								if ( ! response || response.error) {
									reject( response );
								} else {
									// If everything is okay, resolve the Promise
									resolve( response );
								}
							}
						);
					}
				)
				.catch(
					error => {
						// Handle errors
						console.error( error );
					}
				);
			};
			// Process the payment when the checkout has been succesful.
			const unsubscribeCheckoutSuccess = onCheckoutSuccess( processPayment );
			return () => {
					unsubscribeCheckoutSuccess()
				};
			},
		[onCheckoutSuccess, paymentMethodConfigurationId]
	);
}

export default LightboxComponent;

