<?php

use Symfony\Component\HttpFoundation\Request;

$foo = $app['controllers_factory'];

$foo
    ->match('/', 'foo.controller:indexAction')
    ->method('GET|POST')
    ->bind('foo_index')
;

$foo
    ->match('/user/{user}', 'foo.controller:showUserAction')
    ->convert('user', $app['user.param_converter'])
    ->method('GET')
    ->bind('foo_show_user')
;
$foo
    ->match('/energy', 'foo.controller:showEnergy')
    ->method('GET|POST')
    ->bind('foo_show_energy')
;
$foo
    ->match('/energy/', 'foo.controller:showEnergy')
    ->method('GET|POST')
    ->bind('foo_show_energy')
;
$foo
    ->match('/energy/{town}', 'foo.controller:showEnergyTown')
    ->convert('town', function($town) {
        return ucwords(strtolower($town));})
    ->method('GET')
    ->bind('foo_show_energy_town')
;

return $foo;
