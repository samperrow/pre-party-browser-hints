<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class InsertHints {

	private $on_plugin_page;

	public function __construct( bool $on_plugin_page ) {
		$this->on_plugin_page = $on_plugin_page;
	}

	public function markup() {
		echo '<div id="insert-hints" class="pprh-content insert-hints">';
		$display_hints = new DisplayHints( false, $this->on_plugin_page );
		$new_hint      = new NewHint( $this->on_plugin_page, array() );
		$new_hint->create_new_hint_table();
		echo '</div>';
		unset( $display_hints );
	}

}
