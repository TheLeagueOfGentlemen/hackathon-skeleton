<?php

require __DIR__.'/../scripts/functions.php';
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

$verbCategories = include __DIR__.'/../data/category_verbs.php';

$verbs = array();
foreach ($verbCategories as $verb => $categories) {
    $v = R::dispense('verb');
    $v->name = $verb;
    foreach ($categories as $category) {
        $cat = R::findOne("category", "name=?", array($category));
        $v->ownCategory[] = $cat;
    }
    R::store($v);
}