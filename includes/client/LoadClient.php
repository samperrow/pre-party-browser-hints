<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoadClient {

	public function init() {
		do_action( 'pprh_pro_load_client' );
		include_once 'SendHints.php';

		$this->verify_to_load_fp();

		$enabled_hints = Utils::get_pprh_hints( 1 );

		$send_hints = new SendHints();
		$send_hints->init($enabled_hints);

		$disable_wp_hints = get_option( 'pprh_disable_wp_hints' );
		$this->disable_wp_hints( $disable_wp_hints );
	}

	public function verify_to_load_fp() {
		$do_not_load_flying_pages = ( 'false' === get_option( 'pprh_prefetch_enabled' ) );
		$disable_for_logged_in_users = get_option( 'pprh_prefetch_disableForLoggedInUsers' );
		$disabled = ('true' === $disable_for_logged_in_users && is_user_logged_in() );

		if ( $do_not_load_flying_pages || $disabled ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'load_flying_pages' ) );
	}

	private function disable_wp_hints( $disable_wp_hints ) {
		if ( 'true' === $disable_wp_hints ) {
			remove_action( 'wp_head', 'wp_resource_hints', 2 );
		}
	}



	public function load_flying_pages() {
		$debug = ( defined( 'PPRH_DEBUG' ) && PPRH_DEBUG );
		$js_script_path = ($debug) ? 'js/flying-pages.js' : 'js/flying-pages.min.js';

		$fp_data = array(
			'debug'          => ( $debug ) ? 'true' : 'false',
			'delay'          => get_option( 'pprh_prefetch_delay', 0 ),
			'maxRPS'         => get_option( 'pprh_prefetch_maxRPS', 3 ),
			'hoverDelay'     => get_option( 'pprh_prefetch_hoverDelay', 50 ),
			'maxPrefetches'  => get_option( 'pprh_prefetch_max_prefetches', 10 ),
			'ignoreKeywords' => get_option( 'pprh_prefetch_ignoreKeywords', '' )
		);

		wp_register_script( 'pprh_prefetch_flying_pages', PPRH_REL_DIR . $js_script_path, null, PPRH_VERSION, true );
		wp_localize_script( 'pprh_prefetch_flying_pages', 'pprh_fp_data', $fp_data );
		wp_enqueue_script( 'pprh_prefetch_flying_pages' );
	}

}
