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
        $router->get("/insert", self::bind("displayInsert"));
        $router->post("/insert", self::bind("insert"));
        $router->get("/example/html", self::bind("htmlTest"));
        $router->get("/example/json", self::bind("jsonTest"));
        $router->get("/example/xml", self::bind("xmlTest"));
        $router->get("/example/sse", self::bind("sseTest"));
    }

    /**
     * Example route which renders a simple page of items.
     */
    public function index()
    {
        $broker = new ItemBroker();
        $pager = $broker->buildPager($broker->countAll(), 4);
        $items = $broker->findAll();
        $this->render('example', ["items" => $items, "currentDate" => date('Y-m-d')], $pager);
    }

    /**
     * Example route which process the item insertion. Uses the Form class
     * to validate inputs.
     */
    public function insert()
    {
        /**
         * Creates a form instance automatically loaded with the request data
         * ready to be validated.
         */
        $form = $this->buildForm();

        /**
         * Add all the needed validation rules for the specified form. This
         * part can easily be extracted to a private method and called in an
         * update/insert case which normally shares the same validations.
         *
         * The Validator class has many ready to use simple validation functions
         * that can be directly used.
         */
        $form->addRule("name", Validator::NOT_EMPTY, "Le nom ne doit pas être vide");
        $form->addRule("price", Validator::NOT_EMPTY, "Le prix ne doit pas être vide");

        /**
         * The third parameter specifies if the check should be done every time
         * or only if the specified field has no error (make sure to not have
         * multiple errors for one field if its not wanted).
         */
        $form->addRule("price", Validator::DECIMAL, "Le prix doit être un nombre positif", Form::TRIGGER_FIELD_NO_ERROR);

        /**
         * For custom validations, you can pass a callback as a validation
         * function. This function received the field value.
         */
        $form->addRule("price", function($value) {
            return $value >= 0.01 && $value <= 1000;
        }, "Le prix doit être entre 0.01$ et 1000$", Form::TRIGGER_FIELD_NO_ERROR);

        /**
         * Proceeds to the form validation. If it fails, this only redirect to
         * form with the error messages.
         */
        if (!$form->verify()) {
            $messages = $form->getErrorMessages();
            Flash::error($messages);
            redirect("/insert");
        }

        /**
         * Validations has passed, item can be created using the request data
         * and inserted to the database.
         */
        $item = new Item();
        $item->setName($form->getValue("name"));
        $item->setPrice(str_replace(',', '.', $form->getValue("price")));
        $broker = new ItemBroker();
        $broker->insert($item);
        Flash::success("Ajout de l'article #" . $item->getId() . " avec succès");
        redirect("/");
    }

    /**
     * Example route which renders a Pug view named "form.pug".
     */
    public function displayInsert()
    {
        $this->render('form');
    }

    /**
     * Example route which displays simple HTML stream as would normally do PHP
     * without any rendering. Produces the same result as using the $this->html()
     * method.
     */
    public function htmlTest()
    {
        ?>
        <p>Testing without ob_start()</p>
        <?php
    }

    /**
     * Example route showcasing simple xml rendering using an array. Can be also
     * used with a SimpleXMLElement instance.
     */
    public function xmlTest()
    {
        $data = [
            "batman" => [
                "enemies" => ["Joker", "TwoFace"],
                "allies" => ["Gordon", "Alfred"]
            ]
        ];
        $this->xml($data, "root");
    }

    /**
     * Example route which simply sends server-sent event (SSE) of the current
     * timestamp.
     */
    public function sseTest()
    {
        $this->sse(time());
    }
}