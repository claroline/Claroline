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
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());

                $user->setPassword($password);

                $em = $this->get('doctrine')->getEntityManager();
                $em->persist($user);
                $em->flush();

                return $this->redirect($this->generateUrl('claro_core_portal'));
            }
        }

        return $this->render('ClarolineUserBundle:Profile:form.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}