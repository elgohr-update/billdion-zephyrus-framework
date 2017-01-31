<?php

/**
 * This file is included by kernel.php and defines how to handle errors and
 * exceptions which reached the main thread (that nobody trapped). This file
 * should be loaded in production because it is not outputting any information
 * about the errors. Details are logged in errors.log file. You can modify each
 * error handling as you see fit (redirection, disconnection, messages, ...).
 */
use Zephyrus\Application\ErrorHandler;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Session;
use Zephyrus\Exceptions\RouteNotFoundException;
use Zephyrus\Exceptions\UnauthorizedAccessException;
use Zephyrus\Exceptions\InvalidCsrfException;
use Zephyrus\Exceptions\DatabaseException;

$errorHandler = new ErrorHandler();

$errorHandler->exception(function (Error $e) {
    //TODO: handle any
});

$errorHandler->exception(function (Exception $e) {
    //TODO: code to handle any type of Exceptions if not specify elsewhere
});

$errorHandler->exception(function (DatabaseException $e) {
    //TODO: code to handle any type of database errors
});

$errorHandler->exception(function (RouteNotFoundException $e) {
    //TODO: code to handle when the route does not exist
});

$errorHandler->exception(function(UnauthorizedAccessException $e) {
    Session::getInstance()->restart();
    Flash::error("Vous n'avez pas les droits requis pour accéder à la ressource spécifiée");
});

$errorHandler->exception(function (InvalidCsrfException $e) {
    //TODO: code to handle when a CSRF token check fails
});
