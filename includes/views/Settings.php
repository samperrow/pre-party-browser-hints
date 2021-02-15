<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	private $general_settings;
	private $preconnect_settings;
	private $prefetch_settings;

	public function __construct() {

		do_action( 'pprh_load_settings_child' );
		$this->general_settings = new GeneralSettings();
		$this->preconnect_settings = new PreconnectSettings();
		$this->prefetch_settings = new PrefetchSettings();
		$this->display_settings();
	}

	public function display_settings() {
		?>
        <div id="pprh-settings" class="pprh-content">
            <form method="post" action="">
                <?php
                    wp_nonce_field( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
                    $this->save_user_options();
                    $this->general_settings->markup();
				    $this->preconnect_settings->markup();
			    	$this->prefetch_settings->markup();

				    do_action( 'pprh_prerender_settings' );
                ?>
                <div class="text-center">
                    <input type="submit" name="pprh_save_options" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pprh' ); ?>" />
                </div>
            </form>
        </div>
		<?php
	}

	public function save_user_options() {
	    if ( isset( $_POST['pprh_save_options'] ) && check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' ) ) {
			$this->general_settings->save_options();
			$this->preconnect_settings->save_options();
			$this->prefetch_settings->save_options();
		}

		do_action( 'pprh_save_settings' );
	}

}
