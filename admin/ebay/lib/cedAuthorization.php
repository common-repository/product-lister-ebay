<?php
if ( ! class_exists( 'CedAuthorization' ) ) {
	class CedAuthorization {


		public $ebayConfigInstance;

		public $ebayAuthInstance;

		private static $_instance;
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
		}

		/**
		 * Function to get get auth url
		 *
		 * @name _doAuthorization
		 */
		public function _doAuthorization( $siteID ) {
			$authURL   = false;
			$sessionID = $this->ebayAuthInstance->getSessionid( $siteID );
			if ( null != $sessionID && '' != $sessionID ) {
				$authURL = $this->ebayAuthInstance->getAuthurl( $sessionID );
				return array(
					'status'    => '200',
					'authURL'   => $authURL,
					'sessionId' => $sessionID,
				);
			}
			return $authURL;
		}

		/**
		 * Function to get Access Token
		 *
		 * @name _getToken
		 * @return string accessToken
		 */
		public function _getToken( $siteID ) {
			$accessToken = false;
			$sessionID   = get_option( 'ced_umb_ebay_ebay_details' );
			$sessionID   = isset( $sessionID ) ? $sessionID['sessionID'] : false;

			if ( null != $sessionID && '' != $sessionID ) {
				$token = $this->ebayAuthInstance->getToken( $siteID, $sessionID );
				// var_dump($token);
				if ( isset( $token['eBayAuthToken'] ) ) {
					return array(
						'status'       => '200',
						'token'        => $token['eBayAuthToken'],
						'tokenDetails' => $token,
					);
				}
			}
			return $accessToken;
		}

		/**
		 * Function to get session id
		 *
		 * @name getSessionid
		 */
		public function loadDepenedency() {
			if ( is_file( __DIR__ . '/ebayAuthorization.php' ) ) {
				require_once 'ebayAuthorization.php';
				$this->ebayAuthInstance = Ebayauthorization::get_instance();
			}
		}
	}
}
