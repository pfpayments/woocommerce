import React, {useEffect, useState, useCallback} from 'react';

/**
 * Renders a paymentpage in the checkout form.
 *
 * @param {number} paymentMethodConfigurationId
 *   The payment method configuration id as expected by the portal.
 * @returns void
 *
 * @see https://github.com/woocommerce/woocommerce/tree/trunk/plugins/woocommerce-blocks/docs/
 */
function PaymentPageComponent({paymentMethodConfigurationId, paymentPageUrl, eventRegistration}) {
	const { onCheckoutSuccess } = eventRegistration;
	// There's nothing to do here, as user will be redirect in the backend
	// @see WC_WhiteLabelMachineName_Blocks_Support::process_payment()
}

export default PaymentPageComponent;
