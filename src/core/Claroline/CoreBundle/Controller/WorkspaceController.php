<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Symfony\Component\HttpFoundation\Response;

class WorkspaceController extends Controller
{
    const ABSTRACT_WS_CLASS = 'Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace';
    
    public function listAction()
    {
        if (false === $this->get('security.context')->isGranted('ROLE_USER'))
        {
            throw new AccessDeniedHttpException();
        }
        
        $em = $this->get('doctrine.orm.entity_manager');
        $workspaces = $em->getRepository(self::ABSTRACT_WS_CLASS)->findAll();
        
        return $this->render(
            'ClarolineCoreBundle:Workspace:list.html.twig', 
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
            'ClarolineCoreBundle:Workspace:list.html.twig', 
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
            'ClarolineCoreBundle:Workspace:form.html.twig', 
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
            'ClarolineCoreBundle:Workspace:form.html.twig',
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
               
        return $this->render('ClarolineCoreBundle:Workspace:show.html.twig', array('workspace' => $workspace));
    }
    
    public function registerAction($id)
    {
        //$user = $this->get('security.context')->getToken()->getUser();
        return new Response ('todo');
        
    }
    
    public function unregisterAction($id)
    {
        return new Response ('todo');
    }
}