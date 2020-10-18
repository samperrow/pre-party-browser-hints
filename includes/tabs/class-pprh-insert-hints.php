<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

new Insert_Hints();

class Insert_Hints {

	public function __construct() {
		echo '<div id="pprh-insert-hints" class="pprh-content">';
		wp_nonce_field( 'pprh_display_hints_nonce_action', 'pprh_display_hints_nonce' );
		new Display_Hints();
		new New_Hint();
		echo '</div>';
	}

}
