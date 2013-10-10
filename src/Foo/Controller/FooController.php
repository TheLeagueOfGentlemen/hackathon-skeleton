<?php

namespace Foo\Controller;

use Foo\Model\User;

class FooController extends AbstractController
{

    public function indexAction()
    {
        return $this->render(
            'Foo/index.html.twig',
            array(
                'foo' => 'bar',
                'biz' => 'baz'
            )
        );
    }

    public function showUserAction(User $user)
    {
        return $this->render(
            'Foo/showUser.html.twig',
            compact('user')
        );
    }

}
