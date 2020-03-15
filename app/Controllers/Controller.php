<?php namespace Controllers;

use Exception;
use Zephyrus\Application\Configuration;
use Zephyrus\Application\ErrorHandler;
use Zephyrus\Exceptions\DatabaseException;
use Zephyrus\Exceptions\RouteNotFoundException;

/**
 * This class acts as an application middleware, all other controller classes
 * should extends this Controller and thus inherit every global behaviors your
 * application may require. You can override methods like before() and after()
 * to make good use of the middleware feature, or simply override method like
 * render() to define specific variables that all views should have. You can
 * have as much middleware as you want (through extends).
 *
 * Class Controller
 * @package Controllers
 */
abstract class Controller extends SecurityController
{
    /**
     * This method is called immediately before processing any route in your
     * controller. To break the chain of middleware, you can remove the call
     * to the parent "before" method, but it is highly discouraged. Instead,
     * you should always keep the parent call, but place it accordingly to
     * your situation (should the parent's middleware processing be done
     * before or after mine?).
     */
    public function before()
    {
        if (Configuration::getApplicationConfiguration('env') == 'prod') {
            $this->setupErrorHandling();
        }
        parent::before();
    }

    /**
     * Defines how to handle errors and exceptions which reached the main
     * thread (that nobody trapped). These are usage example and should be
     * altered to reflect serious application usage. The ErrorHandler class
     * allows to handle any specific exception as you see fit.
     *
     * Note that using the ErrorHandler changes the way PHP will handle
     * errors at its core if you use notice(), warning() or error().
     */
    private function setupErrorHandling()
    {
        $errorHandler = ErrorHandler::getInstance();

        /**
         * Handles basically every exceptions that were not caught.
         */
        $errorHandler->exception(function (Exception $e) {
        });

        /**
         * Handles specific case when a database exception occurred. Depends on
         * the need of each application. Some may want to specifically handle
         * this case or catch them in the global Exception. In fact, it is
         * possible to handle every exception specifically if needed.
         */
        $errorHandler->exception(function (DatabaseException $e) {
        });

        /**
         * Handles when a user tries to access a route that doesn't exists. In
         * this example, it simply returns a 404 header. You could implement a
         * custom page to display a significant error, do a flash message and
         * redirect, you could also log the attempt, etc. The exception
         * contains the requested URL and http method.
         */
        $instance = $this;
        $errorHandler->exception(function (RouteNotFoundException $e) use ($instance) {
            $instance->abortNotFound();
        });

        // Its recommended to catch these exceptions in security middleware.
        //$errorHandler->exception(function(UnauthorizedAccessException $e) {
        //});
        //$errorHandler->exception(function (InvalidCsrfException $e) {
        //});
    }
}
