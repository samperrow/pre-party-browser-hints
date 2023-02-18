<?php

namespace PPRH;

//use \PPRH\Utils\Utils;

use PPRH\settings\SettingsView;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LoadAdmin {

	public $plugin_page;

    public $show_posts_on_front;

    public $pro_options;

	public function init( int $plugin_page ) {
		$this->plugin_page = $plugin_page;
		\add_action( 'admin_menu', array( $this, 'load_admin_menu' ) );

		$this->show_posts_on_front = ( 'posts' === \get_option( 'show_on_front', '' ) );
		$this->pro_options = \get_option( 'pprh_pro_options', array() );

		if ( $this->plugin_page > 0 ) {
			\add_action( 'admin_init', array( $this, 'add_settings_meta_boxes' ) );
			\add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_files' ) );
			\add_filter( 'set-screen-option', array( $this, 'pprh_set_screen_option' ), 10, 3 );
			$this->load_common_content();
			\apply_filters( 'pprh_load_pro_admin', $this->plugin_page );
		}
	}

	public function load_common_content() {
		$ajax_ops = new AjaxOps( $this->plugin_page );
		$ajax_ops->set_actions();
	}

    // icon not appearing with "disable all wp updates" plugin.
	public function load_admin_menu() {
		$settings_page = \add_menu_page(
			'Pre* Party Settings',
			'Pre* Party',
			'manage_options',
			PPRH_MENU_SLUG,
			array( $this, 'load_dashboard' ),
			PPRH_REL_DIR . 'images/lightning.png'
		);

		\add_action( "load-{$settings_page}", array( $this, 'screen_option' ) );
	}

	public function load_dashboard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			\wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		$dashboard = new Dashboard();
		$dashboard->show_plugin_dashboard( $this->plugin_page );
	}

	public function screen_option() {
		$args = array(
			'label'   => 'Resource hints per page: ',
			'option'  => 'pprh_per_page',
			'default' => 10
		);

		\add_screen_option( 'per_page', $args );
	}

	public function pprh_set_screen_option( $status, $option, $value ) {
		return ( 'pprh_per_page' === $option ) ? $value : $status;
	}

	// Register and call the CSS and JS we need only on the needed page.
	public function register_admin_files( $hook ) {
//		if ( str_contains( PPRH_ADMIN_SCREEN, $hook ) ) {

			$ajax_data = array(
				'nonce'     => wp_create_nonce( 'pprh_table_nonce' ),
				'admin_url' => admin_url()
			);

			\wp_register_script( 'pprh_admin_js', PPRH_REL_DIR . 'js/admin.js', array( 'jquery' ), PPRH_VERSION, true );
			\wp_localize_script( 'pprh_admin_js', 'pprh_data', $ajax_data );

			\wp_register_style( 'pprh_styles_css', PPRH_REL_DIR . 'css/styles.css', null, PPRH_VERSION, 'all' );
			\wp_enqueue_style( 'pprh_styles_css' );
			\wp_enqueue_script( 'pprh_admin_js' );
			\wp_enqueue_script( 'post' );			// needed for metaboxes
//		}
	}

	public function add_settings_meta_boxes() {
		$settings_view = new SettingsView( true );

		\add_meta_box(
            'pprh_general_metabox',
			'General Settings',
			array( $settings_view, 'general_markup' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);

		\add_meta_box(
            'pprh_preconnect_metabox',
			'Auto Preconnect Settings',
			array( $settings_view, 'preconnect_markup' ),
			PPRH_ADMIN_SCREEN,
			'normal',
			'low'
		);

		\add_meta_box(
            'pprh_prefetch_metabox',
            'Auto Prefetch Settings',
            array( $settings_view, 'prefetch_markup' ),
            PPRH_ADMIN_SCREEN,
            'normal',
            'low'
		);

        \add_meta_box(
            'pprh_preload_metabox',
            'Auto Preload Settings',
            array( $this, 'create_preload_metabox' ),
            PPRH_ADMIN_SCREEN,
            'normal',
            'low'
        );

        \add_meta_box(
            'pprh_prerender_metabox',
            'Auto Prerender Settings',
            array( $this, 'prerender_settings_markup' ),
            PPRH_ADMIN_SCREEN,
            'normal',
            'low'
        );
	}

    public function create_preload_metabox() {
		$enabled = ( 'true' === $this->pro_options['preload_enabled'] ) ? 'checked' : '';
		?>
        <table class="form-table"><tbody>

            <tr>
                <th scope="row"><?php \_e( 'Enable Auto Preload?', 'pprh-pro' ); ?><td>
                    <label for="preload_enabled"><input type="checkbox" name="preload_enabled" value="true" <?php echo $enabled; ?>/></label>
                    <p><?php \_e( 'This feature allows preload hints to be automatically created. Critical resources will be preloaded automatically.', 'pprh-pro' ); ?></p>
                </td>
            </tr>

			<?php if ( $this->show_posts_on_front ) { ?>
                <tr>
                    <th scope="row"><?php \_e( 'Reset Home Preload Links?', 'pprh-pro' ); ?></th>
                    <td>
                        <input type="submit" name="reset_home_preload" class="button-primary pprh-reset" data-text="reset auto preload hints used only on the home page?" value="Reset">
                        <p><?php \_e( 'This will reset automatically created preload hints on the home page. (This option only applies when the home page is set to display recent posts.)', 'pprh-pro' ); ?></p>
                    </td>
                </tr>
			<?php } ?>

            <tr>
                <th scope="row"><?php \_e( 'Reset Global Preload Links?', 'pprh-pro' ); ?></th>
                <td>
                    <input type="submit" name="reset_global_preload" class="button-primary pprh-reset" data-text="reset auto preload globally?" value="Reset">
                    <p><?php \_e( 'This will reset all of the automatically generated global preload hints, which are used on all posts and pages.', 'pprh-pro' ); ?></p>
                </td>
            </tr>
            </tbody></table>
		<?php
		return true;
    }

	public function prerender_settings_markup() {
		$prerender_enabled      = ( 'true' === $this->pro_options['prerender_enabled'] ) ? 'checked' : '';
		$enable_for_logged_in   = ( 'true' === $this->pro_options['prerender_enable_for_logged_in_users'] ) ? 'checked' : '';
		$auto_reset_days_option = $this->pro_options['prerender_auto_reset_days'];
		?>

		<table class="form-table">
			<tbody>

			<tr>
				<th scope="row"><?php \_e( 'Enable Auto Prerender?', 'pprh-pro' ); ?>
					<span class="pprh-help-tip-hint">
                        <span><?php \_e( 'This feature allows unique prerender hints to be automatically created for individual pages, based on each pages\' most common referer.', 'pprh-pro' ); ?></span>
                    </span>
				</th>

				<td>
					<label for="prerender_enabled">
						<input type="checkbox" class="toggleMetaBox" name="prerender_enabled" value="true" <?php echo $prerender_enabled; ?>/>
					</label>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php \_e( 'Enable analytics data to be recorded for logged in users?', 'pprh-pro' ); ?></th>
				<td>
					<label for="prerender_enable_for_logged_in_users">
						<input type="checkbox" name="prerender_enable_for_logged_in_users" value="true" <?php echo $enable_for_logged_in; ?>/>
					</label>
					<p><?php \_e( 'Keep this unchecked to have prevent logged in users from skewing the data. (Only the previous page and current page are recorded.)', 'pprh-pro' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php \_e( 'Number of days before automatically generated prerender hints are reset?', 'pprh-pro' ); ?></th>
				<td>
					<label for="prerender_auto_reset_days">
						<input type="number" step="1" min="0" max="180" name="prerender_auto_reset_days" value="<?php echo $auto_reset_days_option; ?>"/>
					</label>
					<p><?php \_e( 'Default is 30 days. This allows for the most accurate data to be used when creating prerender hints. To prevent any automatic resetting of prerender hints, set this option to 0 days.', 'pprh-pro' ); ?></p>
				</td>
			</tr>

			<?php if ( $this->show_posts_on_front ) { ?>
				<tr>
					<th scope="row"><?php \_e( 'Reset all prerender hints on home page?', 'pprh-pro' ); ?></th>
					<td>
						<input type="submit" name="reset_home_prerender" class="button-primary pprh-reset" data-text="manually reset home prerender hints?" value="Reset"/>
						<p><?php \_e( 'This will reset prerender hints on the home page, if sufficient data is available (see FAQ).', 'pprh-pro' ); ?></p>
					</td>
				</tr>
			<?php } ?>

			<tr>
				<th scope="row"><?php \_e( 'Reset all prerender hints on all pages/posts?', 'pprh-pro' ); ?></th>
				<td>
					<input type="submit" name="reset_global_prerender" class="button-primary pprh-reset" data-text="manually reset all prerender hints?" value="Reset"/>
					<p><?php \_e( 'This will reset the prerender hint on each pages/posts, if sufficient data is available (see FAQ).', 'pprh-pro' ); ?></p>
				</td>
			</tr>

			</tbody></table>
		<?php
		return true;
	}

}
