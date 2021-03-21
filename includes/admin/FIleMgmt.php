<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Files {

	private $wp_filesystem;

	public function __construct() {
		$this->wp_filesystem = $this->new_wp_filesystem();
	}

	public function new_wp_filesystem() {
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
		return new \WP_Filesystem_Direct( new \StdClass() );
	}

	public function pprh_has_wp_htaccess_rules( $content ) {
		if ( is_multisite() ) {
			$has_wp_rules = strpos( $content, '# add a trailing slash to /wp-admin' ) !== false;
		} else {
			$has_wp_rules = strpos( $content, '# BEGIN WordPress' ) !== false;
		}

		return apply_filters( 'pprh_has_wp_htaccess_rules', $has_wp_rules, $content );
	}

	public function get_htaccess_marker( $header_info ) {
		$marker = PHP_EOL;
		$marker .= '# BEGIN PPRH v' . PPRH_VERSION . PHP_EOL;
		$marker .= "Header add Link \"$header_info\"" . PHP_EOL;
		$marker .= '# END PPRH' . PHP_EOL;
		return $marker;
	}


	public function write_to_htaccess( $header_info ) {
		global $is_apache;

		if ( ! $is_apache ) {
			return false;
		}

		$htaccess_file = get_home_path() . '.htaccess';

		if ( ! $this->wp_filesystem->is_writable( $htaccess_file ) ) {
			return false;
		}

		// Get content of .htaccess file
		$content = $this->wp_filesystem->get_contents( $htaccess_file );

		if ( false === $content ) {
			// Could not get the file contents.
			return false;
		}

		$has_wp_rules = $this->pprh_has_wp_htaccess_rules( $content );

		// Remove the WP Rocket marker.
		$content = preg_replace( '/\s*# BEGIN PPRH.*# END PPRH\s*?/isU', PHP_EOL . PHP_EOL, $content );
		$content = ltrim( $content );
		$content = $this->get_htaccess_marker( $header_info ) . PHP_EOL . $content;

		if ( $has_wp_rules && ! $this->pprh_has_wp_htaccess_rules( $content ) ) {
			return false;
		}

		// Update the .htacces file.
		return $this->put_content( $htaccess_file, $content );
	}

	public function put_content( $file, $content ) {
		return $this->wp_filesystem->put_contents( $file, $content, 420 );
	}


	public function update_htaccess() {
//		add_action( 'pprh_update_htaccess', array( $this, 'update_htaccess' ) );

		$send_hints_in_html = get_option( 'pprh_html_head' );

		if ( 'true' === $send_hints_in_html ) {
			return;
		}

		include_once PPRH_ABS_DIR . 'includes/SendHints.php';
		$send_hints = new SendHints();
		$query = $send_hints->get_query();
		$send_hints->hints = $send_hints->get_resource_hints( $query );
		$header_info = $send_hints->send_in_http_header();

		include_once PPRH_ABS_DIR . 'includes/functions/Files.php';
		$files = new Files();
		$files->write_to_htaccess( $header_info );
	}


}