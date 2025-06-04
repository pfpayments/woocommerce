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
 * Class WC_PostFinanceCheckout_Migration.
 * This class handles the database setup and migration.
 *
 * @class WC_PostFinanceCheckout_Migration
 */
class WC_PostFinanceCheckout_Migration {

	const POSTFINANCECHECKOUT_CK_DB_VERSION = 'wc_postfinancecheckout_db_version';

	/**
	 * Deprecated table prefix.
	 *
	 * This constant holds the deprecated table prefix. It is necessary to avoid using this prefix
	 * to stay aligned with the WordPress coding standards. The WordPress coding standards recommend
	 * using table names without custom prefixes to ensure compatibility and standardization.
	 *
	 * @deprecated Avoid using this prefix to comply with WordPress coding standards.
	 */
	const POSTFINANCECHECKOUT_DEPRECATED_TABLE_PREFIX = 'wc_';
	const POSTFINANCECHECKOUT_DEPRECATED_PLUGIN_PREFIX = 'woo-';

	/**
	 * Database migrations.
	 *
	 * @var $db_migrations database migrations.
	 */
	private static $db_migrations = array(
		'1.0.0' => 'update_1_0_0_initialize',
		'1.0.1' => 'update_1_0_1_image_url',
		'1.0.2' => 'update_1_0_2_order_allow_null',
		'1.0.3' => 'update_1_0_3_image_domain',
		'1.0.4' => 'update_1_0_4_failure_msg_and_attribute',
		'1.0.5' => 'update_1_0_5_clear_provider_transients',
		'1.0.6' => 'update_1_0_6_shorten_table_names',
		'1.0.7' => 'update_1_0_7_migrate_plugin_name_and_tables',
		'1.0.8' => 'update_1_0_8_store_default_status_mappings',
		'1.0.9' => 'update_1_0_9_restore_default_status_mappings',
	);

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action(
			'init',
			array(
				__CLASS__,
				'check_version',
			),
			5
		);
		add_action(
			'wpmu_new_blog',
			array(
				__CLASS__,
				'wpmu_new_blog',
			)
		);
		add_filter(
			'wpmu_drop_tables',
			array(
				__CLASS__,
				'wpmu_drop_tables',
			)
		);
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'supported_payments_integration_notice' ), 30 );
	}

	/**
	 * Install DB
	 *
	 * @param mixed $networkwide networkwide.
	 */
	public static function install_db( $networkwide ) {
		global $wpdb;
		if ( ! is_blog_installed() ) {
			return;
		}

		wc_maybe_define_constant( 'WC_POSTFINANCECHECKOUT_INSTALLING', true );

		self::check_requirements();

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			// check if it is a network activation - if so, run the activation function for each blog id.
			if ( $networkwide ) {
				// Get all blog ids.
				$table_blogs = $wpdb->blogs;
				// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
				$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $table_blogs" );
				// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::migrate_db();
					restore_current_blog();
				}
				return;
			}
		}
		self::migrate_db();
	}


	/**
	 * Checks if the system requirements are met
	 *
	 * Calls wp_die if requirements not met
	 */
	private static function check_requirements() {
		global $wp_version;
		require_once ABSPATH . '/wp-admin/includes/plugin.php';

		$errors = array();

		if ( version_compare( PHP_VERSION, WC_POSTFINANCECHECKOUT_REQUIRED_PHP_VERSION, '<' ) ) {
			$errors[] = sprintf( esc_html__( "PHP %1\$s+ is required. (You're running version %2\$s)", 'woo-postfinancecheckout' ), WC_POSTFINANCECHECKOUT_REQUIRED_PHP_VERSION, PHP_VERSION );
		}
		if ( version_compare( $wp_version, WC_POSTFINANCECHECKOUT_REQUIRED_WP_VERSION, '<' ) ) {
			$errors[] = sprintf( esc_html__( "WordPress %1\$s+ is required. (You're running version %2\$s)", 'woo-postfinancecheckout' ), WC_POSTFINANCECHECKOUT_REQUIRED_WP_VERSION, $wp_version );

		}

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			/* translators: %s+: version */
			$errors[] = sprintf( esc_html__( 'Woocommerce %s+ has to be active.', 'woo-postfinancecheckout' ), WC_POSTFINANCECHECKOUT_REQUIRED_WC_VERSION );
		} else {
			$woocommerce_data = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php', false, false );

			if ( version_compare( $woocommerce_data['Version'], WC_POSTFINANCECHECKOUT_REQUIRED_WC_VERSION, '<' ) ) {
				$errors[] = sprintf( esc_html__( "Woocommerce %1\$s+ is required. (You're running version %2\$s)", 'woo-postfinancecheckout' ), WC_POSTFINANCECHECKOUT_REQUIRED_WC_VERSION, $woocommerce_data['Version'] );
			}
		}

		try {
			\PostFinanceCheckout\Sdk\Http\HttpClientFactory::getClient();
		} catch ( Exception $e ) {
			$errors[] = __( "Install the PHP cUrl extension or ensure the 'stream_socket_client' function is available.", 'woo-postfinancecheckout' );
		}

		if ( ! empty( $errors ) ) {
			$error_list = '<ul><li>' . implode( '</li><li>', array_map( 'esc_html', $errors ) ) . '</li></ul>';
			wp_die(
				sprintf(
					/* translators: %s: list of requirements */
					esc_html__( 'Please check the following requirements before activating: %s', 'woo-postfinancecheckout' ),
					esc_html( $error_list )
				),
				esc_html__( 'Could not activate plugin PostFinance Checkout.', 'woo-postfinancecheckout' ),
				array( 'back_link' => true )
			);
			return;
		}
	}

	/**
	 * Create tables if new MU blog is created
	 *
	 * @param  mixed $blog_id blog id.
	 * @param  mixed $user_id user id.
	 * @param  mixed $domain domain.
	 * @param  mixed $path path.
	 * @param  mixed $site_id site id.
	 * @param  mixed $meta meta.
	 */
	public static function wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) { //phpcs:ignore

		if ( is_plugin_active_for_network( 'woo-postfinancecheckout/woocommerce-postfinancecheckout.php' ) ) {
			switch_to_blog( $blog_id );
			self::migrate_db();
			restore_current_blog();
		}
	}

	/**
	 * Migrate the database
	 */
	private static function migrate_db() {
		$current_version = get_option( self::POSTFINANCECHECKOUT_CK_DB_VERSION, 0 );
		foreach ( self::$db_migrations as $version => $function_name ) {
			if ( version_compare( $current_version, $version, '<' ) ) {

				call_user_func(
					array(
						__CLASS__,
						$function_name,
					)
				);

				update_option( self::POSTFINANCECHECKOUT_CK_DB_VERSION, $version );
				$current_version = $version;
			}
		}
	}

	/**
	 * Uninstall tables when MU blog is deleted.
	 *
	 * @param  array $tables tables.
	 * @return string[]
	 */
	public static function wpmu_drop_tables( $tables ) {
		global $wpdb;

		$tables[] = $wpdb->prefix . 'postfinancecheckout_method_config';
		$tables[] = $wpdb->prefix . 'postfinancecheckout_transaction_info';
		$tables[] = $wpdb->prefix . 'postfinancecheckout_token_info';
		$tables[] = $wpdb->prefix . 'postfinancecheckout_completion_job';
		$tables[] = $wpdb->prefix . 'postfinancecheckout_void_job';
		$tables[] = $wpdb->prefix . 'postfinancecheckout_refund_job';

		return $tables;
	}

	/**
	 * Check PostFinanceCheckout DB version and run the migration if required.
	 *
	 * This check is done on all requests and runs if he versions do not match.
	 */
	public static function check_version() {
		try {
			$current_version = get_option( self::POSTFINANCECHECKOUT_CK_DB_VERSION, 0 );
			$version_keys = array_keys( self::$db_migrations );
			if ( version_compare( $current_version, '0', '>' ) && version_compare( $current_version, end( $version_keys ), '<' ) ) {
				// We migrate the Db for all blogs.
				self::install_db( true );
			}
		} catch ( Exception $e ) {
			if ( is_admin() ) {
				add_action(
					'admin_notices',
					array(
						'WC_PostFinanceCheckout_Admin_Notices',
						'migration_failed_notices',
					)
				);
			}
		}
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param   mixed $links Plugin Row Meta.
	 * @param   mixed $file  Plugin Base file.
	 * @return  array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( WC_POSTFINANCECHECKOUT_PLUGIN_BASENAME === $file ) {
			$row_meta = array(
				'docs' => '<a href="https://plugin-documentation.postfinance-checkout.ch/pfpayments/woocommerce/3.3.12/docs/en/documentation.html" aria-label="' . esc_html__( 'View Documentation', 'woo-postfinancecheckout' ) . '">' . esc_html__( 'Documentation', 'woo-postfinancecheckout' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Initialise for update 1.0.0.
	 *
	 * @throws Exception Exception.
	 */
	public static function update_1_0_0_initialize() {
		global $wpdb;

		$table_method_config = $wpdb->prefix . 'postfinancecheckout_method_config';
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$sql = "CREATE TABLE IF NOT EXISTS $table_method_config(
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`configuration_id` bigint(20) unsigned NOT NULL,
				`configuration_name` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
				`title` longtext COLLATE utf8_unicode_ci,
				`description` longtext COLLATE utf8_unicode_ci,
				`image` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `unq_space_id_configuration_id` (`space_id`,`configuration_id`),
				KEY `idx_space_id` (`space_id`),
				KEY `idx_configuration_id` (`configuration_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		// phpcs:enable
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery -- $sql is prepared.
		$result = $wpdb->query( $sql );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCachin,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery

		if ( false === $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}

		$table_transaction_info = $wpdb->prefix . 'postfinancecheckout_transaction_info';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query(
			"CREATE TABLE IF NOT EXISTS $table_transaction_info(
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`transaction_id` bigint(20) unsigned NOT NULL,
				`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`space_view_id` bigint(20) unsigned DEFAULT NULL,
				`language` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`currency` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`authorization_amount` decimal(19,8) NOT NULL,
				`image` varchar(512) COLLATE utf8_unicode_ci DEFAULT NULL,
				`labels` longtext COLLATE utf8_unicode_ci,
				`payment_method_id` bigint(20) unsigned DEFAULT NULL,
				`connector_id` bigint(20) unsigned DEFAULT NULL,
				`order_id` int(10) unsigned NOT NULL,
				`failure_reason` longtext COLLATE utf8_unicode_ci,
				`locked_at` datetime,
				PRIMARY KEY (`id`),
				UNIQUE KEY `unq_transaction_id_space_id` (`transaction_id`,`space_id`),
				UNIQUE KEY `unq_order_id` (`order_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( false === $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}

		$table_token_info = $wpdb->prefix . 'postfinancecheckout_token_info';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query(
			"CREATE TABLE IF NOT EXISTS $table_token_info(
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`token_id` bigint(20) unsigned NOT NULL,
				`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`customer_id` int(10) unsigned NOT NULL,
				`payment_method_id` int(10) unsigned NOT NULL,
				`connector_id` bigint(20) unsigned DEFAULT NULL,
				PRIMARY KEY (`id`),
				UNIQUE KEY `unq_transaction_id_space_id` (`token_id`,`space_id`),
				KEY `idx_customer_id` (`customer_id`),
				KEY `idx_payment_method_id` (`payment_method_id`),
				KEY `idx_connector_id` (`connector_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( false === $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}

		$table_completion_job = $wpdb->prefix . 'postfinancecheckout_completion_job';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query(
			"CREATE TABLE IF NOT EXISTS $table_completion_job(
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				`completion_id` bigint(20) unsigned,
				`transaction_id` bigint(20) unsigned NOT NULL,
				`space_id` bigint(20) unsigned NOT NULL,
				`order_id` bigint(20) unsigned NOT NULL,
				`amount` decimal(19,8) NOT NULL,
				`created_at` datetime NOT NULL,
				`updated_at` datetime NOT NULL,
				`restock` varchar(1) COLLATE utf8_unicode_ci,
				`items` longtext COLLATE utf8_unicode_ci,
				`failure_reason` longtext COLLATE utf8_unicode_ci,
				PRIMARY KEY (`id`),
				KEY `idx_transaction_id_space_id` (`transaction_id`,`space_id`),
				KEY `idx_completion_id_space_id` (`completion_id`,`space_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( false === $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}

		$table_void_job = $wpdb->prefix . 'postfinancecheckout_void_job';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query(
			"CREATE TABLE IF NOT EXISTS $table_void_job(
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			`void_id` bigint(20) unsigned,
			`transaction_id` bigint(20) unsigned NOT NULL,
			`space_id` bigint(20) unsigned NOT NULL,
			`order_id` bigint(20) unsigned NOT NULL,
			`created_at` datetime NOT NULL,
			`updated_at` datetime NOT NULL,
			`restock` varchar(1) COLLATE utf8_unicode_ci,
			`failure_reason` longtext COLLATE utf8_unicode_ci,
			PRIMARY KEY (`id`),
			KEY `idx_transaction_id_space_id` (`transaction_id`,`space_id`),
			KEY `idx_void_id_space_id` (`void_id`,`space_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( false === $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}

		$table_refund_job = $wpdb->prefix . 'postfinancecheckout_refund_job';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query(
			"CREATE TABLE IF NOT EXISTS $table_refund_job(
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`state` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
			`wc_refund_id` bigint(20) unsigned NOT NULL,
			`external_id` varchar(100) NOT NULL,
			`transaction_id` bigint(20) unsigned NOT NULL,
			`space_id` bigint(20) unsigned NOT NULL,
			`order_id` bigint(20) unsigned NOT NULL,
			`created_at` datetime NOT NULL,
			`updated_at` datetime NOT NULL,
			`refund` longtext COLLATE utf8_unicode_ci,
			`failure_reason` longtext COLLATE utf8_unicode_ci,
			PRIMARY KEY (`id`),
			KEY `idx_transaction_id_space_id` (`transaction_id`,`space_id`),
			KEY `idx_external_id_space_id` (`external_id`,`space_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( false === $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}
	}

	/**
	 * Update image url for update 1.0.1.
	 *
	 * @throws Exception Exception.
	 */
	public static function update_1_0_1_image_url() {
		global $wpdb;

		$table_transaction_info = $wpdb->prefix . 'postfinancecheckout_transaction_info';
		$table_method_config = $wpdb->prefix . 'postfinancecheckout_method_config';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query( "ALTER TABLE $table_method_config CHANGE `image` `image` VARCHAR(2047) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( false === $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query( "ALTER TABLE $table_transaction_info CHANGE `image` `image` VARCHAR(2047) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( false === $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}
	}

	/**
	 * Allow order NULL for update 1.0.2.
	 *
	 * @throws Exception Exception.
	 */
	public static function update_1_0_2_order_allow_null() {
		global $wpdb;

		$table_transaction_info = $wpdb->prefix . 'postfinancecheckout_transaction_info';
		$table_token_info = $wpdb->prefix . 'postfinancecheckout_token_info';

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query( "ALTER TABLE $table_transaction_info CHANGE `order_id` `order_id` int(10) unsigned NULL DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( false === $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query(
			$wpdb->prepare( "SHOW COLUMNS FROM $table_transaction_info LIKE %s", 'order_mapping_id' )
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( 0 === $result ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
			$result = $wpdb->query( "ALTER TABLE $table_transaction_info ADD `order_mapping_id` int(10) unsigned NULL AFTER order_id" );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCachin,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery

			if ( false === $result ) {
				throw new Exception( esc_html( $wpdb->last_error ) );
			}
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query( "ALTER TABLE $table_token_info CHANGE `customer_id` `customer_id` int(10) unsigned NULL DEFAULT NULL" );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.NoCachin,WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery

		if ( false === $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}
	}

	/**
	 * Add image base fpr update 1.0.3.
	 *
	 * @throws Exception Exception.
	 */
	public static function update_1_0_3_image_domain() {
		global $wpdb;

		$table_transaction_info = $wpdb->prefix . 'postfinancecheckout_transaction_info'; //phpcs:ignore
		$table_method_config = $wpdb->prefix . 'postfinancecheckout_method_config'; //phpcs:ignore

		$result = $wpdb->query( $wpdb->prepare( "SHOW COLUMNS FROM $table_method_config LIKE %s", 'image_base' ) );//phpcs:ignore

		if ( 0 === $result ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
			$result = $wpdb->query( "ALTER TABLE $table_method_config ADD COLUMN `image_base` VARCHAR(2047) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER image" );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

			if ( false === $result ) {
				throw new Exception( esc_html( $wpdb->last_error ) );
			}
		}

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query( $wpdb->prepare( "SHOW COLUMNS FROM $table_transaction_info LIKE %s", 'image_base' ) );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( 0 === $result ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
			$result = $wpdb->query( "ALTER TABLE $table_transaction_info ADD COLUMN `image_base` VARCHAR(2047) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER image" );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

			if ( false === $result ) {
				throw new Exception( esc_html( $wpdb->last_error ) );
			}
		}
	}

	/**
	 * Add failure message and attribute for update 1.0.4.
	 *
	 * @throws Exception Exception.
	 */
	public static function update_1_0_4_failure_msg_and_attribute() {
		global $wpdb;

		$table_transaction_info = $wpdb->prefix . 'postfinancecheckout_transaction_info'; //phpcs:ignore
		$table_attribute_options = $wpdb->prefix . 'postfinancecheckout_attribute_options'; //phpcs:ignore

		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query( $wpdb->prepare( "SHOW COLUMNS FROM $table_transaction_info LIKE %s", 'user_failure_message' ) );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( 0 === $result ) {
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
			$result = $wpdb->query( "ALTER TABLE $table_transaction_info ADD COLUMN `user_failure_message` VARCHAR(2047) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER failure_reason" );
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

			if ( false === $result ) {
				throw new Exception( esc_html( $wpdb->last_error ) );
			}
		}
		// Do not use foreign keys to reference attribute to cascade deletion, as some shop still run with MyISAM enginge as default.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$result = $wpdb->query(
			"CREATE TABLE IF NOT EXISTS $table_attribute_options(
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`attribute_id` bigint(20) UNSIGNED NOT NULL,
				`send` varchar(1) COLLATE utf8_unicode_ci,
				PRIMARY KEY (`id`),
				UNIQUE `unq_attribute_id` (`attribute_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

		if ( false == $result ) {
			throw new Exception( esc_html( $wpdb->last_error ) );
		}
	}

	/**
	 * Delete provider transients.
	 */
	public static function update_1_0_5_clear_provider_transients() {
		WC_PostFinanceCheckout_Helper::instance()->delete_provider_transients();
	}

	/**
	 * Shorten table names on update 1.0.6.
	 *
	 * @throws Exception Exception.
	 */
	public static function update_1_0_6_shorten_table_names() {
		global $wpdb;
		// old table names format => new table names format.
		$table_prefix = self::POSTFINANCECHECKOUT_DEPRECATED_TABLE_PREFIX;
		$table_rename_map = array(
			"{$wpdb->prefix}{$table_prefix}woocommerce_postfinancecheckout_attribute_options" => "{$wpdb->prefix}{$table_prefix}postfinancecheckout_attribute_options",
			"{$wpdb->prefix}{$table_prefix}woocommerce_postfinancecheckout_completion_job" => "{$wpdb->prefix}{$table_prefix}postfinancecheckout_completion_job",
			"{$wpdb->prefix}{$table_prefix}woocommerce_postfinancecheckout_method_configuration" => "{$wpdb->prefix}{$table_prefix}postfinancecheckout_method_config",
			"{$wpdb->prefix}{$table_prefix}woocommerce_postfinancecheckout_refund_job" => "{$wpdb->prefix}{$table_prefix}postfinancecheckout_refund_job",
			"{$wpdb->prefix}{$table_prefix}woocommerce_postfinancecheckout_token_info" => "{$wpdb->prefix}{$table_prefix}postfinancecheckout_token_info",
			"{$wpdb->prefix}{$table_prefix}woocommerce_postfinancecheckout_transaction_info" => "{$wpdb->prefix}{$table_prefix}postfinancecheckout_transaction_info",
			"{$wpdb->prefix}{$table_prefix}woocommerce_postfinancecheckout_void_job" => "{$wpdb->prefix}{$table_prefix}postfinancecheckout_void_job",
		);

		self::rename_tables( $table_rename_map );
	}



	/**
	 * Rename plugin tables and update plugin name.
	 *
	 * This function renames the tables and updates the plugin name
	 * to ensure a smooth upgrade without duplicating the plugin installation, version 4.0.0.
	 *
	 * @throws Exception Exception.
	 */
	public static function update_1_0_7_migrate_plugin_name_and_tables() {
		global $wpdb;
		// old table names format => new table names format.
		$table_prefix = self::POSTFINANCECHECKOUT_DEPRECATED_TABLE_PREFIX;
		$table_rename_map = array(
			"{$wpdb->prefix}{$table_prefix}postfinancecheckout_attribute_options" => "{$wpdb->prefix}postfinancecheckout_attribute_options",
			"{$wpdb->prefix}{$table_prefix}postfinancecheckout_completion_job" => "{$wpdb->prefix}postfinancecheckout_completion_job",
			"{$wpdb->prefix}{$table_prefix}postfinancecheckout_method_config" => "{$wpdb->prefix}postfinancecheckout_method_config",
			"{$wpdb->prefix}{$table_prefix}postfinancecheckout_refund_job" => "{$wpdb->prefix}postfinancecheckout_refund_job",
			"{$wpdb->prefix}{$table_prefix}postfinancecheckout_token_info" => "{$wpdb->prefix}postfinancecheckout_token_info",
			"{$wpdb->prefix}{$table_prefix}postfinancecheckout_transaction_info" => "{$wpdb->prefix}postfinancecheckout_transaction_info",
			"{$wpdb->prefix}{$table_prefix}postfinancecheckout_void_job" => "{$wpdb->prefix}postfinancecheckout_void_job",
		);
		self::rename_tables( $table_rename_map );
	}

	/**
	 * Shows a notice in the admin section if the WooCommerce version installed is not officially yet supported by us.
	 *
	 * @return void
	 */
	public static function supported_payments_integration_notice(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action(
				'admin_notices',
				function () {
					?>
					<div class="notice notice-error">
						<p><?php __( 'WooCommerce is not activated. Please activate WooCommerce to use the payment integration.', 'woo-postfinancecheckout' ); ?></p>
					</div>
					<?php
				}
			);
			return;
		}
		$woocommerce_data = get_plugin_data( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php', false, false );
		if ( version_compare( $woocommerce_data['Version'], WC_POSTFINANCECHECKOUT_REQUIRED_WC_MAXIMUM_VERSION, '>' ) ) {
			$notice_id = "postfinancecheckout-{$woocommerce_data['Version']}-not-yet-supported";
			if ( ! WC_Admin_Notices::user_has_dismissed_notice( $notice_id ) ) {
				$message = sprintf(
					/* translators: 1: Required WooCommerce version, 2: Installed WooCommerce version */
					__( 'The plugin PostFinanceCheckout has been tested up to WooCommerce %1$s but you have installed the version %2$s. Please notice that this is not recommended.', 'woo-postfinancecheckout' ),
					WC_POSTFINANCECHECKOUT_REQUIRED_WC_MAXIMUM_VERSION,
					$woocommerce_data['Version']
				);
				WC_Admin_Notices::add_custom_notice( $notice_id, esc_html( $message ) );

				// Clean up previous dismissals stored in the user data, from previous versions.
				$previous_version = get_user_meta( get_current_user_id(), 'postfinancecheckout-previous-wc-min-version' );
				if ( $previous_version ) {
					delete_user_meta( get_current_user_id(), "dismissed_postfinancecheckout-{$previous_version[0]}-not-yet-supported_notice" );
				}
				update_user_meta( get_current_user_id(), 'postfinancecheckout-previous-wc-min-version', $woocommerce_data['Version'] );
			}
		}
	}

	/**
	 * Rename tables based on the mapping.
	 *
	 * @param array $table_rename_map Array of old table names => new table names.
	 * @throws Exception Exception.
	 */
	private static function rename_tables( $table_rename_map ) {
		global $wpdb;
		// rollback table rename if there are issues.
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
		$wpdb->query( 'START TRANSACTION' ); //phpcs:ignore
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.
		$table_stament = 'Tables_in_' . $wpdb->dbname;

		foreach ( $table_rename_map as $old_table_name => $new_table_name ) {
			// check old table exists.
			// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
			$old_table_result = $wpdb->get_row(
				$wpdb->prepare( "SHOW TABLES WHERE $table_stament = %s", $old_table_name )
			);
			// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

			if ( $old_table_result ) {
				// check new table doesn't exist, if it does there's a problem!.
				// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
				$new_table_result = $wpdb->get_row(
					$wpdb->prepare( "SHOW TABLES WHERE $table_stament = %s", $new_table_name )
				);
				// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.

				if ( ! $new_table_result ) {
					// phpcs:disable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Values are escaped in $wpdb->prepare.
					$result = $wpdb->query( "RENAME TABLE $old_table_name TO $new_table_name" );
					// phpcs:enable WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare.
					if ( false === $result ) {
						throw new Exception( esc_html( $wpdb->last_error ) );
					}
				}
			}
		}

		$wpdb->query( 'COMMIT' ); //phpcs:ignore
	}

	/**
	 * Store default order status mappings in the database during migration.
	 * Ensures that the default order statuses are properly set.
	 */
	public static function update_1_0_8_store_default_status_mappings() {
		$status_adapter = new WC_PostFinanceCheckout_Order_Status_Adapter();
		$status_adapter->store_default_status_mappings_on_database();
	}



	/**
	 * Store default order status mappings in the database during migration.
	 * Ensures that the default order statuses are properly set.
	 */
	public static function update_1_0_9_restore_default_status_mappings() {
		$status_adapter = new WC_PostFinanceCheckout_Order_Status_Adapter();
		$status_adapter->store_default_status_mappings_on_database();
	}
}

WC_PostFinanceCheckout_Migration::init();
