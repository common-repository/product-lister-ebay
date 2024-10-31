<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class Class_Ced_EBay_Send_Http_Request {


	public $userId;
	public $endpointUrl;
	public $partnerId;
	public $secretKey;

	public function __construct() {

		// $this->loadDepenedency();
		// $this->endpointUrl = $this->ced_ebay_configInstance->endpointUrl;
		// $this->partnerId = $this->ced_ebay_configInstance->partnerId;
		// $this->secretKey = $this->ced_ebay_configInstance->secretKey;
	}

	/** SendHttpRequest
	Sends a HTTP request to the server for this session
	Input:  $requestBody
	Output: The HTTP Response as a String
	 */
	public function sendHttpRequest( $action = '', $parameters = '', $store = '' ) {
		$apiUrl = $this->endpointUrl . $action;
		if ( array() != $parameters ) {
			$parameters = $this->addBasicParameter( $parameters, $store );
			$header     = $this->prepareHeader( $apiUrl, $parameters );
		} else {
			$parameters = $this->addBasicParameter( $parameters, $store );
			$header     = $this->prepareHeader( $apiUrl, $parameters );
		}
		if ( ! empty( $store ) ) {
			$parameters['partner_id'] = (int) $this->partnerId;
			$parameters['userid']     = (int) $store;
			$parameters['timestamp']  = time();
			$header                   = $this->prepareHeader( $apiUrl, $parameters );
		}

		$connection = curl_init();
		curl_setopt( $connection, CURLOPT_URL, $apiUrl );
		curl_setopt( $connection, CURLOPT_HTTPHEADER, $header );
		// stop CURL from verifying the peer's certificate
		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, 0 );

		// set method as POST
		curl_setopt( $connection, CURLOPT_POST, 1 );

		curl_setopt( $connection, CURLOPT_POSTFIELDS, json_encode( $parameters ) );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $connection );
		curl_close( $connection );

		$response = $this->ParseResponse( $response );
		return $response;
	}

	public function sendHttpRequesttoimport( $action = '', $offset = '', $itemid = '', $userId ) {
		$parameters = $this->addBasicParameter( array(), $userId );
		if ( '' == $itemid ) {
			if ( '' == $offset ) {
				$parameters['pagination_offset'] = 0;
			} else {
				$parameters['pagination_offset'] = $offset;
			}
			$parameters['pagination_entries_per_page'] = 100;
		} else {
			$parameters['item_id'] = $itemid;
		}
		$apiUrl     = $this->endpointUrl . $action;
		$header     = $this->prepareHeader( $apiUrl, $parameters );
		$connection = curl_init();
		curl_setopt( $connection, CURLOPT_URL, $apiUrl );
		curl_setopt( $connection, CURLOPT_HTTPHEADER, $header );
		// stop CURL from verifying the peer's certificate
		curl_setopt( $connection, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $connection, CURLOPT_SSL_VERIFYHOST, 0 );

		// set method as POST
		curl_setopt( $connection, CURLOPT_POST, 1 );

		curl_setopt( $connection, CURLOPT_POSTFIELDS, json_encode( $parameters ) );
		curl_setopt( $connection, CURLOPT_RETURNTRANSFER, 1 );

		$response = curl_exec( $connection );
		curl_close( $connection );

		$response = $this->ParseResponse( $response );
		return $response;
	}

	public function addBasicParameter( $parameters = array(), $userId = '' ) {
		$parameters['partner_id'] = (int) $this->partnerId;
		$parameters['userid']     = (int) $userId;
		$parameters['timestamp']  = time();
		return $parameters;
	}

	public function preparePostData( $action = '', $orderData ) {

		if ( '' == $action ) {
			return array( 'error' => 'Missing_Action' );
		}

		if ( 'fetchOrder' == $action ) {
			$url      = $this->endpointUrl . $this->merchant_id . '/orders/new/';
			$postData = array( 'url' => $url );
		} elseif ( 'acknowledgeOrder' == $action ) {
			$url         = $this->endpointUrl . $this->merchant_id . '/orders/' . $orderData['siroopOrderId'] . '/received/';
			$requestBody = array( 'merchantOrderRef' => $orderData['merchant_order_reference'] );
			$methodPost  = 1;
			$postData    = array(
				'url'         => $url,
				'methodPost'  => $methodPost,
				'requestBody' => $requestBody,
			);
		} elseif ( 'acceptOrderItem' == $action ) {
			$url         = $this->endpointUrl . $this->merchant_id . '/orders/' . $orderData['siroopOrderId'] . '/items/' . $orderData['itemId'] . '/accept/';
			$methodPost  = 1;
			$requestBody = array();
			$postData    = array(
				'url'         => $url,
				'methodPost'  => $methodPost,
				'requestBody' => $requestBody,
			);
		} elseif ( 'declineOrderItem' == $action ) {
			$url        = $this->endpointUrl . $this->merchant_id . '/orders/' . $orderData['siroopOrderId'] . '/items/' . $orderData['itemId'] . '/decline/';
			$methodPost = 1;
			if ( ! isset( $orderData['cancelReasonText'] ) || '' == $orderData['cancelReasonText'] ) {
				$cancelReasonText = 'The product is not available';
			} else {
				$cancelReasonText = $orderData['cancelReasonText'];
			}

			$requestBody = array(
				'reason'  => $orderData['cancelReason'],
				'message' => $cancelReasonText,
			);
			$postData    = array(
				'url'         => $url,
				'methodPost'  => $methodPost,
				'requestBody' => $requestBody,
			);
		} elseif ( 'cancelOrderItem' == $action ) {

			$url        = $this->endpointUrl . $this->merchant_id . '/orders/' . $orderData['siroopOrderId'] . '/items/' . $orderData['itemId'] . '/cancel/';
			$methodPost = 1;
			if ( ! isset( $orderData['cancelReasonText'] ) || '' == $orderData['cancelReasonText'] ) {
				$cancelReasonText = 'The product is not available';
			} else {
				$cancelReasonText = $orderData['cancelReasonText'];
			}

			$requestBody = array(
				'reason'  => $orderData['cancelReason'],
				'message' => $cancelReasonText,
			);
			$postData    = array(
				'url'         => $url,
				'methodPost'  => $methodPost,
				'requestBody' => $requestBody,
			);
		} elseif ( 'shipOrderItem' == $action ) {
			$url                        = $this->endpointUrl . $this->merchant_id . '/orders/' . $orderData['siroopOrderId'] . '/items/' . $orderData['itemId'] . '/shipped/';
			$methodPost                 = 1;
			$requestBody['trackings'][] = array(
				'carrier' => $orderData['carrier'],
				'code'    => $orderData['trackingCode'],
			);
			$postData                   = array(
				'url'         => $url,
				'methodPost'  => $methodPost,
				'requestBody' => $requestBody,
			);
		} elseif ( 'delivery_note' == $action ) {

			$url         = $this->endpointUrl . $this->merchant_id . '/deliverynote/';
			$methodPost  = 1;
			$requestBody = array( 'orderId' => $orderData['siroopOrderId']/*, 'itemIds' => array_values($orderData['orderItemIds'])*/ );
			$postData    = array(
				'url'         => $url,
				'methodPost'  => $methodPost,
				'requestBody' => $requestBody,
			);
		}

		return $postData;
	}

	public function prepareHeader( $apiUrl = '', $parmaeters = array() ) {
		$authorisation = $apiUrl . '|' . json_encode( $parmaeters );
		$authorisation = rawurlencode( hash_hmac( 'sha256', $authorisation, $this->secretKey, false ) );

		$header = array(
			'Content-Type: application/json',
			'Authorization: ' . $authorisation,
		);
		return $header;
	}

	public function ParseResponse( $response ) {

		if ( ! empty( $response ) ) {
			return json_decode( $response, true );
		}
	}

	/**
	 * Function loadDepenedency
	 *
	 * @name loadDepenedency
	 */
	public function loadDepenedency() {

		if ( is_file( __DIR__ . '/ebayConfig.php' ) ) {
			require_once 'ebayConfig.php';
			$this->ced_ebay_configInstance = Ebayconfig::get_instance();
		}
	}
}
