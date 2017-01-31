<?php

use Zephyrus\Network\RequestFactory;
use Zephyrus\Application\Bootstrap;

foreach (recursiveGlob('../app/routes/*.php') as $file) {
    include($file);
}

Bootstrap::initializeRoutableControllers($router);
$router->run(RequestFactory::read());