<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
$shop_name = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
$shop_data = ced_ebay_get_shop_data( $shop_name );
if ( ! empty( $shop_data ) ) {
	$siteID      = $shop_data['site_id'];
	$token       = $shop_data['access_token'];
	$getLocation = $shop_data['location'];
}


if ( isset( $_POST['ced_ebay_shipping_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ced_ebay_shipping_nonce'] ), 'ced_ebay_shipping_page_nonce' ) ) {
	if ( isset( $_POST['saveShippingPolicies'] ) ) {
		$sanitized_array         = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		$domesticShippingDetails = isset( $sanitized_array['shippingdetails']['domesticShippingService'] ) ? ( $sanitized_array['shippingdetails']['domesticShippingService'] ) : array();

		$intlShippingDetails                 = isset( $sanitized_array['shippingdetails']['internationalShippingService'] ) ? ( $sanitized_array['shippingdetails']['internationalShippingService'] ) : array();
		$domesticShippingDetails['services'] = array_filter( $domesticShippingDetails['services'] );
		$intlShippingDetails['services']     = array_filter( $intlShippingDetails['services'] );
		$intlShippingDetails['services']     = array_values( $intlShippingDetails['services'] );
		$domesticShippingDetails['services'] = array_values( $domesticShippingDetails['services'] );
		$shippingDetails                     = ( $sanitized_array['shippingdetails'] );

		if ( is_array( $shippingDetails ) && ! empty( $shippingDetails ) ) {
			$details['shippingDetails'] = array(
				'domesticShippingService'      => $domesticShippingDetails,
				'internationalShippingService' => $intlShippingDetails,
				'serviceType'                  => $shippingDetails['serviceType'],
				'postal_code'                  => $shippingDetails['postal_code'],
				'exclusion_list'               => $shippingDetails['exclusion_list'],
			);

			if ( isset( $shippingDetails['shippingweightminor'] ) ) {
				$details['shippingDetails']['shippingweightminor'] = $shippingDetails['shippingweightminor'];
			}

			if ( isset( $shippingDetails['shippingweightmajor'] ) ) {
				$details['shippingDetails']['shippingweightmajor'] = $shippingDetails['shippingweightmajor'];
			}
			if ( isset( $shippingDetails['domesticPackagingCost'] ) ) {
				$details['shippingDetails']['domesticPackagingCost'] = $shippingDetails['domesticPackagingCost'];
			}
			if ( isset( $shippingDetails['internationalPackagingCost'] ) ) {
				$details['shippingDetails']['internationalPackagingCost'] = $shippingDetails['internationalPackagingCost'];
			}

			$template_name = $shippingDetails['template_name'];
			global $wpdb;
			$table_name = $wpdb->prefix . 'ced_ebay_shipping';
			if ( isset( $_GET['template_id'] ) ) {
				$edit_id = sanitize_text_field( $_GET['template_id'] );
				$wpdb->update(
					$table_name,
					array(
						'shipping_name' => $template_name,
						'user_id'       => $shop_name,
						'shipping_data' => json_encode( $details ),
					),
					array( 'id' => $edit_id ),
					array( '%s' )
				);
			} else {
				$wpdb->insert(
					$table_name,
					array(
						'shipping_name' => $template_name,
						'user_id'       => $shop_name,
						'shipping_data' => json_encode( $details ),
					),
					array( '%s' )
				);

				$edit_id = $wpdb->insert_id;
			}
		}
		header( 'Location: ' . get_admin_url() . 'admin.php?page=ced_ebay&section=accounts-view&part=shipping&user_id=' . $shop_name );
		exit();
	}
}
if ( isset( $_GET['template_id'] ) ) {
	$edit_id = isset( $_GET['template_id'] ) ? sanitize_text_field( $_GET['template_id'] ) : '';
	$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';

	global $wpdb;

	$template_data = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$wpdb->prefix}ced_ebay_shipping` WHERE `id`=%d AND `user_id`=%s", $edit_id, $user_id ), 'ARRAY_A' );

	if ( is_array( $template_data ) ) {
		$template_data = isset( $template_data[0] ) ? $template_data[0] : $template_data;
	}
	$savedShippingDetails = isset( $template_data['shipping_data'] ) ? json_decode( $template_data['shipping_data'], true ) : array();

	$savedShippingDetails = $savedShippingDetails['shippingDetails'];
}
$class = '';
if ( isset( $_GET['process'] ) && 'template_edit' == $_GET['process'] ) {
	$class = 'ced-ebay-popup-show';
}
?>
<div id="ced-ebay-popup" class="<?php echo esc_attr( $class ); ?> overlay">
	<div class="ced-ebay-popup-wrap">
	<a class="ced_ebay_site_shipping_template_close" href="#">&times;</a>

		<div class="ced-ebay-popup-content">

<div class="ced_ebay_site_new_shipping_template_wrapper">

	<div class="ced_ebay_site_shipping_template_table_wrapper">

		<form method="post">
		<table class="ced_ebay_site_shipping_template_table">

			<tbody>
				<tr>
					<th><?php esc_attr_e( 'Shipping Template Name', 'ced-umb-ebay' ); ?></th>
					<td>
						<?php
						$template_name = '';
						if ( isset( $template_data['shipping_name'] ) ) {
							$template_name = $template_data['shipping_name'];
						}
						?>
						<input type="text" name = "shippingdetails[template_name]" value = "<?php echo esc_attr( $template_name ); ?>">
					</td>
				</tr>
				<tr>
					<th><?php esc_attr_e( 'Service Type', 'ced-umb-ebay' ); ?></th>
					<td class="manage-column">
						<select id="ced_ebay_shipping_service_type" name="shippingdetails[serviceType]">
							<?php
							$flateChecked = '';
							if ( isset( $savedShippingDetails['serviceType'] ) ) {
								if ( 'Flat' == $savedShippingDetails['serviceType'] ) {
									$flateChecked = 'selected';
								}
							}
							$calculatedChecked = '';
							if ( isset( $savedShippingDetails['serviceType'] ) ) {
								if ( 'Calculated' == $savedShippingDetails['serviceType'] ) {
									$calculatedChecked = 'selected';
								}
							}
							?>
							<option <?php echo esc_attr( $flateChecked ); ?> value="Flat"><?php esc_attr_e( 'Flat', 'ced-umb-ebay' ); ?></option>
							<option <?php echo esc_attr( $calculatedChecked ); ?> value="Calculated"><?php esc_attr_e( 'Calculated', 'ced-umb-ebay' ); ?></option>
						</select>
					</td>
				</tr>
				<tr class="ced_ebay_shipping_postal_code">
					<th><?php esc_attr_e( 'Postal Code', 'ced-umb-ebay' ); ?></th>
					<td class="manage-column">
						<?php
						$postal_code = '';
						if ( isset( $savedShippingDetails['postal_code'] ) ) {
							$postal_code = $savedShippingDetails['postal_code'];
						}
						?>
						<input type="text" name = "shippingdetails[postal_code]" value = "<?php echo esc_attr( $postal_code ); ?>">
					</td>
				</tr>

				<?php
				// if( isset( $savedShippingDetails['serviceType'] ) && $savedShippingDetails['serviceType'] == "Calculated" )
				// {
				$shippingweightmajor = isset( $savedShippingDetails['shippingweightmajor'] ) ? $savedShippingDetails['shippingweightmajor'] : '';
				$shippingweightminor = isset( $savedShippingDetails['shippingweightminor'] ) ? $savedShippingDetails['shippingweightminor'] : '';
				?>
				<tr class="ced_ebay_shipping_weight_range_row">
				<th>Package Weight Range <span style="color:red;">(Only numberic)</span></th>
					<td class="manage-column ced_ebay_ship_weight_minor">
						<input type="text" placeholder="Package Min. Weight" value="<?php echo esc_attr( $shippingweightminor ); ?>" name = "shippingdetails[shippingweightminor]">
					</td>
					<td class="manage-column ced_ebay_ship_weight_major">
						<input type="text" placeholder="Package Max. Weight" value="<?php echo esc_attr( $shippingweightmajor ); ?>" name = "shippingdetails[shippingweightmajor]" value = "">
					</td>
					<td>
						<h3><?php echo esc_html( get_option( 'woocommerce_weight_unit' ) ); ?></h3>
					</td>
				</tr>
				<?php
				// }
				if ( isset( $savedShippingDetails['serviceType'] ) && 'Calculated' == $savedShippingDetails['serviceType'] ) {
					$domesticPackagingCost      = isset( $savedShippingDetails['domesticPackagingCost'] ) ? $savedShippingDetails['domesticPackagingCost'] : '';
					$internationalPackagingCost = isset( $savedShippingDetails['internationalPackagingCost'] ) ? $savedShippingDetails['internationalPackagingCost'] : '';
					?>

					<tr class="ced-ebay-domestic-handling-cost-row">
						<th><?php esc_attr_e( 'Domestic Package Handling Cost', 'ced-umb-ebay' ); ?></th>
						<td>
							<input type="text" placeholder="<?php esc_attr_e( 'Package Handling Cost', 'ced-umb-ebay' ); ?>" name = "shippingdetails[domesticPackagingCost]" value="<?php echo esc_attr( $domesticPackagingCost ); ?>">
						</td>
					</tr>
					<tr class="ced-ebay-international-handling-cost-row">
						<th><?php esc_attr_e( 'International Package Handling Cost', 'ced-umb-ebay' ); ?></th>
						<td>
							<input type="text" placeholder="<?php esc_attr_e( 'Package Handling Cost', 'ced-umb-ebay' ); ?>" name = "shippingdetails[internationalPackagingCost]" value="<?php echo esc_attr( $internationalPackagingCost ); ?>">
						</td>
					</tr>
					<?php
				}
				$selected = '';
				require_once CED_EBAY_DIRPATH . 'admin/ebay/lib/ebayUpload.php';

				$shippingDetails = array();
				if ( '' != $token && null != $token ) {
					$mainXml            = '<?xml version="1.0" encoding="utf-8"?>
					<GeteBayDetailsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
					<RequesterCredentials>
					<eBayAuthToken>' . $token . '</eBayAuthToken>
					</RequesterCredentials>
					<DetailName>ShippingCarrierDetails</DetailName>
					<DetailName>ShippingServiceDetails</DetailName>
					</GeteBayDetailsRequest> ';
					$ebayUploadInstance = EbayUpload::get_instance( $siteID, $token );
					$mode_of_operation  = get_option( 'ced_ebay_mode_of_operation', '' );
					if ( 'sandbox' == $mode_of_operation ) {
						$shippingDetails = $ebayUploadInstance->get_shipping_return_service_for_site( $mainXml );
					} else {
						$shippingDetails = $ebayUploadInstance->get_shipping_return_service_for_site( $mainXml );
					}
				}
				?>

			</tbody>
			<tbody>
				<?php
				if ( is_array( $shippingDetails ) && ! empty( $shippingDetails ) && isset( $shippingDetails['ShippingServiceDetails'] ) ) {
					if ( isset( $savedShippingDetails['domesticShippingService'] ) ) {
						foreach ( $savedShippingDetails['domesticShippingService']['services'] as $key1 => $value1 ) {
							?>
							<tr class="ced_ebay_domestic_shipping_service_row">
								<th><?php esc_attr_e( 'Domestic Shipping Service', 'ced-umb-ebay' ); ?></th>
								<td class="manage-column umb_ebay_shipping-service-based-on-site">
									<select name="shippingdetails[domesticShippingService][services][]">
										<option value="">--Select--</option>
										<?php
										if ( 'Success' == $shippingDetails['Ack'] || 'Warning' == $shippingDetails['Ack'] ) {
											foreach ( $shippingDetails['ShippingServiceDetails'] as $key => $service ) {
												if ( isset( $service['ValidForSellingFlow'] ) && 'true' === $service['ValidForSellingFlow'] && ! isset( $service['InternationalService'] ) ) {
													if ( isset( $value1 ) && $value1 == $service['ShippingService'] ) {
														$selected = 'selected';
													}
													?>
													<option
													<?php
													if ( ! empty( $service['ServiceType'] ) ) {
														if ( is_array( $service['ServiceType'] ) && count( $service['ServiceType'] ) == 2 ) {
															$serviceType = 'Flat, Calculated';
														} else {
															$serviceType = $service['ServiceType'];
														}
													}
													if ( 'selected' == $selected ) {
														echo 'selected';}
													?>
													 value="<?php echo esc_attr( $service['ShippingService'] ); ?>"><?php echo esc_attr( $service['Description'] . ' ( ' . $serviceType . ' )' ); ?></option>
													<?php
													$selected = '';
												}
											}
										}
										?>
									</select>
								</td>
								<?php
								if ( isset( $savedShippingDetails['serviceType'] ) && 'Calculated' != $savedShippingDetails['serviceType'] ) {
									?>
									<td class="manage-column ced_ebay_dom_ship_cost">
										<?php
										$shippingCost = '';
										if ( isset( $savedShippingDetails['domesticShippingService']['shippingcost'][ $key1 ] ) ) {
											$shippingCost = $savedShippingDetails['domesticShippingService']['shippingcost'][ $key1 ];
										}
										?>
										<input type="text" placeholder="<?php esc_attr_e( 'Shipping Cost', 'ced-umb-ebay' ); ?>" name = "shippingdetails[domesticShippingService][shippingcost][]" value = "<?php echo esc_attr( $shippingCost ); ?>">
									</td>
									<td class="manage-column ced_ebay_dom_add_ship_cost">
										<?php
										$shippingaddCost = '';
										if ( isset( $savedShippingDetails['domesticShippingService']['shippingaddcost'][ $key1 ] ) ) {
											$shippingaddCost = $savedShippingDetails['domesticShippingService']['shippingaddcost'][ $key1 ];
										}
										?>
										<input type="text" placeholder="<?php esc_attr_e( 'Add. Cost', 'ced-umb-ebay' ); ?>" name = "shippingdetails[domesticShippingService][shippingaddcost][]" value = "<?php echo esc_attr( $shippingaddCost ); ?>">
									</td>
									<?php
								}
								?>
							</tr>
							<?php
						}
					}
					?>
					<tr class="ced_ebay_domestic_shipping_service_row">
						<th><?php esc_attr_e( 'Domestic Shipping Service', 'ced-umb-ebay' ); ?></th>
						<td   class="manage-column umb_ebay_shipping-service-based-on-site">
							<select name="shippingdetails[domesticShippingService][services][]">
								<option value="">--Select--</option>
								<?php
								foreach ( $shippingDetails['ShippingServiceDetails'] as $key => $service ) {
									if ( isset( $service['ValidForSellingFlow'] ) && 'true' === $service['ValidForSellingFlow'] && ! isset( $service['InternationalService'] ) ) {
										if ( ! empty( $service['ServiceType'] ) ) {
											if ( is_array( $service['ServiceType'] ) && count( $service['ServiceType'] ) == 2 ) {
												$serviceType = 'Flat, Calculated';
											} else {
												$serviceType = $service['ServiceType'];
											}
										}
										?>
										<option value="<?php echo esc_attr( $service['ShippingService'] ); ?>"><?php echo esc_attr( $service['Description'] . ' ( ' . $serviceType . ' )' ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</td>
						<?php
						if ( ! isset( $savedShippingDetails['serviceType'] ) || ( isset( $savedShippingDetails['serviceType'] ) && 'Calculated' != $savedShippingDetails['serviceType'] ) ) {
							?>
							<td class="manage-column ced_ebay_dom_ship_cost">
								<input type="text" placeholder="<?php esc_attr_e( 'Shipping Cost', 'ced-umb-ebay' ); ?>" name = "shippingdetails[domesticShippingService][shippingcost][]">
							</td>
							<td class="manage-column ced_ebay_dom_add_ship_cost">
								<input type="text" placeholder="<?php esc_attr_e( 'Add. Cost', 'ced-umb-ebay' ); ?>" name = "shippingdetails[domesticShippingService][shippingaddcost][]" value = "">
							</td>
							<!-- <td></td> -->
							<?php
						}
						?>
						<td>
							<input type="button" class="ced_ebay_add_domestic_shipping_service_row button button-primary" value="Add More"></input>
							<input type="button" class="ced_ebay_remove_domestic_shipping_row_button button button-primary" style="display:none;" value="Remove"></input>
						</td>
					</tr>
					<tr></tr>
					<?php
				} else {
					?>
					<tr>
						<td>
							<?php
							if ( empty( $shippingDetails ) ) {
								esc_attr_e( 'Please Select the Location of Store to select shipping templates', 'ced-umb-ebay' );
							} elseif ( 'Success' != $shippingDetails['Ack'] ) {
								esc_attr_e( 'Please try again later. Internal Error to the Api', 'ced-umb-ebay' );
							}
							?>
						</td>

					</tr>
					<?php
				}
				?>
				<?php
				if ( is_array( $shippingDetails ) && ! empty( $shippingDetails ) && isset( $shippingDetails['ShippingServiceDetails'] ) ) {
					if ( isset( $savedShippingDetails['internationalShippingService'] ) ) {
						$key1 = 0;
						foreach ( $savedShippingDetails['internationalShippingService']['services'] as $key1 => $value1 ) {
							?>
							<tr class="ced_ebay_international_shipping_service_row">
								<th><?php esc_attr_e( 'International Shipping Service', 'ced-umb-ebay' ); ?></th>
								<td   class="manage-column umb_ebay_shipping-service-based-on-site" >
									<select name="shippingdetails[internationalShippingService][services][]">
										<option value="">--Select--</option>
										<?php
										if ( 'Success' == $shippingDetails['Ack'] || 'Warning' == $shippingDetails['Ack'] ) {
											foreach ( $shippingDetails['ShippingServiceDetails'] as $key => $service ) {
												if ( isset( $service['ValidForSellingFlow'] ) && 'true' === $service['ValidForSellingFlow'] && isset( $service['InternationalService'] ) ) {
													if ( isset( $value1 ) && $value1 == $service['ShippingService'] ) {
														$selected = 'selected';
													}
													?>
													<option
													<?php
													if ( ! empty( $service['ServiceType'] ) ) {
														if ( is_array( $service['ServiceType'] ) && count( $service['ServiceType'] ) == 2 ) {
															$serviceType = 'Flat, Calculated';
														} else {
															$serviceType = $service['ServiceType'];
														}
													}
													if ( 'selected' == $selected ) {
														echo 'selected';}
													?>
													 value="<?php echo esc_attr( $service['ShippingService'] ); ?>"><?php echo esc_attr( $service['Description'] . ' ( ' . $serviceType . ' )' ); ?></option>
													<?php
													$selected = '';
												}
											}
										}
										?>
									</select>
								</td>
								<?php
								if ( isset( $savedShippingDetails['serviceType'] ) && 'Calculated' != $savedShippingDetails['serviceType'] ) {
									?>
									<td class="manage-column ced_ebay_intl_ship_cost">
										<?php
										$shippingCost = '';
										if ( isset( $savedShippingDetails['internationalShippingService']['shippingcost'][ $key1 ] ) ) {
											$shippingCost = $savedShippingDetails['internationalShippingService']['shippingcost'][ $key1 ];
										}
										?>
										<input type="text" placeholder="<?php esc_attr_e( 'Shipping Cost', 'ced-umb-ebay' ); ?>" name = "shippingdetails[internationalShippingService][shippingcost][]" value = "<?php echo esc_attr( $shippingCost ); ?>">
									</td>
									<td class="manage-column ced_ebay_intl_add_ship_cost">
										<?php
										$shippingaddCost = '';
										if ( isset( $savedShippingDetails['internationalShippingService']['shippingaddcost'][ $key1 ] ) ) {
											$shippingaddCost = $savedShippingDetails['internationalShippingService']['shippingaddcost'][ $key1 ];
										}
										?>
										<input type="text" placeholder="<?php esc_attr_e( 'Add. Cost', 'ced-umb-ebay' ); ?>" name = "shippingdetails[internationalShippingService][shippingaddcost][]" value = "<?php echo esc_attr( $shippingaddCost ); ?>">
									</td>
									<?php
								}
								?>
								<td>
									<select multiple="multiple" class="select_location ced_etsy_ship_to_locations" name="shippingdetails[internationalShippingService][locations][<?php echo esc_attr( $key1 ); ?>][]" data-placeholder="Select locations">
										<option value="CN"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'CN', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>China</option>
										<option value="RU"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'RU', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>Russian Federation</option>
										<option value="CA"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'CA', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>Canada</option>
										<option value="BR"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'BR', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>Brazil</option>
										<option value="DE"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'DE', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>Germany</option>
										<option value="FR"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'FR', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>France</option>
										<option value="Europe"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'Europe', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>Europe</option>
										<option value="GB"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'GB', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>United Kingdom</option>
										<option value="Americas"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'Americas', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>N. and S. America</option>
										<option value="Asia"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'Asia', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>Asia</option>
										<option value="AU"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'AU', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>Australia</option>
										<option value="MX"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'MX', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>Mexico</option>
										<option value="Worldwide"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'Worldwide', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>Worldwide</option>
										<option value="JP"
										<?php
										if ( is_array( $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) && in_array( 'JP', $savedShippingDetails['internationalShippingService']['locations'][ $key1 ] ) ) {
																echo 'selected';}
										?>
										>Japan</option>
									</select>
								</td>
							</tr>
							<?php
						}
					}
					?>
					<?php
					if ( isset( $key1 ) && $key1 > 0 ) {
						++$key1;
					} else {
						$key1 = 0;
					}
					?>
					<tr class="ced_ebay_international_shipping_service_row">
						<th><?php esc_attr_e( 'International Shipping Service', 'ced-umb-ebay' ); ?></th>
						<td   class="manage-column umb_ebay_shipping-service-based-on-site">
							<select name="shippingdetails[internationalShippingService][services][]">
								<option value="">--Select--</option>
								<?php
								foreach ( $shippingDetails['ShippingServiceDetails'] as $key => $service ) {
									if ( isset( $service['ValidForSellingFlow'] ) && 'true' === $service['ValidForSellingFlow'] && isset( $service['InternationalService'] ) ) {
										if ( ! empty( $service['ServiceType'] ) ) {
											if ( is_array( $service['ServiceType'] ) && count( $service['ServiceType'] ) == 2 ) {
												$serviceType = 'Flat, Calculated';
											} else {
												$serviceType = $service['ServiceType'];
											}
										}
										?>
										<option value="<?php echo esc_attr( $service['ShippingService'] ); ?>"><?php echo esc_attr( $service['Description'] . ' ( ' . $serviceType . ' )' ); ?></option>
										<?php
									}
								}
								?>
							</select>
						</td>
						<?php
						if ( ! isset( $savedShippingDetails['serviceType'] ) || ( isset( $savedShippingDetails['serviceType'] ) && 'Calculated' != $savedShippingDetails['serviceType'] ) ) {
							?>
							<td class="ced_ebay_intl_ship_cost">
								<input type="text" placeholder="<?php esc_attr_e( 'Shipping Cost', 'ced-umb-ebay' ); ?>" name = "shippingdetails[internationalShippingService][shippingcost][]">
							</td>
							<td class="ced_ebay_intl_add_ship_cost">
								<input type="text" placeholder="<?php esc_attr_e( 'Add. Cost', 'ced-umb-ebay' ); ?>" name = "shippingdetails[internationalShippingService][shippingaddcost][]" value = "">
							</td>
							<?php
						}
						?>
						<td>
							<select multiple="multiple" class="select_location ced_etsy_ship_to_locations" name="shippingdetails[internationalShippingService][locations][<?php echo esc_attr( $key1 ); ?>][]" data-placeholder="Select locations">
								<option value="CN">China</option>
								<option value="RU">Russian Federation</option>
								<option value="CA">Canada</option>
								<option value="BR">Brazil</option>
								<option value="DE">Germany</option>
								<option value="FR">France</option>
								<option value="Europe">Europe</option>
								<option value="GB">United Kingdom</option>
								<option value="Americas">N. and S. America</option>
								<option value="Asia">Asia</option>
								<option value="AU">Australia</option>
								<option value="MX">Mexico</option>
								<option value="Worldwide">Worldwide</option>
								<option value="JP">Japan</option>
							</select>
						</td>
						<td>
							<input type="button" data-add = <?php echo esc_attr( $key1 ); ?> class="ced_ebay_add_intl_shipping_service_row button button-primary" value="Add More"></input>
							<input type="button" data-add = <?php echo esc_attr( $key1 ); ?> class="ced_ebay_remove_intl_shipping_service_row button button-primary" style="display:none;" value="Remove"></input>

						</td>
					</tr>
					<?php
				}
				?>

		</tbody>

	</table>
<?php wp_nonce_field( 'ced_ebay_shipping_page_nonce', 'ced_ebay_shipping_nonce' ); ?>
	<!-- <p class="ced_umb_ebay_button_right">
		<input class="button button-ced_umb_ebay" value="<?php esc_attr_e( 'Save Details', 'ced-umb-ebay' ); ?>" name="saveShippingPolicies" type="submit">
	</p> -->
	<div class="ced_ebay_shipping_button_wrap">
	<input name="saveShippingPolicies" class="button button-primary button-large"  value="Save Template" type="submit">
	</div>

</form>
</div>
	</div>
</div>
</div>
</div>
<script>
	jQuery(document).ready(function() {
	jQuery(".ced_ebay_exclusion_countries_selection").selectWoo();
});
</script>
