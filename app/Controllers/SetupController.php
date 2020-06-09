<?php namespace Controllers;

use Zephyrus\Exceptions\HttpRequesterException;
use Zephyrus\Network\HttpRequester;

class SetupController extends Controller
{
    public function initializeRoutes()
    {
        $this->get("/", "index");
    }

    public function index()
    {
        return $this->render('setup/landing', [
            'zephyrus_version' => $this->latestRemoteVersion()
        ]);
    }

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
