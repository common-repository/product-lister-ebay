<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';

global $wpdb;
$tableName   = $wpdb->prefix . 'ced_ebay_accounts';
$shopDetails = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_ebay_accounts WHERE `user_id`=%s", $user_id ), 'ARRAY_A' );

if ( isset( $_GET['section'] ) ) {

	$section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : '';
}

?>
<div class="ced_ebay_loader">
	<img src="<?php echo esc_attr( CED_EBAY_URL ) . 'admin/images/loading.gif'; ?>" width="50px" height="50px" class="ced_ebay_loading_img" >
</div>
<div class=" wrap ced-ebay-v2-wrap">
<div class="ced-ebay-v2-admin-field ced-ebay-v2-admin-menu">
					<ul class="ced-ebay-v2-admin-tabs">
					<li>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_ebay&section=accounts-view&type=ebay-lister&user_id=' . $user_id ) ); ?>" class="
								<?php
								if ( 'accounts-view' == $section ) {
									echo 'active';}
								?>
			"><?php esc_attr_e( 'Account Settings', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
			<a href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_ebay&section=settings-view&type=ebay-lister&user_id=' . $user_id ) ); ?>" class="
								<?php
								if ( 'settings-view' == $section ) {
									echo 'active';}
								?>
			"><?php esc_attr_e( 'General Settings', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( 'category-mapping-view' == $section ) {
				echo 'active';}
			?>
			" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_ebay&section=category-mapping-view&type=ebay-lister&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Category Mapping', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( 'profiles-view' == $section ) {
				echo 'active';}
			?>
			" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_ebay&section=profiles-view&type=ebay-lister&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Profile', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( 'products-view' == $section ) {
				echo 'active';}
			?>
			" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_ebay&section=products-view&type=ebay-lister&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Products', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>

		<li>
		<a class="
		<?php
		if ( 'import-products' == $section ) {
			echo 'active';
		}
		?>
		" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_ebay&section=import-products&type=ebay-lister&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Import Products', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
		<a class="
		<?php
		if ( 'marketing-view' == $section ) {
			echo 'active';
		}
		?>
		" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_ebay&section=marketing-view&type=ebay-lister&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Marketing', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
		<li>
			<a class="
			<?php
			if ( 'orders-view' == $section ) {
				echo 'active';}
			?>
			" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_ebay&section=orders-view&type=ebay-lister&user_id=' . $user_id ) ); ?>"><?php esc_attr_e( 'Orders', 'ebay-integration-for-woocommerce' ); ?></a>
		</li>
					</ul>
</div>

				</div>
				<div class="success-admin-notices is-dismissible"></div>



				<section class="woocommerce-inbox-message plain">
			<div class="woocommerce-inbox-message__wrapper">
				<div class="woocommerce-inbox-message__content">
					<h3 class="woocommerce-inbox-message__title">You are using the Free Version of the plugin</h3>
					<div class="woocommerce-inbox-message__text">
									<b><span>Hey there! With the free version you won't be able to promote your listings on eBay as well as effortlessly and automatically sync your inventory, products and sync and fulfill your eBay Orders. Upgrade now to enjoy all the benefits. </span>

					</b></div><b>
				</b></div>
				<div class="woocommerce-inbox-message__actions"><a href="https://woocommerce.com/products/ebay-integration-for-woocommerce/?quid=eb215c39718f271235a31fd70ac12fbb" class="button button-primary">Upgrade Now</a>
			</div><b>
			</b></div><b>
		</b></section>
