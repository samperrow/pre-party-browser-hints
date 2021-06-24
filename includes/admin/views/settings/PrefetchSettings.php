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
	public $prefetch_max_prefetches;

	public static function turn_textarea_to_csv( $text ) {
		$text = trim( $text );
		return explode( "\r\n", $text );
	}

	public static function save_options() {
		$options = self::get_options();
		self::update_options( $options );
	}

	public static function get_options() {
		$options = array(
			'pprh_prefetch_enabled'                 => isset( $_POST['pprh_prefetch_enabled'] )                 ? 'true' : 'false',
			'pprh_prefetch_disableForLoggedInUsers' => isset( $_POST['pprh_prefetch_disableForLoggedInUsers'] ) ? 'true' : 'false',
			'pprh_prefetch_delay'                   => isset( $_POST['pprh_prefetch_delay'] )                   ? Utils::strip_non_numbers( $_POST['pprh_prefetch_delay'] ) : '0',
			'pprh_prefetch_ignoreKeywords'          => isset( $_POST['pprh_prefetch_ignoreKeywords'] )          ? self::turn_textarea_to_csv( $_POST['pprh_prefetch_ignoreKeywords'] ) : '',
			'pprh_prefetch_maxRPS'                  => isset( $_POST['pprh_prefetch_maxRPS'] )                  ? Utils::strip_non_numbers( $_POST['pprh_prefetch_maxRPS'] ) : '3',
			'pprh_prefetch_hoverDelay'              => isset( $_POST['pprh_prefetch_hoverDelay'] )              ? Utils::strip_non_numbers( $_POST['pprh_prefetch_hoverDelay'] ) : '50',
			'pprh_prefetch_max_prefetches'          => isset( $_POST['pprh_prefetch_max_prefetches'] )          ? Utils::strip_non_numbers( $_POST['pprh_prefetch_max_prefetches'] ) : '10',
		);

		return $options;
	}

	public static function update_options( $options ) {
		\update_option( 'pprh_prefetch_enabled',                 $options['pprh_prefetch_enabled'] );
		\update_option( 'pprh_prefetch_disableForLoggedInUsers', $options['pprh_prefetch_disableForLoggedInUsers'] );
		\update_option( 'pprh_prefetch_delay',                   $options['pprh_prefetch_delay'] );
		\update_option( 'pprh_prefetch_ignoreKeywords',          $options['pprh_prefetch_ignoreKeywords'] );
		\update_option( 'pprh_prefetch_maxRPS',                  $options['pprh_prefetch_maxRPS'] );
		\update_option( 'pprh_prefetch_hoverDelay',              $options['pprh_prefetch_hoverDelay'] );
		\update_option( 'pprh_prefetch_max_prefetches',          $options['pprh_prefetch_max_prefetches'] );
	}



	public function get_each_keyword( $keywords ) {
	    if ( is_null( $keywords ) ) {
	        return '';
        }

		$keywords = explode( ', ', $keywords );

	    $str = '';
	    $count = count( $keywords );
	    $idx = 0;

		foreach ( $keywords as $keyword ) {
			$idx++;
			$str .= $keyword;

			if ( $idx < $count ) {
				$str .= "\n";
			}
		}

		return $str;
	}

	public function show_settings() {
		$this->set_values();
		$this->markup();
	}

	public function set_values() {
		$this->prefetch_disableForLoggedInUsers = \PPRH\Utils::is_option_checked( 'pprh_prefetch_disableForLoggedInUsers' );
		$this->prefetch_enabled = \PPRH\Utils::is_option_checked( 'pprh_prefetch_enabled' );

		$prefetch_ignoreKeywords = \get_option( 'pprh_prefetch_ignoreKeywords' );
		$this->ignoreKeywords = implode( ', ', $prefetch_ignoreKeywords );
		$this->prefetch_initialization_delay = Utils::esc_get_option( 'pprh_prefetch_delay' );
		$this->prefetch_max_prefetches = Utils::esc_get_option( 'pprh_prefetch_max_prefetches' );
	}

	public function markup() {
		?>
<!--        <span class="pprh-help-tip-hint">-->
<!--            <span>--><?php //_e( 'Prefetch entire pages before the user clicks on navigation links, making them load instantly. This will create prefetch hints automatically each time a page is loaded. These hints do not get saved in the database. Special thanks to <a href="https://wpspeedmatters.com/">Gijo Varghese</a> for providing assistance with this prefetch feature.', 'pprh'); ?><!--</span>-->
<!--        </span>-->

        <table class="form-table">

            <tbody>

                <tr>
                    <th><?php esc_html_e( 'Enable this feature? (This allows for navigation links to be automatically prefetched while in viewport.)', 'pprh' ); ?></th>
                    <td>
                        <input type="checkbox" name="pprh_prefetch_enabled" class="toggleMetaBox" value="true" <?php echo $this->prefetch_enabled; ?>/>
                        <p><?php esc_html_e( 'When navigation (anchor) links are being moused over, this feature will initiate a prefetch request for the URL in the link. Select No (default) to disable this feature.', 'pprh' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Disable prefetching for logged in users?', 'pprh' ); ?></th>
                    <td>
                        <input type="checkbox" name="pprh_prefetch_disableForLoggedInUsers" value="true" <?php echo $this->prefetch_disableForLoggedInUsers; ?>/>
                        <p><?php esc_html_e( 'It usually is not necessary for logged in users to prefetch content. This will save some server resources.', 'pprh' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'Prefetch initiation delay (seconds):', 'pprh' ); ?></th>
                    <td>
                        <input type="number" step="1" min="0" max="30" name="pprh_prefetch_delay" value="<?php echo $this->prefetch_initialization_delay; ?>">
                        <p><?php esc_html_e( 'Start prefetching after a short delay. Will be started when the browser becomes idle. Default is 0 seconds.', 'pprh' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'Ignore these keywords:', 'pprh' ); ?></th>
                    <td>
                        <textarea name="pprh_prefetch_ignoreKeywords" rows="6"><?php echo $this->get_each_keyword( $this->ignoreKeywords ); ?></textarea>
                        <p><?php esc_html_e( 'A list of keywords to ignore from prefetching. One keyword per line. You may use an astericks (*) after a keyword (ex: "/products*") to act as a wildcard, preventing links with that value from being prefetched.', 'pprh'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'Maximum requests per second:', 'pprh' ); ?></th>
                    <td>
                        <input type="number" step="1" min="1" max="100" name="pprh_prefetch_maxRPS" value="<?php echo Utils::esc_get_option( 'pprh_prefetch_maxRPS' );; ?>">
                        <p><?php esc_html_e( 'Number of prefetch hints that can be created per second. Default is 3 per second.', 'pprh' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'Delay in prefetching links on mouse hover (milliseconds)', 'pprh' ); ?></th>
                    <td>
                        <input type="number" step="50" min="0" max="1000" name="pprh_prefetch_hoverDelay" value="<?php echo Utils::esc_get_option( 'pprh_prefetch_hoverDelay' );; ?>">
                        <p><?php esc_html_e( 'Set a short pause after the mouse hovers over a link before the prefetch hint is created. Default is 50 milliseconds.', 'pprh' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><?php esc_html_e( 'Maximum number of prefetch hints loaded? (Excluding mouse hover or touch events)', 'pprh' ); ?></th>
                    <td>
                        <input type="number" step="1" min="1" max="100" name="pprh_prefetch_max_prefetches" value="<?php echo $this->prefetch_max_prefetches; ?>">
                        <p><?php esc_html_e( 'Set the maximum number of prefetch hints you would like to be loaded. This can save some server resources. Default is 10.', 'pprh' ); ?></p>
                    </td>
                </tr>

            </tbody>
        </table>
		<?php
	}

}