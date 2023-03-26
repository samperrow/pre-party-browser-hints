<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NewHint {

    private $hint_url;
    private $hint_type;
    private $hint_id;
    private $xorigin;
    private $as_attr;
    private $mime_type_attr;
    private $media;

    private $on_plugin_page;

    public function __construct( bool $on_plugin_page, array $hint = array() ) {
        $this->hint_url       = $hint['url'] ?? '';
		$this->hint_type      = $hint['hint_type'] ?? '';
		$this->hint_id        = $hint['id'] ?? '';
		$this->xorigin        = $hint['crossorigin'] ?? '';
		$this->as_attr        = $hint['as_attr'] ?? '';
		$this->media          = $hint['media'] ?? '';
		$this->mime_type_attr = $hint['type_attr'] ?? '';
        $this->on_plugin_page = $on_plugin_page;
	}

	public function create_new_hint_table() {
		?>
		<div class="pprh-container">
            <form method="post" action="">
            <table id="pprh-enter-data" class="fixed widefat striped text-center" aria-label="Add a new resource hint">

				<thead>
					<tr>
						<th colspan="5" scope="row"><?php esc_html_e( 'Add New Resource Hint', 'pre-party-browser-hints' ); ?></th>
					</tr>
				</thead>

				<tbody>
                    <?php
                        $this->insert_hint_table();
//                        $this->newhint_get_content( 1 );

                        if ( $this->on_plugin_page ) { ?>
                            <tr class="text-center">
                                <td colspan="5">
                                    <span class="pprh-help-tip-hint">
                                        <span><?php esc_html_e( 'If checked, this resource hint will only be used on the home page, which is set to display recent posts.', 'pprh' ); ?></span>
                                    </span>
                                    <span><?php esc_html_e( 'Use this resource hint only on the home page?' ); ?></span>
                                    <input class="pprh_home pprhHomePostHints" name="UseOnHomePostsOnly" type="checkbox" value="HomePostHints"/>
                                </td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="5">
                                    <div style="display: flex; flex-direction: row; justify-content: space-around;">

                                        <div>
                                            <label for="reset_post_prerender">
                                                <input name="reset_post_prerender" id="resetPostPrerender" type="button" class="button button-secondary text-center" value="<?php esc_html_e( 'Reset Post Prerender', 'pprh' ); ?>">
                                            </label>
                                            <span class="pprh-help-tip-hint">
                                                <span><?php esc_html_e( 'This will reset this post\'s automatically configured prerender hint and replace it using the latest analytics data.', 'pprh' ); ?></span>
                                            </span>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="5" class="text-center">
                            <input id="pprhSubmitHints" type="button" class="button button-primary" value="<?php esc_attr_e( 'Insert Resource Hint', 'pre-party-browser-hints' ); ?>" />
                        </td>
                    </tr>
                </tfoot>

			</table>
            </form>
        </div>
		<?php
	}

	public function insert_hint_table() {
		$this->enter_url();
		$this->show_pp_radio_options();
		$this->set_attrs();
		$this->set_media_attr();
	}

	private function enter_url() { ?>
		<tr>
			<td colspan="1">
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Enter a domain name for dns-prefetch and preconnect hints, otherwise enter a full URL.', 'pre-party-browser-hints' ); ?></span>
				</span>
				<?php esc_html_e( 'Domain or URL:', 'pre-party-browser-hints' ); ?>
			</td>
			<td colspan="4">
				<label>
					<input class="widefat pprh_url" value="<?php echo $this->hint_url; ?>" placeholder="<?php esc_attr_e( 'Enter valid domain or URL here...', 'pre-party-browser-hints' ); ?>" name="url"/>
				</label>
			</td>
		</tr>
		<?php
	}

	private function is_checked( string $str, string $value ) {
		if ( $str === $value ) {
			echo 'checked="checked"';
		}
	}

	private function show_pp_radio_options() {
		$name = 'hint_type-' . $this->hint_id;
		?>
		<tr class="pprhHintTypes">

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert domain names from external URL\'s to perform DNS resolution early.', 'pre-party-browser-hints' ); ?></span>
					</span>
					<span><?php esc_html_e( 'DNS-Prefetch', 'pre-party-browser-hints' ); ?></span>
                    <input name="<?php echo $name; ?>" class="hint_type dns-prefetch" type="radio" value="dns-prefetch" <?php $this->is_checked( 'dns-prefetch', $this->hint_type ); ?>/>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a resource that is likely to be needed on a page later.', 'pre-party-browser-hints' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Prefetch', 'pre-party-browser-hints' ); ?></span>
                    <input name="<?php echo $name; ?>" class="hint_type prefetch" type="radio" value="prefetch" <?php $this->is_checked( 'prefetch', $this->hint_type ); ?>/>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a page/post your visitors are likely to navigate towards.', 'pre-party-browser-hints' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Prerender', 'pre-party-browser-hints' ); ?></span>
                    <input name="<?php echo $name; ?>" class="hint_type prerender" type="radio" value="prerender" <?php $this->is_checked( 'prerender', $this->hint_type ); ?>/>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert domain names from external URL\'s to perform DNS resolution, initial connection, and SSL negotiation ahead of time.', 'pre-party-browser-hints' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Preconnect', 'pre-party-browser-hints' ); ?></span>
                    <input name="<?php echo $name ?>" class="hint_type preconnect" type="radio" value="preconnect" <?php $this->is_checked( 'preconnect', $this->hint_type ); ?>/>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a resource that is needed on a current page.', 'pre-party-browser-hints' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Preload', 'pre-party-browser-hints' ); ?></span>
                    <input name="<?php echo $name; ?>" class="hint_type preload" type="radio" value="preload" <?php $this->is_checked( 'preload', $this->hint_type ); ?>/>
				</div>
			</td>

		</tr>

		<?php
	}


	private function get_as_attrs( string $selected_value, string $attr ) {
		if ( 'as' === $attr ) {
			$values = array( 'audio', 'document', 'embed', 'fetch', 'image', 'font', 'object', 'script', 'style', 'track', 'video', 'worker' );
		} else {
			$values = array( 'text/css', 'text/html', 'font/eot', 'font/ttf', 'font/woff', 'font/woff2' );
		}

		foreach ( $values as $index => $value ) {
			$selected = ( $selected_value === $value ) ? 'selected' : '';

			if ( 0 === $index ) {
				echo "<option label=' ' value=''></option>";
			}

			echo "<option $selected value='$value'>$value</option>";
		}
	}

	private function set_attrs() { ?>
		<tr>

            <td colspan="1">
                <span class="pprh-help-tip-hint">
                    <span><?php _e( 'Crossorigin applies to preconnect and preload hints only. For various reasons, font files (and others) need to be loaded with the crossorigin attribute.<a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#Cross-origin_fetches">Source: Mozilla</a>', 'pre-party-browser-hints' ); ?></span>
                </span>
                <span><?php esc_html_e( 'Crossorigin?', 'pre-party-browser-hints' ); ?></span>
                <input class="widefat pprh_crossorigin" value="crossorigin" type="checkbox" name="crossorigin" <?php $this->is_checked( $this->xorigin, 'crossorigin' ); ?>/>
            </td>

            <td colspan="2" style="text-align: right; padding-right: 40px;">
                <span class="pprh-help-tip-hint">
                    <span><?php _e( "Setting this attribute allows the browser to more accurately: <br/> 1) prioritize resource loading <br/>2) store in browser cache <br/>3) apply the correct headers. <a href='https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#The_basics'>Source: Mozilla</a>", 'pre-party-browser-hints' ); ?></span>
                </span>
                <span><?php esc_html_e( 'as:', 'pre-party-browser-hints' ); ?></span>
                <select class="pprh_as_attr" name="as_attr">
                    <?php $this->get_as_attrs( $this->as_attr, 'as' ); ?>
                </select>
            </td>

            <td colspan="2">
                <span class="pprh-help-tip-hint"><span><?php _e( '&lt;link&gt; elements can accept a type attribute, which contains the MIME type of the resource the element points to. This is especially useful when preloading resources — the browser will use the type attribute value to work out if it supports that resource, and will only download it if so, ignoring it if not. <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#Including_a_MIME_type">Source: Mozilla</a>. (This attribute will attempt to be added automatically.)', 'pre-party-browser-hints' ); ?></span></span>
                <span><?php esc_html_e( 'Mime Type:', 'pre-party-browser-hints' ); ?></span>
                <select class="pprh_type_attr" name="type_attr">
                    <?php $this->get_as_attrs( $this->mime_type_attr, 'type' ); ?>
                </select>
            </td>

		</tr>
		<?php
	}

	private function set_media_attr() { ?>
		<tr>
			<td colspan="1">
                <span class="pprh-help-tip-hint"><span><?php _e( 'This applies only to the preload resource hint. This can be used to allow hints to only load on certain devices, screen dimensions, and more. <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries">https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries</a>', 'pre-party-browser-hints' ); ?></span></span>
				<span><?php esc_html_e( 'Media Attribute', 'pre-party-browser-hints' ); ?></span>
			</td>
			<td colspan="4">
                <input placeholder="" class="widefat pprh_media" type="text" name="media" value="<?php echo $this->media; ?>"/>
			</td>
		</tr>
		<?php
	}


	public function newhint_get_content( int $plugin_page ) {
		if ( 1 === $plugin_page ) { ?>
            <tr class="text-center">
                <td colspan="5">
                    <span class="pprh-help-tip-hint">
                        <span><?php esc_html_e( 'If checked, this resource hint will only be used on the home page, which is set to display recent posts.', 'pprh' ); ?></span>
                    </span>
                    <span><?php esc_html_e( 'Use this resource hint only on the home page?' ); ?></span>
                    <input class="pprh_home pprhHomePostHints" name="UseOnHomePostsOnly" type="checkbox" value="HomePostHints"/>
                </td>
            </tr>
		<?php } elseif ( 2 === $plugin_page ) { ?>
            <tr>
                <td colspan="5">
                    <div style="display: flex; flex-direction: row; justify-content: space-around;">

                        <div>
                            <label for="reset_post_prerender">
                                <input name="reset_post_prerender" id="resetPostPrerender" type="button" class="button button-secondary text-center" value="<?php esc_html_e( 'Reset Post Prerender', 'pprh' ); ?>">
                            </label>
                            <span class="pprh-help-tip-hint">
                                <span><?php esc_html_e( 'This will reset this post\'s automatically configured prerender hint and replace it using the latest analytics data.', 'pprh' ); ?></span>
                            </span>
                        </div>

                    </div>
                </td>
            </tr>

			<?php
			return $plugin_page;
		}
	}

}
