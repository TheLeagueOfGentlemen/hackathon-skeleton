<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

R::exec('SET FOREIGN_KEY_CHECKS = 0');
R::wipe('place');
R::wipe('category');
R::exec('SET FOREIGN_KEY_CHECKS = 1');

$data = json_decode(file_get_contents(__DIR__.'/../data/diginvt/parsed_data_geocoded.json'), true);
$placeProperties = array("town", "website", "name", "zipcode", "hours", "phone", "state", "address", "email", "description", "lat", "lon");
$categories = array();
foreach ($data['places'] as $placeData) {

    $place = R::dispense('place');
    $place->import(array_pluck($placeData, $placeProperties));

    foreach ($placeData['categories'] as $category) {
        if ( ! isset($categories[$category])) {
            $categories[$category] = R::dispense('category');
            $categories[$category]->name = $category;
        }
        $categories[$category]->sharedPlaces[] = $place;

    }
}
foreach ($categories as $cat) {
    R::store($cat);
}

function array_pluck($arr, $keys) {
    return array_reduce(
        $keys,
        function ($reduced, $key) use ($arr) {
            $reduced[$key] = isset($arr[$key]) ? $arr[$key] : null;
            return $reduced;
        },
        array()
    );
}
