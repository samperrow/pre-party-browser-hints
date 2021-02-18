<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PrefetchSettings {

	public $prefetch_disableForLoggedInUsers = false;
	public $prefetch_enabled = false;
	public $ignoreKeywords;
	public $prefetch_initialization_delay = 0;





	protected function get_each_keyword() {
		foreach ($this->ignoreKeywords as $keyword) {
			echo $keyword . "\n";
		}
	}

	public function save_options() {
		$options = array(
			'prefetch_enabled'                 => isset( $_POST['prefetch_enabled'] )                 ? 'true' : 'false',
			'prefetch_disableForLoggedInUsers' => isset( $_POST['prefetch_disableForLoggedInUsers'] ) ? 'true' : 'false',
			'prefetch_delay'                   => isset( $_POST['prefetch_delay'] )                   ? Utils::strip_non_numbers($_POST['prefetch_delay']) : '0',
			'prefetch_ignoreKeywords'          => isset( $_POST['prefetch_ignoreKeywords'] )          ? $this->turn_textarea_to_json( $_POST['prefetch_ignoreKeywords'] ) : '',
			'prefetch_maxRPS'                  => isset( $_POST['prefetch_maxRPS'] )                  ? Utils::strip_non_numbers($_POST['prefetch_maxRPS']) : '3',
			'prefetch_hoverDelay'              => isset( $_POST['prefetch_hoverDelay'] )              ? Utils::strip_non_numbers( $_POST['prefetch_hoverDelay'] ) : '50',
		);

		update_option( 'pprh_prefetch_enabled',                 $options['prefetch_enabled'] );
		update_option( 'pprh_prefetch_disableForLoggedInUsers', $options['prefetch_disableForLoggedInUsers'] );
		update_option( 'pprh_prefetch_delay',                   $options['prefetch_delay'] );
		update_option( 'pprh_prefetch_ignoreKeywords',          $options['prefetch_ignoreKeywords'] );
		update_option( 'pprh_prefetch_maxRPS',                  $options['prefetch_maxRPS'] );
		update_option( 'pprh_prefetch_hoverDelay',              $options['prefetch_hoverDelay'] );
	}

	public function show_settings() {
		$this->set_values();
		$this->markup();
	}

	public function set_values() {
		$this->prefetch_disableForLoggedInUsers = \PPRH\Utils::is_option_checked( 'pprh_prefetch_disableForLoggedInUsers' );
		$this->prefetch_enabled = \PPRH\Utils::is_option_checked( 'pprh_prefetch_enabled' );

		$prefetch_ignoreKeywords = get_option( 'pprh_prefetch_ignoreKeywords' );
		$this->ignoreKeywords = json_decode( $prefetch_ignoreKeywords, true );
		$this->prefetch_initialization_delay = Utils::esc_get_option( 'pprh_prefetch_delay' );
	}

	public function markup() {
		?>
		<div class="postbox" id="prefetch">
			<div class="inside">
				<h3><?php esc_html_e( 'Auto Prefetch Settings', 'pprh' ); ?>
					<span class="pprh-help-tip-hint">
                        <span><?php _e( 'Prefetch entire pages before the user clicks on navigation links, making them load instantly. This will create prefetch hints automatically each time a page is loaded. These hints do not get saved in the database. Special thanks to <a href="https://wpspeedmatters.com/">Gijo Varghese</a> for providing assistance with this prefetch feature.',	'pprh'	); ?></span>
                    </span>
				</h3>

				<table class="form-table">
					<tbody>
					<tr>
						<th><?php esc_html_e( 'Enable this feature? (This allows for navigation links to be automatically prefetched while in viewport.)', 'pprh' ); ?></th>

						<td>
							<input type="checkbox" name="prefetch_enabled" value="true" <?php echo $this->prefetch_enabled; ?>/>
							<p><?php esc_html_e( 'When navigation (anchor) links are being moused over, this feature will initiate a prefetch request for the URL in the link. Select No (default) to disable this feature.', 'pprh' ); ?></p>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Disable prefetching for logged in users?', 'pprh' ); ?></th>

						<td>
							<input type="checkbox" name="prefetch_disableForLoggedInUsers" value="1" <?php echo $this->prefetch_disableForLoggedInUsers; ?>/>
							<p><?php esc_html_e( 'It usually is not necessary for logged in users to prefetch content. This will save some server resources.', 'pprh' ); ?></p>
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'Prefetch initiation delay (seconds):', 'pprh' ); ?></th>

						<td>
							<input type="number" step="1" min="0" max="30" name="prefetch_delay" value="<?php echo $this->prefetch_initialization_delay; ?>">
							<p><?php esc_html_e( 'Start prefetching after a short delay. Will be started when the browser becomes idle. Default is 0 seconds.', 'pprh' ); ?></p>
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'Ignore these keywords:', 'pprh' ); ?></th>

						<td>
							<textarea name="prefetch_ignoreKeywords" rows="6"><?php $this->get_each_keyword(); ?></textarea>
							<p><?php esc_html_e( 'A list of keywords to ignore from prefetching. One keyword per line. You may use an astericks ("*") to implement wildcard keywords to match any link (example: "/products*"). Any links matching any of the values specified will not be prefetch.', 'pprh'); ?></p>
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'Maximum requests per second:', 'pprh' ); ?></th>

						<td>
							<input type="number" step="1" min="1" max="100" name="prefetch_maxRPS" value="<?php echo Utils::esc_get_option( 'pprh_prefetch_maxRPS' );; ?>">
							<p><?php esc_html_e( 'Number of prefetch hints that can be created per second. Default is 3 per second.', 'pprh' ); ?></p>
						</td>
					</tr>

					<tr>
						<th><?php esc_html_e( 'Delay in prefetching links on mouse hover (milliseconds)', 'pprh' ); ?></th>

						<td>
							<input type="number" step="50" min="0" max="1000" name="prefetch_hoverDelay" value="<?php echo Utils::esc_get_option( 'pprh_prefetch_hoverDelay' );; ?>">
							<p><?php esc_html_e( 'Set a short pause after the mouse hovers over a link before the prefetch hint is created. Default is 50 milliseconds.', 'pprh' ); ?></p>
						</td>
					</tr>

					</tbody>
				</table>
			</div>
		</div>

		<?php
		// cite https://github.com/gijo-varghese/flying-pages
	}

	public function turn_textarea_to_json( $text ) {
		$str = str_replace( "\r\n", ', ', $text );
		return Utils::json_to_array( $str );
	}

}