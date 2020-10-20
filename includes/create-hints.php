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

        do_action( 'pprh_load_create_hints_child' );

        $this->prev_hints = (object) array();
		$this->init( $data );
	}

	public function init( $hints ) {
		foreach ( $hints as $hint ) {
			$new_hint = (object) $this->create_hint( $hint );

            $dups = $this->get_duplicate_hints( $new_hint );

//            if ( isset( $new_hint->load_adv ) ) {
//                $asdf = apply_filters( 'pprh_excessive_dup_hints_exist', $dups );
//            }

			if ( count( $dups ) > 0 ) {
                $this->results['response']['msg'] .= 'An identical resource hint already exists!';
                $this->results['query']['status'] = 'warning';
			} else {
                $this->results['query'] = $this->insert_hint( $new_hint );
                $this->results['new_hints'][] = $new_hint;
            }
		}

		return $this->results;
	}

	private function create_hint( $raw_hint ) {
		if ( empty( $raw_hint->url ) || empty( $raw_hint->hint_type ) ) {
			return 'err';
		}

		$hint_type = $this->set_hint_type( $raw_hint->hint_type );
		$url       = $this->set_url( $raw_hint, $hint_type );
		$file_type = $this->set_file_type( $url );

		$new_hint = array(
			'hint_type'    => $hint_type,
			'url'          => $url,
			'file_type'    => $file_type,
			'as_attr'      => $this->set_as_attr( $raw_hint, $file_type ),
			'type_attr'    => $this->set_type_attr( $raw_hint, $file_type ),
			'crossorigin'  => $this->set_crossorigin( $raw_hint, $file_type ),
			'auto_created' => ( isset( $raw_hint->auto_created ) ? 1 : 0 ),
		);

        $final_hint = apply_filters( 'pprh_create_hints_add_to_hint', $new_hint, $raw_hint );

		return $final_hint;
	}

	protected function set_hint_type( $type ) {
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

    private function get_duplicate_hints( $new_hint ) {
		global $wpdb;
		$table     = PPRH_DB_TABLE;
		$hint_type = $new_hint->hint_type;
		$url       = $new_hint->url;

		$sql = array(
		    'query' => "SELECT url, hint_type FROM $table WHERE hint_type = %s AND url = %s",
            'args'  => array( $hint_type, $url ),
        );

		$sql = apply_filters( 'pprh_append_duplicate_hints', $new_hint, $sql );

		$prev_hints = $wpdb->get_results(
			$wpdb->prepare( $sql['query'], $sql['args'] )
		);

		if ( count( $prev_hints ) > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	private function insert_hint( $new_hint ) {
		global $wpdb;
		$current_user = wp_get_current_user()->display_name;
		$action = 'create';

		$query = array(
		    'types' => array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
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
        );

        $query = apply_filters( 'pprh_insert_hint_filter', $query, $new_hint );

        $wpdb->insert(
			PPRH_DB_TABLE,
            $query['args'],
            $query['types']
		);

		return Utils::get_wpdb_result( $wpdb, $action );
	}

}
