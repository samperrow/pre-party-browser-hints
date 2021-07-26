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
	private $time_now;

	public function __construct() {
		$this->file_path = PPRH_ABS_DIR . 'logs/errors.log';
		$this->time_now = Utils::get_current_datetime();
	}

	public function get_section_break( $section_break ) {
		if ( $section_break ) {
			return $this->section_break_marker;
		}
		return "";
	}

	public function reset_log_file() {
		$content = $this->time_now . $this->log_reset_marker;
		$fp = fopen( $this->file_path, 'wb' );
		fwrite( $fp, $content );
		fclose( $fp );
	}

	public function append_to_file( $content ) {
		$fp = fopen( $this->file_path,'ab' );
		fwrite( $fp, $content );
		fclose( $fp );
	}

	public function log_error( $message ) {
		$exception_msg = $this->get_msg_from_exception();
		$message .= "\n$exception_msg";
		$message .= $this->get_environment_info();
		$this->append_to_file( $message );
		\wp_mail( 'info@sphacks.io', 'Error', $message );
	}

	private function get_msg_from_exception():string {
		$exception_str = '';
		$exception = new \Exception( '' );

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


	public function get_environment_info():string {
		$browser = \PPRH\Utils::get_server_prop('HTTP_USER_AGENT' );
		$text = "\nEnvironment info: \n";
		$data = array(
			'Datetime'    => $this->time_now,
			'PHP_Version' => PHP_VERSION,
			'WP_version'  => get_bloginfo( 'version' ),
			'home_url'    => home_url(),
			'Browser'     => $browser,
			'PPRH version' => PPRH_VERSION
		);

		foreach ( $data as $item => $val ) {
			$text .= "$item: $val\n";
		}

		return $text;
	}

}