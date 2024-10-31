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

if ( ! class_exists( 'WP_List_Table' ) ) {
	 require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Ced_EBay_Profile_Table extends WP_List_Table {

	 /** Class constructor */
	public function __construct() {

		parent::__construct(
			array(
				'singular' => __( 'eBay Profile', 'ebay-integration-for-woocommerce' ), // singular name of the listed records
				'plural'   => __( 'eBay Profiles', 'ebay-integration-for-woocommerce' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);
	}
	 /**
	  *
	  * Function for preparing profile data to be displayed column
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

		$this->items = self::ced_ebay_get_profiles( $per_page, $current_page );

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
			$this->items = self::ced_ebay_get_profiles( $per_page, $current_page );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}
	}
	 /**
	  *
	  * Function for status column
	  */
	public function ced_ebay_get_profiles( $per_page = 10, $page_number = 1 ) {

		global $wpdb;
		$tableName = $wpdb->prefix . 'ced_ebay_profiles';
		$offset    = ( $page_number - 1 ) * $per_page;
		$user_id   = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_ebay_profiles WHERE `user_id`=%s ORDER BY `id` DESC LIMIT %d OFFSET %d", $user_id, $per_page, $offset ), 'ARRAY_A' );
		return $result;
	}

	 /*
	 *
	 * Function to count number of responses in result
	 *
	 */
	public function get_count() {
		global $wpdb;
		$user_id   = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		$tableName = $wpdb->prefix . 'ced_ebay_profiles';
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ced_ebay_profiles WHERE `user_id`=%s", $user_id ), 'ARRAY_A' );
		return count( $result );
	}

	 /*
	 *
	 * Text displayed when no customer data is available
	 *
	 */
	public function no_items() {
		esc_attr_e( 'No Profiles Created.', 'ebay-integration-for-woocommerce' );
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
			'<input type="checkbox" name="ebay_profile_ids[]" value="%s" class="ebay_profile_ids"/>',
			$item['id']
		);
	}


	public function column_profile_action( $item ) {
		$woo_categories = ! empty( $item['woo_categories'] ) ? json_decode( $item['woo_categories'], true ) : array();
		if ( ! empty( $woo_categories ) ) {
			$button_html = sprintf( '<a class="button button-primary" target="_blank" href="?page=%s&section=%s&user_id=%s&profileID=%s&panel=edit">Edit</a>', esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '' ), 'profiles-view', esc_attr( isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '' ), $item['id'] );
			// $button_html .= sprintf( '<a class="button button-primary" style="margin-left:10px;" data-profile-id=%s id="ced_ebay_add_category_to_profile_btn" href="#">Add Category</a>', esc_attr( isset( $item['id'] ) ? sanitize_text_field( $item['id'] ) : '' ) );
		} else {
			$button_html = sprintf( '<a class="button button-primary" disabled="disabled" target="_blank" href="#">Edit</a>', esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '' ), 'profiles-view', esc_attr( isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '' ), $item['id'] );
			// $button_html .= sprintf( '<a class="button button-primary" style="margin-left:10px;" data-profile-id=%s id="ced_ebay_add_category_to_profile_btn" href="#">Add Category</a>', esc_attr( isset( $item['id'] ) ? sanitize_text_field( $item['id'] ) : '' ) );

		}
		return $button_html;
	}
	 /**
	  * Function for name column
	  *
	  * @param array $item an array of DB data
	  *
	  * @return string
	  */
	public function column_profile_name( $item ) {
		$title = sprintf( '<a  target="_blank" href="?page=%s&section=%s&user_id=%s&profileID=%s&panel=edit">%s</a>', esc_attr( isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : '' ), 'profiles-view', esc_attr( isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '' ), $item['id'], $item['profile_name'] );
		return $title;
	}

	 /**
	  *
	  * Function for profile status column
	  */
	public function column_profile_status( $item ) {

		if ( 'inactive' == $item['profile_status'] ) {
			return 'InActive';
		} else {
			return 'Active';
		}
	}

	 /**
	  *
	  * Function for category column
	  */
	public function column_woo_categories( $item ) {

		$woo_categories = json_decode( $item['woo_categories'], true );
		$profile_id     = ! empty( $item['id'] ) ? $item['id'] : '';
		if ( ! empty( $woo_categories ) && '' != $profile_id ) {
			foreach ( $woo_categories as $key => $value ) {
				$term = get_term_by( 'id', $value, 'product_cat' );
				if ( $term ) {
					echo '<p>' . esc_attr( $term->name ) . '<a href="#" data-profile-id="' . esc_attr( $profile_id ) . '" data-term-id="' . esc_attr( $value ) . '" style="color:red; font-weight:bold;" id="ced_ebay_remove_term_from_profile_btn"> (Remove) </a></p>';
				}
			}
		} else {
			echo '<p style="color:red;"><b>No WooCommerce Categories Mapped!</b></p>';
		}
	}

	 /**
	  *  Associative array of columns
	  *
	  * @return array
	  */
	public function get_columns() {
		$columns = array(
			'cb'             => '<input type="checkbox" />',
			'profile_name'   => __( 'Profile Name', 'ebay-integration-for-woocommerce' ),
			'profile_status' => __( 'Profile Status', 'ebay-integration-for-woocommerce' ),
			'woo_categories' => __( 'Mapped WooCommerce Categories', 'ebay-integration-for-woocommerce' ),
			'profile_action' => __( ' Profile Actions', 'ebay-integration-for-woocommerce' ),

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
			echo '<select name="action' . esc_attr( $two ) . '" class="bulk-action-selector ">';
			echo '<option value="-1">' . esc_attr( 'Bulk Actions' ) . "</option>\n";

			foreach ( $this->_actions as $name => $title ) {
				$class = 'edit' === $name ? ' class="hide-if-no-js"' : '';

				echo "\t" . '<option value="' . esc_attr( $name ) . '"' . esc_attr( $class ) . '>' . esc_attr( $title ) . "</option>\n";
			}

			echo "</select>\n";

			submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => 'ced_ebay_profile_bulk_operation' ) );
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

			// 'bulk-activate'   => __('Activate', 'ebay-integration-for-woocommerce' ),
			// 'bulk-deactivate' => __('Deactivate', 'ebay-integration-for-woocommerce' ),
			'bulk-delete' => __( 'Delete', 'ebay-integration-for-woocommerce' ),
		);
		return $actions;
	}

	 /**
	  * Function to get changes in html
	  */
	public function renderHTML() {
		?>

		<div class="ced-ebay-v2-header">
			<div class="ced-ebay-v2-logo">
			<img src="<?php echo esc_attr( CED_EBAY_URL ) . 'admin/images/icon-100X100.png'; ?>">
			</div>
			<div class="ced-ebay-v2-header-content">
				<div class="ced-ebay-v2-title">
					<h1>eBay Listing Profiles</h1>
				</div>

				<div class="ced-ebay-v2-actions">

					<div class="admin-custom-action-button-outer">

					<div class="admin-custom-action-show-button-outer">
	<button id="ced_ebay_reset_item_aspects_btn" title="Reset Item Specifcs" type="button" class="button btn-normal-tt">
		<span>Reset Item Specifcs</span>
	</button>
</div>
					<div class="admin-custom-action-show-button-outer">
	<button id="ced_ebay_remove_all_profiles_btn" title="Remove All Profiles" type="button" class="button btn-normal-tt">
		<span>Delete All Profiles</span>
	</button>
</div>
<div class="admin-custom-action-show-button-outer">
					<button style="background:#5850ec !important;" id="display-action-btn-tt" title="Sync TT Orders" type="button" class="button btn-normal-tt">
		<span><a style="all:unset;" href="https://docs.woocommerce.com/document/ebay-integration-for-woocommerce/#section-8" target="_blank">
					Documentation					</a></span>
	</button>

</div>

</div>
			</div>
		</div>
</div>

			   <?php
				if ( ! session_id() ) {
					session_start();
				}

				?>

				<div id="post-body" class="metabox-holder columns-2">
					<div id="">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
							   <?php
								wp_nonce_field( 'ebay_profile_view', 'ebay_profile_view_actions' );
								$this->display();
								?>
							</form>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<br class="clear">

		<article class="ced_ebay_faq">
  <div class="ced_ebay_faq_description">
	  <h1>Troubleshooting</h1>
  </div>
  <dl class="ced_ebay_faq_collection">
  <dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		What are eBay Profiles?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	  <p>eBay Profiles are used to group products under a same category. By using profiles, you can set product attribute values which will
	  be shared by all the products assigned to a category for which profile is created. Profiles are automatically created as soon
	  as you map WooCommerce store categories to eBay categories.</p>
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		What are required fields in profiles?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	  <p>For some eBay category specific, you will see a 'Required' tag in front of them. To be able to successfully upload your products
	  to eBay, you'll need to fill the values of 'Required' input at the very least. For different categories, profiles will have different category specific 'Required' fields.
	  All the other fields in the profile are optional and can be left blank.</p>
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		I can't see any profiles?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	  <p>Profiles are created automatically when you map a WooCommerce Store category to an eBay Store category. If you don't see a profile please make sure you have mapped the category.</p>
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js" tabindex="0">
		What is Bulk Upload profile action?<i class="fa fa-chevron-down"></i></dt>
	<dd class="ced_ebay_faq_collection_answer faq-answer-js">
	  <p>Bulk Upload profile action is used to automatically upload products under the selected profile to eBay. You can select one or multiple profile and bulk upload them. Here is how Bulk Upload works -
	  <ul style="list-style:circle;">
	<li>
	<p>As soon as you'll select a profile(s) and click Bulk Upload, we will automatically schedule a background job for you which will prepare the products for upload to eBay.</p>
	</li>
	<li>
	<p>Once the data is prepared and sent to eBay we will schedule a job and display a notification at the top about the scheduled job.</p>
	</li>
	<li>
	<p>eBay will process the products data and list them if there aren't any errors.</p>
	</li>
	<li>
	<p>You can check the status of the job in the Status Feed tab of our plugin Please keep in mind that only one job can be scheduled at a time. So please wait for the existing jobs to finish before you can schedule another one.</p>
	</li>
	<li>
	<p>As soon as a job is finished, you can view logs to find out how many of your products have uploaded and if they have failed to upload then what is the reason.</p>
	</li>
	</ul>
	  </p>
	</dd>
	<dt class="ced_ebay_faq_collection_question toggle-faq-js">
	<h3>If you are facing any other sort of issues with our plugin, please reach us at support@cedcommerce.com or you can join our <a style="text-decoration: underline;" href="https://join.skype.com/UHRP45eJN8qQ">Skype</a> or <a style="text-decoration: underline;" href="https://chat.whatsapp.com/BcJ2QnysUVmB1S2wmwBSnE">WhatsApp</a> group.</h3>

	</dt>
  </dl>
</article>
		 <?php
	}
	 /**
	  *
	  * Function for getting current status
	  */
	public function current_action() {
		if ( isset( $_GET['panel'] ) ) {
			$action = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
			return $action;
		} elseif ( isset( $_POST['action'] ) ) {
			if ( ! isset( $_POST['ebay_profile_view_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ebay_profile_view_actions'] ) ), 'ebay_profile_view' ) ) {
				return;
			}
			$action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : '';
			return $action;
		}
	}

	 /**
	  *
	  * Function for processing bulk actions
	  */
	public function process_bulk_action() {
		$sanitized_array = filter_input_array( INPUT_POST, FILTER_SANITIZE_STRING );

		if ( ! session_id() ) {
			session_start();
		}
		wp_nonce_field( 'ced_ebay_profiles_view_page_nonce', 'ced_ebay_profiles_view_nonce' );
		if ( 'bulk-delete' === $this->current_action() || ( isset( $_GET['action'] ) && 'bulk-delete' === $_GET['action'] ) ) {
			if ( ! isset( $_POST['ebay_profile_view_actions'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ebay_profile_view_actions'] ) ), 'ebay_profile_view' ) ) {
				return;
			}
			$profileIds = isset( $sanitized_array['ebay_profile_ids'] ) ? $sanitized_array['ebay_profile_ids'] : array();
			if ( is_array( $profileIds ) && ! empty( $profileIds ) ) {

				global $wpdb;

				$tableName = $wpdb->prefix . 'ced_ebay_profiles';

				$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( wp_unslash( $_GET['user_id'] ) ) : '';

				foreach ( $profileIds as $index => $pid ) {

					$product_ids_assigned = get_option( 'ced_ebay_product_ids_in_profile_' . $pid, array() );
					foreach ( $product_ids_assigned as $index => $ppid ) {
						delete_post_meta( $ppid, 'ced_ebay_profile_assigned' . $user_id );
					}

					$term_id = $wpdb->get_results( $wpdb->prepare( "SELECT `woo_categories` FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id` = %s ", $pid ), 'ARRAY_A' );
					$term_id = json_decode( $term_id[0]['woo_categories'], true );
					foreach ( $term_id as $key => $value ) {
						delete_term_meta( $value, 'ced_ebay_mapped_to_store_category_' . $user_id );
						delete_term_meta( $value, 'ced_ebay_profile_created_' . $user_id );
						delete_term_meta( $value, 'ced_ebay_profile_id_' . $user_id );
						delete_term_meta( $value, 'ced_ebay_mapped_category_' . $user_id );
					}
				}
				foreach ( $profileIds as $id ) {
					$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}ced_ebay_profiles WHERE `id` IN (%s)", $id ) );
				}

				header( 'Location: ' . get_admin_url() . 'admin.php?page=ced_ebay&section=profiles-view&user_id=' . $user_id );
				exit();
			}
		} elseif ( isset( $_GET['panel'] ) && 'edit' == $_GET['panel'] ) {

			$file = CED_EBAY_DIRPATH . 'admin/partials/profile-edit-view.php';
			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	}
}

$ced_ebay_profile_obj = new Ced_EBay_Profile_Table();
$ced_ebay_profile_obj->prepare_items();

?>
