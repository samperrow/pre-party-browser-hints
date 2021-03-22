<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NewHint {

	public function create_new_hint_table() {
		?>

        <div class="pprh-container">
            <table id="pprh-enter-data" class="fixed widefat striped" aria-label="Add a new resource hint">

                <thead>
                    <tr>
                        <th colspan="5" scope="colgroup"><?php esc_html_e( 'Add New Resource Hint', 'pprh' ); ?></th>
                    </tr>
                </thead>

                <tbody>
				    <?php $this->insert_table_body(); ?>
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="2"><?php do_action( 'pprh_nh_reset_post_preconnects' ); ?></td>
                        <td colspan="1">
                            <input id="pprhSubmitHints" type="button" class="button button-primary" value="<?php esc_attr_e( 'Insert Resource Hint', 'pprh' ); ?>" />
                        </td>
                        <td colspan="2"><?php do_action( 'pprh_nh_reset_post_prerenders' ); ?></td>
                    </tr>
                </tfoot>

            </table>
        </div>

        <?php
	}

	public function insert_table_body() {
		$this->enter_url();
		$this->show_pp_radio_options();
		$this->set_attrs();
		$this->set_media_attr();
		do_action( 'pprh_nh_get_home_page_options' );
	}

	protected function enter_url() {
		?>
        <tr>
            <td colspan="1">
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Enter a domain name for dns-prefetch and preconnect hints, otherwise enter a full URL.', 'pprh' ); ?></span>
				</span>
				<?php esc_html_e( 'Domain or URL:', 'pprh' ); ?>
            </td>
            <td colspan="4">
                <label>
                    <input class="widefat pprh_url" placeholder="<?php esc_attr_e( 'Enter valid domain or URL here...', 'pprh' ); ?>" name="url"/>
                </label>
            </td>
        </tr>
		<?php
	}

	protected function show_pp_radio_options() {
		?>
        <tr class="pprhHintTypes">

            <td>
                <div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert domain names from external URL\'s to perform DNS resolution early.', 'pprh' ); ?></span>
					</span>
                    <span><?php esc_html_e( 'DNS-Prefetch' ); ?></span>
                    <label>
                        <input name="hint_type" type="radio" value="dns-prefetch"/>
                    </label>
                </div>
            </td>

            <td>
                <div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a resource that is likely to be needed on a page later.', 'pprh' ); ?></span>
					</span>
                    <span><?php esc_html_e( 'Prefetch' ); ?></span>
                    <label>
                        <input name="hint_type" type="radio" value="prefetch"/>
                    </label>
                </div>
            </td>

            <td>
                <div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a page/post your visitors are likely to navigate towards.', 'pprh' ); ?></span>
					</span>
                    <span><?php esc_html_e( 'Prerender' ); ?></span>
                    <label>
                        <input name="hint_type" type="radio" value="prerender"/>
                    </label>
                </div>
            </td>

            <td>
                <div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert domain names from external URL\'s to perform DNS resolution, initial connection, and SSL negotiation ahead of time.', 'pprh' ); ?></span>
					</span>
                    <span><?php esc_html_e( 'Preconnect' ); ?></span>
                    <label>
                        <input name="hint_type" type="radio" value="preconnect"/>
                    </label>
                </div>
            </td>

            <td>
                <div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a resource that is needed on a current page.', 'pprh' ); ?></span>
					</span>
                    <span><?php esc_html_e( 'Preload' ); ?></span>
                    <label>
                        <input name="hint_type" type="radio" value="preload"/>
                    </label>
                </div>
            </td>


        </tr>

		<?php
	}

	protected function set_attrs() {
		?>
        <tr>

            <td colspan="1">
				<span class="pprh-help-tip-hint">
					<span><?php _e( 'For various reasons, font files (and others) need to be loaded with the crossorigin attribute. Crossorigin only applies to preconnect hints!<a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#Cross-origin_fetches">Source: Mozilla</a>',
                            'pprh' ); ?></span>
				</span>
                <label><?php esc_html_e( 'Crossorigin?', 'pprh' ); ?>
                    <input class="widefat pprh_crossorigin" value="crossorigin" type="checkbox" name="crossorigin" disabled="true"/>
                </label>
            </td>

            <td colspan="2" style="text-align: right; padding-right: 40px;">
				<span class="pprh-help-tip-hint">
					<span>
						<?php _e( "Setting this attribute allows the browser to more accurately: <br/> 1) prioritize resource loading <br/>2) store in browser cache <br/>3) apply the correct headers. <a href='https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#The_basics'>Source: Mozilla</a>", 'pprh' ); ?>
					</span>
				</span>
                <span><?php esc_html_e( 'as ', 'pprh' ); ?></span>
                <label>
                    <select class="pprh_as_attr" name="as_attr">
                        <option selected label=" "></option>
                        <option value="audio">audio</option>
                        <option value="document">document</option>
                        <option value="embed">embed</option>
                        <option value="fetch">fetch</option>
                        <option value="image">image</option>
                        <option value="font">font</option>
                        <option value="object">object</option>
                        <option value="script">script</option>
                        <option value="style">style</option>
                        <option value="track">track</option>
                        <option value="video">video</option>
                        <option value="worker">worker</option>
                    </select>
                </label>
            </td>

            <td colspan="2">
				<span class="pprh-help-tip-hint">
					<span><?php _e( '&lt;link&gt; elements can accept a type attribute, which contains the MIME type of the resource the element points to. This is especially useful when preloading resources — the browser will use the type attribute value to work out if it supports that resource, and will only download it if so, ignoring it if not. <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#Including_a_MIME_type">Source: Mozilla</a>. (This attribute will attempt to be added automatically.)', 'pprh' ); ?></span>
				</span>
                <span><?php esc_html_e( 'type ', 'pprh' ); ?></span>
                <label>
                    <select class="pprh_type_attr" name="type_attr">
                        <option selected label=" "></option>
                        <option value="text/css">text/css</option>
                        <option value="text/html">text/html</option>
                        <option value="font/eot">font/eot</option>
                        <option value="font/ttf">font/ttf</option>
                        <option value="font/woff">font/woff</option>
                        <option value="font/woff2">font/woff2</option>
                    </select>
                </label>
            </td>

        </tr>
		<?php
	}

	protected function set_media_attr() {
	    ?>
        <tr>

            <td colspan="1">
				<p><?php esc_html_e( 'Media Attribute', 'pprh' ); ?>
                    <span class="pprh-help-tip-hint">
                        <span><?php _e( 'Specify the media attribute. This can be used to allow hints to only load on certain devices, screen dimensions, and more. <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries">https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries</a>', 'pprh' ); ?></span>
                    </span>
                </p>
            </td>

            <td colspan="4">
                <input placeholder="" class="widefat pprh_media" value="" type="text" name="media" disabled="true"/>
            </td>

        </tr>
	    <?php
    }
}
