<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Insert_Hints {

	public function __construct() {
		echo '<div id="pprh-insert-hints" class="pprh-content">';
		wp_nonce_field( 'pprh_display_hints_nonce_action', 'pprh_display_hints_nonce' );
		new Display_Hints();
		$new_hint = new New_Hint();
		$new_hint->create_new_hint_table();
		echo '</div>';
	}

}

new Insert_Hints();
