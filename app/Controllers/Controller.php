<?php namespace Controllers;

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
        parent::before();
    }
}
