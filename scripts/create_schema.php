<?php

require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\DBAL\Schema\Table;
$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

$db = $app['db'];
$schema = $db::schema();

$schema->dropIfExists('user');
$schema->dropIfExists('user_profile');

$schema->create('user_profile', function($table) {
    $table->increments('id');
    $table->string('first_name', 100);
    $table->string('middle_initial', 1);
    $table->string('last_name', 100);
    $table->date('dob');
    $table->boolean('is_awesome');
    $table->integer('num_pimples');
    $table->dateTime('created_at');
    $table->dateTime('updated_at');
});

$schema->create('user', function($table) {
    $table->increments('id');
    $table->integer('profile_id')->unsigned();
    $table->foreign('profile_id')->references('id')->on('user_profile');
    $table->string('email', 255)->unique();
    $table->dateTime('created_at');
    $table->dateTime('updated_at');
});
