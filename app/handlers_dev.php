<?php

/**
 * This file is included by kernel.php and defines how to handle errors and
 * exceptions which reached the main thread (that nobody trapped). This file
 * should be loaded in development because it is outputting detailed
 * information about the errors (inclusing stack trace). Details are also
 * logged in errors.log file. You can modify each error handling as you see
 * fit (redirection, disconnection, messages, ...).
 */
use Zephyrus\Application\ErrorHandler;
use Zephyrus\Exceptions\RouteNotFoundException;
use Zephyrus\Exceptions\UnauthorizedAccessException;
use Zephyrus\Exceptions\InvalidCsrfException;
use Zephyrus\Exceptions\DatabaseException;

$errorHandler = ErrorHandler::getInstance();

$errorHandler->exception(function(Error $e) {
    die($e->getMessage() . ' : ' . $e->getTraceAsString());
});

$errorHandler->exception(function(Exception $e) {
    die($e->getMessage() . ' : ' . $e->getTraceAsString());
});

$errorHandler->exception(function(DatabaseException $e) {
    die($e->getMessage() . ' : ' . $e->getTraceAsString());
});

$errorHandler->exception(function(RouteNotFoundException $e) {
    die($e->getMessage() . ' : ' . $e->getTraceAsString());
});

$errorHandler->exception(function(UnauthorizedAccessException $e) {
    die($e->getMessage() . ' : ' . $e->getTraceAsString());
});

$errorHandler->exception(function(InvalidCsrfException $e) {
    die($e->getMessage() . ' : ' . $e->getTraceAsString());
});