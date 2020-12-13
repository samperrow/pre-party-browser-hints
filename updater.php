<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

new Updater();

class Updater {

	private $api_endpoint;
	private $plugin_file;
	private $plugin_version;

	public function __construct() {
		$this->api_endpoint = 'https://sphacks.io/wp-content/updates/pprh/update-info.json';
		$this->plugin_file = WP_PLUGIN_DIR . '/pre-party-browser-hints/pre-party-browser-hints.php';
		$this->plugin_version = '1.7.3.2';

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
	}

	private function call_api() {
		$url = $this->api_endpoint;

		// Send the request
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$response_body = wp_remote_retrieve_body( $response );
		$result = json_decode($response_body, false);

		if ($result !== null) {
			return $result;
		}
	}

	public function check_for_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$update = $this->call_api();

		if ($update !== null) {
			$plugin_slug = plugin_basename( $this->plugin_file );

			$transient->response[$plugin_slug] = (object) array(
				'new_version'   => $update->new_version,
				'slug'          => $update->slug,
				'package'       => $update->download_url,
			);

			$new_version = $update->new_version;
			$current_version = $this->plugin_version;

			if (version_compare($new_version, $current_version) > 0) {
				return $transient;
			}
		}
	}

}

