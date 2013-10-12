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
    $data = array(
        'verbs' => Verb::all()
    );
    return $app['twig']->render('index.html.twig', $data);
})
->bind('home')
;

/* ------------------------------------------------*/
/* Users
/*-------------------------------------------------*/
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

/* ------------------------------------------------*/
/* Adventures
/*-------------------------------------------------*/

// Display new adventure
$app->get('/adventure', function(Request $request) use ($app) {
    // Term Search
    if ($request->get('verb') && $request->get('term')) {
        $verb = Verb::find($request->get('verb'));
        return new JsonResponse($app['adventure_manager']->findLocationsByTerm($verb, $request->get('term')));
    }

    $categories = $app['adventure_manager']->getCategories();
    $user = $app['user_manager']->getUser($app['UserID']);

    return new JsonResponse(array('User' => $user->toArray(), 'Categories' => $categories->toArray()));
});

$app->get('/adventure/results', function(Request $request) use ($app) {
    $critID = $request->get('criteria');
    if (!$critID) {
        die('where my criteria at?');
    }

    $criteria = AdventureCriteria::find($critID);
    var_dump($critID);
});

// Display new adventure
$app->get('/adventure/results/{criteriaId}', function(Request $request, $criteriaId) use ($app) {
    $criteria = AdventureCriteria::find($criteriaId);
    if (!$criteria) die('Bad criteria id');

    $whereTo = $app['where_to'];
    $whereTo->setAdventureCriteria($criteria);
    $attractions = $whereTo->getAttractions();

    $data = compact('criteria', 'attractions');

    return $app['twig']->render('search_results.html.twig', $data);
});

// Update adventure
$app->put('/adventure/{id}', function($id) use ($app) {

});

/* ------------------------------------------------*/
/* Adventure Criteria
/*-------------------------------------------------*/

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

$app->post('/criteria', function(Request $request) use ($app) {
    $data = array_merge($request->request->all(), array('user_id' => $app['UserID']));
    $data['attractions'] = isset($data['attraction_id']) ? array($data['attraction_id']) : array();
    unset($data['attraction_id']);

    $criteria = $app['adventure_manager']->persistAdventureCriteria($data);

    return $app->redirect('/adventure?criteria=' . $criteria->id);
});

/* ------------------------------------------------*/
/* Categories
/*-------------------------------------------------*/
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

/* ------------------------------------------------*/
/* Attractions
/*-------------------------------------------------*/
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
    $attractions = $app['where_to']->setAdventureCriteria($crit)->getAttractions();
    var_dump(array_map(function ($a) {return $a->name;}, iterator_to_array($attractions)));
    return new JsonResponse($attractions);
});



// WhereTo
$app->get('/criteria/{criteriaId}/attraction/replace/{attractionId}', function($criteriaId, $attractionId) use ($app) {
    $crit = AdventureCriteria::find($criteriaId);

    $attractions = $crit->getAttractions();

    $attractions = $app['where_to']->setAdventureCriteria($crit)->getAttractions();

    return new JsonResponse($attractions);
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
