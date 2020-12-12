<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Load_Client {

	public function __construct () {
		$this->check_if_wp_hints_disabled();

		include_once PPRH_ABS_DIR . 'includes/send-hints.php';

		add_action( 'wp_loaded', array( $this, 'send_resource_hints' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_flying_pages' ) );
	}

	public function send_resource_hints()  {
		$send_hints = new Send_Hints();
		$send_hints->get_resource_hints();
	}

	private function load_flying_pages() {
		$load_flying_pages = get_option( 'pprh_prefetch_enabled' );

		if ( $load_flying_pages === 'true' ) {
			$fp_data = array(
				'delay'          => get_option( 'pprh_prefetch_delay', 0 ),
				'hoverDelay'     => get_option( 'pprh_prefetch_hoverDelay', 50 ),
				'maxRPS'         => get_option( 'pprh_prefetch_maxRPS', 3 ),
				'ignoreKeywords' => get_option( 'pprh_prefetch_ignoreKeywords', '' ),
			);

			wp_register_script( 'pprh_prefetch_flying_pages', PPRH_REL_DIR . 'js/flying-pages.min.js', null, PPRH_VERSION, true );
			wp_localize_script( 'pprh_prefetch_flying_pages', 'pprh_fp_data', $fp_data );
			wp_enqueue_script( 'pprh_prefetch_flying_pages' );
		}
	}

	public function check_if_wp_hints_disabled() {
		if ( 'true' === get_option( 'pprh_disable_wp_hints' ) ) {
			remove_action( 'wp_head', 'wp_resource_hints', 2 );
		}
	}

}
