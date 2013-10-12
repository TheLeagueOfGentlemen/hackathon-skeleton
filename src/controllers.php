<?php

use Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Unlock\Models\User,
    Unlock\Models\Adventure,
    Unlock\Models\Attraction,
    Unlock\Models\Category,
    Unlock\Models\Verb;

/* ------------------------------------------------*/
/* Static
/*-------------------------------------------------*/

// Middleware
$app->before(function (Request $request) use ($app) {
    // Mock User
    $app['UserID'] = 1;
});

$app->get('/', function () use ($app) {
    $data = array(
        'verbs' => Verb::all()
    );
    return $app['twig']->render('index.html.twig', $data);
})
->bind('home')
;

// Users
// Dump users
$app->get('/user', function(Request $request) use ($app) {
    return (string)$app['user_manager']->getUsers();
});

// Display user profile
$app->get('/user/{id}', function(Request $request, $id) use ($app) {
    $user = $app['user_manager']->getUser($id);
    if ($request->isXmlHttpRequest()) {
        return (string)$user;
    } else {
        return $app['twig']->render('user.html.twig', array($user->toArray()));
    }
});


// Display new adventure
$app->get('/adventure', function(Request $request) use ($app) {
    // Term Search
    if ($request->get('verb') && $request->get('term')) {
        return new JsonResponse($app['adventure_manager']->findLocationsByTerm($request->get('verb'), $request->get('term')));
    }

    $categories = $app['adventure_manager']->getCategories();
    $user = $app['user_manager']->getUser($app['UserID']);

    return new JsonResponse(array('User' => $user->toArray(), 'Categories' => $categories->toArray()));
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

// Categories
$app->get('/category', function() use ($app) {
    return new JsonResponse($app['adventure_manager']->getCategories()->toArray());
});

// Get all attractions from specific category
$app->get('/category/{id}/attractions', function($id) use ($app) {
    return new JsonResponse($app['adventure_manager']->getCategoryAttractions($id)->toArray());
});

$app->get('/category/{id}', function($id) use ($app) {
    return new JsonResponse($app['adventure_manager']->getCategory($id)->toArray());
});

// Attractions
$app->get('/attraction/{id}', function($id) use ($app) {
    return new JsonResponse($app['adventure_manager']->getAttraction($id)->toArray());
});

$app->get('/verb/{id}', function($id) use ($app) {
    $verb = Verb::find($id);
    $cats = $verb->getCategories()->get();
    return new JsonResponse($cats->toArray());
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
