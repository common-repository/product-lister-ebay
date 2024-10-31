<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

// header file.
// require_once CED_UMB_EBAY_DIRPATH.'admin/pages/header.php';

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CED_UMB_EBAY_Shipping_Template_List extends WP_List_Table {


	/** Class constructor */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Shipping Template', 'ced-umb-ebay' ), // singular name of the listed records
				'plural'   => __( 'Shipping Templates', 'ced-umb-ebay' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);
	}

	/**
	 * Retrieve Ebay Profiles
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function get_shipping_templates( $per_page = 5, $page_number = 1 ) {

		$siteID    = isset( $shop_data['siteID'] ) ? $shop_data['siteID'] : '';
		$token     = isset( $shop_data['token']['eBayAuthToken'] ) ? $shop_data['token']['eBayAuthToken'] : '';
		$shop_name = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		// var_dump($shop_name);
		global $wpdb;
		$offset    = ( $page_number - 1 ) * $per_page;
		$tableName = $wpdb->prefix . 'ced_ebay_shipping';
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_ebay_shipping WHERE `user_id`=%s  ORDER BY `id` DESC LIMIT %d OFFSET %d", $shop_name, $per_page, $offset ), 'ARRAY_A' );
		return $result;

	}

	/**
	 * The number of profiles.
	 *
	 * @return number
	 */
	public function get_count() {
		global $wpdb;
		$shop_name = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		$tableName = $wpdb->prefix . 'ced_ebay_shipping';
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_ebay_shipping WHERE `user_id`=%s", $shop_name ), 'ARRAY_A' );
		return count( $result );
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html_e( 'No Templates avaliable.', 'ced-umb-ebay' );
	}

	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array  $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'template_data':
				$l         = '';
				$meta_data = json_decode( $item['profile_data'] );
				if ( isset( $meta_data->umb_ebay_category_name ) && '' != $meta_data->umb_ebay_category_name && 'null' != $meta_data->umb_ebay_category_name ) {
					if ( '-- Select --' != $meta_data->umb_ebay_category_name ) {
						$l .= '<b>Category</b> -> ' . $meta_data->umb_ebay_category_name . '<br>';
					} else {
						$l .= '<b>Category</b> -> ' . __( 'Category not selected.', 'ced-umb-ebay' ) . '<br>';
					}
				}

				if ( isset( $meta_data->_umb_ebay_brand->default ) && '' != $meta_data->_umb_ebay_brand->default && 'null' != $meta_data->_umb_ebay_brand->default ) {
					$l .= '<b>Brand</b> -> ' . $meta_data->_umb_ebay_brand->default . '<br>';
				} elseif ( isset( $meta_data->_umb_ebay_brand->metakey ) && '' != $meta_data->_umb_ebay_brand->metakey && 'null' != $meta_data->_umb_ebay_brand->metakey ) {
					$l .= '<b>Brand</b> -> ' . $meta_data->_umb_ebay_brand->metakey . '<br>';
				}
				if ( isset( $meta_data->_umb_ebay_listing_type->default ) && '' != $meta_data->_umb_ebay_listing_type->default && '0' != $meta_data->_umb_ebay_listing_type->default ) {
					$l .= '<b>Listing Type</b> -> ' . $meta_data->_umb_ebay_listing_type->default . '<br>';
				} elseif ( isset( $meta_data->_umb_ebay_listing_type->metakey ) && '' != $meta_data->_umb_ebay_listing_type->metakey && 'null' != $meta_data->_umb_ebay_listing_type->metakey ) {
					$l .= '<b>Listing Type</b> -> ' . $meta_data->_umb_ebay_listing_type->metakey . '<br>';
				}
				return $l;
				break;
			default:
				return print_r( $item, true ); // Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="template_ids[]" value="%s" />',
			$item['id']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	public function column_name( $item ) {
		$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		$title   = '<strong>' . $item['shipping_name'] . '</strong>';
		// $title .= '<input type="hidden" class="ced_ebay_shipping_edit_link" id="ced_ebay_shipping_edit_'.$item['id'].'" data-template_name="'.$item["shipping_name"].'" ></input>';
		$actions = array(
			'edit'   => sprintf( '<a href="?page=%s&process=%s&template_id=%s&section=%s&part=%s&user_id=%s">Edit</a>', esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '' ), 'template_edit', $item['id'], 'accounts-view', 'shipping', $user_id ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&template_id=%s&section=%s&part=%s&user_id=%s">Delete</a>', esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '' ), 'delete', $item['id'], 'accounts-view', 'shipping', $user_id ),
		);
		return $title . $this->row_actions( $actions );
		return $title;
	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'   => '<input type="checkbox" />',
			'name' => __( 'Name', 'ced-umb-ebay' ),
		);
		return $columns;
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

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = array(
			'bulk-delete' => __( 'Delete', 'ced-umb-ebay' ),
		);
		return $actions;
	}

	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		global $wpdb;

		$per_page = 10;
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		// Column headers
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$current_page = $this->get_pagenum();
		if ( 1 < $current_page ) {
			$offset = $per_page * ( $current_page - 1 );
		} else {
			$offset = 0;
		}

		$this->items = self::get_shipping_templates( $per_page, $current_page );

		$count = self::get_count();

		// Set the pagination
		$this->set_pagination_args(
			array(
				'total_items' => $count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $count / $per_page ),
			)
		);

		if ( ! $this->current_action() ) {
			$this->items = self::get_shipping_templates( $per_page, $current_page );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}

	}
	public function renderHTML() {
		?>
		<div class="ced_umb_ebay_wrap ced_umb_ebay_wrap_extn">
			<div class="ced_umb_ebay_button_heading_wrap">
				<h2 class="ced_umb_ebay_setting_header"><?php esc_attr( 'Shipping Templates', 'ced-umb-ebay' ); ?></h2>
				<?php echo '<a href="javascript:void(0);" style="margin-top:10px;" class="ced_ebay_site_add_shipping_template_button button button-ced_umb_ebay page-title-action">' . esc_attr( 'Add Shipping Template', 'ced-umb-ebay' ) . '</a>'; ?>
			</div>
			<div>
				<?php
				if ( ! session_id() ) {
					session_start();
				}

				?>
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'ebay_shipping_field', 'ebay_shipping_field_actions' );
								$this->display();
								?>
							</form>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<br class="clear">
			</div>
		</div>
		<?php
	}

	public function process_bulk_action() {
		$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );
		/** Render configuration setup html of marketplace */
		$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		wp_nonce_field( 'ced_ebay_shipping_templates_view_page_nonce', 'ced_ebay_ebay_shipping_field_actions' );
		if ( 'delete' === $this->current_action() || ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] ) ) {

			$edit_id = isset( $_GET['template_id'] ) ? sanitize_text_field( $_GET['template_id'] ) : '';
			global $wpdb;
			$tableName    = $wpdb->prefix . 'ced_ebay_shipping';
			$deleteStatus = $wpdb->delete( $tableName, array( 'id' => $edit_id ) );
			if ( $deleteStatus ) {
				$notice['message']                          = __( 'Shipping Template Deleted Successfully.', 'ced-umb-ebay' );
				$notice['classes']                          = 'notice notice-success';
				$validation_notice[]                        = $notice;
				$_SESSION['ced_umb_ebay_validation_notice'] = $validation_notice;
			} else {
				$notice['message']                          = __( 'Some Error Encountered.', 'ced-umb-ebay' );
				$notice['classes']                          = 'notice notice-error';
				$validation_notice[]                        = $notice;
				$_SESSION['ced_umb_ebay_validation_notice'] = $validation_notice;
			}

			$redirectURL = get_admin_url() . 'admin.php?page=ced_ebay&section=accounts-view&part=shipping&user_id=' . $user_id;
			wp_redirect( $redirectURL );
		}

		if ( 'bulk-delete' === $this->current_action() ) {
			if ( ! isset( $_POST['ebay_shipping_field_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ebay_shipping_field_actions'] ) ), 'ebay_shipping_field' ) ) {
				return;
			}
			if ( isset( $sanitized_array['template_ids'] ) ) {
				$feedsToDelete = isset( $sanitized_array['template_ids'] ) ? $sanitized_array['template_ids'] : array();

				global $wpdb;
				foreach ( $feedsToDelete as $id ) {
					$deleteStatus = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ced_ebay_shipping WHERE `id` IN (%s)", $id ) );
				}

				if ( $deleteStatus ) {
					$notice['message']                          = __( 'Shipping Templates Deleted Successfully.', 'ced-umb-ebay' );
					$notice['classes']                          = 'notice notice-success';
					$validation_notice[]                        = $notice;
					$_SESSION['ced_umb_ebay_validation_notice'] = $validation_notice;
				} else {
					$notice['message']                          = __( 'Failed to delete on or more shipping template.', 'ced-umb-ebay' );
					$notice['classes']                          = 'notice notice-error';
					$validation_notice[]                        = $notice;
					$_SESSION['ced_umb_ebay_validation_notice'] = $validation_notice;
				}

				$redirectURL = get_admin_url() . 'admin.php?page=ced_ebay&section=accounts-view&part=shipping&user_id=' . $user_id;
				wp_redirect( $redirectURL );
			} else {
				$notice['message']                          = __( 'Please Select Templates for deletion.', 'ced-umb-ebay' );
				$notice['classes']                          = 'notice notice-error ced_umb_ebay_current_notice';
				$validation_notice[]                        = $notice;
				$_SESSION['ced_umb_ebay_validation_notice'] = $validation_notice;
				$redirectURL                                = get_admin_url() . 'admin.php?page=umb-ebay&section=shipping';
				wp_redirect( $redirectURL );
			}
		}
	}

}

$CED_UMB_EBAY_Shipping_Template_List = new CED_UMB_EBAY_Shipping_Template_List();
$CED_UMB_EBAY_Shipping_Template_List->prepare_items();
?>
