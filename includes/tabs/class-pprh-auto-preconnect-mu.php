<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

new Auto_Preconnect();

class Auto_Preconnect {

    public $load_adv;

	public function __construct() {
	    do_action( 'pprh_load_auto_preconnects_mu' );
		$this->preconnect_html();
	}

	public function preconnect_html() {
		?>
        <div id="pprh-auto-preconnect" class="pprh-content">
            <h2 style="margin-top: 30px;"><?php esc_html_e( 'Auto Preconnect Settings', 'pprh' ); ?></h2>

            <table class="pprh-settings-table">
                <tbody>

                <?php
                    $this->auto_set_globals();
                    $this->allow_unauth();

				    $load_basic = apply_filters( 'pprh_sc_preconnect_pro', true );
                    if ( $load_basic ) {
						$this->reset_preconnects();
					}

                ?>
                </tbody>
            </table>
        </div>
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
				<input type="submit" name="pprh_prec_preconnects_set" id="pprhPreconnectReset" class="button-secondary" value="Reset">
			</td>
		</tr>
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
						<option value="true" <?php Utils::get_option_status( 'prec_autoload_preconnects', 'true' ); ?>>
							<?php esc_html_e( 'Yes', 'pprh' ); ?>
						</option>
						<option value="false" <?php Utils::get_option_status( 'prec_autoload_preconnects', 'false' ); ?>>
							<?php esc_html_e( 'No', 'pprh' ); ?>
						</option>
					</select>
				</label>
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
						<option value="true" <?php Utils::get_option_status( 'prec_allow_unauth', 'true' ); ?>>
							<?php esc_html_e( 'Yes', 'pprh' ); ?>
						</option>
						<option value="false" <?php Utils::get_option_status( 'prec_allow_unauth', 'false' ); ?>>
							<?php esc_html_e( 'No', 'pprh' ); ?>
						</option>
					</select>
				</label>
			</td>
		</tr>

		<?php
	}

}
