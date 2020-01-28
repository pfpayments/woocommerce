<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://www.postfinance.ch/checkout).
 *
 * @author customweb GmbH (http://www.customweb.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * This class handles the database setup and migration.
 */
class WC_PostFinanceCheckout_Migration {
    
    const CK_DB_VERSION = 'wc_postfinancecheckout_db_version';
    
	private static $db_migrations = array(
		'1.0.0' => 'update_1_0_0_initialize', 
		'1.0.1' => 'update_1_0_1_image_url',
	    '1.0.2' => 'update_1_0_2_order_allow_null',
	    '1.0.3' => 'update_1_0_3_image_domain',
	    '1.0.4' => 'update_1_0_4_failure_msg_and_attribute',
		'1.0.5' => 'update_1_0_5_clear_provider_transients',
	);

	/**
	 * Hook in tabs.
	 */
	public static function init(){
		add_action('init', array(
			__CLASS__,
			'check_version' 
		), 5);
		add_action('wpmu_new_blog', array(
			__CLASS__,
			'wpmu_new_blog' 
		));
		add_filter('wpmu_drop_tables', array(
			__CLASS__,
			'wpmu_drop_tables' 
		));
		add_action('in_plugin_update_message-woo-postfinancecheckout/woocommerce-postfinancecheckout.php', array(
			__CLASS__,
			'in_plugin_update_message' 
		));
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
	}

	public static function install_db($networkwide){
		global $wpdb;
		if (!is_blog_installed()) {
			return;
		}
		
		wc_maybe_define_constant('WC_POSTFINANCECHECKOUT_INSTALLING', true);
		
		self::check_requirements();
		
		if (function_exists('is_multisite') && is_multisite()) {
			// check if it is a network activation - if so, run the activation function for each blog id
			if ($networkwide) {
				// Get all blog ids
				$blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
				foreach ($blog_ids as $blog_id) {
					switch_to_blog($blog_id);
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
	 * calls wp_die f requirements not met
	 */
	private static function check_requirements() {
		global $wp_version;
		require_once( ABSPATH . '/wp-admin/includes/plugin.php' ) ;
		
		$errors = array();
		
		if ( version_compare( PHP_VERSION, WC_POSTFINANCECHECKOUT_REQUIRED_PHP_VERSION, '<' ) ) {
		    $errors[] = sprintf(__("PHP %s+ is required. (You're running version %s)", "woo-postfinancecheckout"), WC_POSTFINANCECHECKOUT_REQUIRED_PHP_VERSION, PHP_VERSION);
		}
		if ( version_compare( $wp_version, WC_POSTFINANCECHECKOUT_REQUIRED_WP_VERSION, '<' ) ) {
		    $errors[] = sprintf(__("Wordpress %s+ is required. (You're running version %s)", "woo-postfinancecheckout"), WC_POSTFINANCECHECKOUT_REQUIRED_WP_VERSION, $wp_version);
			
		}
		
		if (!is_plugin_active('woocommerce/woocommerce.php')){
		    $errors[] = sprintf(__("Woocommerce %s+ has to be active.", "woo-postfinancecheckout"), WC_POSTFINANCECHECKOUT_REQUIRED_WC_VERSION);
		}
		else{
			$woocommerce_data = get_plugin_data(WP_PLUGIN_DIR .'/woocommerce/woocommerce.php', false, false);
			
			if (version_compare ($woocommerce_data['Version'] , WC_POSTFINANCECHECKOUT_REQUIRED_WC_VERSION, '<')){
			    $errors[] = sprintf(__("Woocommerce %s+ is required. (You're running version %s)", "woo-postfinancecheckout"), WC_POSTFINANCECHECKOUT_REQUIRED_WC_VERSION, $woocommerce_data['Version']);
			}
		}
		
		try{
		    \PostFinanceCheckout\Sdk\Http\HttpClientFactory::getClient();
		}
		catch(Exception $e){
			$errors[] = __("Install the PHP cUrl extension or ensure the 'stream_socket_client' function is available.");
		}
		
		if(!empty($errors)){
			$title = __('Could not activate plugin WooCommerce PostFinance Checkout.', 'woo-postfinancecheckout');
			$message = '<h1><strong>'.$title.'</strong></h1><br/>'.
					'<h3>'.__('Please check the following requirements before activating:', 'woo-postfinancecheckout').'</h3>'.
					'<ul><li>'.
					implode('</li><li>', $errors).
					'</li></ul>';
					
			 
			wp_die($message, $title, array('back_link' => true));
			return;
		}
	}
	
	/**
	 * Create tables if new MU blog is created
	 * @param  array $tables
	 * @return string[]
	 */
	public static function wpmu_new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta){

		if (is_plugin_active_for_network('woo-postfinancecheckout/woocommerce-postfinancecheckout.php')) {
			switch_to_blog($blog_id);
			self::migrate_db();
			restore_current_blog();
		}
	}

	private static function migrate_db(){
	    $current_version = get_option(self::CK_DB_VERSION, 0);
		foreach (self::$db_migrations as $version => $function_name) {
			if (version_compare($current_version, $version, '<')) {
				
				call_user_func(array(
					__CLASS__,
					$function_name 
				));
				
				update_option(self::CK_DB_VERSION, $version);
				$current_version = $version;
			}
		}
	}

	/**
	 * Uninstall tables when MU blog is deleted.
	 * @param  array $tables
	 * @return string[]
	 */
	public static function wpmu_drop_tables($tables){
		global $wpdb;
		
		$tables[] = $wpdb->prefix . 'woocommerce_postfinancecheckout_method_configuration';
		$tables[] = $wpdb->prefix . 'woocommerce_postfinancecheckout_transaction_info';
		$tables[] = $wpdb->prefix . 'woocommerce_postfinancecheckout_token_info';
		$tables[] = $wpdb->prefix . 'woocommerce_postfinancecheckout_completion_job';
		$tables[] = $wpdb->prefix . 'woocommerce_postfinancecheckout_void_job';
		$tables[] = $wpdb->prefix . 'woocommerce_postfinancecheckout_refund_job';
		
		return $tables;
	}

	/**
	 * Check PostFinanceCheckout DB version and run the migration if required.
	 *
	 * This check is done on all requests and runs if he versions do not match.
	 */
	public static function check_version(){
		try {
			$current_version = get_option(self::CK_DB_VERSION, 0);
			$version_keys = array_keys(self::$db_migrations);
			if (version_compare($current_version, '0', '>') && version_compare($current_version, end($version_keys), '<')) {
				//We migrate the Db for all blogs
				self::install_db(true);
			}
		}
		catch (Exception $e) {
			if (is_admin()) {
				add_action('admin_notices', array(
					'WC_PostFinanceCheckout_Admin_Notices',
					'migration_failed_notices' 
				));
			}
		}
	}

	/**
	 * Show plugin changes. Code adapted from W3 Total Cache.
	 */
	public static function in_plugin_update_message($args){
		$transient_name = 'postfinancecheckout_upgrade_notice_' . $args['Version'];
		
		if (false === ($upgrade_notice = get_transient($transient_name))) {
			$response = wp_safe_remote_get('https://plugins.svn.wordpress.org/woo-postfinancecheckout/trunk/readme.txt');
			
			if (!is_wp_error($response) && !empty($response['body'])) {
				$upgrade_notice = self::parse_update_notice($response['body'], $args['new_version']);
				set_transient($transient_name, $upgrade_notice, DAY_IN_SECONDS);
			}
		}
		echo wp_kses_post($upgrade_notice);
	}

	/**
	 * Parse update notice from readme file.
	 *
	 * @param  string $content
	 * @param  string $new_version
	 * @return string
	 */
	private static function parse_update_notice($content, $new_version){
		// Output Upgrade Notice.
		$matches = null;
		$regexp = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote(WC_POSTFINANCECHECKOUT_VERSION) . '\s*=|$)~Uis';
		$upgrade_notice = '';
		
		if (preg_match($regexp, $content, $matches)) {
			$version = trim($matches[1]);
			$notices = (array) preg_split('~[\r\n]+~', trim($matches[2]));
			
			// Check the latest stable version and ignore trunk.
			if ($version === $new_version && version_compare(WC_POSTFINANCECHECKOUT_VERSION, $version, '<')) {
				$upgrade_notice .= '<div class="plugin_upgrade_notice">';
				foreach ($notices as $line) {
					$upgrade_notice .= wp_kses_post(preg_replace('~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line));
				}
				$upgrade_notice .= '</div> ';
			}
		}
		
		return wp_kses_post($upgrade_notice);
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
	            'docs' => '<a href="https://plugin-documentation.postfinance-checkout.ch/pfpayments/woocommerce/1.3.1/docs/en/documentation.html" aria-label="' . esc_attr__('View Documentation', 'woo-postfinancecheckout') . '">' . esc_html__('Documentation', 'woo-postfinancecheckout') . '</a>',
	        );
	        
	        return array_merge( $links, $row_meta );
	    }
	    
	    return (array) $links;
	}
	

	public static function update_1_0_0_initialize(){
		global $wpdb;
		
		$result = $wpdb->query(
				"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_postfinancecheckout_method_configuration(
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
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
		
		if ($result === false) {
			throw new Exception($wpdb->last_error);
		}
		
		$result = $wpdb->query(
				"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_postfinancecheckout_transaction_info(
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
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
		
		if ($result === false) {
			throw new Exception($wpdb->last_error);
		}
		
		$result = $wpdb->query(
				"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_postfinancecheckout_token_info(
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
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
		
		if ($result === false) {
			throw new Exception($wpdb->last_error);
		}
		$result = $wpdb->query(
				"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_postfinancecheckout_completion_job(
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
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
		
		if ($result === false) {
			throw new Exception($wpdb->last_error);
		}
		
		$result = $wpdb->query(
				"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_postfinancecheckout_void_job(
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
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
		
		if ($result === false) {
			throw new Exception($wpdb->last_error);
		}
		
		$result = $wpdb->query(
				"CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_postfinancecheckout_refund_job(
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
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
		
		if ($result === false) {
			throw new Exception($wpdb->last_error);
		}
	}
	
	public static function update_1_0_1_image_url(){
		global $wpdb;
		$result = $wpdb->query(
				"ALTER TABLE `{$wpdb->prefix}woocommerce_postfinancecheckout_method_configuration` CHANGE `image` `image` VARCHAR(2047) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;");
		if ($result === false) {
			throw new Exception($wpdb->last_error);
		}
		
		$result = $wpdb->query(
				"ALTER TABLE `{$wpdb->prefix}woocommerce_postfinancecheckout_transaction_info` CHANGE `image` `image` VARCHAR(2047) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;");
		if ($result === false) {
			throw new Exception($wpdb->last_error);
		}
	}
	
	public static function update_1_0_2_order_allow_null(){
	    global $wpdb;
	    $result = $wpdb->query(
	        "ALTER TABLE `{$wpdb->prefix}woocommerce_postfinancecheckout_transaction_info` CHANGE `order_id` `order_id` int(10) unsigned NULL DEFAULT NULL;");
	    if ($result === false) {
	        throw new Exception($wpdb->last_error);
	    }
	    
	    
	    $result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}woocommerce_postfinancecheckout_transaction_info` LIKE 'order_mapping_id'");
	    if ($result == 0) {
	        $result = $wpdb->query(
	            "ALTER TABLE `{$wpdb->prefix}woocommerce_postfinancecheckout_transaction_info` ADD `order_mapping_id` int(10) unsigned NULL AFTER order_id;");
	        if ($result === false) {
	            throw new Exception($wpdb->last_error);
	        }
	    }	    
	    $result = $wpdb->query(
	        "ALTER TABLE `{$wpdb->prefix}woocommerce_postfinancecheckout_token_info` CHANGE `customer_id` `customer_id` int(10) unsigned NULL DEFAULT NULL;");
	    
	    if ($result === false) {
	        throw new Exception($wpdb->last_error);
	    }
	}
	
	public static function update_1_0_3_image_domain(){
	    global $wpdb;
	    
	    $result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}woocommerce_postfinancecheckout_method_configuration` LIKE 'image_base'");
	    if ($result == 0) {
	        $result = $wpdb->query(
	            "ALTER TABLE `{$wpdb->prefix}woocommerce_postfinancecheckout_method_configuration` ADD COLUMN `image_base` VARCHAR(2047) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER image;");
	        if ($result === false) {
	            throw new Exception($wpdb->last_error);
	        }
	    }
	    
	    $result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}woocommerce_postfinancecheckout_transaction_info` LIKE 'image_base'");
	    if ($result == 0) {
	        $result = $wpdb->query(
	            "ALTER TABLE `{$wpdb->prefix}woocommerce_postfinancecheckout_transaction_info` ADD COLUMN `image_base` VARCHAR(2047) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER image;");
	        if ($result === false) {
	            throw new Exception($wpdb->last_error);
	        }
	    }
	}
	
	public static function update_1_0_4_failure_msg_and_attribute(){
	    global $wpdb;
	    
	    $result = $wpdb->query("SHOW COLUMNS FROM `{$wpdb->prefix}woocommerce_postfinancecheckout_transaction_info` LIKE 'user_failure_message'");
	    if ($result == 0) {
	        $result = $wpdb->query(
	            "ALTER TABLE `{$wpdb->prefix}woocommerce_postfinancecheckout_transaction_info` ADD COLUMN `user_failure_message` VARCHAR(2047) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL AFTER failure_reason;");
	        if ($result === false) {
	            throw new Exception($wpdb->last_error);
	        }
	    }
	    //Do not use foreign keys to reference attribute to cascade deletion, as some shop still run with MyISAM enginge as default.
	    $result = $wpdb->query(
	        "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}woocommerce_postfinancecheckout_attribute_options(
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `attribute_id` bigint(20) UNSIGNED NOT NULL,
				`send` varchar(1) COLLATE utf8_unicode_ci,
				PRIMARY KEY (`id`),
				UNIQUE `unq_attribute_id` (`attribute_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci");
	    if ($result === false) {
	        throw new Exception($wpdb->last_error);
	    }
	}
	
	public static function update_1_0_5_clear_provider_transients() {
		WC_PostFinanceCheckout_Helper::instance()->delete_provider_transients();
	}
}

WC_PostFinanceCheckout_Migration::init();
