<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

$inFile = __DIR__.'/../data/diginvt/parsed_data.json';
$outFile = __DIR__.'/../data/diginvt/parsed_data_geocoded.json';

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

function add_format_field($data, array $keys, $format, $key) {
    $data[$key] = call_user_func_array('sprintf', array_merge(array($format), array_pluck($data, $keys)));
    return $data;
}

function make_add_format_field_fn(array $keys, $format, $key) {
    return function($data) use ($keys, $format, $key) {
        return call_user_func('add_format_field', $data, $keys, $format, $key);
    };
}

function console_write($message) {
    echo $message . PHP_EOL;
}

function make_add_geolocation_fn($geocoder, $addressKey = 'address_string', $latKey = 'lat', $lonKey = 'lon', $sleep = 1) {
    return function ($item) use ($geocoder, $addressKey, $latKey, $lonKey, $sleep) {
        try {
            $geocoded = $geocoder->geocode($item[$addressKey]);
            $item[$latKey] = $geocoded->getLatitude();
            $item[$lonKey] = $geocoded->getLongitude();
        } catch (\Exception $e) {
            $item['geolocation_failed_message'] = $e->getMessage();
        }
        if ($sleep) {
            sleep($sleep);
        }
        return $item;
    };
};

// Configure Geocoder with google maps provider
$adapter  = new \Geocoder\HttpAdapter\GuzzleHttpAdapter();
$geocoder = new \Geocoder\Geocoder();
$chain    = new \Geocoder\Provider\ChainProvider(array(
    new \Geocoder\Provider\GoogleMapsProvider($adapter)
));
$geocoder->registerProvider($chain);

// Load dig in data
console_write('Reading from ' . $inFile);
$data = json_decode(file_get_contents($inFile), true);

$data['places'] = array_map(make_add_geolocation_fn($geocoder), 
    array_map(make_add_format_field_fn(array('address', 'town', 'state', 'zipcode'), '%s, %s, %s %s', 'address_string'), $data['places']));

console_write('Writing to ' . $outFile);
file_put_contents($outFile, json_encode($data, JSON_PRETTY_PRINT));
