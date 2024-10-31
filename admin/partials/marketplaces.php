<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
if ( is_array( $activeMarketplaces ) && ! empty( $activeMarketplaces ) ) {

	?>
	<div class="ced-ebay-v2-header">
	<div class="ced-ebay-v2-logo">
				</div>
			<div class="ced-ebay-v2-header-content">
				<div class="ced-ebay-v2-title">
					<h1>Active Marketplaces</h1>
				</div>

		</div>
</div>
	<div class="ced-marketplaces-card-view-wrapper">
		<?php
		foreach ( $activeMarketplaces as $key => $value ) {
				$url = admin_url( 'admin.php?page=' . $value['menu_link'] );
			?>
			<div class="ced-marketplace-card <?php echo esc_attr_e( $value['name'] ); ?>">
				<a href="<?php echo esc_attr_e( $url ); ?>">
					<div class="thumbnail">
						<div class="thumb-img">
							<img class="img-responsive center-block integration-icons" src="<?php echo esc_attr_e( $value['card_image_link'] ); ?>" height="auto" width="auto" alt="how to sell on vip marketplace">
						</div>
					</div>
					<div class="mp-label"><?php echo esc_attr_e( $value['name'] ); ?></div>
				</a>
			</div>
			<?php
		}
		?>
	</div>
	<?php
}
?>
