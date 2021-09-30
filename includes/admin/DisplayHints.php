<?php

namespace PPRH;

use PPRH\Utils\Sanitize;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( WP_List_Table::class ) ) {
	require_once 'wp-list-table.php';
}

class DisplayHints extends WP_List_Table {

	protected $columns;

	public $_column_headers;
	public $hints_per_page;
	public $table;
	public $items;

	public function __construct( bool $doing_ajax, int $plugin_page ) {
		parent::__construct( array(
			'ajax'        => true,
			'plural'      => 'urls',
			'screen'      => 'toplevel_page_' . PPRH_MENU_SLUG,
			'singular'    => 'url',
			'plugin_page' => $plugin_page,
            'doing_ajax'  => $doing_ajax
		) );

		if ( ! $doing_ajax ) {
			$this->prepare_items();
			$this->display();
		}
	}

	public function column_default( $item, $column_name ) {
		if ( 'post_id' === $column_name ) {
			return \apply_filters( 'pprh_dh_get_post_link', $item );
		}

		if ( '' === $item[ $column_name ] ) {
			return $this->set_item( $item[ $column_name ] );
		}

		return $item[ $column_name ];
	}

	private function set_item( $item ) {
		return ( empty( $item ) ) ? '-' : Sanitize::clean_hint_attr( $item );
	}

	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'url'         => __( 'URL', 'pre-party-browser-hints' ),
			'hint_type'   => __( 'Hint Type', 'pre-party-browser-hints' ),
			'as_attr'     => __( 'As Attr', 'pre-party-browser-hints' ),
			'type_attr'   => __( 'Type Attr', 'pre-party-browser-hints' ),
			'crossorigin' => __( 'Crossorigin', 'pre-party-browser-hints' ),
			'media'       => __( 'Media', 'pre-party-browser-hints' ),
			'status'      => __( 'Status', 'pre-party-browser-hints' ),
			'created_by'  => __( 'Created By', 'pre-party-browser-hints' ),
		);

		return \apply_filters( 'pprh_dh_get_columns', $columns );
	}

	public function get_sortable_columns() {
		$arr = array(
			'url'        => array( 'url', true ),
			'hint_type'  => array( 'hint_type', false ),
			'status'     => array( 'status', false ),
			'created_by' => array( 'created_by', false )
		);

		return \apply_filters( 'pprh_dh_get_sortortable_columns', $arr );
	}

	public function get_bulk_actions():array {
		return array(
			'2' => __( 'Delete', 'pre-party-browser-hints' ),
			'3' => __( 'Enable', 'pre-party-browser-hints' ),
			'4' => __( 'Disable', 'pre-party-browser-hints' )
		);
	}

	public function prepare_items() {
		$this->hints_per_page  = $this->set_hints_per_page();
		$this->columns         = $this->get_columns();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $this->columns, array(), $sortable );
		$current_page          = $this->get_pagenum();
		$all_hints             = DAO::get_admin_hints();
		$this->items           = array_slice( $all_hints, ( ( $current_page - 1 ) * $this->hints_per_page ), $this->hints_per_page );
		$total_items           = count( $all_hints );

		$this->set_pagination_args(
			array(
				'per_page'    => $this->hints_per_page,
				'total_items' => $total_items,
				'total_pages' => ceil( $total_items / $this->hints_per_page ),
			)
		);
	}

	public function set_hints_per_page() {
		$user   = \get_current_user_id();
		$option = 'pprh_per_page';
		$hints_per_page_meta = (int) \get_user_meta( $user, $option, true );
		return empty( $hints_per_page_meta ) ? 10 : $hints_per_page_meta;
	}

	public function no_items() {
		esc_html_e( 'Enter a URL or domain name..', 'pre-party-browser-hints' );
	}

	protected function column_url( $item ) {
		if ( ! empty( $item['id'] ) ) {
			$actions = array(
				'edit'   => sprintf( '<a id="pprh-edit-hint-%1$s" class="pprh-edit-hint">%2$s</a>', $item['id'], 'Edit' ),
				'delete' => sprintf( '<a id="pprh-delete-hint-%1$s">%2$s</a>', $item['id'], 'Delete' ),
			);
		} else {
			$actions = array( 'edit' => '', 'delete' => '' );
		}

		echo sprintf( '%1$s %2$s', $item[ 'url' ], $this->row_actions( $actions ) );
	}

	protected function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="urlValue[]" value="%1$s"/>', $item['id'] );
	}

	protected function inline_edit_row( array $hint ) {
	    $hint_id = $hint['id'] ?? '';
		$hint_id_clean = Sanitize::strip_non_numbers( $hint_id, true );
		?>
		<tr class="pprh-row edit <?php echo $hint_id_clean; ?>">
			<td colspan="9">
				<table id="pprh-edit-<?php echo $hint_id_clean; ?>" aria-label="Update this resource hint">
					<thead>
						<tr>
							<th colspan="5" scope="colgroup"><?php esc_html_e( 'Update Resource Hint', 'pre-party-browser-hints' ); ?></th>
						</tr>
					</thead>

                    <tbody>
                        <?php
                            $new_hint = new NewHint( $hint );
                            $new_hint->insert_hint_table();
                        ?>
                    </tbody>

					<tr>
						<td colspan="5">
							<button type="button" class="pprh-cancel button cancel">Cancel</button>
							<button type="button" class="pprh-update button button-primary save">Update</button>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<?php
	}

}
