<?php

namespace Claroline\RegisterBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Claroline\UserBundle\Service\UserManager\Manager;
use Claroline\UserBundle\Entity\User;
use Claroline\UserBundle\Form\UserType;

class MainController
{
    private $request;
    private $router;
    private $formFactory;
    private $twigEngine;
    private $userManager;
    
    public function __construct(Request $request, 
                                Router $router,
                                FormFactory $factory,
                                TwigEngine $engine,
                                Manager $userManager)
    {
        $this->request = $request;
        $this->router = $router;
        $this->formFactory = $factory;
        $this->twigEngine = $engine;
        $this->userManager = $userManager;
    }
    
    public function indexAction()
    {
        $user = new User();
        $form = $this->formFactory->create(new UserType(), $user);

        if ($this->request->getMethod() == 'POST')
        {
            $form->bindRequest($this->request);

            if ($form->isValid())
            {
                $this->userManager->create($user);
                $route = $this->router->generate('claro_core_desktop');
                
                return new RedirectResponse($route);
            }
        }

        return $this->twigEngine->renderResponse(
            'ClarolineRegisterBundle:Main:form.html.twig', 
            array('form' => $form->createView()));
    }
}