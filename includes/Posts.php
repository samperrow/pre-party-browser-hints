<?php

namespace PPRH;

use PPRH\Utils\Utils;
use PPRH\Utils\Sanitize;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Posts {

    private $show_posts_on_front;

	public function __construct( bool $show_posts_on_front ) {
		$this->show_posts_on_front = $show_posts_on_front;
		\add_action( 'load-post.php', array( $this, 'create_post_meta_box' ) );
		\add_filter( 'pprh_apply_ajaxops_action', array( $this, 'post_reset_action' ), 10, 2 );
	}

	public function create_post_meta_box() {
        $opt = get_option( 'pprh_pro_options' );
		$modal_types = $opt['post_modal_types'] ?? array( 'post', 'page' );
		$id          = 'pprh-poststuff';
		$title       = 'Pre* Party Resource Hints';
		$callback    = array( $this, 'create_metabox' );
		$context     = 'normal';
		$priority    = 'low';
		$screens     = Sanitize::clean_string_array( $modal_types );

		if ( Utils::isArrayAndNotEmpty( $screens ) ) {
			foreach ( $screens as $screen ) {
				\add_meta_box( $id, $title, $callback, $screen, $context, $priority );
			}
		}
	}

	public function create_metabox() {
		$title = $this->shorten_url( \get_the_title() );
		echo '<div id="pprh-wrapper"><h3>';
		echo \esc_html( $title ) . '</h3>';
		Utils::show_notice( '', true );
		$insert_hints = new \PPRH\InsertHints( 2 );
		$insert_hints->markup();
		echo '</div>';
		unset( $insert_hints );
	}

	// this method is only called from the post-edit pages.
	public function post_reset_action( $post_id, $action ):\stdClass {
		if ( \PPRH\str_contains($action, 'preconnect')) {
			$hint_type = 'preconnect';
            $op_code = 5;
		} elseif ( \PPRH\str_contains($action, 'preload')) {
			$hint_type = 'preload';
			$op_code = 6;
		} elseif ( \PPRH\str_contains($action, 'prerender')) {
			$hint_type = 'prerender';
			$op_code = 7;
		}

		if ( isset( $hint_type, $op_code ) ) {
            $settings_save = new \PPRH\SettingsSave( $this->show_posts_on_front );
			$new_hint_data = $settings_save->reset_autoset_hints( $hint_type, $post_id, $op_code );
			return \PPRH\DAO::create_db_result( '', $op_code, 0, array() );
		}

		return \PPRH\DAO::create_db_result( "error resetting this post's $hint_type value. Please either clear your cache and try again, or report the issue to support.", 0, 0, array() );
	}

	public function shorten_url( string $str ):string {
		return esc_html( ( strlen( $str ) > 25 ) ? substr( $str, 0, 25 ) . '...' : $str );
	}

}
