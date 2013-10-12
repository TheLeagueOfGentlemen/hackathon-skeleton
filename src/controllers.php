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
        var_dump($request->get('category'));
        var_dump($catID);
        var_dump($term);
        return;
        $result = $app['adventure_manager']->findLocationsByTerm($request->get('category'), $term);
        var_dump($result);
        return;
    }

    $categories = $app['adventure_manager']->getCategories();
    $user = $app['user_manager']->getUser($app['UserID']);

    return json_encode(array('User' => $user->toArray(), 'Categories' => $categories->toArray()));
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
    return (string)$app['adventure_manager']->getCategories();
});

// Get all attractions from specific category
$app->get('/category/{id}/attractions', function($id) use ($app) {
    return (string)$app['adventure_manager']->getCategoryAttractions($id);
});

$app->get('/category/{id}', function($id) use ($app) {
    return (string)$app['adventure_manager']->getCategory($id);
});

// Attractions
$app->get('/attraction/{id}', function($id) use ($app) {
    return (string)$app['adventure_manager']->getAttraction($id);
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
