<?php

use Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Unlock\Models\User,
    Unlock\Models\Adventure,
    Unlock\Models\Attraction,
    Unlock\Models\Category;

/* ------------------------------------------------*/
/* Static
/*-------------------------------------------------*/

// Middleware
$app->before(function (Request $request) use ($app) {
    // Mock User
    $app['UserID'] = 1;
});

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})
->bind('home')
;

// Users
// Dump users
$app->get('/user', function(Request $request) use ($app) {
    return (string)User::all();
});

// Display user profile
$app->get('/user/{id}', function(Request $request, $id) use ($app) {
    $user = User::find($id);
    if ($request->isXmlHttpRequest()) {
        return (string)$user;
    } else {
        return $app['twig']->render('user.html.twig', array($user->toArray()));
    }
});


// Display new adventure
$app->get('/adventure', function(Request $request) use ($app) {
    $request = Request::create('/category');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    $categories = json_decode($app->handle($request, HttpKernelInterface::SUB_REQUEST, false)->getContent(), 'true');

    $request = Request::create('/user/' . $app['UserID']);
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    $user = json_decode($app->handle($request, HttpKernelInterface::SUB_REQUEST, false)->getContent(), 'true');

    return json_encode(array('User' => $user, 'Categories' => $categories));

    // Return adventure page twig template
    //return $app['twig']->rend('adventure.html.twig', array('categories' => , 'attractions' => ''));
});

// Adventures
// Display adventure
$app->get('/adventure/{id}', function($id) use ($app) {
    $adventure = adventure::find($id);
    if ($app['isAjax']) {
        return (string)$adventure;
    } else {
        return $app['twig']->render('user.html.twig', array($adventure->toArray()));
    }
});

// Update adventure
$app->put('/adventure/{id}', function($id) use ($app) {

});

// Create new adventure
$app->post('/adventure', function() {

});

// Categories
$app->get('/category', function() use ($app) {
    return (string)Category::all();
});

// Get all attractions from specific category
$app->get('/category/{id}/attractions', function($id) use ($app) {
    $attractions = Cateogry::find($id)->attractions();
});

$app->get('/category/{id}', function($id) use ($app) {
    return (string)Category::find($id);
});

// Attractions
$app->get('/attraction/{id}', function($id) use ($app) {
    return (string)Attraction::find($id);
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
