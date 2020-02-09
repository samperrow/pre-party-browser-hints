<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class PPRH_Pro {

    public function __construct() {

        $this->upgrade_to_pro();
//        $user_data = $this->get_user_data();

//        wp_register_script( 'pprh-checkout', plugins_url( PPRH_PLUGIN_FILENAME . '/js/proUpgrade.js' ),
//            null, PPRH_VERSION, true );
//        wp_localize_script( 'pprh-checkout', 'pprh_checkout_data', $user_data );
//        wp_enqueue_script( 'pprh-checkout' );
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
        ?>

        <div>

            <h2>Enjoy Post-Specific Hints, Inline Hint Editing, and More</h2>

            <p>The version of Pre* Party Resource Hints which you are using allows for resource hints to be implemented
                only on a global basis- in which a group of hints are used on every page, without consideration of the
                resources used on each page. This works well for the hints preconnect and dns-prefetch, however
                preload, prefetch, and especially prerender are not well suited to be loaded universally across all
                pages, due to the fact that these hints would load large amounts of data unnecessarily.</p>

            <p>To fully allow the maximum effectiveness of prefetch, preload, and prerender, these hints must be implemented on a per-post/page basis to ensure the user only loads the hint data when needed. I
                have spent the past several months creating a new version of this plugin which has this ability. This
                has taken much more time than I had anticipated, so much so that I am forced to charge a small amount ($9
                USD) for this enhanced version. The benefits of this version include the following:</p>

            <ul style="margin-left: 15px;">

                <li>Create resource hints on a per-post/page basis</li>
                <li>Preconnect hints will be automatically generated, also on a per-post basis, rather than globally.</li>
                <li>Ability to inline-edit existing resource hints easily.</li>
                <li>Improved UI with 100% Ajax-powered tables- creating, editing, and deleting hints doesn't require a
                    page refresh.</li>

            </ul>


            <p>(create youtube video demonstrating 2.0)</p>

            <div style="text-align: center; margin-bottom: 50px;">
                <span id="pprh-checkout" class="thickbox button button-primary">Start Using version 2.0 Now!</span>
            </div>


        </div>


        <?php
    }


}

new PPRH_Pro();