<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PPRH_Insert_Hints {

	public function __construct() {

		echo '<div class="pprhAdminPage" id="pprh-insert-hints">';
		echo '<form id="pprh-list-table" method="post" action="' . admin_url( 'admin.php?page=pprh-plugin-settings' ) . '">';
		wp_nonce_field( 'pprh_display_hints_nonce_action', 'pprh_display_hints_nonce' );
		new PPRH_Display_Hints();
		echo '</form>';
		include_once PPRH_PLUGIN_DIR . '/class-pprh-add-new-hint.php';
		echo '</div>';
	}



}

new PPRH_Insert_Hints();
