<?php

/**
 * Optional file which isolates the application authorization rules and
 * settings. If you are not using the authorization feature, you can
 * completely remove this file and corresponding inclusion (default:
 * security.php).
 */
use Zephyrus\Security\Authorization;

/**
 * The authorization class can be accessed anywhere in the application using
 * the getInstance method.
 */
$auth = Authorization::getInstance();

/**
 * The mode specifies the default behavior when no rule has been defined for a
 * route.
 * Blacklist: if a route has no rule, its automatically granted (everything is
 *            accessible by anyone if not stated otherwise).
 * Whitelist: if a route has no rule, its automatically denied (nothing is
 *            accessible if not stated otherwise).
 */
$auth->setMode(Authorization::MODE_BLACKLIST);

/**
 * Requirements definition for the authorization system. You must create your
 * own requirements based on the needs of your application. You can easily set
 * requirements based on a session data or ip address using, respectively, the
 * methods : addSessionRequirement() and addIpAddressRequirement().
 *
 * For any other needs (custom verifying, database calls, ...), you can use the
 * method addRequirement() which needs a callback.
 *
 * Example below can be read as : create a requirement named "admin" (which can
 * later be referenced) which need the $_SESSION key <AUTH_LEVEL> and the value
 * <admin>.
 */
$auth->addSessionRequirement('admin', 'AUTH_LEVEL', 'admin');

/**
 * Once the requirements are defined, you can start to protect your desired
 * routes. First argument of the protect method is a simple regex of the path
 * you wish to add authorization requirements. Second argument is the HTTP
 * method to validate which can be combined using the binary OR operator like
 * GET | POST. The ALL constant refers to GET | POST | PUT | DELETE. The last
 * argument is the requirement's name to fulfil to grant access.
 *
 * Example below can be read as : route /insert, for all HTTP methods, needs
 * the <admin> requirement to be fulfilled for the route to be accessible.
 */
$auth->protect('/insert', Authorization::ALL, 'admin');

/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * !!!!! EXAMPLE CODE to quickly fulfil the <admin> requirement.          !!!!!
 * !!!!! Normally, this sort of granting would be done in an              !!!!!
 * !!!!! authentication process.                                          !!!!!
 * !!!!!                                                                  !!!!!
 * !!!!! MUST BE DELETED                                                  !!!!!
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */
$_SESSION['AUTH_LEVEL'] = 'admin';