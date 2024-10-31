<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$account_data = isset( $shop_data['account_data'] ) ? $shop_data['account_data'] : '';
$account_data = json_decode( $account_data, true );
$siteID       = isset( $account_data['siteID'] ) ? $account_data['siteID'] : '';
$token        = isset( $account_data['token']['eBayAuthToken'] ) ? $account_data['token']['eBayAuthToken'] : '';
$shop_name    = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
$shop_name = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
$shop_data = ced_ebay_get_shop_data( $shop_name );
if ( ! empty( $shop_data ) ) {
	$siteID      = $shop_data['site_id'];
	$token       = $shop_data['access_token'];
	$getLocation = $shop_data['location'];
}
if ( isset( $_REQUEST['ced_ebay_return_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_REQUEST['ced_ebay_return_nonce'] ), 'ced_ebay_return_page_nonce' ) ) {
	if ( isset( $_POST['ced_ebay_site_settings_save_return_button'] ) ) {
		$sanitized_array                       = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$ced_ebay_site_selected_return_options = isset( $sanitized_array['ced_ebay_site_return_settings'] ) ? ( $sanitized_array['ced_ebay_site_return_settings'] ) : array();
		update_option( 'ced_umb_ebay_site_selected_return_methods_' . $shop_name, $ced_ebay_site_selected_return_options );

	}
}
$return_methods = array();
$return_methods = get_option( 'ced_umb_ebay_site_return_methods_' . $shop_name, array() );

$selected_return_methods = array();
$selected_return_methods = get_option( 'ced_umb_ebay_site_selected_return_methods_' . $shop_name, array() );

if ( empty( $return_methods ) ) {
	if ( ! empty( $token ) ) {
		require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';
		$ebayUploadInstance           = EbayUpload::get_instance( $siteID, $token );
		$mainXml                      = '<?xml version="1.0" encoding="utf-8"?>
					<GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
					  <RequesterCredentials>
					    <eBayAuthToken>' . $token . '</eBayAuthToken>
					  </RequesterCredentials>
					  <DetailName>ReturnPolicyDetails</DetailName>
					</GeteBayDetailsRequest>';
		$site_specific_return_methods = $ebayUploadInstance->get_sitespecific_return_methods( $mainXml );

		if ( is_array( $site_specific_return_methods ) && ! empty( $site_specific_return_methods ) && 'Success' == $site_specific_return_methods['Ack'] ) {
			if ( isset( $site_specific_return_methods['ReturnPolicyDetails'] ) ) {
				$return_methods = $site_specific_return_methods['ReturnPolicyDetails'];
				update_option( 'ced_umb_ebay_site_return_methods_' . $shop_name, $return_methods );
			}
		}
	}
}
?>

<div class="ced_ebay_site_payment_option_wrapper">
	<?php
	if ( ! empty( $return_methods ) ) {
		?>
		<table class="ced_ebay_site_return_options">
			<form action="" method="post">
				<?php
				foreach ( $return_methods as $key => $value ) {
					if ( 'DetailVersion' != $key && 'UpdateTime' != $key ) {
						?>
						<tr class="ced_ebay_site_return_option">
							<th><?php echo esc_attr( $key ); ?></th>
							<?php
							if ( ! empty( $value ) && is_array( $value ) ) {
								?>
								<td><select name="ced_ebay_site_return_settings[<?php echo esc_attr( $key ); ?>]">
								<option value="">--Select--</option>
								<?php
								foreach ( $value as $key1 => $value1 ) {
									$selected                        = '';
									$selected_return_methods[ $key ] = isset( $selected_return_methods[ $key ] ) ? $selected_return_methods[ $key ] : '';
									if ( $value1[ $key . 'Option' ] == $selected_return_methods[ $key ] ) {
										$selected = 'selected';
									}
									?>
										<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $value1[ $key . 'Option' ] ); ?>"><?php echo esc_attr( $value1['Description'] ); ?></option>
									<?php
								}
								?>
								</select></td>
								<?php
							} elseif ( ! empty( $value ) && ! is_array( $value ) ) {
								?>
								<td><input type="text" value="<?php echo isset( $selected_return_methods[ $key ] ) ? esc_attr( $selected_return_methods[ $key ] ) : ''; ?>" name="ced_ebay_site_return_settings[<?php echo esc_attr( $key ); ?>]"></td>
								<?php
							}
							?>
						</tr>
						<?php
					}
					?>
					<?php
				}
				?>
			<?php wp_nonce_field( 'ced_ebay_return_page_nonce', 'ced_ebay_return_nonce' ); ?>
			</table>
				<p class="ced_ebay_site_settings_save_button">
					<input type="submit" name="ced_ebay_site_settings_save_return_button" class="ced_ebay_button button button-primary" id="ced_ebay_site_settings_save_return_button" value="Save Details">
				</p>
			</form>

		<?php
	}
	?>
</div>
