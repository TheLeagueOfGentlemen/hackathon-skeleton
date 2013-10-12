<?php

require __DIR__.'/../scripts/functions.php';
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

$users = R::findAll('user');
foreach ($users as $user) {
    $categories = R::findAll('category');
    foreach ($categories as $cat) {
        $user->sharedCategory[] = $cat;
    }
    R::store($user);
}