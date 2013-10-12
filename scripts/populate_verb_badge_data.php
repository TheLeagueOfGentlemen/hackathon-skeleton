<?php

require __DIR__.'/../scripts/functions.php';
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

$verbBadges = array(
    'Drink' => array(
        'gerund' => 'Drinking',
        'icon' => 'drinking-badge.png',
    ),
    'Eat' => array(
        'gerund' => 'Eating',
        'icon' => 'eating-badge.png',
    ),
    'Hike' => array(
        'gerund' => 'Hiking',
        'icon' => 'hiking-badge.png',
    ),
    'Ski (Alpine)' => array(
        'gerund' => 'Alpine Skiing',
    ),
    'Ski (Nordic)' => array(
        'gerund' => 'Nordic Skiing',
    ),
    'Sleep' => array(
        'gerund' => 'Sleeping',
    ),
    'Learn' => array(
        'gerund' => 'Learning',
    ),
    'Shop' => array(
        'gerund' => 'Shopping',
    ),
);

foreach ($verbBadges as $name => $data) {
    $verb = R::findOne('verb', 'name = ?', array($name));
    if (isset($data['gerund'])) {
        $verb->gerund = $data['gerund'];
    }
    if (isset($data['icon'])) {
        $verb->icon = $data['icon'];
    }
    R::store($verb);
}
