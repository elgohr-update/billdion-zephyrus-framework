<?php

use Zephyrus\Network\RequestFactory;
use Zephyrus\Security\Router;
use Zephyrus\Application\Bootstrap;

$router = new Router();
foreach (recursiveGlob('../app/routes/*.php') as $file) {
    include($file);
}

Bootstrap::initializeRoutableControllers($router);
$router->run(RequestFactory::read());