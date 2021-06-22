<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

//	public $on_pprh_admin = false;

	public function __construct() {
		\add_action( 'pprh_check_to_upgrade', array( $this, 'check_to_upgrade' ), 10, 1 );

		if ( ! \has_action(  'pprh_notice' ) ) {
			\add_action( 'pprh_notice', array( $this, 'default_admin_notice' ), 10, 0 );
		}
    }

    public function default_admin_notice() {
	    Utils::show_notice( '', true );
    }

	public function show_plugin_dashboard( $on_pprh_admin_page ) {
	    if ( ! $on_pprh_admin_page ) {
	        return;
        }

		$insert_hints = new InsertHints();
		$settings = new Settings();
		$hint_info = new HintInfo();
		$upgrade = new Upgrade();

		echo '<div id="poststuff"><h1>';
		esc_html_e( 'Pre* Party Plugin Settings', 'pprh' );
		echo '</h1>';
        \do_action(  'pprh_notice' );
		$this->show_admin_tabs();

		$insert_hints->markup();
		$settings->markup(true);
		$hint_info->markup();
		$upgrade->markup();

		\do_action( 'pprh_load_view_classes' );
		\do_action( 'pprh_check_to_upgrade', '1.7.6.3' );
		$this->show_footer();
		echo '</div>';
		unset( $insert_hints, $settings, $hint_info, $upgrade );
	}


	public function show_admin_tabs() {
		$menu_slug = PPRH_MENU_SLUG;
		$tabs = array(
			'insert-hints' => 'Insert Hints',
			'settings'     => 'Settings',
			'hint-info'    => 'Information',
//            'upgrade'      => 'Upgrade to Pro',
		);

		$tabs = \apply_filters( 'pprh_load_tabs', $tabs );

		echo '<div class="nav-tab-wrapper" style="margin-bottom: 10px;">';
		foreach ( $tabs as $tab => $name ) {
			echo "<a class='nav-tab $tab' href='?page=$menu_slug'>" . $name . '</a>';
		}
		echo '</div>';
	}


	public function check_to_upgrade( $new_version ) {
		if ( $new_version !== PPRH_VERSION ) {
			$this->do_upgrade();
			update_option( 'pprh_version', $new_version );
		}
	}

	public function do_upgrade() {
		$previous_version = PPRH_VERSION;
		$pprh = new Pre_Party_Browser_Hints();
		$pprh->activate_plugin();
		$activate_plugin = new ActivatePlugin();

		$msg = PPRH_VERSION . ' Upgrade Notes: Fixed bug preventing users from selecting crossorigin and media attribute.';
		Utils::show_notice( $msg, true );

		if ( version_compare( '1.7.6', $previous_version ) > 0 ) {
			$activate_plugin->upgrade_prefetch_keywords();
			$activate_plugin->upgrade_plugin();
		}
	}

	public function show_footer() {
		$this->contact_author();
		echo '<br/>';
		echo sprintf( 'Tip: test your website on %sWebPageTest.org%s to know which resource hints and URLs to insert.', '<a href="https://www.webpagetest.org">', '</a>' );
	}

	public function contact_author() {
		add_thickbox();
		?>

		<div id="pprhContactAuthor">
            <a style="margin: 20px 0;" href="#TB_inline?width=500&amp;height=300&amp;inlineId=pprhEmail" class="thickbox button button-primary">
                <span style="margin: 3px 5px 0 0;" class="dashicons dashicons-email"></span>
                Contact Support
            </a>

            <div style="display: none; text-align: center;" id="pprhEmail">
                <h2 style="font-size: 23px; text-align: center;"><?php esc_html_e( 'Request a New Feature or Report a Bug!' ); ?></h2>

                <form method="post" style="width: 350px; margin: 0 auto; text-align: center">
                    <label for="pprhEmailText"><?php wp_nonce_field( 'pprh_email_nonce_action', 'pprh_email_nonce_nonce' ); ?></label><textarea name="pprh_text" id="pprhEmailText" style="height: 100px;" class="widefat" placeholder="<?php esc_attr_e( 'Help make this plugin better!' ); ?>"></textarea>
                    <label for="pprhEmailAddress"></label><input name="pprh_email" id="pprhEmailAddress" style="margin: 10px 0;" class="input widefat" placeholder="<?php esc_attr_e( 'Email address:' ); ?>"/>
                    <br/>
                    <input name="pprh_send_email" id="pprhSubmit" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Submit', 'pprh' ); ?>" />
                </form>

            </div>
		<?php

            if ( isset( $_POST['pprh_send_email'] ) && check_admin_referer( 'pprh_email_nonce_action', 'pprh_email_nonce_nonce' ) ) {
                $debug_info = "\nURL: " . home_url() . "\nPHP Version: " . PHP_VERSION . "\nWP Version: " . get_bloginfo( 'version' );
                wp_mail( 'sam.perrow399@gmail.com', 'Pre Party User Message', 'From: ' . sanitize_email( wp_unslash( $_POST['pprh_email'] ) ) . $debug_info . "\nMessage: " . sanitize_text_field( wp_unslash( $_POST['pprh_text'] ) ) );
            }

		echo '</div>';
	}

}