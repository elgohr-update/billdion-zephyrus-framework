<?php namespace Controllers;

use Models\ComposerPackage;
use Zephyrus\Application\Configuration;
use Zephyrus\Application\Session;
use Zephyrus\Network\Response;
use Zephyrus\Security\Cryptography;
use Zephyrus\Utilities\FileSystem\Directory;
use Zephyrus\Utilities\FileSystem\File;
use ZipArchive;

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
        $view = $this->render('setup/start', [
            'data' => $data,
            'setup' => $setup,
            'examples' => (object) [
                'currency' => $this->formatMoneyExample($data['application_locale'] ?? 'fr_CA', $data['application_currency'] ?? 'CAD'),
                'timezone' => $this->formatDateTimeExample($data['application_timezone'] ?? 'America/New_York')
            ]
        ]);
        if ($setup == 7) {
            $this->setupConfigIniFile();
            $this->setupFrontEnd();
            $this->setupOthers();
            $this->emptyProject();
            Session::getInstance()->destroy();
        }
        return $view;
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

    private function setupConfigIniFile()
    {
        $data = Session::getInstance()->read("setup_data", []);
        $configurations = [
            'application' => [
                'env' => 'dev',
                'locale' => $data['application_locale'],
                'currency' => $data['application_currency'],
                'charset' => $data['application_charset'],
                'timezone' => $data['application_timezone']
            ],
            'database' => [],
            'session' => [
                'name' => Cryptography::randomString(20),
                'encryption_enabled' => ($data['security_session_encrypt'] == "1"),
                'fingerprint_ip' => ($data['security_session_hash'] == "1"),
                'fingerprint_agent' => ($data['security_session_hash'] == "1"),
                ';refresh_after_interval' => 60,
                ';refresh_after_requests' => 4
            ],
            'security' => [
                'ids_enabled' => ($data['security_ids'] == "1"),
                'csrf_guard_enabled' => ($data['security_csrf'] == "1"),
                'csrf_guard_automatic_html' => ($data['security_csrf'] == "1"),
                'encryption_algorithm' => 'aes-256-cbc',
                ';csrf_guard_methods' => ['POST', 'PUT', 'DELETE', 'PATCH'],
                ';ids_exceptions' => ['GET.__utmz', 'GET.__utmc']
            ],
            'lang' => [
                'date' => '%e %B %Y',
                'time' => '%H:%M',
                'datetime' => '%e %B %Y, %H:%M'
            ],
            'pug' => [
                'cache_enabled' => false,
                'cache_directory' => '/var/cache/pug'
            ]
        ];

        // Database settings
        $configurations['database']['dbms'] = $data['database_system'];
        if (!empty($data['database_host'])) {
            $configurations['database']['host'] = $data['database_host'];
        }
        if (!empty($data['database_port'])) {
            $configurations['database']['port'] = $data['database_port'];
        }
        if (!empty($data['database_name'])) {
            $configurations['database']['database'] = $data['database_name'];
        }
        if (!empty($data['database_username'])) {
            $configurations['database']['username'] = $data['database_username'];
        }
        if (!empty($data['database_password'])) {
            $configurations['database']['password'] = $data['database_password'];
        }
        if (!empty($data['database_charset'])) {
            $configurations['database']['charset'] = $data['database_charset'];
        }
        $configurations['database']['shared'] = ($data['database_shared'] == "1");
        $configurations['database']['convert_type'] = ($data['database_convert'] == "1");

        // Security session settings
        if ($data['security_session_refresh'] == "1") {
            $configurations['session']['refresh_probability'] = 0.4;
        } else {
            $configurations['session'][';refresh_probability'] = null;
        }
        $configurations['session'][';decoys'] = null;
        Configuration::getFile()->write($configurations);
        Configuration::getFile()->save();
    }

    private function setupFrontEnd()
    {
        $data = Session::getInstance()->read("setup_data", []);
        if ($data['frontend_framework'] == 'bootstrap_4.5.0') {
            $this->setupBootstrap();
        }
        if ($data['frontend_framework'] == 'bulma') {
            $this->setupBulma();
        }
        if ($data['frontend_framework'] == 'materialize') {
            $this->setupMaterialize();
        }
        if ($data['frontend_jquery'] == '1') {
            $this->setupJquery();
        }
        if ($data['frontend_fontawesome'] == '1') {
            $this->setupFontAwesome();
        }
        if ($data['frontend_lineicons'] == '1') {
            $this->setupLineIcons();
        }
        if ($data['frontend_moments'] == '1') {
            $this->setupMomentsJs();
        }
        if ($data['frontend_numeral'] == '1') {
            $this->setupNumeral();
        }
    }

    private function setupOthers()
    {
        $data = Session::getInstance()->read("setup_data", []);
        if ($data['others_codeclimate'] != '1') {
            (new File(ROOT_DIR . '/.codeclimate.yml'))->remove();
        }
        if ($data['others_travis'] != '1') {
            (new File(ROOT_DIR . '/.travis.yml'))->remove();
        }
        if ($data['others_unittest'] != '1') {
            (new File(ROOT_DIR . '/phpunit.xml'))->remove();
            (new Directory(ROOT_DIR . '/tests'))->remove();
        }
        if ($data['others_styleci'] != '1') {
            (new File(ROOT_DIR . '/.styleci.yml'))->remove();
        }
        if (!empty($data['others_git'])) {
            shell_exec('git init');
            shell_exec('git remote add origin ' . $data['others_git']);
        }
    }

    private function emptyProject()
    {
        $data = Session::getInstance()->read("setup_data", []);
        (new File(ROOT_DIR . '/app/Controllers/SetupController.php'))->remove();
        (new Directory(ROOT_DIR . '/app/Views/setup'))->remove();
        (new Directory(ROOT_DIR . '/public/assets/setup_archives'))->remove();
        (new Directory(ROOT_DIR . '/public/assets/images'))->remove();
        (new Directory(ROOT_DIR . '/locale/cache'))->remove();
        (new Directory(ROOT_DIR . '/public/stylesheets/images'))->remove();
        (new File(ROOT_DIR . '/locale/fr_CA/setup.json'))->remove();
        (new File(ROOT_DIR . '/locale/fr_CA/landing.json'))->remove();
        (new File(ROOT_DIR . '/public/javascripts/vendor/highlight.pack.js'))->remove();
        (new File(ROOT_DIR . '/public/stylesheets/vendor/highlight-default.css'))->remove();
        (new File(ROOT_DIR . '/public/stylesheets/vendor/pretty-checkbox.min.css'))->remove();
        (new File(ROOT_DIR . '/public/javascripts/app.js'))->remove();
        (new File(ROOT_DIR . '/public/stylesheets/style.css'))->remove();
        (new File(ROOT_DIR . '/public/stylesheets/setup.css'))->remove();
        (new File(ROOT_DIR . '/public/stylesheets/vendor/LineIcons.min.css'))->remove();
        if ($data['frontend_framework'] != 'bootstrap_4.5.0') {
            (new File(ROOT_DIR . '/public/stylesheets/vendor/bootstrap.min.css'))->remove();
            (new File(ROOT_DIR . '/public/stylesheets/vendor/bootstrap.min.css.map'))->remove();
            (new File(ROOT_DIR . '/public/javascripts/vendor/bootstrap.min.js'))->remove();
            (new File(ROOT_DIR . '/public/javascripts/vendor/bootstrap.min.js.map'))->remove();
        }
        if ($data['frontend_jquery'] != '1') {
            (new File(ROOT_DIR . '/public/javascripts/vendor/jquery-3.5.1.min.js'))->remove();
        }
        if ($data['frontend_lineicons'] != '1') {
            (new File(ROOT_DIR . '/public/stylesheets/fonts/LineIcons.eot'))->remove();
            (new File(ROOT_DIR . '/public/stylesheets/fonts/LineIcons.svg'))->remove();
            (new File(ROOT_DIR . '/public/stylesheets/fonts/LineIcons.ttf'))->remove();
            (new File(ROOT_DIR . '/public/stylesheets/fonts/LineIcons.woff'))->remove();
        }

        Directory::create(ROOT_DIR . '/public/stylesheets/images');
        Directory::create(ROOT_DIR . '/public/assets/images');
        File::create(ROOT_DIR . '/public/assets/images/.keep');
        File::create(ROOT_DIR . '/public/stylesheets/fonts/.keep');
        File::create(ROOT_DIR . '/public/stylesheets/vendor/.keep');
        File::create(ROOT_DIR . '/public/stylesheets/images/.keep');
        File::create(ROOT_DIR . '/public/javascripts/vendor/.keep');
        File::create(ROOT_DIR . '/public/javascripts/app.js');
        File::create(ROOT_DIR . '/public/stylesheets/style.css');

        $content = str_replace('/sample', '/', file_get_contents(ROOT_DIR . '/app/Controllers/ExampleController.php'));
        (new File(ROOT_DIR . '/app/Controllers/ExampleController.php'))->write($content);
    }

    private function setupBootstrap(string $distribution = "bootstrap-4.5.0-dist")
    {
        $zip = new ZipArchive();
        $zip->open(ROOT_DIR . "/public/assets/setup_archives/$distribution.zip");
        $zip->extractTo(ROOT_DIR . "/public/stylesheets/vendor/", array("$distribution/css/bootstrap.min.css", "$distribution/css/bootstrap.min.css.map"));
        $zip->extractTo(ROOT_DIR . "/public/javascripts/vendor/", array("$distribution/js/bootstrap.min.js", "$distribution/js/bootstrap.min.js.map"));
        $zip->close();
        @rename(ROOT_DIR . "/public/stylesheets/vendor/$distribution/css/bootstrap.min.css", ROOT_DIR . "/public/stylesheets/vendor/bootstrap.min.css");
        @rename(ROOT_DIR . "/public/stylesheets/vendor/$distribution/css/bootstrap.min.css.map", ROOT_DIR . "/public/stylesheets/vendor/bootstrap.min.css.map");
        (new Directory(ROOT_DIR . "/public/stylesheets/vendor/$distribution"))->remove();
        @rename(ROOT_DIR . "/public/javascripts/vendor/$distribution/js/bootstrap.min.js", ROOT_DIR . "/public/javascripts/vendor/bootstrap.min.js");
        @rename(ROOT_DIR . "/public/javascripts/vendor/$distribution/js/bootstrap.min.js.map", ROOT_DIR . "/public/javascripts/vendor/bootstrap.min.js.map");
        (new Directory(ROOT_DIR . "/public/javascripts/vendor/$distribution"))->remove();
    }

    private function setupBulma(string $distribution = "bulma-0.9.0")
    {
        $zip = new ZipArchive();
        $zip->open(ROOT_DIR . "/public/assets/setup_archives/$distribution.zip");
        $zip->extractTo(ROOT_DIR . "/public/stylesheets/vendor/", array("$distribution/css/bulma.min.css"));
        $zip->close();
        @rename(ROOT_DIR . "/public/stylesheets/vendor/$distribution/css/bulma.min.css", ROOT_DIR . "/public/stylesheets/vendor/bulma.min.css");
        (new Directory(ROOT_DIR . "/public/stylesheets/vendor/$distribution"))->remove();
    }

    private function setupMaterialize(string $distribution = "materialize-1.0.0")
    {
        $zip = new ZipArchive();
        $zip->open(ROOT_DIR . "/public/assets/setup_archives/$distribution.zip");
        $zip->extractTo(ROOT_DIR . "/public/stylesheets/vendor/", array("materialize/css/materialize.min.css"));
        $zip->extractTo(ROOT_DIR . "/public/javascripts/vendor/", array("materialize/js/materialize.min.js"));
        @rename(ROOT_DIR . "/public/stylesheets/vendor/materialize/css/materialize.min.css", ROOT_DIR . "/public/stylesheets/vendor/materialize.min.css");
        @rename(ROOT_DIR . "/public/javascripts/vendor/materialize/js/materialize.min.js", ROOT_DIR . "/public/javascripts/vendor/materialize.min.js");
        (new Directory(ROOT_DIR . "/public/stylesheets/vendor/materialize"))->remove();
        (new Directory(ROOT_DIR . "/public/javascripts/vendor/materialize"))->remove();
    }

    private function setupJquery(string $distribution = "jquery-3.5.1")
    {
        $zip = new ZipArchive();
        $zip->open(ROOT_DIR . "/public/assets/setup_archives/$distribution.zip");
        $zip->extractTo(ROOT_DIR . "/public/javascripts/vendor/", ['jquery-3.5.1.min.js']);
    }

    private function setupFontAwesome(string $distribution = "fontawesome-free-5.13.0-web")
    {
        $zip = new ZipArchive();
        $zip->open(ROOT_DIR . "/public/assets/setup_archives/$distribution.zip");
        $zip->extractTo(ROOT_DIR . "/public/stylesheets/vendor/");
        $fontAwesomeContent = file_get_contents(ROOT_DIR . "/public/stylesheets/vendor/$distribution/css/all.min.css");
        $fontAwesomeContent = str_replace('webfonts', 'fonts', $fontAwesomeContent);
        (new File(ROOT_DIR . "/public/stylesheets/vendor/$distribution/css/all.min.css"))->write($fontAwesomeContent);
        @rename(ROOT_DIR . "/public/stylesheets/vendor/$distribution/css/all.min.css", ROOT_DIR . "/public/stylesheets/vendor/fontawesome.min.css");
        (new Directory(ROOT_DIR . "/public/stylesheets/vendor/$distribution/webfonts"))->copy(ROOT_DIR . "/public/stylesheets/fonts");
        (new Directory(ROOT_DIR . "/public/stylesheets/vendor/__MACOSX"))->remove();
        (new Directory(ROOT_DIR . "/public/stylesheets/vendor/$distribution"))->remove();
    }

    private function setupLineIcons(string $distribution = "LineIcons-Package-2")
    {
        $zip = new ZipArchive();
        $zip->open(ROOT_DIR . "/public/assets/setup_archives/$distribution.zip");
        $zip->extractTo(ROOT_DIR . "/public/stylesheets/vendor/");
        @rename(ROOT_DIR . "/public/stylesheets/vendor/$distribution/WebFont/font-css/LineIcons.css", ROOT_DIR . "/public/stylesheets/vendor/LineIcons.css");
        (new Directory(ROOT_DIR . "/public/stylesheets/vendor/$distribution/WebFont/fonts"))->copy(ROOT_DIR . "/public/stylesheets/fonts");
        (new Directory(ROOT_DIR . "/public/stylesheets/vendor/__MACOSX"))->remove();
        (new Directory(ROOT_DIR . "/public/stylesheets/vendor/$distribution"))->remove();
    }

    private function setupMomentsJs(string $distribution = "moment-with-locales")
    {
        $zip = new ZipArchive();
        $zip->open(ROOT_DIR . "/public/assets/setup_archives/$distribution.zip");
        $zip->extractTo(ROOT_DIR . "/public/javascripts/vendor/", ['moment-with-locales.min.js']);
    }

    private function setupNumeral(string $distribution = "numeral-2.0.6")
    {
        $zip = new ZipArchive();
        $zip->open(ROOT_DIR . "/public/assets/setup_archives/$distribution.zip");
        $zip->extractTo(ROOT_DIR . "/public/javascripts/vendor/");
        @rename(ROOT_DIR . "/public/javascripts/vendor/adamwdraper-Numeral-js-7de892f/min", ROOT_DIR . "/public/javascripts/vendor/numeral");
        (new Directory(ROOT_DIR . "/public/javascripts/vendor/__MACOSX"))->remove();
        (new Directory(ROOT_DIR . "/public/javascripts/vendor/adamwdraper-Numeral-js-7de892f"))->remove();
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
