<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pro {

    private $license_url;
//    private $license_key;
    private $license_status;
    private $license_email;
    private $license_username;

    public function __construct() {
        $this->license_url      = 'https://sphacks.io';
//        $this->license_key      = get_option( 'pprh_license_key' );
        $this->license_status   = get_option( 'pprh_license_status' );
        $this->license_email    = get_option( 'pprh_license_email' );
        $this->license_username = get_option( 'pprh_license_username' );

        $this->upgrade_to_pro();
    }

    function get_user_data() {
        $user_info = wp_get_current_user()->data;
        return array(
            'site' => home_url(),
            'name' => $user_info->user_nicename,
            'email' => $user_info->user_email
        );
    }

    public function upgrade_to_pro() {
        $license_key = get_option( 'pprh_license_key' );
        ?>

        <div class="pprh-content upgrade">

            <h2>Enjoy Post-Specific Hints, Inline Hint Editing, and More</h2>

            <p>The version of Pre* Party Resource Hints which you are using allows for resource hints to be implemented
                only on a global basis- in which a group of hints are used on every page, without consideration of the
                resources used on each page. This works reasonably well for the hints preconnect and dns-prefetch, however
                preload, prefetch, and especially prerender are not well suited to be loaded universally across all
                pages, due to the fact that these hints would load large amounts of data, which would usually only serve to slow down your visitors.</p>

            <p>To fully allow the maximum effectiveness of prefetch, preload, and prerender, these hints must be implemented on a per-post/page basis to ensure the user only loads the hint data when needed. I
                have spent the past several months creating a new version of this plugin which has this ability. This
                has taken much more time than I had anticipated, so much so that I am forced to charge a small amount ($9
                USD) for this enhanced version. The benefits of this version include the following:</p>

            <ul style="margin-left: 15px;">
                <li>Create resource hints which can be used on specific posts/pages</li>
                <li>Automatic creation of preconnect hints will be automatically generated, also on a per-post basis.</li>
                <li>Ability to update existing resource hints with inline table editing.</li>
                <li>Improved UI with 100% Ajax-powered tables- creating, editing, and deleting hints doesn't require a page refresh.</li>
                <li>Improved performance, code quality, simpler architecture.</li>
            </ul>


            <p><b>(create youtube video demonstrating 2.0)</b></p>

            <div style="text-align: center; margin-bottom: 50px;">
                <span id="pprh-checkout" class="thickbox button button-primary">Upgrade to Pre* Party Pro!</span>
            </div>

            <form action="" method="post">

                <h3>License key:</h3>
                <input type="text" name="pprh_lic_key" value="<?php echo $license_key; ?>" size="40"/>
                <input type="hidden" name="slm_action" value="slm_activate"/>

                <input type="submit" name="pprh_activate_license" class="button button-primary" value="Activate"/>
            </form>

        </div>

        <?php

        if ( isset( $_POST['pprh_lic_key'] ) ) {
            $msg = $this->prepare_license_action('slm_activate' );
            echo '<p>' . $msg . '</p>';
        }
    }



    public function prepare_license_action( $action ) {
        // TODO: clean this hint
        $license_key = $_POST['pprh_lic_key'];
        $data = array(
            'success' => false,
        );

        if ( empty( $license_key  ) ) {
            return false;
        }

        $api_params = array(
            'slm_action'       => $action,
            'pprh_license_key' => $license_key,
            'domain'           => get_site_url()
        );

        $query    = esc_url_raw( add_query_arg( $api_params, $this->license_url ) );
        $response = wp_remote_get(
            $query,
            array(
                'timeout'   => 30,
                'sslverify' => true,
            )
        );

        if ( is_array( $response ) && isset( $response['response'] ) ) {
            $header = $response['headers'];
            $body = $response['body'];

            if ( 200 === wp_remote_retrieve_response_code($response) ) {
                $data = json_decode( $body, true );
            }
        }

        return $this->output( $data );
    }

    public function output( $response ) {
        $msg = '';

        if ( $response['success'] ) {
            $msg = 'The following message was returned from the server: ' . $response['message'] . '. You should now be receiving a popup containing a zip file containing the pro version (Please enable popups temporarily to receive this). Please use that zip file to install the plugin, once that is successful you may delete this free version. Please note that both versions use the same database table, so your resource hints will be preserved.';
            $url = $response['zip_url'];
            echo '<script>window.open("' . $url . '", "_blank");</script>';
            $this->update_options( $response );
        } else {
            $msg = 'The following message was returned from the server: ' . $response['message'];
        }
        return $msg;
    }


    public function update_options( $data ) {

        update_option( 'pprh_license_key', $data['license_key'] );
        update_option( 'pprh_license_status', $data['status'] );
        update_option( 'pprh_license_email', $data['email'] );
    }


}

new Pro();