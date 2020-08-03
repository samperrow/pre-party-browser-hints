<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

new Gen_Settings();

class Gen_Settings extends Settings {

	public function __construct() {
		$this->general_settings();
	}

	public function general_settings() {
		?>
        <div id="pprh-general" class="settings pprh-content">
            <h2 style="margin-top: 30px;"><?php esc_html_e( 'General Settings', 'pprh' ); ?></h2>

            <table class="pprh-settings-table pprh-content">
                <tbody>
                <?php
                $this->disable_auto_wp_hints();
                $this->set_hint_destination();
                do_action( 'pprh_sc_general_settings' );
                ?>
                </tbody>
            </table>
        </div>
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
						<option value="true" <?php $this->get_option_status( 'disable_wp_hints', 'true' ); ?>>
							<?php esc_html_e( 'Yes', 'pprh' ); ?>
						</option>
						<option value="false" <?php $this->get_option_status( 'disable_wp_hints', 'false' ); ?>>
							<?php esc_html_e( 'No', 'pprh' ); ?>
						</option>
					</select>
				</label>
			</td>
		</tr>

		<?php
	}



	public function set_hint_destination() {
		?>
		<tr>
			<th><?php esc_html_e( 'Send resource hints in HTML head or HTTP header?', 'pprh' ); ?></th>

			<td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Sending hints in the HTTP header allows the browser to receive them sooner, but they will be slightly more difficult to observe than loading them in the HTML head.', 'pprh' ); ?></span>
				</span>
			</td>

			<td>
				<select id="pprhHintLocation" name="html_head">
					<option value="true" <?php $this->get_option_status( 'html_head', 'true' ); ?>>
						<?php esc_html_e( 'HTML &lt;head&gt;', 'pprh' ); ?>
					</option>
					<option value="false" <?php $this->get_option_status( 'html_head', 'false' ); ?>>
						<?php esc_html_e( 'HTTP Header', 'pprh' ); ?>
					</option>
				</select>
			</td>
		</tr>

		<?php
	}



}
