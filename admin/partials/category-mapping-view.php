<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {

	die;

}

$file = CED_EBAY_DIRPATH . 'admin/partials/header.php';

if ( file_exists( $file ) ) {

	require_once $file;

}

$shopDetails            = ced_ebay_get_shop_data( $user_id );
$getLocation            = $shopDetails['location'];
$getLocation            = strtolower( $getLocation );
$getLocation            = str_replace( ' ', '', $getLocation );
$wp_folder              = wp_upload_dir();
$wp_upload_dir          = $wp_folder['basedir'];
$wp_upload_dir          = $wp_upload_dir . '/ced-ebay/cateogry-templates-json/' . $user_id;
$folderName             = $wp_upload_dir . '/categoryLevel-1_' . $getLocation . '.json';
$categoryFirstLevelFile = $folderName;
$ebay_categories        = array();
require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayAuthorization.php';
$cedAuthorization        = new Ebayauthorization();
$cedAuhorizationInstance = $cedAuthorization->get_instance();

$storeDetails = $cedAuhorizationInstance->getStoreData( $shopDetails['site_id'], $user_id );
if ( ! empty( $storeDetails ) && 'Success' == $storeDetails['Ack'] ) {
	$store_categories = $storeDetails['Store']['CustomCategories']['CustomCategory'];
	if ( ! isset( $store_categories[0] ) ) {
		$temp_store_categories = $store_categories;
		unset( $store_categories );
		$store_categories[0] = $temp_store_categories;
	}
	update_option( 'ced_ebay_store_data_' . $user_id, $store_categories );
	if ( 1 != count( $store_categories ) ) {
		$store_custom_category_html = '<select type="text" name="ced_ebay_select_store_categories" id="ced_ebay_select_store_categories" class="w-full p-5 bg-white border border-gray-200 rounded shadow-sm appearance-none" id=""><option value="">Select store category</option>' . ced_ebay_recursive_array_search( $store_categories ) . '</select>';
		update_option( 'ced_ebay_store_category_mapping_html_markup_' . $user_id, $store_custom_category_html );
	}
}

function ced_ebay_recursive_array_search( $store_categories, $depth = 0 ) {
	$indent_str = str_repeat( '-', $depth );
	foreach ( $store_categories as $key => $value ) {
		if ( isset( $value['ChildCategory'] ) ) {
			$store_cat_html .= '<option name="' . $value['Name'] . '" value="' . $value['CategoryID'] . '">' . $indent_str . ' ' . $value['Name'] . '</option>';
			$store_cat_html .= ced_ebay_recursive_array_search( $value['ChildCategory'], ( $depth + 1 ) );
		} else {
			if ( isset( $value['Name'] ) ) {
				$store_cat_html .= '<option name="' . $value['Name'] . '" value="' . $value['CategoryID'] . '">' . $indent_str . ' ' . $value['Name'] . '</option>';
			}
		}
	}
	return $store_cat_html;

}

function ced_ebay_recursive_find_category_id( $needle, $haystack ) {
	foreach ( $haystack as $key => $value ) {
		if ( isset( $value['ChildCategory'] ) ) {
			if ( isset( $value['CategoryID'] ) && $value['CategoryID'] == $needle ) {
				return $value['Name'];
			} else {
				$nextKey = ced_ebay_recursive_find_category_id( $needle, $value['ChildCategory'] );
				if ( $nextKey ) {
					return $nextKey;
				}
			}
		} elseif ( isset( $value['CategoryID'] ) && $value['CategoryID'] == $needle ) {
			return $value['Name'];
		}
	}
	return false;
}

function get_categories_hierarchical( $args = array() ) {

	if ( ! isset( $args['parent'] ) ) {
		$args['parent'] = 0;
	}

	$categories = get_categories( $args );

	foreach ( $categories as $key => $category ) :

		$args['parent'] = $category->term_id;

		$categories[ $key ]->child_categories = get_categories_hierarchical( $args );

	endforeach;

	return $categories;

}

$woo_store_categories = get_categories_hierarchical(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => false,
	)
);

?>





<div class="ced-ebay-v2-header">

			<div class="ced-ebay-v2-logo">

			<img src="<?php echo esc_attr( CED_EBAY_URL ) . 'admin/images/icon-100X100.png'; ?>">

			</div>

			<div class="ced-ebay-v2-header-content">

				<div class="ced-ebay-v2-title">

					<h1>Category Mapping</h1>

				</div>

				<div class="ced-ebay-v2-actions">

<div class="admin-custom-action-button-outer">

<div class="admin-custom-action-show-button-outer">
<button type="button" class="button btn-normal-sbc" name="ced_ebay_refresh_categories" id="ced_ebay_category_refresh_button" data-shop-id="<?php echo esc_attr( $user_id ); ?>">
<span>Refresh Categories</span>
</button>

</div>

<div class="admin-custom-action-show-button-outer">
	<button id="ced_ebay_remove_all_profiles_btn" title="Remove All Profiles" type="button" class="button btn-normal-tt">
		<span>Delete All Mappings</span>
	</button>
</div>

<div class="admin-custom-action-show-button-outer">
<button style="background:#135e96;" type="button" class="button btn-normal-tt">
<span><a style="all:unset;" href="https://docs.woocommerce.com/document/ebay-integration-for-woocommerce/#section-7" target="_blank">
Documentation					</a></span>
</button>

</div>


</div>
</div>
		</div>
</div>

<div id="profile_create_message"></div>

<div class="ced_ebay_category_mapping_wrapper" id="ced_ebay_category_mapping_wrapper">

	<div class="ced_ebay_store_categories_listing" id="ced_ebay_store_categories_listing">

		<table class="wp-list-table widefat fixed striped posts ced_ebay_store_categories_listing_table" id="ced_ebay_store_categories_listing_table">

			<thead>

				<th><b><?php esc_attr_e( 'Select Categories to be Mapped', 'ebay-integration-for-woocommerce' ); ?></b></th>

				<th><b><?php esc_attr_e( 'WooCommerce Store Categories', 'ebay-integration-for-woocommerce' ); ?></b></th>

				<th colspan="3"><b><?php esc_attr_e( 'Mapped to eBay Category', 'ebay-integration-for-woocommerce' ); ?></b></th>
			</thead>

			<?php

			if ( file_exists( $categoryFirstLevelFile ) ) {
				$ebay_categories = file_get_contents( $categoryFirstLevelFile );
				$ebay_categories = json_decode( $ebay_categories, true );

				?>

						<tbody>

									<?php

									function nestdiv( $woo_store_categories, $ebay_categories, $depth = 0 ) {

												$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';

												$indent_str = str_repeat( '-', $depth );

										foreach ( $woo_store_categories as $key => $value ) {
											$category_mapped_name_to_secondary = '';

											?>

		<tr class='ced_ebay_store_category' id=<?php echo 'ced_ebay_store_category_' . esc_attr( $value->term_id ); ?> >

		<td>

		<input type='checkbox' class='ced_ebay_select_store_category_checkbox' name='ced_ebay_select_store_category_checkbox[]' data-categoryID='<?php echo esc_attr( $value->term_id ); ?>'></input>

	</td>







<td><b><span class='ced_ebay_store_category_name'><?php echo esc_attr( $indent_str . ' ' . $value->name ); ?></span></b>

		</td>

											<?php

											$category_mapped_to = get_term_meta( $value->term_id, 'ced_ebay_mapped_category_' . $user_id, true );

											$alreadyMappedCategoriesName = get_option( 'ced_woo_ebay_mapped_categories_name', array() );

											$alreadyMappedSecondaryCategoriesName = get_option( 'ced_woo_ebay_mapped_secondary_categories_name', array() );

											$category_mapped_name_to = isset( $alreadyMappedCategoriesName[ $user_id ][ $category_mapped_to ] ) ? $alreadyMappedCategoriesName[ $user_id ][ $category_mapped_to ] : '';

											if ( ! empty( get_term_meta( $value->term_id, 'ced_ebay_mapped_secondary_category_' . $user_id ) ) ) {

												$category_mapped_to_secondary = get_term_meta( $value->term_id, 'ced_ebay_mapped_secondary_category_' . $user_id, true );

												$category_mapped_name_to_secondary = isset( $alreadyMappedSecondaryCategoriesName[ $user_id ][ $category_mapped_to_secondary ] ) ? $alreadyMappedSecondaryCategoriesName[ $user_id ][ $category_mapped_to_secondary ] : '';

											}

											if ( '' != $category_mapped_to && null != $category_mapped_to && '' != $category_mapped_name_to && null != $category_mapped_name_to ) {

												?>

											<td colspan='4'>

											<span>

												<b><?php echo esc_attr( $category_mapped_name_to ); ?> (Primary eBay Category)<br>

												<?php

												if ( ! empty( $category_mapped_name_to_secondary ) ) {

													?>

												<b><?php echo esc_attr( $category_mapped_name_to_secondary ); ?> (Secondary eBay Category) | <a href="" style="color:red;" id="ced_ebay_remove_category_mapping" data-userId="<?php echo esc_attr( $user_id ); ?>" data-mappingType="ebay-secondary-category" data-termId=<?php echo esc_attr( $value->term_id ); ?>>Delete</a></b><br>

													<?php
												}

												if ( ! empty( get_term_meta( $value->term_id, 'ced_ebay_mapped_to_store_category_' . $user_id, true ) ) ) {

													$store_category_mapped_id = get_term_meta( $value->term_id, 'ced_ebay_mapped_to_store_category_' . $user_id, true );

													if ( ! empty( $store_category_mapped_id ) ) {

														$store_data = ! empty( get_option( 'ced_ebay_store_data_' . $user_id, true ) ) ? get_option( 'ced_ebay_store_data_' . $user_id, true ) : false;

														if ( $store_data ) {

															$store_category_mapped_name = ced_ebay_recursive_find_category_id( $store_category_mapped_id, $store_data );
															if ( ! empty( $store_category_mapped_name ) ) {

																?>

												<b><?php echo esc_attr( $store_category_mapped_name ); ?> ( Primary eBay Store Category) | <a href="" id="ced_ebay_remove_category_mapping" style="color:red;" data-mappingType="ebay-store-primary-category" data-userId="<?php echo esc_attr( $user_id ); ?>" data-termId=<?php echo esc_attr( $value->term_id ); ?>>Delete</a></b><br>

																<?php

															}
														}

														?>



														<?php

													}

													?>







														<?php

												}if ( ! empty( get_term_meta( $value->term_id, 'ced_ebay_mapped_to_store_secondary_category_' . $user_id, true ) ) ) {

													$store_category_secondary_mapped_id = get_term_meta( $value->term_id, 'ced_ebay_mapped_to_store_secondary_category_' . $user_id, true );

													if ( ! empty( $store_category_secondary_mapped_id ) ) {

														$store_data = ! empty( get_option( 'ced_ebay_store_data_' . $user_id, true ) ) ? get_option( 'ced_ebay_store_data_' . $user_id, true ) : false;

														if ( $store_data ) {

															$store_category_secondary_mapped_name = ced_ebay_recursive_find_category_id( $store_category_secondary_mapped_id, $store_data );
															if ( ! empty( $store_category_secondary_mapped_name ) ) {

																?>

					<b><?php echo esc_attr( $store_category_secondary_mapped_name ); ?> ( Secondary eBay Store Category) | <a href="" id="ced_ebay_remove_category_mapping" style="color:red;" data-mappingType="ebay-store-secondary-category" data-userId="<?php echo esc_attr( $user_id ); ?>" data-termId=<?php echo esc_attr( $value->term_id ); ?>>Delete</a></b>

																<?php

															}
														}

														?>



																		<?php

													}

													?>





									</span>

								</td>

													<?php

												}
											} else {

												?>

										<td colspan='4'>

											<span class='ced_ebay_category_not_mapped'>

												<b>Category Not Mapped</b>

											</span>

										</td>

												<?php

											}

											?>



			</tr>

			<tr class="ced_ebay_categories" id="<?php echo 'ced_ebay_categories_' . esc_attr( $value->term_id ); ?>">

									<td><p><b>eBay Site Primay Category</b></p></td>

									<td data-catlevel="1">

										<select class="ced_ebay_level1_category ced_ebay_select_category select2 ced_ebay_select2 select_boxes_cat_map" name="ced_ebay_level1_category[]" data-level=1 data-storeCategoryID="<?php echo esc_attr( $value->term_id ); ?>" data-ebayStoreId="<?php echo esc_attr( $user_id ); ?>">

											<option value=""></option>

											<?php

											foreach ( $ebay_categories['CategoryArray']['Category'] as $key1 => $value1 ) {

												if ( isset( $value1['CategoryName'] ) && '' != $value1['CategoryName'] ) {

													?>

		<option value="<?php echo esc_attr( $value1['CategoryID'] ); ?>"><?php echo esc_attr( $value1['CategoryName'] ); ?></option>



													<?php

												}
											}

											?>

										</select>

									</td>

								</tr>

								<tr class="ced_ebay_secondary_category" id="<?php echo 'ced_ebay_secondary_categories_' . esc_attr( $value->term_id ); ?>" style="display:none;">

									<td><p><b>eBay Site Secondary Category <span style="color:red;">(Extra eBay fees applies)</span></b></p></td>

									<td data-catlevel="1">

										<select class="ced_ebay_level1_secondary_category ced_ebay_select_secondary_category select2 ced_ebay_select2 select_boxes_cat_map" name="ced_ebay_level1_secondary_category[]" data-level-secondary=1 data-storeCategoryID-secondary="<?php echo esc_attr( $value->term_id ); ?>" data-ebayStoreId-secondary="<?php echo esc_attr( $user_id ); ?>">

											<option value=""></option>

											<?php

											foreach ( $ebay_categories['CategoryArray']['Category'] as $key1 => $value1 ) {

												if ( isset( $value1['CategoryName'] ) && '' != $value1['CategoryName'] ) {

													?>

		<option value="<?php echo esc_attr( $value1['CategoryID'] ); ?>"><?php echo esc_attr( $value1['CategoryName'] ); ?></option>



													<?php

												}
											}

											?>

										</select>

									</td>

								</tr>







								<!-- Primary store categories -->



								<tr class="ced_ebay_categories" id="<?php echo 'ced_ebay_store_custom_categories_' . esc_attr( $value->term_id ); ?>">

								<td><p><b>eBay Store Primary Category</b></p></td>

								<td data-catlevel="1">

											<?php

											if ( ! empty( get_option( 'ced_ebay_store_category_mapping_html_markup_' . $user_id ) ) ) {
														print_r( get_option( 'ced_ebay_store_category_mapping_html_markup_' . $user_id ) );
											} else {
												echo 'No Store Category Found!';
											}
											?>

									</td>

								</tr>



								<!-- Secondary store categories -->

								<tr class="ced_ebay_categories" id="<?php echo 'ced_ebay_store_secondary_categories_' . esc_attr( $value->term_id ); ?>">

								<td><p><b>eBay Store Secondary Category</b></p></td>

								<td data-catlevel="1">

											<?php

											if ( ! empty( get_option( 'ced_ebay_store_category_mapping_html_markup_' . $user_id ) ) ) {
														print_r( get_option( 'ced_ebay_store_category_mapping_html_markup_' . $user_id ) );
											} else {
												echo 'No Store Category Found!';
											}
											?>

									</td>

								</tr>



											<?php

											if ( isset( $value->child_categories[0] ) ) {
												nestdiv( $value->child_categories, $ebay_categories, ( $depth + 1 ) );
											}
										}

									}

									nestdiv( $woo_store_categories, $ebay_categories, 0 );

									?>





						</tbody>

						</table>

					</div>

					<div class="ced_ebay_category_mapping_header ced_ebay_hidden" id="ced_ebay_category_mapping_header">

						<a class="ced-ebay-v2-btn" style="background-color:red;" href="" data-ebayStoreID="<?php echo esc_attr( $user_id ); ?>" id="ced_ebay_cancel_category_button">

							<?php esc_attr_e( 'Cancel', 'ebay-integration-for-woocommerce' ); ?>

						</a>

						<button class="ced-ebay-v2-btn" data-ebayStoreID="<?php echo esc_attr( $user_id ); ?>" id="ced_ebay_save_category_button">

							<?php esc_attr_e( 'Save', 'ebay-integration-for-woocommerce' ); ?>

						</button>

					</div>

				</div>

				<?php

			} else {
				?>
<section class="woocommerce-inbox-message plain">
			<div class="woocommerce-inbox-message__wrapper">
				<div class="woocommerce-inbox-message__content">
					<h2 class="woocommerce-inbox-message__title">There are no category files found at the moment. Please click the Refresh Category button to fetch the categories from your eBay account.</h2>

				</div>
			</div>
		</section>
				<?php

			}

			?>



<article class="ced_ebay_faq">

  <div class="ced_ebay_faq_description">

	  <h1>Troubleshooting</h1>

  </div>

  <dl class="ced_ebay_faq_collection">

  <dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">

		How can I map the categories?<i class="fa fa-chevron-down"></i></dt>

	<dd class="ced_ebay_faq_collection_answer faq-answer-js">

	  <p>To begin mapping store categories, click on the 'Refresh Category' button and the plugin will fetch the categories corresponding to your eBay Store Region then

	   click the checkbox corresponding to each category and a dropdown will appear.

	  From the dropdown, select the relevant category and continue this untill you have reached the last category.

	  Make sure you <span style="color:red;">go to the last category</span>. After you have finished the mapping, profiles will be

	  automatically created for you in the Profile Section. You can map multiple WooCommerce categories to the same eBay category.</p>

	</dd>

	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">

		I can't see any categories. What is the issue?<i class="fa fa-chevron-down"></i></dt>

	<dd class="ced_ebay_faq_collection_answer faq-answer-js">

	  <p>If you don't see any categories in this section its because you haven't created any categories in your WooCommerce.

	  This section will map your WooCommerce Store Categories to the eBay category for your region. Mapping store categories

	  to eBay categories is <span style="color:red;"> mandatory</span> to be able to upload products to eBay.</p>

	</dd>

	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">

		How can I delete a category mapping?<i class="fa fa-chevron-down"></i></dt>

	<dd class="ced_ebay_faq_collection_answer faq-answer-js">

	  <p>To delete a category mapping, delete its corresponding profile.</p>

	</dd>

	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">

		I can only see some of my WooCommerce categories and not all?<i class="fa fa-chevron-down"></i></dt>

	<dd class="ced_ebay_faq_collection_answer faq-answer-js">

	  <p>Our plugin picks up only those parent categories or parent and child which have products listed under them.

	  Also, we only support categories created via WooCommerce and not some 3rd-party plugin. If you are using some 3rd-party plugin please contact us to discuss the best approach moving forward.</p>

	</dd>

	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">

		I can't fetch eBay Categories?<i class="fa fa-chevron-down"></i></dt>

	<dd class="ced_ebay_faq_collection_answer faq-answer-js">

	  <p>Sometimes it may happen that we can't detect your correct eBay store region. If this happens, our plugin will not be able to fetch the eBay Categories.

	  This usually happens if you have selected the wrong marketplace location while adding your eBay account. To rectify this please get in touch with us via the below mentioned support links.</p>

	</dd>

	<dt class="ced_ebay_faq_collection_question toggle-faq-js">

	<h3>If you are facing any other sort of issues with our plugin, please reach us at support@cedcommerce.com or you can join our <a style="text-decoration: underline;" href="https://join.skype.com/UHRP45eJN8qQ">Skype</a> or <a style="text-decoration: underline;" href="https://chat.whatsapp.com/BcJ2QnysUVmB1S2wmwBSnE">WhatsApp</a> group.</h3>



	</dt>

  </dl>

</article>
