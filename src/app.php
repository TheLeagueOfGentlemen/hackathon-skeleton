<?php

use Silex\Application,
    Silex\Provider;

use Foo\Controller;
use Foo\Model\User;

$app = new Application();
$app['debug'] = true;
$app->register(new Provider\UrlGeneratorServiceProvider());
$app->register(new Provider\ValidatorServiceProvider());
$app->register(new Provider\ServiceControllerServiceProvider());
$app->register(new Provider\TwigServiceProvider(), array(
    'twig.path'    => array(__DIR__.'/../templates'),
    'twig.options' => array('cache' => __DIR__.'/../cache/twig'),
));

// Session
$app->register(new Provider\SessionServiceProvider());

// Database
$app->register(new Unlock\ServiceProviders\EloquentServiceProvider());

/*$app['foo.controller'] = $app->share(function() use ($app) {
    return new Foo\Controller\FooController($app);
});

// Param converters: Convert url parameters into business objects
$app['user.param_converter'] = function() use ($app) {
    return function ($id) use ($app) {
        $user = User::with('profile')->find($id);
        if (null === $user) $app->abort(404, 'Could not load user with id '. $id);
        return $user;
    };
};

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    // add custom globals, filters, tags, ...
    return $twig;
}));

/**
 * Before Filters
 */
/*$app->before( function() use ( $app ) {
    $flash = $app[ 'session' ]->get( 'flash' );
    $app[ 'session' ]->set( 'flash', null );

    if ( !empty( $flash ) ) {
        $app[ 'twig' ]->addGlobal( 'flash', $flash );
    }
});*/

return $app;
