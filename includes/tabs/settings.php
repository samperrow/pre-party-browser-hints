<?php

namespace PPRH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	public function __construct() {
	    $this->init();
//		$this->display_settings();
	}

	public function init() {

	    ?>
        <div id="pprh-settings" class="pprh-content">

            <h2 class="nav-tab-wrapper">
                <a class="nav-tab settings general-settings" href="">General Settings</a>
                <a class="nav-tab settings preconnect" href="">Preconnect</a>
                <a class="nav-tab settings preload" href="">Preload</a>
<!--                <a class="nav-tab settings prerender" href="">Prerender</a>-->
            </h2>

            <?php
                include_once PPRH_ABS_DIR . '/includes/tabs/general-settings.php';
                include_once PPRH_ABS_DIR . '/includes/tabs/preconnect-mu.php';
                include_once PPRH_ABS_DIR . '/includes/tabs/preload.php';
            ?>
        </div>

        <?php
    }




}

if ( is_admin() ) {
	new Settings();
}
