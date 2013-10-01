<?php

require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\DBAL\Schema\Table;
use Foo\Model\User,
	Foo\Model\UserProfile;

$app = require __DIR__.'/../src/app.php';
require __DIR__.'/../app/config/prod.php';

$schema = $app['db'];

$userProfile = new UserProfile(array(
	'first_name' => 'Bob',
	'middle_initial' => 	'Q',
	'last_name' => 	'Hoskins'
));

$userProfile->save();

$user = new User(array(
	'email' => 'foo@bar.com'
));

$user->profile()->associate($userProfile);
$user->save();
