<?php

/**
 * Laravel - Redireccionador a la carpeta public
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

define('LARAVEL_START', microtime(true));

// Register the Composer autoloader...
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    require __DIR__.'/vendor/autoload.php';
}

// Bootstrap Laravel and handle the request...
if (file_exists(__DIR__.'/bootstrap/app.php')) {
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->handleRequest(Illuminate\Http\Request::capture());
} else {
    echo "Inicializando sistema...";
}
