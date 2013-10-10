<?php

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

return $foo;
