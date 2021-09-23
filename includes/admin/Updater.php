<?php

namespace PPRH;

use PPRH\Utils\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Updater {

	private $api_endpoint;
	private $plugin_file;
	private $transient_name;
	private $current_plugin_version;
	private $pprh_transient;

	public function __construct() {
		$this->api_endpoint   = 'https://sphacks.io/wp-content/pprh/updater-main.json';
		$this->plugin_file    = 'pre-party-browser-hints/pre-party-browser-hints.php';
		$this->transient_name = 'pprh_updater';
		$this->current_plugin_version = PPRH_VERSION_NEW;
		$this->pprh_transient = \get_site_transient( $this->transient_name );

		if ( isset( $_GET['force-check'] ) && '1' === $_GET['force-check'] ) {
			\delete_site_transient( $this->transient_name );
		}
	}

	public function init( $transient ) {
		$new_transient = $this->get_new_transient( $transient, $this->pprh_transient, $this->current_plugin_version );

		if ( is_object( $new_transient ) ) {
			return $new_transient;
		}
	}

	public function get_new_transient( $transient, $plugin_update, string $current_plugin_version ) {

		// transient expired, so make a call to retrieve the external JSON content, then update transient.
		if ( false === $plugin_update ) {
			$plugin_update = $this->fetch_plugin_update();
		}

		if ( is_object( $transient ) && isset( $transient->response ) && Utils::isArrayAndNotEmpty( $plugin_update ) ) {
			\set_transient( $this->transient_name, $plugin_update, HOUR_IN_SECONDS * 6 );
			return $this->update_transient( $transient, $plugin_update, $current_plugin_version );
		}

		return false;
	}


	public function fetch_plugin_update() {
		$args = array(
			'timeout'   => 20,
			'sslverify' => ( str_contains( $this->api_endpoint, 'https://sphacks.io' ) )
		);

		$error_msg = 'Error updating plugin.';
		$response  = array();

		try {
			$response = \wp_safe_remote_get( $this->api_endpoint, $args );
		} catch ( \Exception $exception ) {
			Utils::log_error( $exception );
		}

		if ( Utils::isArrayAndNotEmpty( $response ) ) {
			return Utils::get_api_response_body( $response, $error_msg );
		}

		return false;
	}

	public function update_transient( \stdClass $transient, array $plugin_update, string $current_plugin_version ) {
		$plugin_update_obj = (object) $plugin_update;

		$plugin_slug                                   = plugin_basename( $this->plugin_file );
		$transient->response[ $plugin_slug ]           = $plugin_update_obj;
		$transient->response[ $plugin_slug ]->sections = $plugin_update_obj->sections;
		$transient->response[ $plugin_slug ]->icons    = $plugin_update_obj->icons;
		$transient->response[ $plugin_slug ]->banners  = $plugin_update_obj->banners;
		$received_version = $transient->response[ $plugin_slug ]->new_version;

		if ( $received_version !== $current_plugin_version ) {
			$transient->response[ $plugin_slug ] = $plugin_update_obj;
			$transient->checked[ $plugin_slug ] = $received_version;
			return $transient;
		}

		return false;
	}
}
