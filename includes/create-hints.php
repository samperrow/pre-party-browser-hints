<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Create_Hints {

	public $response = array();

	public $prev_hints = array();

	public function __construct() {
		if ( ! defined( 'CREATING_HINT' ) || ! CREATING_HINT ) {
			exit();
		}

		$this->response = array(
			'msg'    => '',
			'status' => '',
			'query'  => array(),
		);

		$this->prev_hints = (object) array();
	}

	public function verify_data( $hint ) {
		if ( empty( $hint->url ) || empty( $hint->hint_type ) ) {
			return $this->response['msg'] = 'Please use a valid URL and hint type';
		}

		$hint_type = $this->get_hint_type( $hint->hint_type );
		$url       = $this->get_url( $hint, $hint_type );

		if ( $this->duplicate_hint_exists( $url, $hint_type ) ) {
			return false;
		}

		return true;
	}



//	public function init( $hint ) {
//		$new_hint = (object) $this->create_hint( $hint );
//
//		if ( $this->duplicate_hint_exists( $new_hint ) ) {
//			return false;
//		}
//
//		return $new_hint;
//	}

	public function create_hint( $hint ) {
		$hint_type = $this->get_hint_type( $hint->hint_type );
		$url       = $this->get_url( $hint, $hint_type );
		$file_type = $this->get_file_type( $url );

		return (object) array(
			'hint_type'    => $hint_type,
			'url'          => $url,
			'file_type'    => $file_type,
			'as_attr'      => $this->set_as_attr( $hint, $file_type ),
			'type_attr'    => $this->set_type_attr( $hint, $file_type ),
			'crossorigin'  => $this->set_crossorigin( $hint, $file_type ),
			'auto_created' => ( isset( $hint->auto_created ) ? 1 : 0 ),
		);
	}



	public function get_hint_type( $type ) {
		return Utils::clean_hint_type( $type );
	}

	public function get_url( $hint, $type ) {
		if ( preg_match( '/(dns-prefetch|preconnect)/', $type ) ) {
			$url = $this->parse_for_domain_name( $hint->url );
		} else {
			$url = $hint->url;
		}

		$url = Utils::clean_url( $url );
		return $url;
	}

	public function parse_for_domain_name( $url ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! empty( $parsed_url['host'] ) && ! empty( $parsed_url['path'] ) ) {
			$this->response['msg'] .= ' Only the domain name of the entered URL is needed for that hint type.';
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
		return ( ! empty( $hint->crossorigin ) || ( preg_match( '/fonts.(googleapis|gstatic).com/i', $hint->url ) || preg_match( '/(.woff|.woff2|.ttf|.eot)/', $file_type ) ) ) ? 'crossorigin' : '';
	}

	public function set_as_attr( $hint, $file_type ) {
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

		return ( ! empty( $as_attr ) ) ? Utils::clean_hint_attr( $as_attr ) : $this->get_file_type_mime( $media_types, $file_type );
	}

	public function set_type_attr( $hint, $file_type ) {
		$type_attr = ( ! empty( $hint->type_attr ) ) ? $hint->type_attr : '';
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

	public function duplicate_hint_exists( $url, $hint_type ) {
		$table = PPRH_DB_TABLE;
		$sql = "SELECT url, hint_type FROM $table WHERE hint_type = %s AND url = %s";
		$arr = array( $url, $hint_type );
		$dao = new DAO();
		$prev_hints = $dao->get_hints_query( $sql, $arr );

		if ( count( $prev_hints ) > 0 ) {
			$this->response['msg'] .= 'An identical resource hint already exists!';
			$this->response['status'] = 'warning';
			return true;
		}

		return false;
	}

}
