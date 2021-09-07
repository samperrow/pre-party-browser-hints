<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	public function save_user_options() {
		if ( isset( $_POST['pprh_save_options'] ) || isset( $_POST['pprh_preconnect_set'] ) ) {
            \check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
            $settings_save = new SettingsSave();
			$settings_save->save_settings();
		}

		\do_action( 'pprh_sc_save_settings' );
	}

	public function markup( $on_pprh_admin ) {
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

	protected function get_each_keyword( $keywords ) {
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

	public function turn_textarea_to_array( $text ) {
		$clean_text = preg_replace( '/[\'<>^\"\\\]/', '', $text );
		return explode( "\r\n", $clean_text );
	}

}
