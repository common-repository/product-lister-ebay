<?php

if ( ! class_exists( 'Ced_Marketing_API_Request' ) ) {
	class Ced_Marketing_API_Request {


		public $ebayConfigInstance;
		public $verb;

		public function __construct( $siteToUseID ) {
			$this->loadDependency();
			$this->siteID      = $siteToUseID;
			$this->taxonomyURL = $this->ebayConfigInstance->taxonomyURL;
			$this->ebaySites   = $this->ebayConfigInstance->getEbaysites();

		}



		/** SendHttpRequest
		Sends a HTTP request to the server for this session
		Input:  $requestBody
		Output: The HTTP Response as a String
		 */

		public function sendHttpRequestForTaxonomyAPI( $endpoint, $token, $curlRequestType = '' ) {
			// build eBay headers using variables passed via constructor
			$headers    = $this->buildMarketingHeaders( $token );
			$connection = curl_init();
			// set the server we are using (could be Sandbox or Production server)
			curl_setopt( $connection, CURLOPT_URL, $this->taxonomyURL . '/' . $endpoint );

			// stop CURL from verifying the peer's certificate
			curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, 0 );
			curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, 0 );

			if ( 'POST_GET_HEADER_STATUS' == $curlRequestType ) {
				curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );
				curl_setopt( $connection, CURLOPT_VERBOSE, 1 );
				curl_setopt( $connection, CURLOPT_HEADER, 1 );
				curl_setopt( $connection, CURLOPT_POST, 1 );
				curl_setopt( $connection, CURLOPT_POSTFIELDS, '' );
				$response    = curl_exec( $connection );
				$header_size = curl_getinfo( $connection, CURLINFO_HEADER_SIZE );
				$header      = substr( $response, 0, $header_size );
				$body        = substr( $response, $header_size );
				$httpcode    = curl_getinfo( $connection, CURLINFO_HTTP_CODE );
				curl_close( $connection );
				return $httpcode;
			} else {
				curl_setopt( $connection, CURLOPT_HTTPHEADER, $headers );

				// set method as GET
				curl_setopt( $connection, CURLOPT_HTTPGET, 1 );

				// set it to return the transfer as a string from curl_exec
				curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );

				// Send the Request
				$response = curl_exec( $connection );

				curl_close( $connection );

				return $response;
			}

			// set the headers using the array of headers
		}




		public function buildMarketingHeaders( $token ) {
			$headers = array(
				'Authorization:Bearer ' . $token,
				'Accept:application/json',
				'Content-Type:application/json',
			);

			return $headers;
		}

		public function loadDependency() {
			if ( is_file( __DIR__ . '/ebayConfig.php' ) ) {
				require_once 'ebayConfig.php';
				$this->ebayConfigInstance = Ebayconfig::get_instance();
			}

		}

		public function ced_ebay_headersToArray( $str ) {
			$headers         = array();
			$headersTmpArray = explode( "\r\n", $str );
			for ( $i = 0; $i < count( $headersTmpArray ); ++$i ) {
				// we dont care about the two \r\n lines at the end of the headers
				if ( strlen( $headersTmpArray[ $i ] ) > 0 ) {
					// the headers start with HTTP status codes, which do not contain a colon so we can filter them out too
					if ( strpos( $headersTmpArray[ $i ], ':' ) ) {
						$headerName             = substr( $headersTmpArray[ $i ], 0, strpos( $headersTmpArray[ $i ], ':' ) );
						$headerValue            = substr( $headersTmpArray[ $i ], strpos( $headersTmpArray[ $i ], ':' ) + 1 );
						$headers[ $headerName ] = $headerValue;
					}
				}
			}
			return $headers;
		}

	}
}
