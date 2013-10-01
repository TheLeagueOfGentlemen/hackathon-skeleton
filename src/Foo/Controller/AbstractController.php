<?php

namespace Foo\Controller;

abstract class AbstractController
{
	const FLASH_TYPE_SUCCESS = 'success';
	const FLASH_TYPE_ERROR = 'error';

    protected $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

	protected function flashError($message)
	{ 
		$this->setFlash(self::FLASH_TYPE_ERROR, $message);
	}

	protected function flashSuccess($message)
	{ 
		$this->setFlash(self::FLASH_TYPE_SUCCESS, $message);
	}

	protected function setFlash($type, $message)
	{ 
		$this->getSession()->set('flash', array(
			'type' => $type,
			'message' => $message
		));
   	}

	protected function getSession()
	{ 
   		return $this->app['session'];
	}

	protected function render($template, $params = array())
	{ 
   		return $this->app['twig']->render($template, $params);
	}

	protected function redirect($url)
	{ 
		return $this->app->redirect($url);
   	}

	protected function getValidator()
	{
		return $this->app['validator'];
	}

	protected function generateUrl($route, $params = array())
	{ 
		return $this->app['url_generator']->generate($route, $params);
   	}

	protected function create404($message)
	{ 
		return $this->app->abort(404, $message);
   	}

	protected function create500($message)
	{ 
		return $this->app->abort(500, $message);
   	}

	protected function get($service)
	{
		return $this->app[$service];
	}

}

