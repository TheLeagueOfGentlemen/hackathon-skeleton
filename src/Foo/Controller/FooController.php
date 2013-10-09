<?php

namespace Foo\Controller;

use Foo\Model\User;
use Symfony\Component\HttpFoundation\Request;


function loadEnergyData()
{
    $filepath = '/home/brian/web/hackathon-skeleton/public_html/data/vt_energy_towns.json';

    $json = json_decode(file_get_contents($filepath), true);
    return $json;
}

class FooController extends AbstractController
{

    public function indexAction()
    {
        return $this->render(
            'Foo/index.html.twig',
            array(
                'foo' => 'bar',
                'biz' => 'baz',
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

    public function showEnergy(Request $request)
    {
        if ("POST" == $request->getMethod()) {
            $context = array('post' => true, 'req'=> $request->get('town'));
        } else {
            $context = array('post' => false, 'req' => null);
        }
        return $this->render('Foo/showEnergy.html.twig', $context);
    }

    public function showEnergyTown($town)
    {
        $data = loadEnergyData();
        if (array_key_exists($town, $data)) {
            return $this->render(
                'Foo/showTownEnergy.html.twig',
                array(
                    'town' => $town,
                    'json_data' => json_encode($data[$town]),
                    )
            );
        } else {
            $msg = "The town \"$town\" does not exist.";
            $this->flashError($msg);
            return $this->redirect("/index.php/foo/energy");
        }
    }

}
