<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Insert_Hints {

	public function __construct() {
		echo '<div id="pprh-insert-hints" class="pprh-content">';
		echo '<form id="pprh-list-table" method="post" action="' . admin_url() . '">';
		wp_nonce_field( 'pprh_display_hints_nonce_action', 'pprh_display_hints_nonce' );
		new Display_Hints();
		echo '</form>';
		$new_hint = new New_Hint();
		$new_hint->new_hint_table();
		echo '</div>';
	}

}

new Insert_Hints();
