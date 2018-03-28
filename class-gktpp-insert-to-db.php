<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Insert_To_DB {

	public $as_attr = '';
	public $type_attr = '';
	public $crossorigin = '';
	public $url = '';
	public $header_str = '';
	public $head_str = '';

	public function insert_data_to_db() {
		if ( ! is_admin() ) {
			exit;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_table';
		$hint_type = isset( $_POST['hint_type'] ) ? stripslashes( $_POST['hint_type'] ) : '';
		isset( $_POST['url'] ) ? $this->configure_hint_attrs( self::santize_url( $_POST['url'] ), $hint_type ) : '';

		$this->create_str( $this->url, $hint_type, $this->as_attr, $this->type_attr, $this->crossorigin );

		$sql = "INSERT INTO $table ( id, url, hint_type, status, as_attr, type_attr, crossorigin, ajax_domain, header_string, head_string ) 
				VALUES ( null, %s, %s, 'Enabled', %s, %s, %s, %d, %s, %s )";

		$wpdb->query( 
			$wpdb->prepare( $sql, 
			array( $this->url, $hint_type, $this->as_attr, $this->type_attr, $this->crossorigin, 0, $this->header_str, $this->head_str) ) );
	}

	private static function santize_url( $url ) {
		return esc_url( preg_replace('/[^A-z0-9?=\.\/\-:\s]/', '', $url) );
	}

	public function create_str( $url, $hint_type, $as_attr, $type_attr, $crossorigin ) {
		$hint_type = strtolower( $hint_type );
		$header_as_attr = $header_type_attr = $head_as_attr = $head_type_attr = $header_crossorigin = $head_crossorigin = '';

		if ( strlen($as_attr) > 0 ) {
			$header_as_attr = " as=$as_attr;";
			$head_as_attr = " as='$as_attr'";
		}

		if ( strlen($type_attr) > 0 ) {
			$header_type_attr = " type=$type_attr;";
			$head_type_attr = " type='$type_attr'";
		}

		if ( strlen($crossorigin) > 0 ) {
			$header_crossorigin = " $crossorigin;";
			$head_crossorigin = " $crossorigin";
		}

		$this->head_str = "<link href='$url' rel='$hint_type'$head_as_attr$head_type_attr$head_crossorigin>";

		$header = "<$url>; rel=$hint_type;$header_as_attr$head_type_attr$header_crossorigin";
		return $this->header_str = substr( $header, 0, strrpos( $header, ';') ) . ',';
	}

	public function get_attributes( $url ) {
		$basename = pathinfo( $url )['basename'];

		$file_type = strlen( strpbrk( $basename, '?' ) ) > 0
			? strrchr( explode( '?', $basename )[0], '.' ) 
			: strrchr( $basename, '.' );

		switch ( $file_type ) {
			case '.js':
				$this->as_attr = 'script';
				break;
			case '.css':
				$this->as_attr= 'style';
				break;
			case '.mp3':
				$this->as_attr = 'audio';
				break;
			case '.mp4':
				$this->as_attr = 'video';
				break;
			case '.jpg':
			case '.jpeg':
			case '.png':
			case '.svg':
				$this->as_attr = 'image';
				break;
			case '.vtt':
				$this->as_attr = 'track';
				break;
			case '.woff':
				$this->as_attr = 'font';
				$this->crossorigin = 'crossorigin';
				$this->type_attr = 'font/woff';
				break;
			case '.woff2':
				$this->as_attr = 'font';
				$this->crossorigin = 'crossorigin';
				$this->type_attr = 'font/woff2';
				break;
			case '.ttf':
				$this->as_attr = 'font';
				$this->crossorigin = 'crossorigin';
				$this->type_attr = 'font/ttf';
				break;
			case '.eot':
				$this->as_attr = 'font';
				$this->crossorigin = 'crossorigin';
				$this->type_attr = 'font/eot';
				break;
			case '.swf':
				$this->as_attr = 'embed';
				break;
			default:
				$this->as_attr = '';
		}

		$this->check_for_crossorigin( $url );
		return $this->as_attr;
	}

	public function check_for_crossorigin( $url ) {
		return $this->crossorigin = preg_match( '/(fonts.googleapis.com|fonts.gstatic.com)/i', $url ) ? 'crossorigin' : '';
	}

	private function configure_hint_attrs( $url, $hint_type ) {

		$this->get_attributes( $url );

		if ( $hint_type === 'DNS-Prefetch' || $hint_type === 'Preconnect' ) {
			return $this->filter_for_domain_name( $url );
		}

		return $this->url = $url;
	}

	private function filter_for_domain_name( $url ) {
		if ( preg_match( '/(http|https)/i', $url ) ) {
			return $this->url = parse_url( $url, PHP_URL_SCHEME ) . '://' . parse_url( $url, PHP_URL_HOST );
		} elseif ( substr( $url, 0, 2 ) === '//' ) {
			return $this->url = '//' . parse_url( $url, PHP_URL_HOST );
		} else {
			return $this->url = '//' . parse_url( $url, PHP_URL_PATH );
		}
	}
}
