<?php
if ( ! class_exists( 'Ebayauthorization' ) ) {
	class Ebayauthorization {


		private static $_instance;

		public $devID;
		public $appID;
		public $certID;
		public $serverUrl;
		public $loginURL;
		public $runame;
		public $compatLevel;
		public $siteID;
		public $oauthLoginUrl;
		public $oauthCodeGrantUrl;

		/**
		 * Get_instance Instance.
		 *
		 * Ensures only one instance of Ebayauthorization is loaded or can be loaded.
		 *
		userId
		 *
		 * @since 1.0.0
		 * @static
		 * @return get_instance instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		/**
		 * Constructor
		 */
		public function __construct() {
			$this->loadDepenedency();
			$this->devID             = $this->ebayConfigInstance->devID;
			$this->appID             = $this->ebayConfigInstance->appID;
			$this->certID            = $this->ebayConfigInstance->certID;
			$this->serverUrl         = $this->ebayConfigInstance->serverUrl;
			$this->loginURL          = $this->ebayConfigInstance->loginURL;
			$this->runame            = $this->ebayConfigInstance->runame;
			$this->oauthLoginUrl     = $this->ebayConfigInstance->oauthLoginUrl;
			$this->oauthCodeGrantUrl = $this->ebayConfigInstance->oauthCodeGrantUrl;
			$this->oAuthScope        = $this->ebayConfigInstance->oAuthScope;
			$this->compatLevel       = isset( $compatLevel ) ? $compatLevel : '';
			$this->siteID            = isset( $siteID ) ? $siteID : '';
		}


		public function getOAuthUrl( $siteID ) {
			$oauthUrl = $this->oauthLoginUrl . '?client_id=' . $this->appID . '&redirect_uri=' . $this->runame . '&response_type=code&scope=' . $this->oAuthScope . '&prompt=login&state=' . get_admin_url() . 'admin.php?bigcom=ced_woo_ebay_lister_' . $siteID;
			return $oauthUrl;
		}

		public function oauthRequestAccessToken( $code, $siteID, $grantType ) {
			$cedRequest = new Cedrequest( $siteID, '' );
			if ( 'refresh_token' == $grantType ) {
				$requestBody = 'grant_type=' . $grantType . '&refresh_token=' . $code . '&scope=' . $this->oAuthScope;

			} else {
				$requestBody = 'grant_type=' . $grantType . '&code=' . $code . '&redirect_uri=' . $this->runame;
			}
			$response = $cedRequest->sendHttpRequestForOAuth( $code, $requestBody );
			return $response;

		}

		public function getUserData( $access_token, $siteID ) {

			$requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>
			<GetUserRequest xmlns="urn:ebay:apis:eBLBaseComponents">
			  <RequesterCredentials>
			    <eBayAuthToken>' . $access_token . '</eBayAuthToken>
			  </RequesterCredentials>
			</GetUserRequest>';
			$verb           = 'GetUser';
			$cedRequest     = new Cedrequest( $siteID, $verb );
			$response       = $cedRequest->sendHttpRequest( $requestXmlBody );
			$wp_folder      = wp_upload_dir();
			$wp_upload_dir  = $wp_folder['basedir'];
			$wp_upload_dir  = $wp_upload_dir . '/ced-ebay/logs/';
			if ( ! is_dir( $wp_upload_dir ) ) {
					wp_mkdir_p( $wp_upload_dir, 0777 );
			}
			$log_file = $wp_upload_dir . 'user.txt';
			if ( $log_file ) {
				if ( file_exists( $log_file ) ) {
					wp_delete_file( $log_file );
				}
				file_put_contents( $log_file, PHP_EOL . 'Getting seller data...', FILE_APPEND );
			}
			ced_ebay_log_data( $response, 'ced_getUserData', $log_file );
			if ( isset( $response['Ack'] ) && 'Success' == $response['Ack'] ) {
				return $response['User'];
			}
			return $response;
		}

		public function getStoreData( $siteID, $user_id ) {
			$shop_data = ced_ebay_get_shop_data( $user_id );
			if ( ! empty( $shop_data ) ) {
				$siteID = $shop_data['site_id'];
				$token  = $shop_data['access_token'];
			}
			$requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>
			<GetStoreRequest xmlns="urn:ebay:apis:eBLBaseComponents">
				<RequesterCredentials>
					<eBayAuthToken>' . $token . '</eBayAuthToken>
				</RequesterCredentials>
			</GetStoreRequest>';
			$siteID         = $siteID;
			$verb           = 'GetStore';
			$cedRequest     = new Cedrequest( $siteID, $verb );
			$response       = $cedRequest->sendHttpRequest( $requestXmlBody );
			if ( isset( $response['Ack'] ) && 'Success' == $response['Ack'] ) {
				update_option( 'ced_ebay_store_data_' . $response['Store']['URLPath'], $response );
				return $response;
			}
			return false;

		}

		public $ebayConfigInstance;
		public $ebayConfig;
		public $cedRequestInstance;
		/**
		 * Function to get session id
		 *
		 * @name getSessionid
		 */
		public function loadDepenedency() {
			if ( is_file( __DIR__ . '/ebayConfig.php' ) ) {
				require_once 'ebayConfig.php';
				$this->ebayConfigInstance = Ebayconfig::get_instance();
			}
			if ( is_file( __DIR__ . '/cedRequest.php' ) ) {
				require_once 'cedRequest.php';
			}
		}
	}
}
