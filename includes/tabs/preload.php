<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

//new Preload();

class Preload {

    public function __construct() {
        $this->load();
    }

    public function load() {
        ?>
        <div id="pprh-preload" class="pprh-content">
            <h2 style="margin-top: 30px;"><?php esc_html_e( 'Preload Navigation Links', 'pprh' ); ?></h2>
            <form method="post" action="<?php echo admin_url(); ?>admin.php?page=pprh-plugin-settings">
                <table class="pprh-settings-table">
                    <tbody>
                        <?php
                            wp_nonce_field( 'pprh_save_preload_options', 'pprh_admin_preload_nonce' );
//                            $this->save_user_options();
                            $this->allow_preloading();
                            $this->preload_delay();
                            $this->set_ignoreKeywords();
                            $this->set_max_RPS();
                            $this->set_hover_delay();

                        ?>
                    </tbody>
                </table>
                <input name="pprh_save_preload_options" type="submit" value="Save"/>
            </form>
        </div>
        <?php
    }

//    public function save_user_options() {
//
//        if ( isset( $_POST['pprh_save_preload_options'] ) ) {
//
//            if ( check_admin_referer( 'pprh_save_preload_options', 'pprh_admin_preload_nonce' ) ) {
//
//                if ( isset( $_POST['preload_allow'] ) ) {
//                    Utils::update_option( 'pprh_preload', 'allow', 'false' );
//                }
//            }
//        }
//    }


    public function allow_preloading() {
        ?>
        <tr>
            <th><?php esc_html_e( 'Allow for navigation links to be preloaded while in viewport?', 'pprh' ); ?></th>

            <td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'When navigation (anchor) links are being moused over, this feature will initiate a preload request for the URL in the link.', 'pprh' ); ?></span>
				</span>
            </td>

            <td>
                <label>
                    <select name="preload_allow">
                        <option value="true" <?php Utils::get_option_status( 'preload_allow', 'true' ); ?>>
                            <?php esc_html_e( 'Yes', 'pprh' ); ?>
                        </option>
                        <option value="false" <?php Utils::get_option_status( 'preload_allow', 'false' ); ?>>
                            <?php esc_html_e( 'No', 'pprh' ); ?>
                        </option>
                    </select>
                </label>
            </td>
            <td>
                <i><?php esc_html_e( '' ); ?></i>
            </td>
        </tr>

        <?php
    }

    public function preload_delay() {
        ?>
        <tr>
            <th><?php esc_html_e( 'Preload initiation delay:', 'pprh' ); ?></th>

            <td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'sdf', 'pprh' ); ?></span>
				</span>
            </td>

            <td>
                <label>
                    <input type="text" value="<?php get_option( 'preload_delay'); ?>" />
                </label>
            </td>
            <td>
                <i><?php esc_html_e( '' ); ?></i>
            </td>
        </tr>

        <?php
    }

    public function set_ignoreKeywords() {
        ?>
        <tr>
            <th><?php esc_html_e( 'Ignore these keywords:', 'pprh' ); ?></th>

            <td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This should be an array of keywords to ignore from prefetching. Example ["/logout","/cart","about.html","sample.png","#"]', 'pprh' ); ?></span>
				</span>
            </td>

            <td>
                <label>
                    <input type="text" value="<?php get_option( 'preload_ignoreKeywords' ); ?>" />
                </label>
            </td>
            <td>
                <i><?php esc_html_e( '' ); ?></i>
            </td>
        </tr>

        <?php
    }


    public function set_max_RPS() {
        ?>
        <tr>
            <th><?php esc_html_e( 'Maximum requests per second the preload queue should process:', 'pprh' ); ?></th>

            <td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Set to 0 to process all requests immediately (without queue). Defaults to 3.', 'pprh' ); ?></span>
				</span>
            </td>

            <td>
                <label>
                    <input type="text" value="<?php get_option( 'pprh_preload_maxRPS' ); ?>" />
                </label>
            </td>
            <td>
                <i><?php esc_html_e( '' ); ?></i>
            </td>
        </tr>

        <?php
    }

    public function set_hover_delay() {
        ?>
        <tr>
            <th><?php esc_html_e( 'Delay in prefetching links on mouse hover (in milliseconds)', 'pprh' ); ?></th>

            <td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Defaults to 50 milliseconds', 'pprh' ); ?></span>
				</span>
            </td>

            <td>
                <label>
                    <input type="text" value="<?php get_option( 'pprh_preload_hoverDelay' ); ?>" />
                </label>
            </td>
            <td>
                <i><?php esc_html_e( '' ); ?></i>
            </td>
        </tr>

        <?php
    }


}


// cite https://github.com/gijo-varghese/flying-pages