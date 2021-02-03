<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( WP_List_Table::class ) ) {
	require_once PPRH_ABS_DIR . 'includes/wp-list-table.php';
}

class Display_Hints extends WP_List_Table {

	public $_column_headers;
	public $hints_per_page;
	public $table;
	public $data;
	public $items;

	public function __construct() {
		parent::__construct( array(
            'ajax' => true,
			'plural' => 'urls',
			'screen' => 'toplevel_page_pprh-plugin-settings',
			'singular' => 'url'
		) );

		if ( ! wp_doing_ajax() ) {
			$this->prepare_items();
			$this->display();
		}
	}

	public function column_default( $item, $column_name ) {
		switch ($column_name) {
			case 'url':
				return $item['url'];
			case 'hint_type':
				return $item['hint_type'];
			case 'as_attr':
				return $this->set_item($item['as_attr']);
			case 'type_attr':
				return $this->set_item($item['type_attr']);
			case 'crossorigin':
				return $this->set_item($item['crossorigin']);
			case 'status':
				return $item['status'];
			case 'created_by':
				return $item['created_by'];
			case 'post_id':
				return apply_filters('pprh_get_post_link', $item['post_id']);
			default:
				return esc_html('Error', 'pprh');
		}
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
		$this->data = $dao->get_hints( null );
		$current_page = $this->get_pagenum();
		$data = array_slice( $this->data, ( ( $current_page - 1 ) * $this->hints_per_page ), $this->hints_per_page );
		$this->items = $data;
		$total_items = count( $this->data );

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

	public function column_url( $item, $hide ) {

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

	protected function column_cb( $item, $hide ) {
    //		$on_posts_page_and_global = ( ! empty( $item['post_id'] )
    //            ? apply_filters( 'pprh_on_posts_page_and_global', $item['post_id'] )
    //            : false );

		if ( $hide ) {
		    $this->global_hint_alert();
        } else {
			return sprintf( '<input type="checkbox" name="urlValue[]" value="%1$s"/>', $item['id'] );
		}
	}

	public function global_hint_alert() {
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
					<table id="pprh-edit-<?php echo $item_id; ?>">
						<?php
							$new_hint = new New_Hint();
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
