<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Function- Product Fields.
 * Used to add product Fields

 * @since      1.0.0
 *
 * @package    eBay Integration for Woocommerce
 * @subpackage eBay Integration for Woocommerce/admin/helper
 */

if ( ! class_exists( 'CedeBayProductsFields' ) ) {

	/**
	 * Single product related functionality.
	 *
	 * Manage all single product related functionality required for listing product on marketplaces.
	 *
	 * @since      1.0.0
	 * @package    eBay Integration for Woocommerce
	 * @subpackage eBay Integration for Woocommerce/admin/helper
	 */
	class CedeBayProductsFields {


		/**
		 * The Instace of CED_ebay_product_fields.
		 *
		 * @since    1.0.0
		 * @var      $_instance   The Instance of CED_ebay_product_fields class.
		 */
		private static $_instance;

		/**
		 * CED_ebay_product_fields Instance.
		 *
		 * Ensures only one instance of CED_ebay_product_fields is loaded or can be loaded.
		 *
		 * @since 1.0.0
		 * @static
		 * @return CED_ebay_product_fields instance.
		 */
		public static function get_instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}


		/**
		 * Get product custom fields for preparing
		 * product data information to send on different
		 * marketplaces accoding to there requirement.
		 *
		 * @since 1.0.0
		 * @param string $type  required|framework_specific|common
		 * @param bool   $ids  true|false
		 * @return array  fields array
		 */
		public static function ced_ebay_get_custom_products_fields( $categoryID ) {
			global $post;
			$upload_dir = wp_upload_dir();

			$templates_dir = $upload_dir['basedir'] . '/ced-ebay/templates/';

			$templates = array();
			$files     = glob( $upload_dir['basedir'] . '/ced-ebay/templates/*/template.html' );
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
			foreach ( $templates as $key => $value ) {
				$description_template_name[ $key ] = $value['template_name'];
			}
			$user_id         = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
			$required_fields = array(
				array(
					'type'   => '_select',
					'id'     => '_umb_ebay_global_shipping_program',
					'fields' => array(
						'id'          => '_umb_ebay_global_shipping_program',
						'label'       => __( 'Enable Global Shipping Program', 'ced-umb-ebay' ),
						'options'     => array(
							'true'  => __( 'True', 'ced-umb-ebay' ),
							'false' => __( 'False', 'ced-umb-ebay' ),
						),
						'desc_tip'    => true,
						'description' => __( 'Enable/Disable Global shipping program for eBay Listings.', 'ced-umb-ebay' ),
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_umb_ebay_description_template',
					'fields' => array(
						'id'          => '_umb_ebay_description_template',
						'label'       => __( 'eBay Product Description Template', 'ced-umb-ebay' ),
						'options'     => ! empty( $description_template_name ) ? $description_template_name : array(),
						'desc_tip'    => true,
						'description' => __( 'Assign a custom description template your eBay Listings.', 'ced-umb-ebay' ),
					),
				),
				array(
					'type'   => '_select',
					'id'     => '_umb_ebay_profile_price_markup_type',
					'fields' => array(
						'id'          => '_umb_ebay_profile_price_markup_type',
						'label'       => __( 'eBay Product Markup Type', 'ced-umb-ebay' ),
						'description' => __( 'Select the type of Price Increase or Decrease of your products on eBay.', 'ebay-integration-for-woocommerce' ),
						'options'     => array(
							'Fixed_Increase'      => __( 'Fixed Increase', 'ced-umb-ebay' ),
							'Fixed_Decrease'      => __( 'Fixed Decrease', 'ced-umb-ebay' ),
							'Percentage_Increase' => __( 'Percentage Increase', 'ced-umb-ebay' ),
							'Percentage_Decrease' => __( 'Percentage Decrease', 'ced-umb-ebay' ),
						),
						'desc_tip'    => true,
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_umb_ebay_profile_price_markup',
					'fields' => array(
						'id'          => '_umb_ebay_profile_price_markup',
						'label'       => __( 'Markup Price', 'ced-umb-ebay' ),
						'description' => __( 'Specify by how much the product price will increase or decrease.', 'ebay-integration-for-woocommerce' ),
						'desc_tip'    => true,
						'type'        => 'number',
					),
				),
			);

			return $required_fields;
		}
		public function ced_ebay_get_profile_framework_specific() {
			$ebaySpecificFields = array(
				// array(
				// 'type'   => '_select',
				// 'id'     => '_umb_ebay_autopay',
				// 'fields' => array(
				// 'id'          => '_umb_ebay_autopay',
				// 'label'       => __( 'Ebay Auto Pay', 'ced-umb-ebay' ),
				// 'desc_tip'    => true,
				// 'description' => __( 'If checked, the seller requests immediate payment for the item. If false or not specified, immediate payment is not requested . This feature is also dependent on category.', 'ced-umb-ebay' ),
				// 'type'        => 'select',
				// 'options'     => array(
				// 'yes' => 'Yes',
				// 'no'  => 'No',
				// ),
				// 'class'       => 'wc_input_price',
				// ),
				// ),
				// array(
				// 'type'   => '_select',
				// 'id'     => '_umb_ebay_listing_type',
				// 'fields' => array(
				// 'id'          => '_umb_ebay_listing_type',
				// 'label'       => __( 'Listing Type', 'ced-umb-ebay' ),
				// 'desc_tip'    => true,
				// 'description' => __( 'This is required value', 'ced-umb-ebay' ),
				// 'type'        => 'select',
				// 'options'     => array(
				// 'AdType'         => 'AdType',
				// 'Chinese'        => 'Chinese',
				// 'FixedPriceItem' => 'FixedPriceItem',
				// 'Half'           => 'Half',
				// ),
				// 'class'       => 'wc_input_price',
				// ),
				// 'required' => true,
				// ),
				array(
					'type'     => '_select',
					'id'       => '_umb_ebay_listing_duration',
					'fields'   => array(
						'id'          => '_umb_ebay_listing_duration',
						'label'       => __( 'Listing Duration', 'ced-umb-ebay' ),
						'desc_tip'    => true,
						'description' => __( 'Select how long your listing will run. This is a required value.', 'ced-umb-ebay' ),
						'type'        => 'select',
						'options'     => array(
							'Days_1'   => 'Days_1',
							'Days_10'  => 'Days_10',
							'Days_120' => 'Days_120',
							'Days_14'  => 'Days_14',
							'Days_21'  => 'Days_21',
							'Days_3'   => 'Days_3',
							'Days_30'  => 'Days_30',
							'Days_5'   => 'Days_5',
							'Days_60'  => 'Days_60',
							'Days_7'   => 'Days_7',
							'Days_90'  => 'Days_90',
							'GTC'      => 'Good Till Cancelled',
						),
						'class'       => 'wc_input_price',
					),
					'required' => 'required',
				),
				// array(
				// 'type'   => '_select',
				// 'id'     => '_umb_ebay_bestoffer',
				// 'fields' => array(
				// 'id'          => '_umb_ebay_bestoffer',
				// 'label'       => __( 'Enable Best Offer feature', 'ced-umb-ebay' ),
				// 'desc_tip'    => true,
				// 'description' => __( 'If checked, the seller requests immediate payment for the item. If false or not specified, immediate payment is not requested . This feature is also dependent on category.', 'ced-umb-ebay' ),
				// 'type'        => 'select',
				// 'options'     => array(
				// 'yes' => 'Yes',
				// 'no'  => 'No',
				// ),
				// 'class'       => 'wc_input_price',
				// ),
				// ),
				// array(
				// 'type'   => '_text_input',
				// 'id'     => '_umb_ebay_auto_accept_offers',
				// 'fields' => array(
				// 'id'          => '_umb_ebay_auto_accept_offers',
				// 'label'       => __( 'Automatically Accept offers of atleast', 'ced-umb-ebay' ),
				// 'desc_tip'    => true,
				// 'description' => __( 'Specify the percent or fixed price markup that can be accepted. For example - enter values like +|10|$, -|10|$ , +|10|%, -|10|% (use | as seperator). These markup will be applied to product price', 'ced-umb-ebay' ),
				// 'type'        => 'text',
				// 'class'       => 'wc_input_price',
				// ),
				// ),
				// array(
				// 'type'   => '_text_input',
				// 'id'     => '_umb_ebay_auto_decline_offers',
				// 'fields' => array(
				// 'id'          => '_umb_ebay_auto_decline_offers',
				// 'label'       => __( 'Automatically Decline offers lower than', 'ced-umb-ebay' ),
				// 'desc_tip'    => true,
				// 'description' => __( 'Specify the percent or fixed price markup that can be accepted. For example - enter values like +|10|$, -|10|$ , +|10|%, -|10|% (use | as seperator). These markup will be applied to product price', 'ced-umb-ebay' ),
				// 'type'        => 'text',
				// 'class'       => 'wc_input_price',
				// ),
				// ),
				array(
					'type'     => '_text_input',
					'id'       => '_umb_ebay_dispatch_time',
					'fields'   => array(
						'id'          => '_umb_ebay_dispatch_time',
						'label'       => __( 'Maximum Dispatch Time', 'ced-umb-ebay' ),
						'desc_tip'    => true,
						'description' => __( 'Specifies the maximum number of business days the seller commits to for preparing an item to be shipped after receiving a cleared payment.', 'ced-umb-ebay' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
					'required' => 'required',
				),
				array(
					'type'   => '_text_input',
					'id'     => '_umb_ebay_mpn',
					'fields' => array(
						'id'          => '_umb_ebay_mpn',
						'label'       => __( 'MPN', 'ced-umb-ebay' ),
						'desc_tip'    => true,
						'description' => __( 'manufacturer part number of the product.Brand field must be filled to use it.', 'ced-umb-ebay' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_umb_ebay_ean',
					'fields' => array(
						'id'          => '_umb_ebay_ean',
						'label'       => __( 'EAN', 'ced-umb-ebay' ),
						'desc_tip'    => true,
						'description' => __( 'EAN is a unique 8 or 13 digit identifier used to identify products.', 'ced-umb-ebay' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_umb_ebay_isbn',
					'fields' => array(
						'id'          => '_umb_ebay_isbn',
						'class'       => 'tooltip',
						'label'       => __( 'ISBN', 'ced-umb-ebay' ),
						'desc_tip'    => true,
						'description' => __( 'ISBN is a unique identifer for books (an international standard). Specify a 10 or 13-character ISBN.', 'ced-umb-ebay' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
				array(
					'type'   => '_text_input',
					'id'     => '_umb_ebay_upc',
					'fields' => array(
						'id'          => '_umb_ebay_upc',
						'label'       => __( 'UPC', 'ced-umb-ebay' ),
						'desc_tip'    => true,
						'description' => __( 'UPC is a unique, 12-character identifier that many industries use to identify products.', 'ced-umb-ebay' ),
						'type'        => 'text',
						'class'       => 'wc_input_price',
					),
				),
			);

			return $ebaySpecificFields;
		}

		/**
		 *
		 * Function for render dropdown html
		 */
		public function renderDropdownHTML( $attribute_id, $attribute_name, $values, $categoryID, $productID, $marketPlace, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $is_required = '' ) {

			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}

			?><input type="hidden" name="<?php echo esc_attr( $marketPlace ) . '[]'; ?>" value="<?php echo esc_attr( $fieldName ); ?>" />

			<td>
				<label><?php echo esc_attr( $attribute_name ); ?>
				<?php
				if ( 'required' == $is_required ) {
					?>
					<span class="ced_ebay_wal_required"><?php echo esc_attr( '[Required]' ); ?></span>
					<?php
				}
				?>
			</label>
			</td>
			<td>
				<select id="" name="<?php echo esc_attr( $fieldName ) . '[' . esc_attr( $indexToUse ) . ']'; ?>" class="select short" style="">
				<?php
				echo '<option value="">' . esc_attr( '-- Select --' ) . '</option>';
				foreach ( $values as $key => $value ) {
					if ( $previousValue == $key ) {
						echo '<option value="' . esc_attr( $key ) . '" selected>' . esc_attr( $value ) . '</option>';
					} else {
						echo '<option value="' . esc_attr( $key ) . '">' . esc_attr( $value ) . '</option>';
					}
				}
				?>
			</select>
			</td>


			<?php
		}

		/**
		 *
		 * Function to render input fields
		 */
		public function renderInputTextHTML( $attribute_id, $attribute_name, $categoryID, $productID, $marketPlace, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false ) {

			global $post, $product, $loop;
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}

			if ( 'required' == $conditionally_required ) {
				$required_item_specific = true;
			} else {
				$required_item_specific = false;
			}
			if ( $required_item_specific ) {
				$fieldNameRequired  = $fieldName;
				$fieldNameRequired .= '_required';
			}

			?>

				<input type="hidden" name="<?php echo esc_attr( $marketPlace ) . '[]'; ?>" value="<?php echo esc_attr( ! empty( $fieldNameRequired ) ? $fieldNameRequired : $fieldName ); ?>" />
			<td>
				<label>
				<?php echo esc_attr( $attribute_name ); ?>
				<?php
				if ( 'required' == $conditionally_required ) {
					$required_item_specific = true;
					?>
					<span class="ced_ebay_wal_required"><?php echo esc_attr( '[Required]' ); ?></span>
					<?php
				} else {
					$required_item_specific = false;
				}

				?>
			</label>
			</td>
						<td>
				<label>Enter Custom Value</label>
				<input class="short"  name="<?php echo esc_attr( $fieldName ) . '[' . esc_attr( $indexToUse ) . ']'; ?>" id="" value="<?php echo esc_attr( $previousValue ); ?>" placeholder="" type="text" />
				<span style="padding-left:10px;font-size:18px;color:#5850ec;"><b>Or</b></span>
			</td>


			<?php
		}
		/**
		 *
		 * Function to render hidden input fields
		 */
		public function renderInputTextHTMLhidden( $attribute_id, $attribute_name, $categoryID, $productID, $marketPlace, $indexToUse, $additionalInfo = array( 'case' => 'product' ), $conditionally_required = false ) {
			global $post, $product, $loop;
			$fieldName = $categoryID . '_' . $attribute_id;
			if ( 'product' == $additionalInfo['case'] ) {
				$previousValue = get_post_meta( $productID, $fieldName, true );
			} else {
				$previousValue = $additionalInfo['value'];
			}

			?>

				<input type="hidden" name="<?php echo esc_attr( $marketPlace ) . '[]'; ?>" value="<?php echo esc_attr( $fieldName ); ?>" />
			<td>
				</label>
			</td>
						<td>
				<label></label>
				<input class="short" style="" name="<?php echo esc_attr( $fieldName ) . '[' . esc_attr( $indexToUse ) . ']'; ?>" id="" value="<?php echo esc_attr( $previousValue ); ?>" placeholder="" type="hidden" />
			</td>


			<?php
		}
	}
}
