<?php
/**
 * Main class for handling reqests.
 *
 * @since      1.0.0
 *
 * @package    eBay Integration for Woocommerce
 * @subpackage eBay Integration for Woocommerce/marketplaces/ebay
 */

if ( ! class_exists( 'Class_Ced_EBay_Manager' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    eBay Integration for Woocommerce
	 * @subpackage eBay Integration for Woocommerce/marketplaces/ebay
	 */
	class Class_Ced_EBay_Manager {


		/**
		 * The Instace of CED_ebay_ebay_Manager.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of CED_ebay_ebay_Manager class.
		 */
		private static $_instance;
		private static $authorization_obj;
		private static $client_obj;
		/**
		 * CED_ebay_ebay_Manager Instance.
		 *
		 * Ensures only one instance of CED_ebay_ebay_Manager is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_ebay_ebay_Manager instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		public $marketplaceID   = 'ebay';
		public $marketplaceName = 'ebay';

		/**
		 * Constructor.
		 *
		 * Registering actions and hooks for ebay.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->loadDependency();
		}

		/*
		 *
		 *Creating Auto Profiles
		 *
		 *
		 */

		public function ced_ebay_createAutoProfiles( $ebayMappedCategories = array(), $ebayMappedCategoriesName = array(), $ebayMappedSecondaryCategories = array(), $ebayMappedSecondaryCategoriesName = array(), $ebayStoreMappedCustomCategories = array(), $ebayStoreMappedSecondaryCategories = array(), $ebayStoreId = '' ) {
			global $wpdb;
			$logger                      = wc_get_logger();
			$wooStoreCategories          = get_terms( 'product_cat' );
			$alreadyMappedCategories     = get_option( 'ced_woo_ebay_mapped_categories', array() );
			$alreadyMappedCategoriesName = get_option( 'ced_woo_ebay_mapped_categories_name', array() );
			if ( ! empty( $ebayMappedCategories ) ) {
				foreach ( $ebayMappedCategories as $key => $value ) {
					$profileAlreadyCreated = get_term_meta( $key, 'ced_ebay_profile_created_' . $ebayStoreId, true );
					$createdProfileId      = get_term_meta( $key, 'ced_ebay_profile_id_' . $ebayStoreId, true );
					if ( 'yes' == $profileAlreadyCreated && '' != $createdProfileId ) {

						$newProfileNeedToBeCreated = $this->checkIfNewProfileNeedToBeCreated( $key, $value, $ebayStoreId );
						if ( ! $newProfileNeedToBeCreated ) {
							continue;
						} else {
							$this->resetMappedCategoryData( $ebayMappedCategories, $value, $ebayStoreId );
						}
					}

					$wooCategories      = array();
					$categoryAttributes = array();
					$profileName        = isset( $ebayMappedCategoriesName[ $value ] ) ? $ebayMappedCategoriesName[ $value ] : 'Profile for eBay - Category Id : ' . $value;

					$profile_id = $wpdb->get_results( $wpdb->prepare( "SELECT `id` FROM {$wpdb->prefix}ced_ebay_profiles WHERE `profile_name` = %s AND `user_id` = %s", $profileName, $ebayStoreId ), 'ARRAY_A' );
					foreach ( $ebayMappedCategories as $key1 => $value1 ) {
						if ( $value1 == $value ) {
							$wooCategories[] = $key1;
						}
					}
					if ( ! isset( $profile_id[0]['id'] ) && empty( $profile_id[0]['id'] ) ) {
						$is_active       = 1;
						$marketplaceName = 'eBay';

						$profileData = array();
						$profileData = $this->ced_ebay_prepareProfileData( $ebayStoreId, $value, $wooCategories );

						$profileDetails = array(
							'profile_name'   => $profileName,
							'profile_status' => 'active',
							'profile_data'   => json_encode( $profileData ),
							'user_id'        => $ebayStoreId,
							'woo_categories' => json_encode( $wooCategories ),
						);
						$logger->info( wc_print_r( $profileDetails, true ) );
						$profile_id = $this->inserteBayProfile( $profileDetails );
					} else {
						$woo_categories     = array();
						$profile_id         = $profile_id[0]['id'];
						$profile_categories = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id` = %d ", $profile_id ), 'ARRAY_A' );
						$woo_categories     = json_decode( $profile_categories[0]['woo_categories'], true );
						global $wpdb;
						$tableName        = $wpdb->prefix . 'ced_ebay_profiles';
						$woo_categories[] = $key;
						$wpdb->update(
							$tableName,
							array(
								'woo_categories' => json_encode( $woo_categories ),
							),
							array( 'id' => $profile_id ),
							array( '%s' )
						);
					}

					if ( empty( $woo_categories ) ) {
						foreach ( $wooCategories as $key12 => $value12 ) {
							if ( ! empty( $ebayStoreMappedCustomCategories[ $value12 ] ) ) {
								update_term_meta( $value12, 'ced_ebay_mapped_to_store_category_' . $ebayStoreId, $ebayStoreMappedCustomCategories[ $value12 ] );
							}
							if ( ! empty( $ebayStoreMappedSecondaryCategories[ $value12 ] ) ) {
								update_term_meta( $value12, 'ced_ebay_mapped_to_store_secondary_category_' . $ebayStoreId, $ebayStoreMappedSecondaryCategories[ $value12 ] );
							}
							update_term_meta( $value12, 'ced_ebay_profile_created_' . $ebayStoreId, 'yes' );
							update_term_meta( $value12, 'ced_ebay_profile_id_' . $ebayStoreId, $profile_id );
							update_term_meta( $value12, 'ced_ebay_mapped_category_' . $ebayStoreId, $value );
						}
					} else {
						foreach ( $woo_categories as $key12 => $value12 ) {

							if ( ! empty( $ebayStoreMappedCustomCategories[ $value12 ] ) ) {
								update_term_meta( $value12, 'ced_ebay_mapped_to_store_category_' . $ebayStoreId, $ebayStoreMappedCustomCategories[ $value12 ] );
							}
							if ( ! empty( $ebayStoreMappedSecondaryCategories[ $value12 ] ) ) {
								update_term_meta( $value12, 'ced_ebay_mapped_to_store_secondary_category_' . $ebayStoreId, $ebayStoreMappedSecondaryCategories[ $value12 ] );
							}

							update_term_meta( $value12, 'ced_ebay_profile_created_' . $ebayStoreId, 'yes' );
							update_term_meta( $value12, 'ced_ebay_profile_id_' . $ebayStoreId, $profile_id );
							update_term_meta( $value12, 'ced_ebay_mapped_category_' . $ebayStoreId, $value );
						}
					}
				}
			}

			if ( ! empty( $ebayMappedSecondaryCategories ) ) {
				foreach ( $ebayMappedSecondaryCategories as $key2 => $value2 ) {
					foreach ( $ebayMappedSecondaryCategories as $key1Secondary => $value1Secondary ) {
						if ( $value1Secondary == $value2 ) {
							$wooCategoriesSecondary[] = $key1Secondary;
						}
					}

					foreach ( $wooCategoriesSecondary as $key12Secondary => $value12Secondary ) {
						update_term_meta( $value12Secondary, 'ced_ebay_mapped_secondary_category_' . $ebayStoreId, $value2 );
					}
				}
			}
		}


		/*
		 *
		 *Inserting and Saving Profiles
		 *
		 *
		 */

		public function inserteBayProfile( $profileDetails ) {

			global $wpdb;
			$profileTableName = $wpdb->prefix . 'ced_ebay_profiles';

			$wpdb->insert( $profileTableName, $profileDetails, array( '%s' ) );

			$profileId = $wpdb->insert_id;
			return $profileId;
		}

		/*
		 *
		 *Preparing profile data for saving
		 *
		 *
		 */

		public function ced_ebay_prepareProfileData( $ebayStoreId, $ebayCategoryId, $wooCategories = '' ) {
			$profileData                                  = array();
			$profileData['_umb_ebay_category']['default'] = $ebayCategoryId;
			$profileData['_umb_ebay_category']['metakey'] = null;
			return $profileData;
		}

		/*
		 *
		 *Checking if new profile to be created for woo category
		 *
		 *
		 */

		public function checkIfNewProfileNeedToBeCreated( $wooCategoryId = '', $eBayCategoryId = '', $ebayStoreId = '' ) {

			$oldeBayCategoryMapped = get_term_meta( $wooCategoryId, 'ced_ebay_mapped_category_' . $ebayStoreId, true );
			if ( $oldeBayCategoryMapped == $eBayCategoryId ) {
				return false;
			} else {
				return true;
			}
		}

		/*
		 *
		 *Updating profile for a woo category if mapped again
		 *   *
		 *
		 */

		public function resetMappedCategoryData( $wooCategoryIds = '', $eBayCategoryIds = '', $ebayStoreId = '' ) {
			foreach ( $wooCategoryIds as $key => $value ) {

					update_term_meta( $key, 'ced_ebay_mapped_category_' . $ebayStoreId, $value );

					delete_term_meta( $key, 'ced_ebay_profile_created_' . $ebayStoreId );

					$createdProfileId = get_term_meta( $key, 'ced_ebay_profile_id_' . $ebayStoreId, true );

					delete_term_meta( $key, 'ced_ebay_profile_id_' . $ebayStoreId );

					$this->removeCategoryMappingFromProfile( $createdProfileId, $key );

			}

		}

		/*
		 *
		 *removing previous mapped profile to a woo category
		 *
		 *
		 */

		public function removeCategoryMappingFromProfile( $createdProfileId = '', $wooCategoryId = '' ) {

			global $wpdb;
			$profileTableName = $wpdb->prefix . 'ced_ebay_profiles';

			$profile_data = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id`=%s", $createdProfileId ), 'ARRAY_A' );

			if ( is_array( $profile_data ) ) {

				$profile_data  = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
				$wooCategories = isset( $profile_data['woo_categories'] ) ? json_decode( $profile_data['woo_categories'], true ) : array();
				if ( is_array( $wooCategories ) && ! empty( $wooCategories ) ) {
					$categories = array();
					foreach ( $wooCategories as $key => $value ) {
						if ( $value != $wooCategoryId ) {
							$categories[] = $value;
						}
					}
					$categories = json_encode( $categories );
					$wpdb->update( $profileTableName, array( 'woo_categories' => $categories ), array( 'id' => $createdProfileId ), array( '%s' ) );
				}
			}
		}

		/*
		 *
		 *Loading All the required files
		 *
		 *
		 */

		public function loadDependency() {

			$fileProducts = CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayProducts.php';
			if ( file_exists( $fileProducts ) ) {
				require_once $fileProducts;
			}
			$this->ebayProductsInstance = Class_Ced_EBay_Products::get_instance();
		}

		public function prepareProductHtmlForUpload( $userId, $proIDs = array() ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->ebayProductsInstance->ced_ebay_prepareDataForUploading( $proIDs, $userId );
			return $response;

		}

		public function prepareProductHtmlForUpdate( $userId, $proIDs = array() ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->ebayProductsInstance->ced_ebay_prepareDataForUpdating( $userId, $proIDs );
			return $response;
		}
		public function prepareProductHtmlForUpdateStock( $userId, $proIDs = array(), $notAjax = false ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}

			$shop_data = ced_ebay_get_shop_data( $userId );
			if ( ! empty( $shop_data ) ) {
				$siteID      = $shop_data['site_id'];
				$token       = $shop_data['access_token'];
				$getLocation = $shop_data['location'];
			}
			$itemIDs = array();
			foreach ( $proIDs as $prodID ) {
				$itemID  = get_post_meta( $prodID, '_ced_ebay_listing_id_' . $userId, true );
				$itemID  = isset( $itemID ) ? $itemID : false;
				$product = wc_get_product( $prodID );
				if ( '' != $itemID ) {
					if ( $product->is_type( 'variable' ) ) {
						require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
						$ebayUploadInstance = EbayUpload::get_instance( $siteID, $token );
						$ebay_item_data_xml = '
			<?xml version="1.0" encoding="utf-8"?>
			<GetItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
			  <RequesterCredentials>
				<eBayAuthToken>' . $token . '</eBayAuthToken>
			  </RequesterCredentials>
			  <DetailLevel>ReturnAll</DetailLevel>
			  <ItemID>' . $itemID . '</ItemID>
			</GetItemRequest>';

						$ebay_item_details = $ebayUploadInstance->get_item_details( $ebay_item_data_xml );
						if ( ! isset( $ebay_item_details['Item']['Variations']['Variation'][0] ) ) {
							$tempVariationList = array();
							$tempVariationList = $ebay_item_details['Item']['Variations']['Variation'];
							unset( $ebay_item_details['Item']['Variations']['Variation'] );
							$ebay_item_details['Item']['Variations']['Variation'][] = $tempVariationList;
						}

						if ( ! empty( $ebay_item_details['Item']['Variations']['Variation'] ) ) {
							$ebay_variation_sku          = array();
							$ebay_variation_count        = count( $ebay_item_details['Item']['Variations']['Variation'] );
							$ebay_variation_sku['count'] = $ebay_variation_count;
							foreach ( $ebay_item_details['Item']['Variations']['Variation'] as $key => $ebay_variation ) {
								if ( ! empty( $ebay_variation['SKU'] ) ) {
									$ebay_variation_sku['sku'][] = $ebay_variation['SKU'];
								}
							}
						}

						$childIds = $product->get_children();
						foreach ( $childIds as $key => $value ) {
							$itemIDs[ $value ] = $itemID;
						}
					} else {
						$itemIDs[ $prodID ] = $itemID;
					}
				}
			}
			if ( ! empty( $itemIDs ) ) {
				if ( count( $itemIDs ) > 4 ) {
					$itemIDs  = array_chunk( $itemIDs, 4, true );
					$response = array();
					foreach ( $itemIDs as $prodId => $itemId ) {
						$response[] = $this->ebayProductsInstance->ced_ebay_prepareDataForUpdatingStock( $userId, $itemId, $notAjax, $ebay_variation_sku );

					}
				} else {
					$response = $this->ebayProductsInstance->ced_ebay_prepareDataForUpdatingStock( $userId, $itemIDs, $notAjax, $ebay_variation_sku );
				}
			}
			return $response;
		}

		public function ced_ebay_get_seller_preferences( $user_id, $token, $site_id ) {
			if ( ! empty( $token ) && '' != $site_id ) {
				if ( file_exists( CED_EBAY_DIRPATH . 'admin/ebay/lib/cedRequest.php' ) ) {
					require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/cedRequest.php';
					$verb        = 'GetUserPreferences';
					$request_xml = '<?xml version="1.0" encoding="utf-8" ?>
					<GetUserPreferencesRequest xmlns="urn:ebay:apis:eBLBaseComponents"> 
  <RequesterCredentials> 
    <eBayAuthToken>' . $token . '</eBayAuthToken> 
  </RequesterCredentials>
  <ShowSellerProfilePreferences>true</ShowSellerProfilePreferences>
  <ShowSellerReturnPreferences>true</ShowSellerReturnPreferences>
  <ShowSellerPaymentPreferences>true</ShowSellerPaymentPreferences>
  <ShowGlobalShippingProgramPreference>true</ShowGlobalShippingProgramPreference>
  <ShowOutOfStockControlPreference>true</ShowOutOfStockControlPreference> 
</GetUserPreferencesRequest>';
					$ced_request = new Cedrequest( $site_id, $verb );
					$response    = $ced_request->sendHttpRequest( $request_xml );
					if ( ! empty( $response ) && 'Success' == $response['Ack'] ) {
						update_option( 'ced_ebay_seller_preferences_' . $user_id, $response );
						if ( ! empty( $response['OutOfStockControlPreference'] ) ) {
							update_option( 'ced_ebay_out_of_stock_preference_' . $user_id, $response['OutOfStockControlPreference'] );
							return array(
								'out_of_stock_control' => $response['OutOfStockControlPreference'],
							);
						}
					} else {
						ced_ebay_log_data( 'Error while getting the response data', 'ced_ebay_get_seller_preferences' );
						return 'api-error';
					}
				} else {
					return 'request-file-not-found';
				}
			}
		}

		public function ced_ebay_get_manually_ended_listings( $token, $site_id, $page_number = 1 ) {
			if ( ! empty( $token ) && '' != $site_id ) {
				if ( file_exists( CED_EBAY_DIRPATH . 'admin/ebay/lib/cedRequest.php' ) ) {
					require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/cedRequest.php';
					$verb        = 'GetSellerList';
					$currentime  = time();
					$toDate      = $currentime - ( 1 * 60 );
					$fromDate    = $currentime - ( 2 * 24 * 60 * 60 );
					$offset      = '.000Z';
					$toDate      = gmdate( 'Y-m-d', $toDate ) . 'T' . gmdate( 'H:i:s', $toDate ) . $offset;
					$fromDate    = gmdate( 'Y-m-d', $fromDate ) . 'T' . gmdate( 'H:i:s', $fromDate ) . $offset;
					$request_xml = '<?xml version="1.0" encoding="utf-8" ?>
					<GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents"> 
  <RequesterCredentials> 
    <eBayAuthToken>' . $token . '</eBayAuthToken> 
  </RequesterCredentials>
 <EndTimeFrom>' . $fromDate . '</EndTimeFrom>
  <EndTimeTo>' . $toDate . '</EndTimeTo>
  <GranularityLevel>Coarse</GranularityLevel>
  <OutputSelector>EndingReason</OutputSelector>
    <OutputSelector>ItemID</OutputSelector>
      <OutputSelector>SKU</OutputSelector>
            <OutputSelector>PaginationResult</OutputSelector>
  <Pagination>
    <EntriesPerPage>100</EntriesPerPage>
    <PageNumber>' . $page_number . '</PageNumber>
  </Pagination>
</GetSellerListRequest>';
					$ced_request = new Cedrequest( $site_id, $verb );
					$response    = $ced_request->sendHttpRequest( $request_xml );
					if ( ! empty( $response ) && 'Success' == $response['Ack'] ) {
						return $response;
					} else {
						return 'api-error';
					}
				} else {
					return 'request-file-not-found';
				}
			}
		}


		public function ced_ebay_get_seller_transactions( $token, $site_id, $page_number = 1 ) {
			if ( ! empty( $token ) && '' != $site_id ) {
				if ( file_exists( CED_EBAY_DIRPATH . 'admin/ebay/lib/cedRequest.php' ) ) {
					require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/cedRequest.php';
					$verb        = 'GetSellerTransactions';
					$currentime  = time();
					$toDate      = $currentime - ( 1 * 60 );
					$fromDate    = $currentime - ( 2 * 24 * 60 * 60 );
					$offset      = '.000Z';
					$toDate      = gmdate( 'Y-m-d', $toDate ) . 'T' . gmdate( 'H:i:s', $toDate ) . $offset;
					$fromDate    = gmdate( 'Y-m-d', $fromDate ) . 'T' . gmdate( 'H:i:s', $fromDate ) . $offset;
					$request_xml = '<?xml version="1.0" encoding="utf-8" ?>
					<GetSellerTransactionsRequest xmlns="urn:ebay:apis:eBLBaseComponents"> 
  <RequesterCredentials> 
    <eBayAuthToken>' . $token . '</eBayAuthToken> 
  </RequesterCredentials>
 <ModTimeFrom>' . $fromDate . '</ModTimeFrom>
  <ModTimeTo>' . $toDate . '</ModTimeTo>
  <OutputSelector>TransactionArray</OutputSelector>
    <OutputSelector>MonetaryDetails</OutputSelector>
    <OutputSelector>ItemID</OutputSelector>
      <OutputSelector>SKU</OutputSelector>
            <OutputSelector>PaginationResult</OutputSelector>
  <Pagination>
    <EntriesPerPage>20</EntriesPerPage>
    <PageNumber>' . $page_number . '</PageNumber>
  </Pagination>
</GetSellerTransactionsRequest>';
					$ced_request = new Cedrequest( $site_id, $verb );
					$response    = $ced_request->sendHttpRequest( $request_xml );
					if ( ! empty( $response ) && 'Success' == $response['Ack'] ) {
						return $response;
					} else {
						return 'api-error';
					}
				} else {
					return 'request-file-not-found';
				}
			}
		}


		/*
		 * Function to prepare product html for re-listing
		 *
		*/
		public function prepareProductHtmlForRelist( $userId, $proIDs = array() ) {
			if ( ! is_array( $proIDs ) ) {
				$proIDs = array( $proIDs );
			}
			$response = $this->ebayProductsInstance->ced_ebay_prepareDataForReListing( $userId, $proIDs );
			return $response;
		}

		public function renderDependency( $file ) {
			if ( null != $file || '' != $file ) {
				require_once "$file";
				return true;
			}
			return false;
		}
	}
}
