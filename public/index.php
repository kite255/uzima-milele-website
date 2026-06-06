<?php

ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');
set_time_limit(300);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Fix lost query string on some hosting/proxy environments
|--------------------------------------------------------------------------
| Google returns:
| /auth/google/callback?state=xxx&code=xxx
|
| But on your server Laravel receives:
| /auth/google/callback
|
| This block restores query parameters before Laravel captures the request.
*/
if (
    isset($_SERVER['REQUEST_URI']) &&
    str_contains($_SERVER['REQUEST_URI'], '?') &&
    (empty($_SERVER['QUERY_STRING']) || empty($_GET))
) {
    $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);

    if (! empty($queryString)) {
        $_SERVER['QUERY_STRING'] = $queryString;

        parse_str($queryString, $queryParams);

        $_GET = array_merge($_GET, $queryParams);
        $_REQUEST = array_merge($_REQUEST, $queryParams);
    }
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Handle the request...
$app->handleRequest(Request::capture());