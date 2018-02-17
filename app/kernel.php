<?php

/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!! BOOTSTRAP FILE AUTOMATICALLY LOADED                              !!!!!
 * !!!!! Make sure to properly link the framework.                        !!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
define('ROOT_DIR', __DIR__ . '/..');
require ROOT_DIR . '/vendor/autoload.php';

use Zephyrus\Application\Configuration;
use Zephyrus\Application\Bootstrap;
use Zephyrus\Application\Session;
use Zephyrus\Network\Router;
use Zephyrus\Security\IntrusionDetection;

$router = new Router();

include(Bootstrap::getHelperFunctionsPath());
if (Configuration::getApplicationConfiguration('env') == 'prod') {
    include('handlers.php');
}
Bootstrap::start();
Session::getInstance()->start();

/**
 * Defines what to do when an attack attempt (mainly XSS and SQL injection) is
 * detected in the application. The impact value represents the severity of the
 * attempt. The code below only logs the attempt in the security.log when impact
 * is equal or higher than 10. Do nothing more to limit false positive effect on
 * legit users. IntrusionDetection class is a wrapper of the expose library.
 *
 * @see https://github.com/enygma/expose
 */
if (Configuration::getSecurityConfiguration('ids_enabled')) {
    IntrusionDetection::getInstance()->onDetection(function($data) {
        if ($data['impact'] >= 10) {
            // Do something (logs, ...)
        }
    });
}
