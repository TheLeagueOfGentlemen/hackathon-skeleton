<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Silex\Application;

/* ------------------------------------------------*/
/* Static
/*-------------------------------------------------*/

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
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

