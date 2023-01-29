<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DisplayHintsPro {

	public $admin_url;

	public function __construct() {
		$this->admin_url = \admin_url();
		\add_filter( 'pprh_dh_get_columns', array( $this, 'dh_get_columns' ), 10, 1 );
		\add_filter( 'pprh_dh_get_sortortable_columns', array( $this, 'dh_get_sortortable_columns' ), 10, 1 );
		\add_filter( 'pprh_dh_get_post_link', array( $this, 'dh_get_post_link' ), 10, 1 );
	}

	public function dh_get_columns( $cols ) {
		$cols['post_id'] = __( 'Post Name', 'pprh' );
		return $cols;
	}

	public function dh_get_sortortable_columns( $cols ) {
		$cols['post_id'] = array( 'post_id', false );
		return $cols;
	}

	public function dh_get_post_link( array $hint ):string {
		$post_id = $hint['post_id'] ?? '';

		if ( 'global' === $post_id ) {
			$link = 'global';
		} elseif ( '0' === $post_id ) {
			$link = 'Home';
		} else {
			$post_title = \get_the_title( $post_id );
			$link       = ( ! empty( $post_title ) ? sprintf( '<a href="%spost.php?post=%s&action=edit">%s</a>', $this->admin_url, $post_id, $post_title ) : '-' );
		}

		return $link;
	}

}
