<?php

use Symfony\Component\HttpFoundation\Response;

/* ------------------------------------------------*/
/* Static
/*-------------------------------------------------*/

$app->get('/', function () use ($app) {
    $context = array(
        'hello' => 'Doesnt do much',
        'app_name' => array('name' => 'Foo', 'link' => 'foo'),
        );
    return $app['twig']->render('index.html.twig', $context);
})
->bind('home')
;

/* ------------------------------------------------*/
/* Foo Controller
/*-------------------------------------------------*/
$app->mount('/foo', include __DIR__.'/controllers/foo.php');

/* ------------------------------------------------*/
/* App
/*-------------------------------------------------*/

$app->error(function (\Exception $e, $code) use ($app) {
    if ($code === 404) {
        return new Response($app['twig']->render('404.html.twig', array('code' => $code)), $code);
    }

    throw $e;
});
