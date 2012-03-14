<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Symfony\Component\HttpFoundation\Response;

//TODO : ajax error handling

class WorkspaceController extends Controller
{
    const ABSTRACT_WS_CLASS = 'Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace';
    const NUMBER_USER_PER_ITERATION = 25;
    const NUMBER_GROUP_PER_ITERATION = 10;
    
    public function listAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_USER'))
        {
            throw new AccessDeniedHttpException();
        }
        
        $em = $this->get('doctrine.orm.entity_manager');
        $workspaces = $em->getRepository(self::ABSTRACT_WS_CLASS)->findAll();
        
        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_list.html.twig', 
            array('workspaces' => $workspaces)
        );
    }
    
    public function listForUserAction($userId)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_USER'))
        {
            throw new AccessDeniedHttpException();
        }
     
        $em = $this->get('doctrine.orm.entity_manager');
        
        $user = $em->find('Claroline\CoreBundle\Entity\User', $userId);

        $workspaces = $em->getRepository(self::ABSTRACT_WS_CLASS)->getWorkspacesOfUser($user);
        
        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_list.html.twig', 
            array('workspaces' => $workspaces)
        );
    }
    
    public function newAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_WS_CREATOR'))
        {
            throw new AccessDeniedHttpException();
        }
        
        $form = $this->get('form.factory')->create(new WorkspaceType());

        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_form.html.twig', 
            array('form' => $form->createView())
        );
    }
    
    public function createAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_WS_CREATOR'))
        {
            throw new AccessDeniedHttpException();
        }
        
        $form = $this->get('form.factory')->create(new WorkspaceType());
        $form->bindRequest($this->getRequest());

        if ($form->isValid())
        {
              $type = $form->get('type')->getData() == 'simple' ? 
                  Configuration::TYPE_SIMPLE : 
                  Configuration::TYPE_AGGREGATOR;
            
              $config = new Configuration();
              $config->setWorkspaceType($type);
              $config->setWorkspaceName($form->get('name')->getData());
              
              $user = $this->get('security.context')->getToken()->getUser();
              $wsCreator = $this->get('claroline.workspace.creator');
              $wsCreator->createWorkspace($config, $user);
              
              $this->get('session')->setFlash('notice', 'Workspace created');
              $route = $this->get('router')->generate('claro_desktop_index');
            
              return new RedirectResponse($route);
        }

        return $this->render(
            'ClarolineCoreBundle:Workspace:workspace_form.html.twig',
            array('form' => $form->createView())
        );
    }
    
    public function deleteAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($id);
        
        if (false === $this->get('security.context')->isGranted("ROLE_WS_MANAGER_{$id}", $workspace))
        {
            throw new AccessDeniedHttpException();
        }
        
        $em->remove($workspace);
        $em->flush();
        
        $this->get('session')->setFlash('notice', 'Workspace deleted');            
        $route = $this->get('router')->generate('claro_desktop_index');
       
        return new RedirectResponse($route);
    }
    
    public function showAction($id)
    { 
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($id);
        $authorization = false;  
        
        foreach ($workspace->getWorkspaceRoles() as $role)
        {
            $this->get('security.context')->isGranted($role->getName());
            {
                $authorization = true;
            }
        }
        
         if ($authorization == false)
         {
            throw new AccessDeniedHttpException();
         }
               
         return $this->render('ClarolineCoreBundle:Workspace:workspace_show.html.twig', array('workspace' => $workspace));
    }
    
    public function registerAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($id);
        $user->addRole($workspace->getCollaboratorRole());
        $em->flush();
        $route = $this->get('router')->generate('claro_workspace_list_all');
        
        return new RedirectResponse($route);
    }
    
    public function unregisterAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $this->get('security.context')->getToken()->getUser();
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($id);
        $roles = $workspace->getWorkspaceRoles();
        foreach ($roles as $role)
        {
            $user->removeRole($role);
        }
        
        $route = $this->get('router')->generate('claro_workspace_list_all');
        $em->flush();
        
        return new RedirectResponse($route);
    }
    
    public function listUserPerWorkspaceAction($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($id);
        
        if (false === $this->get('security.context')->isGranted("ROLE_WS_MANAGER_{$id}", $workspace))
        {
            throw new AccessDeniedHttpException();
        }
        
        $groups= $em->getRepository('ClarolineCoreBundle:Group')->getGroupsOfWorkspace($workspace);
        $users = $em->getRepository('ClarolineCoreBundle:User')->getUsersOfWorkspace($workspace);           

        return $this->render('ClarolineCoreBundle:Workspace:workspace_user_list.html.twig', array('workspace' => $workspace, 'users' => $users, 'groups' => $groups));
    }
    
    public function deleteUserFromWorkspaceAction($userId, $workspaceId)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
        $user = $em->getRepository('ClarolineCoreBundle:User')->find($userId);
        $roles = $workspace->getWorkspaceRoles();
        
        foreach ($roles as $role)
        {
            $user->removeRole($role);
        };
        
        $em->flush();
        $route = $this->get('router')->generate('claro_workspace_show_user_list_workspace', array('id' => $workspaceId));
        
        return new RedirectResponse($route);
    }
    
    public function ajaxGetAddUserAction($id, $nbIteration)
    {
       $request = $this->get('request');
       
       if($request->isXmlHttpRequest()) 
       {  
            $em = $this->get('doctrine.orm.entity_manager');
            $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($id);
            $users = $em->getRepository('ClarolineCoreBundle:User')->getLazyUnregisteredUsersOfWorkspace($workspace, $nbIteration, self::NUMBER_USER_PER_ITERATION);
            return $this->container->get('templating')->renderResponse('ClarolineCoreBundle:Workspace:AJAX_workspace_user_list_popup.html.twig', array('users' => $users));
       }
       
       return new \Exception("ajax error");
    }
    
    public function ajaxGetAddGroupAction($id, $nbIteration)
    {
       $request = $this->get('request');
       
       if($request->isXmlHttpRequest()) 
       {  
            $em = $this->get('doctrine.orm.entity_manager');
            $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($id);
            $groups = $em->getRepository('ClarolineCoreBundle:Group')->getLazyUnregisteredGroupsOfWorkspace($workspace, $nbIteration, self::NUMBER_GROUP_PER_ITERATION);
            return $this->container->get('templating')->renderResponse('ClarolineCoreBundle:Workspace:AJAX_workspace_group_list_popup.html.twig', array('groups' => $groups));
       }
       
       return new \Exception("ajax error");
    }
    
    public function ajaxGetGenericSearchUnregisteredUserAction($search, $id)
    {
        $request = $this->get('request');
        
        if($request->isXmlHttpRequest())
        {
            $em = $this->getDoctrine()->getEntityManager();
            $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($id);
            $users = $em->getRepository('ClarolineCoreBundle:User')->getUnregisteredUsersOfWorkspaceFromGenericSearch($search, $workspace);
            
            return $this->container->get('templating')->renderResponse('ClarolineCoreBundle:Workspace:AJAX_workspace_user_list_popup.html.twig', array('users' => $users));
        }
    }
    
    public function ajaxAddUserToWorkspaceAction($userId, $workspaceId)
    {
        $request = $this->get('request');
        
        if($request->isXmlHttpRequest())
        {
            $em = $this->get('doctrine.orm.entity_manager');
            $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
            $user = $em->getRepository('ClarolineCoreBundle:User')->find($userId);
            $user->addRole($workspace->getCollaboratorRole());
            $em->flush();
            
            return $this->container->get('templating')->renderResponse('ClarolineCoreBundle:Workspace:AJAX_workspace_add_user_response.html.twig', array('user' => $user, 'workspace' => $workspace));
        }
        
        return new \Exception("ajax error");
    }
    
    public function ajaxDeleteUserFromWorkspaceAction($userId, $workspaceId)
    {
        $request = $this->get('request');
        
        if($request->isXmlHttpRequest())
        {
           
            $em = $this->get('doctrine.orm.entity_manager');
            $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
            $user = $em->getRepository('ClarolineCoreBundle:User')->find($userId);
            $roles = $workspace->getWorkspaceRoles();
        
            foreach ($roles as $role)
            {
                $user->removeRole($role);
            }
        
            $em->flush(); 
            
            return new Response("success");
        }
       
        return new \Exception("ajax error");     
    }
    
    public function ajaxAddGroupToWorkspaceAction($groupId, $workspaceId)
    {
        $request = $this->get('request');
        
        if($request->isXmlHttpRequest())
        {
            $em = $this->get('doctrine.orm.entity_manager');              
            $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($workspaceId);
            $group = $em->getRepository('ClarolineCoreBundle:Group')->find($groupId);   
            $group->addRole($workspace->getCollaboratorRole());
            $em->flush();
            
            return $this->container->get('templating')->renderResponse('ClarolineCoreBundle:Workspace:AJAX_workspace_add_group_response.html.twig', array('group' => $group, 'workspace' => $workspace));
        }
        
        return new \Exception("ajax error");
    }
    
    public function ajaxGetGenericSearchUnregisteredGroupAction($search, $id)
    {
        $request = $this->get('request');
        
        if($request->isXmlHttpRequest())
        {
            $em = $this->get('doctrine.orm.entity_manager');
            $workspace = $em->getRepository(self::ABSTRACT_WS_CLASS)->find($id);
            $groups = $em->getRepository('ClarolineCoreBundle:Group')->getUnregisteredGroupsOfWorkspaceFromGenericSearch($search, $workspace);
            
            return $this->container->get('templating')->renderResponse('ClarolineCoreBundle:Workspace:AJAX_workspace_group_list_popup.html.twig', array('groups' => $groups));
        }
        
        return new \Exception("ajax error");
    }  
    
}