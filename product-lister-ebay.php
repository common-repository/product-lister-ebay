<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://cedcommerce.com
 * @since             1.0.0
 * @package           EBay_Integration_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Product Lister for eBay
 * Plugin URI:        https://cedcommerce.com
 * Description:       The Product Lister for eBay allows merchants to list their products on eBay marketplace.
 * Version:           2.0.9
 * Author:            CedCommerce
 * Author URI:        https://cedcommerce.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ebay-integration-for-woocommerce
 * Domain Path:       /languages


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CED_PRODUCT_LISTER_EBAY', '2.0.0' );
if ( ! function_exists( 'is_plugin_active' ) ) {
	require_once ABSPATH . '/wp-admin/includes/plugin.php';
}
if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	function require_woocommerce_plugin_during_activation(){?>
		<div class="notice notice-error" >
			<p> This plugin requires WooCommerce plugin to be installed.</p>
		</div>
		<?php
		@trigger_error( esc_html_e( 'This plugin requires WooCommerce plugin to be installed and activated.', 'cln' ), E_USER_ERROR );
	}

	add_action( 'network_admin_notices', 'require_woocommerce_plugin_during_activation' );
	register_activation_hook( __FILE__, 'require_woocommerce_plugin_during_activation' );
}
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woocommerce-ebay-integration-activator.php
 */
function activate_EBay_Integration_For_Woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-ebay-integration-activator.php';
	EBay_Integration_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woocommerce-ebay-integration-deactivator.php
 */
function deactivate_EBay_Integration_For_Woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-ebay-integration-deactivator.php';
	EBay_Integration_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_EBay_Integration_For_Woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_EBay_Integration_For_Woocommerce' );

/* DEFINE CONSTANTS */
define( 'CED_EBAY_LOG_DIRECTORY', wp_upload_dir()['basedir'] . '/ced_ebay_log_directory' );
define( 'CED_EBAY_VERSION', '2.0.0' );
define( 'CED_EBAY_PREFIX', 'ced_ebay' );
define( 'CED_EBAY_DIRPATH', plugin_dir_path( __FILE__ ) );
define( 'CED_EBAY_URL', plugin_dir_url( __FILE__ ) );
define( 'CED_EBAY_ABSPATH', untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-ebay-integration.php';

/**
* This file includes core functions to be used globally in plugin.
 *
* @link  http://www.cedcommerce.com/
*/
require_once plugin_dir_path( __FILE__ ) . 'includes/ced-ebay-core-functions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_EBay_Integration_For_Woocommerce() {

	$plugin = new EBay_Integration_For_Woocommerce();
	$plugin->run();

}
run_EBay_Integration_For_Woocommerce();


/* Register activation hook. */
register_activation_hook( __FILE__, 'ced_admin_notice_example_activation_hook_ced_ebay' );

/**
 * Runs only when the plugin is activated.
 *
 * @since 1.0.0
 */
function ced_admin_notice_example_activation_hook_ced_ebay() {

	/* Create transient data */
	set_transient( 'ced-ebay-admin-notice', true, 5 );
}

/*Admin admin notice */

 add_action( 'admin_notices', 'ced_ebay_admin_notice_activation' );

/**
 * Admin Notice on Activation.
 *
 * @since 0.1.0
 */


function ced_ebay_admin_notice_activation() {

	/* Check transient, if available display notice */
	if ( get_transient( 'ced-ebay-admin-notice' ) ) {
		?>
		<div class="updated notice is-dismissible">
		  <p>Welcome to eBay Integration for Woocommerce. Start listing, syncing, managing, & automating your WooCommerce and eBay Stores to boost sales.</p>
		  <a href="admin.php?page=ced_ebay" class ="ced_configuration_plugin_main">Connect to eBay</a>
		</div>
		<?php
		/* Delete transient, only display this notice once. */
		delete_transient( 'ced-ebay-admin-notice' );
	}
}

