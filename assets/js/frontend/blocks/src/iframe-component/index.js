import React, {useEffect, useState, useCallback, useRef} from 'react';

// Pre-translated server-side (see WC_WhiteLabelMachineName_Blocks_Support::get_payment_method_script_handles())
// so these strings go through the same PHP .pot/.po pipeline as the rest of the plugin.
const i18n = window.whitelabelmachinename_block_params?.i18n ?? {};

// How often we re-check for window.IframeCheckoutHandler while waiting for the
// portal script to load (ms).
const HANDLER_POLL_INTERVAL = 100;
// How long we keep polling before giving up (ms).
const HANDLER_POLL_TIMEOUT = 15000;

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
	// Set when the portal script never finished loading (see HANDLER_POLL_TIMEOUT below).
	const [loadError, setLoadError] = useState(false);
  	const containerId = `payment-method-${paymentMethodConfigurationId}`;

	// The handler that manages the iFrame.
	// The IframeCheckoutHandler was retrieved previously from the portal,
	// in the action 'woocommerce_blocks_enqueue_checkout_block_scripts_after'.
	// It is the ultimate responsible for generating the iframe for this payment method.
	// It is created lazily, once window.IframeCheckoutHandler becomes available (see below),
	// and stored in a ref so the callbacks below always see the current instance.
	const handlerRef = useRef(null);

	/**
	 * Defines setIframe, which uses useCallback react's hook.
	 *
	 * By using useCallback, we ensure that the annonymous function we pass to it runs
	 * only when its parameters (paymentMethodConfigurationId, containerId) change.
	 *
	 * @returns {void}
	 */
	const setIframe = useCallback(() => {
		handlerRef.current.create(containerId);

		setIsLoading(false);
	}, [paymentMethodConfigurationId, containerId]);

	// By using useEffect, we ensure that the annonymous function we pass to it runs
	// after the DOM has been updated with the div returned by this component.
	// This way we avoid a potential race condition error that will happen if the handler
	// runs before the div has been rendered on the webpage.
  	useEffect(() => {
		let pollTimeoutId = null;
		let pollElapsed = 0;
		let cancelled = false;

		// Clear any error left over from a previous poll cycle so a since-successful
		// cycle isn't masked by a stale error message.
		setLoadError(false);

		// Resolves once the handler is available or has definitively failed to be.
		// Checkout observers await this instead of failing the instant it's empty.
		let resolveHandlerReady;
		const handlerReady = new Promise((resolve) => {
			resolveHandlerReady = resolve;
		});

		// Handler if already available, otherwise deferred until handlerReady settles.
		// Resolves to null if the handler never became available.
		const getHandler = () => handlerRef.current ? Promise.resolve(handlerRef.current) : handlerReady;

		// Stops the spinner, surfaces an error, and unblocks anything awaiting getHandler().
		const failWithError = (message, error) => {
			console.error(message, error);
			setIsLoading(false);
			setLoadError(true);
			resolveHandlerReady(null);
		};

		// The portal script defining window.IframeCheckoutHandler loads independently
		// over the network, so we poll for it instead of calling it unconditionally.
		const pollForHandler = () => {
			if (cancelled) {
				return;
			}

			if (typeof window.IframeCheckoutHandler !== 'function') {
				if (pollElapsed >= HANDLER_POLL_TIMEOUT) {
					// Never showed up. Resolves handlerReady to null so waiting observers unblock.
					failWithError('IframeCheckoutHandler was not available after waiting for the portal script to load.');
					return;
				}

				pollElapsed += HANDLER_POLL_INTERVAL;
				pollTimeoutId = setTimeout(pollForHandler, HANDLER_POLL_INTERVAL);
				return;
			}

			try {
				handlerRef.current = window.IframeCheckoutHandler(paymentMethodConfigurationId);
			} catch (error) {
				// The portal script loaded but the handler could not be created (e.g. an
				// invalid configuration id).
				failWithError('Failed to create the IframeCheckoutHandler.', error);
				return;
			}

			if (!handlerRef.current) {
				// Some failures surface as a falsy return value instead of a thrown
				// error (e.g. an unrecognized configuration id). Treat that the same
				// as a thrown error instead of letting setIframe() below crash on it.
				failWithError('IframeCheckoutHandler did not return a handler.');
				return;
			}

			// Unblock any checkout observer that is currently awaiting getHandler().
			resolveHandlerReady(handlerRef.current);

			if (document.getElementById(containerId)) {
				setIframe();
			}
		};

		pollForHandler();

		// Register right away so checkout can always submit, even mid-poll. Both
		// observers await getHandler() instead of failing the instant it's still empty.
		const unsubscribeCheckoutSuccess = onCheckoutSuccess(() => {
			return getHandler().then((handler) => {
				if (!handler) {
					// WooCommerce only treats an object with a `type` property as an
					// observer response, so a plain `false` would be invisible to it
					// and let the order be created without ever calling submit().
					console.error('Checkout succeeded but the payment handler never became available.');
					return {
						type: 'error',
						errorMessage: i18n.load_error || 'Unable to load this payment method. Please refresh the page and try again.',
					};
				}

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
		});

		// Register the onCheckoutValidationBeforeProcessing event
		const unsubscribeCheckoutValidation = onCheckoutValidation(() => {
			return getHandler().then((handler) => {
				if (!handler) {
					// The handler never became available (poll timed out, threw, or
					// returned nothing). WooCommerce only treats an object with a
					// `type` property as an observer response, so a plain `false`
					// would let the order be created without a payment.
					console.error('Payment handler never became available, failing checkout validation.');
					return {
						type: 'error',
						errorMessage: i18n.load_error || 'Unable to load this payment method. Please refresh the page and try again.',
					};
				}

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
		});

		// Return a cleanup function that stops any pending poll and unsubscribes
		// from the checkout events we subscribed.
		return () => {
			cancelled = true;

			if (pollTimeoutId) {
				clearTimeout(pollTimeoutId);
			}

			unsubscribeCheckoutSuccess();
			unsubscribeCheckoutValidation();
		};

  	}, [setIframe, containerId, paymentMethodConfigurationId,
		onCheckoutSuccess,
		onCheckoutValidation,
	]);

	return (
		<div>
		  {loadError && <div>{i18n.load_error || 'Unable to load this payment method. Please refresh the page and try again.'}</div>}
		  {isLoading && !loadError && <div>{i18n.loading || 'Loading payment method...'}</div>}
		  <div id={containerId}></div>
		</div>
  	);
}

export default IframeComponent;
