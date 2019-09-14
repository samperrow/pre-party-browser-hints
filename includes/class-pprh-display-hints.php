<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'PPRH_WP_List_Table' ) ) {
	require_once PPRH_PLUGIN_DIR . '/class-pprh-wp-list-table.php';
}

class PPRH_Display_Hints extends PPRH_WP_List_Table {

	public $_column_headers;
	public $hints_per_page;
	public $table;
	public $data;
	public $items;

	public function __construct() {

		parent::__construct(
			array(
				'singular' => 'url',
				'plural'   => 'urls',
				'ajax'     => true,
				'screen'   => 'toplevel_page_pprh-plugin-settings',
			)
		);

		$results = $this->prepare_items();

		// for bulk updates.
		if ( ! empty( $results ) ) {
			PPRH_Misc::pprh_show_update_result( $results );
		}

		$this->display();
	}


	public function column_default( $item, $column_name ) {
		global $wpdb;
		$post_id = $item['post_id'];

		if ( '0' === $post_id ) {
			$link = 'Home';
		} elseif ( 'global' === $post_id ) {
			$link = 'global';
		} else {
			$table       = $wpdb->prefix . 'posts';
			$post_result = $wpdb->get_row( $wpdb->prepare( "SELECT post_title FROM $table WHERE ID = %s", $post_id ) );
			$post_title  = ( ! empty( $post_result ) ) ? PPRH_Misc::shorten_url( $post_result->post_title ) : '-';
			$link        = ( ! empty( $post_result ) ) ? sprintf( '<a href="/wp-admin/post.php?post=%s&action=edit">%s</a>', $item['post_id'], $post_title ) : 'Error: Post Deleted';
		}

		switch ( $column_name ) {
			case 'url':
				return $item['url'];
			case 'hint_type':
				return $item['hint_type'];
			case 'as_attr':
				return ( $item['as_attr'] ) ? $item['as_attr'] : '-';
			case 'type_attr':
				return ( $item['type_attr'] ) ? $item['type_attr'] : '-';
			case 'crossorigin':
				return ( $item['crossorigin'] ) ? $item['crossorigin'] : '-';
			case 'status':
				return $item['status'];
			case 'post_id':
				return $link;
			case 'created_by':
				return $item['created_by'];
			default:
				return esc_html_e( 'Error', 'pprh' );
		}
	}

	public function global_hint_alert() {
		?>
		<span class="pprh-help-tip-hint">
			<span><?php esc_html_e( 'This is a global resource hint, and is used on all pages and posts. To update this hint, do so from the PP page', 'pprh' ); ?></span>
		</span>
		<?php
	}


	public function column_cb( $item ) {
		return ( 'pprhPostEdit' === PPRH_CHECK_PAGE && 'global' === $item['post_id'] )
			? $this->global_hint_alert()
			: sprintf( '<input type="checkbox" name="urlValue[]" value="%1$s"/>', $item['id'] );
	}

	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'url'         => __( 'URL', 'pprh' ),
			'hint_type'   => __( 'Hint Type', 'pprh' ),
			'as_attr'     => __( 'As Attr', 'pprh' ),
			'type_attr'   => __( 'Type Attr', 'pprh' ),
			'crossorigin' => __( 'Crossorigin', 'pprh' ),
			'status'      => __( 'Status', 'pprh' ),
			'created_by'  => __( 'Created By', 'pprh' ),
			'post_id'     => __( 'Post Name', 'pprh' ),
		);
	}

	public function get_sortable_columns() {
		return array(
			'url'         => array( 'url', true ),
			'hint_type'   => array( 'hint_type', false ),
			'as_attr'     => array( 'as_attr', false ),
			'type_attr'   => array( 'type_attr', false ),
			'crossorigin' => array( 'crossorigin', false ),
			'status'      => array( 'status', false ),
			'created_by'  => array( 'created_by', false ),
			'post_id'     => array( 'post_id', false ),
		);
	}

	public function get_bulk_actions() {
		return array(
			'deleted'  => __( 'Delete', 'pprh' ),
			'enabled'  => __( 'Enable', 'pprh' ),
			'disabled' => __( 'Disable', 'pprh' ),
		);
	}

	public function process_bulk_action() {
		if ( isset( $_POST['urlValue'] ) ) {
			check_admin_referer( 'pprh_display_hints_nonce_action', 'pprh_display_hints_nonce' );

			$hint_ids = filter_input( INPUT_POST, 'urlValue', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			$action   = $this->current_action();

			if ( is_array( $hint_ids ) ) {
				return self::update_hints( $action, $hint_ids );
			}
		}
	}

	public function prepare_items() {
		if ( ! is_admin() ) {
			exit;
		}

		global $wpdb;
		$this->table = $wpdb->prefix . 'pprh_table';

		// $screen = get_current_screen();
		// $option = $screen->get_option( 'per_page', 'option' );
		$option = 'pprh_screen_options';

		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$notice = $this->process_bulk_action();

		$user = get_current_user_id();

		$total_hints          = get_user_meta( $user, $option, true );
		$this->hints_per_page = ( $total_hints ) ? $total_hints : 10;

		$this->load_data();

		$current_page = $this->get_pagenum();
		$total_items  = count( $this->data );

		$data = array_slice( $this->data, ( ( $current_page - 1 ) * $this->hints_per_page ), $this->hints_per_page );

		$this->items = $data;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $this->hints_per_page,
				'total_pages' => ceil( $total_items / $this->hints_per_page ),
			)
		);

		return $notice;
	}

	public function load_data() {
		global $wpdb;
		$per_page     = $this->hints_per_page;
		$current_page = $this->get_pagenum();

		$sql = "SELECT * FROM $this->table";

		if ( 'pprhPostEdit' === PPRH_CHECK_PAGE ) {
			global $post;
			$post_ID = $post->ID;
			$sql    .= $wpdb->prepare( ' WHERE post_id = %s OR post_id = %s', $post_ID, 'global' );
		}

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY post_id DESC';
		}

		$this->data = $wpdb->get_results( $sql, ARRAY_A );
		return $this->data;
	}

	public static function update_hints( $action, $hint_ids ) {
		global $wpdb;
		$table      = $wpdb->prefix . 'pprh_table';
		$concat_ids = implode( ',', array_map( 'absint', $hint_ids ) );
		$notice     = array();

		$sql = ( 'deleted' === $action )
			? "DELETE FROM $table WHERE id IN ($concat_ids)"
			: $wpdb->prepare( "UPDATE $table SET status = %s WHERE id IN ($concat_ids)", $action );

		if ( ! empty( $sql ) ) {
			$wpdb->query( $sql );
			$notice['action'] = $action;
			$notice['result'] = ( $wpdb->last_query === $sql && true === $wpdb->result ) ? 'success' : 'failure';
			return $notice;
		}
	}


	public function no_items() {
		esc_html_e( 'Enter a URL or domain name..', 'pprh' );
	}


	// possible to implement this in the future...
	// public function column_url( $item ) {

	// 	$page = 'pprh-plugin-settings';

	// 	$actions = array(
	// 		'edit'    => sprintf( '<a href="?page=%s&action=%s&hint=%s" class="pprh-edit-hint">Edit</a>', $page, 'edit', $item['id'] ),
	// 		'delete'  => sprintf( '<a href="?page=%s&action=%s&hint=%s">Delete</a>', $page, 'delete', $item['id'] ),
	// 		// 'test'    => '<fieldset class="inline-edit-col-right inline-edit-book"><div class="inline-edit-col"><input type="text" value="' . $item['url'] . '"></div></fieldset>',
	// 	);

	// 	// Return the title contents.
	// 	return sprintf(
	// 		'%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
	// 		$item['url'],
	// 		$item['id'],
	// 		$this->row_actions( $actions )
	// 	);

	// }





}
