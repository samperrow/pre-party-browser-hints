<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Insert_Hints {

	public function __construct() {
	    ?>
		<div class="pprh-content" id="pprh-insert-hints">
		    <form id="pprh-list-table" method="post" action="<?php echo admin_url('admin.php?page=pprh-plugin-settings'); ?>">
        <?php
		wp_nonce_field( 'pprh_display_hints_nonce_action', 'pprh_display_hints_nonce' );
		new Display_Hints();
		echo '</form>';
		include_once PPRH_PLUGIN_DIR . '/class-pprh-add-new-hint.php';
		$new_hint = new Add_New_Hint();
		echo '</div>';
	}

}

new Insert_Hints();
