<?php

/**
 * This file is included by kernel.php and defines how to handle errors and
 * exceptions which reached the main thread (that nobody trapped). This file
 * should be loaded in production because it is not outputting any information
 * about the errors. Details are logged in errors.log file. You can modify each
 * error handling as you see fit (redirection, disconnection, messages, ...).
 */
use Zephyrus\Application\ErrorHandler;
use Zephyrus\Exceptions\RouteNotFoundException;
use Zephyrus\Exceptions\UnauthorizedAccessException;
use Zephyrus\Exceptions\InvalidCsrfException;
use Zephyrus\Exceptions\DatabaseException;
use Zephyrus\Network\Response;

$errorHandler = ErrorHandler::getInstance();

$errorHandler->exception(function(Error $e) {
    Response::abortInternalError();
});

$errorHandler->exception(function(Exception $e) {
    Response::abortInternalError();
});

$errorHandler->exception(function(DatabaseException $e) {
    Response::abortInternalError();
});

$errorHandler->exception(function(RouteNotFoundException $e) {
    Response::abortNotFound();
});

$errorHandler->exception(function(UnauthorizedAccessException $e) {
    Response::abortForbidden();
});

$errorHandler->exception(function(InvalidCsrfException $e) {
    Response::abortInternalError();
});