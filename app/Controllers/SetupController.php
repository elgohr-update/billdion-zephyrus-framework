<?php namespace Controllers;

use Zephyrus\Application\Configuration;
use Zephyrus\Application\Session;
use Zephyrus\Exceptions\HttpRequesterException;
use Zephyrus\Network\HttpRequester;
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
        Session::getInstance()->set("setup", 1);
        Session::getInstance()->set("setup_data", []);
        return $this->render('setup/landing');
    }

    public function setup()
    {
        $data = Session::getInstance()->read("setup_data", []);
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
            'zephyrus_version' => $this->latestRemoteVersion()
        ]));
    }

    /**
     * Fetches the latest released core version on the official github repository.
     *
     * @param string $repository
     * @return string
     */
    private function latestRemoteVersion($repository = "dadajuice/zephyrus"): string
    {
        try {
            $url = "https://api.github.com/repos/$repository/releases/latest";
            $response = HttpRequester::get($url)->execute();
            $response = json_decode($response);
            if (!isset($response->tag_name)) {
                return "1.x.x";
            }
            return $response->tag_name;
        } catch (HttpRequesterException $exception) {
            return "1.x.x";
        }
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
