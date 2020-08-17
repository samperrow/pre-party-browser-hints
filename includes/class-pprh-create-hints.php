<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Create_Hints {
	
	public $results = array(
		'action'    => '',
		'result'    => '',
		'msg'       => '',
		'new_hints' => array(),
	);

	public function __construct( $data ) {
		if ( ! defined( 'CREATING_HINT' ) || ! CREATING_HINT ) {
			exit();
		}

		do_action( 'pprh_load_create_hints_child' );

		$this->prev_hints = (object) array();
		$this->init( $data );
	}

	public function init( $hints ) {
		foreach ( $hints as $hint ) {
			$new_hint = (object) $this->create_hint( $hint );

			$dup_hints_exist = apply_filters( 'pprh_hc_dup_hints_exist', $new_hint );

			if ( ! empty( $dup_hints_exist->result ) ) {
				$this->remove_dup_hints( $new_hint );
			}

			if ( ! empty( $dup_hints_exist->make_global ) ) {
				$new_hint->post_id = 'global';
			}

			if ( ! empty( $dup_hints_exist->msg ) && '' !== $dup_hints_exist->msg ) {
				$this->check_and_append_str( $dup_hints_exist['msg'] );
				$this->results['result'] = 'warning';
			} else {
				$this->insert_hint( $new_hint );
				array_push( $this->results['new_hints'], $new_hint );
			}
		}

		return $this->results;
	}

	public function create_hint( $hint ) {
		if ( empty( $hint->url ) || empty( $hint->hint_type ) ) {
			return 'err';
		}

		$hint_type = $this->set_hint_type( $hint->hint_type );
		$url       = $this->set_url( $hint, $hint_type );
		$file_type = $this->set_file_type( $url );

		$new_hint = array(
			'hint_type'    => $hint_type,
			'url'          => $url,
			'file_type'    => $file_type,
			'as_attr'      => $this->set_as_attr( $hint, $file_type ),
			'type_attr'    => $this->set_type_attr( $hint, $file_type ),
			'crossorigin'  => $this->set_crossorigin( $hint, $file_type ),
			'auto_created' => ( isset( $hint->auto_created ) ? 1 : 0 ),
		);

		if ( isset( $hint->post_id ) ) {
			$new_hint = apply_filters( 'pprh_create_hints_add_to_hint', $new_hint, $hint );
		}

		return $new_hint;
	}

	protected function set_hint_type( $type ) {
		return Utils::clean_hint_type( $type );
	}

	protected function set_url( $hint, $type ) {
		if ( preg_match( '/(dns-prefetch|preconnect)/', $type ) ) {
			$url = $this->parse_for_domain_name( $hint->url );
		} else {
			$url = $hint->url;
		}

		return filter_var( str_replace( ' ', '', $url ), FILTER_SANITIZE_URL );
	}

	protected function parse_for_domain_name( $url ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! empty( $parsed_url['scheme'] ) ) {
			$this->check_and_append_str( ' Only the domain name of the entered URL is needed for that hint type.' );
			$url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
		} elseif ( strpos( $url, '//' ) === 0 ) {
			$url = '//' . $parsed_url['host'];
		} else {
			$url = '//' . $parsed_url['path'];
		}
		return $url;
	}

	protected function set_file_type( $url ) {
		$basename = pathinfo( $url )['basename'];
		return strpbrk( $basename, '?' ) !== '' ? strrchr( explode( '?', $basename )[0], '.' ) : strrchr( $basename, '.' );
	}

	protected function set_crossorigin( $hint, $file_type ) {
		return ( ! empty( $hint->crossorigin ) || ( preg_match( '/fonts.(googleapis|gstatic).com/i', $hint->url ) || preg_match( '/(.woff|.woff2|.ttf|.eot)/', $file_type ) ) ) ? 'crossorigin' : '';
	}

	protected function set_as_attr( $hint, $file_type ) {
		$as_attr = ( ! empty( $hint->as_attr ) ) ? $hint->as_attr : '';
		$media_types = array(
			'.js'    => 'script',
			'.css'   => 'style',
			'.mp3'   => 'audio',
			'.mp4'   => 'video',
			'.vtt'   => 'track',
			'.swf'   => 'embed',
			'.woff'  => 'font',
			'.woff2' => 'font',
			'.ttf'   => 'font',
			'.eot'   => 'font',
			'.jpg'   => 'image',
			'.jpeg'  => 'image',
			'.png'   => 'image',
			'.svg'   => 'image',
			'.webp'  => 'image',
		);

		return ( ! empty( $as_attr ) ) ? Utils::clean_hint_attr( $as_attr ) : $this->set_file_type_mime( $media_types, $file_type );
	}

	protected function set_type_attr( $hint, $file_type ) {
		$type_attr = ( ! empty( $hint->type_attr ) ) ? $hint->type_attr : '';
		$mimes = array(
			'.woff'  => 'font/woff',
			'.woff2' => 'font/woff2',
			'.ttf'   => 'font/ttf',
			'.eot'   => 'font/eot',
		);

		return ( ! empty( $type_attr ) ) ? Utils::clean_hint_attr( $type_attr ) : $this->set_file_type_mime( $mimes, $file_type );
	}

	protected function set_file_type_mime( $possible_types, $file_type ) {
		foreach ( $possible_types as $key => $val ) {
			if ( $key === $file_type ) {
				return $val;
			}
		}
		return '';
	}

	public function duplicate_post_hint_exists( $new_hint ) {
		global $wpdb;
		$table     = PPRH_DB_TABLE;
		$hint_type = $new_hint->hint_type;
		$url       = $new_hint->url;

		$sql = apply_filters( 'pprh_hc_check_dup_hints', $sql, $new_hint );


		if ( count( $prev_hints ) > 0 ) {
			$this->check_and_append_str( ' An identical resource hint already exists!' );
			$this->results['result'] = 'warning';
			return true;
		} else {
			return false;
		}
	}

	protected function check_and_append_str( $str ) {
		if ( false === strpos( $this->results['msg'], $str ) ) {
			$this->results['msg'] .= $str ;
		}
	}

	protected function get_dup_hints( $hint_type, $url ) {
		global $wpdb;
		$table = PPRH_DB_TABLE;

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM $table WHERE hint_type = %s AND url = %s", $hint_type, $url )
		);
	}

	protected function remove_dup_hints( $new_hint ) {
		global $wpdb;

		// if a global hint is being created, previous identical hints for posts can be deleted.
		$wpdb->delete(
			PPRH_DB_TABLE,
			array(
				'url'       => $new_hint->url,
				'hint_type' => $new_hint->hint_type,
			),
			array( '%s', '%s' )
		);
		$this->check_and_append_str( ' Identical resource hints used on posts/pages were removed.' );
	}

	protected function insert_hint( $new_hint ) {
		global $wpdb;
		$current_user = wp_get_current_user()->display_name;
		$this->results['action'] = 'created';
		$query = array(
			'args' => array(
				'url'         => $new_hint->url,
				'hint_type'   => $new_hint->hint_type,
				'status'      => 'enabled',
				'as_attr'     => $new_hint->as_attr,
				'type_attr'   => $new_hint->type_attr,
				'crossorigin' => $new_hint->crossorigin,
				'created_by'  => ( ! empty( $current_user ) ? $current_user : ''),
				'auto_created' => $new_hint->auto_created,
			),
			'types' => array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		$query = apply_filters( 'pprh_insert_hint_filter', $query, $new_hint );

		$wpdb->insert(
			PPRH_DB_TABLE,
			$query['args'],
			$query['types']
		);

		$this->results['result'] = ( $wpdb->result ) ? 'success' : 'error';
		$this->check_and_append_str( ' Resource hint created successfully.' );
	}

}
