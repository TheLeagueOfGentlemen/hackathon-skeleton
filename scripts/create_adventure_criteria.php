<?php

require __DIR__.'/../scripts/functions.php';
require_once __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

R::exec('SET FOREIGN_KEY_CHECKS = 0');
R::exec('DROP TABLE adventurecriteria');
R::exec('DROP TABLE adventurecritera_attractions');
R::exec('DROP TABLE adventurecriteria_rejectedattractions');
R::exec('SET FOREIGN_KEY_CHECKS = 1');


R::exec("CREATE TABLE adventurecriteria (
    `id` int not null AUTO_INCREMENT,
    `user_id` int not null,
    `city_id` int null,
    `county_id` int null,
    `verb_id` int null,
    `lat` double not null,
    `lon` double not null,
    `completed_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB");

R::exec("CREATE TABLE adventurecritera_attractions (
    `id` int not null AUTO_INCREMENT,
    `adventurecriteria_id` int not null,
    `attraction_id` int not null,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB");

R::exec("CREATE TABLE adventurecriteria_rejectedattractions (
    `id` int not null AUTO_INCREMENT,
    `adventurecriteria_id` int not null,
    `attraction_id` int not null,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB");

R::exec("
    INSERT INTO adventurecriteria
        (user_id, city_id, county_id, verb_id, lat, lon)
    VALUES
        (1, 243, 13, 1, 44.490239, -73.18479)
");

R::exec("
    INSERT INTO adventurecritera_attractions
        (adventurecriteria_id, attraction_id)
    VALUES
        (1, 270)
");

R::exec("
    INSERT INTO adventurecriteria_rejectedattractions
        (adventurecriteria_id, attraction_id)
    VALUES
        (1, 434)
");
