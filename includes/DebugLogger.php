<?php

namespace PPRH;

if ( ! defined('ABSPATH' ) ) {
	exit;
}

class DebugLogger {

	public $debug_enabled = false;
	public $debug_status = array( 'SUCCESS', 'STATUS', 'NOTICE', 'WARNING', 'FAILURE', 'CRITICAL' );
	public $section_break_marker = "\n----------------------------------------------------------\n\n";
	public $log_reset_marker = "-------- Log File Reset --------\n";
	public $file_path;

	public function __construct() {
		$this->file_path = PPRH_ABS_DIR . 'logs/errors.log';
	}

	public function get_section_break( $section_break ) {
		if ( $section_break ) {
			return $this->section_break_marker;
		}
		return '';
	}

	public function append_to_file( $content ) {
		$fp = fopen( $this->file_path,'ab' );
		fwrite( $fp, $content );
		fclose( $fp );
	}

	public function log_error( $message ) {
		$exception_msg = $this->get_msg_from_exception( $message );
		$message      .= "\n$exception_msg";
		$message      .= Utils::get_debug_info();
		$this->append_to_file( $message );
		Utils::send_email( PPRH_EMAIL, 'Error', $message );
	}

	private function get_msg_from_exception( string $message ):string {
		$exception_str = '';
		$exception     = new \Exception( $message );

		if ( method_exists( $exception, 'getMessage' ) ) {
			$exception_str .= $exception->getMessage();
		}

		if ( method_exists( $exception, 'getFile' ) ) {
			$exception_str .= $exception->getFile();
		}

		if ( method_exists( $exception, 'getTraceAsString' ) ) {
			$exception_str .= $exception->getTraceAsString();
		}

		return $exception_str;
	}

}
