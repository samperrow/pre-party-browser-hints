<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Insert_Hints {

	public function __construct() {
		echo '<div class="pprhAdminPage" id="pprh-insert-hints">';
//		echo '<form id="pprh-list-table" method="post" action="' . admin_url( 'admin.php?page=pprh-plugin-settings' ) . '">';
		echo '<form id="pprh-list-table" method="post" action="">';
		wp_nonce_field( 'pprh_display_hints_nonce_action', 'pprh_display_hints_nonce' );
		new Display_Hints();
		echo '</form>';
		include_once PPRH_PLUGIN_DIR . '/class-pprh-add-new-hint.php';
		$new_hint = new Add_New_Hint();
		echo '</div>';
	}

}

new Insert_Hints();
