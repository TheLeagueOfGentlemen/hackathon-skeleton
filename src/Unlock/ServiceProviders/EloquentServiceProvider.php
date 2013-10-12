<?php

namespace Unlock\ServiceProviders;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

class EloquentServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['db'] = $app->share(function ($app) {
            $capsule = new Capsule;
            $capsule->addConnection($app['db.options']);
            $capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            return $capsule;
        });
    }

    public function boot(Application $app)
    {
        $db = $app['db']; // resolve the database connection
    }
}
