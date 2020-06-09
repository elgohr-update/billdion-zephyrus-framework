<?php namespace Controllers;

use Zephyrus\Exceptions\HttpRequesterException;
use Zephyrus\Network\HttpRequester;
use Zephyrus\Network\Response;

class SetupController extends Controller
{
    public function initializeRoutes()
    {
        $this->get("/", "index");
        $this->get("/setup", "setup");
    }

    public function index()
    {
        return $this->render('setup/landing');
    }

    public function setup()
    {
        return $this->render('setup/start');
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
            return $response->tag_name;
        } catch (HttpRequesterException $exception) {
            return "1.x.x";
        }
    }
}
