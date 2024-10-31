<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( isset( $_GET['mode'] ) ) {
	$login_mode = isset( $_GET['mode'] ) ? sanitize_text_field( $_GET['mode'] ) : '';
	if ( 'sandbox' == $login_mode ) {
		update_option( 'ced_ebay_mode_of_operation', 'sandbox' );
	}
}

if ( isset( $_GET['section'] ) ) {

	$file = CED_EBAY_DIRPATH . 'admin/partials/' . sanitize_text_field( $_GET['section'] ) . '.php';
	if ( file_exists( $file ) ) {
		require_once $file;
	}
} else {
	?>
<div class="ced_ebay_loader">
	<img src="<?php echo esc_attr( CED_EBAY_URL ) . 'admin/images/loading.gif'; ?>" width="50px" height="50px" class="ced_ebay_loading_img" >
</div>
	<?php
	if ( ! empty( get_option( 'ced_ebay_user_access_token' ) ) ) {
		$ebay_user_data = get_option( 'ced_ebay_user_access_token' );
		foreach ( $ebay_user_data as $key => $value ) {
			$user_id = $key;
			$site_id = $value['site_id'];
			break;
		}
		// wp_redirect(get_admin_url() . 'admin.php?page=ced_ebay&user_id='.$user_id);
		?>
	<div class="ced-ebay-v2-header">
			<div class="ced-ebay-v2-logo">
			<img src="<?php echo esc_attr( CED_EBAY_URL ) . 'admin/images/icon-100X100.png'; ?>">
			</div>
			<div class="ced-ebay-v2-header-content">
				<div class="ced-ebay-v2-title">
					<h1>eBay Integration for WooCommerce</h1>
				</div>
				<div class="ced-ebay-v2-actions">


			</div>
		</div>
</div>
		<div class="ced_ebay_wrap ced_ebay_wrap_extn">

			<div>
				<?php
				if ( ! session_id() ) {
					session_start();
				}
				?>

<div class="ced_ebay_marketing-view-container">
  <h1 class="ced_ebay_marketing_welcome_text">
	Welcome, <?php echo esc_html( $user_id ); ?>!
  </h1>
  <p style="max-width:100%;font-size:22.5px;">
	<b>You have Successfully Connected your eBay Store</b>
  </p>
  <p style="max-width:100%;padding-top:5px;font-size:17.5px;">
	Get Started with Listing, Syncing, Managing & Automating your WooCommerce and eBay Store to Boost Sales.
  </p>
  <div class="button-container">


	<ul style="display:block;">

	  <li style="display:inline-block;">

<a  style="all:unset;"href=<?php echo esc_attr( 'admin.php?page=ced_ebay&section=accounts-view&user_id=' . $user_id ); ?>><div style="padding:2px 15px;font-size:16px;" class="button -blue center">Click Here to Get Started</div></a>

</li>

	</ul>


  </div>

  <div class="button-container">


<ul style="display:block;">

<li style="display:inline-block;">
		<div class="button -blue center" data-user-id="<?php echo esc_attr( $user_id ); ?>" data-site-id="<?php echo esc_attr( $site_id ); ?>" id="ced_ebay_marketing_do_login">Having Issues? Authorize Again</div>
	  </li>
	  <li style="display:inline-block;">

<div class="admin-custom-action-show-button-outer">
	<button id="ced_ebay_disconnect_account_btn" type="button" class="button btn-normal-tt">
		<span>Disconnect Account</span>
	</button>
</div>
	  </li>

	  <!-- <li style="display:inline-block;">
		<div style="background:red;color:white;" class="button center" data-user-id="<?php echo esc_attr( $user_id ); ?>" data-site-id="<?php echo esc_attr( $site_id ); ?>" id="ced_ebay_disconnect_account">Disconnect eBay Account</div>

	  </li> -->
</ul>




</div>

<p style="max-width:100%;font-size:19.5px;">
	We also Offer Integrations for <b>Amazon, Etsy, Walmart, Shopee, Lazada, Discogs and Other Global Marketplaces</b>. Please Contact Us to Learn More!
  </p>

  <div class="ced_ebay_marketing_data_grids">
	<!--start of flex grids-->

	<div class="ced_ebay_marketing_data_text">
	  <!--start of flex item-->
					</div>
	<!--end of flex item-->

				</div>  <!--end of flex grids-->

</div>



		<?php
	} else {


		?>

<div class="ced-ebay-v2-header">
			<div class="ced-ebay-v2-logo">
			<img src="<?php echo esc_attr( CED_EBAY_URL ) . 'admin/images/icon-100X100.png'; ?>">
			</div>
			<div class="ced-ebay-v2-header-content">
				<div class="ced-ebay-v2-title">
					<h1>eBay Integration for WooCommerce</h1>
				</div>
				<div class="ced-ebay-v2-actions">


			</div>
		</div>
</div>
		<div class="ced_ebay_wrap ced_ebay_wrap_extn">

			<div>
				<?php
				if ( ! session_id() ) {
					session_start();
				}
				?>

		<div class="ced_ebay_marketing-view-container">
	<div class="ced-ebay-v2-title">
		<?php
		if ( ! empty( get_option( 'ced_ebay_oauth_error_description' ) ) ) {
			?>
<div class="bg-red-200 px-6 py-4 mx-2 rounded-md text-lg flex items-center">
	 <span class="text-green-800"><?php echo esc_html( get_option( 'ced_ebay_oauth_error_description', true ) ); ?></span>
	 <span class="text-green-800">. Please Refresh the page and try logging in again. If the issue persists, please contact support!</span>
   </div>
			<?php
			delete_option( 'ced_ebay_oauth_error_description' );
		}
		?>

	<h1 style="text-align:center;">
	  Hey there! Welcome aboard!
	</h1>
	</div>

	<p style="font-size:17.5px;">
		To start using <b>eBay Integration for WooCommerce</b>, you need to add an eBay account by selecting your eBay Seller Account Region and then clicking on the Login button below.
	</p>
		<?php
		require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayConfig.php';
		$ebayConfig            = new Ebayconfig();
		$ebayConfigInstance    = $ebayConfig->get_instance();
		$ebaySites             = $ebayConfigInstance->getEbaysites();
		$selectedSiteId        = '';
		$optionEbaysites['-1'] = 'Select eBay Site';
		if ( is_array( $ebaySites ) && ! empty( $ebaySites ) ) {
			foreach ( $ebaySites as $sites ) {
				if ( '' != $selectedSiteId && $selectedSiteId == $sites['siteID'] ) {
					$selected = 'selected';
				} else {
					$selected = '';
				}
				$optionEbaysites[ $sites['siteID'] ] = $sites['name'];
			}
		}
		woocommerce_wp_select(
			array(
				'id'      => 'ced_ebay_login_select_site',
				'label'   => '',
				'style'   => 'margin-top:20px;width:180px;',
				'options' => $optionEbaysites,
			)
		);
		?>
	<div class="button-container">
		<?php
		$login_mode = isset( $_GET['mode'] ) ? sanitize_text_field( $_GET['mode'] ) : '';
		if ( 'sandbox' == $login_mode ) {
			?>
	  <div class="button -blue center" data-login-mode="sandbox" id="ced_ebay_marketing_do_login">Login (Sandbox)</div>

			<?php
		} else {
			?>
	  <div class="button -blue center" data-login-mode="production" id="ced_ebay_marketing_do_login">Login</div>
			<?php
		}
		?>
	</div>
	<div class="button-container">
	<p>By Connecting your eBay Account you agree to CedCommerce <a href="https://cedcommerce.com/privacy-policy">Privacy Policy</a></p>

	</div>

	<div class="button-container">
	<p>Made with <span class="dashicons dashicons-heart"></span> in India</p>

	</div>

  </div>

		<?php
	}
}
?>


<style>
	#wpfooter{
		display: none;
	}
</style>
