<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cedcommerce.com
 * @since      1.0.0
 *
 * @package    EBay_Integration_For_Woocommerce
 * @subpackage EBay_Integration_For_Woocommerce/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    EBay_Integration_For_Woocommerce
 * @subpackage EBay_Integration_For_Woocommerce/admin
 */
class EBay_Integration_For_Woocommerce_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->loadDependency();
		// error_reporting(~0);
		// ini_set('display_errors', 1);
		$this->userid = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		add_action( 'ced_ebay_refresh_access_token_schedule', array( $this, 'ced_ebay_refresh_access_token_schedule_action' ) );
		add_action( 'wp_ajax_ced_ebay_filter_products', array( $this, 'ced_ebay_filter_products' ) );
		if ( file_exists( CED_EBAY_DIRPATH . 'admin/ebay/lib/EbayRestEndpoints.php' ) ) {
			require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/EbayRestEndpoints.php';
		}

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in EBay_Integration_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The EBay_Integration_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		$page   = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		if ( 'ced_ebay' == $page || 'cedcommerce-integrations' == $page || 'edit' == $action ) {
			wp_enqueue_style( 'tailwind', plugin_dir_url( __FILE__ ) . 'css/tailwind.css', array(), '2.0', 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-ebay-integration-admin.css', array(), '1.1', 'all' );
			wp_enqueue_style( 'flatpick-min', plugin_dir_url( __FILE__ ) . 'css/flatpickr.min.css', false, 'all' );
			wp_enqueue_style( 'logger', plugin_dir_url( __FILE__ ) . 'css/logger.css', array(), '1.1', 'all' );
			wp_enqueue_style( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css', false, '1.12.1', 'all' );
			wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/select2.min.css', false, '1.12.1', 'all' );

		}

	}
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in EBay_Integration_For_Woocommerce_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The EBay_Integration_For_Woocommerce_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-spinner' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'selectWoo' );
		$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
		$page   = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		if ( 'ced_ebay' == $page || 'cedcommerce-integrations' == $page || 'edit' == $action ) {
			wp_enqueue_script( 'sweet-alert', plugin_dir_url( __FILE__ ) . 'js/sweetalert.min.js', array( 'jquery' ), $this->version, false );
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woocommerce-ebay-integration-admin.js', array( 'jquery' ), time(), false );
			wp_enqueue_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2.min.js', array( 'jquery' ), '4.1.0', false );

			if ( 'edit' != $action ) {
			//	wp_enqueue_script( 'ced-ebay-hubspot-chat', 'https://js-na1.hs-scripts.com/6086579.js', array(), '1.0', true );
				wp_enqueue_script( 'ced-ebay-zoho-chat', plugin_dir_url( __FILE__ ) . 'js/zoho-chat.js', array('jquery'), '1.0', true );
			}
		}
		if ( 'ced_ebay_add_new_template' == $action || 'ced_ebay_edit_template' == $action ) {
			wp_enqueue_script( 'ace', plugin_dir_url( __FILE__ ) . 'js/ace/ace.js', array( 'jquery' ), '1.4.12' );
			wp_enqueue_script( 'mode-css', plugin_dir_url( __FILE__ ) . 'js/ace/mode-css.js', array( 'jquery' ), '1.4.12' );
		}
		$ajax_nonce     = wp_create_nonce( 'ced-ebay-ajax-seurity-string' );
		$localize_array = array(
			'ajax_url'   => admin_url( 'admin-ajax.php' ),
			'ajax_nonce' => $ajax_nonce,
			'user_id'    => isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '',
			'site_url'   => get_option( 'siteurl' ),
			'store_url'  => get_option( 'siteurl' ),
		);
		wp_localize_script( $this->plugin_name, 'ced_ebay_admin_obj', $localize_array );

	}


	public function ced_ebay_add_menus() {
		global $submenu;
		if ( empty( $GLOBALS['admin_page_hooks']['cedcommerce-integrations'] ) ) {
			add_menu_page( __( 'CedCommerce', 'ebay-integration-for-woocommerce' ), __( 'CedCommerce', 'ebay-integration-for-woocommerce' ), 'manage_woocommerce', 'cedcommerce-integrations', array( $this, 'ced_marketplace_listing_page' ), get_site_url( null, '/wp-content/plugins/product-lister-ebay/admin/images/admin_menu_logo.png', 'https' ), 12 );
			/**
			 *  Add product lister menu
			 *
			 *  @since 1.0.1 First time this was introduced.
			 *
			 *  @return array Indicates the menu data.
			 */
			$menus = apply_filters( 'ced_add_marketplace_menus_array', array() );
			if ( is_array( $menus ) && ! empty( $menus ) ) {
				foreach ( $menus as $key => $value ) {
					add_submenu_page( 'cedcommerce-integrations', $value['name'], $value['name'], 'manage_woocommerce', $value['menu_link'], array( $value['instance'], $value['function'] ) );
				}
			}
		}
	}

	public function ced_ebay_add_marketplace_menus_to_array( $menus = array() ) {
		$menus[] = array(
			'name'            => 'eBay',
			'slug'            => 'ebay-integration-for-woocommerce',
			'menu_link'       => 'ced_ebay',
			'instance'        => $this,
			'function'        => 'ced_ebay_accounts_page',
			'card_image_link' => CED_EBAY_URL . 'admin/images/ebay-card.png',
		);
		return $menus;
	}

	public function ced_ebay_marketplace_to_be_logged( $marketplaces = array() ) {

		$marketplaces[] = array(
			'name'             => 'eBay',
			'marketplace_slug' => 'ebay',
		);
		return $marketplaces;
	}

	public function ced_marketplace_listing_page() {
		/**
			 *  Add product lister menu
			 *
			 *  @since 1.0.1 First time this was introduced.
			 *
			 *  @return array Indicates the active marketplaces.
			 */
		$activeMarketplaces = apply_filters( 'ced_add_marketplace_menus_array', array() );
		if ( is_array( $activeMarketplaces ) && ! empty( $activeMarketplaces ) ) {
			require CED_EBAY_DIRPATH . 'admin/partials/marketplaces.php';
		}
	}

	/*
	*
	*Function for displaying default page
	*
	*
	*/
	public function ced_ebay_accounts_page() {
		$fileAccounts = CED_EBAY_DIRPATH . 'admin/partials/ced-ebay-accounts.php';
		if ( file_exists( $fileAccounts ) ) {
			require_once $fileAccounts;
		}
	}

	public function ced_ebay_fetch_next_level_category() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$select_html = '';
			global $wpdb;
			$sanitized_array    = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$tableName          = $wpdb->prefix . 'ced_ebay_accounts';
			$store_category_id  = isset( $sanitized_array['store_id'] ) ? ( $sanitized_array['store_id'] ) : '';
			$ebay_store_id      = isset( $sanitized_array['ebay_store_id'] ) ? ( $sanitized_array['ebay_store_id'] ) : '';
			$ebay_category_name = isset( $sanitized_array['name'] ) ? ( $sanitized_array['name'] ) : '';
			$ebay_category_id   = isset( $sanitized_array['id'] ) ? ( $sanitized_array['id'] ) : '';
			$level              = isset( $sanitized_array['level'] ) ? ( $sanitized_array['level'] ) : '';
			$type               = isset( $sanitized_array['type'] ) ? ( $sanitized_array['type'] ) : '';

			$next_level = intval( $level ) + 1;
			$shop_data  = ced_ebay_get_shop_data( $ebay_store_id );
			if ( ! empty( $shop_data ) ) {
				$siteID      = $shop_data['site_id'];
				$token       = $shop_data['access_token'];
				$getLocation = $shop_data['location'];
			}
			$getLocation   = strtolower( $getLocation );
			$getLocation   = str_replace( ' ', '', $getLocation );
			$wp_folder     = wp_upload_dir();
			$wp_upload_dir = $wp_folder['basedir'];
			$wp_upload_dir = $wp_upload_dir . '/ced-ebay/cateogry-templates-json/' . $ebay_store_id . '/';
			$folderName    = $wp_upload_dir . '/categoryLevel-' . $next_level . '_' . $getLocation . '.json';

			$categoryNextLevelFile = $folderName;
			if ( file_exists( $categoryNextLevelFile ) ) {
				$ebayCategoryList = file_get_contents( $categoryNextLevelFile );
				$ebayCategoryList = json_decode( $ebayCategoryList, true );
			} else {
				$categoryNextLevelFile = CED_EBAY_DIRPATH . 'admin/ebay/lib/json/MY_categoryList.json';
				$ebayCategoryList      = file_get_contents( $categoryNextLevelFile );
				$ebayCategoryList      = json_decode( $ebayCategoryList, true );
			}
			if ( empty( $store_category_id ) ) {
				$select_html = '<select style="max-width:15rem;margin-left:5px;" class="ced_ebay_level' . $next_level . '_category ced_ebay_bulk_select_ebay_category select_boxes_cat_map" name="ced_ebay_level' . $next_level . '_category[]" data-level=' . $next_level . ' data-ebayStoreId="' . $ebay_store_id . '">';
			} else {
				if ( 'primary' == $type ) {
					$select_html .= '<td data-catlevel="' . $next_level . '"><select class="ced_ebay_level' . $next_level . '_category ced_ebay_select_category  select_boxes_cat_map" name="ced_ebay_level' . $next_level . '_category[]" data-level=' . $next_level . ' data-storeCategoryID="' . $store_category_id . '" data-ebayStoreId="' . $ebay_store_id . '">';
				} elseif ( 'secondary' == $type ) {

					$select_html .= '<td data-catlevel-secondary="' . $next_level . '"><select class="ced_ebay_level' . $next_level . '_secondary_category ced_ebay_select_secondary_category select_boxes_cat_map" name="ced_ebay_level' . $next_level . '_secondary_category[]" data-level-secondary=' . $next_level . ' data-storeCategoryID-secondary="' . $store_category_id . '" data-ebayStoreId-secondary="' . $ebay_store_id . '">';

				}
			}
			$select_html           .= '<option value="">' . __( '--Select--', 'woocommerce-ebay-integration' ) . '</option>';
			$nextLevelCategoryArray = array();
			if ( ! empty( $ebayCategoryList ) ) {
				foreach ( $ebayCategoryList['CategoryArray']['Category'] as $key => $value ) {
					if ( isset( $value['CategoryParentID'] ) && $value['CategoryParentID'] == $ebay_category_id ) {
						$nextLevelCategoryArray[] = $value;
					}
				}
			}
			if ( is_array( $nextLevelCategoryArray ) && ! empty( $nextLevelCategoryArray ) ) {

				foreach ( $nextLevelCategoryArray as $key => $value ) {
					if ( '' != $value['CategoryName'] ) {
						$select_html .= '<option value="' . $value['CategoryID'] . '">' . $value['CategoryName'] . '</option>';
					}
				}
			}
			if ( empty( $store_category_id ) ) {
				$select_html .= '</select>';

			} else {
				$select_html .= '</select></td>';

			}
			if ( empty( $nextLevelCategoryArray ) ) {
				$select_html = '';
			}
			echo json_encode( $select_html );
			die;
		}
	}

	public function ced_ebay_onWpAdminInit() {
		$current_uri = home_url( add_query_arg( null, null ) );
		$path        = parse_url( $current_uri, PHP_URL_QUERY );
		$user_id     = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		if ( '' !== $path ) {
			parse_str( $path, $params );
			$access_code             = isset( $params['code'] ) ? $params['code'] : '';
			$bigcom_parms            = isset( $params['bigcom'] ) ? $params['bigcom'] : '';
			$oauth_error_description = isset( $params['error_description'] ) ? $params['error_description'] : '';
			$site_id                 = str_replace( 'ced_woo_ebay_lister_', '', $bigcom_parms );
			if ( '' !== $site_id && '' !== $access_code ) {
				echo '<h1>Please wait while we are redirecting you to complete Authentication!</h1>';
				$this->ced_ebay_fetch_user_data_using_access_token( $access_code, $site_id );
			}
		}
		if ( ! empty( $user_id ) ) {
			if ( function_exists( 'as_has_scheduled_action' ) && function_exists( 'as_schedule_recurring_action' ) ) {
				if ( ! as_has_scheduled_action( 'ced_ebay_refresh_access_token_schedule' ) ) {
					as_schedule_recurring_action( time(), '3600', 'ced_ebay_refresh_access_token_schedule', array( 'data' => array( 'user_id' => $user_id ) ) );
				}
			}

			$access_token_arr = get_option( 'ced_ebay_user_access_token' );
			if ( ! empty( $access_token_arr ) ) {
				foreach ( $access_token_arr as $key => $value ) {
					$tokenValue = get_transient( 'ced_ebay_user_access_token_' . $key );
					if ( false === $tokenValue ) {
						$user_refresh_token = $value['refresh_token'];
						$this->ced_ebay_save_user_access_token( $key, $user_refresh_token, 'refresh_user_token' );
					}
				}
			}
		}

	}

	public function ced_ebay_filter_products() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {

			$search_term     = isset( $_GET['search_term'] ) ? sanitize_text_field( $_GET['search_term'] ) : '';
			$filter_criteria = isset( $_GET['filter_criteria'] ) ? sanitize_text_field( $_GET['filter_criteria'] ) : '';
			$user_id         = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';

			if ( ! empty( $filter_criteria ) ) {
				switch ( $filter_criteria ) {
					case 'ebay_listing_id':
						$args = array(
							'post_type'           => 'product',
							'post_status'         => 'publish',
							'ignore_sticky_posts' => 1,
							'meta_key'            => '_ced_ebay_listing_id_' . $user_id,
							'meta_value'          => $search_term,
							'meta_compare'        => '=',
						);
						break;
					case 'product_name':
						$args = array(
							's'                   => $search_term,
							'post_type'           => 'product',
							'post_status'         => 'publish',
							'ignore_sticky_posts' => 1,
						);
						break;

					case 'product_sku':
						$args = array(
							'post_type'           => 'product',
							'post_status'         => 'publish',
							'ignore_sticky_posts' => 1,
							'meta_key'            => '_sku',
							'meta_value'          => $search_term,
							'meta_compare'        => '=',
						);

				}
			}
			   $products = get_posts( $args );
			if ( 'ebay_listing_id' != $filter_criteria ) {
				$search_results['custom_search']['post_title'] = 'Search for "' . $search_term . '"';
				$search_results['custom_search']['post_id']    = 'custom_search|' . $search_term;

			}
			if ( ! is_wp_error( $products ) && ! empty( $products ) ) {
				foreach ( $products as $key => $search_product ) {
					$search_results[ $key ]['post_title'] = $search_product->post_title;
					$search_results[ $key ]['post_id']    = (int) $search_product->ID;
				}
			}
			   wp_send_json( $search_results );

		}
	}


	public function ced_ebay_oauth_authorization() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$site_id          = isset( $_POST['site_id'] ) ? sanitize_text_field( $_POST['site_id'] ) : '';
			$login_mode       = isset( $_POST['login_mode'] ) ? sanitize_text_field( $_POST['login_mode'] ) : '';
			$oAuthFile        = CED_EBAY_DIRPATH . 'admin/ebay/lib/cedOAuthAuthorization.php';
			$renderDependency = $this->renderDependency( $oAuthFile );
			if ( 'production' == $login_mode || '' == $login_mode ) {
				update_option( 'ced_ebay_mode_of_operation', 'production' );
			}
			if ( $renderDependency ) {
				$cedAuthorization        = new Ced_Ebay_OAuth_Authorization();
				$cedAuhorizationInstance = $cedAuthorization->get_instance();
				$authURL                 = $cedAuhorizationInstance->doOAuthAuthorization( $site_id );
				echo json_encode( $authURL );

				die;
			}
		}

	}


	public function ced_ebay_fetch_user_data_using_access_token( $access_code, $siteID ) {
		$oAuthFile        = CED_EBAY_DIRPATH . 'admin/ebay/lib/cedOAuthAuthorization.php';
		$renderDependency = $this->renderDependency( $oAuthFile );
		if ( $renderDependency ) {
			$cedAuthorization        = new Ced_Ebay_OAuth_Authorization();
			$cedAuhorizationInstance = $cedAuthorization->get_instance();
			$accessCodeResponse      = $cedAuhorizationInstance->fetchOAuthUserAccessToken( $access_code, $siteID, 'authorization_code' );
			$accessCodeResponse      = json_decode( $accessCodeResponse );
			if ( ! empty( $accessCodeResponse->access_token ) ) {
				$file             = CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayAuthorization.php';
				$renderDependency = $this->renderDependency( $file );
				if ( $renderDependency ) {
					$cedAuthorization        = new Ebayauthorization();
					$cedAuhorizationInstance = $cedAuthorization->get_instance();
					$userDetails             = $cedAuhorizationInstance->getUserData( $accessCodeResponse->access_token, $siteID );
					if ( empty( $userDetails ) ) {
						update_option( 'ced_ebay_oauth_error_description', 'Failed to fetch Seller Store Data!' );
						wp_redirect( get_admin_url() . 'admin.php?page=ced_ebay&error_description=failed_to_fetch_user_store_data' );
					}

					require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayConfig.php';
					$ebayConfig         = new Ebayconfig();
					$ebayConfigInstance = $ebayConfig->get_instance();
					$ebaySites          = $ebayConfigInstance->getEbaysites();
					if ( is_array( $ebaySites ) && ! empty( $ebaySites ) ) {
						foreach ( $ebaySites as $sites ) {
							if ( $userDetails['Site'] == $sites['name'] ) {
								$site_id = $sites['siteID'];
								$listing_tld = $sites['tld'];
								break;
							}
						}
						$user_id = $userDetails['UserID'];
						if ( ! empty( $listing_tld ) && ! empty( $user_id ) ) {
							update_option( 'ced_ebay_listing_url_tld_' . $user_id, $listing_tld );
						}
					}

					if ( '' == $user_id || '' == $site_id ) {
						update_option( 'ced_ebay_oauth_error_description', 'Failed to fetch eBay User ID or Site ID!' );
						wp_redirect( get_admin_url() . 'admin.php?page=ced_ebay&error_description=invalid_user_id_or_site_id' );
						return;
					}

					$accessToken  = $accessCodeResponse->access_token;
					$refreshToken = $accessCodeResponse->refresh_token;
					if ( ! empty( get_option( 'ced_ebay_user_access_token' ) ) ) {
						delete_option( 'ced_ebay_user_access_token' );
					}
					$tokenArr = get_option( 'ced_ebay_user_access_token' );
					if ( ! is_array( $tokenArr ) ) {
						$tokenArr = array();
					}
					$tokenArr[ $user_id ] = array(
						'user_id'       => $user_id,
						'access_token'  => $accessToken,
						'refresh_token' => $refreshToken,
						'site_id'       => $site_id,
						'location'      => $userDetails['Site'],
						'seller_email'  => $userDetails['Email'],
						'eiastoken'     => $userDetails['EIASToken'],
						'seller_data'   => $userDetails,
					);
					update_option( 'ced_ebay_user_access_token', $tokenArr );
					set_transient( 'ced_ebay_user_access_token_' . $user_id, $accessToken, 1 * HOUR_IN_SECONDS );
					if ( function_exists( 'as_has_scheduled_action' ) && function_exists( 'as_schedule_recurring_action' ) ) {
						if ( ! as_has_scheduled_action( 'ced_ebay_refresh_access_token_schedule' ) ) {
							as_schedule_recurring_action( time(), '3600', 'ced_ebay_refresh_access_token_schedule', array( 'data' => array( 'user_id' => $user_id ) ) );
						}
					}
					wp_redirect( get_admin_url() . 'admin.php?page=ced_ebay&section=accounts-view&user_id=' . $user_id );

				}
			} elseif ( isset( $accessCodeResponse->error ) ) {
				update_option( 'ced_ebay_oauth_error_description', $accessCodeResponse->error_description );
				wp_redirect( get_admin_url() . 'admin.php?page=ced_ebay&error_description=' . $accessCodeResponse->error_description );
			}
		}
	}

	public function ced_ebay_fetch_oauth_access_code() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$user_id    = isset( $_POST['userid'] ) ? sanitize_text_field( $_POST['userid'] ) : '';
			$accessCode = isset( $_POST['accessCode'] ) ? wp_kses( $_POST['accessCode'], '' ) : '';
			if ( isset( $user_id ) && isset( $accessCode ) ) {

				$this->ced_ebay_save_user_access_token( $user_id, $accessCode, 'get_user_token' );
			}
		}
	}

	public function ced_ebay_refresh_access_token_schedule_action( $user_id_array ) {
		$user_id          = $user_id_array['user_id'];
		$access_token_arr = get_option( 'ced_ebay_user_access_token' );
		if ( ! empty( $access_token_arr ) ) {
			foreach ( $access_token_arr as $key => $value ) {
				$tokenValue = get_transient( 'ced_ebay_user_access_token_' . $key );
				if ( false === $tokenValue ) {
					$user_refresh_token = $value['refresh_token'];
					$this->ced_ebay_save_user_access_token( $key, $user_refresh_token, 'refresh_user_token' );
				}
			}
		}
	}

	public function ced_ebay_save_user_access_token( $user_id, $accessCode, $action ) {
		$shop_data = ced_ebay_get_shop_data( $user_id );
		if ( ! empty( $shop_data ) ) {
			$siteID    = $shop_data['site_id'];
				$token = $shop_data['access_token'];
		}
		$oAuthFile        = CED_EBAY_DIRPATH . 'admin/ebay/lib/cedOAuthAuthorization.php';
		$renderDependency = $this->renderDependency( $oAuthFile );
		if ( $renderDependency ) {
			$cedAuthorization        = new Ced_Ebay_OAuth_Authorization();
			$cedAuhorizationInstance = $cedAuthorization->get_instance();
			if ( 'get_user_token' == $action ) {
				$accessCodeResponse = $cedAuhorizationInstance->fetchOAuthUserAccessToken( $accessCode, $siteID, 'authorization_code' );
				$accessCodeResponse = json_decode( $accessCodeResponse );
				$responseArr        = get_option( 'ced_ebay_access_token_response' );
				if ( ! is_array( $responseArr ) ) {
					$responseArr = array();
				}
				$responseArr[ $user_id ] = $accessCodeResponse;
				update_option( 'ced_ebay_access_token_response', $responseArr );
				if ( ! empty( $accessCodeResponse->access_token ) ) {
					$accessToken  = $accessCodeResponse->access_token;
					$refreshToken = $accessCodeResponse->refresh_token;
					$tokenArr     = get_option( 'ced_ebay_user_access_token' );
					if ( ! is_array( $tokenArr ) ) {
						$tokenArr = array();
					}
					$tokenArr[ $user_id ] = array(
						'access_token'  => $accessToken,
						'refresh_token' => $refreshToken,
					);
					update_option( 'ced_ebay_user_access_token', $tokenArr );
					set_transient( 'ced_ebay_user_access_token_' . $user_id, $accessToken, 1 * HOUR_IN_SECONDS );

					echo 'Success';
					die;
				} else {
					echo 'Failed';
					die;
				}
			} elseif ( 'refresh_user_token' == $action ) {
				$accessCodeResponse = $cedAuhorizationInstance->fetchOAuthUserAccessToken( $accessCode, $siteID, 'refresh_token' );
				$accessCodeResponse = json_decode( $accessCodeResponse );
				$newTokenArr        = get_option( 'ced_ebay_user_access_token' );
				$newAccessToken     = $accessCodeResponse->access_token;
				if ( ! is_array( $newTokenArr ) ) {
					$newTokenArr = array();
				}
				if ( isset( $newTokenArr[ $user_id ] ) ) {
					$newTokenArr[ $user_id ]['access_token'] = $newAccessToken;
					set_transient( 'ced_ebay_user_access_token_' . $user_id, $newAccessToken, 1 * HOUR_IN_SECONDS );
				}
				update_option( 'ced_ebay_access_token_response', $newTokenArr );
				update_option( 'ced_ebay_user_access_token', $newTokenArr );
				return;
			}
		}
	}

	public function ced_ebay_remove_all_profiles() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			global $wpdb;
			$user_id              = isset( $_POST['userid'] ) ? sanitize_text_field( $_POST['userid'] ) : '';
			$woo_store_categories = get_categories(
				array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
				)
			);
			$profile_ids          = array();
			$ebay_profiles        = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM {$wpdb->prefix}ced_ebay_profiles WHERE `user_id`=%s", $user_id ), 'ARRAY_A' );
			foreach ( $ebay_profiles as $key => $ebay_profile ) {
				$profile_ids[] = $ebay_profile['id'];
			}
			foreach ( $profile_ids as $key => $pid ) {
				$product_ids_assigned = get_option( 'ced_ebay_product_ids_in_profile_' . $pid, array() );
				if ( ! empty( $product_ids_assigned ) ) {
					foreach ( $product_ids_assigned as $index => $ppid ) {
						delete_post_meta( $ppid, 'ced_ebay_profile_assigned' . $user_id );
					}
				}
				$ebay_category_id_array             = $wpdb->get_results( $wpdb->prepare( "SELECT `profile_data` FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id` = %s ", $pid ), 'ARRAY_A' );
							$ebay_category_id_array = json_decode( $ebay_category_id_array[0]['profile_data'], true );
				if ( ! empty( $ebay_category_id_array['_umb_ebay_category']['default'] ) ) {
					$ebay_cat_id        = $ebay_category_id_array['_umb_ebay_category']['default'];
					$ebay_cat_attr_data = get_option( 'ced_ebay_category_attr_val_' . $user_id );
					if ( ! empty( $ebay_cat_attr_data ) ) {
						if ( array_key_exists( $ebay_cat_id, $ebay_cat_attr_data ) ) {
							unset( $ebay_cat_attr_data[ $ebay_cat_id ] );
							update_option( 'ced_ebay_category_attr_val_' . $user_id, $ebay_cat_attr_data );

						}
					}
				}

					$term_id = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id` = %s ", $pid ), 'ARRAY_A' );
					$term_id = json_decode( $term_id[0]['woo_categories'], true );
				foreach ( $term_id as $key => $value ) {
					delete_term_meta( $value, 'ced_ebay_mapped_to_store_category_' . $user_id );
					delete_term_meta( $value, 'ced_ebay_profile_created_' . $user_id );
					delete_term_meta( $value, 'ced_ebay_profile_id_' . $user_id );
					delete_term_meta( $value, 'ced_ebay_mapped_category_' . $user_id );
					delete_term_meta( $value, 'ced_ebay_mapped_to_store_category_' . $user_id );
					delete_term_meta( $value, 'ced_ebay_mapped_to_store_secondary_category_' . $user_id );
					delete_term_meta( $value, 'ced_ebay_mapped_secondary_category_' . $user_id );

				}

				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id` IN (%s)", $pid ) );
			}

			if ( ! empty( $woo_store_categories ) ) {

				foreach ( $woo_store_categories as $key => $woo_category ) {
					if ( ! empty( $woo_category->term_id ) ) {
						delete_term_meta( $woo_category->term_id, 'ced_ebay_mapped_to_store_category_' . $user_id );
						delete_term_meta( $woo_category->term_id, 'ced_ebay_profile_created_' . $user_id );
						delete_term_meta( $woo_category->term_id, 'ced_ebay_profile_id_' . $user_id );
						delete_term_meta( $woo_category->term_id, 'ced_ebay_mapped_category_' . $user_id );
						delete_term_meta( $woo_category->term_id, 'ced_ebay_mapped_to_store_category_' . $user_id );
						delete_term_meta( $woo_category->term_id, 'ced_ebay_mapped_to_store_secondary_category_' . $user_id );
						delete_term_meta( $woo_category->term_id, 'ced_ebay_mapped_secondary_category_' . $user_id );
					}
				}
			}

			if ( empty( $wpdb->last_error ) ) {
				wp_send_json(
					array(
						'status'  => 'success',
						'message' => 'Profiles Deleted Successfully',
						'title'   => 'Profiles Deleted',
					)
				);
			} else {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => 'There was an error while trying to Delete Profiles.',
						'title'   => 'Failed to Delete Profiles',
					)
				);
			}
		}

	}

	public function ced_ebay_reset_category_item_specifics() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			global $wp_filesystem;
			$wp_folder     = wp_upload_dir();
			$wp_upload_dir = $wp_folder['basedir'];
			$user_id       = isset( $_POST['userid'] ) ? sanitize_text_field( $_POST['userid'] ) : '';
			$wp_upload_dir = $wp_upload_dir . '/ced-ebay/category-specifics/' . $user_id . '/';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			$dir_exists = $wp_filesystem->exists( $wp_upload_dir );
			if ( $dir_exists ) {
				$is_deleted = $wp_filesystem->rmdir( $wp_upload_dir, true );
				if ( $is_deleted ) {
					if ( ! is_dir( $wp_upload_dir ) ) {
						if ( wp_mkdir_p( $wp_upload_dir, 0777 ) ) {
							wp_send_json(
								array(
									'status'  => 'success',
									'message' => 'eBay category item specifics has been reset.',
									'title'   => 'Item Specifics Reset Successful',
								)
							);
						} else {
							wp_send_json(
								array(
									'status'  => 'error',
									'message' => 'An error has occured while trying to delete and re-create directory structure. Please contact support!',
									'title'   => 'Delete failed',
								)
							);
						}
					}
				}
			} else {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => 'Unable to find the directory to delete!',
						'title'   => 'Delete failed',
					)
				);
			}
		}
	}



	public function ced_ebay_remove_term_from_profile() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$user_id    = isset( $_POST['userid'] ) ? sanitize_text_field( $_POST['userid'] ) : '';
			$term_id    = isset( $_POST['term_id'] ) ? sanitize_text_field( $_POST['term_id'] ) : '';
			$profile_id = isset( $_POST['profile_id'] ) ? sanitize_text_field( $_POST['profile_id'] ) : '';
			if ( ! empty( $term_id ) && ! empty( $profile_id ) ) {
				global $wpdb;
				$get_ebay_profile = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_ebay_profiles WHERE `user_id`=%s AND `id` = %s", $user_id, $profile_id ), 'ARRAY_A' );
				if ( empty( $wpdb->last_error ) ) {
					if ( ! empty( $get_ebay_profile[0] ) ) {
						$woo_categories = json_decode( $get_ebay_profile[0]['woo_categories'], true );
						if ( ! empty( $woo_categories ) ) {
							if ( ( array_search( $term_id, $woo_categories ) ) !== false ) {
								$term_position = array_search( $term_id, $woo_categories );
								delete_term_meta( $term_id, 'ced_ebay_mapped_to_store_category_' . $user_id );
								delete_term_meta( $term_id, 'ced_ebay_profile_created_' . $user_id );
								delete_term_meta( $term_id, 'ced_ebay_profile_id_' . $user_id );
								delete_term_meta( $term_id, 'ced_ebay_mapped_category_' . $user_id );
								delete_term_meta( $term_id, 'ced_ebay_mapped_to_store_category_' . $user_id );
								delete_term_meta( $term_id, 'ced_ebay_mapped_to_store_secondary_category_' . $user_id );
								delete_term_meta( $term_id, 'ced_ebay_mapped_secondary_category_' . $user_id );
								 unset( $woo_categories[ $term_position ] );
								 $tableName = $wpdb->prefix . 'ced_ebay_profiles';
								$wpdb->update(
									$tableName,
									array(
										'woo_categories' => json_encode( $woo_categories ),
									),
									array( 'id' => $profile_id ),
									array( '%s' )
								);
								if ( empty( $wpdb->last_error ) ) {
									wp_send_json(
										array(
											'status'  => 'success',
											'message' => 'The selected category has been removed from the profile.',
											'title'   => 'Deleted Successfully',
										)
									);
								} else {
									wp_send_json(
										array(
											'status'  => 'error',
											'message' => 'There was an error in removing the category from the profile. Please contact support!',
											'title'   => 'Error',
										)
									);
								}
							}
						}
					}
				}
			}
		}
	}




	public function ced_ebay_remove_account_from_integration() {
		wp_cache_flush();
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			if ( ! empty( get_option( 'ced_ebay_user_access_token' ) ) ) {
				$user_data = get_option( 'ced_ebay_user_access_token', true );
				foreach ( $user_data as $key => $ebay_user ) {
					$user_id = $key;
					break;
				}
				if ( ! empty( $user_id ) ) {
					update_option( 'ced_ebay_mode_of_operation', '' );
					delete_transient( 'ced_ebay_user_access_token_' . $user_id );
					if ( wp_next_scheduled( 'ced_ebay_order_scheduler_job_' . $user_id ) ) {
						wp_clear_scheduled_hook( 'ced_ebay_order_scheduler_job_' . $user_id );
					}
					if ( wp_next_scheduled( 'ced_ebay_existing_products_sync_job_' . $user_id ) ) {
						wp_clear_scheduled_hook( 'ced_ebay_existing_products_sync_job_' . $user_id );
					}
					if ( wp_next_scheduled( 'ced_ebay_import_products_job_' . $user_id ) ) {
						wp_clear_scheduled_hook( 'ced_ebay_import_products_job_' . $user_id );
					}
					if ( function_exists( 'as_has_scheduled_action' ) && function_exists( 'as_unschedule_all_actions' ) ) {
						if ( as_has_scheduled_action( 'ced_ebay_refresh_access_token_schedule' ) ) {
							as_unschedule_all_actions( 'ced_ebay_refresh_access_token_schedule' );
						}
						if ( as_has_scheduled_action( 'ced_ebay_inventory_scheduler_job_' . $user_id ) ) {
							as_unschedule_all_actions( 'ced_ebay_inventory_scheduler_job_' . $user_id );
						}
					}

					if ( wp_next_scheduled( 'ced_ebay_bulk_upload_job_scheduler_' . $user_id ) ) {
						wp_clear_scheduled_hook( 'ced_ebay_bulk_upload_job_scheduler_' . $user_id );
					}
					delete_option( 'ced_ebay_user_access_token' );
					wp_send_json(
						array(
							'status'  => 'success',
							'message' => 'Account Deleted Successfully',
							'title'   => 'Account Deleted',
						)
					);

				} else {
					wp_send_json(
						array(
							'status'  => 'error',
							'message' => 'User ID not found',
							'title'   => 'Invalid User ID',
						)
					);
				}
			}
		}
	}


	/*
	*
	*Function for Storing mapped categories
	*
	*
	*/

	public function ced_ebay_map_categories_to_store() {

		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$sanitized_array                      = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$ebay_store_id                        = isset( $sanitized_array['ebay_store_id'] ) ? ( $sanitized_array['ebay_store_id'] ) : '';
			$ebay_category_array                  = isset( $sanitized_array['ebay_category_array'] ) ? ( $sanitized_array['ebay_category_array'] ) : '';
			$ebay_secondary_category_array        = isset( $sanitized_array['ebay_secondary_category_array'] ) ? ( $sanitized_array['ebay_secondary_category_array'] ) : '';
			$store_category_array                 = isset( $sanitized_array['store_category_array'] ) ? ( $sanitized_array['store_category_array'] ) : '';
			$ebay_store_custom_category_array     = isset( $sanitized_array['ebay_store_custom_category_array'] ) ? ( $sanitized_array['ebay_store_custom_category_array'] ) : '';
			$ebay_store_secondary_category_array  = isset( $sanitized_array['ebay_store_secondary_category_array'] ) ? ( $sanitized_array['ebay_store_secondary_category_array'] ) : '';
			$ebay_category_name                   = isset( $sanitized_array['ebay_category_name'] ) ? ( $sanitized_array['ebay_category_name'] ) : '';
			$ebay_secondary_category_name         = isset( $sanitized_array['ebay_secondary_category_name'] ) ? ( $sanitized_array['ebay_secondary_category_name'] ) : '';
			$ebay_saved_category                  = get_option( 'ced_ebay_saved_category', array() );
			$alreadyMappedCategories              = array();
			$alreadyMappedCategoriesName          = array();
			$alreadyMappedSecondaryCategoriesName = array();
			$ebayStoreMappedCustomCategories      = array();
			$ebayMappedCategories                 = array();
			if ( ! empty( $store_category_array ) && ! empty( $ebay_store_custom_category_array ) ) {
				$ebayStoreMappedCustomCategories = array_combine( $store_category_array, $ebay_store_custom_category_array );
				$ebayStoreMappedCustomCategories = array_filter( $ebayStoreMappedCustomCategories );
			}

			$ebayStoreMappedSecondaryCategories = array();
			if ( ! empty( $store_category_array ) && ! empty( $ebay_store_secondary_category_array ) ) {
				$ebayStoreMappedSecondaryCategories = array_combine( $store_category_array, $ebay_store_secondary_category_array );
				$ebayStoreMappedSecondaryCategories = array_filter( $ebayStoreMappedSecondaryCategories );
			}

			$ebayMappedCategories    = array_combine( $store_category_array, $ebay_category_array );
			$ebayMappedCategories    = array_filter( $ebayMappedCategories );
			$alreadyMappedCategories = get_option( 'ced_woo_ebay_mapped_categories', true );
			if ( is_string( $alreadyMappedCategories ) || is_bool( $alreadyMappedCategories ) ) {
				$alreadyMappedCategories = array();
			}
			if ( is_array( $ebayMappedCategories ) && ! empty( $ebayMappedCategories ) ) {
				foreach ( $ebayMappedCategories as $key => $value ) {
					$alreadyMappedCategories[ $ebay_store_id ][ $key ] = $value;
				}
			}
			update_option( 'ced_woo_ebay_mapped_categories', $alreadyMappedCategories );
			$ebayMappedSecondaryCategories = array();
			if ( ! empty( $store_category_array ) && ! empty( $ebay_secondary_category_array ) ) {
				$ebayMappedSecondaryCategories = array_combine( $store_category_array, $ebay_secondary_category_array );
				$ebayMappedSecondaryCategories = array_filter( $ebayMappedSecondaryCategories );
			}
			$alreadyMappedSecondaryCategories = get_option( 'ced_woo_ebay_mapped_secondary_categories', true );
			if ( is_string( $alreadyMappedSecondaryCategories ) || is_bool( $alreadyMappedSecondaryCategories ) ) {
				$alreadyMappedSecondaryCategories = array();
			}
			if ( is_array( $ebayMappedSecondaryCategories ) && ! empty( $ebayMappedSecondaryCategories ) ) {
				foreach ( $ebayMappedSecondaryCategories as $key => $value ) {
					$alreadyMappedSecondaryCategories[ $ebay_store_id ][ $key ] = $value;
				}
			}

			update_option( 'ced_woo_ebay_mapped_secondary_categories', $alreadyMappedSecondaryCategories );

			$ebayMappedCategoriesName    = array_combine( $ebay_category_array, $ebay_category_name );
			$ebayMappedCategoriesName    = array_filter( $ebayMappedCategoriesName );
			$alreadyMappedCategoriesName = get_option( 'ced_woo_ebay_mapped_categories_name', true );
			if ( is_string( $alreadyMappedCategoriesName ) || is_bool( $alreadyMappedCategoriesName ) ) {
				$alreadyMappedCategoriesName = array();
			}
			if ( is_array( $ebayMappedCategoriesName ) && ! empty( $ebayMappedCategoriesName ) ) {
				foreach ( $ebayMappedCategoriesName as $key => $value ) {
					$alreadyMappedCategoriesName[ $ebay_store_id ][ $key ] = $value;
				}
			}
			update_option( 'ced_woo_ebay_mapped_categories_name', $alreadyMappedCategoriesName );
			if ( ! empty( $ebay_secondary_category_array ) && ! empty( $ebay_secondary_category_name ) ) {
				$ebayMappedSecondaryCategoriesName    = array_combine( $ebay_secondary_category_array, $ebay_secondary_category_name );
				$ebayMappedSecondaryCategoriesName    = array_filter( $ebayMappedSecondaryCategoriesName );
				$alreadyMappedSecondaryCategoriesName = get_option( 'ced_woo_ebay_mapped_secondary_categories_name', true );
				if ( is_string( $alreadyMappedSecondaryCategoriesName ) || is_bool( $alreadyMappedSecondaryCategoriesName ) ) {
					$alreadyMappedSecondaryCategoriesName = array();
				}
				if ( is_array( $ebayMappedSecondaryCategoriesName ) && ! empty( $ebayMappedSecondaryCategoriesName ) ) {
					foreach ( $ebayMappedSecondaryCategoriesName as $key => $value ) {
						$alreadyMappedSecondaryCategoriesName[ $ebay_store_id ][ $key ] = $value;
					}
				}
			}

			update_option( 'ced_woo_ebay_mapped_secondary_categories_name', $alreadyMappedSecondaryCategoriesName );

			$file             = CED_EBAY_DIRPATH . 'admin/ebay/class-ebay.php';
			$renderDependency = $this->renderDependency( $file );
			if ( $renderDependency ) {
				$cedeBay         = new Class_Ced_EBay_Manager();
				$cedebayInstance = $cedeBay->get_instance();
				$cedebayInstance->ced_ebay_createAutoProfiles( $ebayMappedCategories, $ebayMappedCategoriesName, $ebayMappedSecondaryCategories, $ebayMappedSecondaryCategoriesName, $ebayStoreMappedCustomCategories, $ebayStoreMappedSecondaryCategories, $ebay_store_id );
			}
			wp_die();
		}
	}





	public function ced_ebay_remove_category_mapping() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$term_id = isset( $_POST['term_id'] ) ? sanitize_text_field( $_POST['term_id'] ) : '';
			$user_id = isset( $_POST['user_id'] ) ? sanitize_text_field( $_POST['user_id'] ) : '';

			$mapping_type = isset( $_POST['mapping_type'] ) ? sanitize_text_field( $_POST['mapping_type'] ) : '';
			if ( ! empty( $term_id ) ) {
				if ( 'ebay-store-secondary-category' == $mapping_type ) {
					delete_term_meta( $term_id, 'ced_ebay_mapped_to_store_secondary_category_' . $user_id );
				} elseif ( 'ebay-store-primary-category' == $mapping_type ) {
					delete_term_meta( $term_id, 'ced_ebay_mapped_to_store_category_' . $user_id );
				} elseif ( 'ebay-secondary-category' == $mapping_type ) {
					delete_term_meta( $term_id, 'ced_ebay_mapped_secondary_category_' . $user_id );
				} elseif ( 'ebay-primary-category' == $mapping_type ) {
					delete_term_meta( $term_id, 'ced_ebay_profile_created_' . $user_id );
					delete_term_meta( $term_id, 'ced_ebay_profile_id_' . $user_id );
					delete_term_meta( $term_id, 'ced_ebay_mapped_category_' . $user_id );
				}
			}
		}
	}

	public function ced_ebay_process_profile_bulk_action() {
		$check_ajax      = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$user_id         = isset( $_POST['user_id'] ) ? sanitize_text_field( $_POST['user_id'] ) : '';
		if ( $check_ajax ) {
			$shop_data = ced_ebay_get_shop_data( $user_id );
			if ( ! empty( $shop_data ) ) {
				$siteID      = $shop_data['site_id'];
				$token       = $shop_data['access_token'];
				$getLocation = $shop_data['location'];
			}
			$profile_ids               = isset( $sanitized_array['profile_ids'] ) ? $sanitized_array['profile_ids'] : '';
			$operation_to_be_performed = isset( $_POST['operation_to_be_performed'] ) ? sanitize_text_field( $_POST['operation_to_be_performed'] ) : false;
			if ( $operation_to_be_performed ) {
				if ( 'bulk-delete' == $operation_to_be_performed ) {
					$wp_folder     = wp_upload_dir();
					$wp_upload_dir = $wp_folder['basedir'];
					$wp_upload_dir = $wp_upload_dir . '/ced-ebay/category-specifics/' . $user_id . '/';

					if ( is_array( $profile_ids ) && ! empty( $profile_ids ) ) {
						$profile_delete_error = false;
						global $wpdb;
						foreach ( $profile_ids as $index => $pid ) {
							   $product_ids_assigned = get_option( 'ced_ebay_product_ids_in_profile_' . $pid, array() );
							foreach ( $product_ids_assigned as $index => $ppid ) {
								delete_post_meta( $ppid, 'ced_ebay_profile_assigned' . $user_id );
							}
							   $ebay_category_id_array = $wpdb->get_results( $wpdb->prepare( "SELECT `profile_data` FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id` = %s ", $pid ), 'ARRAY_A' );
							 $ebay_category_id_array   = json_decode( $ebay_category_id_array[0]['profile_data'], true );
							if ( ! empty( $ebay_category_id_array['_umb_ebay_category']['default'] ) ) {
								$ebay_cat_id = $ebay_category_id_array['_umb_ebay_category']['default'];
								if ( ! empty( $ebay_cat_id ) && file_exists( $wp_upload_dir . 'ebaycat_' . $ebay_cat_id . '.json' ) ) {
									wp_delete_file( $wp_upload_dir . 'ebaycat_' . $ebay_cat_id . '.json' );
								}
							}
							   $term_id = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id` = %s ", $pid ), 'ARRAY_A' );
							   $term_id = json_decode( $term_id[0]['woo_categories'], true );
							foreach ( $term_id as $key => $value ) {
								delete_term_meta( $value, 'ced_ebay_profile_created_' . $user_id );
								delete_term_meta( $value, 'ced_ebay_profile_id_' . $user_id );
								delete_term_meta( $value, 'ced_ebay_mapped_category_' . $user_id );
							}
						}
						foreach ( $profile_ids as $id ) {
							  $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id` IN (%s)", $id ) );
							if ( ! empty( $wpdb->last_error ) ) {
								$profile_delete_error = true;
							}
						}
						if ( false == $profile_delete_error ) {
							echo json_encode(
								array(
									'status'  => 'success',
									'message' => 'Profile successfully deleted!',
								)
							);
							die;
						} else {
							echo json_encode(
								array(
									'status'  => 'error',
									'message' => 'There was an error in deleting profile',
								)
							);
							die;
						}
					}
				}
			}
		}
	}


	public function ced_ebay_category_refresh_button() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$ced_ebay_manager = $this->ced_ebay_manager;
			$sanitized_array  = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$userid           = isset( $_POST['userid'] ) ? sanitize_text_field( $_POST['userid'] ) : '';
			$levels           = isset( $sanitized_array['levels'] ) ? ( $sanitized_array['levels'] ) : '';
			$shop_data        = ced_ebay_get_shop_data( $userid );
			if ( ! empty( $shop_data ) ) {
				$siteID      = $shop_data['site_id'];
				$token       = $shop_data['access_token'];
				$getLocation = $shop_data['location'];
			}
			$pre_flight_check = ced_ebay_pre_flight_check( $userid );
			if ( ! $pre_flight_check ) {
				echo json_encode(
					array(
						'status'  => 'error',
						'message' => 'Unable to fetch categories. Token has been revoked.',
					)
				);
				die;
			}
			$wp_folder     = wp_upload_dir();
			$wp_upload_dir = $wp_folder['basedir'];
			$wp_upload_dir = $wp_upload_dir . '/ced-ebay/cateogry-templates-json/' . $userid . '/';
			if ( ! is_dir( $wp_upload_dir ) ) {
				   wp_mkdir_p( $wp_upload_dir, 0777 );
			}
			$fileCategory = CED_EBAY_DIRPATH . 'admin/ebay/lib/cedGetcategories.php';
			if ( file_exists( $fileCategory ) ) {
				require_once $fileCategory;
			}
			$categoryLevel  = 0;
			$getLocation    = strtolower( $getLocation );
			$getLocation    = str_replace( ' ', '', $getLocation );
			$levels         = isset( $sanitized_array['levels'] ) ? ( $sanitized_array['levels'] ) : '';
			$cedCatInstance = CedGetCategories::get_instance( $siteID, $token );
			foreach ( $levels as $level ) {
				$getCat = $cedCatInstance->_getCategories( $level );
				if ( $getCat && is_array( $getCat ) ) {
					$cates = array();
					foreach ( $getCat['CategoryArray']['Category'] as $key => $value ) {
						if ( "$level" == $value['CategoryLevel'] ) {
							$cates[] = $value;
						}
					}

					$getCat['CategoryArray']['Category'] = $cates;
					$folderName                          = $wp_upload_dir;
					$fileName                            = $folderName . 'categoryLevel-' . $level . '_' . $getLocation . '.json';
					$upload_dir                          = wp_upload_dir();
					$file                                = fopen( $fileName, 'w' );
					$pieces                              = str_split( json_encode( $getCat ), 1024 );
					foreach ( $pieces as $piece ) {
						if ( ! fwrite( $file, $piece, strlen( $piece ) ) ) {
							echo json_encode(
								array(
									'status'  => 'error',
									'message' => 'Permission Denied',
								)
							);
							die;
						}
					}
					fclose( $file );
					echo json_encode(
						array(
							'statuts' => 'success',
							'level'   => $level,
						)
					);
					die;

				} else {
					echo json_encode(
						array(
							'status'  => 'error',
							'message' => 'Unable to fetch categories.',
						)
					);

					die;
				}
			}
			echo json_encode(
				array(
					'status'  => 'success',
					'message' => 'Success',
				)
			);
			die;
		}
	}

	public function ced_ebay_process_bulk_action() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			$ced_ebay_manager = $this->ced_ebay_manager;
			$user_id          = isset( $_POST['userid'] ) ? sanitize_text_field( $_POST['userid'] ) : '';
			$shop_data        = ced_ebay_get_shop_data( $user_id );
			if ( ! empty( $shop_data ) ) {
				$siteID      = $shop_data['site_id'];
				$token       = $shop_data['access_token'];
				$getLocation = $shop_data['location'];
			}

			$pre_flight_check = ced_ebay_pre_flight_check( $user_id );
			$sanitized_array  = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
			$operation        = isset( $sanitized_array['operation_to_be_performed'] ) ? ( $sanitized_array['operation_to_be_performed'] ) : '';
			$product_id       = isset( $sanitized_array['id'] ) ? ( $sanitized_array['id'] ) : '';
			if ( $pre_flight_check ) {
				if ( is_array( $product_id ) ) {
					if ( 'upload_product' == $operation ) {
						$prodIDs          = $product_id[0];
						$wc_product       = wc_get_product( $prodIDs );
						$already_uploaded = get_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id, true );

						if ( $already_uploaded ) {
							echo json_encode(
								array(
									'status'  => 400,
									'message' => 'Product Already Uploaded',
									'prodid'  => $prodIDs,
									'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
								)
							);
							die;
						} else {
							$SimpleXml = $ced_ebay_manager->prepareProductHtmlForUpload( $user_id, $prodIDs );
							if ( is_array( $SimpleXml ) && ! empty( $SimpleXml ) ) {
								require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
							} elseif ( 'No Profile Assigned' == $SimpleXml ) {
								echo json_encode(
									array(
										'status'  => 400,
										'message' => 'No Profile Assigned to the product.',
										'prodid'  => $prodIDs,
										'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',

									)
								);
								die;
							} elseif ( 'No Shipping Template' == $SimpleXml ) {
								echo json_encode(
									array(
										'status'  => 400,
										'message' => 'You haven\'t created any Shipping Templates. Please create a Shipping Template in the Account Settings of our plugin.',
										'prodid'  => $prodIDs,
										'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
									)
								);
								die;
							} elseif ( 'No international country' == $SimpleXml ) {
								echo json_encode(
									array(
										'status'  => 400,
										'message' => 'Please select atleast one country in Shipping Template for International Shipping',
										'prodid'  => $prodIDs,
										'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
									)
								);
								die;
							}
							$ebayUploadInstance = EbayUpload::get_instance( $siteID, $token );
							$uploadOnEbay       = $ebayUploadInstance->upload( $SimpleXml[0], $SimpleXml[1] );
							if ( ! is_array( $uploadOnEbay ) && ! empty( $uploadOnEbay ) ) {
								echo json_encode(
									array(
										'status'  => 400,
										'message' => 'No Profile Assigned to the product.',
										'prodid'  => $prodIDs,
										'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
									)
								);
								die;
							}
							if ( isset( $uploadOnEbay['Ack'] ) ) {
								if ( 'Warning' == $uploadOnEbay['Ack'] || 'Success' == $uploadOnEbay['Ack'] ) {
									$ebayID = $uploadOnEbay['ItemID'];
									update_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id, $ebayID );
									echo json_encode(
										array(
											'status'  => 200,
											'message' => 'Product Uploaded Successfully',
											'prodid'  => $prodIDs,
											'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
										)
									);
									die;
								} else {
									$error = '';

									if ( isset( $uploadOnEbay['Errors'][0] ) ) {
										foreach ( $uploadOnEbay['Errors'] as $key => $value ) {
											if ( 'Error' == $value['SeverityCode'] ) {
												$error_data = str_replace( array( '<', '>' ), array( '{', '}' ), $value['LongMessage'] );
												$error     .= $error_data . '<br>';
											}
										}
									} else {
										$error_data = str_replace( array( '<', '>' ), array( '{', '}' ), $uploadOnEbay['Errors']['LongMessage'] );
										$error     .= $error_data . '<br>';
									}
									echo json_encode(
										array(
											'status'  => 400,
											'message' => $error,
											'prodid'  => $prodIDs,
											'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
										)
									);
									die;
								}
							} else {
								echo json_encode(
									array(
										'status'  => 400,
										'message' => 'Some Error Occured! Please Try again after sometime.',
										'prodid'  => $prodIDs,
										'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',

									)
								);
								die;
							}
						}
					} elseif ( 'relist_product' == $operation ) {
						$prodIDs          = $product_id[0];
						$wc_product       = wc_get_product( $prodIDs );
						$already_uploaded = get_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id, true );
						if ( $already_uploaded ) {
							$itemIDs[ $prodIDs ] = $already_uploaded;
							require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
							$ebayUploadInstance = EbayUpload::get_instance( $siteID, $token );
							$itemId             = get_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id, true );
							$check_stauts_xml   = '
			<?xml version="1.0" encoding="utf-8"?>
			<GetItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
			  <RequesterCredentials>
				<eBayAuthToken>' . $token . '</eBayAuthToken>
			  </RequesterCredentials>
			  <DetailLevel>ReturnAll</DetailLevel>
			  <ItemID>' . $itemId . '</ItemID>
			</GetItemRequest>';
							$itemDetails        = $ebayUploadInstance->get_item_details( $check_stauts_xml );
							if ( 'Success' == $itemDetails['Ack'] || 'Warning' == $itemDetails['Ack'] ) {
								if ( ! empty( $itemDetails['Item']['ListingDetails']['EndingReason'] ) || 'Completed' == $itemDetails['Item']['SellingStatus']['ListingStatus'] ) {
									update_post_meta( $prodIDs, '_ced_ebay_relist_item_id_' . $user_id, $itemId );
									$relistXML = $ced_ebay_manager->prepareProductHtmlForRelist( $user_id, $prodIDs );
									require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
									$ebayUploadInstance = EbayUpload::get_instance( $siteID, $token );
									$relistOnEbay       = $ebayUploadInstance->relist( $relistXML );
									if ( is_array( $relistOnEbay ) && ! empty( $relistOnEbay ) ) {
										if ( isset( $relistOnEbay['Ack'] ) ) {
											if ( 'Warning' == $relistOnEbay['Ack'] || 'Success' == $relistOnEbay['Ack'] ) {
												$ebayID = $relistOnEbay['ItemID'];
												update_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id, $ebayID );
												echo json_encode(
													array(
														'status'  => 200,
														'message' => 'Product Re-Listed Successfully',
														'prodid'  => $prodIDs,
														'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
													)
												);
												die;
											} else {
												$error = '';
												if ( isset( $relistOnEbay['Errors'][0] ) ) {
													foreach ( $relistOnEbay['Errors'] as $key => $value ) {
														if ( 'Error' == $value['SeverityCode'] ) {
															$error .= $value['ShortMessage'] . '<br>';
														}
													}
												} else {
													$error .= $relistOnEbay['Errors']['ShortMessage'] . '<br>';
												}
												echo json_encode(
													array(
														'status'  => 400,
														'message' => $error,
														'prodid'  => $prodIDs,
														'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
													)
												);
												die;
											}
										}
									}
								} else {
									$archiveProducts = $ebayUploadInstance->endItems( $itemIDs );
									if ( is_array( $archiveProducts ) && ! empty( $archiveProducts ) ) {
										if ( isset( $archiveProducts['Ack'] ) ) {
											if ( 'Warning' == $archiveProducts['Ack'] || 'Success' == $archiveProducts['Ack'] ) {
												update_post_meta( $prodIDs, '_ced_ebay_relist_item_id_' . $user_id, $already_uploaded );
												$relistXML = $ced_ebay_manager->prepareProductHtmlForRelist( $user_id, $prodIDs );
												require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
												$ebayUploadInstance = EbayUpload::get_instance( $siteID, $token );
												$relistOnEbay       = $ebayUploadInstance->relist( $relistXML );
												if ( is_array( $relistOnEbay ) && ! empty( $relistOnEbay ) ) {
													if ( isset( $relistOnEbay['Ack'] ) ) {
														if ( 'Warning' == $relistOnEbay['Ack'] || 'Success' == $relistOnEbay['Ack'] ) {
															$ebayID = $relistOnEbay['ItemID'];
															update_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id, $ebayID );
															echo json_encode(
																array(
																	'status'  => 200,
																	'message' => 'Product Re-Listed Successfully',
																	'prodid'  => $prodIDs,
																	'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
																)
															);
															die;
														} else {
															$error = '';
															if ( isset( $relistOnEbay['Errors'][0] ) ) {
																foreach ( $relistOnEbay['Errors'] as $key => $value ) {
																	if ( 'Error' == $value['SeverityCode'] ) {
																		$error .= $value['ShortMessage'] . '<br>';
																	}
																}
															} else {
																$error .= $relistOnEbay['Errors']['ShortMessage'] . '<br>';
															}
															echo json_encode(
																array(
																	'status'  => 400,
																	'message' => $error,
																	'prodid'  => $prodIDs,
																	'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
																)
															);
															die;
														}
													}
												}
											} else {
												delete_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id );
												echo json_encode(
													array(
														'status'  => 400,
														'message' => 'Failed to end the listing on eBay. Please contact support.',
														'prodid'  => $prodIDs,
														'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',

													)
												);
												die;
											}
										}
									}
								}
							}
						} else {
							echo json_encode(
								array(
									'status'  => 400,
									'message' => __(
										'Product Not Found On eBay',
										'woocommerce-ebay-integration'
									),
									'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',

								)
							);
							die;
						}
					} elseif ( 'update_product' == $operation ) {
						$prodIDs          = $product_id[0];
						$wc_product       = wc_get_product( $prodIDs );
						$already_uploaded = get_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id, true );
						if ( $already_uploaded ) {
							$SimpleXml = $ced_ebay_manager->prepareProductHtmlForUpdate( $user_id, $prodIDs );
							if ( is_array( $SimpleXml ) && ! empty( $SimpleXml ) ) {
								require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
								$ebayUploadInstance = EbayUpload::get_instance( $siteID, $token );
							} elseif ( 'No Profile Assigned' == $SimpleXml ) {
								echo json_encode(
									array(
										'status'  => 400,
										'message' => 'No Profile Assigned to the product.',
										'prodid'  => $prodIDs,
										'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',

									)
								);
								die;
							} elseif ( 'No international country' == $SimpleXml ) {
								echo json_encode(
									array(
										'status'  => 400,
										'message' => 'Please select atleast one country in Shipping Template for International Shipping',
										'prodid'  => $prodIDs,
										'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
									)
								);
								die;
							}
							$uploadOnEbay = $ebayUploadInstance->update( $SimpleXml[0], $SimpleXml[1] );
							if ( is_array( $uploadOnEbay ) && ! empty( $uploadOnEbay ) ) {
								if ( isset( $uploadOnEbay['Ack'] ) ) {
									if ( 'Warning' == $uploadOnEbay['Ack'] || 'Success' == $uploadOnEbay['Ack'] ) {
										$ebayID = $uploadOnEbay['ItemID'];
										echo json_encode(
											array(
												'status'  => 200,
												'message' => 'Product Updated Successfully',
												'prodid'  => $prodIDs,
												'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',

											)
										);
										die;
									} else {
										$error = '';

										if ( isset( $uploadOnEbay['Errors'][0] ) ) {
											foreach ( $uploadOnEbay['Errors'] as $key => $value ) {
												if ( 'Error' == $value['SeverityCode'] ) {
													$error .= $value['ShortMessage'] . '<br>';
												}
											}
										} else {
											$error .= $uploadOnEbay['Errors']['ShortMessage'] . '<br>';
										}
										echo json_encode(
											array(
												'status'  => 400,
												'message' => $error,
												'prodid'  => $prodIDs,
												'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
											)
										);
										die;
									}
								}
							}
						} else {
							echo json_encode(
								array(
									'status'  => 400,
									'message' => __(
										'Product Not Found On eBay',
										'woocommerce-ebay-integration'
									),
									'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
								)
							);
							die;
						}
					} elseif ( 'remove_product' == $operation ) {
						$prodIDs          = $product_id[0];
						$wc_product       = wc_get_product( $prodIDs );
						$already_uploaded = get_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id, true );
						if ( $already_uploaded ) {
							$itemIDs[ $prodIDs ] = $already_uploaded;
							require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
							$ebayUploadInstance = EbayUpload::get_instance( $siteID, $token );
							$itemId             = get_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id, true );
							$check_stauts_xml   = '
			<?xml version="1.0" encoding="utf-8"?>
			<GetItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
			  <RequesterCredentials>
				<eBayAuthToken>' . $token . '</eBayAuthToken>
			  </RequesterCredentials>
			  <DetailLevel>ReturnAll</DetailLevel>
			  <ItemID>' . $itemId . '</ItemID>
			</GetItemRequest>';
							$itemDetails        = $ebayUploadInstance->get_item_details( $check_stauts_xml );
							if ( 'Success' == $itemDetails['Ack'] || 'Warning' == $itemDetails['Ack'] ) {
								if ( ! empty( $itemDetails['Item']['ListingDetails']['EndingReason'] ) || 'Completed' == $itemDetails['Item']['SellingStatus']['ListingStatus'] ) {
									delete_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id );
									delete_post_meta( $prodIDs, '_ced_ebay_relist_item_id_' . $user_id );
									echo json_encode(
										array(
											'status'  => 200,
											'message' => 'Product has been Reset!',
											'prodid'  => $prodIDs,
											'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
										)
									);
									die;
								}
							} elseif ( 'Failure' == $itemDetails['Ack'] ) {
								if ( ! empty( $itemDetails['Errors']['ErrorCode'] ) && '17' == $itemDetails['Errors']['ErrorCode'] ) {
									delete_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id );
									delete_post_meta( $prodIDs, '_ced_ebay_relist_item_id_' . $user_id );
									echo json_encode(
										array(
											'status'  => 200,
											'message' => 'Product has been Reset!',
											'prodid'  => $prodIDs,
											'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
										)
									);
									die;
								}
							}

							$archiveProducts = $ebayUploadInstance->endItems( $itemIDs );
							if ( is_array( $archiveProducts ) && ! empty( $archiveProducts ) ) {
								if ( isset( $archiveProducts['Ack'] ) ) {
									if ( 'Warning' == $archiveProducts['Ack'] || 'Success' == $archiveProducts['Ack'] ) {
										delete_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id );
										delete_post_meta( $prodIDs, '_ced_ebay_relist_item_id_' . $user_id );
										echo json_encode(
											array(
												'status'  => 200,
												'message' => 'Product Deleted Successfully',
												'prodid'  => $prodIDs,
												'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
											)
										);
										die;
									} else {
										if ( 1047 == $archiveProducts['EndItemResponseContainer']['Errors']['ErrorCode'] ) {
											delete_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id );
											delete_post_meta( $prodIDs, '_ced_ebay_relist_item_id_' . $user_id );
										}
										$endResponse = $archiveProducts['EndItemResponseContainer']['Errors']['LongMessage'];
										echo json_encode(
											array(
												'status'  => 400,
												'message' => $endResponse,
												'prodid'  => $prodIDs,
												'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
											)
										);
										die;
									}
								}
							}
						} else {
							echo json_encode(
								array(
									'status'  => 400,
									'message' => __(
										'Product Not Found On eBay',
										'woocommerce-ebay-integration'
									),
									'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
								)
							);
							die;
						}
					} elseif ( 'update_stock' == $operation ) {
						$prodIDs          = $product_id[0];
						$wc_product       = wc_get_product( $prodIDs );
						$already_uploaded = get_post_meta( $prodIDs, '_ced_ebay_listing_id_' . $user_id, true );
						if ( $already_uploaded ) {
							require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
							$ebayUploadInstance = EbayUpload::get_instance( $siteID, $token );
							$SimpleXml          = $ced_ebay_manager->prepareProductHtmlForUpdateStock( $user_id, $prodIDs );
							if ( is_array( $SimpleXml ) && ! empty( $SimpleXml ) ) {

								foreach ( $SimpleXml as $key => $value ) {
									$uploadOnEbay[] = $ebayUploadInstance->cedEbayUpdateInventory( $value );
								}
							} else {

								$uploadOnEbay = $ebayUploadInstance->cedEbayUpdateInventory( $SimpleXml );
							}

							if ( is_array( $uploadOnEbay ) && ! empty( $uploadOnEbay[0] ) ) {
								foreach ( $uploadOnEbay as $key => $inventory_update ) {
									if ( isset( $inventory_update['Ack'] ) ) {
										if ( 'Warning' == $inventory_update['Ack'] || 'Success' == $inventory_update['Ack'] ) {
											$ebayID = $inventory_update['ItemID'];
											echo json_encode(
												array(
													'status'  => 200,
													'message' => 'Stock Updated Successfully',
													'prodid'  => $prodIDs,
													'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
												)
											);
											die;
										} else {

											$error = '';
											if ( isset( $inventory_update['Errors'][0] ) ) {
												foreach ( $inventory_update['Errors'] as $key => $value ) {
													if ( 'Error' == $value['SeverityCode'] ) {
														$error .= $value['ShortMessage'] . '<br>';
													}
												}
											} else {
												$error .= $inventory_update['Errors']['ShortMessage'] . '<br>';
											}
										}
									}
								}
								echo json_encode(
									array(
										'status'  => 400,
										'message' => $error,
										'prodid'  => $prodIDs,
										'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
									)
								);
								die;
							} else {
								if ( isset( $uploadOnEbay['Ack'] ) ) {
									if ( 'Warning' == $uploadOnEbay['Ack'] || 'Success' == $uploadOnEbay['Ack'] ) {
										echo json_encode(
											array(
												'status'  => 200,
												'message' => 'Stock Updated Successfully',
												'prodid'  => $prodIDs,
												'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
											)
										);
										die;
									} else {

										$error = '';
										if ( isset( $uploadOnEbay['Errors'][0] ) ) {
											foreach ( $uploadOnEbay['Errors'] as $key => $value ) {
												if ( 'Error' == $value['SeverityCode'] ) {
													$error .= $value['ShortMessage'] . '<br>';
												}
											}
										} else {
											$error .= $uploadOnEbay['Errors']['ShortMessage'] . '<br>';
										}
										echo json_encode(
											array(
												'status'  => 400,
												'message' => $error,
												'prodid'  => $prodIDs,
												'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
											)
										);
										die;
									}
								}
							}
						} else {
							echo json_encode(
								array(
									'status'  => 400,
									'message' => __(
										'Product Not Found On eBay',
										'woocommerce-ebay-integration'
									),
									'title'   => ! empty( $wc_product->get_title() ) ? $wc_product->get_title() : '',
								)
							);
							die;
						}
					} elseif ( 'update_ad_status' == $operation ) {
						$prodID                = $product_id[0];
						$promoted_listing_data = get_post_meta( $prodID, '_ced_ebay_promoted_listings_ad_data_' . $user_id, true );
						$prod_ad_id            = isset( $promoted_listing_data['adId'] ) ? $promoted_listing_data['adId'] : '';
						$prod_campaign_id      = isset( $promoted_listing_data['campaignId'] ) ? $promoted_listing_data['campaignId'] : '';
						$marketingRequestFile  = CED_EBAY_DIRPATH . 'admin/ebay/lib/cedMarketingRequest.php';
						$renderDependency      = $this->renderDependency( $marketingRequestFile );
						if ( $renderDependency ) {
							$cedMarketingRequest = new Ced_Marketing_API_Request( $siteID );
							$user_access_token   = get_transient( 'ced_ebay_user_access_token_' . $user_id );
							if ( empty( $user_access_token ) ) {
								echo json_encode(
									array(
										'status'  => 'error',
										'message' => 'User token has expired. Please login and try again.',
									)
								);
								die;
							}
							$endpoint = 'ad_campaign/' . $prod_campaign_id . '/ad/' . $prod_ad_id;
							$response = $cedMarketingRequest->sendHttpRequestForCampaignAPI( '', $endpoint, $user_access_token, 'GET' );
							$response = json_decode( $response );
							if ( isset( $response->errors[0]->errorId ) && '' != $response->errors[0]->errorId ) {
								if ( 35044 == $response->errors[0]->errorId ) {
									delete_post_meta( $prodID, '_ced_ebay_promoted_listings_ad_data_' . $user_id );
									echo json_encode(
										array(
											'status'  => 200,
											'message' => __(
												'Ad Status Successfully Updated. You have ended this Ad.',
												'woocommerce-ebay-integration'
											),
										)
									);
									die;
								}
								echo json_encode(
									array(
										'status'  => 400,
										'message' => __(
											$response->errors[0]->message,
											'woocommerce-ebay-integration'
										),
									)
								);
								die;
							} else {
								echo json_encode(
									array(
										'status'  => 200,
										'message' => __(
											'Ad is still Active',
											'woocommerce-ebay-integration'
										),
									)
								);
								die;
							}
						}
					}
				}
			} else {
				echo json_encode(
					array(
						'status'  => 400,
						'message' => 'We are not able to connect to eBay at the moment. Please try again later. If the issue persists, please contact support.',
						'prodid'  => $prodIDs,
						'title'   => 'Error',
					)
				);
				die;
			}
		}
	}




	public function ced_ebay_create_new_description_template( $req_action ) {
		$allowed_html  = array(
			'a'          => array(
				'class' => array(),
				'href'  => array(),
				'rel'   => array(),
				'title' => array(),
			),
			'span'       => array(
				'class' => array(),
				'style' => array(),
			),
			'abbr'       => array(
				'title' => array(),
			),
			'b'          => array(),
			'blockquote' => array(
				'cite' => array(),
			),
			'cite'       => array(
				'title' => array(),
			),
			'code'       => array(),
			'del'        => array(
				'datetime' => array(),
				'title'    => array(),
			),
			'dd'         => array(),
			'div'        => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'dl'         => array(),
			'dt'         => array(),
			'em'         => array(),
			'h1'         => array(),
			'h2'         => array(),
			'h3'         => array(),
			'h4'         => array(),
			'h5'         => array(),
			'h6'         => array(),
			'i'          => array(),
			'img'        => array(
				'alt'    => array(),
				'class'  => array(),
				'height' => array(),
				'src'    => array(),
				'width'  => array(),
			),
			'li'         => array(
				'class' => array(),
			),
			'ol'         => array(
				'class' => array(),
			),
			'p'          => array(
				'class' => array(),
				'style' => array(),
				'align' => array(),
			),
			'q'          => array(
				'cite'  => array(),
				'title' => array(),
			),
			'span'       => array(
				'class' => array(),
				'title' => array(),
				'style' => array(),
			),
			'strike'     => array(),
			'strong'     => array(),
			'ul'         => array(
				'class' => array(),
			),
		);
		$upload_dir    = wp_upload_dir();
		$templates_dir = $upload_dir['basedir'] . '/ced-ebay/templates/';
		if ( ! empty( $req_action ) && 'ced_ebay_add_new_template' == $req_action ) {
			$template_name  = isset( $_REQUEST['ced_ebay_template_name'] ) ? sanitize_text_field( $_REQUEST['ced_ebay_template_name'] ) : false;
			$template_info  = "/* \n";
			$template_info .= "Template: $template_name\n";
			$template_info .= "*/\n";
			$custom_html    = isset( $_REQUEST['ced_ebay_custom_html'] ) ? wp_kses( $_REQUEST['ced_ebay_custom_html'], $allowed_html ) : false;
			$dirname        = trim( strtolower( sanitize_file_name( $template_name ) ), '.' );
			// TODO: if $dirname is empty, don't create a template.
			$ced_template_dir = $templates_dir . $dirname;
			// if ( is_dir( $ced_template_dir ) ) {
			// TODO: if folder exists, show error message
			// }
				$result = mkdir( $ced_template_dir );
			if ( false === $result ) {
				// TODO: show error message when we can't create the template folder.
				return false;
			} else {
				file_put_contents( $ced_template_dir . '/template.html', $custom_html );
				file_put_contents( $ced_template_dir . '/info.txt', $template_info );
			}
		} elseif ( ! empty( $req_action ) && 'ced_ebay_edit_template' == $req_action ) {
			$upload_dir    = wp_upload_dir();
			$foldername    = isset( $_REQUEST['template'] ) ? sanitize_text_field( $_REQUEST['template'] ) : false;
			$folderpath    = $upload_dir['basedir'] . '/ced-ebay/templates/' . $foldername;
			$template_html = @file_get_contents( $folderpath . '/template.html' );
			if ( file_exists( $folderpath . '/info.txt' ) ) {
				$template_header = array(
					'Template' => 'Template',
				);
				$template_data   = get_file_data( $folderpath . '/info.txt', $template_header, 'theme' );
				$template_name   = $template_data['Template'];
			}
		}
	}



	public function ced_ebay_ajax_live_search_categories() {
		$check_ajax = check_ajax_referer( 'ced-ebay-ajax-seurity-string', 'ajax_nonce' );
		if ( $check_ajax ) {
			global $wpdb, $wp;
			$search_value = isset( $_POST['search_value'] ) ? sanitize_text_field( $_POST['search_value'] ) : false;
			$user_id      = isset( $_POST['userid'] ) ? sanitize_text_field( $_POST['userid'] ) : false;
			if ( $search_value ) {
				$query_results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT DISTINCT $wpdb->posts.*
            FROM $wpdb->posts, $wpdb->postmeta
            WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id
            AND (
                ($wpdb->postmeta.meta_key = '_sku' AND $wpdb->postmeta.meta_value LIKE %s)
                OR
                ($wpdb->posts.post_title LIKE %s)
            )
            AND $wpdb->posts.post_status = 'publish'
            AND $wpdb->posts.post_type = 'product'
            ORDER BY $wpdb->posts.post_date DESC",
						'%' . $wpdb->esc_like( $search_value ) . '%',
						'%' . $wpdb->esc_like( $search_value ) . '%'
					)
				);
			}

			if ( ! empty( $query_results ) ) {
				$output = '<div class="ced-box"><h3 class="ced-h3">Results</h3><ul style="max-height:250px;overflow-y:auto;overflow-x:hidden;">';
				foreach ( $query_results as $result ) {
					$product_filter_url = admin_url( 'admin.php?page=ced_ebay&section=products-view&user_id=' . $user_id . '&prodID=' . $result->ID );
					$output            .= '<li><a href="' . esc_attr( $product_filter_url ) . '">' . $result->post_title . ' (ID: ' . $result->ID . ')</a></li>';
				}
				$output .= '</ul></div>';
				wp_send_json(
					array(
						'status' => 'success',
						'output' => $output,
					)
				);
			}
		}
	}


	public function ced_ebay_duplicate_product_exclude_meta( $metakeys = array() ) {
		global $wpdb;
		$shopDetails = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ced_ebay_accounts", 'ARRAY_A' );
		foreach ( $shopDetails as $key => $value ) {
			$metakeys[] = '_ced_ebay_listing_id_' . $value['user_id'];
		}
		return $metakeys;
	}





	public function renderDependency( $file ) {
		if ( null != $file || '' != $file ) {
			require_once "$file";
			return true;
		}
		return false;
	}
	public function loadDependency() {
		require_once CED_EBAY_DIRPATH . 'admin/ebay/class-ebay.php';
		$this->ced_ebay_manager = Class_Ced_EBay_Manager::get_instance();

		require_once ABSPATH . '/wp-admin/includes/file.php';
	}



}
