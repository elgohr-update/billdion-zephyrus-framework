<?php namespace Controllers;

use Models\Brokers\ItemBroker;
use Models\Item;
use Zephyrus\Application\Controller;
use Zephyrus\Application\Flash;
use Zephyrus\Application\Form;
use Zephyrus\Network\Router;
use Zephyrus\Utilities\Validator;

class ExampleController extends Controller
{
    /**
     * Defines all the routes supported by this controller associated with
     * inner methods.
     *
     * @param Router $router
     */
    public static function initializeRoutes(Router $router)
    {
        $router->get("/", self::bind("index"));
    }

    /**
     * Example route which renders a simple page of items.
     */
    public function index()
    {
        $this->render('example', ["items" => $this->buildDemoItems(), "currentDate" => date('Y-m-d')]);
    }

    private function buildDemoItems()
    {
        $items = [];
        $item = new Item();
        $item->setName('Pomme');
        $item->setPrice(1.26);
        $items[] = $item;
        $item = new Item();
        $item->setName('Poulet');
        $item->setPrice(8.60);
        $items[] = $item;
        $item = new Item();
        $item->setName('Sandwish');
        $item->setPrice(4.2);
        $items[] = $item;
        return $items;
    }
}