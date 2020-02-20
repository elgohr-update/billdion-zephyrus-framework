<?php

// region Composer autoloading and Zephyrus instance
// This part is essential for the correct inclusion of the Framework on part
// with Composer dependency manager. Do not modify.
use Zephyrus\Network\Router;
use Zephyrus\Application\Bootstrap;
define('ROOT_DIR', __DIR__ . '/..');
require ROOT_DIR . '/vendor/autoload.php';
$router = new Router();
include(Bootstrap::getHelperFunctionsPath());
Bootstrap::start();
// endregion

use Zephyrus\Application\Localization;
use Zephyrus\Exceptions\LocalizationException;
use Zephyrus\Application\Configuration;
use Zephyrus\Application\Session;

if (Configuration::getApplicationConfiguration('env') == 'prod') {
    include('handlers.php');
}


// region Session startup
// Optional if your project does not require a session. E.g. an API.
Session::getInstance()->start();
// endregion

// region Localisation engine
// Optional if you dont want to use the /locale feature. This features enables
// the use of json files to properly organize project messages whether or not
// you have multiple languages. It is thus highly recommended for a more clean
// and maintainable codebase.
try {

    // The <locale> argument is optional, if none is given the configured locale in
    // config.ini will be used.
    Localization::getInstance()->start('fr_CA');
} catch (LocalizationException $e) {

    // If engine cannot properly start an exception will be thrown and must be corrected
    // to use this feature. Common errors are syntax error in json files. The exception
    // messages should be explicit enough.
    die($e->getMessage());
}
// endregion
