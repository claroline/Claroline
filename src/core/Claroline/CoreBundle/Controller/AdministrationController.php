<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Form\ProfileType;
use Claroline\CoreBundle\Form\GroupType;
use Claroline\CoreBundle\Form\GroupSettingsType;
use Claroline\CoreBundle\Form\ClarolineSettingsType;

class AdministrationController extends Controller
{
    public function indexAction()
    {
        return $this->render('ClarolineCoreBundle:Administration:administration.html.twig');
    }

    public function showFormAddUserAction()
    {
        $userRoles = $this->get('security.context')->getToken()->getUser()->getOwnedRoles();
        $formUserProfile = $this->createForm(new ProfileType($userRoles));

        return $this->render(
            'ClarolineCoreBundle:Administration:user_add.html.twig', array(
            'form_complete_user' => $formUserProfile->createView())
        );
    }
    
    //TODO change getOwnedRole method

    public function addUserAction()
    {      
        $request = $this->get('request');
        $userRoles = $this->get('security.context')->getToken()->getUser()->getOwnedRoles();
        $form = $this->get('form.factory')->create(new ProfileType($userRoles));
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
            'users' => $users)
        );
    }

    public function showFormCreateGroupAction()
    {
        $group = new Group();
        $formGroup = $this->createForm(new GroupType(), $group);

        return $this->render('ClarolineCoreBundle:Administration:group_create.html.twig', array(
            'form_group' => $formGroup->createView())
        );
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
            'groups' => $groups)
        );
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

        return $this->render(
            'ClarolineCoreBundle:Administration:user_add_to_group.html.twig', array(
                'group' => $group, 'users' => $users)
        );
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

    public function showGroupSettingsAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($id);
        $form = $this->createForm(new GroupSettingsType(), $group);

        return $this->render(
            'ClarolineCoreBundle:Administration:group_edit_settings.html.twig',
            array('group' => $group, 'form_settings' => $form->createView())
        );
    }

    public function editGroupSettingsAction($id)
    {
        $request = $this->get('request');
        $em = $this->getDoctrine()->getEntityManager();
        $group = $em->getRepository('ClarolineCoreBundle:Group')->find($id);
        $form = $this->createForm(new GroupSettingsType(), $group);
        $form->bindRequest($request);

        if ($form->isValid())
        {
            $group = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($group);
            $em->flush();
        }
        
        $url = $this->generateUrl('claro_admin_group_list');

        return $this->redirect($url);
    }
      
    public function showFormClaroSettingsAction()
    {
        $platformConfig = $this->get('claroline.config.platform_config_handler')
             ->getPlatformConfig();
        $form = $this->createForm(new ClarolineSettingsType(), $platformConfig);  
        
        return $this->render(
            'ClarolineCoreBundle:Administration:claro_settings.html.twig', array(
            'form_settings' => $form->createView())
        );
    }
    
    public function editClaroSettingsAction()
    {
        $request = $this->get('request');
        $configHandler = $this->get('claroline.config.platform_config_handler');
        $form = $this->get('form.factory')->create(new ClarolineSettingsType());
        $form->bindRequest($request);
        
        if ($form->isValid())
        {      
            $configHandler->setParameter('allow_self_registration', $form['selfRegistration']->getData());
            $configHandler->setParameter('locale_language', $form['localLanguage']->getData());

            $this->get('session')->setLocale($form['localLanguage']->getData());
        }

        $url = $this->generateUrl('claro_admin_claro_settings_form');

        return $this->redirect($url);
    }
}