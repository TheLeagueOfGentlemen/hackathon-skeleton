<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

use RedBean_Facade as R;

R::setup(sprintf('mysql:host=%s;dbname=%s', $app['db.options']['host'], $app['db.options']['database']), $app['db.options']['username'], $app['db.options']['password']);

R::exec('SET FOREIGN_KEY_CHECKS = 0');
R::exec('DROP TABLE attraction');
R::exec('DROP TABLE attraction_category');
R::exec('DROP TABLE category');
R::exec('DROP TABLE verb');
R::exec('SET FOREIGN_KEY_CHECKS = 1');
