<?php namespace Controllers;

use Models\Item;
use Zephyrus\Application\Controller;
use Zephyrus\Network\ContentType;
use Zephyrus\Network\Response;

class ExampleController extends Controller
{
    private $executionTime;

    /**
     * Defines all the routes supported by this controller associated with
     * inner methods.
     */
    public function initializeRoutes()
    {
        $this->get("/", "index");
        $this->get("/items", "jsonTest");
    }

    public function before()
    {
        $this->executionTime = microtime(true);
        return true;
    }

    public function after(?Response $response)
    {
        if (!is_null($response) && $response->getContentType() == ContentType::HTML) {
            $timeZone = '<div id="execution">' . (microtime(true) - $this->executionTime) . '</div>';
            $response->setContent($response->getContent() . $timeZone);
        }
        return $response;
    }

    /**
     * Example route which renders a simple page of items.
     */
    public function index()
    {
        return $this->render('example', ["currentDate" => date('Y-m-d')]);
    }

    /**
     * Example route rendering json entities.
     */
    public function jsonTest()
    {
        $items = $this->buildItems();
        return $this->json($items);
    }

    /**
     * @return Item[]
     */
    private function buildItems(): array
    {
        $items = [];
        $item = new Item();
        $item->setId(1);
        $item->setName("Batarang");
        $item->setPrice(10.50);
        $items[] = $item;

        $item = new Item();
        $item->setId(2);
        $item->setName("Captain America Shield");
        $item->setPrice(400);
        $items[] = $item;

        $item = new Item();
        $item->setId(3);
        $item->setName("Thor Hammer");
        $item->setPrice(700);
        $items[] = $item;
        return $items;
    }
}
