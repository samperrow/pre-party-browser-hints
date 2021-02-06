<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CreateHints {

	public $result = array();

//	protected $new_hint = array();

	public function __construct() {
//		if ( ! defined( 'CREATING_HINT' ) || ! CREATING_HINT ) {
////			exit();
////		}

		$this->result = array(
			'new_hint' => array(),
			'response' => array(
				'msg'     => '',
				'status'  => '',
				'success' => false
			),
		);

		if ( defined( 'PPRH_PRO_ABS_DIR' ) ) {
			include_once PPRH_PRO_ABS_DIR . 'includes/CreateHintsChild.php';
		}
	}

	// hint creation utils
	public static function create_pprh_hint( $raw_data ) {
		$create_hints = new CreateHints();
		$dao = new DAO();
		$new_hint = $create_hints->create_hint( $raw_data );

		if ( is_array( $new_hint ) ) {
			$duplicate_hints_exist = $create_hints->duplicate_hints_exist( $new_hint );

			if ( $duplicate_hints_exist ) {
				return $dao->create_db_result( false, '', 'A duplicate hint already exists!', 'create', null );
			}
			return $new_hint;
		}
		return false;
	}

//	public static function create_raw_hint_array( $url, $hint_type, $auto_created = 0, $as_attr = '', $type_attr = '', $crossorigin = '', $post_id = '', $post_url = '' ) {
//		$arr = array(
//			'url'          => $url,
//			'as_attr'      => $as_attr,
//			'hint_type'    => $hint_type,
//			'type_attr'    => $type_attr,
//			'crossorigin'  => $crossorigin,
//			'auto_created' => $auto_created
//		);
//
//		$arr = apply_filters( 'pprh_append_hint_array', $arr, $post_id, $post_url );
//		return $arr;
//	}

	public function duplicate_hints_exist( $new_hint ) {
		$duplicate_hints = $this->get_duplicate_hints( $new_hint );
		return ( count( $duplicate_hints ) > 0 );
	}


	public function create_hint( $hint ) {
		if ( empty( $hint['url'] ) || empty( $hint['hint_type'] ) ) {
			return false;
		}

		$hint_type = $this->get_hint_type( $hint['hint_type'] );
		$url = $this->get_url( $hint['url'], $hint_type );
		$file_type = $this->get_file_type( $url );

		$new_hint = array(
			'url'          => $url,
			'as_attr'      => $this->set_as_attr( $hint, $file_type ),
			'hint_type'    => $hint_type,
			'type_attr'    => $this->set_type_attr( $hint, $file_type ),
			'crossorigin'  => $this->set_crossorigin( $hint, $file_type ),
			'auto_created' => ( ! empty( $hint['auto_created'] ) ? 1 : 0 )
		);

		return apply_filters( 'pprh_append_hint', $new_hint, $hint );
	}

	public function get_hint_type( $type ) {
		return Utils::clean_hint_type( $type );
	}

	public function get_url( $url, $type ) {
		$url = Utils::clean_url( $url );

		if ( preg_match( '/(dns-prefetch|preconnect)/', $type ) ) {
			$url = $this->parse_for_domain_name( $url );
		}

		return $url;
	}

	public function parse_for_domain_name( $url ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! empty( $parsed_url['host'] ) && ! empty( $parsed_url['path'] ) ) {
			$this->result['response']['msg'] .= ' Only the domain name of the entered URL is needed for that hint type.';
			$url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
		} elseif ( strpos( $url, '//' ) === 0 ) {
			$url = '//' . $parsed_url['host'];
		} elseif ( empty( $parsed_url['scheme'] ) && ! empty( $parsed_url['path'] ) ) {
			$url = '//' . $parsed_url['path'];
		}
		return $url;
	}

	public function get_file_type( $url ) {
		$basename = pathinfo( $url )['basename'];
		return strpbrk( $basename, '?' ) !== '' ? strrchr( explode( '?', $basename )[0], '.' ) : strrchr( $basename, '.' );
	}

	public function set_crossorigin( $hint, $file_type ) {
		if ( ! empty( $hint['crossorigin'] ) || ( preg_match( '/fonts.(googleapis|gstatic).com/i', $hint['url'] ) || preg_match( '/(.woff|.woff2|.ttf|.eot)/', $file_type ) ) ) {
			return 'crossorigin';
		}

		return '';
	}

	public function set_as_attr( $hint, $file_type ) {
		$as_attr = ( ! empty( $hint['as_attr'] ) ? Utils::clean_hint_attr( $hint['as_attr'] ) : '' );
		$media_types = array(
			'.mp3'   => 'audio',
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
			'.js'    => 'script',
			'.css'   => 'style',
			'.vtt'   => 'track',
			'.mp4'   => 'video',
			'.webm'  => 'video'
		);

//		var_dump($this->get_file_type_mime( $media_types, $file_type ));

		return ( '' === $as_attr )
			? $this->get_file_type_mime( $media_types, $file_type )
			: $as_attr;
	}

	public function set_type_attr( $hint, $file_type ) {
		$type_attr = ( ! empty( $hint['type_attr'] ) ) ? $hint['type_attr'] : '';
		$mimes = array(
			'.woff'  => 'font/woff',
			'.woff2' => 'font/woff2',
			'.ttf'   => 'font/ttf',
			'.eot'   => 'font/eot',
		);

		return ( ! empty( $type_attr ) ) ? Utils::clean_hint_attr( $type_attr ) : $this->get_file_type_mime( $mimes, $file_type );
	}

	public function get_file_type_mime( $possible_types, $file_type ) {
		foreach ( $possible_types as $key => $val ) {
			if ( $key === $file_type ) {
				return $val;
			}
		}
		return '';
	}


	public function get_duplicate_hints( $hint ) {
		$query = array(
			'sql'  => " WHERE url = %s AND hint_type = %s",
			'args' => array( $hint['url'], $hint['hint_type'] )
		);

//		if ( ! empty( $hint['post_id'] ) ) {
//			$query = apply_filters( 'pprh_duplicate_hint_query', $query, $hint );
//		}

		$dao = new DAO();
		return $dao->get_hints( $query );
	}



}
