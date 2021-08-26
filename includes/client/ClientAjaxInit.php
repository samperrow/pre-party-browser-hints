<?php
declare(strict_types=1);

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ClientAjaxInit {

	private $allow_user = false;
	private $doing_ajax = false;

	public $args;

	public function __construct() {
		$this->args = array(
			'hints_set_name'           => 'pprh_preconnect_set',
			'script_filepath'          => PPRH_REL_DIR . 'js/preconnect.js',
			'allow_unauth_option_name' => 'pprh_preconnect_allow_unauth',
		);

		\add_action( 'wp_loaded', array( $this, 'initialize' ), 10, 0 );
	}

	public function initialize() {
		if ( empty( $this->args ) ) {
			return;
		}

		$allow_unauth_option       = ( 'true' === \get_option( $this->args['allow_unauth_option_name'] ) );
		$reset_preconnects_option  = ( 'false' === \get_option( $this->args['hints_set_name'] ) );
		$user_logged_in            = \is_user_logged_in();
		$this->allow_user = ( $allow_unauth_option || $user_logged_in );
		$this->doing_ajax = \wp_doing_ajax();

		$this->initialize_ctrl( $this->allow_user, $reset_preconnects_option, $this->doing_ajax );
	}

	public function initialize_ctrl( bool $allow_user, bool $reset_preconnects_option, bool $doing_ajax ):bool {
		if ( $doing_ajax ) {
			$this->load_ajax_callbacks( $allow_user );
			return false;
		}

		$perform_reset = ( $allow_user && $reset_preconnects_option );

		if ( ! $perform_reset ) {
			return false;
		}

		\add_action( 'wp_enqueue_scripts', array( $this, "enqueue_scripts" ) );
		return true;
	}

	public function load_ajax_callbacks( bool $allow_user ) {
		$callback = 'pprh_preconnect_callback';

		if ( $allow_user ) {
			\add_action( "wp_ajax_nopriv_{$callback}", array( $this, $callback ) );		// not logged in
		}
		\add_action( "wp_ajax_{$callback}", array( $this, $callback ) );				// for logged in users
	}

	public function enqueue_scripts() {
		$js_object = $this->create_js_object( time() );
		$script_file = $this->args['script_filepath'];

		\wp_register_script( 'pprh_preconnect_js', $script_file, null, PPRH_VERSION, true );
		\wp_localize_script( 'pprh_preconnect_js', 'pprh_data', $js_object );
		\wp_enqueue_script( 'pprh_preconnect_js' );
	}

	public function create_js_object( int $time ):array {
		return array(
			'hints'      => array(),
			'nonce'      => \wp_create_nonce( 'pprh_ajax_nonce' ),
			'timeout'    => PPRH_IN_DEV ? 1000 : 7000,
			'admin_url'  => \admin_url() . 'admin-ajax.php',
			'start_time' => $time
		);
	}

	public function pprh_preconnect_callback() {
		$this->pprh_callback_fn();
	}

	private function pprh_callback_fn() {
		if ( isset( $_POST['pprh_data'] ) && $this->doing_ajax && $this->allow_user ) {
			\check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );

			$client_ajax_response = new ClientAjaxResponse();
			$results = $client_ajax_response->protected_post_domain_names();

			if ( PPRH_IN_DEV ) {
				echo \wp_json_encode( $results );
			}

			\wp_die();
		}
	}

}
