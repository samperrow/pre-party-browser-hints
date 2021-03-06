<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PreconnectSettings {

	public $autoload = false;
	public $allow_unauth = false;

	protected $on_pprh_admin = false;

	public function __construct($on_pprh_admin) {
	    $this->on_pprh_admin = $on_pprh_admin;
    }

	public function save_options() {
	    $options = array(
			'autoload_preconnects' => isset( $_POST['pprh_preconnect_autoload_preconnects'] )  ? 'true' : 'false',
			'allow_unauth'         => isset( $_POST['pprh_preconnect_allow_unauth'] )          ? 'true' : 'false',
            'preconnect_set'       => ( isset( $_POST['pprh_preconnect_set'] ) && 'Reset' === $_POST['pprh_preconnect_set'] ) ? 'false' : 'true'
        );

		update_option('pprh_preconnect_autoload', $options['autoload_preconnects']);
		update_option('pprh_preconnect_allow_unauth', $options['allow_unauth']);
		update_option('pprh_preconnect_set', $options['preconnect_set']);

//		if (isset($_POST['pprh_preconnect_set'])) {
//			update_option('pprh_preconnect_set', 'false');
//		}
    }

	public function show_settings() {
		$this->set_values();
		$this->markup();
	}

	public function set_values() {
		$this->autoload = \PPRH\Utils::is_option_checked( 'pprh_preconnect_autoload' );
		$this->allow_unauth = \PPRH\Utils::is_option_checked( 'pprh_preconnect_allow_unauth' );
	}

	public function markup() {
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
							<input type="checkbox" name="pprh_preconnect_autoload_preconnects" value="true" <?php echo $this->autoload; ?>/>
							<p><?php esc_html_e( 'JavaScript, CSS, and images loaded from external domains will preconnect automatically.', 'pprh' ); ?></p>
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
			</div>
		</div>
		<?php
	}

    public function load_reset_settings() {
		if ( $this->on_pprh_admin && PPRH_PRO_PLUGIN_ACTIVE ) {
            do_action( 'pprh_sc_show_preconnect_settings');
            return true;
		} else { ?>
            <tr>
                <th><?php esc_html_e( 'Reset automatically created preconnect links?', 'pprh' ); ?></th>

                <td>
                    <input type="submit" name="pprh_preconnect_set" id="pprhPreconnectReset" class="pprh-reset button-primary" data-text="reset auto-preconnect hints?" value="Reset">
                    <p><?php esc_html_e( 'This will reset automatically created preconnect hints, allowing new preconnect hints to be generated when your front end is loaded.', 'pprh' ); ?></p>
                </td>
            </tr>
		<?php
		    return false;
		}
	}

}


