<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
// var_dump($shop_data);
$shop_data = isset( $shop_data['account_data'] ) ? json_decode( $shop_data['account_data'], true ) : array();
$siteID    = isset( $shop_data['siteID'] ) ? $shop_data['siteID'] : '';
$token     = isset( $shop_data['token']['eBayAuthToken'] ) ? $shop_data['token']['eBayAuthToken'] : '';
$shop_name = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
$shop_name = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
$shop_data = ced_ebay_get_shop_data( $shop_name );
if ( ! empty( $shop_data ) ) {
	$siteID      = $shop_data['site_id'];
	$token       = $shop_data['access_token'];
	$getLocation = $shop_data['location'];
}
if ( isset( $_POST['ced_ebay_payment_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ced_ebay_payment_nonce'] ), 'ced_ebay_payment_page_nonce' ) ) {
	if ( isset( $_POST['ced_ebay_site_settings_save_payment_button'] ) ) {
		$sanitized_array                        = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$ced_ebay_site_selected_payment_options = isset( $sanitized_array['ced_ebay_site_selected_payment_options'] ) ? ( $sanitized_array['ced_ebay_site_selected_payment_options'] ) : array();
		$email                                  = isset( $sanitized_array['ced_ebay_site_email_address'] ) ? ( $sanitized_array['ced_ebay_site_email_address'] ) : '';

	}
}

$payment_methods = array();
$payment_methods = get_option( 'ced_umb_ebay_site_payment_methods_' . $shop_name, array() );

$selected_payment_methods = array();
$selected_payment_methods = get_option( 'ced_umb_ebay_site_selected_payment_methods_' . $shop_name, array() );
if ( ! is_array( $selected_payment_methods ) ) {
	$selected_payment_methods = array();
}
$email = get_option( 'ced_ebay_site_email_address_' . $shop_name, '' );

if ( empty( $payment_methods ) ) {
	if ( ! empty( $token ) ) {
		require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
		$ebayUploadInstance            = EbayUpload::get_instance( $siteID, $token );
		$mainXml                       = '<?xml version="1.0" encoding="utf-8"?>
						<GetCategoryFeaturesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
						    <eBayAuthToken>' . $token . '</eBayAuthToken>
						  </RequesterCredentials>
						  <DetailLevel>ReturnAll</DetailLevel>
						  <AllFeaturesForCategory>true</AllFeaturesForCategory>
						</GetCategoryFeaturesRequest>';
		$site_specific_payment_methods = $ebayUploadInstance->get_sitespecific_payment_methods( $mainXml );
		if ( is_array( $site_specific_payment_methods ) && ! empty( $site_specific_payment_methods ) ) {
			if ( isset( $site_specific_payment_methods['SiteDefaults'] ) ) {
				$payment_methods = $site_specific_payment_methods['SiteDefaults']['PaymentMethod'];
				update_option( 'ced_umb_ebay_site_payment_methods_' . $shop_name, $payment_methods );
			}
		}
	}
}
?>

<div class="ced_ebay_site_payment_option_wrapper">
	<?php
	if ( ! empty( $payment_methods ) ) {
		require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/eBayPaymentConfig.php';
		$eBayConfigInstance    = Ced_EBay_Config::get_instance();
		$allEbayPaymentMethods = $eBayConfigInstance->getEbayPaymentMethods();

		?>
		<div class="ced_ebay_site_payment_options">
			<form action="" method="post">
				<?php
				foreach ( $payment_methods as $key => $value ) {
					$selected = '';
					if ( in_array( $value, $selected_payment_methods ) ) {
						$selected = 'checked';
					}
					?>
					<div class="ced_ebay_site_payment_option">
					<div class="checkbox">
		<input id="<?php echo esc_attr( $value ); ?>" type="checkbox" name="ced_ebay_site_selected_payment_options[]"  value="<?php echo esc_attr( $value ); ?>" <?php echo esc_attr( $selected ); ?> class="checkbox__input">
		<label for="<?php echo esc_attr( $value ); ?>" class="checkbox__label"><?php echo esc_attr( $value ); ?></label>
	</div>

					</div>
					<?php
				}
				?>
				<label>Paypal Email Address</label><input type="text" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" name="ced_ebay_site_email_address" class="ced_email_address_field" value="<?php echo esc_attr( $email ); ?>">
				<?php wp_nonce_field( 'ced_ebay_payment_page_nonce', 'ced_ebay_payment_nonce' ); ?>
				<footer>
				<input type="submit" name="ced_ebay_site_settings_save_payment_button" class="ced_ebay_button button button-primary" id="ced_ebay_site_settings_save_payment_button" value="Save Details">

				</footer>
			</form>
		</div>
		<?php
	}
	?>
</div>
