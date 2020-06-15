<?php namespace Controllers;

use Models\ComposerPackage;
use Zephyrus\Application\Configuration;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;

class SetupController extends Controller
{
    public function initializeRoutes()
    {
        $this->get("/", "index");
        $this->get("/setup", "setup");
        $this->get("/setup-cancel", "backward");
        $this->post("/setup", "forward");
    }

    public function index()
    {
        return $this->render('setup/landing');
    }

    public function setup()
    {
        $data = Session::getInstance()->read("setup_data", []);
        $setup = Session::getInstance()->read("setup", 0);
        if ($setup == 0) {
            Session::getInstance()->set("setup", 1);
            Session::getInstance()->set("setup_data", []);
        }
        return $this->render('setup/start', [
            'data' => $data,
            'examples' => (object) [
                'currency' => $this->formatMoneyExample($data['application_locale'] ?? 'fr_CA', $data['application_currency'] ?? 'CAD'),
                'timezone' => $this->formatDateTimeExample($data['application_timezone'] ?? 'America/New_York')
            ]
        ]);
    }

    public function forward()
    {
        $form = $this->buildForm();
        Session::getInstance()->set("setup", Session::getInstance()->read("setup") + 1);
        Session::getInstance()->set("setup_data", array_merge(Session::getInstance()->read("setup_data"), $form->getFields()));
        return $this->redirect("/setup");
    }

    public function backward()
    {
        Session::getInstance()->set("setup", Session::getInstance()->read("setup") - 1);
        return $this->redirect("/setup");
    }

    /**
     * For this controller, all route rendering should include the latest Zephyrus core version in its parameters.
     *
     * @param string $page
     * @param array $args
     * @return Response
     */
    protected function render($page, $args = []): Response
    {
        return parent::render($page, array_merge($args, [
            'system_date' => date(FORMAT_DATE_TIME),
            'zephyrus_version' => ComposerPackage::getVersion("zephyrus/zephyrus")
        ]));
    }

    private function formatMoneyExample($locale, $currency)
    {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 2);
        $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
        $formatter->setAttribute(\NumberFormatter::ROUNDING_MODE, \NumberFormatter::ROUND_HALFUP);
        return $formatter->formatCurrency(999999.99, $currency);
    }

    private function formatDateTimeExample(string $timezone)
    {
        date_default_timezone_set($timezone);
        $dateTime = strftime(Configuration::getConfiguration('lang', 'datetime'), time());
        date_default_timezone_set(Configuration::getApplicationConfiguration('timezone'));
        return $dateTime;
    }
}
