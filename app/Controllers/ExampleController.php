<?php namespace Controllers;

class ExampleController extends Controller
{
    /**
     * Defines all the routes supported by this controller associated with
     * inner methods.
     */
    public function initializeRoutes()
    {
        $this->get("/", "index");
        $this->get("/items", "jsonTest");
    }

    /**
     * Example route returning a HTML response by rendering a pug file.
     */
    public function index()
    {
        return $this->render('example', ["currentDate" => date(FORMAT_DATE)]);
    }

    /**
     * Example route that returns a JSON response.
     */
    public function jsonTest()
    {
        return $this->json([
            ['alias' => 'Batman', 'name' => 'Bruce Wayne'],
            ['alias' => 'Superman', 'name' => 'Clark Kent'],
            ['alias' => 'Wonder Woman', 'name' => 'Diana Prince'],
            ['alias' => 'The Flash', 'name' => 'Bruce Wayne'],
            ['alias' => 'Aquaman', 'name' => 'Arthur Curry'],
            ['alias' => 'Green Lantern', 'name' => 'Hal Jordan']
        ]);
    }
}
