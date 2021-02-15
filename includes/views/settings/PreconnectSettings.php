<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PreconnectSettings {

	public $load_basic;
	public $autoload = false;
	public $allow_unauth = false;

	public function set_values() {
		$this->load_basic = apply_filters( 'pprh_sc_preconnect_pro', true );
		$this->autoload = \PPRH\Utils::is_option_checked( 'pprh_preconnect_autoload' );
		$this->allow_unauth = \PPRH\Utils::is_option_checked( 'pprh_preconnect_allow_unauth' );
    }

	public function save_options() {
	    $options = array(
			'autoload_preconnects' => isset( $_POST['preconnect_autoload_preconnects'] )  ? 'true' : 'false',
			'allow_unauth'         => isset( $_POST['preconnect_allow_unauth'] )          ? 'true' : 'false',
        );

		update_option('pprh_preconnect_autoload', $options['autoload_preconnects']);
		update_option('pprh_preconnect_allow_unauth', $options['allow_unauth']);

		if (isset($_POST['pprh_preconnect_set'])) {
			update_option('pprh_preconnect_set', 'false');
		}
    }

	public function markup() {
	    $this->set_values();
		?>
		<div class="postbox" id="preconnect">
			<div class="inside">
				<h3><?php esc_html_e( 'Auto Preconnect Settings', 'pprh' ); ?>
					<span class="pprh-help-tip-hint">
                        <span><?php _e( 'This feature will collect the domain names of external resources used on your site, and create resource hints from those. For example, if you are using Google Fonts and Google Analytics, this feature will find the host names of these resources ("https://www.google-analytics.com", "https://fonts.gstatic.com", "https://fonts.googleapis.com"), and create resource hints for those. To initialize this, you only need to view a page on your website and this plugin will take care of the rest! It will automatically run after plugin installation, or by clicking the "Reset" button below.', 'pprh' ); ?></span>
                    </span>
				</h3>

				<table class="form-table">
					<tbody>

					<tr>
						<th><?php esc_html_e( 'Automatically set preconnect hints?', 'pprh' ); ?></th>

						<td>
							<input type="checkbox" name="preconnect_autoload_preconnects" value="1" <?php echo $this->autoload; ?>/>
							<p><?php esc_html_e( 'JavaScript, CSS, and images loaded from external domains will preconnect automatically.', 'pprh' ); ?></p>
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'Allow unauthenticated users to automatically set preconnect hints via Ajax?', 'pprh' ); ?></th>

						<td>
							<input type="checkbox" name="preconnect_allow_unauth" value="1" <?php echo $this->allow_unauth; ?>/>
							<p><?php esc_html_e( 'This plugin has a feature which allows preconnect hints to be automatically created asynchronously in the background with Ajax by the first user to visit a page (assuming the user has that option to be reset). There is an extremely remote possibility that if a visitor knew the hints would be set, they could choose to manually load many external scripts, which could trick the plugin script into accepting these as valid preconnect hints. But again this is a very remote possiblity and only a nuisance, not a vulnerability, due to the strict sanitization procedures in place.', 'pprh' ); ?></p>
						</td>
					</tr>

					<?php $this->load_pro(); ?>

					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	public function load_pro() {
		$this->load_basic = apply_filters( 'pprh_pro_settings', 'preconnect' );

		if ( true !== $this->load_basic ) { ?>
			<tr>
				<th><?php esc_html_e( 'Reset automatically created preconnect links?', 'pprh' ); ?></th>

				<td>
					<input type="submit" name="pprh_preconnect_set" id="pprhPreconnectReset" class="button-secondary" value="Reset">
					<p><?php esc_html_e( 'This will reset automatically created preconnect hints.', 'pprh' ); ?></p>
				</td>
			</tr>

			<?php
		} else {
			do_action( 'pprh_show_preconnect_options' );
		}
	}

}