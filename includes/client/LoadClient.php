<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoadClient {

	public function init( $client_data ) {
//		$data = \apply_filters( 'pprh_pro_load_client', $pprh_preconnect_autoload );

		if ( is_array( $client_data ) ) {
			$client_data = array();
		}

		$this->verify_to_load_fp_ctrl();

		$send_hints = new SendHints();
		$send_hints->init_ctrl( $client_data );
		$disable_wp_hints = \get_option( 'pprh_disable_wp_hints' );
		$this->disable_wp_hints( $disable_wp_hints );
	}

	public function verify_to_load_fp_ctrl() {
		$flying_pages_enabled        = ( 'true' === \get_option( 'pprh_prefetch_enabled' ) );
		$disable_for_logged_in_users = \get_option( 'pprh_prefetch_disableForLoggedInUsers', 'false' );
		$disabled                    = ( 'true' === $disable_for_logged_in_users && \is_user_logged_in() );

		if ( $this->verify_to_load_fp( $flying_pages_enabled, $disabled ) ) {
			\add_action( 'wp_enqueue_scripts', array( $this, 'load_flying_pages' ) );
		}
	}

	public function verify_to_load_fp( $flying_pages_enabled, $disabled ) {
		return ( $flying_pages_enabled && ! $disabled );
	}

	private function disable_wp_hints( $disable_wp_hints ) {
		if ( 'true' === $disable_wp_hints ) {
			\remove_action( 'wp_head', 'wp_resource_hints', 2 );
		}
	}

	public function load_flying_pages() {
		$js_script_path = ( PPRH_IN_DEV ) ? 'js/flying-pages.js' : 'js/flying-pages.min.js';

		$fp_data = array(
			'testing'        => ( PPRH_IN_DEV ) ? 'true' : 'false',
			'delay'          => \get_option( 'pprh_prefetch_delay', 0 ),
			'maxRPS'         => \get_option( 'pprh_prefetch_maxRPS', 3 ),
			'hoverDelay'     => \get_option( 'pprh_prefetch_hoverDelay', 50 ),
			'maxPrefetches'  => \get_option( 'pprh_prefetch_max_prefetches', 10 ),
			'ignoreKeywords' => \get_option( 'pprh_prefetch_ignoreKeywords', array() )
		);

		\wp_register_script( 'pprh_prefetch_flying_pages', PPRH_REL_DIR . $js_script_path, null, PPRH_VERSION, true );
		\wp_localize_script( 'pprh_prefetch_flying_pages', 'pprh_fp_data', $fp_data );
		\wp_enqueue_script( 'pprh_prefetch_flying_pages' );
	}

}
