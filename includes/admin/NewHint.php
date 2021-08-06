<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NewHint {

	public function create_new_hint_table() {
		?>
		<div class="pprh-container">
			<table id="pprh-enter-data" class="fixed widefat striped text-center" aria-label="Add a new resource hint">

				<thead>
					<tr>
						<th colspan="5" scope="colgroup"><?php esc_html_e( 'Add New Resource Hint', 'pprh' ); ?></th>
					</tr>
				</thead>

				<tbody>
					<?php $this->insert_hint_table( array() ); ?>
				</tbody>

				<tfoot>
					<tr>
						<td colspan="2" class="text-center"><?php \apply_filters( 'pprh_newhint_get_content', 2, 'post_preconnects_output' ); ?></td>
						<td colspan="1">
							<input id="pprhSubmitHints" type="button" class="button button-primary" value="<?php esc_attr_e( 'Insert Resource Hint', 'pprh' ); ?>" />
						</td>
						<td colspan="2" class="text-center"><?php \apply_filters( 'pprh_newhint_get_content', 2, 'post_prerenders_output' ); ?></td>
					</tr>
				</tfoot>

			</table>
		</div>
		<?php
	}

	public function insert_hint_table( array $hint = array() ) {
		echo '<form method="post">';
		$url       = $hint['url'] ?? '';
		$hint_type = $hint['hint_type'] ?? '';
		$xorigin   = $hint['crossorigin'] ?? '';
		$hint_id   = $hint['id'] ?? '';
		$type_attr = $hint['type_attr'] ?? '';
		$as_attr   = $hint['as_attr'] ?? '';
		$media     = $hint['media'] ?? '';

		$this->enter_url( $url );
		$this->show_pp_radio_options( $hint_type, $hint_id );
		$this->set_attrs( $hint_type, $xorigin, $as_attr, $type_attr );
		$this->set_media_attr( $hint_type, $media );
		\apply_filters( 'pprh_newhint_get_content', 1, 'home_page_output' );
		echo '</form>';
	}

	protected function enter_url( string $hint_url ) {
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
					<input class="widefat pprh_url" value="<?php echo $hint_url; ?>" placeholder="<?php esc_attr_e( 'Enter valid domain or URL here...', 'pprh' ); ?>" name="url"/>
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

	protected function show_pp_radio_options( string $hint_type, string $hint_id ) {
		$name = 'hint_type-' . $hint_id;
		?>
		<tr class="pprhHintTypes">

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert domain names from external URL\'s to perform DNS resolution early.', 'pprh' ); ?></span>
					</span>
					<span><?php esc_html_e( 'DNS-Prefetch' ); ?></span>
					<label for="<?php echo $name; ?>">
						<input name="<?php echo $name; ?>" class="hint_type dns-prefetch" type="radio" value="dns-prefetch" <?php $this->is_checked( 'dns-prefetch', $hint_type ); ?>/>
					</label>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a resource that is likely to be needed on a page later.', 'pprh' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Prefetch' ); ?></span>
					<label for="<?php echo $name; ?>">
						<input name="<?php echo $name; ?>" class="hint_type prefetch" type="radio" value="prefetch" <?php $this->is_checked( 'prefetch', $hint_type ); ?>/>
					</label>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a page/post your visitors are likely to navigate towards.', 'pprh' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Prerender' ); ?></span>
					<label for="<?php echo $name; ?>">
						<input name="<?php echo $name; ?>" class="hint_type prerender" type="radio" value="prerender" <?php $this->is_checked( 'prerender', $hint_type ); ?>/>
					</label>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert domain names from external URL\'s to perform DNS resolution, initial connection, and SSL negotiation ahead of time.', 'pprh' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Preconnect' ); ?></span>
					<label for="<?php echo $name; ?>">
						<input name="<?php echo $name ?>" class="hint_type preconnect" type="radio" value="preconnect" <?php $this->is_checked( 'preconnect', $hint_type ); ?>/>
					</label>
				</div>
			</td>

			<td>
				<div>
					<span class="pprh-help-tip-hint">
						<span><?php esc_html_e( 'Insert the full URL of a resource that is needed on a current page.', 'pprh' ); ?></span>
					</span>
					<span><?php esc_html_e( 'Preload' ); ?></span>
					<label for="<?php echo $name; ?>">
						<input name="<?php echo $name; ?>" class="hint_type preload" type="radio" value="preload" <?php $this->is_checked( 'preload', $hint_type ); ?>/>
					</label>
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

	protected function set_attrs( string $hint_type, string $xorigin, string $as_attr, string $type_attr ) {
		$xorigin_disabled = ( empty( $xorigin ) ? '' : 'checked="checked"' );
		$disabled = ( 0 === preg_match( '/preconnect|preload/', $hint_type ) ) ? 'disabled' : '';
		?>
		<tr>

		<td colspan="1">
			<span class="pprh-help-tip-hint">
				<span><?php _e( 'Crossorigin applies to preconnect and preload hints only. For various reasons, font files (and others) need to be loaded with the crossorigin attribute.<a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#Cross-origin_fetches">Source: Mozilla</a>', 'pprh' ); ?></span>
			</span>
			<label>
				<span><?php esc_html_e( 'Crossorigin?', 'pprh' ); ?></span>
				<input class="widefat pprh_crossorigin" value="crossorigin" type="checkbox" name="crossorigin" <?php echo $xorigin_disabled . $disabled; ?>/>
			</label>
		</td>

		<td colspan="2" style="text-align: right; padding-right: 40px;">
				<span class="pprh-help-tip-hint">
					<span>
						<?php _e( "Setting this attribute allows the browser to more accurately: <br/> 1) prioritize resource loading <br/>2) store in browser cache <br/>3) apply the correct headers. <a href='https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#The_basics'>Source: Mozilla</a>", 'pprh' ); ?>
					</span>
				</span>
				<span><?php esc_html_e( 'as:', 'pprh' ); ?></span>
				<label>
					<select class="pprh_as_attr" name="as_attr">
						<?php $this->get_as_attrs( $as_attr, 'as' ); ?>
					</select>
				</label>
			</td>

			<td colspan="2">
				<span class="pprh-help-tip-hint">
					<span><?php _e( '&lt;link&gt; elements can accept a type attribute, which contains the MIME type of the resource the element points to. This is especially useful when preloading resources — the browser will use the type attribute value to work out if it supports that resource, and will only download it if so, ignoring it if not. <a href="https://developer.mozilla.org/en-US/docs/Web/HTML/Preloading_content#Including_a_MIME_type">Source: Mozilla</a>. (This attribute will attempt to be added automatically.)', 'pprh' ); ?></span>
				</span>
				<span><?php esc_html_e( 'Mime Type:', 'pprh' ); ?></span>
				<label>
					<select class="pprh_type_attr" name="type_attr">
						<?php $this->get_as_attrs( $type_attr, 'type' ); ?>
					</select>
				</label>
			</td>

		</tr>
		<?php
	}

	protected function set_media_attr( string $hint_type, string $media ) {
		$med_value  = "value='$media'";
		$med_value .= ( 'preload' === $hint_type ) ? '' : ' disabled';
		?>
		<tr>

			<td colspan="1">
				<p><?php esc_html_e( 'Media Attribute', 'pprh' ); ?>
					<span class="pprh-help-tip-hint">
						<span><?php _e( 'This applies only to the preload resource hint. This can be used to allow hints to only load on certain devices, screen dimensions, and more. <a href="https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries">https://developer.mozilla.org/en-US/docs/Web/CSS/Media_Queries/Using_media_queries</a>', 'pprh' ); ?></span>
					</span>
				</p>
			</td>

			<td colspan="4">
				<input placeholder="" class="widefat pprh_media" type="text" name="media" <?php echo $med_value; ?> />
			</td>

		</tr>
		<?php
	}
}
