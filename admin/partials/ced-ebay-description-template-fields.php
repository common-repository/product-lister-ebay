<?php
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class CED_UMB_EBAY_Description_Template_List extends WP_List_Table {


	/** Class constructor */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Description Template', 'ced-umb-ebay' ), // singular name of the listed records
				'plural'   => __( 'Description Templates', 'ced-umb-ebay' ), // plural name of the listed records
				'ajax'     => false, // does this table support ajax?
			)
		);

		$ebay_templates_dir = WP_CONTENT_DIR . '/uploads/ced-ebay/templates/';
		if ( ! file_exists( $ebay_templates_dir ) ) {
			wp_mkdir_p( $ebay_templates_dir );
		}
	}
	/**
	 * Retrieve Ebay Profiles
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public function get_description_templates( $per_page = 5, $page_number = 1 ) {

		$upload_dir = wp_upload_dir();
		// if ($upload_dir['error']) {
		// TODO: show error if there is a problem with uploads folder
		// }

		$templates = array();
		$files     = glob( $upload_dir['basedir'] . '/ced-ebay/templates/*/template.html' );
		if ( is_array( $files ) ) {
			foreach ( $files as $file ) {
				// save template path relative to WP_CONTENT_DIR
				// $file = str_replace(WP_CONTENT_DIR,'',$file);
				$file        = basename( dirname( $file ) );
				$templates[] = $this->ced_ebay_getItem( $file );
			}
		}

		return $templates;

	}

	public function ced_ebay_getItem( $foldername = false, $fullpath = false ) {
		$upload_dir    = wp_upload_dir();
		$templates_dir = $upload_dir['basedir'] . '/ced-ebay/templates/';

		if ( $foldername ) {
			$fullpath = $templates_dir . $foldername;
		} else {
			$fullpath = $this->folderpath;
		}
		$item = array();

		// default template name
		$item['template_name'] = basename( $fullpath );
		$item['template_path'] = str_replace( WP_CONTENT_DIR, '', $fullpath );
		$item['template_id']   = urlencode( $item['template_name'] );
		if ( file_exists( $fullpath . '/info.txt' ) ) {
			$template_header       = array(
				'Template' => 'Template',
			);
			$template_data         = get_file_data( $fullpath . '/info.txt', $template_header, 'theme' );
			$item['template_name'] = $template_data['Template'];
		}
		return $item;
	}

	/**
	 * The number of profiles.
	 *
	 * @return number
	 */
	public function get_count() {
		global $wpdb;
		$tableName = $wpdb->prefix . 'ced_ebay_discription_template';
		$result    = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}ced_ebay_discription_template", 'ARRAY_A' );
		return count( $result );
	}

	/** Text displayed when no customer data is available */
	public function no_items() {
		esc_html_e( 'No Templates available.', 'ced-umb-ebay' );
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */

	public function column_name( $item ) {
		$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		$title   = '<strong>' . urldecode( $item['template_name'] ) . '</strong>';
		$page    = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : false;

		$actions = array(
			'edit'   => sprintf( '<a href="?page=%s&section=ced_ebay_description_styling&action=%s&template=%s&user_id=%s">%s</a>', $page, 'ced_ebay_edit_template', $item['template_id'], $user_id, __( 'Edit', 'ebay-integration-for-woocommerce' ) ),

			'delete' => sprintf( '<a href="?page=ced_ebay&section=accounts-view&part=description_template&action=%s&template=%s&user_id=%s">Delete</a>', 'ced_ebay_delete_template', $item['template_id'], $user_id ),
		);

		return $title . $this->row_actions( $actions );

	}

	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
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

		$this->items = self::get_description_templates( $per_page, $current_page );

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
			$this->items = self::get_description_templates( $per_page, $current_page );
			$this->renderHTML();
		} else {
			$this->process_bulk_action();
		}

	}

	public function renderHTML() {
		$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( wp_unslash( $_GET['user_id'] ) ) : '';

		?>
		<style>
			.tablenav{
				height: 0px;
				margin: 0px;
				padding-top: 0px;
			}
		</style>
		<div class="ced_umb_ebay_wrap ced_umb_ebay_wrap_extn">

			<div>

				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								wp_nonce_field( 'ced_ebay_description_template_action', 'ced_ebay_description_template_nonce_field' );
								$this->display();
								?>
							</form>
						</div>
					</div>
				</div>
				   <form id="ced-ebay-templates-add" method="get" action="" >
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<?php $request_page = isset( $_REQUEST['page'] ) ? sanitize_text_field( $_REQUEST['page'] ) : false; ?>
		<input type="hidden" name="page" value="<?php echo esc_attr( $request_page ); ?>" />
		<input type="hidden" name="section" value="ced_ebay_description_styling" />
		<input type="hidden" name="action" value="ced_ebay_add_new_template" />
		<input type="hidden" name="user_id" value="<?php echo esc_attr_e( $user_id ); ?>">

		<input type="submit" value="<?php echo esc_attr( 'Create new template' ); ?>" name="submit" class="button-primary" target="_blank">
	</form>
	<br style="clear:both;"/>
			</div>
		</div>

		<?php
	}

	public function process_bulk_action() {
		/** Render configuration setup html of marketplace */
		$user_id = isset( $_GET['user_id'] ) ? sanitize_text_field( $_GET['user_id'] ) : '';
		if ( ! isset( $_POST['ced_ebay_description_template_nonce_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ced_ebay_description_template_nonce_field'] ) ), 'ced_ebay_description_template_action' ) ) {
			$action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : '';
			switch ( $action ) {
				case 'ced_ebay_delete_template':
					$template_name = isset( $_GET['template'] ) ? sanitize_text_field( wp_unslash( $_GET['template'] ) ) : false;
					$upload_dir    = wp_upload_dir();
					// if ( $upload_dir['error'] ) {
					// TODO: show error if there is a problem with uploads folder
					// }
					$template_to_delete = $upload_dir['basedir'] . '/ced-ebay/templates/' . $template_name;
					$template_folders   = glob( $upload_dir['basedir'] . '/ced-ebay/templates/*' );
					if ( in_array( $template_to_delete, $template_folders ) ) {
						require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
						require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
						$fileSystemDirect = new WP_Filesystem_Direct( false );
						$fileSystemDirect->rmdir( $template_to_delete, true );
						header( 'Location: ' . get_admin_url() . 'admin.php?page=ced_ebay&section=accounts-view&part=description_template&user_id=' . $user_id );
						exit();
					} //TODO: Throw error if we can't access template folder.
					break;

			}
		}

	}

}
$CED_UMB_EBAY_Description_Template_List = new CED_UMB_EBAY_Description_Template_List();
$CED_UMB_EBAY_Description_Template_List->prepare_items();

?>
