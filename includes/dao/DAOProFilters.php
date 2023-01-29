<?php
declare(strict_types=1);

namespace PPRH\DAO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAOProFilters {

//	public function __construct() {}

	public function set_filters() {
		// used on admin and client side.
		if ( ! \has_action( 'pprh_dao_insert_hint_schema' ) ) {
			\add_filter( 'pprh_dao_insert_hint_schema', array( $this, 'dao_insert_hint_schema' ), 10, 2 );
		}

		// admin
		if ( ! \has_action( 'pprh_append_admin_sql' ) ) {
			\add_filter( 'pprh_append_admin_sql', array( $this, 'append_admin_sql' ), 10, 3 );
		}

		// client side
		if ( ! \has_action( 'pprh_delete_auto_created_hints' ) ) {
			\add_filter( 'pprh_delete_auto_created_hints', array( $this, 'delete_auto_created_hints' ), 10, 2 );
			\add_filter( 'pprh_append_client_sql', array( $this, 'append_client_sql' ), 10, 2 );
			\add_filter( 'pprh_autohint_config', array( $this, 'autohint_config' ), 10, 1 );
		}
	}

	public function autohint_config( $args ) {

	}

	// filter
	public function dao_insert_hint_schema( $args, $new_hint ) {
		if ( isset( $new_hint['post_id'] ) ) {
			$args['columns']['post_id'] = $new_hint['post_id'];
			$args['types'][] = '%s';
		}

		return $args;
	}

	// filter
	public function append_admin_sql( array $query, string $order_by, string $order ):array {
		$admin_post_id = \PPRH\UtilsPro::get_admin_post_id();

		if ( 'global' === $admin_post_id ) {
			$query['args'] = array();
		} elseif ( '' !== $admin_post_id ) {
			$query['sql'] .= ' WHERE post_id = %s OR post_id = %s';
			$query['args'] = array( 'global', $admin_post_id );
		}

		$query['sql'] .= ( '' === $order_by && '' === $order ) ? ' ORDER BY post_id DESC, hint_type ASC, url ASC' : " ORDER BY $order_by $order";
		return $query;
	}

	// filter
	public function append_client_sql( array $query, array $data ):array {
		// if no post ID is set, set it to the homepage post ID (0)
		if ( ! isset( $data['post_id'] ) ) {
			$data['post_id'] = '0';
		}

		$query['sql'] .= ' AND post_id = %s OR post_id = %s';
		array_push( $query['args'], 'global', $data['post_id'] );
		return $query;
	}

	// filter
	public static function delete_auto_created_hints( array $query, string $post_id ):array {
//		if ( 'global' === $post_id ) {
		$query['sql'] .= " AND post_id = %s";
		$query['args'][] = $post_id;
//		}
//		elseif ( '0' === $post_id ) {
//			$query['sql'] .= " AND post_id = %s";
//			$query['args'][] = '0';
//		}
//		else {
//			$query['sql'] .= " AND post_id != %s AND post_id != %s";
//			array_push( $query['args'], 'global', '0' );
//		}

		return $query;
	}

	/**
	 * UTILS BELOW
	 */
	public static function create_assoc_post_obj( int $post_id, string $post_type, string $page_template = '' ):\stdClass {
		return (object) array(
			'post_id'       => $post_id,
			'post_type'     => $post_type,
			'page_template' => $page_template
		);
	}

	// util
	protected static function array_items_to_int( array $posts ):array {
		return array_filter( $posts, function( $post ) {
			return $post->post_id = (int) $post->post_id;
		});
	}

	public static function get_post_modal_types():array {
		$post_modals = \PPRH\Utils\Utils::get_json_option_value( 'pprh_pro_options', 'post_modal_types' );

		if ( \PPRH\Utils\Utils::isArrayAndNotEmpty( $post_modals ) ) {
			return $post_modals;
		}

		return array();
	}

	// utils
	protected function get_active_post_types():array {
		global $wp_post_types;
		$valid_post_types = array();
		$generic_post_types = array( 'attachment', 'page', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'scheduled-action' );

		foreach ( $wp_post_types as $post_type ) {
			if ( ! in_array( $post_type->name, $generic_post_types, true ) ) {
				$valid_post_types[] = $post_type->name;
			}
		}

		return $valid_post_types;
	}

	// utils
	public function post_type_formatter( array $active_post_types ):string {
		$active_post_types_indx = count( $active_post_types ) - 1;
		$str_args = '';

		foreach ( $active_post_types as $idx => $active_post_type ) {
			$str_args .= '%s';

			if ( $idx !== $active_post_types_indx ) {
				$str_args .= ', ';
			}
		}

		return $str_args;
	}

	// tested
	public static function get_post_name_from_url( $url ) {
		$url = trim( $url );

		if ( ! str_contains( $url, '/' ) ) {
			return $url;
		}

		$url_arr = explode( '/', $url );
		$arr_len = count( $url_arr );

		if ( '' !== $url_arr[ $arr_len - 1 ] ) {
			$final = $url_arr[ $arr_len - 1 ];
		} elseif ( '' !== $url_arr[ $arr_len - 2 ] ) {
			$final = $url_arr[ $arr_len - 2 ];
		} else {
			$final = $url;
		}
		return $final;
	}

}
