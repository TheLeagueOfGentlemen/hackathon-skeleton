<?php

require __DIR__.'/../scripts/functions.php';
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

R::exec('SET FOREIGN_KEY_CHECKS = 0');
R::exec('DROP TABLE city');
R::exec('DROP TABLE county');
R::exec('SET FOREIGN_KEY_CHECKS = 1');

$townsCounties = include __DIR__.'/../data/towns_counties_wikipedia.php';
$townsCounties = include __DIR__.'/../data/towns_counties.php';

$counties = array();
foreach ($townsCounties as $town => $county) {
    if ( ! isset($counties[$county])) {
        $counties[$county] = R::dispense('county');
        $counties[$county]->name = $county;
    }
    $county = $counties[$county];
    $city = R::dispense('city');
    $city->name = $town;
    $county->ownCity[] = $city;
}

foreach ($counties as $county) {
    R::store($county);
}
