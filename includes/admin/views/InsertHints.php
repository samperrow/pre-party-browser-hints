<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InsertHints {

	public function __construct() {

	}

	public function markup() {
		echo '<div class="pprh-content insert-hints">';
		wp_nonce_field( 'pprh_display_hints_nonce_action', 'pprh_display_hints_nonce' );
		$display_hints = new DisplayHints(false);
		$new_hint = new NewHint();
		$new_hint->create_new_hint_table();
		echo '</div>';
		unset( $display_hints );
	}

}
