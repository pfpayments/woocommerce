<?php
/**
 * Plugin Name: PostFinanceCheckout
 * Author: postfinancecheckout AG
 * Text Domain: postfinancecheckout
 * Domain Path: /languages/
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   postfinancecheckout AG (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_PostFinanceCheckout_Helper Class.
 */
class WC_PostFinanceCheckout_Helper {

	const POSTFINANCECHECKOUT_SHOP_SYSTEM = 'x-meta-shop-system';
	const POSTFINANCECHECKOUT_SHOP_SYSTEM_VERSION = 'x-meta-shop-system-version';
	const POSTFINANCECHECKOUT_SHOP_SYSTEM_AND_VERSION = 'x-meta-shop-system-and-version';
	const POSTFINANCECHECKOUT_CHECKOUT_VERSION = 'x-checkout-type';
	const POSTFINANCECHECKOUT_CHECKOUT_TYPE_BLOCKS = 'blocks';
	const POSTFINANCECHECKOUT_CHECKOUT_TYPE_LEGACY = 'legacy';

	/**
	 * Instance.
	 *
	 * @var mixed $instance instance.
	 */
	private static $instance;

	/**
	 * Api client.
	 *
	 * @var mixed $api_client api client.
	 */
	private $api_client;

	/**
	 * Construct.
	 */
	private function __construct() {}

	/**
	 * Instance.
	 *
	 * @return WC_PostFinanceCheckout_Helper
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Delete provider transients.
	 *
	 * @return void
	 */
	public function delete_provider_transients() {
		$transients = array(
			'wc_postfinancecheckout_currencies',
			'wc_postfinancecheckout_label_description_groups',
			'wc_postfinancecheckout_label_descriptions',
			'wc_postfinancecheckout_languages',
			'wc_postfinancecheckout_payment_connectors',
			'wc_postfinancecheckout_payment_methods',
		);
		foreach ( $transients as $transient ) {
			delete_transient( $transient );
		}
	}

	/**
	 * Get api client.
	 *
	 * @throws Exception Exception.
	 * @return \PostFinanceCheckout\Sdk\ApiClient
	 */
	public function get_api_client() {
		if ( null === $this->api_client ) {
			$user_id = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_APP_USER_ID );
			$user_key = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_APP_USER_KEY );
			if ( ! empty( $user_id ) && ! empty( $user_key ) ) {
				$this->api_client = new \PostFinanceCheckout\Sdk\ApiClient( $user_id, $user_key );
				$this->api_client->setBasePath( rtrim( $this->get_base_gateway_url(), '/' ) . '/api' );
				foreach ( self::get_default_header_data() as $key => $value ) {
					$this->api_client->addDefaultHeader( $key, $value );
				}
			} else {
				throw new Exception( esc_html__( 'The API access data is incomplete.', 'woo-postfinancecheckout' ) );
			}
		}
		return $this->api_client;
	}

	/**
	 * Reset api client.
	 *
	 * @return void
	 */
	public function reset_api_client() {
		$this->api_client = null;
	}

	/**
	 * Returns the base URL to the gateway.
	 *
	 * @return string
	 */
	public function get_base_gateway_url() {
		return get_option( 'wc_postfinancecheckout_base_gateway_url', 'https://checkout.postfinance.ch/' );
	}


	/**
	 * Translate.
	 *
	 * @param mixed $translated_string translated string.
	 * @param mixed $language language.
	 * @return mixed|null
	 */
	public function translate( $translated_string, $language = null ) {
		if ( empty( $language ) ) {
			$language = $this->get_cleaned_locale();
		}
		if ( isset( $translated_string[ $language ] ) ) {
			return $translated_string[ $language ];
		}

		try {
			$language_provider = WC_PostFinanceCheckout_Provider_Language::instance();
			$primary_language = $language_provider->find_primary( $language );
			if ( $primary_language && isset( $translated_string[ $primary_language->getIetfCode() ] ) ) {
				return $translated_string[ $primary_language->getIetfCode() ];
			}
		} catch ( Exception $e ) {
			return null;
		}

		if ( isset( $translated_string['en-US'] ) ) {
			return $translated_string['en-US'];
		}

		return null;
	}

	/**
	 * Returns the URL to a resource on PostFinanceCheckout in the given context (space, space view, language).
	 *
	 * @param string $base base.
	 * @param string $path path.
	 * @param string $language language.
	 * @param int    $space_id space id.
	 * @param int    $space_view_id space view id.
	 * @return string
	 */
	public function get_resource_url( $base, $path, $language = null, $space_id = null, $space_view_id = null ) {
		if ( empty( $base ) ) {
			$url = $this->get_base_gateway_url();
		} else {
			$url = $base;
		}
		$url = rtrim( $url, '/' );

		if ( ! empty( $language ) ) {
			$url .= '/' . str_replace( '_', '-', $language );
		}

		if ( ! empty( $space_id ) ) {
			$url .= '/s/' . $space_id;
		}

		if ( ! empty( $space_view_id ) ) {
			$url .= '/' . $space_view_id;
		}

		$url .= '/resource/' . $path;
		return $url;
	}

	/**
	 * Returns the fraction digits of the given currency.
	 *
	 * @param string $currency_code currency code.
	 * @return int
	 */
	public function get_currency_fraction_digits( $currency_code ) {
		$currency_provider = WC_PostFinanceCheckout_Provider_Currency::instance();
		$currency = $currency_provider->find( $currency_code );
		if ( $currency ) {
			return $currency->getFractionDigits();
		} else {
			return 2;
		}
	}

	/**
	 * Get total amount including tax.
	 *
	 * @param array $line_items line items.
	 * @param bool  $exclude_discounts exclude discounts.
	 * @return int
	 */
	public function get_total_amount_including_tax( array $line_items, bool $exclude_discounts = false ) {
		$sum = 0;
		foreach ( $line_items as $line_item ) {
			$type = $line_item->getType();
			$name = $line_item->getName();

			if ( $exclude_discounts && \PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT === $type
				&& strpos( $name, WC_PostFinanceCheckout_Packages_Coupon_Discount::POSTFINANCECHECKOUT_COUPON ) !== false
			) {
				// convert negative values to positive in order to be able to subtract it.
				$sum -= abs( $line_item->getAmountIncludingTax() );
			} else {
				$sum += abs( $line_item->getAmountIncludingTax() );
			}
		}
		return $sum;
	}

	/**
	 * Cleanup line items.
	 *
	 * @param array $line_items line items.
	 * @param mixed $expected_sum expected sum.
	 * @param mixed $currency currency.
	 * @return \PostFinanceCheckout\Sdk\Model\LineItemCreate[]
	 * @throws WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount.
	 */
	public function cleanup_line_items( array $line_items, $expected_sum, $currency ) {
		// ensure that the effective sum coincides with the total discounted by the coupons.
		$has_coupons = apply_filters( 'wc_postfinancecheckout_packages_coupon_has_coupon_discounts_applied', $currency ); //phpcs:ignore
		$effective_sum = $this->round_amount( $this->get_total_amount_including_tax( $line_items, $has_coupons ), $currency );
		$rounded_expected_sum = $this->round_amount( $expected_sum, $currency );

		if ( $has_coupons ) {
			$result = apply_filters( 'wc_postfinancecheckout_packages_coupon_process_line_items_with_coupons', $line_items, $expected_sum, $currency ); //phpcs:ignore
			$line_items = $result['line_items_cleaned'];
			$effective_sum = $result['effective_sum'];
			$rounded_expected_sum = $this->round_amount( $expected_sum, $currency );
		}

		$inconsistent_amount = $rounded_expected_sum - $effective_sum;
		if ( 0 !== (int) $inconsistent_amount ) {
			$enforce_consistency = get_option( WooCommerce_PostFinanceCheckout::POSTFINANCECHECKOUT_CK_ENFORCE_CONSISTENCY );
			switch ( $enforce_consistency ) {
				case 'no':
					$line_item = new \PostFinanceCheckout\Sdk\Model\LineItemCreate();
					$line_item->setAmountIncludingTax( $this->round_amount( $inconsistent_amount, $currency ) );
					$line_item->setName( esc_html__( 'Adjustment', 'woo-postfinancecheckout' ) );
					$line_item->setQuantity( 1 );
					$line_item->setSku( 'adjustment' );
					$line_item->setUniqueId( 'adjustment' );
					$line_item->setShippingRequired( false );
					$line_item->setType( $enforce_consistency > 0 ? \PostFinanceCheckout\Sdk\Model\LineItemType::FEE : \PostFinanceCheckout\Sdk\Model\LineItemType::DISCOUNT );
					$line_items[] = $line_item;
					break;
				default:
					throw new WC_PostFinanceCheckout_Exception_Invalid_Transaction_Amount( esc_html( $effective_sum ), esc_html( $rounded_expected_sum ) );
			}
		}
		return $this->ensure_unique_ids( $line_items );
	}


	/**
	 * Ensure unique ids.
	 *
	 * @param array $line_items line items.
	 * @return array
	 * @throws Exception Exception.
	 */
	public function ensure_unique_ids( array $line_items ) {
		$unique_ids = array();
		foreach ( $line_items as $line_item ) {
			$unique_id = $line_item->getUniqueId();
			if ( empty( $unique_id ) ) {
				$unique_id = preg_replace( '/[^a-z0-9]/', '', strtolower( $line_item->getSku() ) );
			}
			if ( empty( $unique_id ) ) {
				throw new Exception( 'There is an invoice item without unique id.' );
			}
			if ( isset( $unique_ids[ $unique_id ] ) ) {
				$backup = $unique_id;
				$unique_id = $unique_id . '_' . $unique_ids[ $unique_id ];
				++$unique_ids[ $backup ];
			} else {
				$unique_ids[ $unique_id ] = 1;
			}

			$line_item->setUniqueId( $unique_id );
		}

		return $line_items;
	}


	/**
	 * Get reduction amount.
	 *
	 * @param array $line_items line items.
	 * @param array $reductions reductions.
	 * @return float|int
	 */
	public function get_reduction_amount( array $line_items, array $reductions ) {
		$line_item_map = array();
		foreach ( $line_items as $line_item ) {
			$line_item_map[ $line_item->getUniqueId() ] = $line_item;
		}

		$amount = 0;
		foreach ( $reductions as $reduction ) {
			$line_item = $line_item_map[ $reduction->getLineItemUniqueId() ];
			$amount += $line_item->getUnitPriceIncludingTax() * $reduction->getQuantityReduction();
			$amount += $reduction->getUnitPriceReduction() * ( $line_item->getQuantity() - $reduction->getQuantityReduction() );
		}

		return $amount;
	}

	/**
	 * Round amount.
	 *
	 * @param mixed $amount amount.
	 * @param mixed $currency_code currency code.
	 * @return float
	 */
	public function round_amount( $amount, $currency_code ) {
		return round( $amount, $this->get_currency_fraction_digits( $currency_code ) );
	}

	/**
	 * Get current cart id.
	 *
	 * @return array|mixed|string
	 * @throws Exception Exception.
	 */
	public function get_current_cart_id() {
		$session_handler = WC()->session;
		if ( null === $session_handler ) {
			throw new Exception( 'No session available.' );
		}
		$current_cart_id = $session_handler->get( 'postfinancecheckout_current_cart_id', null );
		if ( null === $current_cart_id ) {
			$current_cart_id = WC_PostFinanceCheckout_Unique_Id::get_uuid();
			$session_handler->set( 'postfinancecheckout_current_cart_id', $current_cart_id );
		}
		return $current_cart_id;
	}

	/**
	 * Destroy current cart id.
	 *
	 * @return void
	 */
	public function destroy_current_cart_id() {
		$session_handler = WC()->session;
		$session_handler->set( 'postfinancecheckout_current_cart_id', null );
	}

	/**
	 * Create a lock to prevent concurrency.
	 *
	 * @param int $space_id space id.
	 * @param int $transaction_id transaction id.
	 */
	public function lock_by_transaction_id( $space_id, $transaction_id ) {
		global $wpdb;

		$locked_at = gmdate( 'Y-m-d H:i:s' );
		$table_transaction_info = $wpdb->prefix . 'postfinancecheckout_transaction_info';
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$wpdb->query(
			$wpdb->prepare(
				"SELECT * FROM $table_transaction_info WHERE transaction_id = %d and space_id = %d FOR UPDATE",
				$transaction_id,
				$space_id
			)
		);

		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $table_transaction_info
					SET locked_at = %s
					WHERE transaction_id = %d AND space_id = %d",
				$locked_at,
				$transaction_id,
				$space_id
			)
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.
	}


	/**
	 * Get cleaned locale.
	 *
	 * @param mixed $use_default use default.
	 * @return string|null
	 */
	public function get_cleaned_locale( $use_default = true ) {
		$language_string = get_locale();
		return $this->get_clean_locale_for_string( $language_string, $use_default );
	}


	/**
	 * Get clean locale for string.
	 *
	 * @param mixed $language_string lanugage string.
	 * @param mixed $use_default use default.
	 * @return string|null
	 */
	public function get_clean_locale_for_string( $language_string, $use_default ) {
		$language_string = str_replace( '_', '-', $language_string );
		$language = false;
		if ( strlen( $language_string ) >= 5 ) {
			// We assume it was a long ietf code, check if it exists.
			$language = WC_PostFinanceCheckout_Provider_Language::instance()->find( $language_string );
			if ( ! $language && strpos( $language_string, '-' ) !== false ) {
				$language_parts = explode( '-', $language_string );
				array_pop( $language_parts );
				while ( ! $language && ! empty( $language_parts ) ) {
					$language = WC_PostFinanceCheckout_Provider_Language::instance()->find( implode( '-', $language_parts ) );
					array_pop( $language_parts );
				}
			}
		}
		if ( ! $language ) {
			if ( strpos( $language_string, '-' ) !== false ) {
				$language_string = strtolower( substr( $language_string, 0, strpos( $language_string, '-' ) ) );
			}
			$language = WC_PostFinanceCheckout_Provider_Language::instance()->find_by_iso_code( $language_string );
		}
		// We did not find anything, so fall back.
		if ( ! $language ) {
			if ( $use_default ) {
				return 'en-US';
			}
			return null;
		}
		return $language->getIetfCode();
	}


	/**
	 * Try to parse date.
	 *
	 * @param mixed $date_string date string.
	 * @return DateTime|false
	 */
	public function try_to_parse_date( $date_string ) {
		$date_of_birth = false;
		$custom_date_of_birth_format = apply_filters( 'wc_postfinancecheckout_custom_date_of_birth_format', '' ); //phpcs:ignore
		if ( ! empty( $custom_date_of_birth_format ) ) {
			$date_of_birth = DateTime::createFromFormat( $custom_date_of_birth_format, $date_string );
		} else {
			$date_of_birth = DateTime::createFromFormat( 'd.m.Y', $date_string );
			if ( ! $date_of_birth ) {
				$date_of_birth = DateTime::createFromFormat( 'd-m-Y', $date_string );
			}
			if ( ! $date_of_birth ) {
				$date_of_birth = DateTime::createFromFormat( 'm/d/Y', $date_string );
			}
			if ( ! $date_of_birth ) {
				$date_of_birth = DateTime::createFromFormat( 'Y-m-d', $date_string );
			}
			if ( ! $date_of_birth ) {
				$date_of_birth = DateTime::createFromFormat( 'Y/m/d', $date_string );
			}
		}
		return $date_of_birth;
	}

	/**
	 * Start database transaction.
	 *
	 * @return void
	 */
	public function start_database_transaction() {
		global $wpdb;
		$wpdb->query( 'SET TRANSACTION ISOLATION LEVEL READ COMMITTED' ); //phpcs:ignore
		wc_transaction_query( 'start' ); //phpcs:ignore
	}

	/**
	 * Commit database transaction.
	 *
	 * @return void
	 */
	public function commit_database_transaction() {
		wc_transaction_query( 'commit' ); //phpcs:ignore
	}

	/**
	 * Rollback database trnsaction.
	 *
	 * @return void
	 */
	public function rollback_database_transaction() {
		wc_transaction_query( 'rollback' ); //phpcs:ignore
	}

	/**
	 * Maybe restock items for order.
	 *
	 * @param WC_Order $order order.
	 * @return void
	 * @throws Exception Exception.
	 */
	public function maybe_restock_items_for_order( WC_Order $order ) {

		if ( version_compare( '3.5.0', WC_VERSION, '>' ) ) {
			$data_store = WC_Data_Store::load( 'order' );
			if ( $data_store->get_stock_reduced( $order->get_id() ) ) {
				$this->restock_items_for_order( $order );
				$data_store->set_stock_reduced( $order->get_id(), false );
			}
		} else {
			wc_maybe_increase_stock_levels( $order );
		}
	}

	/**
	 * Restock items for order.
	 *
	 * @param WC_Order $order order.
	 * @return void
	 */
	protected function restock_items_for_order( WC_Order $order ) {
		if (
			'yes' === get_option( 'woocommerce_manage_stock' )
			&& $order && apply_filters( 'postfinancecheckout_can_increase_order_stock', true, $order )//phpcs:ignore
			&& count( $order->get_items() ) > 0
		) {

			foreach ( $order->get_items() as $item ) {
					$product = $item->get_product();
				if ( $item->is_type( 'line_item' ) && $product && $product->managing_stock() ) {
					//phpcs:ignore
					$qty = apply_filters( 'woocommerce_order_item_quantity', $item->get_quantity(), $order, $item );
					$item_name = esc_attr( $product->get_formatted_name() );
					$new_stock = wc_update_product_stock( $product, $qty, 'increase' );
					if ( ! is_wp_error( $new_stock ) ) {
						/* translators: 1: item name 2: old stock quantity 3: new stock quantity */
						$order->add_order_note( sprintf( esc_html__( '%1$s stock increased from %2$s to %3$s.', 'woo-postfinancecheckout' ), $item_name, $new_stock - $qty, $new_stock ) );
					}
				}
			}
			do_action( 'wc_postfinancecheckout_restocked_order', $order ); //phpcs:ignore
		}
	}

	/**
	 * Retrieve the default header data.
	 *
	 * @return array Default header data.
	 */
	protected static function get_default_header_data() {
		$version = WC_VERSION;

		$shop_version = str_replace( 'v', '', $version );
		list ($major_version, $minor_version) = explode( '.', $shop_version, 3 );
		return array(
			self::POSTFINANCECHECKOUT_SHOP_SYSTEM => 'woocommerce',
			self::POSTFINANCECHECKOUT_SHOP_SYSTEM_VERSION => $shop_version,
			self::POSTFINANCECHECKOUT_SHOP_SYSTEM_AND_VERSION => 'woocommerce-' . $major_version . '.' . $minor_version,
		);
	}
}
