<?php

namespace Claroline\WorkspaceBundle\Controller;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManager;
use Claroline\WorkspaceBundle\Service\Manager\ACLWorkspaceManager;
use Claroline\WorkspaceBundle\Entity\Workspace;
use Claroline\WorkspaceBundle\Form\WorkspaceType;

class WorkspaceController
{
    private $request;
    private $session;
    private $securityContext;
    private $entityManager;
    private $router;
    private $formFactory;
    private $twigEngine;
    private $workspaceAclManager;
    
    public function __construct(Request $request,
                                Session $session,
                                SecurityContext $context,
                                EntityManager $em,
                                Router $router,
                                FormFactory $factory,
                                TwigEngine $engine,
                                ACLWorkspaceManager $aclManager)
    {
        $this->request = $request;
        $this->session = $session;
        $this->securityContext = $context;
        $this->entityManager = $em;
        $this->router = $router;
        $this->formFactory = $factory;
        $this->twigEngine = $engine;
        $this->workspaceAclManager = $aclManager;
    }
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function newAction()
    {
        $workspace = new Workspace();
        $form = $this->formFactory->create(new WorkspaceType(), $workspace);

        return $this->twigEngine->renderResponse(
            'ClarolineWorkspaceBundle:Workspace:form.html.twig', 
            array('form' => $form->createView())
        );
    }
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function createAction()
    {
        $workspace = new Workspace();
        $form = $this->formFactory->create(new WorkspaceType(), $workspace);
        $form->bindRequest($this->request);
        $user = $this->securityContext->getToken()->getUser();
        $workspace->setOwner($user);

        if ($form->isValid())
        {
            $this->workspaceAclManager->create($workspace);
            $route = $this->router->generate('claro_core_desktop');
            
            return new RedirectResponse($route);
        }

        return $this->twigEngine->renderResponse(
            'ClarolineWorkspaceBundle:Workspace:form.html.twig',
            array('form' => $form->createView())
        );
    }
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function deleteAction($id)
    {
        $workspaceEntity = 'ClarolineWorkspaceBundle:Workspace';
        $workspaceRepo = $this->entityManager->getRepository($workspaceEntity);
        $workspace = $workspaceRepo->find($id);
        $this->workspaceAclManager->delete($workspace);
        
        $this->session->setFlash('notice', 'Workspace successfully deleted');            
        $route = $this->router->generate('claro_core_desktop');
        
        return new RedirectResponse($route);
    }
}