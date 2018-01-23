<?php

/**
 * This file is automatically included for each request and should only defined
 * security settings for the application.
 */
use Zephyrus\Network\RequestFactory;
use Zephyrus\Security\ContentSecurityPolicy;
use Zephyrus\Security\IntrusionDetection;
use Zephyrus\Application\Configuration;

/**
 * Include the authorization requirements and rules.
 */
include('acl.php');

/**
 * Defines what to do when an attack attempt (mainly XSS and SQL injection) is
 * detected in the application. The impact value represents the severity of the
 * attempt. The code below only logs the attempt in the security.log when impact
 * is equal or higher than 10. Do nothing more to limit false positive effect on
 * legit users. IntrusionDetection class is a wrapper of the expose library.
 *
 * @see https://github.com/enygma/expose
 */
if (Configuration::getSecurityConfiguration('ids_enabled')) {
    $ids = IntrusionDetection::getInstance(new class extends \Psr\Log\AbstractLogger {
        public function log($level, $message, array $context = array())
        {
            // Register log somewhere ...
        }
    });
    $ids->onDetection(function($data) {
        if ($data['impact'] >= 10) {
            // Do something (logs, ...)
        }
    });
}

/**
 * The ContentSecurityPolicy class help craft and maintain the CSP headers
 * easily. These headers should be seriously crafted since they greatly help
 * to prevent cross-site scripting attacks. For more information on the CSP
 * headers please see : https://content-security-policy.com/
 */
$csp = new ContentSecurityPolicy();
$csp->setDefaultSources(["'self'"]);
$csp->setFontSources(["'self'", 'https://fonts.googleapis.com', 'https://fonts.gstatic.com']);
$csp->setStyleSources(["'self'", 'https://fonts.googleapis.com']);
$csp->setScriptSources(["'self'", 'https://ajax.googleapis.com', 'https://maps.googleapis.com',
    'https://www.google-analytics.com', 'http://connect.facebook.net']);
$csp->setChildSources(["'self'", 'http://staticxx.facebook.com']);
$csp->setImageSources(["'self'", 'data:']);
$csp->setBaseUri([RequestFactory::read()->getBaseUrl()]);

/**
 * The SecureHeader class is the instance that will actually sent all the
 * headers concerning security including the CSP. Other headers includes policy
 * concerning iframe integration, strict transport security and xss protection.
 */
$router->getSecureHeader()->setContentSecurityPolicy($csp);
