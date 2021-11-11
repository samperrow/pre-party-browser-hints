<?php
declare(strict_types=1);

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PluginUpdater {

	public $current_version;
	public $update_path;
	public $plugin_file;
	public $slug;

	/**
	 * Initialize a new instance of the WordPress Auto-Update class
	 * @param string $current_version
	 * @param string $update_path
	 * @param string $plugin_file
	 * @param string $plugin_slug
	 */
	public function __construct( string $current_version, string $update_path, string $plugin_file, string $plugin_slug ) {
		$this->current_version = $current_version;
		$this->update_path     = $update_path;
		$this->plugin_file     = $plugin_file;
		$this->slug            = $plugin_slug;

		// define the alternative API for updating checking
		\add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
	}

	/**
	 * Add our self-hosted autoupdate plugin to the filter transient
	 *
	 * @param $transient
	 * @return object $transient
	 */
	public function check_update( $transient ):\stdClass {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		// Get the remote version
		$remote_update = $this->get_remote_update();

		// If a newer version is available, add the update
		if ( is_array( $remote_update ) && isset( $remote_update['new_version'] ) && $this->current_version !== $remote_update['new_version'] ) {
			$transient = $this->update_plugin_obj( $remote_update, $transient );
		}

		return $transient;
	}

	public function update_plugin_obj( array $remote_update, $transient ):\stdClass {
		$obj          = (object) $remote_update;
		$obj->url     = $this->update_path;
		$obj->package = $remote_update['download_url'];
		$transient->response[$this->plugin_file] = $obj;
		return $transient;
	}

	/**
	 * Return the remote version
	 */
	public function get_remote_update():array {
		$args = array(
			'body'      => array( 'action' => 'version' ),
			'sslverify' => ( str_contains( 'sptrix.com', $this->update_path ) ),
			'headers' => array(
				'Accept' => 'application/json'
			)
		);

		$response  = \wp_safe_remote_get( $this->update_path, $args );
		$json_body = \wp_remote_retrieve_body( $response );
		return json_decode( $json_body, true );
	}

}
