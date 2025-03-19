<?php
/**
 * Tax Calculation Tests
 *
 * @package WooCommerce
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WC_PostFinanceCheckout_Tax_Test
 */
class WC_PostFinanceCheckout_Tax_Test extends WP_UnitTestCase {

    /**
     * Test setup
     */
    public function setUp(): void {
        parent::setUp();
        
        // Create a test product
        $this->product = WC_Helper_Product::create_simple_product();
        
        // Create test tax rates
        $this->create_test_tax_rates();
        
        // Create test tax classes
        $this->create_test_tax_classes();
    }

    /**
     * Test teardown
     */
    public function tearDown(): void {
        // Clean up test data
        WC_Helper_Product::delete_product($this->product->get_id());
        $this->cleanup_test_tax_rates();
        $this->cleanup_test_tax_classes();
        
        parent::tearDown();
    }

    /**
     * Create test tax rates
     */
    private function create_test_tax_rates() {
        // Create a standard rate (20%)
        $this->standard_rate = WC_Tax::_insert_tax_rate(array(
            'tax_rate_country'  => 'GB',
            'tax_rate_state'    => '',
            'tax_rate'          => '20.0000',
            'tax_rate_name'     => 'Standard Rate',
            'tax_rate_priority' => 1,
            'tax_rate_compound' => 0,
            'tax_rate_shipping' => 1,
            'tax_rate_order'    => 0,
        ));

        // Create a reduced rate (5%)
        $this->reduced_rate = WC_Tax::_insert_tax_rate(array(
            'tax_rate_country'  => 'GB',
            'tax_rate_state'    => '',
            'tax_rate'          => '5.0000',
            'tax_rate_name'     => 'Reduced Rate',
            'tax_rate_priority' => 1,
            'tax_rate_compound' => 0,
            'tax_rate_shipping' => 1,
            'tax_rate_order'    => 0,
        ));
    }

    /**
     * Create test tax classes
     */
    private function create_test_tax_classes() {
        // Create standard tax class
        $this->standard_tax_class = WC_Tax::create_tax_class('Standard Tax Class');
        
        // Create reduced tax class
        $this->reduced_tax_class = WC_Tax::create_tax_class('Reduced Tax Class');
        
        // Link tax rates to classes
        WC_Tax::create_tax_class_rate_link($this->standard_tax_class, $this->standard_rate);
        WC_Tax::create_tax_class_rate_link($this->reduced_tax_class, $this->reduced_rate);
    }

    /**
     * Clean up test tax rates
     */
    private function cleanup_test_tax_rates() {
        WC_Tax::_delete_tax_rate($this->standard_rate);
        WC_Tax::_delete_tax_rate($this->reduced_rate);
    }

    /**
     * Clean up test tax classes
     */
    private function cleanup_test_tax_classes() {
        WC_Tax::delete_tax_class($this->standard_tax_class);
        WC_Tax::delete_tax_class($this->reduced_tax_class);
    }

    /**
     * Test basic tax calculation
     */
    public function test_basic_tax_calculation() {
        $this->product->set_tax_class($this->standard_tax_class);
        $this->product->set_regular_price('100.00');
        $this->product->save();

        $line_item_service = new WC_PostFinanceCheckout_Service_Line_Item();
        $line_items = $line_item_service->get_items_from_session();

        $this->assertEquals(1, count($line_items));
        $this->assertEquals('120.00', $line_items[0]->getAmountIncludingTax());
    }

    /**
     * Test tax calculation with reduced rate
     */
    public function test_reduced_rate_tax_calculation() {
        $this->product->set_tax_class($this->reduced_tax_class);
        $this->product->set_regular_price('100.00');
        $this->product->save();

        $line_item_service = new WC_PostFinanceCheckout_Service_Line_Item();
        $line_items = $line_item_service->get_items_from_session();

        $this->assertEquals(1, count($line_items));
        $this->assertEquals('105.00', $line_item_service->get_items_from_session()[0]->getAmountIncludingTax());
    }

    /**
     * Test tax calculation with discounts
     */
    public function test_tax_calculation_with_discount() {
        $this->product->set_tax_class($this->standard_tax_class);
        $this->product->set_regular_price('100.00');
        $this->product->save();

        // Create and apply a coupon
        $coupon = WC_Helper_Coupon::create_coupon('test-coupon', array(
            'discount_type' => 'percent',
            'coupon_amount' => 10,
        ));

        WC()->cart->add_to_cart($this->product->get_id());
        WC()->cart->apply_coupon('test-coupon');

        $line_item_service = new WC_PostFinanceCheckout_Service_Line_Item();
        $line_items = $line_item_service->get_items_from_session();

        // Check product line item
        $this->assertEquals('108.00', $line_items[0]->getAmountIncludingTax());
        
        // Check discount line item
        $this->assertEquals('-12.00', $line_items[1]->getAmountIncludingTax());

        WC_Helper_Coupon::delete_coupon($coupon->get_id());
    }

    /**
     * Test tax calculation with shipping
     */
    public function test_tax_calculation_with_shipping() {
        $this->product->set_tax_class($this->standard_tax_class);
        $this->product->set_regular_price('100.00');
        $this->product->save();

        // Add shipping method
        WC()->shipping->load_shipping_methods();
        $shipping_methods = WC()->shipping->get_shipping_methods();
        $flat_rate = $shipping_methods['flat_rate'];
        $flat_rate->set_option('cost', '10.00');
        $flat_rate->set_option('tax_status', 'taxable');

        WC()->cart->add_to_cart($this->product->get_id());
        WC()->cart->calculate_totals();

        $line_item_service = new WC_PostFinanceCheckout_Service_Line_Item();
        $line_items = $line_item_service->get_items_from_session();

        // Check product line item
        $this->assertEquals('120.00', $line_items[0]->getAmountIncludingTax());
        
        // Check shipping line item
        $this->assertEquals('12.00', $line_items[1]->getAmountIncludingTax());
    }

    /**
     * Test tax calculation with multiple tax rates
     */
    public function test_multiple_tax_rates() {
        // Create a product with compound tax
        $this->product->set_tax_class($this->standard_tax_class);
        $this->product->set_regular_price('100.00');
        $this->product->save();

        // Add a second tax rate
        $additional_rate = WC_Tax::_insert_tax_rate(array(
            'tax_rate_country'  => 'GB',
            'tax_rate_state'    => '',
            'tax_rate'          => '10.0000',
            'tax_rate_name'     => 'Additional Rate',
            'tax_rate_priority' => 2,
            'tax_rate_compound' => 1,
            'tax_rate_shipping' => 1,
            'tax_rate_order'    => 0,
        ));

        WC_Tax::create_tax_class_rate_link($this->standard_tax_class, $additional_rate);

        $line_item_service = new WC_PostFinanceCheckout_Service_Line_Item();
        $line_items = $line_item_service->get_items_from_session();

        $this->assertEquals('132.00', $line_items[0]->getAmountIncludingTax());

        WC_Tax::_delete_tax_rate($additional_rate);
    }

    /**
     * Test tax calculation with zero amount
     */
    public function test_tax_calculation_with_zero_amount() {
        $this->product->set_tax_class($this->standard_tax_class);
        $this->product->set_regular_price('0.00');
        $this->product->save();

        $line_item_service = new WC_PostFinanceCheckout_Service_Line_Item();
        $line_items = $line_item_service->get_items_from_session();

        $this->assertEquals('0.00', $line_items[0]->getAmountIncludingTax());
    }
} 