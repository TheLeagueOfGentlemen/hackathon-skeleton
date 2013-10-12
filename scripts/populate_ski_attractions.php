<?php

require __DIR__.'/../scripts/functions.php';
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

$inFile = __DIR__.'/../data/ski_vt/ski_resorts_geocoded.json';

$skiData = json_decode(file_get_contents($inFile), true);
$skiProperties = array('name', 'website', 'description', 'email', 'lat', 'lon');

$cats = array();
foreach (['Nordic', 'Alpine'] as $name) {
    $categoryName = $name . ' Skiing';
    $cat = R::findOne('category', 'name = ?', array($categoryName));
    if ( ! $cat) {
        $cat = R::dispense('category');
        $cat->name = $categoryName;
        R::store($cat);
    }
    $cats[$name] = $cat;
}

foreach ($skiData as $data) {
    $attraction = R::dispense('attraction');
    $properties = array_pluck($data, $skiProperties);
    $properties['address'] = isset($data['address']['street']) ? $data['address']['street'] : null;
    $properties['town'] = isset($data['address']['town']) ? $data['address']['town'] : null;
    $properties['state'] = isset($data['address']['state']) ? $data['address']['state'] : null;
    $properties['zip_code'] = isset($data['address']['zipcode']) ? $data['address']['zipcode'] : null;
    $attraction->import($properties);

    foreach ($data['type'] as $catName) {
        if ( ! isset($cats[$catName])) {
            echo 'Could not find cat ' . $catName;
        }
        $cats[$catName]->sharedAttractions[] = $attraction;
    }
}

foreach ($cats as $cat) {
    R::store($cat);
}

