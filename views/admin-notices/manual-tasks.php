<?php 
if (!defined('ABSPATH')) {
	exit(); // Exit if accessed directly.
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
?>

<div class="error notice notice-error">
	<p><?php
    	if($number_of_manual_tasks == 1){
    	    _e('There is a manual task that needs your attention.', 'woo-postfinancecheckout');
    	}
    	else{
    	   echo  sprintf(_n('There is %s manual task that needs your attention.', 'There are %s manual tasks that need your attention', $number_of_manual_tasks, 'woo-postfinancecheckout'), $number_of_manual_tasks);
    	}
		?>
    	</p>
	<p>
		<a href="<?php echo esc_url($manual_taks_url);?>" target="_blank"><?php _e('View', 'woo-postfinancecheckout');?></a>
	</p>
</div>