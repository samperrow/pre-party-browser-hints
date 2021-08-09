<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PreconnectSettings {

	public $autoload     = false;
	public $allow_unauth = false;

	public static function save_options() {
		$options = array(
			'autoload_preconnects' => isset( $_POST[ 'pprh_preconnect_autoload_preconnects' ] ) ? 'true' : 'false',
			'allow_unauth'         => isset( $_POST[ 'pprh_preconnect_allow_unauth' ] ) ? 'true' : 'false',
			'preconnect_set'       => ( isset( $_POST[ 'pprh_preconnect_set' ] ) && 'Reset' === $_POST[ 'pprh_preconnect_set' ] ) ? 'false' : 'true'
		);

		Utils::update_option( 'pprh_preconnect_autoload', $options['autoload_preconnects'] );
		Utils::update_option( 'pprh_preconnect_allow_unauth', $options['allow_unauth'] );
		Utils::update_option( 'pprh_preconnect_set', $options['preconnect_set'] );
	}

	public function show_settings() {
		$this->set_values();
		$this->markup();
	}

	public function set_values() {
		$this->autoload     = \PPRH\Utils::does_option_match( 'pprh_preconnect_autoload', 'true', 'checked' );
		$this->allow_unauth = \PPRH\Utils::does_option_match( 'pprh_preconnect_allow_unauth', 'true', 'checked' );
	}

	public function markup() {
		?>
		<table class="form-table">
			<tbody>

				<tr>
					<th><?php esc_html_e( 'Enable Auto Preconnect?', 'pprh' ); ?></th>
					<td>
						<input type="checkbox" name="pprh_preconnect_autoload_preconnects" value="true" <?php echo $this->autoload; ?>/>
						<p><?php esc_html_e( 'This feature allows preconnect hints to be automatically created. JavaScript, CSS, and images loaded from external domains will preconnect automatically.', 'pprh' ); ?></p>
					</td>
				</tr>

				<tr>
					<th><?php esc_html_e( 'Allow unauthenticated users to automatically set preconnect hints via Ajax?', 'pprh' ); ?></th>
					<td>
						<input type="checkbox" name="pprh_preconnect_allow_unauth" value="1" <?php echo $this->allow_unauth; ?>/>
						<p><?php esc_html_e( 'This plugin has a feature which allows preconnect hints to be automatically created asynchronously in the background with Ajax by the first user to visit a page (assuming the user has that option to be reset). There is an extremely remote possibility that if a visitor knew the hints would be set, they could choose to manually load many external scripts, which could trick the plugin script into accepting these as valid preconnect hints. But again this is a very remote possiblity and only a nuisance, not a vulnerability, due to the strict sanitization procedures in place.', 'pprh' ); ?></p>
					</td>
				</tr>

				<?php $this->load_reset_settings(); ?>

			</tbody>
		</table>
		<?php
	}

	public function load_reset_settings() {
		$res = \apply_filters( 'pprh_display_preconnect_markup', array() );
		if ( ! empty( $res ) ) {
			return false;
		}
		?>
		<tr>
			<th><?php esc_html_e( 'Reset automatically created preconnect links?', 'pprh' ); ?></th>

			<td>
				<input type="submit" name="pprh_preconnect_set" class="pprh-reset button-primary" data-text="reset auto-preconnect hints?" value="Reset">
				<p><?php esc_html_e( 'This will reset automatically created preconnect hints, allowing new preconnect hints to be generated when your front end is loaded.', 'pprh' ); ?></p>
			</td>
		</tr>
		<?php
		return false;
	}

}
