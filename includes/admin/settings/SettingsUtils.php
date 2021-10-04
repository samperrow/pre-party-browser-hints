<?php
declare(strict_types=1);

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SettingsUtils {

	public function turn_textarea_to_array( $text ) {
		$clean_text = preg_replace( '/[\'<>^\"\\\]/', '', $text );
		return explode( "\r\n", $clean_text );
	}

	protected function get_each_keyword( $keywords ) {
		if ( is_null( $keywords ) ) {
			return '';
		}

		$keywords = explode( ', ', $keywords );
		$str   = '';
		$count = count( $keywords );
		$idx   = 0;

		foreach ( $keywords as $keyword ) {
			$idx++;
			$str .= $keyword;

			if ( $idx < $count ) {
				$str .= "\n";
			}
		}

		return $str;
	}

	public function esc_get_option( string $option ) {
		return \esc_html( \get_option( $option ) );
	}

	public function does_option_match( string $option, string $match, string $output ) {
		$option_value = $this->esc_get_option( $option );
		return ( ( $option_value === $match ) ? $output : '' );
	}

	public function update_checkbox_option( array $post, string $option_name ):string {
		$update_val = $post[ $option_name ] ?? 'false';
		\PPRH\Utils\Utils::update_option( $option_name, $update_val );
		return $update_val;
	}

}
