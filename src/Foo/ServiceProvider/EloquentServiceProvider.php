<?php

namespace Foo\ServiceProvider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;
use Doctrine\Common\EventManager;
use Symfony\Bridge\Doctrine\Logger\DbalLogger;
use Illuminate\Database\Capsule\Manager as Capsule;

class EloquentServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['db.default_options'] = array(
			'driver'    => 'mysql',
			'host'      => 'localhost',
			'database'  => null,
			'username'  => null,
			'password'  => null,
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
        );

        $app['dbs.options.initializer'] = $app->protect(function () use ($app) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app['dbs.options'])) {
                $app['dbs.options'] = isset($app['db.options']) ? $app['db.options'] : $app['db.default_options'];
            }
        });

        $app['dbs'] = $app->share(function ($app) {
            $app['dbs.options.initializer']();

            $dbs = new \Pimple();
			$config = $app['dbs.options'];
			$dbs['db'] = $dbs->share(function ($dbs) use ($config) {
				$capsule = new Capsule;
				$capsule->addConnection($config);
				$capsule->setEventDispatcher(new \Illuminate\Events\Dispatcher(new \Illuminate\Container\Container));
				$capsule->setAsGlobal();
				$capsule->bootEloquent();

				return $capsule;
			});

            return $dbs;
        });

        // shortcuts for the "first" DB
        $app['db'] = $app->share(function ($app) {
            $dbs = $app['dbs'];

            return $dbs['db'];
        });
    }

    public function boot(Application $app)
    {
    }
}
