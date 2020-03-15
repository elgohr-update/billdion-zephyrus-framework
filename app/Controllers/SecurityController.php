<?php namespace Controllers;

use Zephyrus\Exceptions\IntrusionDetectionException;
use Zephyrus\Security\Authorization;
use Zephyrus\Security\ContentSecurityPolicy;
use Zephyrus\Security\Controller as ZephyrusBaseController;

/**
 * This controller class acts as a security middleware for the application. All
 * controllers should extends this middleware to ensure proper security and
 * maintainability. This class should be used to specified authorizations,
 * CSP headers, intrusion detection behaviors, and another another security
 * specific settings for your application.
 *
 * Class SecurityController
 * @package Controllers
 */
abstract class SecurityController extends ZephyrusBaseController
{
    public function before()
    {
        $this->applyContentSecurityPolicies();
        $this->setupAuthorizations();

        // May throw an UnauthorizedAccessException, InvalidCsrfException or
        // IntrusionDetectionException. Its possible to catch the exception
        // directly here or in the error handling file.
        try {
            parent::before();
        } catch (IntrusionDetectionException $exception) {
            /**
             * Defines what to do when an attack attempt (mainly XSS and SQL
             * injection) is detected in the application. The impact value
             * represents the severity of the attempt. The code below only logs
             * the attempt in the security.log when impact is equal or higher
             * than 10. Do nothing more to limit false positive effect on legit
             * users. IntrusionDetection class is a wrapper of the expose
             * library.
             *
             * @see https://github.com/enygma/expose
             */
            $data = $exception->getIntrusionData();
            if ($data['impact'] >= 10) {
                // Do something (logs, database report, redirect, ...)
            }
        }
    }

    private function setupAuthorizations()
    {
        /**
         * The mode specifies the default behavior when no rule has been
         * defined for a route.
         *
         * Blacklist: if a route has no rule, its automatically granted [default]
         * Whitelist: if a route has no rule, its automatically denied.
         */
        parent::getAuthorization()->setMode(Authorization::MODE_BLACKLIST);

        /**
         * Rules definition for the authorization system. You must create your
         * own rules based on the needs of your application. You can easily set
         * rules based on a session data or ip address using, respectively, the
         * methods : addSessionRule() and addIpAddressRule().
         *
         * For any other needs (custom verifying, database calls, ...), you can
         * use the method addRule() which needs a callback.
         *
         * Example below can be read as : create a rule named "admin" (which can
         * later be referenced) that needs the $_SESSION key <AUTH_LEVEL> with
         * the value <admin>.
         */
        parent::getAuthorization()->addSessionRule('admin', 'AUTH_LEVEL', 'admin');

        /**
         * When in whitelist mode (every route that doesn't have a rule is
         * automatically denied), it may be use full to specify a "public"
         * rule for the login screen in example. For a more in depth
         * processing, the callback can receive the url arguments.
         */
        parent::getAuthorization()->addRule('public', function () {
            return true;
        });

        /**
         * Once the rules are defined, you can start to protect your desired
         * routes. First argument of the protect method is the path (written
         * as a controller route) you wish to add authorization requirements.
         * Second argument is the HTTP method to validate which can be
         * combined using the binary OR operator like GET | POST. The ALL
         * constant refers to GET | POST | PUT | PATCH | DELETE. The last
         * argument is the rule's name to fulfil to grant access.
         *
         * Example below can be read as : route "/insert", for all HTTP
         * methods, needs the <admin> rule to be fulfilled for the route to
         * be accessible.
         */
        parent::getAuthorization()->protect('/insert', Authorization::ALL, 'admin');

        /**
         * Example below can be read as : route "/login", for all HTTP
         * methods, can be accessed by anyone.
         */
        parent::getAuthorization()->protect('/login', Authorization::ALL, 'public');
    }

    private function applyContentSecurityPolicies()
    {
        /**
         * The ContentSecurityPolicy class helps to craft and maintain the CSP
         * headers easily. These headers should be seriously crafted since
         * they greatly help to prevent cross-site scripting attacks.
         *
         * @see https://content-security-policy.com/
         */
        $csp = new ContentSecurityPolicy();
        $csp->setDefaultSources(["'self'"]);
        $csp->setFontSources(["'self'", 'https://fonts.googleapis.com', 'https://fonts.gstatic.com']);
        $csp->setStyleSources(["'self'", 'https://fonts.googleapis.com']);
        $csp->setScriptSources(["'self'", 'https://ajax.googleapis.com', 'https://maps.googleapis.com',
            'https://www.google-analytics.com', 'http://connect.facebook.net']);
        $csp->setChildSources(["'self'", 'http://staticxx.facebook.com']);
        $csp->setImageSources(["'self'", 'data:']);
        $csp->setBaseUri([$this->request->getBaseUrl()]);

        /**
         * The SecureHeader class is the instance that will actually sent all
         * the headers concerning security including the CSP. Other headers
         * includes policy concerning iframe integration, strict transport
         * security and xss protection.
         */
        parent::getSecureHeader()->setContentSecurityPolicy($csp);
    }
}
