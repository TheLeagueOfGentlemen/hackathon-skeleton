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
/* Templates
/*-------------------------------------------------*/
// Display user profile
$app->get('/directions/{criteriaId}', function(Request $request, $criteriaId) use ($app) {
    $criteria = AdventureCriteria::find($criteriaId);
    if (!$criteria) die('Bad criteria id');

    $whereTo = $app['where_to'];
    $whereTo->setAdventureCriteria($criteria);
    $attractions = $whereTo->getAttractions();

    foreach ($attractions as $a) {
        $isExisting = false;
        foreach ($criteria->getAttractionCollection()->getResults() as $att) {
            if ($att->id === $a->id) {
                $isExisting = true;
            }
        }
        if ( ! $isExisting) {
            $criteria->getAttractionCollection()->attach($a['id']);
        }
    }
    $criteria->save();

    $data = compact('criteria', 'attractions');
    return $app['twig']->render('directions.html.twig', $data);
})
->bind('directions')
;

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

    foreach ($attractions as $a) {
        $isExisting = false;
        foreach ($criteria->getAttractionCollection()->getResults() as $att) {
            if ($att->id === $a->id) {
                $isExisting = true;
            }
        }
        if ( ! $isExisting) {
            $criteria->getAttractionCollection()->attach($a['id']);
        }
    }
    $criteria->save();

    $data = compact('criteria', 'attractions');

    return $app['twig']->render('search_results.html.twig', $data);
})->bind('search_results');

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

    return $app->redirect(
        $app['url_generator']->generate('directions', array(
            'criteriaId' => $criteria->id
        ))
    );
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

    // Remove the attraction
    $attractions = $crit->getAttractions();
    $foundRemove = false;
    foreach ($attractions as $key => $a) {
        if ($a->id == $attractionId) {
            $foundRemove = true;
            $crit->attractions()->detach($a);
            $crit->rejected_attractions()->attach($a);
        }
    }

    $attractions = $app['where_to']->setAdventureCriteria($crit)->getAttractions();
    $new_attraction = $attractions->last();
    $crit->attractions()->attach($new_attraction);
    $crit->save();

    $data = array(
        'id' => $new_attraction->id,
        'name' => $new_attraction->name,
        'lat' => $new_attraction->lat,
        'lon' => $new_attraction->lon,
        'teaser' => $new_attraction->getTeaser(),
        'city' => array(
            'name' => $new_attraction->city()->first() ? $new_attraction->city->name : ''
        )
    );
    return new JsonResponse($data);
});

$app->get('/preference/{id}', function(Request $request, $id) use ($app) {
    $user = User::find($id);

    if ($request->isMethod('POST')) {
        $app['user_manager']->setPreferences($id, $request->request->all());
    }
    $prefs = $app['user_manager']->getPreferences($id)->get()->toArray();
    $newprefs = array();
    foreach ($prefs as $k => $v) {
        $newprefs[$k] = $v['name']; // id -> name
    }

    $all_categories = Category::all()->toArray();
    return $app['twig']->render('user_prefs.html.twig',
                                array('user' => $user->toArray(),
                                      'prefs' => $newprefs,
                                      'categories' => $all_categories,
                                      )
                                );
})
->method('GET|POST')
->bind('preferences')
;



/* ------------------------------------------------*/
/* App
/*-------------------------------------------------*/

$app->error(function (\Exception $e, $code) use ($app) {
    if ($code === 404) {
        return new Response($app['twig']->render('404.html.twig', array('code' => $code)), $code);
    }

    throw $e;
});
