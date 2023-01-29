<?php

namespace PPRH;

//use PPRH\Utils\Utils;

if ( ! defined('ABSPATH' ) ) {
	exit;
}

class DebugLogger {

	private $transient_name;
	private $debug_enabled;

	public function __construct() {
		$this->transient_name = 'pprh_pro_debug_log';
		$this->debug_enabled = \get_option( 'pprh_pro_debug_enabled', 'false' );
	}

	public static function logger( bool $error, $message ) {
		return (new DebugLogger)->log_msg( $error, $message );
	}

	public function log_msg( bool $error, $message ):bool {
		$exception_msg = $this->get_msg_from_exception( $error, $message );
		$debug_msg     = $exception_msg . \PPRH\Utils\Debug::get_debug_info() . "\n\n\n";
		$send_email    = $this->check_to_send_debug_email( $error, $debug_msg );

		// if transient is past expiration, send email to admin and delete transient.
		$this->send_debug_email( $send_email, $this->transient_name, $debug_msg );
		return true;
	}

	private function get_msg_from_exception( bool $error, $message ):string {
		if ( $error && $message instanceof \Exception ) {
			$exception     = new \Exception();
			$exception_str = $message . "\n";
			$exception_str .= $exception->getMessage();
			$exception_str .= $exception->getFile();
			$exception_str .= $exception->getTraceAsString();
		}
		elseif ( is_array( $message ) || is_object( $message ) ) {
			$exception_str = json_encode( $message );
		} elseif ( is_string( $message ) ) {
			$exception_str = $message;
		} else {
			$exception_str = 'Error!';
		}

		$exception_str .= "\n";
		return ( $error ) ? "Error: $exception_str" : "Debug msg: $exception_str";
	}

	public function check_to_send_debug_email( bool $error, string $debug_msg ):bool {
		$transient_expire_time = (int) \get_option( "_transient_timeout_{$this->transient_name}" );
		$time_now              = time();
		$debug_log             = \get_option( "_transient_{$this->transient_name}" );
		$debug_msg .= $debug_log;
		return ( $time_now >= $transient_expire_time && $error && ! empty( $debug_msg ) );
	}

	private function send_debug_email( bool $send_email, string $transient_name, string $debug_msg ) {
		if ( PPRH_RUNNING_UNIT_TESTS || 'true' !== $this->debug_enabled ) {
			return;
		}

		if ( $send_email ) {
			\wp_mail( PPRH_EMAIL, 'Error Report', $debug_msg );
			\delete_transient( $transient_name );
		} else {
			\set_transient( $this->transient_name, $debug_msg, DAY_IN_SECONDS / 12);
		}
	}


}
