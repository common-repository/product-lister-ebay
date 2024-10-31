<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    EBay_Integration_For_Woocommerce
 * @subpackage EBay_Integration_For_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    EBay_Integration_For_Woocommerce
 * @subpackage EBay_Integration_For_Woocommerce/includes
 */
class EBay_Integration_For_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0

	 * @var      EBay_Integration_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0

	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0

	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ebay-integration-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - EBay_Integration_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - EBay_Integration_For_Woocommerce_I18n. Defines internationalization functionality.
	 * - EBay_Integration_For_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - EBay_Integration_For_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-ebay-integration-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-ebay-integration-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-ebay-integration-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */

		$this->loader = new EBay_Integration_For_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the EBay_Integration_For_Woocommerce_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 */
	private function set_locale() {

		$plugin_i18n = new EBay_Integration_For_Woocommerce_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 */
	private function define_admin_hooks() {

		$plugin_admin = new EBay_Integration_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ced_ebay_add_menus', 22 );
		$this->loader->add_filter( 'ced_add_marketplace_menus_array', $plugin_admin, 'ced_ebay_add_marketplace_menus_to_array', 13 );
		$this->loader->add_action( 'wp_ajax_ced_ebay_fetch_next_level_category', $plugin_admin, 'ced_ebay_fetch_next_level_category' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_map_categories_to_store', $plugin_admin, 'ced_ebay_map_categories_to_store' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_category_refresh_button', $plugin_admin, 'ced_ebay_category_refresh_button' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_process_bulk_action', $plugin_admin, 'ced_ebay_process_bulk_action' );
		$this->loader->add_filter( 'woocommerce_duplicate_product_exclude_meta', $plugin_admin, 'ced_ebay_duplicate_product_exclude_meta' );
		$this->loader->add_filter( 'ced_marketplaces_logged_array', $plugin_admin, 'ced_ebay_marketplace_to_be_logged' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_modify_product_data_for_upload', $plugin_admin, 'ced_ebay_modify_product_data_for_upload' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_get_modifed_product_details', $plugin_admin, 'ced_ebay_get_modifed_product_details' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_ajax_live_search_categories', $plugin_admin, 'ced_ebay_ajax_live_search_categories' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_remove_category_mapping', $plugin_admin, 'ced_ebay_remove_category_mapping' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_remove_account_from_integration', $plugin_admin, 'ced_ebay_remove_account_from_integration' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_oauth_authorization', $plugin_admin, 'ced_ebay_oauth_authorization' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_fetch_oauth_access_code', $plugin_admin, 'ced_ebay_fetch_oauth_access_code' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'ced_ebay_onWpAdminInit' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_remove_all_profiles', $plugin_admin, 'ced_ebay_remove_all_profiles' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_reset_category_item_specifics', $plugin_admin, 'ced_ebay_reset_category_item_specifics' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_remove_term_from_profile', $plugin_admin, 'ced_ebay_remove_term_from_profile' );
		$this->loader->add_action( 'wp_ajax_ced_ebay_process_profile_bulk_action', $plugin_admin, 'ced_ebay_process_profile_bulk_action' );

	}



	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    EBay_Integration_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}



}
