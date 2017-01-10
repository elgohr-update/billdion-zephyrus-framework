<?php

/**
 * This file is automatically included for each request and should only defined
 * security settings for the application.
 */
use Zephyrus\Network\RequestFactory;
use Zephyrus\Security\ContentSecurityPolicy;
use Zephyrus\Security\IntrusionDetection;
use Zephyrus\Security\SecureHeader;
use Zephyrus\Application\Configuration;
use Zephyrus\Security\SystemLog;

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
    $ids = IntrusionDetection::getInstance();
    $ids->onDetection(function($data) {
        if ($data['impact'] >= 10) {
            SystemLog::addSecurity('IDS Detection : ' . json_encode($data));
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
$csp->setStyleSources(["'unsafe-inline'", "'self'", 'https://fonts.googleapis.com', 'http://zephyrus.local']);
$csp->setScriptSources(["'self'", 'https://ajax.googleapis.com', 'https://maps.googleapis.com', 'https://www.google-analytics.com', 'http://connect.facebook.net']);
$csp->setChildSources(["'self'", 'http://zephyrus.local', 'http://staticxx.facebook.com']);
$csp->setImageSources([
    "'self'", 'http://zephyrus.local',
    'data:', 'https://csi.gstatic.com', 'https://maps.gstatic.com',
    'https://maps.googleapis.com', 'https://www.google-analytics.com',
    'https://www.facebook.com']);
$csp->setBaseUri([RequestFactory::create()->getBaseUrl()]);

/**
 * The generateNonce() method initialize the need to use a nonce to identify
 * javascript in html. This will automatically correctly adds the nonce in the
 * CSP headers. Basically, if the rest of the script CSP header is correctly
 * crafted, any javascript included in HTML needs this nonce to be executed.
 */
$csp::generateNonce();

/**
 * The SecureHeader class is the instance that will actually sent all the
 * headers concerning security including the CSP. Other headers includes policy
 * concerning iframe integration, strict transport security and xss protection.
 */
$header = SecureHeader::getInstance();
$header->setContentSecurityPolicy($csp);