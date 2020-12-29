<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

//define( 'PPRH_ABS_DIR', WP_PLUGIN_DIR . '/pre-party-browser-hints/' );

include_once '/Users/samperrow/Desktop/repos/WordPress/wp-load.php';
include_once WP_PLUGIN_DIR . '/pprh-pro/tests/autoload.php';

include_once PPRH_ABS_DIR . 'includes/utils.php';
include_once PPRH_ABS_DIR . 'pre-party-browser-hints.php';
include_once PPRH_ABS_DIR . 'includes/create-hints.php';


include_once PPRH_PRO_ABS_DIR . 'pprh-pro.php';
include_once PPRH_PRO_ABS_DIR . 'includes/utils-pro.php';