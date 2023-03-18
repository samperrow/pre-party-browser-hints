<?php

namespace PPRH\Settings;

use PPRH\Utils\Sanitize;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsView extends SettingsSave {

	public $pro_options;

    public $show_posts_on_front;

	public function __construct( bool $show_posts_on_front ) {
//        parent::__construct( $show_posts_on_front );
        $this->show_posts_on_front = $show_posts_on_front;
		$this->pro_options = \get_option( 'pprh_options', array() );
    }

	public static function markup( $on_pprh_admin ) {
		?>
        <div class="pprh-content settings">
            <form method="post" action="">
				<?php
				\wp_nonce_field( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );

				if ( $on_pprh_admin ) {
					\wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
					\do_meta_boxes( PPRH_ADMIN_SCREEN, 'normal', null );
				}
				?>
                <div class="text-center">
                    <input type="submit" name="pprh_save_options" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pre-party-browser-hints' ); ?>" />
                </div>
            </form>
        </div>
		<?php
	}

	public function create_preload_metabox() {
		$enabled = ( 'true' === $this->pro_options['preload_enabled'] ) ? 'checked' : '';
		?>
        <table class="form-table"><tbody>

            <tr>
                <th scope="row"><?php \_e( 'Enable Auto Preload?', 'pprh-pro' ); ?><td>
                    <label for="preload_enabled"><input type="checkbox" name="preload_enabled" value="true" <?php echo $enabled; ?>/></label>
                    <p><?php \_e( 'This feature allows preload hints to be automatically created. Critical resources will be preloaded automatically.', 'pprh-pro' ); ?></p>
                </td>
            </tr>

			<?php if ( $this->show_posts_on_front ) { ?>
                <tr>
                    <th scope="row"><?php \_e( 'Reset Home Preload Links?', 'pprh-pro' ); ?></th>
                    <td>
                        <input type="submit" name="reset_home_preload" class="button-primary pprh-reset" data-text="reset auto preload hints used only on the home page?" value="Reset">
                        <p><?php \_e( 'This will reset automatically created preload hints on the home page. (This option only applies when the home page is set to display recent posts.)', 'pprh-pro' ); ?></p>
                    </td>
                </tr>
			<?php } ?>

            <tr>
                <th scope="row"><?php \_e( 'Reset Global Preload Links?', 'pprh-pro' ); ?></th>
                <td>
                    <input type="submit" name="reset_global_preload" class="button-primary pprh-reset" data-text="reset auto preload globally?" value="Reset">
                    <p><?php \_e( 'This will reset all of the automatically generated global preload hints, which are used on all posts and pages.', 'pprh-pro' ); ?></p>
                </td>
            </tr>
            </tbody></table>
		<?php
		return true;
	}

	public function prerender_settings_markup() {
		$prerender_enabled      = ( 'true' === $this->pro_options['prerender_enabled'] ) ? 'checked' : '';
		$enable_for_logged_in   = ( 'true' === $this->pro_options['prerender_enable_for_logged_in_users'] ) ? 'checked' : '';
		$auto_reset_days_option = $this->pro_options['prerender_auto_reset_days'];
		?>

        <table class="form-table">
            <tbody>

            <tr>
                <th scope="row"><?php \_e( 'Enable Auto Prerender?', 'pprh-pro' ); ?>
                    <span class="pprh-help-tip-hint">
                        <span><?php \_e( 'This feature allows unique prerender hints to be automatically created for individual pages, based on each pages\' most common referer.', 'pprh-pro' ); ?></span>
                    </span>
                </th>

                <td>
                    <label for="prerender_enabled">
                        <input type="checkbox" class="toggleMetaBox" name="prerender_enabled" value="true" <?php echo $prerender_enabled; ?>/>
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php \_e( 'Enable analytics data to be recorded for logged in users?', 'pprh-pro' ); ?></th>
                <td>
                    <label for="prerender_enable_for_logged_in_users">
                        <input type="checkbox" name="prerender_enable_for_logged_in_users" value="true" <?php echo $enable_for_logged_in; ?>/>
                    </label>
                    <p><?php \_e( 'Keep this unchecked to have prevent logged in users from skewing the data. (Only the previous page and current page are recorded.)', 'pprh-pro' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php \_e( 'Number of days before automatically generated prerender hints are reset?', 'pprh-pro' ); ?></th>
                <td>
                    <label for="prerender_auto_reset_days">
                        <input type="number" step="1" min="0" max="180" name="prerender_auto_reset_days" value="<?php echo $auto_reset_days_option; ?>"/>
                    </label>
                    <p><?php \_e( 'Default is 30 days. This allows for the most accurate data to be used when creating prerender hints. To prevent any automatic resetting of prerender hints, set this option to 0 days.', 'pprh-pro' ); ?></p>
                </td>
            </tr>

			<?php if ( $this->show_posts_on_front ) { ?>
                <tr>
                    <th scope="row"><?php \_e( 'Reset all prerender hints on home page?', 'pprh-pro' ); ?></th>
                    <td>
                        <input type="submit" name="reset_home_prerender" class="button-primary pprh-reset" data-text="manually reset home prerender hints?" value="Reset"/>
                        <p><?php \_e( 'This will reset prerender hints on the home page, if sufficient data is available (see FAQ).', 'pprh-pro' ); ?></p>
                    </td>
                </tr>
			<?php } ?>

            <tr>
                <th scope="row"><?php \_e( 'Reset all prerender hints on all pages/posts?', 'pprh-pro' ); ?></th>
                <td>
                    <input type="submit" name="reset_global_prerender" class="button-primary pprh-reset" data-text="manually reset all prerender hints?" value="Reset"/>
                    <p><?php \_e( 'This will reset the prerender hint on each pages/posts, if sufficient data is available (see FAQ).', 'pprh-pro' ); ?></p>
                </td>
            </tr>

            </tbody></table>
		<?php
		return true;
	}

	private function get_post_types() {
		global $wp_post_types;
		$post_modal_types_option = \PPRH\Utils\Utils::get_json_option_value( 'pprh_options', 'post_modal_types' );
		$post_modal_types = Sanitize::clean_string_array( $post_modal_types_option );

		if ( ! is_array( $post_modal_types ) || empty( $post_modal_types ) ) {
			return false;
		}

		$post_types = '';

		foreach ( $wp_post_types as $post_type ) {
			$str = '';

			foreach ( $post_modal_types as $post ) {
				if ( $post === $post_type->name ) {
					$str = 'checked="checked"';
					break;
				}
			}

			$post_types .= '<input type="checkbox" ' . $str . ' name="post_modal_types[]" value="' . esc_html( $post_type->name ) . '"><span> ' . esc_html( $post_type->label ) . '</span><br/>';
		}

		return $post_types;
	}

	public function general_markup() {
		$selected = 'selected="selected"';
		$disable_wp_hints = $this->does_option_match( 'pprh_disable_wp_hints', 'true', 'checked' );
		$clear_dup_nonglobals = ( 'true' === $this->pro_options['clear_dup_nonglobals'] ) ? 'checked' : '';
		$dup_hint_percent     = Sanitize::strip_non_numbers( $this->pro_options['duplicate_hint_removal_percent'] ?? '65' );
		$post_types           = $this->get_post_types();
		$debug_enabled        = ( 'true' === \get_option( 'pprh_debug_enabled' ) ? 'checked' : '' );
		?>
        <table class="form-table">
            <tbody>

            <tr>
                <th scope="row"><?php esc_html_e( 'Disable automatically generated WordPress resource hints?', 'pre-party-browser-hints' ); ?></th>
                <td>
                    <label for="pprh_disable_wp_hints">
                        <input type="checkbox" name="pprh_disable_wp_hints" value="true" <?php echo $disable_wp_hints; ?>/>
                    </label>
                    <p><?php esc_html_e( 'This option will remove three resource hints automatically generated by WordPress, as of 4.8.2.', 'pre-party-browser-hints' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Send resource hints in HTML head or HTTP header?', 'pre-party-browser-hints' ); ?></th>
                <td>
                    <label for="pprh_html_head">
                        <select id="pprhHintLocation" name="pprh_html_head">
                            <option value="true" <?php echo $this->does_option_match( 'pprh_html_head', 'true', $selected ); ?>><?php esc_html_e( 'HTML &lt;head&gt;', 'pre-party-browser-hints' ); ?></option>
                            <option value="false" <?php echo $this->does_option_match( 'pprh_html_head', 'false', $selected ); ?>><?php esc_html_e( 'HTTP Header', 'pre-party-browser-hints' ); ?></option>
                        </select>
                    </label>
                    <p><?php esc_html_e( 'Send hints in the HTML &lt;head&gt; or the HTTP header.', 'pre-party-browser-hints' ); ?></p>
                </td>
            </tr>

<!--            <tr>-->
<!--                <th scope="row">--><?php //\_e( 'Automatically replace numerous duplicate post hints with one global hint?', 'pprh-pro' ); ?><!--</th>-->
<!--                <td>-->
<!--                    <label for="clear_dup_nonglobals">-->
<!--                        <input type="checkbox" name="clear_dup_nonglobals" value="true" --><?php //echo $clear_dup_nonglobals; ?>
<!--                    </label>-->
<!--                    <p>--><?php //\_e( 'If you have numerous identical post hints, these can be replaced with a single global hint. For example, if you have 50 posts, each with the same hint, the next time you add another post hint, a global hint will be created instead. This replaces 50 hints with one hint. This mechanism is triggered when a new post hint is created, and 65% of all posts have the same hint.', 'pprh-pro' ); ?>
<!--            </p>  -->
<!--                </td>-->
<!--            </tr>-->

<!--            <tr>-->
<!--                <th scope="row">--><?php //\_e( 'Percent of duplicate post hints to active posts/pages required to create a global hint?', 'pprh-pro' ); ?><!--</th>-->
<!--                <td>-->
<!--                    <label for="duplicate_hint_removal_percent">-->
<!--                        <input type="number" min="1" max="100" step="1" name="duplicate_hint_removal_percent" value="--><?php //echo $dup_hint_percent; ?><!--">-->
<!--                    </label>-->
<!--                    <span>%</span>-->
<!--                    <p>--><?php //\_e( 'For example, if a site has 100 posts, and 65 of those posts have the same resource hint used, a percent of 65% causes those 65 hints to be deleted and replaced by a single, "global" hint, which is used on all posts/pages. Default is 65%. (If the option above is disabled, this option has no effect.)', 'pprh-pro' ); ?><!--</p>-->
<!--                </td>-->
<!--            </tr>-->

            <tr>
                <th scope="row"><?php \_e( 'Allow Pre* Party post modal to be shown on these post types:', 'pprh-pro' ); ?></th>
                <td>
					<?php echo $post_types; ?>
                    <p><?php \_e( '(Check the boxes of the post types you would like the modal window to appear.)', 'pprh-pro' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php \_e( 'Enable debug logger?', 'pprh-pro' ); ?></th>
                <td>
                    <label for="debug_enabled">
                        <input type="checkbox" name="debug_enabled" value="true" <?php echo $debug_enabled; ?>>
                    </label>
                    <p><?php \_e( 'This will allow the details of any errors encountered to be saved to the database, emailed to support, then deleted.', 'pprh-pro' ); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
		<?php
	}

	public function preconnect_markup() {
		$autoload = $this->does_option_match( 'pprh_preconnect_autoload', 'true', 'checked' );
        $allow_unauth = $this->does_option_match( 'pprh_preconnect_allow_unauth', 'true', 'checked' );
		?>
        <table class="form-table">
            <tbody>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Enable Auto Preconnect?', 'pre-party-browser-hints' ); ?></th>
                    <td>
                        <label for="pprh_preconnect_autoload">
                            <input type="checkbox" name="pprh_preconnect_autoload" value="true" <?php echo $autoload; ?>/>
                        </label>
                        <p><?php esc_html_e( 'This feature allows preconnect hints to be automatically created. JavaScript, CSS, and images loaded from external domains will preconnect automatically.', 'pre-party-browser-hints' ); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Allow unauthenticated users to automatically set preconnect hints via Ajax?', 'pre-party-browser-hints' ); ?></th>
                    <td>
                        <label for="pprh_preconnect_allow_unauth">
                            <input type="checkbox" name="pprh_preconnect_allow_unauth" value="true" <?php echo $allow_unauth; ?>/>
                        </label>
                        <p><?php esc_html_e( 'This plugin has a feature which allows preconnect hints to be automatically created asynchronously in the background with Ajax by the first user to visit a page (assuming the user has that option to be reset). There is an extremely remote possibility that if a visitor knew the hints would be set, they could choose to manually load many external scripts, which could trick the plugin script into accepting these as valid preconnect hints. But again this is a very remote possiblity and only a nuisance, not a vulnerability, due to the strict sanitization procedures in place.', 'pre-party-browser-hints' ); ?></p>
                    </td>
                </tr>

        <?php if ( $this->show_posts_on_front ) { ?>
                <tr>
                    <th scope="row"><?php \_e( 'Reset Home Preconnect Links?', 'pprh-pro' ); ?></th>
                    <td>
                        <input type="submit" name="reset_home_preconnect" class="button-primary pprh-reset" data-text="reset auto preconnect hints used only on the home page?" value="Reset">
                        <p><?php \_e( 'This will reset automatically created preconnect hints on the home page. (This option only applies when the home page is set to display recent posts.)', 'pprh-pro' ); ?></p>
                    </td>
                </tr>
        <?php } ?>

                <tr>
                    <th scope="row"><?php \_e( 'Reset Global Preconnect Links?', 'pprh-pro' ); ?></th>
                    <td>
                        <input type="submit" name="reset_global_preconnect" class="button-primary pprh-reset" data-text="reset auto preconnects globally?" value="Reset">
                        <?php \_e( '<p>This will reset all of the automatically generated global preconnect hints, which are used on all posts and pages.</p>', 'pprh-pro' ); ?>
                    </td>
                </tr>

<!--            <tr>-->
<!--                <th scope="row">--><?php //esc_html_e( 'Reset automatically created preconnect links?', 'pre-party-browser-hints' ); ?><!--</th>-->
<!--                <td>-->
<!--                    <input type="submit" name="pprh_preconnect_set" class="pprh-reset button-primary" data-text="reset auto-preconnect hints?" value="Reset">-->
<!--                    <p>--><?php //esc_html_e( 'This will reset automatically created preconnect hints, allowing new preconnect hints to be generated when your front end is loaded.', 'pre-party-browser-hints' ); ?><!--</p>-->
<!--                </td>-->
<!--            </tr>-->

            </tbody>
        </table>
		<?php
	}

	public function prefetch_markup() {
		$prefetch_enabled                 = $this->does_option_match( 'pprh_prefetch_enabled', 'true', 'checked' );
		$prefetch_disableForLoggedInUsers = $this->does_option_match( 'pprh_prefetch_disableForLoggedInUsers', 'true', 'checked' );
		$prefetch_initialization_delay    = $this->esc_get_option( 'pprh_prefetch_delay' );
		$ignore_keywords                  = implode( ', ', \get_option( 'pprh_prefetch_ignoreKeywords', array() ) );
		$prefetch_max_prefetches          = $this->esc_get_option( 'pprh_prefetch_max_prefetches' );
		?>
        <table class="form-table"><tbody>

            <tr>
                <th scope="row"><?php esc_html_e( 'Enable Auto Prefetch?', 'pre-party-browser-hints' ); ?></th>
                <td>
                    <label for="pprh_prefetch_enabled">
                        <input type="checkbox" name="pprh_prefetch_enabled" class="toggleMetaBox" value="true" <?php echo $prefetch_enabled; ?>/>
                    </label>
                    <p><?php esc_html_e( 'This allows for navigation links to be automatically prefetched while in viewport. When navigation (anchor) links are being moused over, this feature will initiate a prefetch request for the URL in the link. Select No (default) to disable this feature.', 'pre-party-browser-hints' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Disable prefetching for logged in users?', 'pre-party-browser-hints' ); ?></th>
                <td>
                    <label for="pprh_prefetch_disableForLoggedInUsers">
                        <input type="checkbox" name="pprh_prefetch_disableForLoggedInUsers" value="true" <?php echo $prefetch_disableForLoggedInUsers; ?>/>
                    </label>
                    <p><?php esc_html_e( 'It usually is not necessary for logged in users to prefetch content. This will save some server resources.', 'pre-party-browser-hints' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Prefetch initiation delay (seconds):', 'pre-party-browser-hints' ); ?></th>
                <td>
                    <label for="pprh_prefetch_delay">
                        <input type="number" step="1" min="0" max="30" name="pprh_prefetch_delay" value="<?php echo $prefetch_initialization_delay; ?>">
                    </label>
                    <p><?php esc_html_e( 'Start prefetching after a short delay. Will be started when the browser becomes idle. Default is 0 seconds.', 'pre-party-browser-hints' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Ignore these keywords:', 'pre-party-browser-hints' ); ?></th>
                <td>
                    <label for="pprh_prefetch_ignoreKeywords">
                        <textarea name="pprh_prefetch_ignoreKeywords" rows="6"><?php echo $this->get_each_keyword( $ignore_keywords ); ?></textarea>
                    </label>
                    <p><?php esc_html_e( 'A list of keywords to ignore from prefetching. One keyword per line. You may use an astericks (*) after a keyword (ex: "/products*") to act as a wildcard, preventing links with that value from being prefetched.', 'pre-party-browser-hints'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Maximum requests per second:', 'pre-party-browser-hints' ); ?></th>
                <td>
                    <label for="pprh_prefetch_maxRPS">
                        <input type="number" step="1" min="1" max="100" name="pprh_prefetch_maxRPS" value="<?php echo $this->esc_get_option( 'pprh_prefetch_maxRPS' ); ?>">
                    </label>
                    <p><?php esc_html_e( 'Number of prefetch hints that can be created per second. Default is 3 per second.', 'pre-party-browser-hints' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Delay in prefetching links on mouse hover (milliseconds)', 'pre-party-browser-hints' ); ?></th>
                <td>
                    <label for="pprh_prefetch_hoverDelay">
                        <input type="number" min="0" max="1000" step="1" name="pprh_prefetch_hoverDelay" value="<?php echo $this->esc_get_option( 'pprh_prefetch_hoverDelay' ); ?>">
                    </label>
                    <p><?php esc_html_e( 'Set a short pause after the mouse hovers over a link before the prefetch hint is created. Default is 50 milliseconds.', 'pre-party-browser-hints' ); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php esc_html_e( 'Maximum number of prefetch hints loaded? (Excluding mouse hover or touch events)', 'pre-party-browser-hints' ); ?></th>
                <td>
                    <label for="pprh_prefetch_max_prefetches">
                        <input type="number" step="1" min="1" max="100" name="pprh_prefetch_max_prefetches" value="<?php echo $prefetch_max_prefetches; ?>">
                    </label>
                    <p><?php esc_html_e( 'Set the maximum number of prefetch hints you would like to be loaded. This can save some server resources. Default is 10.', 'pre-party-browser-hints' ); ?></p>
                </td>
            </tr>

            </tbody></table>
		<?php
		return true;
	}


	private function get_each_keyword( $keywords ) {
		if ( is_null( $keywords ) ) {
			return '';
		}

		$keywords = explode( ', ', $keywords );
		$str   = '';
		$count = count( $keywords );
		$idx   = 0;

		foreach ( $keywords as $keyword ) {
			$idx++;
			$str .= $keyword;

			if ( $idx < $count ) {
				$str .= "\n";
			}
		}

		return $str;
	}

	private function esc_get_option( string $option ) {
		return \esc_html( \get_option( $option ) );
	}

	private function does_option_match( string $option, string $match, string $output ) {
		$option_value = $this->esc_get_option( $option );
		return ( ( $option_value === $match ) ? $output : '' );
	}

}
