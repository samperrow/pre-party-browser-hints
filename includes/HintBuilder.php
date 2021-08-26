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

	public function create_pprh_hint( array $hint ) {
		if ( empty( $hint['url'] ) || empty( $hint['hint_type'] ) ) {
			return false;
		}

		$hint_type    = Utils::clean_hint_type( $hint['hint_type'] );
		$url          = $this->get_url( $hint['url'], $hint_type );
		$file_type    = $this->get_file_type( $url );
		$auto_created = ( ! empty( $hint['auto_created'] ) ? 1 : 0 );
		$as_attr      = $this->set_as_attr( $hint['as_attr'], $file_type );
		$type_attr    = $this->set_type_attr( $hint, $file_type );
		$crossorigin  = $this->set_crossorigin( $hint, $file_type );

		//		$new_hint = apply_filters( 'pprh_append_hints', $new_hint, $hint );

		return Utils::create_raw_hint( $url, $hint_type, $auto_created, $as_attr, $type_attr, $crossorigin );
	}


	public function get_url( string $url, string $hint_type ) {
		$url = Utils::clean_url( $url );

		if ( preg_match( '/(dns-prefetch|preconnect)/', $hint_type ) ) {
			$url = $this->parse_for_domain_name( $url );
		}

		return $url;
	}

	public function parse_for_domain_name( $url ) {
		$parsed_url = wp_parse_url( $url );

		if ( ! empty( $parsed_url['host'] ) && ! empty( $parsed_url['path'] ) ) {
			$url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
		} elseif ( str_starts_with( $url, '//' ) ) {
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

	public function set_as_attr( $as_attr, $file_type ) {
		return ( ! empty( $as_attr ) ) ? Utils::clean_hint_attr( $as_attr ) : $this->get_file_type_mime( $this->file_mime_types, $file_type );
	}

	public function set_type_attr( $hint, $file_type ) {
		$type_attr = ( ! empty( $hint['type_attr'] ) ) ? $hint['type_attr'] : '';

		return ( ! empty( $type_attr ) ) ? Utils::clean_hint_attr( $type_attr ) : $this->get_file_type_mime( $this->file_mime_types, $file_type );
	}

	public function get_file_type_mime( $file_mime_types, $file_type ) {
		foreach ( $file_mime_types as $key => $val ) {
			if ( $key['fileType'] === $file_type ) {
				return $val;
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
