<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( empty( get_option( 'ced_ebay_user_access_token' ) ) ) {
	wp_redirect( get_admin_url() . 'admin.php?page=ced_ebay' );
}

$fileHeader   = CED_EBAY_DIRPATH . 'admin/partials/header.php';
$fileCategory = CED_EBAY_DIRPATH . 'admin/ebay/lib/cedGetcategories.php';
$fileFields   = CED_EBAY_DIRPATH . 'admin/partials/products_fields.php';


if ( file_exists( $fileHeader ) ) {
	require_once $fileHeader;
}
if ( file_exists( $fileCategory ) ) {
	require_once $fileCategory;
}
if ( file_exists( $fileFields ) ) {
	require_once $fileFields;
}
$user_id       = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
$wp_folder     = wp_upload_dir();
$wp_upload_dir = $wp_folder['basedir'];
$wp_upload_dir = $wp_upload_dir . '/ced-ebay/category-specifics/' . $user_id . '/';
if ( ! is_dir( $wp_upload_dir ) ) {
	wp_mkdir_p( $wp_upload_dir, 0777 );
}
$shop_data = ced_ebay_get_shop_data( $user_id );
if ( ! empty( $shop_data ) ) {
	$siteID      = $shop_data['site_id'];
	$token       = $shop_data['access_token'];
	$getLocation = $shop_data['location'];
}
$isProfileSaved = false;
$profileID      = isset( $_GET['profileID'] ) ? sanitize_text_field( $_GET['profileID'] )
	: '';
if ( isset( $_POST['ced_ebay_profile_edit'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ced_ebay_profile_edit'] ), 'ced_ebay_profile_edit_page_nonce' ) ) {
	if ( isset( $_POST['add_meta_keys'] ) || isset( $_POST['ced_ebay_profile_save_button'] ) ) {

		global $wpdb;

		$tableName = $wpdb->prefix . 'ced_ebay_profiles';

		$profile_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id`=%d", $profileID ), 'ARRAY_A' );
		if ( ! empty( $profile_data ) ) {
			$profile_category_data = json_decode( $profile_data[0]['profile_data'], true );
		}
		$profile_category_data = isset( $profile_category_data ) ? $profile_category_data : '';
		$profile_category_id   = isset( $profile_category_data['_umb_ebay_category']['default'] ) ? $profile_category_data['_umb_ebay_category']['default'] : '';
		$sanitized_array       = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$is_active             = isset( $sanitized_array['profile_status'] ) ? 'Active' : 'Inactive';
		$marketplaceName       = isset( $sanitized_array['marketplaceName'] ) ? ( $sanitized_array['marketplaceName'] ) : 'all';
		$updateinfo            = array();
		$common                = isset( $sanitized_array['ced_ebay_required_common'] ) ? ( $sanitized_array['ced_ebay_required_common'] ) : array();
		foreach ( $common as $key ) {
			$arrayToSave = array();
			if ( false !== strpos( $key, '_required' ) ) {
				$position                            = strpos( $key, '_required' );
				$key                                 = substr( $key, 0, $position );
				$sanitized_array[ $key ]['required'] = true;
				$arrayToSave['required']             = $sanitized_array[ $key ]['required'];
			}
			isset( $sanitized_array[ $key ][0] ) ? $arrayToSave['default'] = ( $sanitized_array[ $key ][0] ) : $arrayToSave['default'] = '';

			if ( '_umb_' . $marketplaceName . '_subcategory' == $key ) {

				isset( $sanitized_array[ $key ] ) ? $arrayToSave['default'] = ( $sanitized_array[ $key ] )
				: $arrayToSave['default']                                   = '';
			}
			if ( '_umb_ebay_category' == $key && '' == $profileID ) {
				$category_id = isset( $sanitized_array['ced_ebay_level3_category'] ) ? ( $sanitized_array['ced_ebay_level3_category'] )
				: '';
				$category_id = isset( $category_id[0] ) ? $category_id[0] : '';
				isset( $sanitized_array[ $key ][0] ) ? $arrayToSave['default'] = $category_id : $arrayToSave['default'] = '';
			}
			isset( $sanitized_array[ $key . '_attibuteMeta' ] ) ? $arrayToSave['metakey'] = ( $sanitized_array[ $key . '_attibuteMeta' ] )
			: $arrayToSave['metakey'] = 'null';
			$updateinfo[ $key ]       = $arrayToSave;
		}
		$updateinfo['selected_product_id']           = isset( $sanitized_array['selected_product_id'] ) ? ( $sanitized_array['selected_product_id'] )
		: '';
		$updateinfo['selected_product_name']         = isset( $sanitized_array['ced_sears_pro_search_box'] ) ? ( $sanitized_array['ced_sears_pro_search_box'] )
		: '';
		$updateinfo['_umb_ebay_category']['default'] = $profile_category_id;
		$updateinfo                                  = json_encode( $updateinfo );
		if ( '' == $profileID ) {
			$ebay_categories_name = get_option( 'ced_ebay_categories' . $user_id, array() );
			$categoryIDs          = array();
			for ( $i = 1; $i <= 6; $i++ ) {
				$categoryIDs[] = isset( $sanitized_array[ 'ced_ebay_level' . $i . '_category' ] ) ? ( ( $sanitized_array[ 'ced_ebay_level' . $i . '_category' ] ) )
				: '';
			}
			$categoryName = '';
			foreach ( $categoryIDs as $index => $categoryId ) {
				foreach ( $ebay_categories_name['categories'] as $key => $value ) {
					if ( isset( $categoryId[0] ) && ! empty( $categoryId[0] ) && $categoryId[0] == $value['category_id'] ) {
						$categoryName    .= $value['category_name'] . ' --> ';
						$ebay_category_id = $value['category_id'];
					}
				}
			}
			$profile_name   = substr( $categoryName, 0, -5 );
			$profileDetails = array(
				'profile_name'   => $profile_name,
				'profile_status' => 'active',
				'profile_data'   => $updateinfo,
				'user_id'        => $user_id,
			);
			global $wpdb;
			$profileTableName = $wpdb->prefix . 'ced_ebay_profiles';

			$wpdb->insert( $profileTableName, $profileDetails );
			$profileId          = $wpdb->insert_id;
			$cat_specifics_file = $wp_upload_dir . 'ebaycat_' . $profile_category_id . '.json';
			if ( file_exists( $cat_specifics_file ) ) {
				$available_attribute = json_decode( file_get_contents( $cat_specifics_file ), true );
			}
			if ( ! is_array( $available_attribute ) ) {
				$available_attribute = array();
			}

			if ( ! empty( $available_attribute ) ) {
				$categoryAttributes = $available_attribute;
			} else {
				$ebayCategoryInstance    = CedGetCategories::get_instance( $siteID, $token );
				$categoryAttributes      = $ebayCategoryInstance->_getCatSpecifics( $profile_category_id );
				$categoryAttributes_json = json_encode( $categoryAttributes );
				$cat_specifics_file      = $wp_upload_dir . 'ebaycat_' . $profile_category_id . '.json';
				if ( file_exists( $cat_specifics_file ) ) {
					wp_delete_file( $cat_specifics_file );
				}
				file_put_contents( $cat_specifics_file, $categoryAttributes_json );
			}
			$ebayCategoryInstance = CedGetCategories::get_instance( $siteID, $token );
			$getCatFeatures       = $cedCatInstance->_getCatFeatures( $catID, $limit );
			$getCatFeatures       = isset( $getCatFeatures['Category'] ) ? $getCatFeatures['Category'] : false;
			$profile_edit_url     = admin_url( 'admin.php?page=ced_ebay&section=profiles-view&user_id=' . $user_id . '&profileID=' . $profileId . '&panel=edit' );
			header( 'location:' . $profile_edit_url . '' );
		} elseif ( $profileID ) {
			global $wpdb;
			$tableName = $wpdb->prefix . 'ced_ebay_profiles';
			$wpdb->update(
				$tableName,
				array(
					'profile_status' => $is_active,
					'profile_data'   => $updateinfo,
				),
				array( 'id' => $profileID )
			);

		}

		if ( '' === $wpdb->last_error ) {
			$isProfileSaved = true;
		}
	}
}

global $wpdb;

$tableName = $wpdb->prefix . 'ced_ebay_profiles';

$profile_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id`=%d", $profileID ), 'ARRAY_A' );
if ( ! empty( $profile_data ) ) {
	$profile_category_data = json_decode( $profile_data[0]['profile_data'], true );
}
$profile_category_data = isset( $profile_category_data ) ? $profile_category_data : '';
$profile_category_id   = isset( $profile_category_data['_umb_ebay_category']['default'] ) ? $profile_category_data['_umb_ebay_category']['default'] : '';
$profile_data          = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
$attributes            = wc_get_attribute_taxonomies();
$attrOptions           = array();
$addedMetaKeys         = get_option( 'CedUmbProfileSelectedMetaKeys', false );
$selectDropdownHTML    = '';

global $wpdb;
$results = $wpdb->get_results( "SELECT DISTINCT meta_key FROM {$wpdb->prefix}postmeta WHERE meta_key NOT LIKE '%wcf%' AND meta_key NOT LIKE '%elementor%' AND meta_key NOT LIKE '%_menu%'", 'ARRAY_A' );
foreach ( $results as $key => $meta_key ) {
	$post_meta_keys[] = $meta_key['meta_key'];
}
$custom_prd_attrb = array();
$query            = $wpdb->get_results( $wpdb->prepare( "SELECT `meta_value` FROM  {$wpdb->prefix}postmeta WHERE `meta_key` LIKE %s", '_product_attributes' ), 'ARRAY_A' );
if ( ! empty( $query ) ) {
	foreach ( $query as $key => $db_attribute_pair ) {
		foreach ( maybe_unserialize( $db_attribute_pair['meta_value'] ) as $key => $attribute_pair ) {
			if ( 1 != $attribute_pair['is_taxonomy'] ) {
				$custom_prd_attrb[] = $attribute_pair['name'];
			}
		}
	}
}
if ( $addedMetaKeys && count( $addedMetaKeys ) > 0 ) {
	foreach ( $addedMetaKeys as $metaKey ) {
		$attrOptions[ $metaKey ] = $metaKey;
	}
}
if ( ! empty( $attributes ) ) {
	foreach ( $attributes as $attributesObject ) {
		$attrOptions[ 'umb_pattr_' . $attributesObject->attribute_name ] = $attributesObject->attribute_label;
	}
}
/* select dropdown setup */
ob_start();
$fieldID             = '{{*fieldID}}';
$selectId            = $fieldID . '_attibuteMeta';
$selectDropdownHTML .= '<label>Get value from</lable>';
$selectDropdownHTML .= '<select class="ced_ebay_search_item_sepcifics_mapping" id="' . $selectId . '" name="' . $selectId . '">';
$selectDropdownHTML .= '<option value="null"> -- select -- </option>';
$selectDropdownHTML .= '<option value="ced_product_tags">Product Tags</option>';

if ( class_exists( 'ACF' ) ) {
	$acf_fields_posts = get_posts(
		array(
			'posts_per_page' => -1,
			'post_type'      => 'acf-field',
		)
	);

	foreach ( $acf_fields_posts as $key => $acf_posts ) {
		$acf_fields[ $key ]['field_name'] = $acf_posts->post_title;
		$acf_fields[ $key ]['field_key']  = $acf_posts->post_name;
	}
}
if ( is_array( $attrOptions ) ) {
	$selectDropdownHTML .= '<optgroup label="Global Attributes">';
	foreach ( $attrOptions as $attrKey => $attrName ) :
		$selectDropdownHTML .= '<option value="' . $attrKey . '">' . $attrName . '</option>';
	endforeach;
}

if ( ! empty( $custom_prd_attrb ) ) {
	$custom_prd_attrb    = array_unique( $custom_prd_attrb );
	$selectDropdownHTML .= '<optgroup label="Custom Attributes">';
	foreach ( $custom_prd_attrb as $key => $custom_attrb ) {
		$selectDropdownHTML .= '<option value="ced_cstm_attrb_' . esc_attr( $custom_attrb ) . '">' . esc_html( $custom_attrb ) . '</option>';
	}
}

if ( ! empty( $post_meta_keys ) ) {
	$post_meta_keys      = array_unique( $post_meta_keys );
	$selectDropdownHTML .= '<optgroup label="Custom Fields">';
	foreach ( $post_meta_keys as $key => $p_meta_key ) {
		$selectDropdownHTML .= '<option value="' . $p_meta_key . '">' . $p_meta_key . '</option>';
	}
}

if ( ! empty( $acf_fields ) ) {
	$selectDropdownHTML .= '<optgroup label="ACF Fields">';
	foreach ( $acf_fields as $key => $acf_field ) :
		$selectDropdownHTML .= '<option value="acf_' . $acf_field['field_key'] . '">' . $acf_field['field_name'] . '</option>';
	endforeach;
}
$selectDropdownHTML .= '</select>';
$cat_specifics_file  = $wp_upload_dir . 'ebaycat_' . $profile_category_id . '.json';
if ( file_exists( $cat_specifics_file ) ) {
	$available_attribute = json_decode( file_get_contents( $cat_specifics_file ), true );
}
if ( ! is_array( $available_attribute ) ) {
	$available_attribute = array();
}
if ( ! empty( $available_attribute ) ) {
	$categoryAttributes = $available_attribute;
} else {
	$ebayCategoryInstance    = CedGetCategories::get_instance( $siteID, $token );
	$categoryAttributes      = $ebayCategoryInstance->_getCatSpecifics( $profile_category_id );
	$categoryAttributes_json = json_encode( $categoryAttributes );
	$cat_specifics_file      = $wp_upload_dir . 'ebaycat_' . $profile_category_id . '.json';
	if ( file_exists( $cat_specifics_file ) ) {
		wp_delete_file( $cat_specifics_file );
	}
	file_put_contents( $cat_specifics_file, $categoryAttributes_json );
}
$ebayCategoryInstance = CedGetCategories::get_instance( $siteID, $token );
$limit                = array( 'ConditionEnabled', 'ConditionValues' );
$getCatFeatures       = $ebayCategoryInstance->_getCatFeatures( $profile_category_id, array() );
$getCatFeatures       = isset( $getCatFeatures['Category'] ) ? $getCatFeatures['Category'] : false;
$attribute_data       = array();


$productFieldInstance = CedeBayProductsFields::get_instance();
$fields               = $productFieldInstance->ced_ebay_get_custom_products_fields( $profile_category_id );


$woo_store_categories = get_terms( 'product_cat' );
$user_id              = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';


?>
<form action="" method="post">
	<div class="ced_ebay_profile_details_wrapper">
		<div class="ced_ebay_profile_details_fields">
			<table>
				<thead>
				<div class="ced-ebay-v2-header">
			<div class="ced-ebay-v2-logo">
			<img src="<?php echo esc_attr( CED_EBAY_URL ) . 'admin/images/icon-100X100.png'; ?>">
			</div>
			<div class="ced-ebay-v2-header-content">
				<div class="ced-ebay-v2-title">

					<h2 style="font-size:18px;"><b><?php echo esc_attr_e( $profile_data['profile_name'] ); ?></h2>
				</div>
				<div class="ced-ebay-v2-actions">
				<a class="ced-ebay-v2-btn" href="<?php echo esc_attr( admin_url( 'admin.php?page=ced_ebay&section=profiles-view&user_id=' . $user_id ) ); ?>">
					Go Back					</a>
				<a class="ced-ebay-v2-btn" href="https://docs.woocommerce.com/document/ebay-integration-for-woocommerce/#section-8" target="_blank">
					Documentation					</a>

			</div>
		</div>
</div>
<?php
if ( $isProfileSaved ) {
	?>
<div
		class="bg-green-200 px-6 py-4 mx-2 my-4 rounded-md text-lg flex items-center  w-3/4 xl:w-2/4"
		>

	 <span class="text-green-800"> We’ve saved your profile data. </span>
   </div>
   <!-- End Alert Success -->


   </div>
   </div>
	<?php
}
?>
				</thead>
				<tbody>

<th colspan="3" class="px-4 mt-4 py-6 sm:p-6 border-t-2 border-green-500" style="text-align:left;margin:0;">

<label style="font-size: 1.25rem;color: #6574cd;" ><?php esc_attr_e( 'GENERAL DETAILS', 'ebay-integration-for-woocommerce' ); ?></label>

</th>
				<tr class="">
					<?php
					$requiredInAnyCase = array( '_umb_id_type', '_umb_id_val', '_umb_brand' );
					global $global_CED_ebay_Render_Attributes;
					$marketPlace        = 'ced_ebay_required_common';
					$productID          = 0;
					$categoryID         = '';
					$indexToUse         = 0;
					$selectDropdownHTML = $selectDropdownHTML;
					?>


						<?php
						if ( ! empty( $profile_data ) ) {
							$data = json_decode( $profile_data['profile_data'], true );
						}
						foreach ( $fields as $value ) {
							$isText   = false;
							$field_id = trim( $value['fields']['id'], '_' );
							if ( in_array( $value['fields']['id'], $requiredInAnyCase ) ) {
								$attributeNameToRender  = ucfirst( $value['fields']['label'] );
								$attributeNameToRender .= '<span class="ced_ebay_wal_required">' . __( '[ Required ]', 'ebay-integration-for-woocommerce' ) . '</span>';
							} else {
								$attributeNameToRender = ucfirst( $value['fields']['label'] );
							}
							$default = isset( $data[ $value['fields']['id'] ]['default'] ) ? $data[ $value['fields']['id'] ]['default'] : '';
							echo '<tr class="form-field _umb_id_type_field ">';

							if ( '_select' == $value['type'] ) {
								$valueForDropdown = $value['fields']['options'];
								if ( '_umb_id_type' == $value['fields']['id'] ) {
									unset( $valueForDropdown['null'] );
								}
								$productFieldInstance->renderDropdownHTML(
									$field_id,
									$attributeNameToRender,
									$valueForDropdown,
									$categoryID,
									$productID,
									$marketPlace,
									$indexToUse,
									array(
										'case'  => 'profile',
										'value' => $default,
									)
								);
								$isText = false;
							} elseif ( '_text_input' == $value['type'] ) {
								$productFieldInstance->renderInputTextHTML(
									$field_id,
									$attributeNameToRender,
									$categoryID,
									$productID,
									$marketPlace,
									$indexToUse,
									array(
										'case'  => 'profile',
										'value' => $default,
									)
								);
								$isText = true;
							} elseif ( '_hidden' == $value['type'] ) {
								$productFieldInstance->renderInputTextHTMLhidden(
									$field_id,
									$attributeNameToRender,
									$categoryID,
									$productID,
									$marketPlace,
									$indexToUse,
									array(
										'case'  => 'profile',
										'value' => $profile_category_id,
									)
								);
								$isText = false;
							} else {
								$isText = false;
							}

							echo '<td>';
							if ( $isText ) {
								$previousSelectedValue = 'null';
								if ( isset( $data[ $value['fields']['id'] ]['metakey'] ) && 'null' != $data[ $value['fields']['id'] ]['metakey'] ) {
									$previousSelectedValue = $data[ $value['fields']['id'] ]['metakey'];
								}
								$updatedDropdownHTML = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
								$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
								print_r( $updatedDropdownHTML );
							}
							echo '</td>';
							echo '</tr>';
						}
						?>
						<th colspan="3" class="px-4 mt-4 py-6 sm:p-6 border-t-2 border-green-500" style="text-align:left;margin:0;">

<label style="font-size: 1.25rem;color: #6574cd;" ><?php esc_attr_e( 'ITEM ASPECTS', 'ebay-integration-for-woocommerce' ); ?></label>
<p style="font-size:16px;text-style:none;">Specify additional details about products listed on eBay based on the selected eBay Category.
You can only set the values of <span style="color:red;">[Required]</span> Item Aspects and leave other fields in this section.
To do so, either enter a custom value or get the values from Product Attributes or Custom Fields.</p>
</th>



						<?php
						if ( ! empty( $categoryAttributes ) ) {
							if ( ! empty( $profile_data ) ) {
								$data = json_decode( $profile_data['profile_data'], true );
							}
							foreach ( $categoryAttributes as $key1 => $value ) {
								$isText   = true;
								$field_id = trim( urlencode( $value['localizedAspectName'] ) );
								$default  = isset( $data[ $profile_category_id . '_' . $field_id ] ) ? $data[ $profile_category_id . '_' . $field_id ] : '';
								$default  = isset( $default['default'] ) ? $default['default'] : '';
								$required = '';
								echo '<tr class="form-field _umb_brand_field ">';

								if ( 'SELECTION_ONLY' == $value['aspectConstraint']['aspectMode'] ) {
									$valueForDropdown     = $value['aspectValues'];
									$tempValueForDropdown = array();
									foreach ( $valueForDropdown as $key => $_value ) {
										$tempValueForDropdown[ $_value['localizedValue'] ] = $_value['localizedValue'];
									}
									$valueForDropdown = $tempValueForDropdown;

									if ( 'true' == $value['aspectConstraint']['aspectRequired'] ) {
										$required = 'required';
									}

									$productFieldInstance->renderDropdownHTML(
										$field_id,
										ucfirst( $value['localizedAspectName'] ),
										$valueForDropdown,
										$profile_category_id,
										$productID,
										$marketPlace,
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$required
									);
									$isText = false;
								} elseif ( 'COMBO_BOX' == isset( $value['input_type'] ) ? $value['input_type'] : '' ) {
									$isText = true;
									if ( 'true' == $value['aspectConstraint']['aspectRequired'] ) {
										$required = 'required';
									}
									$productFieldInstance->renderInputTextHTML(
										$field_id,
										ucfirst( $value['localizedAspectName'] ),
										$profile_category_id,
										$productID,
										$marketPlace,
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$required
									);
								} elseif ( 'text' == isset( $value['input_type'] ) ? $value['input_type'] : '' ) {
									$isText = true;
									if ( 'true' == $value['aspectConstraint']['aspectRequired'] ) {
										$required = 'required';
									}
									$productFieldInstance->renderInputTextHTML(
										$field_id,
										ucfirst( $value['localizedAspectName'] ),
										$profile_category_id,
										$productID,
										$marketPlace,
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$required
									);
								} else {
									$isText = true;
									if ( 'true' == $value['aspectConstraint']['aspectRequired'] ) {
										$required = 'required';
									}
									$productFieldInstance->renderInputTextHTML(
										$field_id,
										ucfirst( $value['localizedAspectName'] ),
										$profile_category_id,
										$productID,
										$marketPlace,
										$indexToUse,
										array(
											'case'  => 'profile',
											'value' => $default,
										),
										$required
									);
								}


								echo '<td>';
								if ( $isText ) {
									$previousSelectedValue = 'null';
									if ( isset( $data[ $profile_category_id . '_' . $field_id ] ) && 'null' != $data[ $profile_category_id . '_' . $field_id ] ) {

										$previousSelectedValue = $data[ $profile_category_id . '_' . $field_id ]['metakey'];
									}
									$updatedDropdownHTML = str_replace( '{{*fieldID}}', $profile_category_id . '_' . $field_id, $selectDropdownHTML );
									$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
									print_r( $updatedDropdownHTML );
								}
								echo '</td>';


								echo '</tr>';
							}
						}
						if ( $getCatFeatures ) {
							if ( isset( $getCatFeatures['ConditionValues'] ) ) {
								$valueForDropdown     = $getCatFeatures['ConditionValues']['Condition'];
								$tempValueForDropdown = array();
								if ( isset( $valueForDropdown[0] ) ) {
									foreach ( $valueForDropdown as $key => $value ) {
										$tempValueForDropdown[ $value['ID'] ] = $value['DisplayName'];
									}
								} else {
									$tempValueForDropdown[ $valueForDropdown['ID'] ] = $valueForDropdown['DisplayName'];
								}
								$valueForDropdown = $tempValueForDropdown;
								$name             = 'Condition';
								$default          = isset( $profile_category_data[ $profile_category_id . '_' . $name ] ) ? $profile_category_data[ $profile_category_id . '_' . $name ] : '';
								$default          = isset( $default['default'] ) ? $default['default'] : '';
								if ( 'Required' == $getCatFeatures['ConditionEnabled'] ) {
									$catFeatureSavingForvalidation[ $categoryID ][] = 'Condition';
								}
								$productFieldInstance->renderDropdownHTML(
									'Condition',
									$name,
									$valueForDropdown,
									$profile_category_id,
									$productID,
									$marketPlace,
									$indexToUse,
									array(
										'case'  => 'profile',
										'value' => $default,
									),
									'required'
								);
							}
						}
						?>

						<tr>
						<th colspan="3" class="px-4 mt-4 py-6 sm:p-6 border-t-2 border-green-500" style="text-align:left;margin:0;">

<label style="font-size: 1.25rem;color: #6574cd;" ><?php esc_attr_e( 'FRAMEWORK SPECIFIC', 'ebay-integration-for-woocommerce' ); ?></label>
<p style="font-size:16px;">Specify additional details about products listed on eBay based on the selected eBay Category.
You can only set the values of <span style="color:red;">[Required]</span> Item Aspects and leave other fields in this section.
To do so, either enter a custom value or get the values from Product Attributes or Custom Fields.</p>
</th>
						</tr>
						<?php
						if ( ! empty( $profile_data ) ) {
							$data = json_decode( $profile_data['profile_data'], true );
						}
						$productFieldInstance = CedeBayProductsFields::get_instance();
						$fields               = $productFieldInstance->ced_ebay_get_profile_framework_specific( $profile_category_id );

						foreach ( $fields as $value ) {
							$isText   = false;
							$field_id = trim( $value['fields']['id'], '_' );
							if ( in_array( $value['fields']['id'], $requiredInAnyCase ) ) {
								$attributeNameToRender  = ucfirst( $value['fields']['label'] );
								$attributeNameToRender .= '<span class="ced_ebay_wal_required">' . __( '[ Required ]', 'ebay-integration-for-woocommerce' ) . '</span>';
							} else {
								$attributeNameToRender = ucfirst( $value['fields']['label'] );
							}
							$default  = isset( $data[ $value['fields']['id'] ]['default'] ) ? $data[ $value['fields']['id'] ]['default'] : '';
							$required = isset( $value['required'] ) ? $value['required'] : '';
							echo '<tr class="form-field _umb_id_type_field ">';

							if ( '_select' == $value['type'] ) {
								$valueForDropdown = $value['fields']['options'];
								if ( '_umb_id_type' == $value['fields']['id'] ) {
									unset( $valueForDropdown['null'] );
								}
								$productFieldInstance->renderDropdownHTML(
									$field_id,
									$attributeNameToRender,
									$valueForDropdown,
									$categoryID,
									$productID,
									$marketPlace,
									$indexToUse,
									array(
										'case'  => 'profile',
										'value' => $default,
									),
									$required
								);
								$isText = false;
							} elseif ( '_text_input' == $value['type'] ) {
								$productFieldInstance->renderInputTextHTML(
									$field_id,
									$attributeNameToRender,
									$categoryID,
									$productID,
									$marketPlace,
									$indexToUse,
									array(
										'case'  => 'profile',
										'value' => $default,
									),
									$required
								);
								$isText = true;
							} elseif ( '_hidden' == $value['type'] ) {
								$productFieldInstance->renderInputTextHTMLhidden(
									$field_id,
									$attributeNameToRender,
									$categoryID,
									$productID,
									$marketPlace,
									$indexToUse,
									array(
										'case'  => 'profile',
										'value' => $profile_category_id,
									)
								);
								$isText = false;
							} else {
								$isText = false;
							}

							echo '<td>';
							if ( $isText ) {
								$previousSelectedValue = 'null';
								if ( isset( $data[ $value['fields']['id'] ]['metakey'] ) && 'null' != $data[ $value['fields']['id'] ]['metakey'] ) {
									$previousSelectedValue = $data[ $value['fields']['id'] ]['metakey'];
								}
								$updatedDropdownHTML = str_replace( '{{*fieldID}}', $value['fields']['id'], $selectDropdownHTML );
								$updatedDropdownHTML = str_replace( 'value="' . $previousSelectedValue . '"', 'value="' . $previousSelectedValue . '" selected="selected"', $updatedDropdownHTML );
								print_r( $updatedDropdownHTML );
							}
							echo '</td>';
							echo '</tr>';
						}
						?>
					</tr>
				</tbody>

			</table>
			<button style="margin:50px 5px;" class="ced-ebay-v2-btn" name="ced_ebay_profile_save_button" ><?php esc_attr_e( 'Save Profile Data', 'ebay-integration-for-woocommerce' ); ?></button>

		</div>
	</div>
	<?php wp_nonce_field( 'ced_ebay_profile_edit_page_nonce', 'ced_ebay_profile_edit' ); ?>
</form>

<script>
	jQuery(document).ready(function() {
	jQuery(".ced_ebay_search_item_sepcifics_mapping").select2();
});
</script>


