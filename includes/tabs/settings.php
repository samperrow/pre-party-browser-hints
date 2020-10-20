<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	public function __construct() {
		$this->display_settings();
	}

	public function display_settings() {
		?>
			<div id="pprh-settings" class="pprh-content">
				<form method="post" action="<?php echo admin_url(); ?>admin.php?page=pprh-plugin-settings">
					<?php
					wp_nonce_field( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
					$this->save_user_options();
					$this->settings_html();
					?>
				</form>
			</div>
		<?php
	}

	public function save_user_options() {

		if ( isset( $_POST['save_options'] ) || isset( $_POST['pprh_preconnects_set'] ) ) {

			if ( check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' ) ) {

				if ( isset( $_POST['save_options'] ) ) {
					update_option( 'pprh_autoload_preconnects', wp_unslash( $_POST['autoload_preconnects'] ) );
					update_option( 'pprh_disable_wp_hints', wp_unslash( $_POST['disable_wp_hints'] ) );
					update_option( 'pprh_allow_unauth', wp_unslash( $_POST['allow_unauth'] ) );
					update_option( 'pprh_html_head', wp_unslash( $_POST['html_head'] ) );
				} elseif ( isset( $_POST['pprh_preconnects_set'] ) ) {
					update_option( 'pprh_preconnects_set', 'false' );
				}
			}
		}
	}

	public function settings_html() {

		?>
		<h2 style="margin-top: 30px;"><?php esc_html_e( 'Settings', 'pprh' ); ?></h2>

		<table class="pprh-settings-table">
			<tbody>

			<?php
			$this->auto_set_globals();
			$this->reset_preconnects();
			$this->allow_unauth();
			$this->disable_auto_wp_hints();
			$this->set_hint_destination();
			?>
			</tbody>

			<tfoot>
				<tr>
					<td colspan="3">
						<input type="submit" name="save_options" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pprh' ); ?>" />
					</td>
                    <td></td>
				</tr>
			</tfoot>

		</table>

		<?php
	}

	public function auto_set_globals() {
		?>
		<tr>
			<th><?php esc_html_e( 'Automatically set preconnect hints?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'JavaScript, CSS, and images loaded from external domains will preconnect automatically.', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<label>
					<select name="autoload_preconnects">
						<option value="true" <?php Utils::get_option_status( 'autoload_preconnects', 'true' ); ?>>
							<?php esc_html_e( 'Yes', 'pprh' ); ?>
						</option>
						<option value="false" <?php Utils::get_option_status( 'autoload_preconnects', 'false' ); ?>>
							<?php esc_html_e( 'No', 'pprh' ); ?>
						</option>
					</select>
				</label>
			</td>
            <td>
                <i><?php esc_html_e( 'This feature will collect the domain names of external resources used on your site, and create resource hints from those. For example, if you are using Google Fonts and Google Analytics, the "auto preconnect" feature will find the host names of these resources ("https://www
                    .google-analytics.com", "https://fonts.gstatic.com", "https://fonts.googleapis.com"), and create resource hints for those. To initialize this, you only need to view a page on your website and this plugin will take care of the rest! It will automatically run after plugin
                    installation, or by clicking the "Reset" button below.', 'pprh' ); ?></i>
            </td>
		</tr>

		<?php
	}

	public function reset_preconnects() {
		?>
        <tr>
            <th><?php esc_html_e( 'Reset automatically created preconnect links?', 'pprh' ); ?></th>

            <td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This will reset automatically created preconnect hints.', 'pprh' ); ?></span>
				</span>
            </td>

            <td>
                <input type="submit" name="pprh_preconnects_set" id="pprhPreconnectReset" class="button-secondary" value="Reset">
            </td>
            <td>
                <i><?php esc_html_e( 'Clicking this button will re-initialize the creation of the auto preconnect hints, and replace previously created auto-preconnect hints.' ); ?></i>
            </td>
        </tr>

		<?php
	}

	public function allow_unauth() {
		?>
        <tr>
            <th><?php esc_html_e( 'Allow unauthenticated users to automatically set preconnect hints via Ajax?', 'pprh' ); ?></th>

            <td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This plugin has a feature which allows preconnect hints to be automatically created asynchronously in the background with Ajax by the first user to visit a page (assuming the user has that option to be reset). There is an extremely remote possibility that if a visitor knew the hints would be set, they could choose to manually load many external scripts, which could trick the plugin script into accepting these as valid preconnect hints. But again this is a very remote possiblity and only a nuisance, not a vulnerability, due to the strict sanitization procedures in place.', 'pprh' ); ?></span>
				</span>
            </td>

            <td>
                <label>
                    <select name="allow_unauth">
                        <option value="true" <?php Utils::get_option_status( 'allow_unauth', 'true' ); ?>>
							<?php esc_html_e( 'Yes', 'pprh' ); ?>
                        </option>
                        <option value="false" <?php Utils::get_option_status( 'allow_unauth', 'false' ); ?>>
							<?php esc_html_e( 'No', 'pprh' ); ?>
                        </option>
                    </select>
                </label>
            </td>
            <td></td>
        </tr>

		<?php
	}

	public function disable_auto_wp_hints() {
		?>
		<tr>
			<th><?php esc_html_e( 'Disable automatically generated WordPress resource hints?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This option will remove three resource hints automatically generated by WordPress, as of 4.8.2.', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<label>
					<select name="disable_wp_hints">
						<option value="true" <?php Utils::get_option_status( 'disable_wp_hints', 'true' ); ?>>
							<?php esc_html_e( 'Yes', 'pprh' ); ?>
						</option>
						<option value="false" <?php Utils::get_option_status( 'disable_wp_hints', 'false' ); ?>>
							<?php esc_html_e( 'No', 'pprh' ); ?>
						</option>
					</select>
				</label>
			</td>
            <td></td>
		</tr>

		<?php
	}

	public function set_hint_destination() {
		?>
		<tr>
			<th><?php esc_html_e( 'Send resource hints in HTML head or HTTP header?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Send hints', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<select id="pprhHintLocation" name="html_head">
					<option value="true" <?php Utils::get_option_status( 'html_head', 'true' ); ?>>
						<?php esc_html_e( 'HTML &lt;head&gt;', 'pprh' ); ?>
					</option>
					<option value="false" <?php Utils::get_option_status( 'html_head', 'false' ); ?>>
						<?php esc_html_e( 'HTTP Header', 'pprh' ); ?>
					</option>
				</select>
			</td>
            <td></td>
		</tr>

		<?php
	}



}

if ( is_admin() ) {
	new Settings();
}
