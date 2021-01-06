<?php

declare(strict_types=1);

use PPRH\Preconnects;
use PHPUnit\Framework\TestCase;

final class PreconnectsTest extends TestCase {

//	public function __construct() {}

	public function test_PreconnectDoesNotLoad (): void {
		update_option('pprh_preconnect_autoload', 'true');
		update_option('pprh_preconnect_set', 'true');

		$prec = new \PPRH\Preconnects();
		$wp_loaded = has_action( 'wp_loaded', array( $prec, 'initialize' ) );
		$this->assertEquals(false, $wp_loaded);
	}

	public function test_PreconnectsDoesLoad (): void {
		update_option('pprh_preconnect_autoload', 'true');
		update_option('pprh_preconnect_set', 'false');

		$prec = new \PPRH\Preconnects();
		$wp_loaded = has_action( 'wp_loaded', array( $prec, 'initialize' ) );
		$this->assertEquals(true, $wp_loaded);
	}

	public function test_load_preconnects_only_for_logged_in_users(): void {
		update_option('pprh_preconnect_autoload', 'true');
		update_option('pprh_preconnect_set', 'false');
		update_option( 'pprh_preconnect_allow_unauth', 'false' );

		remove_action( 'wp_ajax_pprh_post_domain_names', array(Preconnects::class, 'pprh_post_domain_names' ) );
		remove_action( 'wp_ajax_nopriv_pprh_post_domain_names', array( Preconnects::class, 'pprh_post_domain_names' ) );

		$prec = new \PPRH\Preconnects();
		$prec->initialize();

		$loaded_ajax = has_action( 'wp_ajax_pprh_post_domain_names' );
		$stop_load_ajax_for_all = has_action( 'wp_ajax_nopriv_pprh_post_domain_names' );

		$this->assertEquals(true, $loaded_ajax);
		$this->assertEquals(false, $stop_load_ajax_for_all);

		remove_action( 'wp_ajax_pprh_post_domain_names', array(Preconnects::class, 'pprh_post_domain_names' ) );
		remove_action( 'wp_ajax_nopriv_pprh_post_domain_names', array(Preconnects::class, 'pprh_post_domain_names' ) );
	}

//	public function test_load_preconnects_for_all_users(): void {
//		update_option('pprh_preconnect_autoload', 'true');
//		update_option('pprh_preconnect_set', 'false');
//		update_option( 'pprh_preconnect_allow_unauth', 'true' );
//
//		remove_action( 'wp_ajax_pprh_post_domain_names', array(Preconnects::class, 'pprh_post_domain_names' ) );
//		remove_action( 'wp_ajax_nopriv_pprh_post_domain_names', array( Preconnects::class, 'pprh_post_domain_names' ) );
//
//		$prec = new \PPRH\Preconnects();
//		$prec->initialize();
//
//		$loaded_ajax = has_action( 'wp_ajax_pprh_post_domain_names' );
//		$stop_load_ajax_for_all = has_action( 'wp_ajax_nopriv_pprh_post_domain_names' );
//
//		$this->assertEquals(true, $loaded_ajax);
//		$this->assertEquals(true, $stop_load_ajax_for_all);
//
//		remove_action( 'wp_ajax_pprh_post_domain_names', array(Preconnects::class, 'pprh_post_domain_names' ) );
//		remove_action( 'wp_ajax_nopriv_pprh_post_domain_names', array( Preconnects::class, 'pprh_post_domain_names' ) );
//	}



//	public function test_pprh_post_domain_names(): void {}


//	public function test_create_hint(): void {}

//	public function test_update_options(): void {}

}