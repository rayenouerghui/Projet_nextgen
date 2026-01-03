<?php
/**
 * Global Path Constants
 * Include this file at the TOP of every controller/view
 */

define('ROOT_PATH', dirname(__DIR__));
define('CONFIG_PATH', ROOT_PATH . '/config');
define('CONTROLLERS_PATH', ROOT_PATH . '/controller');
define('MODELS_PATH', ROOT_PATH . '/models');
define('SERVICES_PATH', ROOT_PATH . '/services');
define('VIEWS_PATH', ROOT_PATH . '/view');
define('WEB_ROOT', '/project%20nextgen');
define('WEB_API', WEB_ROOT . '/view/api');

/**
 * Helper function for redirects
 */
function redirect($path) {
    header("Location: " . WEB_ROOT . '/' . ltrim($path, '/'));
    exit;
}
