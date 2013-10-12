<?php

require __DIR__.'/../scripts/functions.php';
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

$townMappings = include __DIR__.'/../data/town_name_mapping.php';

$attractions = R::find('attraction');
$missingTowns = [];
$cities = [];
foreach ($attractions as $attraction) {
    if (empty($attraction->town)) {
        continue;
    }

    $city = R::findOne('city', 'name = ?', array($attraction->town));
    if (empty($city) && isset ($townMappings[$attraction->town])) {
        $city = R::findOne('city', 'name = ?', array($townMappings[$attraction->town]));
    }

    if (empty($city)) {
        $missingTowns[] = $attraction->town;
        continue;
    }

    $city->ownAttraction[] = $attraction;
    $cities[] = $city;
}

foreach ($cities as $city) {
    R::store($city);
}

foreach (array_unique($missingTowns) as $town) {
    echo $town;
    echo PHP_EOL;
}
