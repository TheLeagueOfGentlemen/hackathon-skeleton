<?php

use Symfony\Component\ClassLoader\DebugClassLoader;
use Symfony\Component\HttpKernel\Debug\ErrorHandler;
use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

require_once __DIR__.'/../vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('html_errors', 1);

DebugClassLoader::enable();
ErrorHandler::register();
if ('cli' !== php_sapi_name()) {
    ExceptionHandler::register();
}

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/dev.php';
require __DIR__.'/../src/controllers.php';

$app->run();
