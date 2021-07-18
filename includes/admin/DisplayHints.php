<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( WP_List_Table::class ) ) {
	require_once 'wp-list-table.php';
}

class DisplayHints extends WP_List_Table {

	public $_column_headers;
	public $hints_per_page;
	public $table;
	public $items;
	protected $columns;

	public function __construct( $doing_ajax ) {
		parent::__construct( array(
            'ajax'     => true,
			'plural'   => 'urls',
			'screen'   => 'toplevel_page_' . PPRH_MENU_SLUG,
			'singular' => 'url',
		) );

		if ( ! $doing_ajax ) {
			$this->prepare_items();
			$this->display();
		}
	}

	public function column_default( $item, $column_name ) {
		if ( 'post_id' === $column_name ) {
            return Utils::apply_pprh_filters( 'pprh_dh_get_post_link', array( $item['post_id'] ) );
        }

		if ( '' === $item[$column_name] ) {
            return $this->set_item( $item[$column_name] );
        }

		return $item[$column_name];
	}

	private function set_item( $item ) {
		return ( ! empty( $item ) ) ? Utils::clean_hint_attr( $item ) : '-';
	}

	public function get_columns() {
		$columns = array(
            'cb'          => '<input type="checkbox" />',
            'url'         => __( 'URL', 'pprh' ),
            'hint_type'   => __( 'Hint Type', 'pprh' ),
            'as_attr'     => __( 'As Attr', 'pprh' ),
            'type_attr'   => __( 'Type Attr', 'pprh' ),
            'crossorigin' => __( 'Crossorigin', 'pprh' ),
            'media'       => __( 'Media', 'pprh' ),
            'status'      => __( 'Status', 'pprh' ),
            'created_by'  => __( 'Created By', 'pprh' ),
        );

		return Utils::apply_pprh_filters( 'pprh_dh_get_columns', array( $columns ) );
	}

	public function get_sortable_columns() {
		$arr = array(
            'url'         => array('url', true),
            'hint_type'   => array('hint_type', false),
            'status'      => array('status', false),
            'created_by'  => array('created_by', false)
        );

		return Utils::apply_pprh_filters( 'pprh_dh_get_sortortable_columns', array( $arr ) );
	}

	public function get_bulk_actions() {
		return array(
            '2' => __( 'Delete', 'pprh' ),
            '3' => __( 'Enable', 'pprh' ),
            '4' => __( 'Disable', 'pprh' )
        );
	}

	public function prepare_items() {
		$this->hints_per_page = $this->set_hints_per_page();
		$this->columns = $this->get_columns();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $this->columns, array(), $sortable );
		$current_page = $this->get_pagenum();
		$all_hints = DAO::get_pprh_hints( true, array() );
		$this->items = array_slice( $all_hints, ( ( $current_page - 1 ) * $this->hints_per_page ), $this->hints_per_page );
		$total_items = count( $all_hints );

		$this->set_pagination_args(
            array(
				'per_page'    => $this->hints_per_page,
				'total_items' => $total_items,
				'total_pages' => ceil( $total_items / $this->hints_per_page ),
            )
        );
	}

	public function set_hints_per_page() {
		$user = \get_current_user_id();
//		$screen = \get_current_screen();
		$option = 'pprh_per_page';
		$hints_per_page_meta = (int) \get_user_meta( $user, $option, true );
		return empty( $hints_per_page_meta ) ? 10 : $hints_per_page_meta;
    }

	public function no_items() {
		esc_html_e( 'Enter a URL or domain name..', 'pprh' );
	}

	public function column_url( $item ) {
	    if ( ! empty( $item['id'] ) ) {
			$actions = array(
				'edit'   => sprintf( '<a id="pprh-edit-hint-%1$s" class="pprh-edit-hint">%2$s</a>', $item['id'], 'Edit' ),
				'delete' => sprintf( '<a id="pprh-delete-hint-%1$s">%2$s</a>', $item['id'], 'Delete' ),
			);
        } else {
	        $actions = array( 'edit' => '', 'delete' => '' );
        }

		return sprintf( '%1$s %2$s', $item['url'], $this->row_actions( $actions ) );
	}

	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="urlValue[]" value="%1$s"/>', $item['id'] );
	}

	public function inline_edit_row( $item ) {
		$json = json_encode( $item,true );
		$item_id = Utils::strip_non_numbers( $item['id'] );
		?>
			<tr class="pprh-row edit <?php echo $item_id; ?>">
				<td colspan="9">
					<table id="pprh-edit-<?php echo $item_id; ?>" aria-label="Update this resource hint">
                        <thead>
                            <tr>
                                <th colspan="5" scope="colgroup"><?php esc_html_e( 'Update Resource Hint', 'pprh' ); ?></th>
                            </tr>
                        </thead>
						<?php
							$new_hint = new NewHint();
						    $new_hint->insert_table_body();
						?>
                        <tr>
                            <td colspan="5">
                                <button type="button" class="pprh-cancel button cancel">Cancel</button>
                                <button type="button" class="pprh-update button button-primary save">Update</button>
                            </td>
                        </tr>
					</table>
				    <input type="hidden" id="pprh-hint-storage-<?php echo $item_id; ?>" class="pprh-hint-storage <?php echo $item_id; ?>" value='<?php echo $json; ?>'>
				</td>
		    </tr>
		<?php
	}


}
