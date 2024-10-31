<?php
if ( ! class_exists( 'Ebayconfig' ) ) {
	class Ebayconfig {


		public $devID;
		public $appID;
		public $certID;
		public $serverUrl;
		public $shoppingURL;
		public $findingURL;
		public $loginURL;
		public $feedbackURL;
		public $runame;
		public $marketingURL;
		public $fulfillmentURL;
		public $oauthLoginUrl;
		public $oAuthScope;
		public $oauthCodeGrantUrl;
		public $accountURL;
		public $taxonomyURL;
		public $postOrderURL;

		public static $_instance;

		/**
		 * CED_UMB_EBAY_ebayConfig Instance.
		 *
		 * Ensures only one instance of Ebay_config is loaded or can be loaded.
		 *
		userId
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_UMB_EBAY_ebay_Manager instance.
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
			$production = true;
			$mode       = get_option( 'ced_ebay_mode_of_operation', '' );

			$saved_ebay_details = get_option( 'ced_umb_ebay_ebay_merchant_application_keys', array() );

			if ( 'production' == $mode || '' == $mode ) {

					$this->devID  = 'bb6a459c-124f-41db-83b1-33c78f5ee4f9';   // insert your devID for sandbox
					$this->appID  = 'Cedcomme-CEDCOMME-PRD-e16e08212-ecdc3f0d';   // different from prod keys
					$this->certID = 'PRD-16e0821229a1-37d7-4a8a-807c-0b07';   // need three keys and one token
					$this->runame = 'Cedcommerce_Dev-Cedcomme-CEDCOM-quedmw';

				// set the Server to use (Sandbox or Production)
				$this->serverUrl         = 'https://api.ebay.com/ws/api.dll';
				$this->shoppingURL       = 'http://open.api.ebay.com/shopping';
				$this->findingURL        = 'http://svcs.ebay.com/services/search/FindingService/v1';
				$this->loginURL          = 'https://signin.ebay.com/ws/eBayISAPI.dll'; // This is the URL to start the Auth & Auth process
				$this->feedbackURL       = 'http://feedback.ebay.com/ws/eBayISAPI.dll'; // This is used to for link to feedback
				$this->marketingURL      = 'https://api.ebay.com/sell/marketing/v1';
				$this->fulfillmentURL    = 'https://api.ebay.com/sell/fulfillment/v1/order';
				$this->accountURL        = 'https://api.ebay.com/sell/account/v1/';
				$this->taxonomyURL       = 'https://api.ebay.com/commerce/taxonomy/v1/';
				$this->postOrderURL      = 'https://api.ebay.com/post-order/v2/';
				$this->oauthLoginUrl     = 'https://auth.ebay.com/oauth2/authorize';
				$this->oauthCodeGrantUrl = 'https://api.ebay.com/identity/v1/oauth2/token';
				$this->oAuthScope        = 'https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.marketing.readonly%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.marketing%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.inventory.readonly%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.inventory%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.account.readonly%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.account%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.fulfillment.readonly%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.fulfillment%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.analytics.readonly%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.finances%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.payment.dispute%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fcommerce.identity.readonly';
				$this->compatLevel       = 1267;
			} elseif ( 'sandbox' == $mode ) {

					$this->devID  = 'bb6a459c-124f-41db-83b1-33c78f5ee4f9';   // insert your devID for sandbox
					$this->appID  = 'Cedcomme-CEDCOMME-SBX-e16e2f5cf-07e8ce80';   // different from prod keys
					$this->certID = 'SBX-16e2f5cfcd96-1842-4fc2-99f8-dcf1';   // need three keys and one token
					$this->runame = 'Cedcommerce_Dev-Cedcomme-CEDCOM-qjozhoj';  // sandbox runame

				$this->serverUrl         = 'https://api.sandbox.ebay.com/ws/api.dll';
				$this->shoppingURL       = 'http://open.api.sandbox.ebay.com/shopping';
				$this->findingURL        = 'http://svcs.sandbox.ebay.com/services/search/FindingService/v1';
				$this->loginURL          = 'https://signin.sandbox.ebay.com/ws/eBayISAPI.dll'; // This is the URL to start the Auth & Auth process
				$this->feedbackURL       = 'http://feedback.sandbox.ebay.com/ws/eBayISAPI.dll'; // This is used to for link to feedback
				$this->marketingURL      = 'https://api.sandbox.ebay.com/sell/marketing/v1/';
				$this->fulfillmentURL    = 'https://api.sandbox.ebay.com/sell/fulfillment/v1/order/';
				$this->accountURL        = 'https://api.sandbox.ebay.com/sell/account/v1/fulfillment_policy/';
				$this->taxonomyURL       = 'https://api.sandbox.ebay.com/commerce/taxonomy/v1/';
				$this->oauthLoginUrl     = 'https://auth.sandbox.ebay.com/oauth2/authorize';
				$this->oauthCodeGrantUrl = 'https://api.sandbox.ebay.com/identity/v1/oauth2/token';
				$this->oAuthScope        = 'https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.marketing.readonly%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.marketing%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.inventory.readonly%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.inventory%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.account.readonly%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.account%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.fulfillment.readonly%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.fulfillment%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.analytics.readonly%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.finances%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fsell.payment.dispute%20https%3A%2F%2Fapi.ebay.com%2Foauth%2Fapi_scope%2Fcommerce.identity.readonly';
				$this->compatLevel       = 1267;
			}
		}

		/**
		 * Function to get All available sites for ebay
		 *
		 * @name getEbaysites
		 */
		public function getEbaysites() {
			$ebaySites = array(
				array(
					'siteID'       => '15',
					'name'         => 'Australia',
					'countrycode'  => 'AU',
					'abbreviation' => 'AU',
					'tld'          => '.com.au',
					'currency'     => array( 'AUD' ),
				),
				array(
					'siteID'       => '2',
					'name'         => 'Canada',
					'countrycode'  => 'CA',
					'abbreviation' => 'CA',
					'tld'          => '.ca',
					'currency'     => array( 'CAD', 'USD' ),
				),
				array(
					'siteID'       => '210',
					'name'         => 'Canada (French)',
					'countrycode'  => 'CA',
					'abbreviation' => 'CAFR',
					'tld'          => '.ca',
					'currency'     => array( 'CAD', 'USD' ),
				),
				array(
					'siteID'       => '201',
					'name'         => 'HongKong',
					'countrycode'  => 'HK',
					'abbreviation' => 'HK',
					'tld'          => '.com.hk',
					'currency'     => array( 'HKD' ),
				),
				array(
					'siteID'       => '207',
					'name'         => 'Malaysia',
					'countrycode'  => 'MY',
					'abbreviation' => 'MY',
					'tld'          => '.com.my',
					'currency'     => array( 'MYR' ),
				),
				array(
					'siteID'       => '211',
					'name'         => 'Philippines',
					'countrycode'  => 'PH',
					'abbreviation' => 'PH',
					'tld'          => '.ph',
					'currency'     => array( 'PHP' ),
				),
				array(
					'siteID'       => '216',
					'name'         => 'Singapore',
					'countrycode'  => 'SG',
					'abbreviation' => 'SG',
					'tld'          => '.com.sg',
					'currency'     => array( 'SGD' ),
				),
				array(
					'siteID'       => '186',
					'name'         => 'Spain',
					'countrycode'  => 'ES',
					'abbreviation' => 'ES',
					'tld'          => '.es',
					'currency'     => array( 'EUR' ),
				),
				array(
					'siteID'       => '3',
					'name'         => 'UK',
					'countrycode'  => 'GB',
					'abbreviation' => 'UK',
					'tld'          => '.co.uk',
					'currency'     => array( 'GBP' ),
				),
				array(
					'siteID'       => '0',
					'name'         => 'US',
					'countrycode'  => 'US',
					'abbreviation' => 'US',
					'tld'          => '.com',
					'currency'     => array( 'USD' ),
				),
				array(
					'siteID'       => '100',
					'name'         => 'eBay Motors',
					'countrycode'  => 'US',
					'abbreviation' => 'US',
					'tld'          => '.com',
					'currency'     => array( 'USD' ),
				),
				array(
					'siteID'       => '71',
					'name'         => 'France',
					'abbreviation' => 'FR',
					'tld'          => '.fr',
					'currency'     => array( 'EUR' ),
					'countrycode'  => 'FR',
				),
				array(
					'siteID'       => '77',
					'name'         => 'Germany',
					'countrycode'  => 'DE',
					'abbreviation' => 'DE',
					'tld'          => '.de',
					'currency'     => array( 'EUR' ),
				),
				array(
					'siteID'       => '101',
					'name'         => 'Italy',
					'countrycode'  => 'IT',
					'abbreviation' => 'IT',
					'tld'          => '.it',
					'currency'     => array( 'EUR' ),
				),
				array(
					'siteID'       => '146',
					'name'         => 'Netherlands',
					'countrycode'  => 'NL',
					'abbreviation' => 'NL',
					'tld'          => '.nl',
					'currency'     => array( 'EUR' ),
				),
				array(
					'siteID'       => '218',
					'name'         => 'Sweden',
					'countrycode'  => 'SE',
					'abbreviation' => 'SE',
					'tld'          => '.se',
					'currency'     => array( 'SEK' ),
				),
				array(
					'siteID'       => '205',
					'name'         => 'Ireland',
					'countrycode'  => 'IE',
					'abbreviation' => 'IE',
					'tld'          => '.ie',
					'currency'     => array( 'EUR' ),
				),
				array(
					'siteID'       => '16',
					'name'         => 'Austria',
					'countrycode'  => 'AT',
					'abbreviation' => 'AT',
					'tld'          => '.at',
					'currency'     => array( 'EUR' ),
				),
				array(
					'siteID'       => '193',
					'name'         => 'Switzerland',
					'countrycode'  => 'CH',
					'abbreviation' => 'CH',
					'tld'          => '.ch',
					'currency'     => array( 'CHF' ),
				),
			);
			return $ebaySites;
		}
		/**
		 * Function to get All available sites for ebay
		 *
		 * @name getEbaysites
		 */
		public function getEbaycountrDetail( $siteID ) {
			$sites = $this->getEbaysites();
			if ( is_array( $sites ) && ! empty( $sites ) ) {
				foreach ( $sites as $site ) {
					if ( $site['siteID'] == $siteID ) {
						return $site;
					}
				}
			}
		}
	}
}
