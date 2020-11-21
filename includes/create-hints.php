<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Create_Hints {

	public $results = array(
		'query'     => array(),
		'new_hints' => array(),
	);

	public $prev_hints = array();

	public function __construct( $data ) {
		if ( ! defined( 'CREATING_HINT' ) || ! CREATING_HINT ) {
			exit();
		}

		$this->prev_hints = (object) array();
		$this->init( $data );
	}

	private function init( $hints ) {

		foreach ( $hints as $hint ) {
			$new_hint = (object) $this->create_hint( $hint );

			if ( $this->check_for_duplicate_post_hint( $new_hint ) ) {
				$dao = new DAO();
				$this->results['query'] = $dao->insert_hint( $new_hint );
				$this->results['new_hints'][] = $new_hint;
			}
		}

		return $this->results;
	}

	private function create_hint( $hint ) {
		if ( empty( $hint->url ) || empty( $hint->hint_type ) ) {
			return 'err';
		}

		$hint_type = $this->set_hint_type( $hint->hint_type );
		$url       = $this->set_url( $hint, $hint_type );
		$file_type = $this->set_file_type( $url );

		return array(
			'hint_type'    => $hint_type,
			'url'          => $url,
			'file_type'    => $file_type,
			'as_attr'      => $this->set_as_attr( $hint, $file_type ),
			'type_attr'    => $this->set_type_attr( $hint, $file_type ),
			'crossorigin'  => $this->set_crossorigin( $hint, $file_type ),
			'auto_created' => ( isset( $hint->auto_created ) ? 1 : 0 ),
		);
	}

	private function set_hint_type( $type ) {
		return Utils::clean_hint_type( $type );
	}

	private function set_url( $hint, $type ) {
		if ( preg_match( '/(dns-prefetch|preconnect)/', $type ) ) {
			$url = $this->parse_for_domain_name( $hint->url );
		} else {
			$url = $hint->url;
		}

		$url = Utils::clean_url( $url );
		return $url;
	}

	private function parse_for_domain_name( $url ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! empty( $parsed_url['host'] ) && ! empty( $parsed_url['path'] ) ) {
			$this->results['query']['msg'] .= ' Only the domain name of the entered URL is needed for that hint type.';
			$url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
		} elseif ( strpos( $url, '//' ) === 0 ) {
			$url = '//' . $parsed_url['host'];
		} elseif ( empty( $parsed_url['scheme'] ) && ! empty( $parsed_url['path'] ) ) {
			$url = '//' . $parsed_url['path'];
		}
		return $url;
	}

	private function set_file_type( $url ) {
		$basename = pathinfo( $url )['basename'];
		return strpbrk( $basename, '?' ) !== '' ? strrchr( explode( '?', $basename )[0], '.' ) : strrchr( $basename, '.' );
	}

	private function set_crossorigin( $hint, $file_type ) {
		return ( ! empty( $hint->crossorigin ) || ( preg_match( '/fonts.(googleapis|gstatic).com/i', $hint->url ) || preg_match( '/(.woff|.woff2|.ttf|.eot)/', $file_type ) ) ) ? 'crossorigin' : '';
	}

	private function set_as_attr( $hint, $file_type ) {
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

	private function set_type_attr( $hint, $file_type ) {
		$type_attr = ( ! empty( $hint->type_attr ) ) ? $hint->type_attr : '';
		$mimes = array(
			'.woff'  => 'font/woff',
			'.woff2' => 'font/woff2',
			'.ttf'   => 'font/ttf',
			'.eot'   => 'font/eot',
		);

		return ( ! empty( $type_attr ) ) ? Utils::clean_hint_attr( $type_attr ) : $this->set_file_type_mime( $mimes, $file_type );
	}

	private function set_file_type_mime( $possible_types, $file_type ) {
		foreach ( $possible_types as $key => $val ) {
			if ( $key === $file_type ) {
				return $val;
			}
		}
		return '';
	}

	private function check_for_duplicate_post_hint( $new_hint ) {
		global $wpdb;
		$table     = PPRH_DB_TABLE;
		$hint_type = $new_hint->hint_type;
		$url       = $new_hint->url;

		$prev_hints = $wpdb->get_results(
			$wpdb->prepare( "SELECT url, hint_type FROM $table WHERE hint_type = %s AND url = %s", $hint_type, $url )
		);

		if ( count( $prev_hints ) > 0 ) {
			$this->results['response']['msg'] .= 'An identical resource hint already exists!';
			$this->results['query']['status'] = 'warning';
			return false;
		}

		return true;
	}

}
