<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Updater {

	private $api_endpoint;
	private $plugin_file;
	private $transient_name;
	private $plugin_version;

	public $pprh_transient;

	public function __construct() {
		$this->api_endpoint   = 'https://sphacks.io/wp-content/pprh/free/updater.json';
		$this->plugin_file    = 'pre-party-browser-hints/pre-party-browser-hints.php';
		$this->transient_name = 'pprh_updater';
		$this->plugin_version = PPRH_VERSION_NEW;
		$this->pprh_transient = \get_site_transient( $this->transient_name );

		if ( isset( $_GET['force-check'] ) && '1' === $_GET['force-check'] ) {
			\delete_site_transient( $this->transient_name );
		}
	}

	public function init( $transient ) {
		$plugin_update = $this->get_plugin_update( $transient );

		if ( \PPRH\Utils::isArrayAndNotEmpty( $plugin_update ) ) {
			$new_transient = $this->update_transient( $transient, $plugin_update );

			if ( is_object( $new_transient ) ) {
				return $new_transient;
			}
		}
	}


	public function get_plugin_update( $transient ) {

		// transient expired, so make a call to retrieve the external JSON content, then update transient.
		if ( false === $this->pprh_transient ) {
			$plugin_update = $this->fetch_plugin_update();

			if ( is_object( $transient ) && isset( $transient->response ) && \PPRH\Utils::isArrayAndNotEmpty( $plugin_update ) ) {
				\set_site_transient( $this->transient_name, $plugin_update, HOUR_IN_SECONDS * 6 );
				return $plugin_update;
			}
		} else {
			return $this->pprh_transient;
		}
	}


	public function fetch_plugin_update() {
		$args = array(
			'timeout'   => 20,
			'sslverify' => ( str_contains( $this->api_endpoint, 'https://sphacks.io' ) )
		);

		$error_msg = 'Error updating plugin.';
		$response  = array();

		try {
			$response = \wp_remote_get( $this->api_endpoint, $args );
		} catch ( \Exception $exception ) {
			\PPRH\Utils::log_error( $exception );
		}

		if ( \PPRH\Utils::isArrayAndNotEmpty( $response ) ) {
			return Utils::get_api_response_body( $response, $error_msg );
		}

		return false;
	}

	public function update_transient( object $transient, array $update ) {
		$update_obj = (object) $update;

		$plugin_slug                                   = plugin_basename( $this->plugin_file );
		$transient->response[ $plugin_slug ]           = $update_obj;
		$transient->response[ $plugin_slug ]->sections = $update_obj->sections;
		$transient->response[ $plugin_slug ]->icons    = $update_obj->icons;
		$transient->response[ $plugin_slug ]->banners  = $update_obj->banners;
		$new_version = $transient->response[ $plugin_slug ]->new_version;

		if ( version_compare( $new_version, $this->plugin_version ) > 0 ) {
			$transient->response[ $plugin_slug ] = $update_obj;
			$transient->checked[ $plugin_slug ]  = $new_version;
			return $transient;
		}
	}
}
