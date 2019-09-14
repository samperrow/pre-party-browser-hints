<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PPRH_Create_Hints {

	public $results = array(
		'action'           => '',
		'result'           => '',
		'globalHintExists' => '',
		'removedDupHint'   => '',
		'url_parsed'       => '',
	);

	public function __construct( $nonce_action, $nonce_name ) {
		if ( isset( $_POST['hint_data'] ) ) {

			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				check_ajax_referer( $nonce_action, $nonce_name );
			} else {
				check_admin_referer( $nonce_action, $nonce_name );
			}
			$data = json_decode( wp_unslash( $_POST['hint_data'] ) );
			$this->init( $data );
		}
	}


	private function init( $data ) {
		$urls = $data->url;

		foreach ( $urls as $url ) {
			$this->create_hint( $url, $data->hint_type, $data->post_id );
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$this->update_options();
		}
		$this->complete();
	}

	private function create_hint( $url, $hint_type, $post_id ) {
		$this->set_hint_type( $hint_type );
		$this->set_url( $url );
		$this->get_file_type();
		$this->set_crossorigin();
		$this->set_as_attr();
		$this->set_type_attr();
		$this->set_post_id( $post_id );
	}

	private function set_hint_type( $hint_type ) {
		$this->hint_type = PPRH_Misc::clean_hint_type( $hint_type );
	}

	private function set_url( $url ) {
		$this->url = strtolower( filter_var( str_replace( ' ', '', $url ), FILTER_SANITIZE_URL ) );

		if ( preg_match( '/(dns-prefetch|preconnect)/', $this->hint_type ) ) {
			$this->parse_for_domain_name();
		}
	}

	private function parse_for_domain_name() {
		$orig_url   = $this->url;
		$parsed_url = wp_parse_url( $this->url );

		if ( ! empty( $parsed_url['scheme'] ) ) {
			$this->url = $parsed_url['scheme'] . '://' . $parsed_url['host'];
			if ( $orig_url !== $this->url ) {
				$this->results['url_parsed'] = true;
			}
		} elseif ( '//' === substr( $this->url, 0, 2 ) ) {
			$this->url = '//' . $parsed_url['host'];
		} else {
			$this->url = '//' . $parsed_url['path'];
		}
	}

	private function get_file_type() {
		$basename = pathinfo( $this->url )['basename'];

		$this->file_type = strlen( strpbrk( $basename, '?' ) ) > 0
			? strrchr( explode( '?', $basename )[0], '.' )
			: strrchr( $basename, '.' );
	}

	private function set_crossorigin() {
		$this->crossorigin = ( isset( $_POST['crossorigin'] ) ) ? $_POST['crossorigin'] : ( preg_match( '/fonts.(googleapis|gstatic).com/i', $this->url ) || preg_match( '/(.woff|.woff2|.ttf|.eot)/', $this->file_type ) ) ? ' crossorigin' : '';
	}

	private function set_as_attr() {

		$media_types = array(
			'.js'    => 'script',
			'.css'   => 'style',
			'.mp3'   => 'audio',
			'.mp4'   => 'video',
			'.vtt'   => 'track',
			'.swf'   => 'embed',
			'.woff'  => 'font',
			'.woff2' => 'font',
			'.ttf'   => 'font',
			'.eot'   => 'font',
			'.jpg'   => 'image',
			'.jpeg'  => 'image',
			'.png'   => 'image',
			'.svg'   => 'image',
			'.webp'  => 'image',
		);

		$this->as_attr = ( isset( $_POST['as_attr'] ) && ! empty( $_POST['as_attr'] ) ) ? $_POST['as_attr'] : $this->get_file_type_mime( $media_types );
	}

	private function set_type_attr() {

		$mimes = array(
			'.woff'  => 'font/woff',
			'.woff2' => 'font/woff2',
			'.ttf'   => 'font/ttf',
			'.eot'   => 'font/eot',
		);

		$this->type_attr = ( isset( $_POST['type_attr'] ) && ! empty( $_POST['type_attr'] ) ) ? $_POST['type_attr'] : $this->get_file_type_mime( $mimes );
	}

	private function set_post_id( $post_id ) {
		if ( ! is_null( $post_id ) ) {
			$this->post_id = PPRH_Misc::pprh_strip_non_alphanums( $post_id );
		} elseif ( isset( $_POST['UseOnHomePostsOnly'] ) ) {            // if home page is set to display recent posts.
			$this->post_id = '0';
		} else {                                                        // global hint.
			$this->post_id = 'global';
		}
	}

	private function get_file_type_mime( $file_types ) {

		foreach ( $file_types as $key => $val ) {
			if ( $key === $this->file_type ) {
				return $val;
			}
		}
		return '';
	}

	private function complete() {

		// if dup hint is being added, should halt the addition and throw warning msg.
		$this->check_for_duplicate_post_hint();

		if ( $this->results['globalHintExists'] ) {
			if ( 'global' === $this->post_id ) {
				$this->remove_duplicate_hints();
			} else {
				return;
			}
		}

		$this->insert_hints();
		return $this->results;
	}



	private function check_for_duplicate_post_hint() {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';

		if ( 'global' === $this->post_id ) {
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE hint_type = %s AND url = %s", $this->hint_type, $this->url );
		} else {
			$sql = $wpdb->prepare(
				"SELECT COUNT(*) FROM $table WHERE hint_type = %s AND url = %s AND (post_id = %s OR post_id = %s)",
				$this->hint_type,
				$this->url,
				$this->post_id,
				'global'
			);
		}

		$count = (int) $wpdb->get_var( $sql );

		$this->results['globalHintExists'] = ( $count > 0 ) ? true : false;
	}

	private function remove_duplicate_hints() {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';

		// if a global hint is being created, previous identical hints for posts can be deleted.
		$wpdb->delete(
			$table,
			array(
				'url'       => $this->url,
				'hint_type' => $this->hint_type,
			),
			array( '%s', '%s' )
		);
		$this->results['removedDupHint'] = 'true';
		// return $this->results;
	}

	private function insert_hints() {
		global $wpdb;
		$table = $wpdb->prefix . 'pprh_table';
		$current_user = wp_get_current_user()->display_name;

		$this->autoset = ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ? 1 : 0;

		$wpdb->insert(
			$table,
			array(
				'url'         => $this->url,
				'hint_type'   => $this->hint_type,
				'ajax_domain' => $this->autoset,
				'as_attr'     => $this->as_attr,
				'type_attr'   => $this->type_attr,
				'crossorigin' => $this->crossorigin,
				'post_id'     => $this->post_id,
				'created_by'  => $current_user,
			),
			array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		$this->results['action'] = 'added';
		$this->results['result'] = ( $wpdb->result ) ? 'success' : 'failure';
	}


	private function update_options() {

		if ( 'true' === get_option( 'pprh_reset_global_preconnects' ) ) {
			update_option( 'pprh_reset_global_preconnects', 'false' );
		}

		if ( '0' === $this->post_id ) {
			update_option( 'pprh_reset_home_preconnect', 'false' );
		} else {
			update_post_meta( $this->post_id, 'pprh_reset_preconnects', 'false' );
		}

	}

}
