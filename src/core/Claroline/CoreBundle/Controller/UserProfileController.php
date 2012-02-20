<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\UserType;

class UserProfileController extends Controller
{
    public function showEditProfileAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $formUserProfile = $this->createForm(new ProfileType(), $user);
        
        return $this->render(
                'ClarolineCoreBundle:UserProfile:show_edit.html.twig', array(
                'formuser' => $formUserProfile->createView()));
    }

    public function editProfileAction()
    {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $form = $this->get('form.factory')->create(new ProfileType(), $user);
        $form->bindRequest($request);

       if($form->isValid())
        {
            $user = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();         
            $em->persist($user);
            $em->flush();
            $this->get('security.context')->getToken()->setUser($user);
        }   
        
        $url = $this->generateUrl('claro_profile_show_edit');
        
        return $this->redirect($url);
    }

    public function showProfileAction($id)
    {
       $em = $this->getDoctrine()->getEntityManager();
       $user = $em->getRepository('ClarolineDocumentBundle:User')->find($id);
       
       return $this->render('ClarolinCoreBundle:UserProfile:show.html.twig', array(
                'user' => $user));
    }
}