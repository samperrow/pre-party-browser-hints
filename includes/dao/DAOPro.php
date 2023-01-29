<?php

namespace PPRH\DAO;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DAOPro extends DAOProFilters {

	// dao
	public static function get_post_id_from_url( string $url ):string {
		global $wpdb;
		$posts_table = PPRH_POST_TABLE;
		$url         = self::get_post_name_from_url( $url );
		$sql         = "SELECT ID FROM $posts_table WHERE post_name = %s AND post_status = %s";
		$post_id     = $wpdb->get_var( $wpdb->prepare( $sql, $url, 'publish' ), 0, 0 );

		if ( ! empty( $wpdb->last_error ) ) {
			\PPRH\DebugLogger::logger( true, $wpdb->last_error );
		}

		return $post_id ?? '0';
	}

	// dao
	public function get_all_active_posts( bool $show_posts_on_front ):array {
		global $wpdb;
		$post_table = PPRH_POST_TABLE;

		$posts = $wpdb->get_results(
			$wpdb->prepare( "SELECT ID AS post_id, post_type FROM $post_table WHERE post_status = %s AND post_type NOT IN (%s,%s)", 'publish', 'custom_css', 'nav_menu_item' )
		);

		$active_posts = self::array_items_to_int( $posts );

		// add the home page pseudo post ID if recent posts are displayed on home page.
		if ( $show_posts_on_front ) {
			$active_posts[] = self::create_assoc_post_obj( 0, 'post' );
		}

		return $active_posts;
	}

	// dao. used in Prerender.
	public static function get_all_postmeta_values( string $meta_key ):array {
		global $wpdb;
		$postmeta_table = PPRH_POSTMETA_TABLE;

		$meta_values = $wpdb->get_results(
			$wpdb->prepare( "SELECT post_id, meta_value FROM $postmeta_table WHERE meta_key = %s", $meta_key )
		);

		return $meta_values;
	}

	public static function get_all_db_tables( bool $is_multisite ) {
		$db_tables = array();

		if ( $is_multisite ) {
			$db_tables = self::get_multisite_tables();
		}

		$db_tables[] = PPRH_DB_TABLE;
		return $db_tables;
	}

	private static function get_multisite_tables():array {
		global $wpdb;
		$blog_table     = $wpdb->base_prefix . 'blogs';
		$ms_table_names = array();
		$multisite_table_exists = self::check_for_multisite_table( $blog_table );

		if ( ! $multisite_table_exists ) {
			return array();
		}

		$ms_blog_ids = $wpdb->get_results(
			$wpdb->prepare( "SELECT blog_id FROM $blog_table WHERE blog_id != %d", 1 )
		);

		if ( ! empty( $ms_blog_ids ) && count( $ms_blog_ids ) > 0 ) {
			foreach ( $ms_blog_ids as $ms_blog_id ) {
				$ms_table_name = $wpdb->base_prefix . $ms_blog_id->blog_id . '_pprh_table';
				$ms_table_names[] = $ms_table_name;
			}
		}

		return $ms_table_names;
	}

	// dao
	public static function update_table_schema( string $db_table ):array {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		if ( ! function_exists( 'dbDelta' ) ) {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$sql = "CREATE TABLE $db_table (
            id INT(9) NOT NULL AUTO_INCREMENT,
            url VARCHAR(255) DEFAULT '' NOT NULL,
            hint_type ENUM( 'dns-prefetch', 'prefetch', 'prerender', 'preconnect', 'preload' ) NOT NULL,
            status VARCHAR(55) DEFAULT 'enabled' NOT NULL,
            as_attr VARCHAR(55) DEFAULT '',
            type_attr VARCHAR(55) DEFAULT '',
            crossorigin VARCHAR(55) DEFAULT '',
			media VARCHAR(255) DEFAULT '',
            created_by VARCHAR(55) DEFAULT '' NOT NULL,
            post_id VARCHAR(55) DEFAULT 'global' NOT NULL,	
            auto_created INT(2) DEFAULT 0,
            PRIMARY KEY  (id)
        ) $charset;";

		return dbDelta( $sql, true );
	}

	public static function update_post_auto_hint( string $hint_type, string $post_id ) {
		if ( ! preg_match( '/preconnect|preload/', $hint_type ) ) {
			return false;
		}

		$meta_key  = "pprh_pro_{$hint_type}_set";
		$posts_arr = \get_option( $meta_key, array() );

		if ( ! in_array( $post_id, $posts_arr, true ) ) {
			$posts_arr[] = $post_id;
		}

		\PPRH\Utils\Utils::update_option( $meta_key, $posts_arr );

		return array(
			'updated_meta_key'   => $meta_key,
			'new_post_to_update' => $post_id
		);
	}

	// fired from client side.
	public static function get_post_auto_hints( string $post_id ) {

//		$meta_key  = "pprh_pro_preload_set";
		$preload_posts = \get_option( 'pprh_pro_preload_set', array() );
		$preconnect_posts = \get_option( 'pprh_pro_preconnect_set', array() );

		return array(
			'preloads'    => in_array( $post_id, $preload_posts, true ),
			'preconnects' => in_array( $post_id, $preconnect_posts, true ),
		);
	}


}
