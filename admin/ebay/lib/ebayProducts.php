<?php

class Class_Ced_EBay_Products {


	public static $_instance;

	/**
	 * Ced_EBay_Config Instance.
	 * Ensures only one instance of Ced_EBay_Config is loaded or can be loaded.

	 * @since 1.0.0
	 * @static
	 */
	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
	}

	/*
	 *
	 *function for preparing product data to be uploaded
	 *
	 *
	 */

	public function ced_ebay_prepareDataForUploading( $proIDs = array(), $userId = '' ) {
		foreach ( $proIDs as $key => $value ) {
			$prod_data        = wc_get_product( $value );
			$type             = $prod_data->get_type();
			$already_uploaded = get_post_meta( $value, '_ced_ebay_listing_id_' . $userId, true );
			$preparedData     = $this->getFormattedData( $value, $userId );
			if ( 'No Profile Assigned' == $preparedData ) {
				return $preparedData;
			}
			return $preparedData;
		}
	}
	/*
	 *
	 *function for preparing product data to be updated
	 *
	 *
	 */
	public function ced_ebay_prepareDataForUpdating( $userId, $proIDs = array() ) {
		foreach ( $proIDs as $key => $value ) {
			$prod_data        = wc_get_product( $value );
			$type             = $prod_data->get_type();
			$item_id          = get_post_meta( $value, '_ced_ebay_listing_id_' . $userId, true );
			$already_uploaded = get_post_meta( $value, '_ced_ebay_listing_id_' . $userId, true );
			$preparedData     = $this->getFormattedData( $value, $userId, $item_id );
			return $preparedData;
		}
	}
	/*
	 *
	 *function for getting stock of products to be updated
	 *
	 *
	 */
	public function ced_ebay_prepareDataForUpdatingStock( $userId, $_to_update_productIds = array(), $notAjax = false ) {
		if ( empty( $_to_update_productIds ) ) {
			return 'Empty Product Ids';
		}

		$shop_data = ced_ebay_get_shop_data( $userId );
		if ( ! empty( $shop_data ) ) {
			$siteID          = $shop_data['site_id'];
				$token       = $shop_data['access_token'];
				$getLocation = $shop_data['location'];
		}
		$reviseInventoryXml = '<?xml version="1.0" encoding="utf-8"?>
			<ReviseInventoryStatusRequest xmlns="urn:ebay:apis:eBLBaseComponents">
				<RequesterCredentials>
					<eBayAuthToken>' . $token . '</eBayAuthToken>
				</RequesterCredentials>
				<Version>1267</Version>
				<WarningLevel>High</WarningLevel>
				<CedInventoryStatus>ced</CedInventoryStatus>
			</ReviseInventoryStatusRequest>';

		$CedInventoryStatusXml = '';

		foreach ( $_to_update_productIds as $productId => $itemId ) {
			$profileData  = $this->ced_ebay_getProfileAssignedData( $productId, $userId );
			$product      = wc_get_product( $productId );
			$stock_status = get_post_meta( $productId, '_stock_status', true );
			if ( get_option( 'ced_ebay_global_settings', false ) ) {
				$dataInGlobalSettings = get_option( 'ced_ebay_global_settings', false );
				$price_markup_type    = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup_type'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup_type'] : '';
				$price_markup_value   = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup'] : '';
				$price_sync           = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_sync_price'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_sync_price'] : '';
			}

			$dataInGlobalSettings = ! empty( get_option( 'ced_ebay_global_settings', false ) ) ? get_option( 'ced_ebay_global_settings', false ) : '';
			$price_selection      = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_price_option'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_price_option'] : '';

			if ( 'Regular_Price' == $price_selection ) {
				$price = $product->get_regular_price();
			} elseif ( 'Sale_Price' == $price_selection ) {
				$price = $product->get_sale_price();
			} else {
				$price = $product->get_price();
			}
			$profile_price_markup_type = $this->fetchMetaValueOfProduct( $productId, '_umb_ebay_profile_price_markup_type' );
			$profile_price_markup      = $this->fetchMetaValueOfProduct( $productId, '_umb_ebay_profile_price_markup' );
			if ( ! empty( $profile_price_markup_type ) && ! empty( $profile_price_markup ) ) {
				if ( 'Fixed_Increase' == $profile_price_markup_type ) {
					$price = $price + $profile_price_markup;
				} elseif ( 'Percentage_Increase' == $profile_price_markup_type ) {
					$price = $price + ( ( $price * $profile_price_markup ) / 100 );
				} elseif ( 'Percentage_Decrease' == $profile_price_markup_type ) {
					$price = $price - ( ( $price * $profile_price_markup ) / 100 );
				} elseif ( 'Fixed_Decrease' == $profile_price_markup_type ) {
					$price = $price - $profile_price_markup;
				}
			}

			if ( 'Fixed_Increased' == $price_markup_type ) {
				$price = $price + $price_markup_value;
			} elseif ( 'Percentage_Increased' == $price_markup_type ) {
				$price = $price + ( ( $price * $price_markup_value ) / 100 );
			} elseif ( 'Percentage_Decreased' == $price_markup_type ) {
				$price = $price - ( ( $price * $price_markup_value ) / 100 );
			} elseif ( 'Fixed_Decreased' == $price_markup_type ) {
				$price = $price - $price_markup_value;
			}
			$renderDataOnGlobalSettings = get_option( 'ced_ebay_global_settings', false );
			$manage_stock               = get_post_meta( $productId, '_manage_stock', true );
			if ( 'yes' != $manage_stock && 'instock' == $stock_status ) {
				$listing_stock_type = isset( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_product_stock_type'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_product_stock_type'] : '';
				$listing_stock      = isset( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_listing_stock'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_listing_stock'] : '';
				if ( ! empty( $listing_stock_type ) && ! empty( $listing_stock ) && 'MaxStock' == $listing_stock_type ) {
					$quantity = $listing_stock;
				} else {
					$quantity = 1;
				}
			} else {
				if ( 'outofstock' != $stock_status ) {
					$quantity           = get_post_meta( $productId, '_stock', true );
					$listing_stock_type = isset( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_product_stock_type'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_product_stock_type'] : '';
					$listing_stock      = isset( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_listing_stock'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_listing_stock'] : '';
					if ( ! empty( $listing_stock_type ) && ! empty( $listing_stock ) && 'MaxStock' == $listing_stock_type ) {
						if ( $quantity > $listing_stock ) {
							$quantity = $listing_stock;
						} else {
							$quantity = intval( $quantity );
							if ( $quantity < 1 ) {
								$quantity = '0';
							}
						}
					} else {
						$quantity = intval( $quantity );
						if ( $quantity < 1 ) {
							$quantity = '0';
						}
					}
				} else {
					$quantity = 0;
				}
			}

			if ( 'On' == $price_sync ) {
				if ( $product->is_type( 'variation' ) ) {
					$sku = get_post_meta( $productId, '_sku', true );
					if ( empty( $sku ) ) {
						$sku = $productId;
					}
					$CedInventoryStatusXml .= '<InventoryStatus>
						<ItemID>' . $itemId . '</ItemID>
						<SKU>' . str_replace( '&', '&amp;', $sku ) . '</SKU>
						<Quantity>' . (int) $quantity . '</Quantity>
						<StartPrice>' . $price . '</StartPrice>
						</InventoryStatus>';
				} else {
					$sku = get_post_meta( $productId, '_sku', true );
					if ( '' != $sku && null != $sku ) {
						$CedInventoryStatusXml .= '<InventoryStatus>
							<ItemID>' . $itemId . '</ItemID>
							<SKU>' . str_replace( '&', '&amp;', $sku ) . '</SKU>
							<Quantity>' . (int) $quantity . '</Quantity>
							<StartPrice>' . $price . '</StartPrice>
							</InventoryStatus>';
					} else {
						$CedInventoryStatusXml .= '<InventoryStatus>
							<ItemID>' . $itemId . '</ItemID>
							<SKU>' . $productId . '</SKU>
							<Quantity>' . (int) $quantity . '</Quantity>
							<StartPrice>' . $price . '</StartPrice>
						</InventoryStatus>';
					}
				}
			} else {

				if ( $product->is_type( 'variation' ) ) {
					$sku = get_post_meta( $productId, '_sku', true );
					if ( empty( $sku ) ) {
						$sku = $productId;
					}
					$CedInventoryStatusXml .= '<InventoryStatus>
						<ItemID>' . $itemId . '</ItemID>
						<SKU>' . str_replace( '&', '&amp;', $sku ) . '</SKU>
						<Quantity>' . (int) $quantity . '</Quantity>
						</InventoryStatus>';
				} else {
					$sku = get_post_meta( $productId, '_sku', true );
					if ( '' != $sku && null != $sku ) {
						$CedInventoryStatusXml .= '<InventoryStatus>
							<ItemID>' . $itemId . '</ItemID>
							<SKU>' . str_replace( '&', '&amp;', $sku ) . '</SKU>
							<Quantity>' . (int) $quantity . '</Quantity>
							</InventoryStatus>';
					} else {
						$CedInventoryStatusXml .= '<InventoryStatus>
							<ItemID>' . $itemId . '</ItemID>
							<SKU>' . $productId . '</SKU>
							<Quantity>' . (int) $quantity . '</Quantity>
						</InventoryStatus>';
					}
				}
			}
		}
		if ( '' != $CedInventoryStatusXml ) {
			$reviseInventoryXml = str_replace( '<CedInventoryStatus>ced</CedInventoryStatus>', $CedInventoryStatusXml, $reviseInventoryXml );
		}

		return $reviseInventoryXml;
	}


	/*
	 *
	 *function for preparing  product data
	 *
	 *
	 */
	public function getFormattedData( $proIds = '', $userId = '', $ebayItemID = '' ) {
		$variation   = true;
		$finalXml    = '';
		$counter     = 0;
		$profileData = $this->ced_ebay_getProfileAssignedData( $proIds, $userId );
		if ( false == $this->isProfileAssignedToProduct ) {
			return 'No Profile Assigned';
		}
		$product = wc_get_product( $proIds );
		$item    = array();
		if ( WC()->version > '3.0.0' ) {
			$product_data            = $product->get_data();
			$productType             = $product->get_type();
			$quantity                = (int) get_post_meta( $proIds, '_stock', true );
			$title                   = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_product_title_val' );
			$product_custom_condtion = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_product_custom_condition' );
			$subtitle                = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_product_subtitle_val' );
			$get_alt_description     = get_post_meta( $proIds, 'ced_ebay_alt_prod_description_' . $proIds . '_' . $userId, true );
			if ( ! empty( $get_alt_description ) ) {
				$description = urldecode( $get_alt_description );
				$description = nl2br( $description );
			} else {
				$description = $product_data['description'] . ' ' . $product_data['short_description'];
				$description = nl2br( $description );
			}
			if ( '' == $title ) {
				$get_alt_title = get_post_meta( $proIds, 'ced_ebay_alt_prod_title_' . $proIds . '_' . $userId, true );
				if ( ! empty( $get_alt_title ) ) {
					$title = $get_alt_title;
				} else {
					$title = $product_data['name'];
				}
			}
		}
		$shop_data = ced_ebay_get_shop_data( $userId );
		if ( ! empty( $shop_data ) ) {
			$siteID      = $shop_data['site_id'];
			$token       = $shop_data['access_token'];
			$getLocation = $shop_data['location'];
		}
		$savedReturnPolicies = get_option( 'ced_umb_ebay_site_selected_return_methods_' . $userId, array() );
		$package_length      = get_post_meta( $proIds, '_length', true );
		$package_width       = get_post_meta( $proIds, '_width', true );
		$package_height      = get_post_meta( $proIds, '_height', true );
		$listingDuration     = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_listing_duration' );
		$lisyingType         = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_listing_type' );
		$dispatchTime        = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_dispatch_time' );
		if ( '' === $dispatchTime || '0' === $dispatchTime ) {
			$dispatchTime = 0;
		}
		$pictureUrl     = wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'full' ) ? str_replace( ' ', '%20', wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'full' ) ) : '';
		$pictureUrl     = strtok( $pictureUrl, '?' );
		$primarycatId   = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_category' );
		$attachment_ids = $product->get_gallery_image_ids();

		$category_id = isset( $product_data['category_ids'] ) ? $product_data['category_ids'] : array();
		foreach ( $category_id as $key => $value ) {
			$storeCustomCatId = get_term_meta( $value, 'ced_ebay_mapped_to_store_category_' . $userId, true );
			if ( ! empty( $storeCustomCatId ) ) {
				break;
			}
		}
		foreach ( $category_id as $key => $value ) {
			$storeSecondaryID = get_term_meta( $value, 'ced_ebay_mapped_to_store_secondary_category_' . $userId, true );
			if ( ! empty( $storeSecondaryID ) ) {
				break;
			}
		}
		foreach ( $category_id as $key => $value ) {
			$ebay_secondary_category_id = get_term_meta( $value, 'ced_ebay_mapped_secondary_category_' . $userId, true );
			if ( ! empty( $ebay_secondary_category_id ) ) {
				break;
			}
		}

		$store_data = ! empty( get_option( 'ced_ebay_store_data_' . $userId, true ) ) ? get_option( 'ced_ebay_store_data_' . $userId, true ) : false;

		if ( $store_data ) {
			$store_custom_categories = ! empty( $store_data['Store']['CustomCategories']['CustomCategory'] ) ? $store_data['Store']['CustomCategories']['CustomCategory'] : false;
		}

		if ( $store_data ) {

			if ( $storeCustomCatId ) {
				$storeCustomCatName = $this->ced_ebay_recursive_find_category_id( $storeCustomCatId, $store_data );
			}

			if ( $storeSecondaryID ) {
				$storeCustomSecondaryCatName = $this->ced_ebay_recursive_find_category_id( $storeSecondaryID, $store_data );
			}
		}

		global $wpdb;
		$shippingTemplates = $wpdb->get_results( $wpdb->prepare( "SELECT `id`,`shipping_name`,`shipping_data` FROM {$wpdb->prefix}ced_ebay_shipping WHERE `user_id` = %s ORDER BY `id` DESC", $userId ), 'ARRAY_A' );
		if ( empty( $shippingTemplates ) ) {
			return 'No Shipping Template';
		}
		if ( ! empty( $shippingTemplates ) ) {
			foreach ( $shippingTemplates as $template ) {
				$templateData  = isset( $template['shipping_data'] ) ? json_decode( $template['shipping_data'], true ) : array();
				$productWeight = get_post_meta( $proIds, '_weight', true );
				if ( empty( $productWeight ) ) {
					$productWeight = 0;
				}

				$minWeightRange = isset( $templateData['shippingDetails']['shippingweightminor'] ) ? $templateData['shippingDetails']['shippingweightminor'] : 0;
				$maxWeightRange = isset( $templateData['shippingDetails']['shippingweightmajor'] ) ? $templateData['shippingDetails']['shippingweightmajor'] : 100;

				if ( $productWeight >= $minWeightRange && $productWeight <= $maxWeightRange ) {
					$savedShippingDetails = $templateData['shippingDetails'];
				}
			}
		}
		$item['Title'] = $title;
		if ( ! empty( $subtitle ) ) {
			$item['Subtitle'] = $subtitle;
		}
		$mpn   = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_mpn' );
		$ean   = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_ean' );
		$isbn  = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_isbn' );
		$upc   = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_upc' );
		$brand = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_brand' );
		if ( empty( $ean ) ) {
			$ean = 'Does Not Apply';
		}
		if ( empty( $mpn ) ) {
			if ( ! empty( get_post_meta( $proIds, '_sku', true ) ) ) {
				$mpn = get_post_meta( $proIds, '_sku', true );
			} else {
				$mpn = 'Does Not Apply';
			}
		}
		if ( '' != $mpn || '' != $ean || '' != $isbn || '' != $upc ) {
			if ( '' != $brand ) {
				$item['ProductListingDetails']['BrandMPN']['Brand'] = $brand;
				$item['ProductListingDetails']['BrandMPN']['MPN']   = $mpn;

			} else {
				$item['ProductListingDetails']['BrandMPN']['Brand'] = 'No Brand Availaible';
				$item['ProductListingDetails']['BrandMPN']['MPN']   = $mpn;
			}
			if ( '' != $ean ) {
				$item['ProductListingDetails']['EAN'] = $ean;
			}
			if ( '' != $isbn ) {
				$item['ProductListingDetails']['ISBN'] = $isbn;
			}
			if ( '' != $upc ) {
				$item['ProductListingDetails']['UPC'] = $upc;
			} else {
				$item['ProductListingDetails']['UPC'] = 'Does Not Apply';
			}
		}
		if ( ! empty( $ebayItemID ) ) {
			$item['ItemID'] = $ebayItemID;
		}

		$description_template_id    = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_description_template' );
		$renderDataOnGlobalSettings = get_option( 'ced_ebay_global_settings', false );
		if ( empty( $description_template_id ) || '' == $description_template_id ) {
			$description_template_id = ! empty( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_listing_description_template'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_listing_description_template'] : '';
		}
		if ( isset( $description_template_id ) && '' != $description_template_id ) {
			$upload_dir    = wp_upload_dir();
			$templates_dir = $upload_dir['basedir'] . '/ced-ebay/templates/';
			if ( file_exists( $templates_dir . $description_template_id ) ) {
				$template_html = @file_get_contents( $templates_dir . $description_template_id . '/template.html' );
				$custom_css    = @file_get_contents( $templates_dir . $description_template_id . '/style.css' );
			}
			if ( get_option( 'ced_ebay_global_settings', false ) ) {
				$dataInGlobalSettings = get_option( 'ced_ebay_global_settings', false );
				$price_markup_type    = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup_type'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup_type'] : '';
				$price_markup_value   = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup'] : '';
			}
			$dataInGlobalSettings = ! empty( get_option( 'ced_ebay_global_settings', false ) ) ? get_option( 'ced_ebay_global_settings', false ) : '';
			$price_selection      = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_price_option'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_price_option'] : '';

			if ( $product->is_type( 'variable' ) ) {
				$allVariations = $product->get_children();
				foreach ( $allVariations as $key => $var_prod_id ) {
					if ( strpos( $template_html, '[woo_ebay_product_price][' . $key . ']' ) ) {
						$var_prod = wc_get_product( $var_prod_id );

						if ( 'Regular_Price' == $price_selection ) {
							$price = $product->get_regular_price();
						} elseif ( 'Sale_Price' == $price_selection ) {
							$price = $product->get_sale_price();
						} else {
							$price = $product->get_price();
						}


						if ( 'Fixed_Increased' == $price_markup_type ) {
							$product_price = $product_price + $price_markup_value;
						} elseif ( 'Percentage_Increased' == $price_markup_type ) {
							$product_price = $product_price + ( ( $product_price * $price_markup_value ) / 100 );
						} elseif ( 'Percentage_Decreased' == $price_markup_type ) {
							$product_price = $product_price - ( ( $product_price * $price_markup_value ) / 100 );
						} elseif ( 'Fixed_Decreased' == $price_markup_type ) {
							$product_price = $product_price - $price_markup_value;
						}
						$template_html = str_replace( '[woo_ebay_product_price][' . $key . ']', $product_price, $template_html );

					}
				}
			} else {
				if ( 'Regular_Price' == $price_selection ) {
					$price = $product->get_regular_price();
				} elseif ( 'Sale_Price' == $price_selection ) {
					$price = $product->get_sale_price();
				} else {
					$price = $product->get_price();
				}
				if ( 'Fixed_Increased' == $price_markup_type ) {
					$product_price = $product_price + $price_markup_value;
				} elseif ( 'Percentage_Increased' == $price_markup_type ) {
					$product_price = $product_price + ( ( $product_price * $price_markup_value ) / 100 );
				} elseif ( 'Percentage_Decreased' == $price_markup_type ) {
					$product_price = $product_price - ( ( $product_price * $price_markup_value ) / 100 );
				} elseif ( 'Fixed_Decreased' == $price_markup_type ) {
					$product_price = $product_price - $price_markup_value;
				}
							$template_html = str_replace( '[woo_ebay_product_price]', $product_price, $template_html );

			}

			$product_image             = '<img src="' . utf8_uri_encode( strtok( $pictureUrl, '?' ) ) . '" >';
			$product_content           = wp_kses( nl2br( $product->get_description() ), array( 'br' => array() ) );
			$product_short_description = nl2br( $product->get_short_description() );
			$product_sku               = $product->get_sku();
			$product_category          = wp_get_post_terms( $proIds, 'product_cat' );

			$product_gallery_images = array();
			if ( ! empty( $attachment_ids ) ) {
				foreach ( $attachment_ids as $attachment_id ) {
					$product_gallery_images[] = wp_get_attachment_url( $attachment_id );
				}
			}

			if ( ! empty( $product_gallery_images ) ) {
				foreach ( $product_gallery_images as $key => $image_url ) {
					$image_url = strtok( $image_url, '?' );
					if ( strpos( $template_html, '[ced_ebay_gallery_image][' . $key . ']' ) ) {
						$gallery_image_html = '<img src="' . $image_url . '" >';
						$template_html      = str_replace( '[ced_ebay_gallery_image][' . $key . ']', $gallery_image_html, $template_html );
					}
				}
			}

			$template_html       = str_replace( '[woo_ebay_product_title]', $title, $template_html );
			$template_html       = str_replace( '[woo_ebay_product_description]', $product_content, $template_html );
			$template_html       = str_replace( '[woo_ebay_product_short_description]', $product_short_description, $template_html );
			$template_html       = str_replace( '[woo_ebay_product_sku]', $product_sku, $template_html );
			$template_html       = str_replace( '[woo_ebay_product_main_image]', $product_image, $template_html );
			$template_html       = str_replace( '[woo_ebay_product_category]', $product_category[0]->name, $template_html );
			$template_html       = str_replace( '[woo_ebay_product_type]', $productType, $template_html );
			$template_html       = str_replace( '[woo_ebay_product_short_description]', $product_short_description, $template_html );
			$custom_css          = '<style type="text/css">' . $custom_css . '</style>';
			$product_description = $custom_css . ' <br> ' . $template_html . ' </br> ';
		}

		$item['Description']                   = ! empty( $product_description ) ? $product_description : $description;
		$item['PrimaryCategory']['CategoryID'] = $primarycatId;
		$item['CategoryMappingAllowed']        = true;
		$item['Site']                          = $getLocation;
		if ( ! empty( get_post_meta( $proIds, 'ced_ebay_prod_store_cat_value_' . $proIds . '_' . $userId, true ) && ! empty( get_post_meta( $proIds, 'ced_ebay_prod_store_cat_name_' . $proIds . '_' . $userId, true ) ) ) ) {
			$store_category_id                       = get_post_meta( $proIds, 'ced_ebay_prod_store_cat_value_' . $proIds . '_' . $userId, true );
			$store_category_name                     = get_post_meta( $proIds, 'ced_ebay_prod_store_cat_name_' . $proIds . '_' . $userId, true );
			$item['Storefront']['StoreCategoryID']   = $store_category_id;
			$item['Storefront']['StoreCategoryName'] = $store_category_name;
		}

		if ( ( ! empty( $storeCustomCatId ) && ! empty( $storeCustomCatName ) ) ) {
			$item['Storefront']['StoreCategoryID']   = $storeCustomCatId;
			$item['Storefront']['StoreCategoryName'] = $storeCustomCatName;

		}
		if ( ! empty( $storeSecondaryID ) && ! empty( $storeCustomSecondaryCatName ) ) {
			$item['Storefront']['StoreCategory2ID']   = $storeSecondaryID;
			$item['Storefront']['StoreCategory2Name'] = $storeCustomSecondaryCatName;
		}

		if ( ! empty( $ebay_secondary_category_id ) ) {
			$item['SecondaryCategory']['CategoryID'] = $ebay_secondary_category_id;
		}

		$renderDataOnGlobalSettings = get_option( 'ced_ebay_global_settings', false );
		$item_location_state        = ! empty( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_item_location_state'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_item_location_state'] : $getLocation;
		$item['Location']           = $item_location_state;
		$amount                     = get_post_meta( $proIds, '_stock', true );
		$listing_stock_type         = isset( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_product_stock_type'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_product_stock_type'] : '';
		$listing_stock              = isset( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_listing_stock'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_listing_stock'] : '';

			$manage_stock   = get_post_meta( $proIds, '_manage_stock', true );
			$product_status = get_post_meta( $proIds, '_stock_status', true );

		if ( 'yes' != $manage_stock && 'instock' == $product_status ) {
			$renderDataOnGlobalSettings = get_option( 'ced_ebay_global_settings', false );
			if ( ! empty( $listing_stock_type ) && ! empty( $listing_stock ) && 'MaxStock' == $listing_stock_type ) {
				$amount = $listing_stock;
			} else {
				$amount = 1;
			}
		} else {
			if ( 'outofstock' != $product_status ) {
				if ( ! empty( $listing_stock_type ) && ! empty( $listing_stock ) && 'MaxStock' == $listing_stock_type ) {
					if ( $amount > $listing_stock ) {
						$amount = $listing_stock;
					} else {
						$amount = intval( $amount );
						if ( $amount < 1 ) {
							$amount = '0';
						}
					}
				} else {
					$amount = intval( $amount );
					if ( $amount < 1 ) {
						$amount = '0';
					}
				}
			} else {
				$amount = 0;
			}
		}

		$dataInGlobalSettings = ! empty( get_option( 'ced_ebay_global_settings', false ) ) ? get_option( 'ced_ebay_global_settings', false ) : '';
		$price_selection      = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_price_option'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_price_option'] : '';
		if ( 'Regular_Price' == $price_selection ) {
			$price = $product->get_regular_price();
		} elseif ( 'Sale_Price' == $price_selection ) {
			$price = $product->get_sale_price();
		} else {
			$price = $product->get_price();
		}

		
		$profile_price_markup_type = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_profile_price_markup_type' );
		$profile_price_markup      = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_profile_price_markup' );
		if ( get_option( 'ced_ebay_global_settings', false ) ) {
			if ( 'Regular_Price' == $price_selection ) {
				$price = $product->get_regular_price();
			} elseif ( 'Sale_Price' == $price_selection ) {
				$price = $product->get_sale_price();
			} else {
				$price = $product->get_price();
			}
			
			$dataInGlobalSettings = get_option( 'ced_ebay_global_settings', false );
			$price_markup_type    = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup_type'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup_type'] : '';
			$price_markup_value   = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup'] : '';
		}
		if ( ! empty( $profile_price_markup_type ) && ! empty( $profile_price_markup ) ) {
			if ( 'Fixed_Increase' == $profile_price_markup_type ) {
				$price = $price + $profile_price_markup;
			} elseif ( 'Percentage_Increase' == $profile_price_markup_type ) {
				$price = $price + ( ( $price * $profile_price_markup ) / 100 );
			} elseif ( 'Percentage_Decrease' == $profile_price_markup_type ) {
				$price = $price - ( ( $price * $profile_price_markup ) / 100 );
			} elseif ( 'Fixed_Decrease' == $profile_price_markup_type ) {
				$price = $price - $profile_price_markup;
			}
		} elseif ( ! empty( $price_markup_type ) && ! empty( $price_markup_value ) ) {
			if ( 'Fixed_Increased' == $price_markup_type ) {
				$price = $price + $price_markup_value;
			} elseif ( 'Percentage_Increased' == $price_markup_type ) {
				$price = $price + ( ( $price * $price_markup_value ) / 100 );
			} elseif ( 'Percentage_Decreased' == $price_markup_type ) {
				$price = $price - ( ( $price * $price_markup_value ) / 100 );
			} elseif ( 'Fixed_Decreased' == $price_markup_type ) {
				$price = $price - $price_markup_value;
			}
		}
		$item['StartPrice']      = $price;
		$item['SKU']             = ! empty( $product->get_sku() ) ? $product->get_sku() : $proIds;
		$item['ListingDuration'] = ! empty( $listingDuration ) ? $listingDuration : 'GTC';
		$item['ListingType']     = 'FixedPriceItem';
		$item['DispatchTimeMax'] = 'cedDispatchTime';

		$dispatch_time_xml = '<DispatchTimeMax>' . $dispatchTime . '</DispatchTimeMax>';
		$Autopay           = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_autopay' );
		if ( 'No' == $Autopay ) {
			$item['AutoPay'] = 'false';
		} elseif ( 'Yes' == $Autopay ) {
			$item['AutoPay'] = 'true';
		}

		$postalCode = '';
		$postalCode = isset( $savedShippingDetails['postal_code'] ) ? $savedShippingDetails['postal_code'] : '';
		if ( '' == $postalCode || null == $postalCode ) {
			$missingValues[] = __( 'Missing postal code', 'ced-umb-ebay' );
		}

		$wp_folder     = wp_upload_dir();
		$wp_upload_dir = $wp_folder['basedir'];
		$wp_upload_dir = $wp_upload_dir . '/ced-ebay/category-specifics/' . $userId . '/';

		$cat_specifics_file = $wp_upload_dir . 'ebaycat_' . $primarycatId . '.json';
		if ( file_exists( $cat_specifics_file ) ) {
			$available_attribute = json_decode( file_get_contents( $cat_specifics_file ), true );
		}
		if ( ! is_array( $available_attribute ) ) {
			$available_attribute = array();
		}

		$fileCategory = CED_EBAY_DIRPATH . 'admin/ebay/lib/cedGetcategories.php';
		if ( file_exists( $fileCategory ) ) {
			require_once $fileCategory;
		}

		$ebayConfig = CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayConfig.php';
		if ( file_exists( $ebayConfig ) ) {
			require_once $ebayConfig;
		}

		$ebayCategoryInstance = CedGetCategories::get_instance( $siteID, $token );
		if ( ! empty( $available_attribute ) && is_array( $available_attribute ) ) {
			$getCatSpecifics = $available_attribute;
			$limit           = array( 'ConditionEnabled', 'ConditionValues' );
			$getCatFeatures  = $ebayCategoryInstance->_getCatFeatures( $primarycatId, $limit );
			$getCatFeatures  = isset( $getCatFeatures['Category'] ) ? $getCatFeatures['Category'] : false;
		} else {
			$getCatSpecifics      = $ebayCategoryInstance->_getCatSpecifics( $primarycatId );
			$getCatSpecifics_json = json_encode( $getCatSpecifics );
			$cat_specifics_file   = $wp_upload_dir . 'ebaycat_' . $primarycatId . '.json';
			if ( file_exists( $cat_specifics_file ) ) {
				wp_delete_file( $cat_specifics_file );
			}
			file_put_contents( $cat_specifics_file, $getCatSpecifics_json );
			$limit          = array( 'ConditionEnabled', 'ConditionValues' );
			$getCatFeatures = $ebayCategoryInstance->_getCatFeatures( $primarycatId, $limit );
			$getCatFeatures = isset( $getCatFeatures['Category'] ) ? $getCatFeatures['Category'] : false;
		}
		$nameValueList = '';
		$catSpecifics  = array();
		if ( ! empty( $getCatSpecifics ) ) {
			$catSpecifics = $getCatSpecifics;
		}

		if ( is_array( $catSpecifics ) && ! empty( $catSpecifics ) ) {
			foreach ( $catSpecifics as $specific ) {
				if ( isset( $specific['localizedAspectName'] ) ) {
					$catSpcfcs = $this->fetchMetaValueOfProduct( $proIds, urlencode( $primarycatId . '_' . $specific['localizedAspectName'] ) );
					if ( isset( $specific['ValidationRules']['MinValues'] ) && 1 == $specific['ValidationRules']['MinValues'] && ( '' == $catSpcfcs || null == $catSpcfcs ) ) {
						$missingValues[] = $specific['Name'];
					}
					if ( $catSpcfcs ) {
						if ( strpos( $catSpcfcs, '&' ) !== false ) {
							$catSpcfcs = str_replace( '&', '&amp;', $catSpcfcs );
						} elseif ( strpos( $specific['localizedAspectName'], '&' ) !== false ) {
							$specific['localizedAspectName'] = str_replace( '&', '&amp;', $specific['localizedAspectName'] );
						}
						$nameValueList .= '<NameValueList>
						<Name>' . $specific['localizedAspectName'] . '</Name>
						<Value>' . $catSpcfcs . '</Value>
					</NameValueList>';
					}
				}
			}
		}
		$conditionID = '';
		if ( $getCatFeatures ) {
			if ( isset( $getCatFeatures['ConditionValues'] ) ) {
				$valueForDropdown     = $getCatFeatures['ConditionValues']['Condition'];
				$tempValueForDropdown = array();
				$conditionID          = $this->fetchMetaValueOfProduct( $proIds, $primarycatId . '_Condition' );
				if ( '' == $conditionID || null == $conditionID ) {
					$missingValues[] = 'Condition id';
				}
			}
		}

		$item['Title']      = $title;
		$custom_template_id = 0;
		$custom_template_id = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_custom_description_template' );

		if ( '' != $nameValueList && null != $nameValueList ) {
			$nameValueList         = '<ItemSpecifics>' . $nameValueList . '</ItemSpecifics>';
			$item['ItemSpecifics'] = 'ced';
		}

		if ( 'variable' == $productType ) {
			$VariationSpecificsFinalSet = $this->getFormattedDataForVariation( $proIds, $userId );
			if ( '' != $VariationSpecificsFinalSet && null != $VariationSpecificsFinalSet ) {
				$item['Variations'] = 'ced';
			}
		}
		$item['PrimaryCategory']['CategoryID'] = $primarycatId;

		if ( '' != $mpn || '' != $ean || '' != $isbn || '' != $upc ) {
			if ( '' != $ean ) {
				$item['ProductListingDetails']['EAN'] = $ean;
			}
			if ( '' != $isbn ) {
				$item['ProductListingDetails']['ISBN'] = $isbn;
			}
			if ( '' != $upc ) {
				$item['ProductListingDetails']['UPC'] = $upc;
			} else {
				$item['ProductListingDetails']['UPC'] = 'DoesNotApply';
			}
		}

		$_umb_ebay_prefill_listing_with_ebay_catalog = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_prefill_listing_with_ebay_catalog' );
		$_umb_ebay_use_stock_image                   = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_use_stock_image' );

		if ( ! empty( $_umb_ebay_prefill_listing_with_ebay_catalog ) ) {
			$item['ProductListingDetails']['IncludeeBayProductDetails'] = $_umb_ebay_prefill_listing_with_ebay_catalog;
		}
		if ( ! empty( $_umb_ebay_use_stock_image ) ) {
			$item['ProductListingDetails']['IncludeStockPhotoURL'] = $_umb_ebay_use_stock_image;
		}

		if ( ! empty( $product_custom_condtion ) ) {
			$item['ConditionDescription'] = $product_custom_condtion;
		}

		$item['CategoryMappingAllowed'] = true;
		if ( '' != $conditionID && null != $conditionID ) {
			$item['ConditionID'] = $conditionID;
		} else {
			$item['ConditionID'] = 1000;
		}
		if ( ! empty( $amount ) || 0 == $amount ) {
			$item['Quantity'] = $amount;
		}
		$item['StartPrice']      = $price;
		$item['ListingDuration'] = ! empty( $listingDuration ) ? $listingDuration : 'GTC';
		$item['ListingType']     = 'FixedPriceItem';

		$BestOfferEnabled = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_bestoffer' );
		if ( 'No' == $BestOfferEnabled ) {
			$item['BestOfferDetails']['BestOfferEnabled'] = 'false';
		} elseif ( 'Yes' == $BestOfferEnabled ) {
			$item['BestOfferDetails']['BestOfferEnabled'] = 'true';

			$_umb_ebay_auto_accept_offers  = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_auto_accept_offers' );
			$_umb_ebay_auto_decline_offers = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_auto_decline_offers' );

			if ( ! empty( $_umb_ebay_auto_accept_offers ) ) {
				$operator         = explode( '|', $_umb_ebay_auto_accept_offers )[0];
				$markup           = explode( '|', $_umb_ebay_auto_accept_offers )[1];
				$fixed_or_percent = explode( '|', $_umb_ebay_auto_accept_offers )[2];

				if ( '$' == $fixed_or_percent ) {
					if ( '+' == $operator ) {
						$autoaceptprice = $price + $markup;
					} elseif ( '-' == $operator ) {
						$autoaceptprice = $price - $markup;
					}
					$item['ListingDetails']['BestOfferAutoAcceptPrice'] = $autoaceptprice;
				} elseif ( '%' == $fixed_or_percent ) {
					if ( '+' == $operator ) {
						$autoaceptprice = $price + ( $price * $markup / 100 );
					} elseif ( '-' == $operator ) {
						$autoaceptprice = $price - ( $price * $markup / 100 );
					}
					$item['ListingDetails']['BestOfferAutoAcceptPrice'] = $autoaceptprice;
				}
			}

			if ( ! empty( $_umb_ebay_auto_decline_offers ) ) {
				$operator         = explode( '|', $_umb_ebay_auto_decline_offers )[0];
				$markup           = explode( '|', $_umb_ebay_auto_decline_offers )[1];
				$fixed_or_percent = explode( '|', $_umb_ebay_auto_decline_offers )[2];

				if ( '$' == $fixed_or_percent ) {
					if ( '+' == $operator ) {
						$autodeclineprice = $price + $markup;
					} elseif ( '-' == $operator ) {
						$autodeclineprice = $price - $markup;
					}
					$item['ListingDetails']['MinimumBestOfferPrice'] = $autodeclineprice;
				} elseif ( '%' == $fixed_or_percent ) {
					if ( '+' == $operator ) {
						$autodeclineprice = $price + ( $price * $markup / 100 );
					} elseif ( '-' == $operator ) {
						$autodeclineprice = $price - ( $price * $markup / 100 );
					}
					$item['ListingDetails']['MinimumBestOfferPrice'] = $autodeclineprice;
				}
			}
		}

		$length = get_post_meta( $proIds, '_length', true );
		if ( empty( $length ) ) {
			$length = 0;
		}
		$width = get_post_meta( $proIds, '_width', true );
		if ( empty( $width ) ) {
			$width = 0;
		}
		$height = get_post_meta( $proIds, '_height', true );
		if ( empty( $height ) ) {
			$height = 0;
		}
		if ( '' != $height ) {
			$item['ShippingPackageDetails']['PackageDepth'] = $height;
		}

		if ( '' != $length ) {
			$item['ShippingPackageDetails']['PackageLength'] = $length;
		}

		if ( '' != $width ) {
			$item['ShippingPackageDetails']['PackageWidth'] = $width;
		}

		$productWeight = get_post_meta( $proIds, '_weight', true );
		$weight_unit   = get_option( 'woocommerce_weight_unit' );
		if ( 'Flat' != $savedShippingDetails['serviceType'] ) {
			if ( '' != $productWeight ) {
				if ( 'lbs' == $weight_unit ) {
					$item['ShippingPackageDetails']['MeasurementUnit'] = 'English';
					$weight_in_pounds                                  = (int) $productWeight;
					$weight_frac                                       = $productWeight - $weight_in_pounds;
					$weight_in_ounces                                  = ceil( $weight_frac * 16 );
					$weight_major_xml                                  = '<WeightMajor unit="lbs">' . $weight_in_pounds . '</WeightMajor><WeightMinor unit="oz">' . $weight_in_ounces . '</WeightMinor>';

				} elseif ( 'kg' == $weight_unit ) {
					$item['ShippingPackageDetails']['MeasurementUnit'] = 'Metric';
					$weight_in_kg                                      = (int) $productWeight;
					$weight_frac                                       = $productWeight - $weight_in_kg;
					$weight_in_grams                                   = $weight_frac * 1000;
					$weight_major_xml                                  = '<WeightMajor unit="kg">' . $weight_in_kg . '</WeightMajor><WeightMinor unit="gr">' . $weight_in_grams . '</WeightMinor>';
				} elseif ( 'g' == $weight_unit ) {
					$item['ShippingPackageDetails']['MeasurementUnit'] = 'Metric';
					$weight_in_grams                                   = (int) $productWeight;
					$weight_frac                                       = $productWeight - $weight_in_grams;
					$weight_in_kg                                      = $weight_frac / 1000;
					$weight_major_xml                                  = '<WeightMajor unit="kg">' . $weight_in_kg . '</WeightMajor><WeightMinor unit="gr">' . $weight_in_grams . '</WeightMinor>';
				} elseif ( 'oz' == $weight_unit ) {
					$item['ShippingPackageDetails']['MeasurementUnit'] = 'English';
					$weight_in_oz                                      = (int) $productWeight;
					$weight_frac                                       = $productWeight - $weight_in_oz;
					$weight_in_pounds                                  = ceil( $weight_frac / 16 );
					$weight_major_xml                                  = '<WeightMajor unit="lbs">' . $weight_in_pounds . '</WeightMajor><WeightMinor unit="oz">' . $weight_in_oz . '</WeightMinor>';
				}

				$item['ShippingPackageDetails']['WeightMajor'] = 'cedWeightMajor';
			}
		} else {
			if ( '' == $productWeight ) {
				$productWeight = 0;
			}
		}

		$item['ShippingDetails']['ShippingServiceOptions'] = 'cedShipping';
		$globalShippingProgram                             = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_global_shipping_program' );
		if ( ! empty( $globalShippingProgram ) ) {
			$item['ShippingDetails']['GlobalShipping'] = $globalShippingProgram;
		}
		if ( '' != $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_sales_tax_percent' ) ) {
			$item['ShippingDetails']['SalesTax']['SalesTaxPercent']       = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_sales_tax_percent' );
			$item['ShippingDetails']['SalesTax']['SalesTaxState']         = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_sales_tax_location' );
			$item['ShippingDetails']['SalesTax']['ShippingIncludedInTax'] = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_sales_tax_for_shipping' );
		}

		if ( ! empty( $savedShippingDetails['internationalShippingService']['services'] ) ) {
			$item['ShippingDetails']['InternationalShippingServiceOption'] = 'cedShipping';
		}

		$domestic_str_shipping = '';
		$intl_str_shipping     = '';

		if ( is_array( $savedShippingDetails ) && ! empty( $savedShippingDetails ) ) {
			if ( ! empty( $savedShippingDetails['exclusion_list'] ) ) {
				$item['ShippingDetails']['ExcludeShipToLocation'] = 'cedExcludeLocation';
				$exclude_location_xml                             = '';
				$item['Item']['BuyerRequirementDetails']['ShipToRegistrationCountry'] = 'true';
				foreach ( $savedShippingDetails['exclusion_list'] as $key => $excluded_location ) {
					if ( strpos( $excluded_location, 'Domestic_' ) !== false ) {
						$exc_location          = substr( $excluded_location, 9, strlen( $excluded_location ) );
						$exclude_location_xml .= '<ExcludeShipToLocation>' . $exc_location . '</ExcludeShipToLocation>';
					}
					if ( strpos( $excluded_location, 'International_' ) !== false ) {
						$exc_location          = substr( $excluded_location, 14, strlen( $excluded_location ) );
						$exclude_location_xml .= '<ExcludeShipToLocation>' . $exc_location . '</ExcludeShipToLocation>';
					}
				}
			}
			if ( isset( $savedShippingDetails['serviceType'] ) ) {
				$item['ShippingDetails']['ShippingType'] = $savedShippingDetails['serviceType'];

				if ( 'Calculated' == $savedShippingDetails['serviceType'] ) {

					$item['ShippingDetails']['CalculatedShippingRate']['OriginatingPostalCode'] = $postalCode;
					$domesticPackagingCost      = isset( $savedShippingDetails['domesticPackagingCost'] ) ? $savedShippingDetails['domesticPackagingCost'] : '';
					$internationalPackagingCost = isset( $savedShippingDetails['internationalPackagingCost'] ) ? $savedShippingDetails['internationalPackagingCost'] : '';

					if ( '' != $domesticPackagingCost ) {
						$item['ShippingDetails']['CalculatedShippingRate']['PackagingHandlingCosts'] = $domesticPackagingCost;
					}

					if ( '' != $internationalPackagingCost ) {
						$item['ShippingDetails']['CalculatedShippingRate']['InternationalPackagingHandlingCosts'] = $internationalPackagingCost;
					}
				}
			}

			if ( isset( $savedShippingDetails['domesticShippingService'] ) ) {
				$count = 1;
				foreach ( $savedShippingDetails['domesticShippingService']['services'] as $key11 => $value11 ) {
					$add_cost = $savedShippingDetails['domesticShippingService']['shippingaddcost'][ $key11 ];
					if ( empty( $add_cost ) ) {
						$add_cost = 0;
					}
					$domestic_str_shipping .= '<ShippingServiceOptions>';
					$domestic_str_shipping .= "<ShippingService>$value11</ShippingService>";
					if ( 'Flat' == $savedShippingDetails['serviceType'] ) {
						$domestic_str_shipping .= '<ShippingServiceAdditionalCost>' . $add_cost . '</ShippingServiceAdditionalCost>';
						$domestic_str_shipping .= '<ShippingServiceCost>' . $savedShippingDetails['domesticShippingService']['shippingcost'][ $key11 ] . '</ShippingServiceCost>';
					}
					$domestic_str_shipping .= '<ShippingServicePriority>' . $count . '</ShippingServicePriority>';
					$domestic_str_shipping .= '</ShippingServiceOptions>';
					++$count;
				}
			}

			if ( isset( $savedShippingDetails['internationalShippingService'] ) ) {
				$count = 1;
				foreach ( $savedShippingDetails['internationalShippingService']['services'] as $key11 => $value11 ) {
					$add_cost = $savedShippingDetails['internationalShippingService']['shippingaddcost'][ $key11 ];
					if ( empty( $add_cost ) ) {
						$add_cost = 0;
					}
					$intl_str_shipping .= '<InternationalShippingServiceOption>';
					$intl_str_shipping .= "<ShippingService>$value11</ShippingService>";
					if ( 'Flat' == $savedShippingDetails['serviceType'] ) {
						$intl_str_shipping .= '<ShippingServiceAdditionalCost>' . $add_cost . '</ShippingServiceAdditionalCost>';
						$intl_str_shipping .= '<ShippingServiceCost>' . $savedShippingDetails['internationalShippingService']['shippingcost'][ $key11 ] . '</ShippingServiceCost>';
						++$count;
					}
					$intl_str_shipping .= '<ShippingServicePriority>' . $count . '</ShippingServicePriority>';
					if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key11 ] ) ) {
						foreach ( $savedShippingDetails['internationalShippingService']['locations'][ $key11 ] as $key12 => $value12 ) {
							$intl_str_shipping .= '<ShipToLocation>' . $value12 . '</ShipToLocation>';
						}
					} else {
						return 'No international country';
					}
					$intl_str_shipping .= '</InternationalShippingServiceOption>';
				}
			}
		}

		if ( is_array( $savedReturnPolicies ) && ! empty( $savedReturnPolicies ) ) {
			if ( isset( $savedReturnPolicies['ReturnsAccepted'] ) ) {
				$item['ReturnPolicy']['ReturnsAcceptedOption'] = $savedReturnPolicies['ReturnsAccepted'];
			}

			if ( 'ReturnsNotAccepted' != $savedReturnPolicies['ReturnsAccepted'] ) {

				if ( isset( $savedReturnPolicies['Refund'] ) ) {
					$item['ReturnPolicy']['RefundOption'] = $savedReturnPolicies['Refund'];
				}

				if ( isset( $savedReturnPolicies['ReturnsWithin'] ) ) {
					$item['ReturnPolicy']['ReturnsWithinOption'] = $savedReturnPolicies['ReturnsWithin'];
				}

				if ( isset( $savedReturnPolicies['ShippingCostPaidBy'] ) ) {
					$item['ReturnPolicy']['ShippingCostPaidByOption'] = $savedReturnPolicies['ShippingCostPaidBy'];
				}
			}
		}
		$themeId = get_post_meta( $proIds, 'ced_umb_ebay_product_template', true );
		if ( '' != $themeId || null != $themeId ) {
			$item['ListingDesigner']['OptimalPictureSize'] = true;
			$item['ListingDesigner']['ThemeID']            = $themeId;
		}
		$private_listing = get_post_meta( $proIds, '_umb_ebay_private_listing', true );
		if ( 'yes' == $private_listing ) {
			$item['PrivateListing'] = true;
		}
		$configInstance   = Ebayconfig::get_instance();
		$countyDetails    = $configInstance->getEbaycountrDetail( $siteID );
		$country          = $countyDetails['countrycode'];
		$currency         = $countyDetails['currency'][0];
		$item_country     = ! empty( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_item_location_country'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_item_location_country'] : $country;
		$item['Country']  = $item_country;
		$item['Currency'] = $currency;
		if ( empty( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_item_location_country'] ) ) {
			$item['PostalCode'] = $postalCode;
		}
		$item['PaymentMethods']         = 'ced';
		$paymethod                      = '';
		$selected_payment_methods       = get_option( 'ced_umb_ebay_site_selected_payment_methods_' . $userId, array() );
		$savedPaymentDetails['methods'] = array( 'PayPal' );
		$email                          = get_option( 'ced_ebay_site_email_address_' . $userId, '' );
		foreach ( $selected_payment_methods as $paymentmethods => $paymentmethod ) {
			$paymethod .= '<PaymentMethods>' . $paymentmethod . '</PaymentMethods>';
		}
		if ( in_array( 'PayPal', $selected_payment_methods ) ) {
			$item['PayPalEmailAddress'] = $email;
		}
		$item['PictureDetails']['PictureURL'] = 'cedPicture';

		$str_pictures   = '<PictureURL>' . utf8_uri_encode( strtok( $pictureUrl, '?' ) ) . '</PictureURL>';
		$attachment_ids = $product->get_gallery_image_ids();
		if ( ! empty( $attachment_ids ) ) {
			foreach ( $attachment_ids as $attachment_id ) {
				$str_pictures .= '<PictureURL>' . utf8_uri_encode( strtok( wp_get_attachment_url( $attachment_id ), '?' ) ) . '</PictureURL>';
			}
		}

		if ( 'variable' == $productType ) {
			$xmlArray['MessageID'] = $proIds;
			$xmlArray['Item']      = $item;
			$rootElement           = 'Item';
			$xml                   = new SimpleXMLElement( "<$rootElement/>" );
			$this->array2XML( $xml, $xmlArray['Item'] );
		} else {
			$xmlArray['MessageID'] = $proIds;
			$xmlArray['Item']      = $item;
			$rootElement           = 'Item';
			$xml                   = new SimpleXMLElement( "<$rootElement/>" );
			$this->array2XML( $xml, $xmlArray['Item'] );
		}
		$val = $xml->asXML();
		if ( false !== strpos( $val, '<ItemSpecifics>ced</ItemSpecifics>' ) ) {
			$val = str_replace( '<ItemSpecifics>ced</ItemSpecifics>', $nameValueList, $val );
		}
		if ( false !== strpos( $val, '<DispatchTimeMax>cedDispatchTime</DispatchTimeMax>' ) ) {
			$val = str_replace( '<DispatchTimeMax>cedDispatchTime</DispatchTimeMax>', $dispatch_time_xml, $val );
		}

		if ( false !== strpos( $val, '<PaymentMethods>ced</PaymentMethods>' ) ) {
			$val = str_replace( '<PaymentMethods>ced</PaymentMethods>', $paymethod, $val );
		}

		if ( false !== strpos( $val, '<PictureURL>cedPicture</PictureURL>' ) ) {
			$val = str_replace( '<PictureURL>cedPicture</PictureURL>', $str_pictures, $val );
		}

		if ( false !== strpos( $val, '<WeightMajor>cedWeightMajor</WeightMajor>' ) ) {
			$val = str_replace( '<WeightMajor>cedWeightMajor</WeightMajor>', $weight_major_xml, $val );
		}

		if ( false !== strpos( $val, '<ShippingServiceOptions>cedShipping</ShippingServiceOptions>' ) ) {
			$val = str_replace( '<ShippingServiceOptions>cedShipping</ShippingServiceOptions>', $domestic_str_shipping, $val );
		}

		if ( false !== strpos( $val, '<InternationalShippingServiceOption>cedShipping</InternationalShippingServiceOption>' ) ) {
			$val = str_replace( '<InternationalShippingServiceOption>cedShipping</InternationalShippingServiceOption>', $intl_str_shipping, $val );
		}

		if ( false !== strpos( $val, '<ExcludeShipToLocation>cedExcludeLocation</ExcludeShipToLocation>' ) ) {
			$val = str_replace( '<ExcludeShipToLocation>cedExcludeLocation</ExcludeShipToLocation>', $exclude_location_xml, $val );
		}
		$finalXml .= $val;

		$counter++;

		$finalXml = str_replace( '<?xml version="1.0"?>', '', $finalXml );
		if ( 'variable' == $productType ) {
			if ( ! empty( $ebayItemID ) ) {
				$xmlHeader = '<?xml version="1.0" encoding="utf-8"?>
				<ReviseFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
					<RequesterCredentials>
						<eBayAuthToken>' . $token . '</eBayAuthToken>
					</RequesterCredentials>
					<MessageID>' . $proIds . '</MessageID>
					<Version>1267</Version>
					<ErrorLanguage>en_US</ErrorLanguage>
					<WarningLevel>High</WarningLevel>';
				$xmlFooter = '</ReviseFixedPriceItemRequest>';
			} else {
				$xmlHeader = '<?xml version="1.0" encoding="utf-8"?>
				<AddFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
					<RequesterCredentials>
						<eBayAuthToken>' . $token . '</eBayAuthToken>
					</RequesterCredentials>
					<MessageID>' . $proIds . '</MessageID>
					<Version>1267</Version>
					<ErrorLanguage>en_US</ErrorLanguage>
					<WarningLevel>High</WarningLevel>';
				$xmlFooter = '</AddFixedPriceItemRequest>';
			}
		} else {
			if ( ! empty( $ebayItemID ) ) {
				$xmlHeader = '<?xml version="1.0" encoding="utf-8"?>
				<ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
					<RequesterCredentials>
						<eBayAuthToken>' . $token . '</eBayAuthToken>
					</RequesterCredentials>
					<MessageID>' . $proIds . '</MessageID>
					<Version>1267</Version>
					<ErrorLanguage>en_US</ErrorLanguage>
					<WarningLevel>High</WarningLevel>';
				$xmlFooter = '</ReviseItemRequest>';
			} else {
				$xmlHeader = '<?xml version="1.0" encoding="utf-8"?>
				<AddItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
					<RequesterCredentials>
						<eBayAuthToken>' . $token . '</eBayAuthToken>
					</RequesterCredentials>
					<MessageID>' . $proIds . '</MessageID>
					<Version>1267</Version>
					<ErrorLanguage>en_US</ErrorLanguage>
					<WarningLevel>High</WarningLevel>';
				$xmlFooter = '</AddItemRequest>';
			}
		}

		$mainXML = $xmlHeader . $finalXml . $xmlFooter;
		if ( 'variable' == $productType ) {
			if ( '' != $VariationSpecificsFinalSet && null != $VariationSpecificsFinalSet ) {
				$mainXML = str_replace( '<Variations>ced</Variations>', $VariationSpecificsFinalSet, $mainXML );
			}
		}

		if ( 'variable' == $productType ) {
			return array( $mainXML, true );
		} else {
			return array( $mainXML, false );
		}

	}

	public function ced_ebay_recursive_find_category_id( $needle, $haystack ) {
		foreach ( $haystack as $key => $value ) {
			if ( isset( $value['ChildCategory'] ) ) {
				if ( isset( $value['CategoryID'] ) && $value['CategoryID'] == $needle ) {
					return $value['Name'];
				} else {
					$nextKey = $this->ced_ebay_recursive_find_category_id( $needle, $value['ChildCategory'] );
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

	public function getFormattedDataForVariation( $proIDs, $userId = '' ) {
		$_product            = wc_get_product( $proIDs );
		$variation_attribute = $_product->get_variation_attributes();
		$allVariations       = $_product->get_children();
		$primarycatId        = $this->fetchMetaValueOfProduct( $proIDs, '_umb_ebay_category' );
		$shop_data           = ced_ebay_get_shop_data( $userId );
		if ( ! empty( $shop_data ) ) {
			$siteID      = $shop_data['site_id'];
			$token       = $shop_data['access_token'];
			$getLocation = $shop_data['location'];
		}
		$file             = CED_EBAY_DIRPATH . 'admin/ebay/lib/cedGetcategories.php';
		$renderDependency = $this->renderDependency( $file );

		$variationspecificsset  = '';
		$variationspecificsset .= '<VariationSpecificsSet>';

		foreach ( $variation_attribute as $attr_name => $attr_value ) {
			$taxonomy          = $attr_name;
			$attr_name         = str_replace( 'pa_', '', $attr_name );
			$attr_name         = str_replace( 'attribute_', '', $attr_name );
			$attr_name         = wc_attribute_label( $attr_name, $_product );
			$attr_name_by_slug = get_taxonomy( $taxonomy );
			if ( is_object( $attr_name_by_slug ) ) {
				$attr_name = $attr_name_by_slug->label;
			}
			$variationspecificsset .= '<NameValueList>';
			if ( 'Quantity' == $attr_name || 'Type' == $attr_name ) {
				$variationspecificsset .= '<Name>Product ' . $attr_name . '</Name>';
			} else {
				$variationspecificsset .= '<Name>' . $attr_name . '</Name>';
			}
			foreach ( $attr_value as $k => $v ) {
				 $termObj = get_term_by( 'slug', $v, $taxonomy );
				if ( is_object( $termObj ) ) {
					$term_name = $termObj->name;
					if ( strpos( $term_name, '&' ) !== false ) {
						$term_name = str_replace( '&', '&amp;', $term_name );
					}
					$variationspecificsset .= '<Value>' . $term_name . '</Value>';
				} else {
					if ( strpos( $v, '&' ) !== false ) {
						$v = str_replace( '&', '&amp;', $v );
					}
					$variationspecificsset .= '<Value>' . $v . '</Value>';
				}
			}
			$variationspecificsset .= '</NameValueList>';
		}
		$variationspecificsset .= '</VariationSpecificsSet>';
		$variation              = '';
		foreach ( $allVariations as $key => $Id ) {
			$var_attr   = wc_get_product_variation_attributes( $Id );
			$variation .= '<Variation>';
			$mpn        = $this->fetchMetaValueOfProduct( $Id, '_umb_ebay_mpn' );
			$ean        = $this->fetchMetaValueOfProduct( $Id, '_umb_ebay_ean' );
			$isbn       = $this->fetchMetaValueOfProduct( $Id, '_umb_ebay_isbn' );
			$upc        = $this->fetchMetaValueOfProduct( $Id, '_umb_ebay_upc' );

			if ( empty( $mpn ) ) {
				$mpn = 'Does Not Apply';
			}
			if ( empty( $ean ) ) {
				$ean = 'Does Not Apply';
			}
			if ( '' != $ean || '' != $isbn || '' != $upc ) {
				$variation .= '<VariationProductListingDetails>';
				if ( '' != $ean ) {
					$variation .= '<EAN>' . $ean . '</EAN>';
				}
				if ( '' != $isbn ) {
					$variation .= '<ISBN>' . $isbn . '</ISBN>';
				}
				if ( '' != $upc ) {
					$variation .= '<UPC>' . $upc . '</UPC>';
				}
				$variation .= '</VariationProductListingDetails>';
			} else {
				$variation .= '<VariationProductListingDetails>';
				$variation .= '<EAN>Does Not Apply</EAN>';
				$variation .= '</VariationProductListingDetails>';
			}
			$renderDataOnGlobalSettings = get_option( 'ced_ebay_global_settings', false );
			$amount                     = get_post_meta( $Id, '_stock', true );
			$manage_stock               = get_post_meta( $Id, '_manage_stock', true );
			$product_status             = get_post_meta( $Id, '_stock_status', true );

			$listing_stock_type = isset( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_product_stock_type'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_product_stock_type'] : '';
			$listing_stock      = isset( $renderDataOnGlobalSettings[ $userId ]['ced_ebay_listing_stock'] ) ? $renderDataOnGlobalSettings[ $userId ]['ced_ebay_listing_stock'] : '';

			if ( 'yes' != $manage_stock && 'instock' == $product_status ) {
				$renderDataOnGlobalSettings = get_option( 'ced_ebay_global_settings', false );
				if ( ! empty( $listing_stock_type ) && ! empty( $listing_stock ) && 'MaxStock' == $listing_stock_type ) {
					$amount = $listing_stock;
				} else {
					$amount = 1;
				}
			} else {
				if ( 'outofstock' != $product_status ) {
					if ( ! empty( $listing_stock_type ) && ! empty( $listing_stock ) && 'MaxStock' == $listing_stock_type ) {
						if ( $amount > $listing_stock ) {
							$amount = $listing_stock;
						} else {
							$amount = intval( $amount );
							if ( $amount < 1 ) {
								$amount = '0';
							}
						}
					} else {
						$amount = intval( $amount );
						if ( $amount < 1 ) {
							$amount = '0';
						}
					}
				} else {
					$amount = 0;
				}
			}

			$var_prod             = wc_get_product( $Id );
			$dataInGlobalSettings = ! empty( get_option( 'ced_ebay_global_settings', false ) ) ? get_option( 'ced_ebay_global_settings', false ) : '';
			$price_selection      = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_price_option'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_price_option'] : '';
			if ( 'Regular_Price' == $price_selection ) {
				$price = $var_prod->get_regular_price();
			} elseif ( 'Sale_Price' == $price_selection ) {
				$price = $var_prod->get_sale_price();
			} else {
				$price = $var_prod->get_price();
			}
			$profile_price_markup_type = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_profile_price_markup_type' );
			$profile_price_markup      = $this->fetchMetaValueOfProduct( $proIds, '_umb_ebay_profile_price_markup' );
			if ( get_option( 'ced_ebay_global_settings', false ) ) {
				$dataInGlobalSettings = get_option( 'ced_ebay_global_settings', false );
				$price_markup_type    = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup_type'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup_type'] : '';
				$price_markup_value   = isset( $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup'] ) ? $dataInGlobalSettings[ $userId ]['ced_ebay_product_markup'] : '';
			}
			if ( ! empty( $profile_price_markup_type ) && ! empty( $profile_price_markup ) ) {
				if ( 'Fixed_Increase' == $profile_price_markup_type ) {
					$price = $price + $profile_price_markup;
				} elseif ( 'Percentage_Increase' == $profile_price_markup_type ) {
					$price = $price + ( ( $price * $profile_price_markup ) / 100 );
				} elseif ( 'Percentage_Decrease' == $profile_price_markup_type ) {
					$price = $price - ( ( $price * $profile_price_markup ) / 100 );
				} elseif ( 'Fixed_Decrease' == $profile_price_markup_type ) {
					$price = $price - $profile_price_markup;
				}
			} elseif ( ! empty( $price_markup_type ) && ! empty( $price_markup_value ) ) {
				if ( 'Percentage_Increased' == $price_markup_type ) {
					$price = $price + ( ( $price * $price_markup_value ) / 100 );

				} elseif ( 'Fixed_Increased' == $price_markup_type ) {
					$price = $price + $price_markup_value;
				} elseif ( 'Percentage_Decreased' == $price_markup_type ) {
					$price = $price - ( ( $price * $price_markup_value ) / 100 );
				} elseif ( 'Fixed_Decreased' == $price_markup_type ) {
					$price = $price - $price_markup_value;
				}
			}

			$sku = get_post_meta( $Id, '_sku', true );
			if ( empty( $sku ) ) {
				$sku = $Id;
			}

			$var_image_id = $var_prod->get_image_id();
			if ( ! empty( $var_image_id ) ) {
				$var_image_array = wp_get_attachment_image_src( $var_image_id, 'full' );
				$var_image_src   = $var_image_array[0];
			}

			$variation .= '<StartPrice>' . $price . '</StartPrice>
		<Quantity>' . $amount . '</Quantity><SKU>' . $sku . '</SKU>';
			$variation .= '<VariationSpecifics>';
			foreach ( $var_attr as $key => $value ) {
				$taxonomy          = $key;
				$atr_name          = str_replace( 'attribute_', '', $key );
				$taxonomy          = $atr_name;
				$atr_name          = str_replace( 'pa_', '', $atr_name );
				$atr_name          = wc_attribute_label( $atr_name, $_product );
				$termObj           = get_term_by( 'slug', $value, $taxonomy );
				$attr_name_by_slug = get_taxonomy( $taxonomy );

				if ( is_object( $attr_name_by_slug ) ) {
					$atr_name = $attr_name_by_slug->label;
				}

				if ( is_object( $termObj ) ) {
					$term_name = $termObj->name;
					if ( strpos( $term_name, '&' ) !== false ) {
						$term_name = str_replace( '&', '&amp;', $term_name );
					}
					$variation .= '<NameValueList><Name>' . $atr_name . '</Name><Value>' . $term_name . '</Value></NameValueList>';
					if ( ! empty( $additional_image_url ) ) {
						$variation_img[ $atr_name ][] = array(
							'term_name' => $term_name,
							'image_set' => $additional_image_url,
						);
					} elseif ( ! empty( $var_image_src ) ) {
						$variation_img[ $atr_name ][] = array(
							'term_name' => $term_name,
							'image_set' => $var_image_src,
						);
					}
				} else {
					if ( strpos( $value, '&' ) !== false ) {
						$value = str_replace( '&', '&amp;', $value );
					}
					if ( 'Quantity' == $attr_name || 'Type' == $attr_name ) {
						$variation .= '<NameValueList><Name>Product ' . $atr_name . '</Name><Value>' . $value . '</Value></NameValueList>';
					} else {
						$variation .= '<NameValueList><Name>' . $atr_name . '</Name><Value>' . $value . '</Value></NameValueList>';
					}                   if ( ! empty( $additional_image_url ) ) {
						$variation_img[ $atr_name ][] = array(
							'term_name' => $value,
							'image_set' => $additional_image_url,
						);
					} elseif ( ! empty( $var_image_src ) ) {
						$variation_img[ $atr_name ][] = array(
							'term_name' => $value,
							'image_set' => $var_image_src,
						);
					}
				}
			}
			$variation .= '</VariationSpecifics>';
			$variation .= '</Variation>';
		}
		if ( ! empty( $variation_img ) ) {
			$var_img_xml .= '<Pictures>';
			$terms        = array();
			foreach ( $variation_img as $attr_name => $attr_values ) {
				if ( 'Quantity' == $attr_name || 'Type' == $attr_name ) {
					$var_img_xml .= ' <VariationSpecificName>Product ' . $attr_name . '</VariationSpecificName>';

				} else {
						$var_img_xml .= ' <VariationSpecificName>' . $attr_name . '</VariationSpecificName>';

				}               foreach ( $attr_values as $data_attr ) {
					if ( in_array( $data_attr['term_name'], $terms ) ) {
						continue;
					}
					$terms[]      = $data_attr['term_name'];
					$var_img_xml .= '<VariationSpecificPictureSet>';
					$var_img_xml .= '<VariationSpecificValue>' . $data_attr['term_name'] . '</VariationSpecificValue>';
					if ( ! empty( $data_attr['image_set'] ) && is_array( $data_attr['image_set'] ) ) {
						foreach ( $data_attr['image_set'] as $key => $additional_var_images ) {
							$var_img_xml .= '<PictureURL>' . utf8_uri_encode( strtok( $additional_var_images, '?' ) ) . '</PictureURL>';
						}
					} else {
						$var_img_xml .= '<PictureURL>' . utf8_uri_encode( strtok( $data_attr['image_set'], '?' ) ) . '</PictureURL>';
					}
					$var_img_xml .= '</VariationSpecificPictureSet>';
				}
				break;
			}
			$var_img_xml .= '</Pictures>';
		}

		$main_attribute = '<Variations>' . $var_img_xml . $variationspecificsset . $variation . '</Variations>';
		return $main_attribute;
	}


	/*
	 *
	 *function for getting profile data of the product
	 *
	 *
	 */
	public function ced_ebay_getProfileAssignedData( $proIds, $userId ) {
		global $wpdb;
		$productData = wc_get_product( $proIds );
		$product     = $productData->get_data();
		$category_id = isset( $product['category_ids'] ) ? $product['category_ids'] : array();
		if ( ! empty( $category_id ) ) {
			rsort( $category_id );
		}
		$profile_id = get_post_meta( $proIds, 'ced_ebay_profile_assigned' . $userId, true );
		if ( ! empty( $profile_id ) ) {
			$profile_id = $profile_id;
		} else {
			foreach ( $category_id as $key => $value ) {
				$profile_id = get_term_meta( $value, 'ced_ebay_profile_id_' . $userId, true );
				if ( ! empty( $profile_id ) ) {
					break;

				}
			}
		}
		if ( isset( $profile_id ) && ! empty( $profile_id ) && '' != $profile_id ) {
			$this->isProfileAssignedToProduct = true;
			$profile_data                     = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id`=%s", $profile_id ), 'ARRAY_A' );
			if ( is_array( $profile_data ) ) {
				$profile_data = isset( $profile_data[0] ) ? $profile_data[0] : $profile_data;
				$profile_data = isset( $profile_data['profile_data'] ) ? json_decode( $profile_data['profile_data'], true ) : array();

			}
		} else {
			$this->isProfileAssignedToProduct = false;
		}
		$this->profile_data = isset( $profile_data ) ? $profile_data : '';

	}

	/*
	 *
	 *function for getting meta value of the product
	 *
	 *
	 */
	public function fetchMetaValueOfProduct( $proIds, $metaKey ) {
		if ( isset( $this->isProfileAssignedToProduct ) && $this->isProfileAssignedToProduct ) {
			$_product = wc_get_product( $proIds );

			if ( is_bool( $_product ) ) {
				return;
			}

			if ( 'variation' == $_product->get_type() ) {
				$parentId = $_product->get_parent_id();
			} else {
				$parentId = '0';
			}

			if ( ! empty( $this->profile_data ) && isset( $this->profile_data[ $metaKey ] ) ) {
				$profileData     = $this->profile_data[ $metaKey ];
				$tempProfileData = $profileData;
				if ( isset( $tempProfileData['default'] ) && ! empty( $tempProfileData['default'] ) && '' != $tempProfileData['default'] && ! is_null( $tempProfileData['default'] ) && 'null' == $tempProfileData['metakey'] ) {
					if ( '{product_title}' == $tempProfileData['default'] ) {
						if ( ! empty( $parentId ) ) {
							$parent_product = wc_get_product( $parentId );
							$prnt_prd_data  = $parent_product->get_data();
							$prd_title      = $prnt_prd_data['name'];
							$value          = $prd_title;
						} else {
							$prd_data  = $_product->get_data();
							$prd_title = $prd_data['name'];
							$value     = $prd_title;
						}
					} else {
						$value = $tempProfileData['default'];
					}
				} elseif ( isset( $tempProfileData['metakey'] ) && ! empty( $tempProfileData['metakey'] ) && 'null' != $tempProfileData['metakey'] ) {

					if ( false !== strpos( $tempProfileData['metakey'], 'umb_pattr_' ) ) {

						$wooAttribute = explode( 'umb_pattr_', $tempProfileData['metakey'] );
						$wooAttribute = end( $wooAttribute );

						if ( 'variation' == $_product->get_type() ) {
							$var_product = wc_get_product( $parentId );
							$attributes  = $var_product->get_variation_attributes();
							if ( isset( $attributes[ 'attribute_pa_' . $wooAttribute ] ) && ! empty( $attributes[ 'attribute_pa_' . $wooAttribute ] ) ) {
								$wooAttributeValue = $attributes[ 'attribute_pa_' . $wooAttribute ];
								if ( '0' != $parentId ) {
									$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
								} else {
									$product_terms = get_the_terms( $proIds, 'pa_' . $wooAttribute );
								}
							} else {
								$wooAttributeValue = $var_product->get_attribute( 'pa_' . $wooAttribute );
								$wooAttributeValue = explode( ',', $wooAttributeValue );
								$wooAttributeValue = $wooAttributeValue[0];

								if ( '0' != $parentId ) {
									$product_terms = get_the_terms( $parentId, 'pa_' . $wooAttribute );
								} else {
									$product_terms = get_the_terms( $proIds, 'pa_' . $wooAttribute );
								}
							}
							if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
								foreach ( $product_terms as $tempkey => $tempvalue ) {
									if ( $tempvalue->slug == $wooAttributeValue ) {
										$wooAttributeValue = $tempvalue->name;
										break;
									}
								}
								if ( isset( $wooAttributeValue ) && ! empty( $wooAttributeValue ) ) {
									$value = $wooAttributeValue;
								} else {
									$value = get_post_meta( $proIds, $metaKey, true );
								}
							} else {
								$value = get_post_meta( $proIds, $metaKey, true );
							}
						} else {
							$wooAttributeValue = $_product->get_attribute( 'pa_' . $wooAttribute );
							$product_terms     = get_the_terms( $proIds, 'pa_' . $wooAttribute );
							if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
								foreach ( $product_terms as $tempkey => $tempvalue ) {
									if ( $tempvalue->slug == $wooAttributeValue ) {
										$wooAttributeValue = $tempvalue->name;
										break;
									}
								}
								if ( isset( $wooAttributeValue ) && ! empty( $wooAttributeValue ) ) {
									$value = $wooAttributeValue;
								} else {
									$value = get_post_meta( $proIds, $metaKey, true );
								}
							} else {
								$value = get_post_meta( $proIds, $metaKey, true );
							}
						}
					} elseif ( false !== strpos( $tempProfileData['metakey'], 'ced_cstm_attrb_' ) ) {
						$custom_prd_attrb = explode( 'ced_cstm_attrb_', $tempProfileData['metakey'] );
						$custom_prd_attrb = end( $custom_prd_attrb );
						$wooAttribute     = $custom_prd_attrb;
						if ( ! empty( $wooAttribute ) ) {
							if ( 'variation' == $_product->get_type() ) {
								$var_product = wc_get_product( $parentId );
								$attributes  = $var_product->get_variation_attributes();
								if ( isset( $attributes[ 'attribute_' . $wooAttribute ] ) && ! empty( $attributes[ 'attribute_' . $wooAttribute ] ) ) {
									$wooAttributeValue = $attributes[ 'attribute_' . $wooAttribute ];
									if ( '0' != $parentId ) {
										$product_terms = get_the_terms( $parentId, $wooAttribute );
									} else {
										$product_terms = get_the_terms( $proIds, $wooAttribute );
									}
								} else {
									$wooAttributeValue = $var_product->get_attribute( $wooAttribute );
									$wooAttributeValue = explode( ',', $wooAttributeValue );
									$wooAttributeValue = $wooAttributeValue[0];

									if ( '0' != $parentId ) {
										$product_terms = get_the_terms( $parentId, $wooAttribute );
									} else {
										$product_terms = get_the_terms( $proIds, $wooAttribute );
									}
								}
								if ( is_array( $product_terms ) && ! empty( $product_terms ) ) {
									foreach ( $product_terms as $tempkey => $tempvalue ) {
										if ( $tempvalue->slug == $wooAttributeValue ) {
											$wooAttributeValue = $tempvalue->name;
											break;
										}
									}
									if ( isset( $wooAttributeValue ) && ! empty( $wooAttributeValue ) ) {
										$value = $wooAttributeValue;
									} else {
										$value = get_post_meta( $proIds, $metaKey, true );
									}
								} else {
									$value = get_post_meta( $proIds, $metaKey, true );
								}
							} else {
								$wooAttributeValue = $_product->get_attribute( $wooAttribute );
								if ( ! empty( $wooAttributeValue ) ) {
									$value = $wooAttributeValue;
								}
							}
						}
					} elseif ( false !== strpos( $tempProfileData['metakey'], 'ced_product_tags' ) ) {
						$terms             = get_the_terms( $proIds, 'product_tag' );
						$product_tags_list = array();
						if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
							foreach ( $terms as $term ) {
								$product_tags_list[] = $term->name;
							}
						}
						if ( ! empty( $product_tags_list ) ) {
							$value = implode( ',', $product_tags_list );
						} else {
							$value = '';
						}
					} elseif ( false !== strpos( $tempProfileData['metakey'], 'acf_' ) ) {
						$acf_field        = explode( 'acf_', $tempProfileData['metakey'] );
						$acf_field        = end( $acf_field );
						$acf_field_object = get_field_object( $acf_field, $proIds );
						$value            = $acf_field_object['value'];
					} else {
						$value = get_post_meta( $proIds, $tempProfileData['metakey'], true );
						if ( '_thumbnail_id' == $tempProfileData['metakey'] ) {
							$value = wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $proIds, '_thumbnail_id', true ), 'thumbnail' ) : '';
						}
						if ( ! isset( $value ) || empty( $value ) || '' == $value || is_null( $value ) || '0' == $value || 'null' == $value ) {
							if ( '0' != $parentId ) {

								$value = get_post_meta( $parentId, $tempProfileData['metakey'], true );
								if ( '_thumbnail_id' == $tempProfileData['metakey'] ) {
									$value = wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'thumbnail' ) ? wp_get_attachment_image_url( get_post_meta( $parentId, '_thumbnail_id', true ), 'thumbnail' ) : '';
								}

								if ( ! isset( $value ) || empty( $value ) || '' == $value || is_null( $value ) ) {
									$value = get_post_meta( $proIds, $metaKey, true );

								}
							} else {
								$value = get_post_meta( $proIds, $metaKey, true );
							}
						}
					}
				} else {
					$value = get_post_meta( $proIds, $metaKey, true );
				}
			} else {
				$value = get_post_meta( $proIds, $metaKey, true );
			}
			if ( '' == $value ) {
				if ( isset( $tempProfileData['default'] ) && ! empty( $tempProfileData['default'] ) && '' != $tempProfileData['default'] && ! is_null( $tempProfileData['default'] ) ) {
					$value = $tempProfileData['default'];
				}
			}
			return $value;
		}

	}

	public function array2XML( $xml_obj, $array ) {
		foreach ( $array as $key => $value ) {
			if ( is_numeric( $key ) ) {
				$key = $key;
			}
			if ( is_array( $value ) ) {
				$node = $xml_obj->addChild( $key );
				$this->array2XML( $node, $value );
			} else {
				$xml_obj->addChild( $key, htmlspecialchars( $value ) );
			}
		}
	}
	public function renderDependency( $file ) {
		if ( null != $file || '' != $file ) {
			require_once "$file";
			return true;
		}
		return false;
	}


	public function ced_ebay_prepareDataForReListing( $userId, $proIDs = array() ) {
		$shop_data = ced_ebay_get_shop_data( $userId );
		if ( ! empty( $shop_data ) ) {
			$siteID          = $shop_data['site_id'];
				$token       = $shop_data['access_token'];
				$getLocation = $shop_data['location'];
		}
		$response = '<?xml version="1.0" encoding="utf-8"?>
			<RelistFixedPriceItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
				<RequesterCredentials>
					<eBayAuthToken>' . $token . '</eBayAuthToken>
				</RequesterCredentials><Item>';
		foreach ( $proIDs as $key => $value ) {
			$listing_id = get_post_meta( $value, '_ced_ebay_relist_item_id_' . $userId, true );
			$response  .= '<ItemID>' . $listing_id . '</ItemID>';
		}
		$response .= '</Item></RelistFixedPriceItemRequest>';
		return $response;
	}

}
