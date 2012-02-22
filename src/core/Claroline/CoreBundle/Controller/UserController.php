<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\UserType;

//TODO redirections

class UserController extends Controller
{
    public function showEditProfileAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $formUserProfile = $this->createForm(new ProfileType($user->getOwnedRoles()), $user);

        return $this->render(
            'ClarolineCoreBundle:User:show_edit.html.twig', array(
            'form_complete_user' => $formUserProfile->createView()));
    }

    public function editProfileAction()
    {
        $request = $this->get('request');
        $user = $this->get('security.context')->getToken()->getUser();
        $form = $this->get('form.factory')->create(new ProfileType($user->getOwnedRoles()), $user);
        $form->bindRequest($request);

        if ($form->isValid())
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
        $user = $em->getRepository('ClarolineCoreBundle:User')->find($id);

        return $this->render('ClarolineCoreBundle:User:user_show.html.twig', array(
                'user' => $user));
    }

    public function getListProfileAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('ClarolineCoreBundle:User')->findAll();

        return $this->render('ClarolineCoreBundle:User:user_list.html.twig', array(
                'users' => $users));
    }

    public function deleteProfileAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('ClarolineCoreBundle:User')->find($id);
        $em->remove($user);
        $em->flush();
        
        //this will be changed later
        return $this->redirect($this->generateUrl('claro_admin_user_list'));
    }

    //TODO: self explanatory
    public function searchUserAction($options)
    {
        
    }
}