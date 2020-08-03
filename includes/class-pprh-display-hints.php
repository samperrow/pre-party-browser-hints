<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( WP_List_Table::class ) ) {
	require_once PPRH_ABS_DIR . '/includes/class-pprh-wp-list-table.php';
}

class Display_Hints extends WP_List_Table {

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

		do_action( 'pprh_load_display_hints_child' );

		if ( ! wp_doing_ajax() ) {
			$this->prepare_items( null );
			$this->display();
		}
	}

	public function column_default( $item, $column_name ) {

		$link = ( isset( $item['post_id'] ) ) ? apply_filters( 'pprh_get_post_link', $item['post_id'] ) : '';

		switch ( $column_name ) {
			case 'url':
				return $item['url'];
			case 'hint_type':
				return $item['hint_type'];
			case 'as_attr':
				return $this->set_item( $item['as_attr'] );
			case 'type_attr':
				return $this->set_item( $item['type_attr'] );
			case 'crossorigin':
				return $this->set_item( $item['crossorigin'] );
			case 'status':
				return $item['status'];
			case 'created_by':
				return $item['created_by'];
			case 'post_id':
				return $link;
			default:
				return esc_html_e( 'Error', 'pprh' );
		}
	}

	private function set_item( $item ) {
	    return ( ! empty( $item ) ) ? Utils::clean_hint_attr($item) : '-';
    }

	public function get_columns() {
		$cols = array(
			'cb'          => '<input type="checkbox" />',
			'url'         => __( 'URL', 'pprh' ),
			'hint_type'   => __( 'Hint Type', 'pprh' ),
			'as_attr'     => __( 'As Attr', 'pprh' ),
			'type_attr'   => __( 'Type Attr', 'pprh' ),
			'crossorigin' => __( 'Crossorigin', 'pprh' ),
			'status'      => __( 'Status', 'pprh' ),
			'created_by'  => __( 'Created By', 'pprh' ),
		);

		return apply_filters( 'pprh_get_columns', $cols );
	}

	public function get_sortable_columns() {
		$sort_cols = array(
			'url'         => array( 'url', true ),
			'hint_type'   => array( 'hint_type', false ),
			'as_attr'     => array( 'as_attr', false ),
			'type_attr'   => array( 'type_attr', false ),
			'crossorigin' => array( 'crossorigin', false ),
			'status'      => array( 'status', false ),
			'created_by'  => array( 'created_by', false ),
		);
		return apply_filters( 'pprh_get_sort_cols', $sort_cols );
	}

	public function get_bulk_actions() {
		return array(
			'delete'  => __( 'Delete', 'pprh' ),
			'enable'  => __( 'Enable', 'pprh' ),
			'disable' => __( 'Disable', 'pprh' ),
		);
	}

	public function prepare_items( $results = null ) {
		if ( ! is_admin() ) {
			exit;
		}

		$option                = 'pprh_screen_options';
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$user                  = get_current_user_id();
		$total_hints           = (int) get_user_meta( $user, $option, true );
		$this->hints_per_page  = ( ! empty( $total_hints ) ) ? $total_hints : 10;
		$this->load_data( $results );
		$current_page = $this->get_pagenum();
		$data = array_slice( $this->data, ( ( $current_page - 1 ) * $this->hints_per_page ), $this->hints_per_page );
		$this->items = $data;
		$total_items = count( $this->data );

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $this->hints_per_page,
				'total_pages' => ceil( $total_items / $this->hints_per_page ),
				'orderby'     => ! empty( $_REQUEST['orderby'] ) && '' !== $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'title',
				'order'       => ! empty( $_REQUEST['order'] ) && '' !== $_REQUEST['order'] ? $_REQUEST['order'] : 'asc',
			)
		);
	}

	public function load_data( $results = null ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;
		$sql = "SELECT * FROM $table";

		$sql = apply_filters( 'pprh_dh_append_sql', $sql, $results );

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY post_id DESC';
		}

		$this->data = $wpdb->get_results( $sql, ARRAY_A );
		return $this->data;
	}

	public function no_items() {
		esc_html_e( 'Enter a URL or domain name..', 'pprh' );
	}

	public function column_url( $item ) {

		$actions = array(
			'edit'    => sprintf( '<a id="pprh-edit-hint-%s" class="pprh-edit-hint">Edit</a>', $item['id'] ),
			'delete'  => sprintf( '<a id="pprh-delete-hint-%s">Delete</a>', $item['id'] ),
		);

		return sprintf( '%1$s %2$s', $item['url'], $this->row_actions( $actions ) );
	}

	public function inline_edit_row( $item ) {
		if ( ! class_exists( New_Hint::class ) ) {
			require_once PPRH_ABS_DIR . '/includes/class-pprh-new-hint.php';
		}

		$json = json_encode( $item,true );
		$item_id = $item['id'];

		?>
			<tr class="pprh-row edit <?php echo $item_id; ?>">
				<td colspan="8">
					<table id="pprh-edit-<?php echo $item_id; ?>">
						<?php
							$new_hint = new New_Hint();
						    $new_hint->insert_table_body();
						?>
                        <tr>
                            <td colspan="5">
                                <button style="margin: 0 20px;" type="button" class="pprh-cancel button cancel">Cancel</button>
                                <button style="margin: 0 20px;" type="button" class="pprh-update button button-primary save">Update</button>
                            </td>
                        </tr>
					</table>
				    <input type="hidden" class="pprh-hint-storage <?php echo $item_id; ?>" value='<?php echo $json; ?>'>
				</td>
		    </tr>

		<?php
	}

}
