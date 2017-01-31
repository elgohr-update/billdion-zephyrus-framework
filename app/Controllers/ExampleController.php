<?php namespace Controllers;

use Models\Item;
use Zephyrus\Application\Controller;

class ExampleController extends Controller
{
    /**
     * Defines all the routes supported by this controller associated with
     * inner methods.
     */
    public function initializeRoutes()
    {
        $this->get("/", "index");
    }

    /**
     * Example route which renders a simple page of items.
     */
    public function index()
    {
        $this->render('example', ["currentDate" => date('Y-m-d')]);
    }
}
