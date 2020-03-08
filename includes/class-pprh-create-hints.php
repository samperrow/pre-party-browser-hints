<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Create_Hints {

	public $results = array(
		'action' => '',
		'result' => '',
		'msg'    => '',
	);

	public $new_hint = array(
		'url'         => '',
		'hint_type'   => '',
		'file_type'   => '',
		'crossorigin' => '',
		'as_attr'     => '',
		'type_attr'   => '',
	);

	private $prev_hints = array();

	private $data = array();

	public function __construct( $data ) {
		if ( isset( $_POST['pprh_data'] ) ) {

			if ( ! defined( 'CREATING_HINT' ) || ! CREATING_HINT ) {
				exit();
			}

			$this->init( $data );
			$this->results['action'] = 'create';
		}
	}

	private function init( $data ) {
		global $wpdb;
		$urls = $data->url;

		foreach ( $urls as $url ) {
			$this->data = $data;
			$this->create_hint( $url );
			if ( $this->check_for_duplicate_post_hint() ) {
				$this->insert_hint();
			}
		}

		return $this->results;
	}

	private function create_hint( $url ) {
        $this->new_hint['hint_type'] = $this->set_hint_type();
        $this->new_hint['file_type'] = $this->set_file_type();
        $this->new_hint['crossorigin'] = $this->set_crossorigin();
        $this->new_hint['as_attr'] = $this->set_as_attr();
        $this->new_hint['type_attr'] = $this->set_type_attr();
        $this->new_hint['post_url'] = $this->set_post_url();
    }

	private function set_hint_type() {
		return Utils::clean_hint_type( $this->data->hint_type );
	}

	private function set_url( $url ) {
		if ( preg_match( '/(dns-prefetch|preconnect)/', $this->new_hint['hint_type'] ) ) {
            $url = $this->parse_for_domain_name( $url );
		}

        return filter_var( str_replace( ' ', '', $url ), FILTER_SANITIZE_URL );
    }

	private function parse_for_domain_name( $url ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! empty( $parsed_url['scheme'] ) ) {
            $this->results['msg'] .= ' Only the domain name of the entered URL is needed for that hint type.';
            $url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
		} elseif ( strpos( $this->new_hint['url'], '//' ) === 0 ) {
            $url = '//' . $parsed_url['host'];
		} else {
            $url = '//' . $parsed_url['path'];
		}
		return $url;
	}

	private function set_file_type() {
		$basename = pathinfo( $this->new_hint['url'] )['basename'];
		return strpbrk( $basename, '?' ) !== '' ? strrchr( explode( '?', $basename )[0], '.' ) : strrchr( $basename, '.' );
	}

	private function set_crossorigin() {
		return ( ! empty( $this->data->crossorigin ) || ( preg_match( '/fonts.(googleapis|gstatic).com/i', $this->new_hint['url'] ) || preg_match( '/(.woff|.woff2|.ttf|.eot)/', $this->new_hint['file_type'] ) ) ) ? 'crossorigin' : '';
	}

	private function set_as_attr() {
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

		return ( ! empty( $this->data->as_attr ) ) ? $this->data->as_attr : $this->set_file_type_mime( $media_types );
	}

	private function set_type_attr() {
		$mimes = array(
			'.woff'  => 'font/woff',
			'.woff2' => 'font/woff2',
			'.ttf'   => 'font/ttf',
			'.eot'   => 'font/eot',
		);

        return ( ! empty( $this->data->type_attr ) ) ? $this->data->type_attr : $this->set_file_type_mime( $mimes );
	}

    private function set_file_type_mime( $file_types ) {
		foreach ( $file_types as $key => $val ) {
			if ( $key === $this->new_hint['file_type'] ) {
				return $val;
			}
		}
		return '';
	}

	private function insert_hint() {
		global $wpdb;
		$current_user = wp_get_current_user()->display_name;

		$wpdb->insert(
			PPRH_DB_TABLE,
			array(
				'url'         => $this->new_hint['url'],
				'hint_type'   => $this->new_hint['hint_type'],
				'status'      => 'enabled',
				'as_attr'     => $this->new_hint['as_attr'],
				'type_attr'   => $this->new_hint['type_attr'],
				'crossorigin' => $this->new_hint['crossorigin'],
				'created_by'  => $current_user,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		$this->results['result'] = ( $wpdb->result ) ? 'success' : 'error';
	}

}
