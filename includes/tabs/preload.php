<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

new Preload();

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
                            $this->save_user_options();
                            $this->allow_preloading();
                            $this->preload_delay();
                            $this->set_ignoreKeywords();
                            $this->set_max_RPS();
                            $this->set_hover_delay();

                        ?>
                    </tbody>
                </table>
                <div class="text-center">
                    <input type="submit" name="pprh_save_preload" class="button button-primary" value="<?php esc_attr_e( 'Save Preload Changes', 'pprh' ); ?>" />
                </div>
            </form>
        </div>
        <?php
    }

    public function save_user_options() {

        if ( isset( $_POST['pprh_save_preload'] ) ) {

            if ( check_admin_referer( 'pprh_save_preload_options', 'pprh_admin_preload_nonce' ) ) {

                $preload_opts = array(
                    'allow'          => Utils::strip_non_alphanums( $_POST['pprh_preload_allow'] ),
                    'delay'          => Utils::strip_non_alphanums( $_POST['pprh_preload_delay'] ),
                    'ignoreKeywords' => esc_html( $_POST['pprh_preload_ignoreKeywords'] ),
                    'maxRPS'         => Utils::strip_non_alphanums( $_POST['pprh_preload_maxRPS'] ),
                    'hoverDelay'     => Utils::strip_non_alphanums( $_POST['pprh_preload_hoverDelay'] ),
                );
                $json = json_encode( $preload_opts, false );
                update_option( 'pprh_preload', $json );
            }
        }
    }


    public function allow_preloading() {
        $allow = Utils::get_option( 'pprh_preload', 'allow', 'false' );
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
                    <select name="pprh_preload_allow">
                        <option value="true" <?php echo esc_html( ( $allow === 'true' ? 'selected=selected' : '' ) ); ?>>
                            <?php esc_html_e( 'Yes', 'pprh' ); ?>
                        </option>
                        <option value="false" <?php echo esc_html( ( $allow === 'false' ? 'selected=selected' : '' ) ); ?>>
                            <?php esc_html_e( 'No', 'pprh' ); ?>
                        </option>
                    </select>
                </label>
            </td>
        </tr>

        <?php
    }

    public function preload_delay() {
        $delay = Utils::get_option( 'pprh_preload', 'delay', '0' );
        ?>
        <tr>
            <th><?php esc_html_e( 'Preload initiation delay:', 'pprh' ); ?></th>

            <td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'Start prefetching after a delay. Will be started when the browser becomes idle. Default value is 0 milliseconds.', 'pprh' ); ?></span>
				</span>
            </td>

            <td>
                <label>
                    <input name="pprh_preload_delay" type="text" value="<?php esc_html_e( $delay ); ?>" />
                </label>
            </td>
        </tr>

        <?php
    }

    public function set_ignoreKeywords() {
        $ignoreKeywords = Utils::get_option( 'pprh_preload', 'ignoreKeywords', '' );
        ?>
        <tr>
            <th><?php esc_html_e( 'Ignore these keywords:', 'pprh' ); ?></th>

            <td>
				<span class="pprh-help-tip-hint">
					<span><?php esc_html_e( 'This should be a comma separated series of keywords to ignore from prefetching. Example: "/logout","/cart","about.html","sample.png","#"', 'pprh' ); ?></span>
				</span>
            </td>

            <td>
                <label>
                    <input name="pprh_preload_ignoreKeywords" type="text" placeholder="Ex: '/logout', '/cart'" value="<?php esc_html_e( $ignoreKeywords ); ?>" />
                </label>
            </td>
        </tr>

        <?php
    }


    public function set_max_RPS() {
        $maxRPS = Utils::get_option( 'pprh_preload', 'maxRPS', '3' );
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
                    <input name="pprh_preload_maxRPS" type="text" value="<?php esc_html_e( $maxRPS ); ?>" />
                </label>
            </td>
        </tr>

        <?php
    }

    public function set_hover_delay() {
        $hoverDelay = Utils::get_option( 'pprh_preload', 'hoverDelay', '0' );
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
                    <input name="pprh_preload_hoverDelay" type="text" value="<?php esc_html_e( $hoverDelay ); ?>" />
                </label>
            </td>
        </tr>
        <?php
    }


}


// cite https://github.com/gijo-varghese/flying-pages