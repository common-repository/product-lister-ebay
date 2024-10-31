<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( isset( $_POST['ced_ebay_key_setting'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ced_ebay_key_setting'] ), 'ced_ebay_key_setting_page_nonce' ) ) {
	if ( isset( $_POST['saveApplicationDetails'] ) ) {

		$saved_ebay_details                   = get_option( 'ced_umb_ebay_ebay_merchant_details', array() );
		$sanitized_array                      = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$ced_ebay_application_id              = isset( $sanitized_array['ced_ebay_application_id'] ) ? trim( $sanitized_array['ced_ebay_application_id'] ) : '';
		$ced_ebay_developer_id                = isset( $sanitized_array['ced_ebay_developer_id'] ) ? trim( $sanitized_array['ced_ebay_developer_id'] ) : '';
		$ced_ebay_certification_id            = isset( $sanitized_array['ced_ebay_certification_id'] ) ? trim( $sanitized_array['ced_ebay_certification_id'] ) : '';
		$ced_ebay_run_name                    = isset( $sanitized_array['ced_ebay_run_name'] ) ? trim( $sanitized_array['ced_ebay_run_name'] ) : '';
		$ced_umb_ebay_application_keys_to_use = isset( $sanitized_array['ced_umb_ebay_application_keys_to_use'] ) ? ( $sanitized_array['ced_umb_ebay_application_keys_to_use'] ) : 'cedcommerce';
		$ced_ebay_mode_of_operation           = isset( $sanitized_array['ced_ebay_mode_of_operation'] ) ? trim( $sanitized_array['ced_ebay_mode_of_operation'] ) : 'production';
		update_option( 'ced_ebay_mode_of_operation', $ced_ebay_mode_of_operation );

		if ( 'cedcommerce' == $ced_umb_ebay_application_keys_to_use ) {
			$saved_ebay_details['application_details']['ced_umb_ebay_application_keys_to_use'] = $ced_umb_ebay_application_keys_to_use;
		} else {

			$saved_ebay_details['application_details']['ced_umb_ebay_application_keys_to_use'] = $ced_umb_ebay_application_keys_to_use;
			$saved_ebay_details['application_details']['ced_ebay_application_id']              = $ced_ebay_application_id;
			$saved_ebay_details['application_details']['ced_ebay_developer_id']                = $ced_ebay_developer_id;
			$saved_ebay_details['application_details']['ced_ebay_certification_id']            = $ced_ebay_certification_id;
			$saved_ebay_details['application_details']['ced_ebay_run_name']                    = $ced_ebay_run_name;

		}

		$ss = update_option( 'ced_umb_ebay_ebay_merchant_details', $saved_ebay_details );
		$so = update_option( 'ced_umb_ebay_ebay_merchant_application_keys', $saved_ebay_details );

	}
}

$saved_ebay_details        = get_option( 'ced_umb_ebay_ebay_merchant_application_keys', array() );
$saved_ebay_detailsss      = get_option( 'ced_umb_ebay_ebay_merchant_details', array() );
$saved_application_details = isset( $saved_ebay_details['application_details'] ) ? $saved_ebay_details['application_details'] : array();
?>
<div class="ced_umb_ebay_bottom_margin">
	<h2 class="ced_umb_ebay_setting_header"><?php esc_attr_e( 'Ebay Application Keys', 'ced-umb-ebay' ); ?></h2>
	<span class="ced_umb_ebay_white_txt"><?php esc_attr_e( 'This section is for saving the Ebay developer application keys for using the eBay trading API.', 'ced-umb-ebay' ); ?></span>
</div>
<div class="ced_umb_ebay_return_address">
	<form method="post">
		<table class="ced_umb_ebay_return_address wp-list-table widefat fixed striped activityfeeds"  id="umb_ebay_choose_keys_to_use_table">
			<tbody>
				<tr>
					<th>
						<?php esc_attr_e( 'Choose Ebay Application Keys to use.', 'ced-umb-ebay' ); ?>
					</th>
					<td>
						<?php
						$own         = '';
						$cedcommerce = '';
						if ( isset( $saved_application_details['ced_umb_ebay_application_keys_to_use'] ) ) {
							if ( 'own' == $saved_application_details['ced_umb_ebay_application_keys_to_use'] ) {
								$own = 'checked';
							} elseif ( 'cedcommerce' == $saved_application_details['ced_umb_ebay_application_keys_to_use'] ) {
								$cedcommerce = 'checked';
							}
						}

						?>
						<span>
							<input type="radio" id="ced_ebay_use_own_keys" class="ced_test" name="ced_umb_ebay_application_keys_to_use" value="own" <?php echo esc_attr_e( $own ); ?>><?php esc_attr_e( 'Use your own application keys', 'ced-umb-ebay' ); ?>( <em><?php esc_attr_e( 'Recomended', 'ced-umb-ebay' ); ?></em> )</input>
						</span>
						<span>
							<input type="radio" name="ced_umb_ebay_application_keys_to_use" class="ced_test"  <?php echo esc_attr_e( $cedcommerce ); ?> value="cedcommerce"><?php esc_attr_e( 'Use Cedcommerce application keys', 'ced-umb-ebay' ); ?></input>
						</span>
					</td>
				</tr>
				<tr>
					<th><?php esc_attr_e( 'Select Mode of operation', 'ced-umb-ebay' ); ?></th>
					<td>
						<td>
							<?php
							$production        = '';
							$sandbox           = '';
							$mode_of_operation = get_option( 'ced_ebay_mode_of_operation', '' );
							if ( 'production' == $mode_of_operation ) {
								$production = 'selected';
							} elseif ( 'sandbox' == $mode_of_operation ) {
								$sandbox = 'selected';
							}
							?>
							<select name="ced_ebay_mode_of_operation">
								<option value=""><?php esc_attr_e( '--Select--', 'ced-umb-ebay' ); ?></option>
								<option value="sandbox" <?php echo esc_attr_e( $sandbox ); ?>><?php esc_attr_e( 'Sandbox Testing Mode', 'ced-umb-ebay' ); ?></option>
								<option value="production" <?php echo esc_attr_e( $production ); ?>><?php esc_attr_e( 'Production Mode', 'ced-umb-ebay' ); ?></option>
							</select>
						</td>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="ced_umb_ebay_return_address wp-list-table widefat fixed striped activityfeeds"  id="umb_ebay_application_key_details" style="
		<?php
		if ( '' == $own ) {
			echo 'display: none;';}
		?>
			">
			<thead>
				<tr><td colspan="2"><p><b>Follow these steps to get your Application Keys - </b></p><p><b>Step-1</b> First you need to create a developer account on eBay. </p><p><b>Step-2</b>Create an application and generate a production keyset.</p><p><b>Step-2</b>To get the run name , create a user token.</p></td></tr>
			</thead>
			<tbody>
				<tr>
					<th><?php esc_attr_e( 'Application ID', 'ced-umb-ebay' ); ?></th>
					<td>
						<?php
						$app_Id = isset( $saved_application_details['ced_ebay_application_id'] ) ? esc_attr( $saved_application_details['ced_ebay_application_id'] ) : '';
						print_r( '<input type="text" placeholder="Enter Application Id" name="ced_ebay_application_id" value=" ' . $app_Id . ' "></input>' );
						?>
					</td>
				</tr>
				<tr>
					<th><?php esc_attr_e( 'Developer ID', 'ced-umb-ebay' ); ?></th>
					<td>
						<?php
						$dev_Id = isset( $saved_application_details['ced_ebay_developer_id'] ) ? esc_attr( $saved_application_details['ced_ebay_developer_id'] ) : '';
						print_r( '<input type="text" placeholder="Enter Developer Id" name="ced_ebay_developer_id" value=" ' . $dev_Id . ' "></input>' );
						?>
					</td>
				</tr>
				<tr>
					<th><?php esc_attr_e( 'Certification ID', 'ced-umb-ebay' ); ?></th>
					<td>
						<?php
						$cert_Id = isset( $saved_application_details['ced_ebay_certification_id'] ) ? esc_attr( $saved_application_details['ced_ebay_certification_id'] ) : '';
						print_r( '<input type="text" placeholder="Enter Certification Id" name="ced_ebay_certification_id" value=" ' . $cert_Id . ' "></input>' );
						?>
					</td>
				</tr>
				<tr>
					<th><?php esc_attr_e( 'Run Name', 'ced-umb-ebay' ); ?></th>
					<td>
						<?php
						$runName = isset( $saved_application_details['ced_ebay_run_name'] ) ? esc_attr( $saved_application_details['ced_ebay_run_name'] ) : '';
						print_r( '<input type="text" placeholder="Enter Run Name" name="ced_ebay_run_name" value=" ' . $runName . ' "></input>' );
						?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php wp_nonce_field( 'ced_ebay_key_setting_page_nonce', 'ced_ebay_key_setting' ); ?>
		<p class="ced_umb_ebay_button_right">
			<input class="button button-ced_umb_ebay" value="<?php esc_attr_e( 'Save Application Details', 'ced-umb-ebay' ); ?>" name="saveApplicationDetails" type="submit">
		</p>
	</form>
</div>
