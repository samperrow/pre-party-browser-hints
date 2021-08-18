<?php
declare(strict_types=1);

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ClientAjaxInit {

	private $allow_user = false;
	private $doing_ajax = false;

	protected $hint_type;

	public $reset_pro;
	public $callback;
	public $args;

	public function __construct( string $hint_type, array $args = array() ) {
		if ( 'preconnect' === $hint_type ) {
			$this->args = array(
				'hints_set_name'           => 'pprh_preconnect_set',
				'script_filepath'          => PPRH_REL_DIR . 'js/preconnect.js',
				'allow_unauth_option_name' => 'pprh_preconnect_allow_unauth'
			);
		}
		elseif ( 'preload' === $hint_type ) {
			$this->args = $args;
		}

		$this->hint_type = $hint_type;
		$this->callback  = "pprh_{$this->hint_type}_callback";
		\add_action( 'wp_loaded', array( $this, 'initialize' ), 10, 0 );
	}

	public function initialize() {
		if ( empty( $this->args ) ) {
			return;
		}

		$allow_unauth_option       = ( 'true' === \get_option( $this->args['allow_unauth_option_name'] ) );
		$reset_preconnects_option  = ( 'false' === \get_option( $this->args['hints_set_name'] ) );
		$user_logged_in            = \is_user_logged_in();
		$this->reset_pro           = \apply_filters( 'pprh_check_to_reset', $this->hint_type );

		$this->allow_user = ( $allow_unauth_option || $user_logged_in );
		$this->doing_ajax = \wp_doing_ajax();

		$this->initialize_ctrl( $this->allow_user, $reset_preconnects_option, $this->doing_ajax, $this->reset_pro );
	}

	public function initialize_ctrl( bool $allow_user, bool $reset_preconnects_option, bool $doing_ajax, $reset_pro ):bool {
		if ( $doing_ajax ) {
			$this->load_ajax_callbacks( $allow_user );
			return false;
		} else {
			$reset_hints  = ( is_null( $reset_pro ) ) ? $reset_preconnects_option : $reset_pro;
			$perform_reset = ( $allow_user && $reset_hints );
		}

		if ( ! $perform_reset ) {
			return false;
		}

		\add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		return true;
	}

	public function load_ajax_callbacks( bool $allow_user ) {
		if ( $allow_user ) {
			\add_action( "wp_ajax_nopriv_{$this->callback}", array( $this, $this->callback ) );		// not logged in
		}
		\add_action( "wp_ajax_{$this->callback}", array( $this, $this->callback ) );				// for logged in users
	}

	public function enqueue_scripts() {
		$js_object = $this->create_js_object( time() );
		$script_file = $this->args['script_filepath'];

		\wp_register_script( 'pprh_create_hints_js', PPRH_REL_DIR . 'js/create-hints.js', null, PPRH_VERSION, true );
		\wp_enqueue_script( 'pprh_create_hints_js' );

		\wp_register_script( "pprh_$this->hint_type", $script_file, null, PPRH_VERSION, true );
		\wp_localize_script( "pprh_$this->hint_type", 'pprh_data', $js_object );
		\wp_enqueue_script( "pprh_$this->hint_type" );
	}

	public function create_js_object( int $time ):array {
		$js_arr = array(
			'hints'      => array(),
			'nonce'      => \wp_create_nonce( 'pprh_ajax_nonce' ),
			'timeout'    => PPRH_IN_DEV ? 1000 : 7000,
			'admin_url'  => \admin_url() . 'admin-ajax.php',
			'start_time' => $time
		);

		if ( $this->reset_pro ) {
			$js_arr = \apply_filters( 'pprh_append_hint_object', $js_arr );
		}

		return $js_arr;
	}

	public function pprh_preload_callback() {
		$this->pprh_callback_fn( 'preload' );
	}

	public function pprh_preconnect_callback() {
		$this->pprh_callback_fn( 'preconnect' );
	}


	private function pprh_callback_fn( string $hint_type ) {
		if ( isset( $_POST['pprh_data'] ) && $this->doing_ajax && $this->allow_user ) {
			\check_ajax_referer( 'pprh_ajax_nonce', 'nonce' );

			$this->hint_type = $hint_type;
			$client_ajax_response = new ClientAjaxResponse( $this->hint_type );
			$results = $client_ajax_response->protected_post_domain_names();

			if ( PPRH_IN_DEV ) {
				echo \wp_json_encode( $results );
			}

			\wp_die();
		}
	}

}
