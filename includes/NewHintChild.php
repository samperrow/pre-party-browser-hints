<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NewHintChild extends \PPRH\NewHint {

	public $plugin_page;

	public function __construct( int $plugin_page ) {
//		parent::__construct( array() );
		$this->plugin_page = $plugin_page;
		\add_filter( 'pprh_newhint_get_content', array( $this, 'newhint_get_content' ), 10, 1 );
	}


	public function newhint_get_content() {
		if ( 1 === $this->plugin_page ) { ?>
            <tr class="text-center">
                <td colspan="5">
                    <span class="pprh-help-tip-hint">
                        <span><?php esc_html_e( 'If checked, this resource hint will only be used on the home page, which is set to display recent posts.', 'pprh-pro' ); ?></span>
                    </span>
                    <span><?php esc_html_e( 'Use this resource hint only on the home page?' ); ?></span>
                    <label for="UseOnHomePostsOnly"><input class="pprh_home pprhHomePostHints" name="UseOnHomePostsOnly" type="checkbox" value="HomePostHints"/></label>
                </td>
            </tr>
		<?php } elseif ( 2 === $this->plugin_page ) { ?>
            <tr>
                <td colspan="5">
                    <div style="display: flex; flex-direction: row; justify-content: space-around;">

                        <div>
                            <label for="reset_post_preconnect">
                                <input name="reset_post_preconnect" id="resetPostPreconnect" type="button" class="button button-secondary" value="<?php esc_html_e('Reset Post Preconnects', 'pprh-pro' ); ?>">
                            </label>
                            <span class="pprh-help-tip-hint">
                            <span><?php esc_html_e( 'This will gather fresh data from the PageSpeed Insights API, and use the information to generate fresh preconnect hints for this post.', 'pprh-pro' ); ?></span>
                        </span>
                        </div>

                        <div>
                            <label for="reset_post_preload">
                                <input name="reset_post_preload" id="resetPostPreload" type="button" class="button button-secondary" value="<?php esc_html_e('Reset Post Preloads', 'pprh-pro' ); ?>">
                            </label>
                            <span class="pprh-help-tip-hint">
                                <span><?php esc_html_e( 'This will gather fresh data from the PageSpeed Insights API, and use the information to generate fresh preload hints for this post.', 'pprh-pro' ); ?></span>
                            </span>
                        </div>

                        <div>
                            <label for="reset_post_prerender">
                                <input name="reset_post_prerender" id="resetPostPrerender" type="button" class="button button-secondary text-center" value="<?php esc_html_e( 'Reset Post Prerender', 'pprh-pro' ); ?>">
                            </label>
                            <span class="pprh-help-tip-hint">
                                <span><?php esc_html_e( 'This will reset this post\'s automatically configured prerender hint and replace it using the latest analytics data.', 'pprh-pro' ); ?></span>
                            </span>
                        </div>

                    </div>
                </td>
            </tr>

        <?php
            return $this->plugin_page;

		}
	}

}
