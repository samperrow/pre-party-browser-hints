<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	public function save_user_options() {
		if ( isset( $_POST['pprh_save_options'] ) || isset( $_POST['pprh_preconnect_set'] ) ) {
		    \check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
			GeneralSettings::save_options();
			PreconnectSettings::save_options();
			PrefetchSettings::save_options();
		}

		\do_action( 'pprh_sc_save_settings' );
	}

	public function markup( $on_pprh_admin ) {
		$this->save_user_options();
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
					<input type="submit" name="pprh_save_options" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pprh' ); ?>" />
				</div>
			</form>
		</div>
		<?php
	}

}
