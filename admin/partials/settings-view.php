<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( empty( get_option( 'ced_ebay_user_access_token' ) ) ) {
	wp_redirect( get_admin_url() . 'admin.php?page=ced_ebay' );
}
$file = CED_EBAY_DIRPATH . 'admin/partials/header.php';
if ( file_exists( $file ) ) {
	require_once $file;
}

if ( isset( $_POST['ced_ebay_setting_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ced_ebay_setting_nonce'] ), 'ced_ebay_setting_page_nonce' ) ) {
	if ( isset( $_POST['global_settings'] ) ) {
		$objDateTime = new DateTime( 'NOW' );
		$timestamp   = $objDateTime->format( 'Y-m-d\TH:i:s\Z' );

		$settings                              = array();
		$sanitized_array                       = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$settings                              = get_option( 'ced_ebay_global_settings', array() );
		$settings[ $user_id ]                  = isset( $sanitized_array['ced_ebay_global_settings'] ) ? ( $sanitized_array['ced_ebay_global_settings'] ) : array();
		 $settings[ $user_id ]['last_updated'] = $timestamp;
		update_option( 'ced_ebay_global_settings', $settings );



		$admin_success_notice = '<div
		class="bg-green-200 px-6 py-4 my-4 rounded-md text-lg flex items-center" style="margin-right:1.2rem;">

	 <span class="text-green-800"> Your configuration has been saved! </span>
   </div>';
		print_r( $admin_success_notice );
	} elseif ( isset( $_POST['reset_global_settings'] ) ) {
		delete_option( 'ced_ebay_global_settings' );
		$admin_success_notice = '<div
		class="bg-green-200 px-6 py-4 my-4 rounded-md text-lg flex items-center" style="margin-right:1.2rem;">

	 <span class="text-green-800"> Your configuration has been Reset! </span>
   </div>';
		print_r( $admin_success_notice );
	}
}
$renderDataOnGlobalSettings = get_option( 'ced_ebay_global_settings', false );
?>
<div class="ced-ebay-v2-header">
	<div class="ced-ebay-v2-logo">
		<img src="<?php echo esc_attr( CED_EBAY_URL ) . 'admin/images/icon-100X100.png'; ?>">
	</div>
	<div class="ced-ebay-v2-header-content">
		<div class="ced-ebay-v2-title">
			<h1>eBay Configuration</h1>
		</div>
		<div class="ced-ebay-v2-actions">

				<div class="admin-custom-action-button-outer">

				<?php if ( ! empty( $renderDataOnGlobalSettings[ $user_id ]['last_updated'] ) ) { ?>
<div class="admin-custom-action-show-button-outer">
		<button style="margin-left:5px;" type="button" class="button btn-normal-sbc">
<span>Last Updated <?php echo esc_html( ced_ebay_time_elapsed_string( $renderDataOnGlobalSettings[ $user_id ]['last_updated'] ) ); ?></span>
</button>
			</div>
			<?php } ?>

<div class="admin-custom-action-show-button-outer">
<button style="background:#5850ec !important;" type="button" class="button btn-normal-tt">
<span><a style="all:unset;" href="https://docs.woocommerce.com/document/ebay-integration-for-woocommerce/#section-6" target="_blank">
Documentation					</a></span>
</button>

</div>

</div>
		</div>


	</div>
</div>

<style>


	.wp-core-ui select {
		vertical-align: baseline;
	}
</style>
<div class="ced-ebay-ui dashboard-wrapper version_control">
	<form id="cmb2-metabox-ced" class="ced-ebay-rollback-form cmb2-form ced-ebay-box" style="margin-right:20px;margin-left:2px;" action="" method="post">

		<header>
			<h3>Listings Configuration</h3>
		</header>

		<p>Increase or decrease the Price of eBay Listings, Adjust Stock Levels, Sync Price from WooCommerce and import eBay Categories.</p>

		<table class="form-table">
			<tbody>
				<tr>
					<?php
					$listing_stock = isset( $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_listing_stock'] ) ? $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_listing_stock'] : '';
					$stock_type    = isset( $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_product_stock_type'] ) ? $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_product_stock_type'] : '';
					?>
					<th scope="row"><label>Stock Levels</label></th>
					<td>
					<select name="ced_ebay_global_settings[ced_ebay_product_stock_type]" data-fieldId="ced_ebay_product_stock_type">
									<option value=""><?php esc_attr_e( 'Select', 'ebay-integration-for-woocommerce' ); ?></option>
									<option <?php echo ( 'MaxStock' == $stock_type ) ? 'selected' : ''; ?> value="MaxStock"><?php esc_attr_e( 'Maximum Stock', 'ebay-integration-for-woocommerce' ); ?></option>
								</select>
						<input type="text" value="<?php echo esc_attr( $listing_stock ); ?>" id="ced_ebay_listing_stock" name="ced_ebay_global_settings[ced_ebay_listing_stock]">
						<p class="description">Set rules for Quantity of items listed on eBay.</p>
					</td>
				</tr>
				<tr>

					<th scope="row"><label>Markup</label></th>
					<td>
						<ul style="margin:0;">
							<li style="display:inline-block;">
								<?php
								$markup_type = isset( $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_product_markup_type'] ) ? $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_product_markup_type'] : '';
								?>
								<select name="ced_ebay_global_settings[ced_ebay_product_markup_type]" data-fieldId="ced_ebay_product_markup">
									<option value=""><?php esc_attr_e( 'Select', 'ebay-integration-for-woocommerce' ); ?></option>
									<option <?php echo ( 'Fixed_Increased' == $markup_type ) ? 'selected' : ''; ?> value="Fixed_Increased"><?php esc_attr_e( 'Fixed Increment', 'ebay-integration-for-woocommerce' ); ?></option>
									<option <?php echo ( 'Fixed_Decreased' == $markup_type ) ? 'selected' : ''; ?> value="Fixed_Decreased"><?php esc_attr_e( 'Fixed Decrement', 'ebay-integration-for-woocommerce' ); ?></option>
									<option <?php echo ( 'Percentage_Increased' == $markup_type ) ? 'selected' : ''; ?> value="Percentage_Increased"><?php esc_attr_e( 'Percentage Increment', 'ebay-integration-for-woocommerce' ); ?></option>
									<option <?php echo ( 'Percentage_Decreased' == $markup_type ) ? 'selected' : ''; ?> value="Percentage_Decreased"><?php esc_attr_e( 'Percentage Decrement', 'ebay-integration-for-woocommerce' ); ?></option>
								</select>

							</li>
							<li style="display:inline-block;">
								<?php
								$markup_price = isset( $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_product_markup'] ) ? $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_product_markup'] : '';
								?>
								<input type="text" value="<?php echo esc_attr( $markup_price ); ?>" id="ced_ebay_product_markup" name="ced_ebay_global_settings[ced_ebay_product_markup]">

							</li>
						</ul>


						<p class="description">Set your preference for Increasing/Decreasing the price of your eBay Listings.</p>
					</td>
				</tr>
				<tr class="cmb-row cmb-type-switch">
					<?php
					$sync_price = isset( $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_sync_price'] ) ? $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_sync_price'] : '';
					?>
					<th scope="row"><label>Sync Price</label></th>
					<td style="padding:0px;">
						<ul class="cmb2-radio-list cmb2-list">
							<li>
								<input type="radio" class="cmb2-option"
								<?php
								if ( 'Off' == $sync_price ) {
									echo 'checked="checked"';}
								?>
								 name="ced_ebay_global_settings[ced_ebay_sync_price]" id="ced_ebay_global_settings[ced_ebay_sync_price]_Off" checked="checked" value="Off">
								<label for="ced_ebay_global_settings[ced_ebay_sync_price]_Off">Off</label>
							</li>
							<li>
								<input type="radio" class="cmb2-option"
								<?php
								if ( 'On' == $sync_price ) {
									echo 'checked="checked"';}
								?>
								 name="ced_ebay_global_settings[ced_ebay_sync_price]" id="ced_ebay_global_settings[ced_ebay_sync_price]_On" value="On">
								<label for="ced_ebay_global_settings[ced_ebay_sync_price]_On">On</label>
							</li>
						</ul>
						<p class="description">Set your preference for WooCommerce Products Price Sync to eBay during Automatic Inventory Update.</p>

					</td>
				</tr>


				<tr>

					<th scope="row"><label>Item Location</label></th>
					<td>
						<ul style="margin:0;">
							<li style="display:inline-block;">
								<?php
								$wc_countries          = new WC_Countries();
								$countries             = $wc_countries->get_countries();
								$item_location_country = isset( $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_item_location_country'] ) ? $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_item_location_country'] : '';
								?>
								<select name="ced_ebay_global_settings[ced_ebay_item_location_country]" data-fieldId="ced_ebay_product_location">
								<option value=""><?php esc_attr_e( 'Select', 'ebay-integration-for-woocommerce' ); ?></option>
								<?php
								foreach ( $countries as $key => $country ) {
									?>
									<option <?php echo ( $key == $item_location_country ) ? 'selected' : ''; ?> value="<?php echo esc_attr( $key ); ?>"><?php esc_attr_e( $country, 'ebay-integration-for-woocommerce' ); ?></option>
									<?php
								}
								?>
								</select>

							</li>
							<li style="display:inline-block;">
								<?php
								$item_location_state = isset( $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_item_location_state'] ) ? $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_item_location_state'] : '';
								?>
								<input type="text" placeholder="Enter City Name" value="<?php echo esc_attr( $item_location_state ); ?>" id="ced_ebay_product_markup" name="ced_ebay_global_settings[ced_ebay_item_location_state]">

							</li>
						</ul>


						<p class="description">If you are shipping the product from outside of your eBay Account Region, please set the item location.</p>
					</td>
				</tr>

				<th scope="row"><label>Description Template</label></th>
					<td>
						<ul style="margin:0;">
							<li style="display:inline-block;">
								<?php
								$upload_dir    = wp_upload_dir();
								$templates_dir = $upload_dir['basedir'] . '/ced-ebay/templates/';
								$templates     = array();
								$files         = glob( $upload_dir['basedir'] . '/ced-ebay/templates/*/template.html' );
								if ( is_array( $files ) ) {
									foreach ( $files as $file ) {
										$file     = basename( dirname( $file ) );
										$fullpath = $templates_dir . $file;

										if ( file_exists( $fullpath . '/info.txt' ) ) {
											$template_header       = array(
												'Template' => 'Template',
											);
											$template_data         = get_file_data( $fullpath . '/info.txt', $template_header, 'theme' );
											$item['template_name'] = $template_data['Template'];
										}
										$template_id                                = basename( $fullpath );
										$templates[ $template_id ]['template_name'] = $item['template_name'];
									}
								}
								$listing_description_template = isset( $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_listing_description_template'] ) ? $renderDataOnGlobalSettings[ $user_id ]['ced_ebay_listing_description_template'] : '';
								if ( ! empty( $templates ) ) {
									?>
								<select name="ced_ebay_global_settings[ced_ebay_listing_description_template]" data-fieldId="ced_ebay_listing_description_template">
								<option value=""><?php esc_attr_e( 'Select', 'ebay-integration-for-woocommerce' ); ?></option>
									<?php
									foreach ( $templates as $key => $value ) {
										?>
									<option <?php echo ( $key == $listing_description_template ) ? 'selected' : ''; ?> value="<?php echo esc_attr( $key ); ?>"><?php esc_attr_e( $value['template_name'], 'ebay-integration-for-woocommerce' ); ?></option>
										<?php
									}
									?>
								</select>
									<?php
								} else {
									?>
						<p>No templates were found. Please create atleast one template in the Account Settings section of the plugin.</p>

									<?php
								}
								?>

							</li>
						</ul>

					</td>
				</tr>




			</tbody>
		</table>

		<footer>
		</footer>


		<header>
			<h3>Scheduler Configuration</h3>
		</header>

		<p>Manage the Automatic Sync of Products, Stock and Orders.</p>

		<table class="form-table ced-form-table" >
			<tbody>
				<tr class="cmb-row cmb-type-switch">

					<th scope="row"><label>Order Sync</label></th>
					<td>

						<p class="description">Fetch your eBay Orders automatically and create them in WooCommerce. Only available in the premium version. </p>
						</p>
					</td>
				</tr>
				<tr class="cmb-row cmb-type-switch">

					<th scope="row"><label>Inventory Sync</label></th>
					<td>

						<p class="description"> Update your eBay inventory automatically as soon as stock change is detected in your WooCommerce store. Only available in the premium version.</p>
						</p>
						</p>
					</td>
				</tr>

				<tr class="cmb-row cmb-type-switch">

					<th scope="row"><label>Existing Products Sync</label></th>
					<td>

						<p class="description"> Sync the Listing ID of your eBay Products to the corresponding WooCommerce Products <b>on the basis of SKU</b>. Only available in the premium version.</p>
						</p>
						</p>
						</p>
					</td>
				</tr>
				<tr class="cmb-row cmb-type-switch">

<th scope="row"><label>Automatic Product Import</label></th>
<td>


	<p class="description"> Import your eBay Products to your WooCommerce Store automatically. For auction listings, the price of imported products will be 0. Only available in the premium version.</p>

	</p>
	</p>
	</p>
</td>
</tr>
			</tbody>
		</table>

		<footer>
			<?php wp_nonce_field( 'ced_ebay_setting_page_nonce', 'ced_ebay_setting_nonce' ); ?>
			<button id="save_global_settings" name="global_settings" class="button button-primary button-xlarge"><?php esc_attr_e( 'Save Configuration', 'ebay-integration-for-woocommerce' ); ?></button>
			<button id="rest_global_settings" name="reset_global_settings" class="button button-primary button-xlarge" style="background:#c62019 !important;"><?php esc_attr_e( 'Reset Configuration', 'ebay-integration-for-woocommerce' ); ?></button>
		</footer>

	</form>
</div>

<script>
	if ( jQuery( 'input[name="ced_ebay_global_settings[ced_ebay_import_ebay_categories]"]:checked' ).val() == 'Enabled') {
			jQuery('#ced_ebay_select_categories_type_to_import').show();
		} else if( jQuery( 'input[name="ced_ebay_global_settings[ced_ebay_import_ebay_categories]"]:checked' ).val() == 'Disabled') {
			jQuery('#ced_ebay_select_categories_type_to_import').hide();

		}
</script>
