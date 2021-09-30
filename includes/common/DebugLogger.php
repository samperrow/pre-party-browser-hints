<?php

namespace PPRH;

if ( ! defined('ABSPATH' ) ) {
	exit;
}

class DebugLogger {

	public function log_error( $message ) {
		$exception_msg = $this->get_msg_from_exception( $message );
		$input       = $exception_msg;
		$input      .= \PPRH\Utils\Utils::get_debug_info() . "\n\n\n";

		$transient = \get_transient( 'pprh_debug_logger' );
		$transient .= ( false === $transient ) ? '' : $input;
		\set_transient( 'pprh_debug_logger', $transient, 60 );
	}

	private function get_msg_from_exception( $message ):string {
		if ( is_array( $message ) ) {
			$exception_str = json_encode( $message );
		} else {
			$exception_str = $message . "\n";
			$exception     = new \Exception();

			if ( method_exists( $exception, 'getMessage' ) ) {
				$exception_str .= $exception->getMessage();
			}

			if ( method_exists( $exception, 'getFile' ) ) {
				$exception_str .= $exception->getFile();
			}

			if ( method_exists( $exception, 'getTraceAsString' ) ) {
				$exception_str .= $exception->getTraceAsString();
			}
		}

		return "Error: $exception_str\n";
	}

}
