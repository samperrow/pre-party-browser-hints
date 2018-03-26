<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GKTPP_Insert_To_DB {

	private $as_attr = '';
	private $type_attr = '';
	private $crossorigin = '';
	private $url = '';

	public function insert_data_to_db() {
		if ( ! is_admin() ) {
			exit;
		}
		global $wpdb;
		$table = $wpdb->prefix . 'gktpp_table';
		$hint_type = isset( $_POST['hint_type'] ) ? stripslashes( $_POST['hint_type'] ) : '';
		isset( $_POST['url'] ) ? $this->configure_hint_attrs( self::santize_url( $_POST['url'] ), $hint_type ) : '';
		

		$sql = "INSERT INTO $table ( id, url, hint_type, status, as_attr, type_attr, crossorigin, ajax_domain ) 
				VALUES ( null, %s, %s, 'Enabled', %s, %s, %s, 0 )";

		$wpdb->query( $wpdb->prepare( $sql, array( $this->url, $hint_type, $this->as_attr, $this->type_attr, $this->crossorigin ) ) );
	}

	private static function santize_url( $url ) {
		return esc_url( preg_replace('/[^A-z0-9\.\/\-:\s]/', '', $url) );
	}

	private function get_preload_attrs( $url ) {
		$file_type = strrchr( $url, '.' );

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
				$this->as_attr = 'fetch';
		}
	}

	private function check_for_crossorigin( $url ) {
		if ( preg_match( '/(fonts.googleapis.com|fonts.gstatic.com)/i', $url ) ) {
			$this->crossorigin = 'crossorigin';
		}
	}

	private function configure_hint_attrs( $url, $hint_type ) {

		$this->check_for_crossorigin( $url );

		if ( $hint_type === 'DNS-Prefetch' || $hint_type === 'Preconnect' ) {
			return $this->filter_for_domain_name( $url );
		} elseif ( $hint_type === 'Preload' ) {
			$this->get_preload_attrs( $url );
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
