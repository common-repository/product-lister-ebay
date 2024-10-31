<?php

/**
 * Fired during plugin activation
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    EBay_Integration_For_Woocommerce
 * @subpackage EBay_Integration_For_Woocommerce/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    EBay_Integration_For_Woocommerce
 * @subpackage EBay_Integration_For_Woocommerce/includes
 */
class EBay_Integration_For_Woocommerce_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$tableName = $wpdb->prefix . 'ced_ebay_accounts';

		$create_accounts_table =
			"CREATE TABLE $tableName (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			account_status VARCHAR(255) NOT NULL,
			site_id BIGINT(20) DEFAULT NULL,
			location VARCHAR(50) NOT NULL,
			account_data TEXT DEFAULT NULL,
			user_id VARCHAR(255) DEFAULT NULL,
			user_email VARCHAR(255) DEFAULT NULL,
			user_EIASToken TEXT DEFAULT NULL,
			user_data TEXT DEFAULT NULL,
			is_store VARCHAR(255) DEFAULT NULL,
			store_url VARCHAR(255) DEFAULT NULL,
			store_name VARCHAR(255) DEFAULT NULL,
			store_data TEXT DEFAULT NULL,
			payment_profile TEXT DEFAULT NULL,
			return_profile TEXT DEFAULT NULL,
			order_sync VARCHAR(255) DEFAULT NULL,
			inventory_sync VARCHAR(255) DEFAULT NULL,
			product_sync VARCHAR(255) DEFAULT NULL,
			product_auto_upload VARCHAR(255) DEFAULT NULL,
			last_order_sync datetime DEFAULT NULL,
			PRIMARY KEY (id)
		);";
		dbDelta( $create_accounts_table );
		$tableName = $wpdb->prefix . 'ced_ebay_discription_template';

		$create_discription_template_table =
			"CREATE TABLE $tableName (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			template_name VARCHAR(255) NOT NULL,
			template_html LONGTEXT NOT NULL,
			template_css  LONGTEXT NOT NULL,
			user_id		  VARCHAR(255) NOT NULL,
			PRIMARY KEY (id));";
		dbDelta( $create_discription_template_table );
		$tableName = $wpdb->prefix . 'ced_ebay_profiles';

		$create_profile_table =
			"CREATE TABLE $tableName (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			profile_name VARCHAR(255) NOT NULL,
			profile_status VARCHAR(255) NOT NULL,
			user_id VARCHAR(255) DEFAULT NULL,
			profile_data TEXT DEFAULT NULL,
			woo_categories TEXT DEFAULT NULL,
			PRIMARY KEY (id)
		);";
		dbDelta( $create_profile_table );

		$tableName = $wpdb->prefix . 'ced_ebay_shipping';

		$create_shipping_table =
			"CREATE TABLE $tableName (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			shipping_name VARCHAR(255) NOT NULL,
			weight_range VARCHAR(255) NOT NULL,
			user_id VARCHAR(255) DEFAULT NULL,
			shipping_data TEXT DEFAULT NULL,
			PRIMARY KEY (id)
		);";
		dbDelta( $create_shipping_table );

		$tableName = $wpdb->prefix . 'ced_ebay_logs';

		$create_logs_table =
			"CREATE TABLE $tableName (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			call_name VARCHAR(255) NOT NULL,
			request TEXT DEFAULT NULL,
			response TEXT DEFAULT NULL,
			shop_url VARCHAR(255) DEFAULT NULL,
			site_id VARCHAR(255) DEFAULT NULL,
			request_time datetime DEFAULT NULL,
			PRIMARY KEY (id)
		);";
		dbDelta( $create_logs_table );
	}

}
