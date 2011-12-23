<?php

namespace Claroline\WorkspaceBundle\Controller;

use Symfony\Bundle\TwigBundle\TwigEngine;
use JMS\SecurityExtraBundle\Annotation\Secure;

class WorkspaceController
{
    private $twigEngine;
    
    public function __construct(TwigEngine $twigEngine)
    {
        $this->twigEngine = $twigEngine;
    }
    
    /**
     * @Secure(roles="ROLE_USER")
     */
    public function indexAction()
    {
        return $this->twigEngine->renderResponse(
            'ClarolineWorkspaceBundle:Workspace:index.html.twig'
        );
    }
    
    
//    private $request;
//    private $session;
//    private $securityContext;
//    private $entityManager;
//    private $router;
//    private $formFactory;
//    private $twigEngine;
//    
//    private $rightManager;
//    
//    public function __construct(
//        Request $request,
//        Session $session,
//        SecurityContext $context,
//        EntityManager $em,
//        Router $router,
//        FormFactory $factory,
//        TwigEngine $engine,
//        RightManagerInterface $rightManager
//    )
//    {
//        $this->request = $request;
//        $this->session = $session;
//        $this->securityContext = $context;
//        $this->entityManager = $em;
//        $this->router = $router;
//        $this->formFactory = $factory;
//        $this->twigEngine = $engine;
//        $this->rightManager = $rightManager;
//    }
//    
//    /**
//     * @Secure(roles="ROLE_USER")
//     */
//    public function newAction()
//    {
//        $workspace = new Workspace();
//        $form = $this->formFactory->create(new WorkspaceType(), $workspace);
//
//        return $this->twigEngine->renderResponse(
//            'ClarolineWorkspaceBundle:Workspace:form.html.twig', 
//            array('form' => $form->createView())
//        );
//    }
//    
//    /**
//     * @Secure(roles="ROLE_USER")
//     */
//    public function createAction()
//    {
//        $workspace = new Workspace();
//        $form = $this->formFactory->create(new WorkspaceType(), $workspace);
//        $form->bindRequest($this->request);
//        $user = $this->securityContext->getToken()->getUser();
//
//        if ($form->isValid())
//        {
//            $this->entityManager->persist($workspace);
//            $this->entityManager->flush();
//            $this->rightManager->setOwner($workspace, $user);
//            
//            $route = $this->router->generate('claroline_desktop_index');
//            
//            return new RedirectResponse($route);
//        }
//
//        return $this->twigEngine->renderResponse(
//            'ClarolineWorkspaceBundle:Workspace:form.html.twig',
//            array('form' => $form->createView())
//        );
//    }
//    
//    /**
//     * @Secure(roles="ROLE_USER")
//     */
//    public function deleteAction($id)
//    {
//        $workspaceEntity = 'ClarolineWorkspaceBundle:Workspace';
//        $workspace = $this->entityManager->find($workspaceEntity, $id);
//        
//        if (false === $this->securityContext->isGranted('DELETE', $workspace))
//        {
//            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
//        }
//        
//        $this->entityManager->remove($workspace);
//        $this->entityManager->flush();
//        
//        $this->session->setFlash('notice', 'Workspace successfully deleted');            
//        $route = $this->router->generate('claroline_desktop_index');
//       
//        return new RedirectResponse($route);
//    }
}