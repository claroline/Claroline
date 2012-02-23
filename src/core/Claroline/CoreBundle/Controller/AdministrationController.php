<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\GroupType;
use Symfony\Component\HttpFoundation\Response;

class AdministrationController extends Controller
{
    public function indexAction()
    {
        return $this->render('ClarolineCoreBundle:Administration:administration.html.twig');
    }

    public function showFormAddUserAction()
    {
        $formUserProfile = $this->createForm(new ProfileType(
            $this->get('security.context')->getToken()->getUser()->getOwnedRoles())
        );

        return $this->render(
            'ClarolineCoreBundle:Administration:add_user.html.twig', array(
            'form_complete_user' => $formUserProfile->createView())
        );
    }

    public function addUserAction()
    {
        $request = $this->get('request');
        $user = new User();
        $form = $this->get('form.factory')->create(new ProfileType($this->get('security.context')->getToken()->getUser()->getOwnedRoles()), $user);
        $form->bindRequest($request);

        if ($form->isValid())
        {
            $user = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($user);
            $em->flush();
        }
        
        $url = $this->generateUrl('claro_admin_user_list');
        
        return $this->redirect($url);
    }

    public function listUserAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('ClarolineCoreBundle:User')->findAll();

        return $this->render('ClarolineCoreBundle:Administration:user_list.html.twig', array(
                'users' => $users));
    }

    public function showFormCreateGroupAction()
    {
        $group = new Group();
        $formGroup = $this->createForm(new GroupType(), $group);
        
        return $this->render('ClarolineCoreBundle:Administration:create_group.html.twig', array(
                'form_group' => $formGroup->createView()));
    }

    public function createGroupAction()
    {
        $request = $this->get('request');
        $group = new Group();
        $form = $this->get('form.factory')->create(new GroupType(), $group);
        $form->bindRequest($request);

        if ($form->isValid())
        {
            $role = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($role);
            $em->flush();
        }
        else
        {
            return new Response("formulaire non valide");
        }
        
        $url = $this->generateUrl('claro_admin_group_list');
        
        return $this->redirect($url);
    }

    public function listGroupAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $groups = $em->getRepository('ClarolineCoreBundle:Group')->findAll();
        
        return $this->render('ClarolineCoreBundle:Administration:group_list.html.twig', array(
                'groups' => $groups));
    }
    
    public function listUserPerGroupAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($id);
        
        return $this->render('ClarolineCoreBundle:Administration:user_per_group_list.html.twig', array('group' => $group));
    }
    
    public function listAddUserToGroupAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($id);
        $users = $em->getRepository('ClarolineCoreBundle:User')->findAll();
        
         return $this->render('ClarolineCoreBundle:Administration:add_user_to_group.html.twig', array('group' => $group, 'users' => $users));
    }
    
    public function addUserToGroupAction($idGroup, $idUser)
    {
         $em = $this->getDoctrine()->getEntityManager();
         $user = $em->getRepository('ClarolineCoreBundle:User')->find($idUser);
         $group = $em->getRepository('ClarolineCoreBundle:Group')->find($idGroup);
         $group->addUser($user);
         $em->persist($group);
         $em->flush();  
         $url = $this->generateUrl('claro_admin_group_list');
         
         return $this->redirect($url);   
    }
    
    public function deleteUserFromGroupAction($idGroup, $idUser)
    {
         $em = $this->getDoctrine()->getEntityManager();
         $user = $em->getRepository('ClarolineCoreBundle:User')->find($idUser);
         $group = $em->getRepository('ClarolineCoreBundle:Group')->find($idGroup);
         $group->removeUser($user);
         $em->persist($group);
         $em->flush();  
         $url = $this->generateUrl('claro_admin_group_list');
         
         return $this->redirect($url); 
    }
    
    public function deleteGroupAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($id);
        $em->remove($group);
        $em->flush();
        $url = $this->generateUrl('claro_admin_group_list');
         
         return $this->redirect($url); 
    }
    
}