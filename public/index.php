<?php

use Zephyrus\Application\Bootstrap;
use Zephyrus\Exceptions\RouteMethodUnsupportedException;
use Zephyrus\Exceptions\RouteNotAcceptedException;
use Zephyrus\Exceptions\RouteNotFoundException;
use Zephyrus\Network\ResponseFactory;

Bootstrap::initializeRoutableControllers($router);

try {
    $router->run(Zephyrus\Network\RequestFactory::read());
} catch (RouteMethodUnsupportedException $e) {
    ResponseFactory::getInstance()->buildAbortMethodNotAllowed()->send();
} catch (RouteNotAcceptedException $e) {
    ResponseFactory::getInstance()->buildAbortNotAcceptable()->send();
} catch (RouteNotFoundException $e) {
    ResponseFactory::getInstance()->buildAbortNotFound()->send();
}
