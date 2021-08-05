<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dashboard {

	public function __construct() {
		if ( ! \has_action(  'pprh_notice' ) ) {
			\add_action( 'pprh_notice', array( $this, 'default_admin_notice' ), 10, 0 );
		}
    }

    public function default_admin_notice() {
	    Utils::show_notice( '', true );
    }

	public function show_plugin_dashboard( $on_pprh_page ) {
	    if ( 0 === $on_pprh_page ) {
	        return;
        }

		$settings = new Settings();
        $faq = new FAQ();

		echo '<div id="pprh-poststuff" class="wrap"><h1>';
		esc_html_e( 'Pre* Party Resource Hints', 'pprh' );
		echo '</h1>';
        \do_action(  'pprh_notice' );
		$this->do_upgrade( PPRH_VERSION_NEW, PPRH_VERSION );
		$insert_hints = new InsertHints( $on_pprh_page );
		$this->show_admin_tabs();

		$insert_hints->markup();
		$settings->markup( true );
		$faq->markup();

		\do_action( 'pprh_load_view_classes' );
		$this->show_footer();
		echo '</div>';
		unset( $insert_hints, $settings, $faq );
	}


	public function show_admin_tabs() {
		$menu_slug = PPRH_MENU_SLUG;
		$tabs = array(
			'insert-hints' => 'Insert Hints',
			'settings'     => 'Settings',
			'faq'          => 'FAQ',
		);

		$tabs = \apply_filters( 'pprh_load_tabs', $tabs );

		echo '<div class="nav-tab-wrapper" style="margin-bottom: 10px;">';
		foreach ( $tabs as $tab => $name ) {
			echo "<a class='nav-tab $tab' href='?page=$menu_slug'>" . $name . '</a>';
		}
		echo '</div>';
	}

	public function do_upgrade( $new_version, $old_version ) {
	    $ugprade = $this->check_to_upgrade( $new_version, $old_version );

	    if ( ! $ugprade ) {
	        return;
        }

        $activate_plugin = new ActivatePlugin();
		$msg = 'Version ' . PPRH_VERSION_NEW . ' Upgrade Notes: 1) Fixed issue in auto prefetch script, improved plugin testability, and other small improvements.';
        Utils::show_notice( $msg, true );
        $activate_plugin->upgrade_plugin();

        if ( $activate_plugin->plugin_activated ) {
            Utils::update_option( 'pprh_version', $new_version );
        }
    }

	public function check_to_upgrade( $new_version, $old_version ):bool {
		return $new_version !== $old_version;
	}

	public function show_footer() {
		$this->contact_author();
		echo '<br/>';
		echo sprintf( 'Tip: test your website on %sWebPageTest.org%s to know which resource hints and URLs to insert.', '<a href="https://www.webpagetest.org">', '</a>' );
	}

	public function contact_author() {
		\add_thickbox();
		?>

		<div class="text-center">
            <a style="margin: 20px 0;" href="#TB_inline?width=500&amp;height=300&amp;inlineId=pprhEmail" class="thickbox button button-primary">
                <span style="margin: 3px 5px 0 0;" class="dashicons dashicons-email"></span>
                Contact Support
            </a>

            <div style="display: none; text-align: center;" id="pprhEmail">
                <h2 style="font-size: 23px; text-align: center;"><?php esc_html_e( 'Request a New Feature or Report a Bug' ); ?></h2>

                <form method="post" style="width: 350px; margin: 0 auto; text-align: center">
                    <label for="pprhEmailText">
                        <?php \wp_nonce_field( 'pprh_email_nonce_action', 'pprh_email_nonce_nonce' ); ?>
                    </label>
                    <textarea name="pprh_text" id="pprhEmailText" style="height: 100px;" class="widefat" placeholder="<?php esc_attr_e( 'Help make this plugin better!' ); ?>"></textarea>
                    <label for="pprhEmailAddress"></label><input name="pprh_email" id="pprhEmailAddress" style="padding: 5px;" class="input widefat" placeholder="<?php esc_attr_e( 'Email address:' ); ?>"/>
                    <br/>
                    <input name="pprh_send_email" id="pprhSubmit" type="submit" class="button button-primary" value="<?php esc_attr_e( 'Submit', 'pprh' ); ?>" />
                </form>

            </div>
		<?php
            $this->verify_support_email();
		echo '</div>';
	}

	public function verify_support_email() {
		if ( isset( $_POST['pprh_send_email'], $_POST['pprh_email'], $_POST['pprh_text'] ) && check_admin_referer( 'pprh_email_nonce_action', 'pprh_email_nonce_nonce' ) ) {
			$email_address = \sanitize_email( \wp_unslash( $_POST['pprh_email'] ) );
			$text = \sanitize_text_field( \wp_unslash( $_POST['pprh_text'] ) );
			$this->send_support_email( $email_address, $text );
		}
	}

    public function send_support_email( string $email_address, string $text ) {
		$msg = "From: $email_address";
		$msg .= Utils::get_debug_info();
		$msg .= "\nMessage: $text";
        Utils::send_email( 'sam.perrow399@gmail.com', 'Pre Party User Message', $msg );
	}

}