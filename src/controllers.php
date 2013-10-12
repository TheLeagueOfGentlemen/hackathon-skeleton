<?php

use Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Unlock\Models\User,
    Unlock\Models\Adventure,
    Unlock\Models\Attraction,
    Unlock\Models\Category,
    Unlock\Models\Verb,
    Unlock\Models\AdventureCriteria;

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
    if ($request->get('category') && $request->get('term')) {
        return new JsonResponse($app['adventure_manager']->findLocationsByTerm($request->get('category'), $request->get('term')));
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

$app->get('/testcriteria/{id}', function($id) use ($app) {
    $advCrit = AdventureCriteria::find($id);
    ob_start();
    echo '<pre>' . var_dump($advCrit->toArray()) . '</pre>';
    echo '<pre>' . var_dump($advCrit->user->toArray()) . '</pre>';
    echo '<pre>' . var_dump($advCrit->city->toArray()) . '</pre>';
    echo '<pre>' . var_dump($advCrit->county->toArray()) . '</pre>';
    echo '<pre>' . var_dump($advCrit->verb->toArray()) . '</pre>';
    echo '<pre>' . var_dump($advCrit->getCategories()->toArray()) . '</pre>';
    echo '<pre>' . var_dump($advCrit->getAttractions()->toArray()) . '</pre>';
    echo '<pre>' . var_dump($advCrit->getRejectedAttractions()->toArray()) . '</pre>';
    return ob_get_clean();
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

$app->get('/verb', function() use ($app) {
    return new JsonResponse(Verb::all()->toArray());
});

$app->get('/verb/{id}', function($id) use ($app) {
    $verb = Verb::find($id);
    return new JsonResponse($verb->getCategories()->get()->toArray());
});

// WhereTo
$app->get('/whereto/{id}', function($id) use ($app) {
    $crit = AdventureCriteria::find($id);
    return new JsonResponse($app['where_to']->setAdventureCriteria($crit)->getAttractions());
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
