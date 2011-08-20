<?php

namespace Claroline\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ProfileController extends Controller
{
    public function registerAction()
    {
        $user = new \Claroline\UserBundle\Entity\User();
        $form = $this->createForm(new \Claroline\UserBundle\Form\UserType(), $user);

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

        return $this->render('ClarolineUserBundle:Profile:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}