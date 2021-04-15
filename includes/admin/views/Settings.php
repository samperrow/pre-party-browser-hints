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

	public function __construct($on_pprh_admin = true) {
        $this->on_pprh_admin = $on_pprh_admin;
		$this->general_settings = new GeneralSettings();
		$this->preconnect_settings = new PreconnectSettings($on_pprh_admin);
		$this->prefetch_settings = new PrefetchSettings();

		$this->display_settings();

	}

	public function display_settings() {
		?>
        <div id="pprh-settings" class="pprh-content">
            <div id="post-body" class="metabox-holder columns-1">
                <form method="post" action="">
                    <?php
                        wp_nonce_field( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
                        $this->save_user_options();

                        if ( $this->on_pprh_admin ) {
                            echo '<div id="postbox-container-1" class="postbox-container">';

                            do_meta_boxes('pprh-plugin-settings', 'normal', null);

                            echo '</div>';

    //						$this->general_settings->show_settings();
    //						$this->preconnect_settings->show_settings();
    //						$this->prefetch_settings->show_settings();
                        }

                        do_action( 'pprh_sc_prerender_settings' );
                    ?>
                    <div class="text-center">
                        <input type="submit" name="pprh_save_options" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pprh' ); ?>" />
                    </div>
                </form>
            </div>
        </div>
		<?php
	}

	public function save_user_options() {
	    if ( ( isset( $_POST['pprh_save_options'] ) || isset( $_POST['pprh_preconnect_set'] ) ) && check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' ) ) {
			$this->general_settings->save_options();
			$this->preconnect_settings->save_options();
			$this->prefetch_settings->save_options();
		}

		do_action( 'pprh_sc_save_settings' );
	}

}
