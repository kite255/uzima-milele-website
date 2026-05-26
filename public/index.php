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

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// =========================================================================
// SULUHISHO LA GOOGLE CALLBACK (SULUHISHO LA NYA/PROXY LOSS)
// =========================================================================
// Angalia kama URL ina 'auth/google/callback' na kama seva ilipoteza query parameters njiani
if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], 'auth/google/callback') !== false) {
    $urlParts = explode('?', $_SERVER['REQUEST_URI']);
    if (isset($urlParts[1]) && !empty($urlParts[1])) {
        // Hapa tunalazimisha PHP isome vigezo kama 'code' kutoka kwenye REQUEST_URI halisi ya kivinjari
        $_SERVER['QUERY_STRING'] = $urlParts[1];
        parse_str($urlParts[1], $_GET);
    }
}
// =========================================================================

// Chakata ombi la mtumiaji sasa likiwa na data zote salama
$app->handleRequest(Request::capture());