<?php

namespace Claroline\RegisterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\UserBundle\Entity\User;
use Claroline\UserBundle\Form\UserType;

class MainController extends Controller
{
    public function indexAction()
    {
        $user = new User();
        $form = $this->createForm(new UserType(), $user);

        $request = $this->get('request');
        if ($request->getMethod() == 'POST')
        {
            $form->bindRequest($request);

            if ($form->isValid())
            {
                $this->get('claroline.user.manager')->create($user);

                return $this->redirect($this->generateUrl('claro_core_desktop'));
            }
        }

        return $this->render('ClarolineRegisterBundle:Main:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}