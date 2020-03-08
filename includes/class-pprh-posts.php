<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Posts {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'create_meta_box' ), 10, 0 );
    }

	public function get_pprh_modal_post_types() {
	    return json_decode( get_option( 'pprh_post_modal_types' ) );
	}

	public function create_meta_box() {
		// $validate_license = new PPRH_License();
		 $license_is_valid = Utils::verify_license();
		 $callback_name    = ($license_is_valid) ? 'create_pp_meta_box' : 'create_tease_meta_box';
//		$callback_name    = '$callback_name';

        $id       = 'pprh_post_meta';
		$title    = 'Pre* Party Resource Hints';
		$callback = array( $this, $callback_name );
		$context  = 'normal';
		$priority = 'low';
		$screens = $this->get_pprh_modal_post_types();

		if ( isset( $screens ) && count( $screens ) > 0 ) {
            foreach ( $screens as $screen ) {
                add_meta_box(
                    $id,
                    $title,
                    $callback,
                    $screen,
                    $context,
                    $priority
                );
            }
        }
	}

	public function create_tease_meta_box() {
		?>
		<div style="text-align: center;">
			<h3>For just $9 you can upgrade to the Pre* Party Resource Hints paid plan to enjoy these features:</h3>
			<ul style="max-width: 500px; text-align: left; list-style-type: disc; display: block; margin: 0 auto;">
				<li>Implement resource hints to specific posts and pages.</li>
				<li>Inline table editing of resource hints.</li>
				<li>Automatic and post-specific creation of custom preconnect hints for each post/page.</li>
				<li>100% Ajax-enabled resource hint creation, updating, and deletion.</li>
			</ul>
			<input id="pprhOpenCheckoutModal" type="button" class="button button-primary" value="Purchase License"/>
		</div>
		<?php
	}

	public function create_pp_meta_box() {
		$title = Utils::shorten_url( get_the_title() );
		echo '<h3>';
		esc_html_e( 'Resource Hints used on ', 'pprh' );
		echo esc_html( $title );
		echo '</h3>';
		Utils::pprh_notice();

		new Display_Hints();
		include_once PPRH_PLUGIN_DIR . '/class-pprh-add-new-hint.php';
		$table = new Add_New_Hint();
		$table->create_new_hint_table();
		?>
		<input size="50" type="hidden" name="pprh_post_reset" id="pprhPageResetValue" value=""/>
        <input size="50" type="hidden" name="pprh_link_changed" id="pprhLinkChanged" value=""/>
        <?php
	}

}

new Posts();
