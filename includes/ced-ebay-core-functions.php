<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

function ced_ebay_countries() {
	$countries = array(
		'MY' => 'Malaysia',
		'TH' => 'Thailand',
		'VN' => 'Vietnam',
		'PH' => 'Philippines',
		'SG' => 'Singapore',
		'ID' => 'Indonesia',
		'TW' => 'Taiwan',
	);
	return $countries;
}



function ced_ebay_get_shop_data( $user_id = '' ) {
	if ( ! empty( get_option( 'ced_ebay_user_access_token' ) ) ) {
		require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayAuthorization.php';
		$shop_data = get_option( 'ced_ebay_user_access_token' );
		if ( ! empty( $shop_data ) ) {
			return $shop_data[ $user_id ];
		}
	}
}

function ced_ebay_pre_flight_check( $user_id ) {
	if ( ! empty( get_option( 'ced_ebay_user_access_token' ) ) ) {
		require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayAuthorization.php';
		$shop_data = get_option( 'ced_ebay_user_access_token' );
		if ( ! empty( $shop_data ) ) {
			$token   = isset( $shop_data[ $user_id ]['access_token'] ) ? $shop_data[ $user_id ]['access_token'] : '';
			$site_id = isset( $shop_data[ $user_id ]['site_id'] ) ? $shop_data[ $user_id ]['site_id'] : '';
			if ( ! empty( $token ) && '' != $site_id ) {
				require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
				$ebayUploadInstance     = EbayUpload::get_instance( $site_id, $token );
				$check_token_status_xml = '<?xml version="1.0" encoding="utf-8"?>
				<GeteBayOfficialTimeRequest xmlns="urn:ebay:apis:eBLBaseComponents">
				<RequesterCredentials>
				<eBayAuthToken>' . $token . '</eBayAuthToken>
				</RequesterCredentials>
				</GeteBayOfficialTimeRequest>';
				$get_ebay_time          = $ebayUploadInstance->get_ebay_time( $check_token_status_xml );
				if ( isset( $get_ebay_time['Ack'] ) && 'Success' == $get_ebay_time['Ack'] ) {
					return true;
				} else {
					return false;
				}
			}
		}
	}
}



function eBay_Domain() {
	$domains = array(
		'MY' => 'https://ebay.com.my/',
		'TH' => 'https://ebay.co.th/',
		'VN' => 'https://ebay.vn/',
		'PH' => 'https://ebay.ph/',
		'SG' => 'https://ebay.sg/',
		'ID' => 'https://ebay.co.id/',
		'TW' => 'https://ebay.tw/',
	);
	return $domains;
}

function ced_ebay_log_data( $message, $log_name, $log_file = '' ) {
	$log = new WC_Logger();
	if ( is_array( $message ) ) {
		$message = print_r( $message, true );
	} elseif ( is_object( $message ) ) {
		$ob_get_length = ob_get_length();
		if ( ! $ob_get_length ) {
			if ( false === $ob_get_length ) {
				ob_start();
			}
			var_dump( $message );
			$message = ob_get_contents();
			if ( false === $ob_get_length ) {
				ob_end_clean();
			} else {
				ob_clean();
			}
		} else {
			$message = '(' . get_class( $message ) . ' Object)';
		}
	}
	$log->add( $log_name, $message );
	if ( ! empty( $log_file ) ) {
		if ( file_exists( $log_file ) ) {
			file_put_contents( $log_file, PHP_EOL . $message, FILE_APPEND );
		} else {
			return;
		}
	}
}

function ced_ebay_time_elapsed_string( $datetime, $full = false ) {
	$now  = new DateTime();
	$ago  = new DateTime( $datetime );
	$diff = $now->diff( $ago );

	$diff->w  = floor( $diff->d / 7 );
	$diff->d -= $diff->w * 7;

	$string = array(
		'y' => 'year',
		'm' => 'month',
		'w' => 'week',
		'd' => 'day',
		'h' => 'hour',
		'i' => 'minute',
		's' => 'second',
	);
	foreach ( $string as $k => &$v ) {
		if ( $diff->$k ) {
			$v = $diff->$k . ' ' . $v . ( $diff->$k > 1 ? 's' : '' );
		} else {
			unset( $string[ $k ] );
		}
	}

	if ( ! $full ) {
		$string = array_slice( $string, 0, 1 );
	}
	return $string ? implode( ', ', $string ) . ' ago' : 'just now';
}

