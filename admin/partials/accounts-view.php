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

$shop_data = isset( $shopDetails ) ? $shopDetails : array();
$user_id   = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
$part      = isset( $_GET['part'] ) ? sanitize_text_field( $_GET['part'] ) : '';
?>


<div class="ced-ebay-v2-header">
			<div class="ced-ebay-v2-logo">
			<img src="<?php echo esc_attr( CED_EBAY_URL ) . 'admin/images/icon-100X100.png'; ?>">
			</div>
			<div class="ced-ebay-v2-header-content">
				<div class="ced-ebay-v2-title">
					<h1>Account Settings</h1>
				</div>
				<div class="ced-ebay-v2-actions">
				<a class="ced-ebay-v2-btn" href="https://docs.woocommerce.com/document/ebay-integration-for-woocommerce/#section-5" target="_blank">
					Documentation					</a>

			</div>
		</div>
</div>
		<?php
		$shipping_select             = '';
		$return_select               = '';
		$description_template_select = '';
		
		if ( 'shipping' == $part || '' == $part																																																														) {
			$shipping_select = 'active';
		} elseif ( 'return' == $part ) {
			$return_select = 'active';
		} elseif ( 'description_template' == $part ) {
			$description_template_select = 'active';
		}
		?>

		<div class="ced_ebay_flex_wrapper">
			<div class="ced_ebay_account_settings_header">
				<ul>
					
					<li class="<?php echo esc_attr_e( $shipping_select ); ?>">																																																																										
						<a href="<?php echo esc_attr_e( admin_url( 'admin.php?page=ced_ebay&section=accounts-view&type=ebay-lister&part=shipping&user_id=' . $user_id ) ); ?>" id="shipping_details" class="ced-ebay-v2-btn"><?php esc_html_e( 'Shipping Settings', 'ebay-integration-for-woocommerce' ); ?></a>
					</li>
					<li class="<?php echo esc_attr_e( $return_select ); ?>">
						<a href="<?php echo esc_attr_e( admin_url( 'admin.php?page=ced_ebay&section=accounts-view&type=ebay-lister&part=return&user_id=' . $user_id ) ); ?>" id="return_details" class="ced-ebay-v2-btn"><?php esc_html_e( 'Return Settings', 'ebay-integration-for-woocommerce' ); ?></a>
					</li>
					<li class="<?php echo esc_attr_e( $description_template_select ); ?>">
						<a href="<?php echo esc_attr_e( admin_url( 'admin.php?page=ced_ebay&section=accounts-view&type=ebay-lister&part=description_template&user_id=' . $user_id ) ); ?>" id="description_template_details" class="ced-ebay-v2-btn"><?php esc_html_e( 'Description Template', 'ebay-integration-for-woocommerce' ); ?></a>
					</li>
				</ul>
			</div>
			<div class="ced_ebay_account_settings_fields">
				<?php
				if ( 'shipping' == $part || '' == $part ) {
					?>
					<div class="ced_ebay_shipping_details_wrapper">
						<?php
						include_once CED_EBAY_DIRPATH . 'admin/partials/ced-ebay-shipping-fields.php';
						include_once CED_EBAY_DIRPATH . 'admin/partials/ced-ebay-add-shipping-template-fields.php';
						?>
					</div>
					<article class="ced_ebay_faq">
  <div class="ced_ebay_faq_description">
	  <h1>Troubleshooting</h1>
  </div>
  <dl class="ced_ebay_faq_collection">
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		What are Shipping Templates?<i class="fa fa-chevron-down"></i>
	  </dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	  Shipping templates are used for attaching shipping details to your product when they are uploaded on eBay. It's mandatory to have atleast one shipping template to be able to successfully upload your product to eBay.
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		So many fields to fill, is this necessary?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	  YES. Products listed on eBay are required to have valid a shipping method.
	All the fields, except for Domestic Shipping Service or International Shipping service, based on which you choose, are mandatory and can't be left blank.
	Failure to fill any of the fields will result in product upload error.
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		What do you mean by Postal Code and Packet Weight Range?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	You need to fill the postal code of the place from where you will be shipping your products. Packet weight range is used by our plugin to distinguish which shipping template to use based on the weight of the product to upload.
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		I am getting an error, what do I do?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	During product upload, you may encounter the following errors -
	<ul>
	<li>
	<p style="color:red;">At least one valid shipping service must be specified</p>
	<p>You haven't specified any shipping template or the weight range mentioned in the template is wrong.
	For example, if you have mentioned the weight range from 0-0.5 and one of your product is above 0.5
	it won't be uploaded and you will encounter this error.</p>
	</li>
	<li>
	<p style="color:red;">Your application encountered an error</p>
	<p>This error usually happens when you haven't selected any countries to ship to while filling the International Shipping Service field.</p>
	</li>
	<li>
	<p style="color:red;">All locations are dropped</p>
	<p>This error means that your marketplace region doesn't support shipping to a country you mentioned in
	the shipping template. For example, if your marketplace region is U.K then you can't ship to Brazil.
	If you have choosen brazil as a supported country for International Delivery, you will encounter this error.</p>
	</li>
	</ul>
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js">
	<h4>If you are facing any other sort of issues with our plugin, please reach us at support@cedcommerce.com or you can join our <a href="https://join.skype.com/UHRP45eJN8qQ">Skype</a> or <a href="https://chat.whatsapp.com/BcJ2QnysUVmB1S2wmwBSnE">WhatsApp</a> group.</h4>

	</dt>
  </dl>
</article>
					<?php
				} elseif ( 'return' == $part ) {
					?>
					<div class="ced_ebay_return_details_wrapper">
						<?php
						include_once CED_EBAY_DIRPATH . 'admin/partials/ced-ebay-return-fields.php';
						?>
					</div>
					<article class="ced_ebay_faq">
  <div class="ced_ebay_faq_description">
	  <h1>Troubleshooting</h1>
  </div>
  <dl class="ced_ebay_faq_collection">
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		What is this section about?<i class="fa fa-chevron-down"></i>
	  </dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	 In this section, you can select details about the return options which you will be providing for your products listed on
	 eBay. All the options are fetched from eBay and we don't have any control over them. Once you have selected the options and filled the description, use the <span style="color:red;">'Save Details'</span> button to save them.
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		Do I need to fill in Description?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	  Yes. You can fill something along the lines of '30 Days of Return' or anything else. <span style="color:red;">Description is mandatory</span>
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js">
	<h4>If you are facing any other sort of issues with our plugin, please reach us at support@cedcommerce.com or you can join our <a href="https://join.skype.com/UHRP45eJN8qQ">Skype</a> or <a href="https://chat.whatsapp.com/BcJ2QnysUVmB1S2wmwBSnE">WhatsApp</a> group.</h4>

	</dt>
  </dl>
</article>
					<?php
				} elseif ( 'description_template' == $part ) {
					?>
					<div class="ced_ebay_description_template_details_wrapper">
						<?php
						include_once CED_EBAY_DIRPATH . 'admin/partials/ced-ebay-description-template-fields.php';
						?>
					</div>
					<article class="ced_ebay_faq">
  <div class="ced_ebay_faq_description">
	  <h1>Troubleshooting</h1>
  </div>
  <dl class="ced_ebay_faq_collection">
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		What is this section about?<i class="fa fa-chevron-down"></i>
	  </dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	An eBay listing template is a pre-made layout that you can use when listing items to eBay. It helps you to achieve consistency across your eBay listings,
	while also making it quick and easy to add news products to the platform. You can also benefit from enhanced branding,
	SEO-optimization, psychological link placement, mobile responsiveness, and much more.
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		Do I need to create a Description Template?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	  No. Description templates are optional. If no description template has been created and selected in the products profile our plugin picks the default WooCommerce product description and uploads it to eBay.</span>
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		I have created a description template, now what?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	  After you have successfully created a description template, you can choose which template to use during profile creation. <span style="color:red;">Description templates are not mandatory.</span>
	  If you haven't created any description template, our plugin will use the default WooCommerce product description.
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		What are Product Shortcodes?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	  Product shortcodes are used in Description Template to automatically fill in the relevant product details such as title, description, image. Only the shortcodes listed in the Description Template
	  popup are supported at the moment.
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js">
	<h4>If you are facing any other sort of issues with our plugin, please reach us at support@cedcommerce.com or you can join our <a href="https://join.skype.com/UHRP45eJN8qQ">Skype</a> or <a href="https://chat.whatsapp.com/BcJ2QnysUVmB1S2wmwBSnE">WhatsApp</a> group.</h4>

	</dt>
  </dl>
</article>
					<?php
				}
				?>
			</div>
		</div>
	</div>

</div>
