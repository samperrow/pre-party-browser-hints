<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InsertHints {

	private $plugin_page;

	public function __construct( $plugin_page ) {
		$this->plugin_page = $plugin_page;
	}

	public function markup() {
		echo '<div class="pprh-content insert-hints">';
		$display_hints = new DisplayHints( false, $this->plugin_page );
		$new_hint      = new NewHint( array() );
		$new_hint->create_new_hint_table();
		echo '</div>';
		unset( $display_hints );
	}

}
