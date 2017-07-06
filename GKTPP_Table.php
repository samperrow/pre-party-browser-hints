<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GKTPP_WP_List_Table' ) ) {
	require_once GKT_PREP_PLUGIN_DIR . '/GKTPP_WP_List_Table.php';
}

class GKTPP_Table extends GKTPP_WP_List_Table {

	public $_column_headers;

	public function __construct() {
		parent::__construct( array(
			'singular' => 'someURL',
			'plural'   => 'someURLs',
			'ajax'     => false,
		) );
	}

	public static function create_table( $per_page, $page_number = 1 ) {
		global $wpdb;

		$user = get_current_user_id();
		$screen = get_current_screen();
		$option = $screen->get_option( 'per_page', 'option' );

		$per_page = get_user_meta( $user, $option, true );

		if ( '' === $per_page ) {
			settype( $per_page, 'int' );
			$per_page = 10;
		}

		$table = $wpdb->prefix . 'gktpp_table';

		$sql = "SELECT * FROM $table";

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
	     }

		$sql .= " LIMIT $per_page";
	     $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

	     return $result;
	}

	public function get_columns() {
	     $columns = array(
	          'cb'  	  		=> '<input type="checkbox" />',
	          'someURL'   		=> __( 'URL', 'gktpp' ),
	          'hint_type' 		=> __( 'Hint Type', 'gktpp' ),
			'status'    		=> __( 'Status', 'gktpp' ),
			'author'    		=> __( 'Author', 'gktpp' ),
	     );

	     return $columns;
	}

	public static function delete_url( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_table';

		$wpdb->delete(
			$table,
			array( 'id' => $id ),
			array( '%d' )
		);
	}

	public function check_status( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_table';
		$status_check = '';

		if ( $this->current_action() === 'enabled' ) {
			$status_check = 'Enabled';
		}

		elseif ( $this->current_action() === 'disabled' ) {
			$status_check = 'Disabled';
		}

		$wpdb->update( $table,
			array( 'status' => $status_check ),
			array( 'id' => $id ),
			array( '%s', '%d' )
		);
	}

	public static function url_count() {
		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_table';

		$sql = "SELECT COUNT(*) FROM $table";

		return $wpdb->get_var( $sql );
	}

	public function no_items() {
		esc_html_e( 'Enter a URL or domain name..', 'gktpp' );
	}

	public function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'someURL':
				return $item['url'];

			case 'hint_type':
				return $item['hint_type'];

			case 'status':
				return $item['status'];

			case 'author':
				return wp_get_current_user()->display_name;

			default:
				return esc_html_e( 'Error', 'gktpp' );
		}

	}

	public function column_cb( $id ) {
		return sprintf( '<input type="checkbox" name="someURL[]" value="%1$s" />', $id['id'] );
	}

	public function get_sortable_columns() {
		$sortable_columns = array(
			'url'       => array( 'url', true ),
			'hint_type' => array( 'hint_type', false ),
			'status'    => array( 'status', false ),
		);

		return $sortable_columns;
	}

	public function prepare_items() {
		if ( ! is_admin() ) {
			exit;
		}
		?>
		<form method="post" action="<?php admin_url( 'admin.php?page=gktpp-plugin-settings' );?>" style="margin-top: 20px;">
			<?php
			if ( isset( $_GET['updated'] ) ) {
				$this->update_success();
			}

			if ( isset( $_POST['gktpp-settings-submit'] ) && ! ( isset( $_GET['updated'] ) ) ) {
				 $this->insert_url_fail();
			 }

			$this->_column_headers = $this->get_column_info();
			$this->process_bulk_action();

			$user = get_current_user_id();
			$screen = get_current_screen();
			$option = $screen->get_option( 'per_page', 'option' );

			$per_page = get_user_meta( $user, $option, true );

			if ( '' === $per_page ) {
				settype( $per_page, 'int' );
				$per_page = 10;
			}

		     $current_page = $this->get_pagenum();
		     $total_items  = self::url_count();

			$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			) );

			$this->items = self::create_table( $per_page, $current_page );

			$this->display();
			GKTPP_Enter_Data::add_url_hint();
			?>
		</form>
		<?php
		GKTPP_Enter_Data::show_info();

		 $text = sprintf( __( 'Tip: test your website on <a href="%s">WebPageTest.org</a> to know which resource hints and URLs to insert.' ), __( 'https://www.webpagetest.org' ) );
		 echo $text;

	}

	public function get_bulk_actions() {
		$actions = array(
			'deleted'  => __( 'Delete', 'gktpp' ),
			'enabled'  => __( 'Enable', 'gktpp' ),
			'disabled' => __( 'Disable', 'gktpp' ),
		);

		return $actions;
	}

	private function process_bulk_action() {
		if ( ! isset( $_POST['someURL'] ) )
			return;

		global $wpdb;
		$url_ids = filter_input( INPUT_POST, 'someURL', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		if ( ( in_array( $this->current_action(), array( 'deleted', 'enabled', 'disabled' ) ) ) && ( is_array( $url_ids ) ) && ( ! empty( $url_ids )) ) {

			foreach ( $url_ids as $value ) {
				settype( $value, 'int' );

				( ( $this->current_action() === 'enabled' ) || ( 'disabled' === $this->current_action() ) )
					? self::check_status( $value )
					: self::delete_url( $value );
			}

			if ( 'enabled' === $this->current_action() ) {
				GKTPP_Options::url_updated( $this->current_action() );
			} elseif ( 'disabled' === $this->current_action() ) {
				GKTPP_Options::url_updated( $this->current_action() );
			} elseif ( 'deleted' === $this->current_action() ) {
				GKTPP_Options::url_updated( $this->current_action() );
			}
		}
	}

	public static function collect_page_url_IDs() {
		if ( ! isset( $_POST['gktpp_pages'] ) ) {
			return;
		}

		$page_id_array = array();
		foreach ( $_POST['gktpp_pages'] as $key ) {
			$page_id_array[] = $key;
		}

		return json_encode( $page_id_array );
	}

	private function insert_url_fail() {
		?>
		<div class="inline notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Please enter a valid URL and resource hint type.', 'gktpp' ); ?></p>
		</div>
		<?php
	}

	private function update_success() {
		?>
		<div class="inline notice notice-success is-dismissible">
			<p><?php esc_html_e( 'URL added successfully.', 'gktpp' ); ?></p>
		</div>
		<?php
	}

}
