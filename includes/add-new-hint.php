<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Add_New_Hint {

	public function __construct() {
        $this->create_new_hint_table();
    }

	public function create_new_hint_table() {
		if ( ! is_admin() ) {
			exit();
		}
		?>

		<div class="pprh-container">
            <form id="pprh-new-hint" method="post" action="<?php echo admin_url('admin.php?page=pprh-plugin-settings'); ?>">
                <?php wp_nonce_field( 'pprh_nonce_action', 'pprh_nonce_val' ); ?>
                <table id="pprh-enter-data" class="fixed widefat striped">

				<thead>
					<tr>
						<th colspan="5" style="text-align: center; font-size: 23px; font-weight: 600; padding: 15px 0;"><?php esc_html_e( 'Add New Resource Hint', 'pre-party-browser-hints' ); ?></th>
					</tr>
				</thead>

				<tbody>

					<?php
					$this->enter_url();
					$this->show_pp_radio_options();
					$this->set_attrs();
					?>

				</tbody>

				<tfoot>
					<tr>
						<th style="text-align: center; padding: 20px 0;" colspan="5">
							<input id="pprhSubmitHints" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Insert Resource Hint', 'pre-party-browser-hints' ); ?>" />
						</th>
					</tr>
				</tfoot>

			</table>
                <input type="hidden" name="pprh_data" id="pprhInsertedHints" value=""/>

            </form>
		</div>

		<?php
	}

	protected function enter_url() {
		?>
		<tr>
			<td colspan="1">
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Enter a domain name for dns-prefetch and preconnect hints, otherwise enter a full URL.', 'pre-party-browser-hints' ); ?></span>
				</span>
				<?php esc_html_e( 'Domain or URL:', 'pre-party-browser-hints' ); ?>
			</td>
			<td colspan="4">
				<input id="pprhURL" placeholder="<?php esc_attr_e( 'Enter valid domain or URL here...', 'pre-party-browser-hints' ); ?>" class="widefat" name="url" />
			</td>
		</tr>
		<?php
	}

	protected function show_pp_radio_options() {
		?>
		<tr>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert domain names from external URL\'s to perform DNS resolution early.', 'pre-party-browser-hints' ); ?></span>
					</span>
					<span><?php esc_html_e( 'DNS-Prefetch' ); ?></span>
					<input name="hint_type" type="radio" value="dns-prefetch"/>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a resource that is likely to be needed on a page later.', 'pre-party-browser-hints' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Prefetch' ); ?></span>
					<input name="hint_type" type="radio" value="prefetch"/>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a page/post your visitors are likely to navigate towards.', 'pre-party-browser-hints' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Prerender' ); ?></span>
					<input name="hint_type" type="radio" value="prerender"/>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert domain names from external URL\'s to perform DNS resolution, initial connection, and SSL negotiation ahead of time.', 'pre-party-browser-hints' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Preconnect' ); ?></span>
					<input name="hint_type" type="radio" value="preconnect"/>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a resource that is needed on a current page.', 'pre-party-browser-hints' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Preload' ); ?></span>
					<input name="hint_type" type="radio" value="preload"/>
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
					<span><?php _e( 'For various reasons, font files (and others) need to be loaded with crossorigin. <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#Cross-origin_fetches">Source: Mozilla</a>', 'pre-party-browser-hints' ); ?></span>
				</span>
				<?php esc_html_e( 'Crossorigin?', 'pre-party-browser-hints' ); ?>
				<input value="crossorigin" id="pprhCrossorigin" type="checkbox" class="widefat" name="crossorigin"/>
			</td>

			<td colspan="2" style="text-align: right; padding-right: 40px;">
				<span class="pprh-help-tip-hint">
					<span>
						<?php _e( "Setting this attribute allows the browser to more accurately: <br/> 1) prioritize resource loading <br/>2) store in browser cache <br/>3) apply the correct headers. <a href='https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#The_basics'>Source: Mozilla</a>", 'pre-party-browser-hints' ); ?>
					</span>
				</span>
				<?php esc_html_e( 'as ', 'pre-party-browser-hints' ); ?>
				<select id="pprhAsAttr" name="as_attr">
					<option></option>
					<option value="font">font</option>
					<option value="script">script</option>
					<option value="style">style</option>
					<option value="audio">audio</option>
					<option value="video">video</option>
					<option value="image">image</option>
					<option value="track">track</option>
					<option value="embed">embed</option>
				</select>
			</td>

			<td colspan="2">
				<span class="pprh-help-tip-hint">
					<span><?php _e( '&lt;link&gt; elements can accept a type attribute, which contains the MIME type of the resource the element points to. This is especially useful when preloading resources — the browser will use the type attribute value to work out if it supports that resource, and will only download it if so, ignoring it if not. <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#Including_a_MIME_type">Source: Mozilla</a>. (This attribute will attempt to be added automatically.)', 'pre-party-browser-hints' ); ?></span>
				</span>
				<?php esc_html_e( 'type ', 'pre-party-browser-hints' ); ?>
				<select id="pprhTypeAttr" name="type_attr">
					<option></option>
					<option value="font/woff">font/woff</option>
					<option value="font/woff2">font/woff2</option>
					<option value="font/ttf">font/ttf</option>
					<option value="font/eot">font/eot</option>
				</select>
			</td>

		</tr>
		<?php
	}
}
