<?php
if ( ! class_exists( 'EbayGetCategories' ) ) {
	class EbayGetCategories {


		public $ebayConfigInstance;

		private static $_instance;

		public $siteID;

		public $token;
		/**
		 * Get_instance Instance.
		 *
		 * Ensures only one instance of CedAuthorization is loaded or can be loaded.
		 *
		userId
		 *
		 * @since 1.0.0
		 * @static
		 * @return get_instance instance.
		 */
		public static function get_instance( $siteID, $token ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self( $siteID, $token );
			}
			return self::$_instance;
		}
		/**
		 * Constructor
		 */
		public function __construct( $siteID, $token ) {
			$this->siteID = $siteID;
			$this->token  = $token;
			$this->loadDepenedency();

		}

		/*
		 * Get Categories form ebay
		 */
		public function GetCategories( $level = 1, $ParentcatID = null ) {
			$siteID      = $this->siteID;
			$verb        = 'GetCategories';
			$token       = $this->token;
			$requestBody = $this->getCategoryRequestBody( $siteID, $token );
			$cedRequest  = new Cedrequest( $siteID, $verb );
			$response    = $cedRequest->sendHttpRequest( $requestBody );
			if ( isset( $response['Ack'] ) && 'Success' == $response['Ack'] ) {
				return $response;
			}
			return false;
		}

		public function GetCategoryTree() {
			$siteID      = $this->siteID;
			$verb        = 'GetCategories';
			$token       = $this->token;
			$requestBody = $this->getCategoryTreeRequestBody( $siteID, $token );
			$cedRequest  = new Cedrequest( $siteID, $verb );
			$response    = $cedRequest->sendHttpRequest( $requestBody );
			if ( isset( $response['Ack'] ) && 'Success' == $response['Ack'] ) {
				return $response;
			}
			return false;
		}

		/*
		 * XML for get categories
		 */
		public function getCategoryRequestBody( $siteID, $token ) {
			$requestXmlBody  = '<?xml version="1.0" encoding="utf-8"?>';
			$requestXmlBody .= '<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$token</eBayAuthToken></RequesterCredentials>";
			$requestXmlBody .= '<CategorySiteID>' . $siteID . '</CategorySiteID>';
			$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
			$requestXmlBody .= '</GetCategoriesRequest>';
			return $requestXmlBody;
		}


		public function getCategoryTreeRequestBody( $siteID, $token, $ParentcatID = null ) {
			$requestXmlBody  = '<?xml version="1.0" encoding="utf-8"?>';
			$requestXmlBody .= '<GetCategoriesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			$requestXmlBody .= "<RequesterCredentials><eBayAuthToken>$token</eBayAuthToken></RequesterCredentials>";
			if ( null != $ParentcatID ) {
				$requestXmlBody .= '<CategoryParent>' . $ParentcatID . '</CategoryParent>';
			}
			$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel><ViewAllNodes>false</ViewAllNodes>';
			$requestXmlBody .= '</GetCategoriesRequest>';
			return $requestXmlBody;
		}

		public function GetJsonCategories( $level = 1, $ParentcatID = null ) {
			$folderName = CED_EBAY_DIRPATH . 'admin/ebay/lib/json/';
			$fileName   = $folderName . 'categoryLevel-' . $level . '.json';
			if ( file_exists( $fileName ) ) {
				$catDetails = file_get_contents( $fileName );
				$catDetails = json_decode( $catDetails, true );
				$catDetails = $catDetails['CategoryArray']['Category'];
				foreach ( $catDetails as $catDetail ) {
					if ( isset( $catDetail['CategoryParentID'] ) ) {
						if ( $catDetail['CategoryParentID'] == $ParentcatID ) {
							$finalCat[] = $catDetail;
						}
					}
				}
				if ( is_array( $finalCat ) && ! empty( $finalCat ) ) {
					return $finalCat;
				}
				return false;
			}
		}

		public function GetCategorySpecifics( $catID ) {
			$siteID = $this->siteID;
			$verb   = 'GetCategorySpecifics';
			$token  = $this->token;
			require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/cedMarketingRequest.php';
			$oAuthRequest = new Ced_Marketing_API_Request( $siteID );
			$response     = $oAuthRequest->sendHttpRequestForTaxonomyAPI( 'category_tree/' . $siteID . '/get_item_aspects_for_category?category_id=' . $catID, $token );
			$response     = json_decode( $response, true );
			if ( ! empty( $response['aspects'] ) ) {
				if ( ! isset( $response['aspects'][0] ) ) {
					$temp_response = $response['aspects'];
					unset( $response['aspects'] );
					$response['aspects'][] = $temp_response;
				}
				return $response['aspects'];
			}
			return false;
		}

		public function getCategorySpecificsRequestBody( $token, $catID ) {
			$requestXmlBody  = '<?xml version="1.0" encoding="utf-8"?>';
			$requestXmlBody .= '<GetCategorySpecificsRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			$requestXmlBody .= '<WarningLevel>High</WarningLevel>';
			$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
			$requestXmlBody .= '<CategorySpecific><CategoryID>' . $catID . '</CategoryID></CategorySpecific>';
			$requestXmlBody .= '<RequesterCredentials><eBayAuthToken>' . $token . '</eBayAuthToken></RequesterCredentials><MaxValuesPerName>1000</MaxValuesPerName>';
			$requestXmlBody .= '</GetCategorySpecificsRequest>';
			return $requestXmlBody;
		}

		public function GetCategoryFeatures( $catID, $limit ) {

			$siteID      = $this->siteID;
			$verb        = 'GetCategoryFeatures';
			$token       = $this->token;
			$requestBody = $this->getCategoryFeaturesRequestBody( $token, $catID, $limit );
			$cedRequest  = new Cedrequest( $siteID, $verb );
			$response    = $cedRequest->sendHttpRequest( $requestBody );
			if ( isset( $response['Ack'] ) && 'Success' == $response['Ack'] ) {
				return $response;
			}
			return false;
		}
		public function getCategoryFeaturesRequestBody( $token, $catID, $limits = array() ) {
			$requestXmlBody  = '<?xml version="1.0" encoding="utf-8"?>';
			$requestXmlBody .= '<GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
			$requestXmlBody .= '<RequesterCredentials><eBayAuthToken>' . $token . '</eBayAuthToken></RequesterCredentials>';
			$requestXmlBody .= '<CategoryID>' . $catID . '</CategoryID>';
			$requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
			$requestXmlBody .= '<ViewAllNodes>true</ViewAllNodes>';
			if ( is_array( $limits ) && ! empty( $limits ) ) {
				foreach ( $limits as $limit ) {
					$requestXmlBody .= '<FeatureID>' . $limit . '</FeatureID>';
				}
			}
			$requestXmlBody .= '</GetCategoryFeaturesRequest>';
			return $requestXmlBody;
		}
		/**
		 * Function loadDepenedency
		 *
		 * @name loadDepenedency
		 */
		public function loadDepenedency() {
			if ( is_file( __DIR__ . '/cedRequest.php' ) ) {
				require_once 'cedRequest.php';
			}
		}
	}
}
