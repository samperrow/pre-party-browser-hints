<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


class GKTPP_Options {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'settings_page_init' ) );
		add_filter( 'set-screen-option', array( $this, 'apply_wp_screen_options' ), 1, 3 );
	}

	public function settings_page_init() {
		$settings_page = add_menu_page(
			' Pre* Party Settings',
			'Pre* Party',
			'manage_options',
			'gktpp-plugin-settings',
			array( $this, 'settings_page' ),
			plugins_url( '/pre-party-browser-hints/images/lightning.png' )
		);
		add_action( "load-{$settings_page}", array( $this, 'save_plugin_tabs' ) );
		add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
	}

	public function save_plugin_tabs() {
		if ( isset( $_POST['gktpp-settings-submit'] ) ) {
			check_admin_referer( 'gkt_preP-settings-page' );

			if ( ( '' !== $_POST['url'] ) && ( isset( $_POST['hint_type'] ) ) ) {
				$send_hints = new GKTPP_Insert_To_DB;
				$send_hints->insert_data_to_db();

				$url_parameters = isset( $_GET['tab'] ) ? 'updated=true&tab=' . $_GET['tab'] : 'updated=true';
				wp_safe_redirect( admin_url( 'admin.php?page=gktpp-plugin-settings&' . $url_parameters ) );
				exit();
			}
	    }
	}

	public function apply_wp_screen_options( $status, $option, $value ) {
        return ( 'gktpp_screen_options' === $option ) ? $value : $status;
	}

	public function admin_tabs( $current = 'insert-urls' ) {
		$tabs = array(
			'insert-urls' => 'Insert URLs',
			'info' => 'Information'
			);

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $tab => $name ) {
			$class = ( $tab === $current ) ? ' nav-tab-active' : '';
			echo "<a class='nav-tab$class' href='?page=gktpp-plugin-settings&tab=$tab'>" . esc_html( $name ) . "</a>";
		}
		echo '</h2>';
	}

	public function settings_page() {
		if ( ! is_admin() ) {
			exit;
		}
		global $pagenow;
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Pre* Party Plugin Settings', 'gktpp' ); ?></h2>
			<form method="post" action="<?php admin_url( 'admin.php?page=gktpp-plugin-settings' ); ?>">
				<?php ( isset( $_GET['tab'] ) ) ? $this -> admin_tabs( $_GET['tab'] ) : $this -> admin_tabs( 'insert-urls' ); ?>
			</form>
				<?php
				if ( 'admin.php' === $pagenow && 'gktpp-plugin-settings' === $_GET['page'] ) {
                    ( isset( $_GET['tab'] ) ) ? $tab = $_GET['tab'] : $tab = 'insert-urls';
                    
                    $hint_info = new GKTPP_Hint_Info();

					switch ( $tab ) {
						case 'insert-urls' :
							$callPrepareItems = new GKTPP_Table();
							$callPrepareItems->prepare_items();
						break;

						case 'info':
                            $hint_info->resource_hint_nav();
							$hint_info->show_dnsprefetch_info();
							$hint_info->show_prefetch_info();
							$hint_info->show_prerender_info();
							$hint_info->show_preconnect_info();
							$hint_info->show_preload_info();
						break;

						default:
							$callPrepareItems = new GKTPP_Table();
							$callPrepareItems->prepare_items();
						break;
					}
				}
				?>
		</div>
	<?php }

	public function screen_option() {
		$option = 'per_page';
		$args = array(
			'label'   => 'URLs',
			'default' => 10,
			'option'  => 'gktpp_screen_options',
		);

		add_screen_option( $option, $args );

		$this->resource_obj = new GKTPP_Table();
	}

	public static function url_updated( $status ) { 
        ?>
		<div class="inline notice notice-success is-dismissible">
			<p><?php esc_html_e( "Resource hints {$status} successfully." ); ?></p>
		</div>
        <?php 
    }

}

new GKTPP_Options();
