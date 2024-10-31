<?php

// phpinfo();
// die;
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

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class EBayListProducts extends WP_List_Table {


	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'ced-ebay-product', 'ebay-integration-for-woocommerce' ), // singular name of the listed records
				'plural'   => __( 'ced-ebay-products', 'ebay-integration-for-woocommerce' ), // plural name of the listed records
				'ajax'     => true, // does this table support ajax?
			)
		);

	}

	/**
	 *
	 * Function for preparing data to be displayed
	 */

	public function prepare_items() {

		global $wpdb;
		$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';

		if ( isset( $_POST['ced_ebay_view_entries_product_section'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ced_ebay_view_entries_product_section'] ), 'ced_ebay_view_entries_product_section_nonce' ) ) {
			$per_page_preference = ! empty( $_POST['ced_ebay_view_entries_prod_section_input'] ) ? sanitize_text_field( $_POST['ced_ebay_view_entries_prod_section_input'] ) : 0;
			update_option( 'ced_ebay_product_section_per_page_' . $user_id, $per_page_preference );
		}

		$per_page = ! empty( get_option( 'ced_ebay_product_section_per_page_' . $user_id ) ) ? get_option( 'ced_ebay_product_section_per_page_' . $user_id ) : 10;

		$post_type = 'product';
		$columns   = $this->get_columns();
		$hidden    = array();
		$sortable  = $this->get_sortable_columns();

		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}
		$this->items = self::ced_ebay_get_product_details( $per_page, $current_page, $post_type );
		$count       = self::get_count( $per_page, $current_page );

		// Set the pagination
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			// $this->items = self::ced_ebay_get_product_details( $per_page, $current_page ,$post_type  );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}

	/**
	 *
	 * Function for get product data
	 */
	public function ced_ebay_get_product_details( $per_page = '', $page_number = '', $post_type = '' ) {
		$filterFile = CED_EBAY_DIRPATH . 'admin/partials/products-filters.php';
		if ( file_exists( $filterFile ) ) {
			require_once $filterFile;
		}

		$instanceOf_FilterClass = new FilterClass();

		$args = $this->GetFilteredData( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) || isset( $args['s'] ) ) {
			$args = $args;
		} elseif ( ! isset( $args['prodID'] ) && ! isset( $args['search_by_sku'] ) ) {
			$args = array(
				'post_type'      => $post_type,
				'posts_per_page' => $per_page,
				'paged'          => $page_number,
			);
		} elseif ( isset( $args['search_by_sku'] ) ) {
			$args = array(
				'post_type'           => 'product',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => 1,
				'meta_key'            => '_sku',
				'meta_value'          => $args['search_by_sku'],
				'meta_compare'        => 'LIKE',
			);
		}

		if ( isset( $args['prodID'] ) ) {
			$prod           = new stdClass();
			$prod->ID       = $args['prodID'];
			$product_data[] = $prod;
		} else {
			$loop         = new WP_Query( $args );
			$product_data = $loop->posts;
		}

		$woo_categories = get_terms( 'product_cat', array( 'hide_empty' => false ) );
		$woo_products   = array();
		foreach ( $product_data as $key => $value ) {
			$get_product_data = wc_get_product( $value->ID );
			$get_product_data = $get_product_data->get_data();
			if ( ! empty( $get_product_data['category_ids'] ) ) {
				rsort( $get_product_data['category_ids'] );
			}
			$woo_products[ $key ]['category_id']  = isset( $get_product_data['category_ids'] ) ? $get_product_data['category_ids'] : '';
			$woo_products[ $key ]['id']           = $value->ID;
			$woo_products[ $key ]['name']         = $get_product_data['name'];
			$woo_products[ $key ]['stock']        = $get_product_data['stock_quantity'];
			$woo_products[ $key ]['stock_status'] = $get_product_data['stock_status'];
			$woo_products[ $key ]['sku']          = $get_product_data['sku'];
			$woo_products[ $key ]['price']        = $get_product_data['price'];
			$Image_url_id                         = $get_product_data['image_id'];
			$woo_products[ $key ]['image']        = wp_get_attachment_url( $Image_url_id );
			foreach ( $woo_categories as $key1 => $value1 ) {
				if ( isset( $get_product_data['category_ids'] ) ) {
					foreach ( $get_product_data['category_ids'] as $key2 => $prodCat ) {
						if ( $value1->term_id == $prodCat ) {
							$woo_products[ $key ]['category'][] = $value1->name;
						}
					}
				}
			}
		}

		if ( isset( $_POST['ced_ebay_product_filter_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ced_ebay_product_filter_nonce'] ), 'ced_ebay_product_filter_page_nonce' ) ) {
			if ( isset( $_POST['filter_button'] ) ) {
				$woo_products = $instanceOf_FilterClass->ced_ebay_filters_on_products();
			}
		}

		if ( isset( $_POST['ced_ebay_filter_product_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ced_ebay_filter_product_nonce'] ), 'ced_ebay_filter_product_action_nonce' ) ) {
			if ( isset( $_POST['ced_ebay_filter_product_button'] ) ) {
				$woo_products = $instanceOf_FilterClass->ced_ebay_product_search_box();
			}
		}

		return $woo_products;

	}

	/**
	 *
	 * Text displayed when no data is available
	 */
	public function no_items() {
		esc_html_e( 'No Products To Show.', 'ebay-integration-for-woocommerce' );
	}

	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */

	public function get_sortable_columns() {
		$sortable_columns = array();
		return $sortable_columns;
	}

	/*
	 * Render the bulk edit checkbox
	 *
	 */
	public function column_cb( $item ) {
		echo '<div class="admin-custom-action-button-outer"><div class="admin-custom-action-show-button-outer">';
		return sprintf(
			'<input type="checkbox" name="ebay_product_ids[]" class="ebay_products_id" value="%s" /></div></div>',
			$item['id']
		);
	}

	/**
	 *
	 * Function for name column
	 */
	public function column_name( $item ) {
		$actions       = array();
		$user_id       = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		$url           = get_edit_post_link( $item['id'], '' );
		$actions['id'] = 'ID:' . __( $item['id'] );
		echo '<div class="admin-custom-action-button-outer"><div class="admin-custom-action-show-button-outer">';
		echo '<b><a class="ced_ebay_prod_name" href="' . esc_attr( $url ) . '" target="_blank">' . esc_attr( $item['name'] ) . '</a></b><br>';
		echo '</div></div>';
		$actions['modify'] = '<a href="#" id="ced_ebay_modify_listing_button" data-prod-id="' . esc_html( $item['id'] ) . '">' . __( 'Modify', 'ebay-integration-for-woocommerce' ) . '</a>';
		return $this->row_actions( $actions );
	}

	/**
	 *
	 * Function for profile column
	 */
	public function column_profile( $item ) {
		echo '<div class="admin-custom-action-button-outer"><div class="admin-custom-action-show-button-outer">';
		$user_id                      = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		$get_profile_id_of_prod_level = get_post_meta( $item['id'], 'ced_ebay_profile_assigned' . isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '', true );
		if ( ! empty( $get_profile_id_of_prod_level ) ) {
			global $wpdb;
			$profile_name = $wpdb->get_results( $wpdb->prepare( "SELECT `profile_name` FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id` = %s", $get_profile_id_of_prod_level ), 'ARRAY_A' );
			$profile_name = isset( $profile_name[0]['profile_name'] ) ? $profile_name[0]['profile_name'] : '';
			echo '<b>' . esc_attr( $profile_name ) . '</b>';
			$profile_id = $get_profile_id_of_prod_level;
		} else {
			$get_ebay_category_id = '';
			foreach ( $item['category_id'] as $key => $category_id ) {
				$get_ebay_category_id_data = get_term_meta( $category_id );
				$get_ebay_category_id      = isset( $get_ebay_category_id_data[ 'ced_ebay_mapped_category_' . $user_id ] ) ? $get_ebay_category_id_data[ 'ced_ebay_mapped_category_' . $user_id ] : '';
				if ( ! empty( $get_ebay_category_id ) ) {
					break;
				}
			}
			if ( ! empty( $get_ebay_category_id ) && isset( $get_ebay_category_id ) ) {
				foreach ( $get_ebay_category_id as $key => $ebay_id ) {
					$get_ebay_profile_assigned = get_option( 'ced_woo_ebay_mapped_categories_name' );
					$get_ebay_profile_assigned = isset( $get_ebay_profile_assigned[ isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '' ][ $ebay_id ] ) ? $get_ebay_profile_assigned[ isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '' ][ $ebay_id ] : '';
				}

				if ( isset( $get_ebay_profile_assigned ) && ! empty( $get_ebay_profile_assigned ) ) {
					$profile_id            = isset( $get_ebay_category_id_data[ 'ced_ebay_profile_id_' . $user_id ] ) ? $get_ebay_category_id_data[ 'ced_ebay_profile_id_' . $user_id ] : '';
					$profile_id            = isset( $profile_id[0] ) ? $profile_id[0] : 0;
					$assigned_profile_html = sprintf( '<a  target="_blank" href="?page=%s&section=%s&user_id=%s&profileID=%s&panel=edit">%s</a>', esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '' ), 'profiles-view', esc_attr( isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '' ), $profile_id, $get_ebay_profile_assigned );
					print_r( $assigned_profile_html );
				}
			} else {
				echo '<b class="not_completed">' . esc_attr( 'Profile Not Assigned', 'ebay-integration-for-woocommerce' ) . '</b>';
			}
		}
		echo '</div></div>';
	}
	/**
	 *
	 * Function for stock column
	 */
	public function column_stock( $item ) {
		echo '<div class="admin-custom-action-button-outer"><div class="admin-custom-action-show-button-outer">';

		if ( 'instock' == $item['stock_status'] ) {
			if ( 0 == $item['stock'] || '0' == $item['stock'] ) {
				return '<b class="stock_alert_instock" >' . esc_attr( 'In Stock', 'ebay-integration-for-woocommerce' ) . '</b>';
			} else {
				return '<b class="stock_alert_instock">In Stock(' . $item['stock'] . ')</b>';
			}
		} else {
			return '<b class="stock_alert_outofstock" >' . esc_attr( 'Out of Stock', 'ebay-integration-for-woocommerce' ) . '</b>';
		}

		echo '</div></div>';

	}
	/**
	 *
	 * Function for category column
	 */
	public function column_category( $item ) {
		echo '<div class="admin-custom-action-button-outer"><div class="admin-custom-action-show-button-outer">';

		if ( isset( $item['category'] ) ) {
			$allCategories = '';
			foreach ( $item['category'] as $key => $prodCat ) {
				$allCategories .= '<b>-->' . $prodCat . '</b><br>';
			}
			return $allCategories;
		}

		echo '</div></div>';

	}
	/**
	 *
	 * Function for price column
	 */
	public function column_price( $item ) {
		echo '<div class="admin-custom-action-button-outer"><div class="admin-custom-action-show-button-outer">';
		$currencySymbol = get_woocommerce_currency_symbol();
		return $currencySymbol . '&nbsp<b class="success_upload_on_ebay">' . $item['price'] . '</b>';
		echo '</div></div>';
	}
	/**
	 *
	 * Function for product type column
	 */
	public function column_type( $item ) {
		echo '<div class="admin-custom-action-button-outer"><div class="admin-custom-action-show-button-outer">';

		$product      = wc_get_product( $item['id'] );
		$product_type = $product->get_type();
		return '<b>' . $product_type . '</b>';
		echo '</div></div>';
	}
	/**
	 *
	 * Function for sku column
	 */
	public function column_sku( $item ) {
		echo '<div class="admin-custom-action-button-outer"><div class="admin-custom-action-show-button-outer">';
		return '<b>' . $item['sku'] . '</b>';
		echo '</div></div>';
	}
	/**
	 *
	 * Function for image column
	 */
	public function column_image( $item ) {
		echo '<div class="admin-custom-action-button-outer"><div class="admin-custom-action-show-button-outer">';
		return '<img height="50" width="50" src="' . $item['image'] . '">';
		echo '</div></div>';
	}
	/**
	 *
	 * Function for status column
	 */
	public function column_status( $item ) {
		$actions    = array();
		$user_id    = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		$listing_id = get_post_meta( $item['id'], '_ced_ebay_listing_id_' . $user_id, true );
		if ( ! empty( get_post_meta( $item['id'], 'ced_ebay_alt_prod_description_' . $item['id'] . '_' . $user_id, true ) ) || ! empty( get_post_meta( $item['id'], 'ced_ebay_alt_prod_title_' . $item['id'] . '_' . $user_id, true ) ) ) {
			echo '<button class="px-3 py-1 mr-3 text-white font-semibold bg-blue-500 rounded">Modified</button><br>';

		}
		if ( ! empty( get_post_meta( $item['id'], '_ced_ebay_relist_item_id_' . $user_id, true ) ) ) {
			echo '<button class="px-3 py-1 mr-3 text-white font-semibold bg-blue-500 rounded">Re-Listed</button><br>';
		}
		if ( isset( $listing_id ) && ! empty( $listing_id ) ) {
			$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
			if ( ! empty( get_option( 'ced_ebay_listing_url_tld_' . $user_id ) ) ) {
				$listing_url_tld     = get_option( 'ced_ebay_listing_url_tld_' . $user_id, true );
				$view_url_production = 'https://www.ebay' . $listing_url_tld . '/itm/' . $listing_id;
				$view_url_sandbox    = 'https://sandbox.ebay' . $listing_url_tld . '/itm/' . $listing_id;
			} else {
				$view_url_production = 'https://www.ebay.com/itm/' . $listing_id;
				$view_url_sandbox    = 'https://sandbox.ebay.com/itm/' . $listing_id;

			}
			$mode_of_operation = get_option( 'ced_ebay_mode_of_operation', '' );
			if ( 'sandbox' == $mode_of_operation ) {
				echo '<div class="admin-custom-action-button-outer">';
				echo '<div class="admin-custom-action-show-button-outer">';
				echo '<a target="_blank" href="' . esc_attr( $view_url_sandbox ) . '" type="button" style="background:#5850ec !important;" class="button btn-normal-tt"><span>View on eBay</span></a>';
				echo '</div></div>';
			} elseif ( 'production' == $mode_of_operation ) {
				echo '<div class="admin-custom-action-button-outer">';
				echo '<div class="admin-custom-action-show-button-outer">';
				echo '<a target="_blank" href="' . esc_attr( $view_url_production ) . '" type="button" style="background:#5850ec !important;" class="button btn-normal-tt"><span>View on eBay</span></a>';
				echo '</div></div>';            } else {
				echo '<div class="admin-custom-action-button-outer">';
				echo '<div class="admin-custom-action-show-button-outer">';
				echo '<a target="_blank" href="' . esc_attr( $view_url_production ) . '" type="button" style="background:#5850ec !important;" class="button btn-normal-tt"><span>View on eBay</span></a>';
				echo '</div></div>';            }
		} else {
			echo '<div class="admin-custom-action-button-outer">';
			echo '<div class="admin-custom-action-show-button-outer">';
			echo '<button type="button" class="button btn-normal-tt"><span>Not Uploaded</span></button>';
			echo '</div></div>';
		}
		return $this->row_actions( $actions );

	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */

	public function get_columns() {
		$columns = array(
			'cb'       => '<input type="checkbox" />',
			'image'    => __( 'Product Image', 'ebay-integration-for-woocommerce' ),
			'name'     => __( 'Product Name', 'ebay-integration-for-woocommerce' ),
			'type'     => __( 'Product Type', 'ebay-integration-for-woocommerce' ),
			'price'    => __( 'Product Price', 'ebay-integration-for-woocommerce' ),
			'profile'  => __( 'Profile Assigned', 'ebay-integration-for-woocommerce' ),
			'sku'      => __( 'Product Sku', 'ebay-integration-for-woocommerce' ),
			'stock'    => __( 'Product Stock', 'ebay-integration-for-woocommerce' ),
			'category' => __( 'Woo Category', 'ebay-integration-for-woocommerce' ),
			'status'   => __( 'Product Status', 'ebay-integration-for-woocommerce' ),
		);
		return $columns;
	}

	/**
	 *
	 * Function to count number of responses in result
	 */
	public function get_count( $per_page, $page_number ) {
		$args = $this->GetFilteredData( $per_page, $page_number );
		if ( ! empty( $args ) && isset( $args['tax_query'] ) || isset( $args['meta_query'] ) || isset( $args['s'] ) ) {
			$args = $args;
		} else {
			$args = array( 'post_type' => 'product' );
		}
		$loop         = new WP_Query( $args );
		$product_data = $loop->posts;
		$product_data = $loop->found_posts;

		return $product_data;
	}

	public function GetFilteredData( $per_page, $page_number ) {
		$args    = array();
		$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		if ( ! empty( $_REQUEST['searchType'] ) && ! empty( $_REQUEST['searchQuery'] ) && ! empty( $_REQUEST['searchCriteria'] ) ) {
			$search_criteria = isset( $_GET['searchCriteria'] ) ? sanitize_text_field( $_GET['searchCriteria'] ) : '';
			$search_type     = isset( $_GET['searchType'] ) ? sanitize_text_field( $_GET['searchType'] ) : '';
			$search_query    = isset( $_GET['searchQuery'] ) ? sanitize_text_field( $_GET['searchQuery'] ) : '';
			if ( 'productId' == $search_type ) {
				$args['prodID'] = $search_query;
			}
			if ( 'product_name' == $search_criteria && 'productCustomSearch' == $search_type ) {
				$args['s'] = $search_query;
			}
			if ( 'product_sku' == $search_criteria && 'productCustomSearch' == $search_type ) {
				$args['search_by_sku'] = $search_query;
			}
		}
		// die('123');
		if ( ( isset( $_GET['status_sorting'] ) || isset( $_GET['pro_cat_sorting'] ) || isset( $_GET['pro_type_sorting'] ) || isset( $_GET['searchBy'] ) || isset( $_GET['pro_profile_sorting'] ) || isset( $_GET['prodID'] ) ) && empty( $_REQUEST['searchType'] ) ) {
			if ( isset( $_REQUEST['pro_cat_sorting'] ) && ! empty( $_REQUEST['pro_cat_sorting'] ) ) {
				$pro_cat_sorting = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( $_GET['pro_cat_sorting'] ) : '';
				if ( '' != $pro_cat_sorting ) {
					$selected_cat          = array( $pro_cat_sorting );
					$tax_query             = array();
					$tax_queries           = array();
					$tax_query['taxonomy'] = 'product_cat';
					$tax_query['field']    = 'id';
					$tax_query['terms']    = $selected_cat;
					$args['tax_query'][]   = $tax_query;
				}
			}

			if ( isset( $_REQUEST['pro_type_sorting'] ) && ! empty( $_REQUEST['pro_type_sorting'] ) ) {
				$pro_type_sorting = isset( $_GET['pro_type_sorting'] ) ? sanitize_text_field( $_GET['pro_type_sorting'] ) : '';
				if ( '' != $pro_type_sorting ) {
					$selected_type         = array( $pro_type_sorting );
					$tax_query             = array();
					$tax_queries           = array();
					$tax_query['taxonomy'] = 'product_type';
					$tax_query['field']    = 'id';
					$tax_query['terms']    = $selected_type;
					$args['tax_query'][]   = $tax_query;
				}
			}

			if ( isset( $_REQUEST['status_sorting'] ) && ! empty( $_REQUEST['status_sorting'] ) ) {
				$status_sorting = isset( $_GET['status_sorting'] ) ? sanitize_text_field( $_GET['status_sorting'] ) : '';
				if ( '' != $status_sorting ) {
					$meta_query = array();
					if ( 'Uploaded' == $status_sorting ) {

						$meta_query[] = array(
							'key'     => '_ced_ebay_listing_id_' . $user_id,
							'value'   => '',
							'compare' => '!=',
						);
					} elseif ( 'NotUploaded' == $status_sorting ) {
						$meta_query[] = array(
							'key'     => '_ced_ebay_listing_id_' . $user_id,
							'value'   => '',
							'compare' => '=',
						);
					}
					$args['meta_query'] = $meta_query;
				}
			}

			if ( isset( $_REQUEST['pro_stock_sorting'] ) && ! empty( $_REQUEST['pro_stock_sorting'] ) ) {
				$sort_by_stock = isset( $_GET['pro_stock_sorting'] ) ? sanitize_text_field( $_GET['pro_stock_sorting'] ) : '';
				if ( '' != $sort_by_stock ) {
					$meta_query = array();
					if ( 'instock' == $sort_by_stock ) {
						if ( 'Uploaded' == $_REQUEST['status_sorting'] ) {
							$args['meta_query'] = array(
								'relation' => 'AND',
								array(
									'key'     => '_ced_ebay_listing_id_' . $user_id,
									'compare' => 'EXISTS',
								),
								array(
									'key'     => '_stock_status',
									'value'   => 'instock',
									'compare' => '=',
								),

							);

						} elseif ( 'NotUploaded' == $_REQUEST['status_sorting'] ) {
							$args['meta_query'] = array(
								'relation' => 'AND',
								array(
									'key'     => '_ced_ebay_listing_id_' . $user_id,
									'compare' => 'NOT EXISTS',
								),
								array(
									'key'     => '_stock_status',
									'value'   => 'instock',
									'compare' => '=',
								),

							);

						} else {
							$args['meta_query'][] = array(
								'key'   => '_stock_status',
								'value' => 'instock',
							);
						}
					} elseif ( 'outofstock' == $sort_by_stock ) {
						if ( 'Uploaded' == $_REQUEST['status_sorting'] ) {
							$args['meta_query'] = array(
								'relation' => 'AND',
								array(
									'key'     => '_ced_ebay_listing_id_' . $user_id,
									'compare' => 'EXISTS',
								),
								array(
									'key'     => '_stock_status',
									'value'   => 'outofstock',
									'compare' => '=',
								),

							);

						} elseif ( 'NotUploaded' == $_REQUEST['status_sorting'] ) {
							$args['meta_query'] = array(
								'relation' => 'AND',
								array(
									'key'     => '_ced_ebay_listing_id_' . $user_id,
									'compare' => 'NOT EXISTS',
								),

								array(
									'key'     => '_stock_status',
									'value'   => 'outofstock',
									'compare' => '=',
								),

							);

						} else {
							$args['meta_query'][] = array(
								'key'   => '_stock_status',
								'value' => 'outofstock',
							);
						}
					}
				}
			}
			if ( ! empty( $_REQUEST['searchBy'] ) ) {
				$search_by = isset( $_GET['searchBy'] ) ? sanitize_text_field( wp_unslash( $_GET['searchBy'] ) ) : '';
				if ( ! empty( $search_by ) ) {
					$args['s'] = $search_by;
				}
			}

			if ( ! empty( $_REQUEST['prodID'] ) ) {
				$prodID = isset( $_GET['prodID'] ) ? sanitize_text_field( wp_unslash( $_GET['prodID'] ) ) : '';
				if ( ! empty( $prodID ) ) {
					$args['prodID'] = $prodID;
				}
			}
		}

		$args['post_type']          = 'product';
			$args['posts_per_page'] = $per_page;
			$args['paged']          = $page_number;

			return $args;

	}
	/**
	 *
	 * Render bulk actions
	 */

	protected function bulk_actions( $which = '' ) {
		if ( 'top' == $which ) :
			if ( is_null( $this->_actions ) ) {
				$this->_actions = $this->get_bulk_actions();
				/**
				 * Filters the list table Bulk Actions drop-down.
				 *
				 * The dynamic portion of the hook name, `$this->screen->id`, refers
				 * to the ID of the current screen, usually a string.
				 *
				 * This filter can currently only be used to remove bulk actions.
				 *
				 * @since 3.5.0
				 *
				 * @param array $actions An array of the available bulk actions.
				 */
				$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
				$two            = '';
			} else {
				$two = '2';
			}

			if ( empty( $this->_actions ) ) {
				return;
			}

			echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . esc_attr( 'Select bulk action' ) . '</label>';
			echo '<select name="action' . esc_attr( $two ) . '" class="ced_ebay_select_ebay_product_action">';
			echo '<option value="-1">' . esc_attr( 'Bulk Actions' ) . "</option>\n";

			foreach ( $this->_actions as $name => $title ) {
				$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

				echo "\t" . '<option value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . "</option>\n";
			}

			echo "</select>\n";

			submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => 'ced_ebay_bulk_operation' ) );
			echo "\n";
		endif;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'upload_product'   => __( 'Upload', 'ebay-integration-for-woocommerce' ),
			'relist_product'   => __( 'Relist', 'ebay-integration-for-woocommerce' ),
			'update_product'   => __( 'Update Product', 'ebay-integration-for-woocommerce' ),
			'update_stock'     => __( 'Update Inventory', 'ebay-integration-for-woocommerce' ),
			'remove_product'   => __( 'End/Reset Product', 'ebay-integration-for-woocommerce' ),
		);
		return $actions;
	}
	/**
	 *
	 * Function for rendering html
	 */
	public function renderHTML() {
		$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		?>
		<div class="justify-center my-8 select-none flex">

</div>

		<div class="ced-ebay-v2-header">
			<div class="ced-ebay-v2-logo">
			<img src="<?php echo esc_attr( CED_EBAY_URL ) . 'admin/images/icon-100X100.png'; ?>">
			</div>
			<div class="ced-ebay-v2-header-content">
				<div class="ced-ebay-v2-title">
					<h1>Products Management</h1>
				</div>

			<div class="ced-ebay-v2-actions">

<div class="admin-custom-action-button-outer">

<div class="admin-custom-action-show-button-outer">
		<?php
		if ( function_exists( 'as_get_scheduled_actions' ) ) {
			$scheduled_recurring_upload    = false;
			$scheduled_bulk_upload_actions = as_get_scheduled_actions(
				array(
					'group'  => 'ced_ebay_bulk_upload_' . $user_id,
					'status' => ActionScheduler_Store::STATUS_PENDING,
				),
				'ARRAY_A'
			);

			if ( as_has_scheduled_action( 'ced_ebay_recurring_bulk_upload_' . $user_id ) ) {
				$scheduled_recurring_upload = true;
			}
		}
		if ( ! empty( $scheduled_bulk_upload_actions || $scheduled_recurring_upload ) ) {
			?>
<button style="background:red;" data-action="turn_off" style="margin-left:5px;" id="ced_ebay_toggle_bulk_upload_btn" type="button" class="button btn-normal-tt">
<span>Turn Off Bulk Products Upload</span>
</button>
			<?php
		} else {
			?>
<button  style="margin-left:5px;" data-action="turn_on" id="ced_ebay_toggle_bulk_upload_btn" type="button" class="button btn-normal-sbc">
<span>Turn On Bulk Products Upload</span>
</button>
			<?php
		}
		?>
</div>

<div class="admin-custom-action-show-button-outer">
		<?php
		if ( function_exists( 'as_has_scheduled_action' ) ) {

			if ( as_has_scheduled_action( 'ced_ebay_inventory_scheduler_job_' . $user_id ) ) {
				?>
<button  data-action="turn_off" type="button" id="ced_ebay_toggle_bulk_inventory_btn" style="background:#c62019 !important;" class="button btn-normal-tt">
<span>Turn Off Inventory Sync</span>
</button>
				<?php
			} else {
				?>
<button  data-action="turn_on" type="button" id="ced_ebay_toggle_bulk_inventory_btn" class="button btn-normal-sbc">
<span>Turn On Inventory Sync</span>
</button>
				<?php
			}
		}
		?>

</div>
<div class="admin-custom-action-show-button-outer">
<button style="background:#5850ec !important;"  type="button" class="button btn-normal-tt">
<span><a style="all:unset;" href="https://docs.woocommerce.com/document/ebay-integration-for-woocommerce/#section-11" target="_blank">
Documentation					</a></span>
</button>

</div>

</div>
</div>
		</div>
</div>

<section class="woocommerce-inbox-message plain">
			<div class="woocommerce-inbox-message__wrapper">
				<div class="woocommerce-inbox-message__content">
					<h2 class="woocommerce-inbox-message__title">We've upgraded the support!</h2>
					<div class="woocommerce-inbox-message__text">
					<h4>If you're facing a specific problem or want to get the most out of the eBay Integration as an eBay seller, we're here to help.
		  To live chat with our seller expert, please click on the chat icon in the bottom right.</h4>
					</div>
				</div>
			</div>

		</section>
		<div class="ced-ebay-products-view-notice success-admin-notices is-dismissible"></div>

		<div id="post-body" class="metabox-holder columns-2">
			<div id="post-body-content">
				<div class="meta-box-sortables ui-sortable">
					<?php
					$status_actions = array(
						'Uploaded'    => __( 'Uploaded', 'ebay-integration-for-woocommerce' ),
						'NotUploaded' => __( 'Not Uploaded', 'ebay-integration-for-woocommerce' ),
					);

					$product_types = get_terms( 'product_type', array( 'hide_empty' => false ) );
					$temp_array    = array();
					foreach ( $product_types as $key => $value ) {
						if ( 'simple' == $value->name || 'variable' == $value->name ) {
							$temp_array_type[ $value->term_id ] = ucfirst( $value->name );
						}
					}
					$product_types      = $temp_array_type;
					$product_categories = $this->ced_ebay_get_taxonomy_hierarchy( 'product_cat', 0, 0 );
					$temp_array         = array();
					// foreach ( $product_categories as $key => $value ) {
					// $temp_array[ $value->term_id ] = $value->name;
					// }
					// $product_categories = $temp_array;
					$profiles_array = array();
					global $wpdb;
					$tableName         = $wpdb->prefix . 'ced_ebay_profiles';
					$assigned_profiles = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_ebay_profiles WHERE `user_id`=%s", $user_id ), 'ARRAY_A' );
					foreach ( $assigned_profiles as $key => $ced_profiles ) {
						$profiles_array[ $ced_profiles['id'] ] = $ced_profiles['profile_name'];
					}
					$assigned_profiles              = $profiles_array;
					$previous_selected_status       = isset( $_GET['status_sorting'] ) ? sanitize_text_field( $_GET['status_sorting'] ) : '';
					$previous_selected_cat          = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( $_GET['pro_cat_sorting'] ) : '';
					$previous_selected_type         = isset( $_GET['pro_type_sorting'] ) ? sanitize_text_field( $_GET['pro_type_sorting'] ) : '';
					$previous_selected_stock_status = isset( $_GET['pro_stock_sorting'] ) ? sanitize_text_field( $_GET['pro_stock_sorting'] ) : '';
					echo '<div class="ced_ebay_wrap">';
					echo '<form method="post" action="">';
					echo '<div class="ced_ebay_top_wrapper">';
					echo '<select name="status_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_attr( 'Product Status', 'ebay-integration-for-woocommerce' ) . '</option>';
					foreach ( $status_actions as $name => $title ) {
						$selectedStatus = ( $previous_selected_status == $name ) ? 'selected="selected"' : '';
						$class          = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selectedStatus ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}

					echo '</select>';
					$previous_selected_cat = isset( $_GET['pro_cat_sorting'] ) ? sanitize_text_field( $_GET['pro_cat_sorting'] ) : '';

					$dropdown_cat_args = array(
						'name'            => 'pro_cat_sorting',
						'show_count'      => 1,
						'hierarchical'    => 1,
						'depth'           => 10,
						'taxonomy'        => 'product_cat',
						'class'           => 'select_boxes_product_page',
						'selected'        => $previous_selected_cat,
						'show_option_all' => 'Product Category',

					);
					wp_dropdown_categories( $dropdown_cat_args );
					echo '<select name="pro_type_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_attr( 'Product Type', 'ebay-integration-for-woocommerce' ) . '</option>';
					foreach ( $product_types as $name => $title ) {
						$selectedType = ( $previous_selected_type == $name ) ? 'selected="selected"' : '';
						$class        = 'edit' === $name ? ' class="hide-if-no-js"' : '';
						echo '<option ' . esc_attr( $selectedType ) . ' value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . '</option>';
					}
					echo '</select>';

					echo '<select name="pro_stock_sorting" class="select_boxes_product_page">';
					echo '<option value="">' . esc_attr( 'Stock Status', 'ebay-integration-for-woocommerce' ) . '</option>';
					echo '<option ' . esc_attr( ( 'instock' == $previous_selected_stock_status ) ? 'selected="selected"' : '' ) . ' value="instock">In Stock</option>';
					echo '<option ' . esc_attr( ( 'outofstock' == $previous_selected_stock_status ) ? 'selected="selected"' : '' ) . ' value="outofstock">Out Of Stock</option>';
					echo '</select>';
					wp_nonce_field( 'ced_ebay_product_filter_page_nonce', 'ced_ebay_product_filter_nonce' );
					submit_button( __( 'Filter', 'ebay-integration-for-woocommerce' ), 'action', 'filter_button', false, array() );
					?>


					<?php
					echo '</div>';
					echo '</form>';
					echo '</div>';

					?>

					<form method="post">

</div>
	</div>
						<?php
						$this->display();
						?>

					</form>



				</div>
			</div>
			<div class="clear"></div>
		</div>
		<?php
	}

	public function extra_tablenav( $which ) {
		$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		if ( 'top' == $which ) {
			ob_start();
			?>

	  <div class="alignleft actions bulkactions" style="padding-right:0px !important;">

		  <select name="product-filter" class="ced-ebay-filter-products" style="min-width:210px;">
		  </select>


	  </div>
	  <div class="alignleft actions bulkactions">

<select name="product-filter-criteria" class="ced-ebay-filter-products-criteria">
<option value="product_name">Name</option>
<option value="product_sku">SKU</option>
<option value="ebay_listing_id">eBay Item ID</option>

</select>

<button type="submit" class="button btn-normal-tt" name="ced_ebay_filter_product_button" id="ced_ebay_filter_product_button">
<span> Search </span></button>
</div>


			<?php
			wp_nonce_field( 'ced_ebay_filter_product_action_nonce', 'ced_ebay_filter_product_nonce' );
			ob_flush();

			?>
			<div class="alignleft actions bulkactions">
			<div class="admin-custom-action-button-outer" style="margin: 0px !important;">
							<div class="admin-custom-action-show-button-outer">

							<input type="text" placeholder="No. of entries to view" name="ced_ebay_view_entries_prod_section_input" style="line-height:0;" value="<?php echo ! empty( get_option( 'ced_ebay_product_section_per_page_' . $user_id ) ) ? esc_attr( get_option( 'ced_ebay_product_section_per_page_' . $user_id, true ) ) : ''; ?>">
		</div>
			<div class="admin-custom-action-show-button-outer" style="line-height:2 !important; min-height:30px !important;">
				<button style="background:#135e96 !important;vertical-align:bottom;" type="submit" class="button btn-normal-tt" name="ced_ebay_view_entries_bulk_upload">
<span>Save</span>
</button>
			<?php wp_nonce_field( 'ced_ebay_view_entries_product_section_nonce', 'ced_ebay_view_entries_product_section' ); ?>
	</div>

		</div>

			</div>
			<?php
		}
	}


}

$ced_ebay_products_obj = new EBayListProducts();
$ced_ebay_products_obj->prepare_items();

?>

<style>
	.select2-container .select2-selection--single {
		height: 30px !important;
	}
</style>
