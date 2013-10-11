<?php

require __DIR__.'/../scripts/functions.php';
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

$inFile = __DIR__.'/../data/trailfinder/trailfinder.json';
$trailData = json_decode(file_get_contents($inFile), true);

$trailProperties = array('name');
$catName = 'Trails';
$cat = R::find('category', 'name = ?', array($catName));
if ( ! $cat) {
    $cat = R::dispense('category');
    $cat->name = $catName;
} else {
    $cat = array_shift($cat);
}

foreach ($trailData['trails'] as $data) {
    $attraction = R::dispense('attraction');
    $properties = array_pluck($data, $trailProperties);
    $properties['description'] = $data['features'];
    $properties['address'] = isset($data['address']['road']) ? $data['address']['road'] : null;
    $properties['town'] = isset($data['address']['city']) ? $data['address']['city'] : null;
    $properties['state'] = isset($data['address']['state']) ? $data['address']['state'] : null;
    $properties['zip_code'] = isset($data['address']['postcode']) ? $data['address']['postcode'] : null;
    $properties['lat'] = $data['trailmarker']['lat'];
    $properties['lon'] = $data['trailmarker']['lng'];
    $properties['trail_length'] = $data['length'];
    $properties['trail_length_units'] = $data['lengthunits'];
    $properties['trail_length_units'] = $data['lengthunits'];
    $trailLine = array_shift($data['TrailLines']);
    $properties['trail_latlon'] = $trailLine['latlng'];
    $properties['trail_latlon_length'] = $trailLine['latlnglength'];
    $attraction->import($properties);

    $cat->sharedAttractions[] = $attraction;
}

R::store($cat);
