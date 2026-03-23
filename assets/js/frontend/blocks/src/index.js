import IframeComponent from './iframe-component';
import LightboxComponent from './lightbox-component';
import PaymentPageComponent from './paymentpage-component';
import { parseImgTag, LabelComponent } from './label-component';
import { registerPaymentMethod, getPaymentMethods } from '@woocommerce/blocks-registry';

// Fetching payment methods from backend
const fetchPaymentMethods = async (options = {}) => {
	const {
		updateTransaction = false,
		enqueuePortalScripts = false,
	} = options;
    try {
        const formData = new URLSearchParams();
        formData.append('action', 'whitelabelmachinename_get_payment_methods');
        formData.append('whitelabelmachinename_nonce', window.whitelabelmachinename_block_params?.whitelabelmachinename_nonce || '');

		if (updateTransaction) {
			formData.append('updateTransaction', '1');
		}

		if (enqueuePortalScripts) {
			formData.append('enqueuePortalScripts', '1');
		}

        const response = await fetch(wp.apiFetch.nonceEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData,
        });

        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }

        const data = await response.json();
        if (!data) throw new Error('No payment methods found');

        return data;
    } catch (error) {
        console.error('Error fetching payment methods:', error);
    }
};

// Function do display/hide payment methods on checkout page (according to isActive field)
// The payment methods loaded here are the ones available from the backend of woocommerce
const registerPaymentMethods = async (paymentMethods) => {
    paymentMethods.forEach((methodInfo) => {

		const content = (() => {
			switch (methodInfo.integration_mode) {
				case 'iframe':
					return <IframeComponent paymentMethodConfigurationId={methodInfo.configuration_id} />;
				case 'lightbox':
					return <LightboxComponent paymentMethodConfigurationId={methodInfo.configuration_id} />;
				case 'payment_page':
					return <PaymentPageComponent paymentMethodConfigurationId={methodInfo.configuration_id} />;
				default:
					return <PaymentPageComponent paymentMethodConfigurationId={methodInfo.configuration_id} />;
			}
		})();

        const iconAttributes = parseImgTag(methodInfo.icon);

        registerPaymentMethod({
            name: methodInfo.name,
            label: <LabelComponent label={methodInfo.label} {...iconAttributes} />,
            content,
            edit: <div>{methodInfo.description}</div>,
            ariaLabel: methodInfo.ariaLabel,
            supports: { features: methodInfo.supports },
            canMakePayment: () => Promise.resolve(methodInfo.isActive),
        });
    });
};

const initPaymentMethods = async () => {
    try {
        const paymentMethods = await fetchPaymentMethods({ updateTransaction: true, enqueuePortalScripts: true });
        registerPaymentMethods(paymentMethods);
    } catch (error) {
        console.error('Error initializing payment methods:', error);
    }
};

const updateMethodList = async (options = {}) => {
	try {
		const paymentMethods = await fetchPaymentMethods(options);
		const activePaymentMethods = getPaymentMethods();

		paymentMethods.forEach((methodInfo) => {
			const paymentMethod = activePaymentMethods[methodInfo.name] ?? false;
			const newOptions = {
				...paymentMethod,
				canMakePayment: () => {
					return methodInfo.isActive;
				},
			};
			registerPaymentMethod(newOptions);
		});
	} catch (error) {
	    console.error("Error handling batch response:", error);
	}
}

function getFormData() {
	const form = document.querySelector('form.woocommerce-checkout')
	|| document.querySelector('form.checkout')
	|| document;

	const requiredFormFields = form.querySelectorAll('[required], [aria-required="true"]');
	return requiredFormFields;
}

function areRequiredFieldsFilled(formFields) {

	for (const field of formFields) {
		const type = (field.getAttribute('type') || '').toLowerCase();

		if(field.tagName.toLowerCase() === 'select') {
			if (!field.value) return false;
			continue;
		}

		if(type === 'checkbox') {
			continue;
		}

		if(type === 'radio') {
			const radioName = field.getAttribute('name');
			const radioChecked = document.querySelector(`input[type="radio"][name="${radioName}"]:checked`);
			if (!radioChecked) return false;
			continue;
		}

		if(!field.value.toString().trim()) return false;
	}
	return true;
}

// If not checkout page, do nothing
if (document.body.classList.contains('woocommerce-checkout') &&
    !document.body.classList.contains('woocommerce-order-received')) {
	// Initial payment methods comes from backend, so we save one request
	const dataJsonString = document.getElementById('whitelabelmachinename-payment-methods')?.getAttribute('data-json');
	if (dataJsonString) {
	    try {
	        const jsonData = JSON.parse(dataJsonString);
	        registerPaymentMethods(jsonData);
	    } catch (error) {
	        console.error('Error parsing JSON from HTML:', error);
	    }
	} else {
	    initPaymentMethods();
	}

	// Intercept WooCommerce batch API responses, payment methods are updated only when customer data is updated and we can update transaction with accurate data
    (function () {
        const originalFetch = window.fetch;

        window.fetch = async (...args) => {
            const response = await originalFetch(...args);

            const url = args[0];
            if (typeof url === "string" && url.includes("/wc/store/v1/batch")) {
                const requestBody = args[1]?.body ? JSON.parse(args[1].body) : null;
                if (requestBody && requestBody.requests && Array.isArray(requestBody.requests)) {
                    if (requestBody.requests.some(r => r.path === "/wc/store/v1/cart/update-customer")) {
                        const formData = getFormData();
                        // We update payment method list only when customer has filled all required fields
                        if(areRequiredFieldsFilled(formData)) {
                            updateMethodList();
                        }
						// Here we are checking to see if there is a request from the giftcards plugin to add/remove giftcard from checkout
                    } else if (requestBody.requests?.[0]?.data?.namespace === "woocommerce-gift-cards") {
						updateMethodList({ updateTransaction: true });
					} else if (requestBody.requests.some(r => r.path === "/wc/store/v1/cart/apply-coupon") || requestBody.requests.some(r => r.path === "/wc/store/v1/cart/remove-coupon")) {
						updateMethodList({ enqueuePortalScripts: true });
                    }
                }
            }
            return response;
        };
    })();
}
