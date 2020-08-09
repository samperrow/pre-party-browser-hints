<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

new Settings();

class Settings {

	protected $load_adv = false;

	public function __construct() {

		do_action( 'pprh_load_settings_child' );
		$this->display_settings();
	}

	public function display_settings() {
		?>
        <div id="pprh-settings" class="pprh-content">
            <form method="post" action="<?php echo admin_url(); ?>admin.php?page=pprh-plugin-settings">
                <?php
                $this->main();

                wp_nonce_field( 'pprh_save_admin_options', 'pprh_admin_options_nonce' );
                $this->save_user_options();

                include_once PPRH_ABS_DIR . '/includes/tabs/settings/class-pprh-general.php';
                include_once PPRH_ABS_DIR . '/includes/tabs/settings/class-pprh-auto-preconnect.php';

                do_action( 'pprh_sc_load_prerender_mu' );
                ?>

                <div style="text-align: center;">
                    <input type="submit" name="pprh_save_options" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'pprh' ); ?>" />
                </div>

            </form>
        </div>
		<?php
	}

	public function main() {
		$tabs = array(
			'general'         => 'General Settings',
			'auto-preconnect' => 'Auto Preconnect Settings',
		);

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			echo "<a id='pprh-$tab-settings' class='nav-tab pprh-settings' href=''>" . $name . '</a>';
		}
		echo '</h2>';
	}

	public function save_user_options() {
		if ( isset( $_POST['pprh_save_options'] ) && check_admin_referer( 'pprh_save_admin_options', 'pprh_admin_options_nonce' ) ) {

            if ( isset( $_POST['pprh_save_options'] ) ) {
                update_option( 'pprh_prec_autoload_preconnects', wp_unslash( $_POST['autoload_preconnects'] ) );
                update_option( 'pprh_disable_wp_hints', wp_unslash( $_POST['disable_wp_hints'] ) );
                update_option( 'pprh_prec_allow_unauth', wp_unslash( $_POST['allow_unauth'] ) );
                update_option( 'pprh_html_head', wp_unslash( $_POST['html_head'] ) );

                do_action( 'pprh_pro_save_settings' );
            }
		} elseif ( isset( $_POST['pprh_prec_preconnects_set'] ) ) {
			update_option( 'pprh_prec_preconnects_set', 'false' );
		}
	}

	public function get_option_status( $option, $val ) {
		echo esc_html( ( get_option( 'pprh_' . $option ) === $val ? 'selected=selected' : '' ) );
	}

}
