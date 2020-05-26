<?php namespace Controllers;

use Zephyrus\Network\ContentType;

class ExampleController extends Controller
{
    /**
     * Defines all the routes supported by this controller associated with inner methods. The first argument is a string
     * representation of the uri of the route you want to define starting with a slash. The second argument is the name
     * of a public method accessible within this class to process the route call. It is possible to include parameters
     * within the route uri definition using curly braces (E.g /item/{id}).
     */
    public function initializeRoutes()
    {
        // Home page
        $this->get("/", "index");

        // It is possible to define routes according to the desired representation accepted. The following example
        // allows a standard HTML rendering but also a JSON representation for the same route.
        $this->get("/items", "readAllItems");
        $this->get("/items", "jsonItems", ContentType::JSON);

        // It is also possible to define multiple accepted representations for a specific route. The following example
        // will display the same result (as JSON) either for an HTML requested render or a JSON response. Of course
        // defining accepted content type is optional, by default */* is considered.
        $this->get("/items/{id}", "jsonTest", [ContentType::HTML, ContentType::JSON]);
    }

    /**
     * Example route returning a HTML response by rendering a pug file.
     */
    public function index()
    {
        return $this->render('example', ["name" => "Bruce Wayne"]);
    }

    public function readAllItems()
    {
        return $this->plain("allo");
    }

    /**
     * Example route that returns a JSON response.
     */
    public function jsonItems()
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
