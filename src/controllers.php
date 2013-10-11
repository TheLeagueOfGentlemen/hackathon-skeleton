<?php

use Symfony\Component\HttpFoundation\Response,
    Unlock\Models\User,
    Unlock\Models\Adventure;

/* ------------------------------------------------*/
/* Static
/*-------------------------------------------------*/

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('home')
;

// User
$app->get('/user', function() use ($app) {
    return (string)User::all();
});

$app->get('/user/{id}', function($id) use ($app) {
});

// Adventure
$app->get('/adventure/{id}', function($id) use ($app) {

});

$app->get('/adventure', function() {

});

$app->put('/adventure/{id}', function($id) use ($app) {

});

$app->post('/adventure', function() {

});


/* ------------------------------------------------*/
/* App
/*-------------------------------------------------*/

$app->error(function (\Exception $e, $code) use ($app) {
    if ($code === 404) {
        return new Response($app['twig']->render('404.html.twig', array('code' => $code)), $code);
    }

    throw $e;
});
