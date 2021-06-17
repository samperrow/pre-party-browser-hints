<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	private $general_settings;
	private $preconnect_settings;
	private $prefetch_settings;

	protected $on_pprh_admin = false;

	public function __construct() {
//		$this->on_pprh_admin = $on_pprh_admin;
//		$this->general_settings = new GeneralSettings();
//		$this->preconnect_settings = new PreconnectSettings($on_pprh_admin);
//		$this->prefetch_settings = new PrefetchSettings();
		$this->display_settings( true );
	}

	public function display_settings( $on_pprh_admin ) {
		?>
        <div id="pprh-settings" class="pprh-content">
            <form method="post" action="">
                <?php
                    wp_nonce_field( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
                    $this->save_user_options();

                    if ( $on_pprh_admin ) {
						wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
						do_meta_boxes( 'toplevel_page_pprh-plugin-settings', 'normal', null );
					}

                ?>
                <div class="text-center">
                    <input type="submit" name="pprh_save_options" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pprh' ); ?>" />
                </div>
            </form>
        </div>
		<?php
	}

	public function save_user_options() {
	    if ( ( isset( $_POST['pprh_save_options'] ) || isset( $_POST['pprh_preconnect_set'] ) ) && check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' ) ) {
			GeneralSettings::save_options();
			PreconnectSettings::save_options();
			PrefetchSettings::save_options();
		}

		\do_action( 'pprh_sc_save_settings' );
	}

}
