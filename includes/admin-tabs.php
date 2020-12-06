<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Tabs {

//	public $results;

	public function __construct() {
		include_once PPRH_ABS_DIR . 'includes/new-hint.php';

//		do_action( 'pprh_load_admin_tabs_child' );

		$this->settings_page();
	}

	public function settings_page() {
		if ( ! is_admin() ) {
			exit;
		}

		echo '<div id="pprh-wrapper" class="wrap">';
		echo '<h2>Pre* Party Plugin Settings</h2>';
		Utils::pprh_notice();
		$this->show_admin_tabs();
		echo '</div>';
	}

	public function show_admin_tabs() {
		$tabs = array(
			'insert-hints' => 'Insert Hints',
			'settings'     => 'Settings',
            'info'         => 'Information',
//            'upgrade'      => 'Upgrade to Pro',
		);

//		$tabs = apply_filters( 'pprh_pro_show_tabs', $tabs );

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			echo "<a class='nav-tab $tab' href='?page=pprh-plugin-settings'>" . $name . '</a>';
		}
		echo '</h2>';
		echo '<div class="pprh-box">';

        $this->include_files();
		$this->show_footer();
		echo '</div>';
	}

	private function include_files() {
		include_once PPRH_ABS_DIR . 'includes/tabs/insert-hints.php';
		include_once PPRH_ABS_DIR . 'includes/tabs/settings.php';
        include_once PPRH_ABS_DIR . 'includes/tabs/info.php';
//		include_once PPRH_ABS_DIR . 'includes/tabs/upgrade.php';
//		do_action( 'pprh_pro_add_files' );
	}

	public function show_footer() {
		self::contact_author();
		echo '<br/>';
		echo sprintf( 'Tip: test your website on %sWebPageTest.org%s to know which resource hints and URLs to insert.', '<a href="https://www.webpagetest.org">', '</a>' );
	}

	public static function contact_author() {
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
