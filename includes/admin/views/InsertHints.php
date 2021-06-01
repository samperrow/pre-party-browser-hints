<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InsertHints {

	public function __construct() {
		echo '<div id="pprh-insert-hints" class="pprh-content">';
		wp_nonce_field( 'pprh_display_hints_nonce_action', 'pprh_display_hints_nonce' );
		$display_hints = new DisplayHints();
		$new_hint = new NewHint();
		$new_hint->create_new_hint_table();
		echo '</div>';
		unset( $display_hints );
	}

}
