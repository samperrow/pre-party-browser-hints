<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( WP_List_Table::class ) ) {
	require_once PPRH_ABS_DIR . 'includes/wp-list-table.php';
}

class DisplayHints extends WP_List_Table {

	public $_column_headers;
	public $hints_per_page;
	public $table;
	public $items;

	public function __construct() {
		parent::__construct( array(
            'ajax'     => true,
			'plural'   => 'urls',
			'screen'   => 'toplevel_page_pprh-plugin-settings',
			'singular' => 'url'
		) );

		if ( ! wp_doing_ajax() ) {
			$this->prepare_items();
			$this->display();
		}
	}

	public function column_default( $item, $column_name ) {

		if ('post_id' === $column_name) {
            return apply_filters('pprh_get_post_link', $item['post_id']);
        }

		if ('' === $item[$column_name]) {
            return $this->set_item( $item[$column_name] );
        }

		return $item[$column_name];
	}

	private function set_item( $item ) {
		return ( ! empty( $item ) ) ? Utils::clean_hint_attr( $item ) : '-';
	}

	public function get_columns() {
		$arr = array(
            'cb'          => '<input type="checkbox" />',
            'url'         => __('URL', 'pprh'),
            'hint_type'   => __('Hint Type', 'pprh'),
            'as_attr'     => __('As Attr', 'pprh'),
            'type_attr'   => __('Type Attr', 'pprh'),
            'crossorigin' => __('Crossorigin', 'pprh'),
            'status'      => __('Status', 'pprh'),
            'created_by'  => __('Created By', 'pprh'),
        );

		return apply_filters('pprh_get_columns', $arr);
	}

	public function get_sortable_columns() {
		$arr = array(
            'url'         => array('url', true),
            'hint_type'   => array('hint_type', false),
            'as_attr'     => array('as_attr', false),
            'type_attr'   => array('type_attr', false),
            'crossorigin' => array('crossorigin', false),
            'status'      => array('status', false),
            'created_by'  => array('created_by', false)
        );

		return apply_filters('pprh_get_sort_cols', $arr);
	}

	public function get_bulk_actions() {
		return array(
            'delete'   => __( 'Delete', 'pprh' ),
            'enable'  => __( 'Enable', 'pprh' ),
            'disable' => __( 'Disable', 'pprh' )
        );
	}

	public function prepare_items() {
		$dao = new DAO();
		$option = 'pprh_screen_options';
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$user = get_current_user_id();
		$total_hints = (int) get_user_meta( $user, $option, true );
		$this->hints_per_page = ( ! empty( $total_hints ) ) ? $total_hints : 10;
		$data = $dao->get_hints_ordered( null );
		$current_page = $this->get_pagenum();
		$this->items = array_slice( $data, ( ( $current_page - 1 ) * $this->hints_per_page ), $this->hints_per_page );
		$total_items = count( $data );

		$this->set_pagination_args(
            array(
				'order'       => ! empty( $_REQUEST['order'] ) && '' !== $_REQUEST['order'] ? $_REQUEST['order'] : 'asc',
				'orderby'     => ! empty( $_REQUEST['orderby'] ) && '' !== $_REQUEST['orderby'] ? $_REQUEST['orderby'] : 'title',
				'per_page'    => $this->hints_per_page,
				'total_items' => $total_items,
				'total_pages' => ceil( $total_items / $this->hints_per_page ),
            )
        );
	}

	public function no_items() {
		esc_html_e( 'Enter a URL or domain name..', 'pprh' );
	}

	public function column_url( $item, $hide = false ) {
	    if ( $hide ) {
	        $actions = array(
                'edit'   => '',
                'delete' => ''
            );
        } else {
			$actions = array(
				'edit'   => sprintf( '<a id="pprh-edit-hint-%1$s" class="pprh-edit-hint">%2$s</a>', $item['id'], 'Edit' ),
				'delete' => sprintf( '<a id="pprh-delete-hint-%1$s">%2$s</a>', $item['id'], 'Delete' ),
			);
        }

		return sprintf( '%1$s %2$s', $item['url'], $this->row_actions( $actions ) );
	}

	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="urlValue[]" value="%1$s"/>', $item['id'] );
	}

	protected function global_hint_alert() {
		?>
        <span class="pprh-help-tip-hint">
			<span><?php esc_html_e( 'This is a global resource hint, and is used on all pages and posts. To update this hint, please do so from the main Pre* Party plugin page.', 'pprh' ); ?></span>
		</span>
		<?php
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
				    <input type="hidden" class="pprh-hint-storage <?php echo $item_id; ?>" value='<?php echo $json; ?>'>
				</td>
		    </tr>
		<?php
	}



}
