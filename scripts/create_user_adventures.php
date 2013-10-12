<?php

require __DIR__.'/../scripts/functions.php';
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

R::exec('SET FOREIGN_KEY_CHECKS = 0');
R::exec('DROP TABLE user');
R::exec('DROP TABLE adventure');
R::exec('DROP TABLE attraction_user');
R::exec('DROP TABLE attraction_adventure');
R::exec('SET FOREIGN_KEY_CHECKS = 1');

$users = array(
    array(
        'first_name' => 'John',
        'last_name' => 'Smith',
        'email' => 'jsmith@leagueofgentleman.com',
        'password' => '1234',
        'preferred_categories' => '1,2,3',
        'num_completed_adventures' => 2
    )
);

$visitedAttractions = array(
    array(
        'user_id' => 1,
        'attraction_id' => 1
    )
);

$adventures = array(
    array(
        'user_id' => 1,
        'name' => 'win hackvt',
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => date('Y-m-d H:i:s', strtotime('+1 day')),
        'completed_at' => date('Y-m-d H:i:s')
    )
);

$adventureAttractions = array(
    array(
        'adventure_id' => 1,
        'attraction_id' => 1
    )
);

foreach ($users as $userData) {
    $user = R::dispense('user');
    $user->import($userData);
    R::store($user);
}

foreach ($adventures as $adventureData) {
    $adventure = R::dispense('adventure');
    $user = R::load('user', $adventureData['user_id']);
    $adventure->import($adventureData);
    $user->ownAdventure[] = $adventure;
    R::store($user);
}

foreach ($visitedAttractions as $attractionData) {
    $user = R::load('user', $attractionData['user_id']);
    $attraction = R::findOne('attraction');
    $user->sharedAttractions[] = $attraction;
    R::store($user);
}

foreach ($adventureAttractions as $attrationData) {
    $adventure = R::load('adventure', $attrationData['adventure_id']);
    $attraction = R::load('attraction', $attractionData['attraction_id']);
    $adventure->sharedAttractions[] = $attraction;
    R::store($adventure);
}
