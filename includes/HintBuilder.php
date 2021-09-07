<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HintBuilder {

	private $file_mime_types;

	public function __construct() {
		$this->file_mime_types = $this->set_file_mime_types();
	}

	public function create_pprh_hint( array $raw_hint ):array {
		if ( ! isset( $raw_hint['url'], $raw_hint['hint_type'] ) ) {
			return array();
		}

		$hint_type = $this->get_hint_type( $raw_hint['hint_type'] );
		$url       = $this->get_url( $raw_hint['url'], $hint_type );

		if ( empty( $hint_type ) || empty( $url ) ) {
			return array();
		}

		$file_type    = $this->get_file_type( $url );
		$as_attr      = $raw_hint['as_attr'] ?? '';
		$media_attr   = $raw_hint['media'] ?? '';
		$auto_created = $raw_hint['auto_created'] ?? 0;

		$as_attr      = $this->set_as_attr( $as_attr, $file_type );
		$type_attr    = $this->set_mime_type_attr( $raw_hint, $file_type );
		$crossorigin  = $this->set_crossorigin( $raw_hint, $file_type );
		$media        = Utils::strip_bad_chars( $media_attr );

		$new_hint = Utils::create_raw_hint( $url, $hint_type, $auto_created, $as_attr, $type_attr, $crossorigin, $media );
		$new_hint['current_user'] = \wp_get_current_user()->display_name ?? '';

		return apply_filters( 'pprh_append_hint', $new_hint, $raw_hint );
	}

	public function get_hint_type( string $hint_type ) {
		$hint_type = Utils::clean_hint_type( $hint_type );
		$valid_hints = array( 'dns-prefetch', 'prefetch', 'prerender', 'preconnect', 'preload' );

		if ( ! in_array( $hint_type, $valid_hints ) ) {
			return '';
		}

		return $hint_type;
	}

	public function get_url( string $url, string $hint_type ):string {
		$url = Utils::clean_url( $url );

		if ( preg_match( '/(dns-prefetch|preconnect)/', $hint_type ) ) {
			$url = $this->parse_for_domain_name( $url );
		}

		if ( '//' === $url || str_starts_with( $url, '//data:' ) ) {
			return '';
		}

		return $url;
	}

	public function parse_for_domain_name( string $url ):string {
		$parsed_url = \wp_parse_url( $url );

		if ( ! isset( $parsed_url['host'], $parsed_url['scheme'] ) ) {
			return ( str_starts_with( $url, '//' ) ) ? $url : '//' . $url;
		}

		$domain = ( str_starts_with( $url, '//' ) ) ? '//' : $parsed_url['scheme'] . '://';
		$domain .= $parsed_url['host'];
		return $domain;
	}

	public function get_file_type( string $url ):string {
		$basename = pathinfo( $url )['basename'];

		if ( str_contains( $basename, '?' ) ) {
			$basename = explode( '?', $basename )[0];
		}

		return strrchr( $basename, '.' );
	}

	public function set_crossorigin( array $hint, string $file_type ) {
		$match = ( 0 < preg_match( '/(.woff|.woff2|.ttf|.eot)/', $file_type ) );
		$match_2 = ( 0 < preg_match( '/fonts.(googleapis|gstatic).com/i', $hint['url'] ) );

		if ( ( isset( $hint['crossorigin'] ) && ! empty( $hint['crossorigin'] ) ) || $match_2 || $match ) {
			return 'crossorigin';
		}

		return '';
	}

	public function set_as_attr( string $as_attr, string $file_type ) {
		return ( ! empty( $as_attr ) ) ? Utils::clean_hint_attr( $as_attr ) : $this->get_file_type_mime( $this->file_mime_types, $file_type, 'as' );
	}

	public function set_mime_type_attr( array $hint, string $file_type ) {
		if ( isset( $hint['type_attr'] ) && ! empty( $hint['type_attr'] ) ) {
			$mime_type = Utils::clean_hint_attr( $hint['type_attr'] );
		} else {
			$mime_type = $this->get_file_type_mime( $this->file_mime_types, $file_type, 'mimeType' );
		}

		return $mime_type;
	}

	private function get_file_type_mime( $file_mime_types, $file_type, string $prop ) {
		foreach ( $file_mime_types as $file_mime_type ) {
			if ( $file_mime_type['fileType'] === $file_type ) {
				return $file_mime_type[$prop];
			}
		}
		return '';
	}

	public function set_file_mime_types():array {
		$types = array(
			array( 'fileType' => '.epub',   'as' => '',         'mimeType' => 'application/epub+zip' ),
			array( 'fileType' => '.json',   'as' => '',         'mimeType' => 'application/json' ),
			array( 'fileType' => '.jsonld', 'as' => '',         'mimeType' => 'application/ld+json' ),
			array( 'fileType' => '.bin',    'as' => '',         'mimeType' => 'application/octet-stream' ),
			array( 'fileType' => '.ogx',    'as' => '',         'mimeType' => 'application/ogg' ),
			array( 'fileType' => '.pdf',    'as' => '',         'mimeType' => 'application/pdf' ),
			array( 'fileType' => '.swf',    'as' => 'embed',    'mimeType' => 'application/x-shockwave-flash' ),
			array( 'fileType' => '.aac',    'as' => 'audio',    'mimeType' => 'audio/aac' ),
			array( 'fileType' => '.mp3',    'as' => 'audio',    'mimeType' => 'audio/mpeg' ),
			array( 'fileType' => '.mpeg',   'as' => 'audio',    'mimeType' => 'audio/mpeg' ),
			array( 'fileType' => '.oga',    'as' => '',         'mimeType' => 'audio/ogg' ),
			array( 'fileType' => '.opus',   'as' => '',         'mimeType' => 'audio/opus' ),
			array( 'fileType' => '.weba',   'as' => 'audio',    'mimeType' => 'audio/webm' ),
			array( 'fileType' => '.eot',    'as' => 'font',     'mimeType' => 'font/eot' ),
			array( 'fileType' => '.otf',    'as' => 'font',     'mimeType' => 'font/otf' ),
			array( 'fileType' => '.ttf',    'as' => 'font',     'mimeType' => 'font/ttf' ),
			array( 'fileType' => '.woff',   'as' => 'font',     'mimeType' => 'font/woff' ),
			array( 'fileType' => '.woff2',  'as' => 'font',     'mimeType' => 'font/woff2' ),
			array( 'fileType' => '.css',    'as' => 'style',    'mimeType' => 'text/css' ),
			array( 'fileType' => '.htm',    'as' => 'document', 'mimeType' => 'text/html' ),
			array( 'fileType' => '.html',   'as' => 'document', 'mimeType' => 'text/html' ),
			array( 'fileType' => '.js',     'as' => 'script',   'mimeType' => 'text/javascript' ),
			array( 'fileType' => '.txt',    'as' => '',         'mimeType' => 'text/plain' ),
			array( 'fileType' => '.vtt',    'as' => 'track',    'mimeType' => 'text/vtt' ),
			array( 'fileType' => '.mp4',    'as' => 'video',    'mimeType' => 'video/mp4' ),
			array( 'fileType' => '.ogv',    'as' => 'video',    'mimeType' => 'video/ogg' ),
			array( 'fileType' => '.webm',   'as' => 'video',    'mimeType' => 'video/webm' ),
			array( 'fileType' => '.avi',    'as' => 'video',    'mimeType' => 'video/x-msvideo' ),
			array( 'fileType' => '.bmp',    'as' => 'image',    'mimeType' => 'image/bmp' ),
			array( 'fileType' => '.jpg',    'as' => 'image',    'mimeType' => 'image/jpeg' ),
			array( 'fileType' => '.jpeg',   'as' => 'image',    'mimeType' => 'image/jpeg' ),
			array( 'fileType' => '.png',    'as' => 'image',    'mimeType' => 'image/png' ),
			array( 'fileType' => '.svg',    'as' => 'image',    'mimeType' => 'image/svg+xml' ),
			array( 'fileType' => '.ico',    'as' => 'image',    'mimeType' => 'image/vnd.microsoft.icon' ),
			array( 'fileType' => '.webp',   'as' => 'image',    'mimeType' => 'image/webp' )
    	);

		return $types;
	}

}
