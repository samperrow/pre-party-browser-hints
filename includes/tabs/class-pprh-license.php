<?php

namespace PPRH;

// prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// TODO
// show accurate results after getting/setting page/license info

class License {

	private $license_url;

	private $license_key;
	private $license_status;
	private $license_email;
	private $license_username;


	public function __construct() {

		$this->license_url = 'https://sphacks.io';

		$this->license_key      = get_option( 'pprh_license_key' );
		$this->license_status   = get_option( 'pprh_license_status' );
		$this->license_email    = get_option( 'pprh_license_email' );
		$this->license_username = get_option( 'pprh_license_username' );

		$this->show_page();
	}

	public function show_page() {

		?>
		<div class="pprhAdminPage" id="pprh-license">
		<h3>License</h3>
		<form action="" method="post" style="width: 490px;">
			<table class="form-table">
				<tbody>
					<tr>
						<th style="width:100px;"><label for="pprh_license_key">License Key</label></th>
						<td><input class="regular-text" type="text" id="pprh_license_key" name="pprh_license_key" value="<?php echo $this->license_key; ?>"></td>
					</tr>

					<?php
					if ( $this->license_key ) {
						$this->show_license_content();
					}
					?>

				</tbody>

			</table>
			<p class="submit text-center">
				<input id="pprhActivateLicense" type="submit" name="slm_activate" value="Activate" class="button-secondary" />
				<input id="pprhDeactivateLicense" type="submit" name="slm_deactivate" value="Deactivate" class="button" />
                <input id="pprhOpenCheckoutModal" type="button" class="button button-primary" value="Purchase License"/>
			</p>
		</form>
		</div>
		<?php
		if ( isset( $_REQUEST['slm_activate'] ) ) {
			$this->license_query( 'slm_activate' );
		}

		if ( isset( $_REQUEST['slm_deactivate'] ) ) {
			$this->license_query( 'slm_deactivate' );
		}
	}

	public function show_license_content() {
		?>
		<tr>
			<th style="width:100px;"><label for="pprh_license_status">License status:</label></th>
			<td><input class="regular-text" type="text" id="pprh_license_status" name="pprh_license_status" value="<?php echo $this->license_status; ?>"></td>
		</tr>

		<tr>
			<th style="width:100px;"><label for="pprh_license_email">Email associated with license:</label></th>
			<td><input class="regular-text" type="text" id="pprh_license_email" name="pprh_license_email" value="<?php echo $this->license_email; ?>"></td>
		</tr>

		<tr>
			<th style="width:100px;"><label for="pprh_license_name">Name associated with license:</label></th>
			<td><input class="regular-text" type="text" id="pprh_license_username" name="pprh_license_username" value="<?php echo $this->license_username; ?>"></td>
		</tr>

		<?php
	}

	public function prepare_license_action( $action ) {

		$license_key = $_REQUEST['pprh_license_key'];

		$api_params = array(
			'slm_action'       => $action,
			'pprh_license_key' => $license_key,
			'domain'           => get_site_url()
		);

		$query    = esc_url_raw( add_query_arg( $api_params, $this->license_url ) );
		$response = wp_remote_get(
			$query,
			array(
				'timeout'   => 5,
				'sslverify' => true,
			)
		);

		return json_decode( wp_remote_retrieve_body( $response ), false, 512, JSON_THROW_ON_ERROR );
	}

	public function license_query( $action ) {

		$license_info = $this->prepare_license_action( $action );

		if ( 'success' === $license_info->result ) {
			echo '<br />The following message was returned from the server: ' . $license_info->message;
			$this->update_options( $license_info->data );
		} else {
			echo '<br/>The following message was returned from the server: ' . $license_info->message;
		}
	// wp_die();
	}

	public function update_options( $data ) {
		update_option( 'pprh_license_key', $data->license_key );
		update_option( 'pprh_license_status', $data->lic_status );
		update_option( 'pprh_license_email', $data->email );
		update_option( 'pprh_license_username', $data->name );
	}

	public function verify_license() {
		// $license_key = get_option( 'pprh_license_key' );
		$license_key = 'x';

		$license_info = $this->prepare_license_action( 'slm_check' );

		if ( 'success' === $license_info->result ) {
			echo sprintf( '<br />The following message was returned from the server: %s', $license_info->message );
			$this->update_options( $license_info->data );
		} else {
			echo sprintf( '<br/>The following message was returned from the server: %s', $license_info->message );
		}

		return 'success' === $license_info->result;
	}

}

new License();
