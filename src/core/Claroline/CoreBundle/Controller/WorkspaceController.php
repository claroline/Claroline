<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace;
use Claroline\CoreBundle\Form\WorkspaceType;
use Claroline\CoreBundle\Library\Workspace\Configuration;
use Claroline\CoreBundle\Library\Workspace\Creator;
use Claroline\CoreBundle\Library\Browsing\HistoryBrowser;

class WorkspaceController
{
    private $request;
    private $session;
    private $securityContext;
    private $entityManager;
    private $router;
    private $formFactory;
    private $twigEngine;
    private $workspaceCreator;
    private $historyBrowser;
    
    public function __construct(
        Request $request,
        Session $session,
        SecurityContext $context,
        EntityManager $em,
        Router $router,
        FormFactory $factory,
        TwigEngine $engine,
        Creator $workspaceCreator,
        HistoryBrowser $historyBrowser
    )
    {
        $this->request = $request;
        $this->session = $session;
        $this->securityContext = $context;
        $this->entityManager = $em;
        $this->router = $router;
        $this->formFactory = $factory;
        $this->twigEngine = $engine;
        $this->workspaceCreator = $workspaceCreator;
        $this->historyBrowser = $historyBrowser;
    }
    
    public function newAction()
    {
        // check if granted
        
        $workspace = new SimpleWorkspace();
        $form = $this->formFactory->create(new WorkspaceType(), $workspace);

        return $this->twigEngine->renderResponse(
            'ClarolineCoreBundle:Workspace:form.html.twig', 
            array('form' => $form->createView())
        );
    }
    
    public function createAction()
    {
        // check if granted
              
        $workspace = new SimpleWorkspace();
        $form = $this->formFactory->create(new WorkspaceType(), $workspace);
        $form->bindRequest($this->request);
        $user = $this->securityContext->getToken()->getUser();

        if ($form->isValid())
        {
            $config = new Configuration();
            $config->setName($workspace->getName());
            $this->workspaceCreator->createWorkspace($config, $user);
            
            $route = $this->router->generate('claroline_desktop_index');
            
            return new RedirectResponse($route);
        }

        return $this->twigEngine->renderResponse(
            'ClarolineCoreBundle:Workspace:form.html.twig',
            array('form' => $form->createView())
        );
    }
    
    public function deleteAction($id)
    {
        $workspaceEntity = 'ClarolineCoreBundle:Workspace';
        $workspace = $this->entityManager->find($workspaceEntity, $id);
        
        if (false === $this->securityContext->isGranted('DELETE', $workspace))
        {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }
        
        $this->entityManager->remove($workspace);
        $this->entityManager->flush();
        
        $this->session->setFlash('notice', 'Workspace successfully deleted');            
        $route = $this->router->generate('claroline_desktop_index');
       
        return new RedirectResponse($route);
    }
}