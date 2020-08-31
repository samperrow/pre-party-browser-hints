<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Insert_Hints {

	public function __construct() {
		echo '<div id="pprh-insert-hints" class="pprh-content">';
		new Display_Hints();
		$new_hint = new New_Hint();
		$new_hint->new_hint_table();
		echo '</div>';
	}

}

new Insert_Hints();
