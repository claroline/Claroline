<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Doctrine\ORM\EntityManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\UserType;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Acl\Domain\ClassIdentity;

class RegistrationController
{
    private $request;
    private $securityContext;
    private $formFactory;
    private $twigEngine;
    private $translator;
    private $userManager;
    private $isSelfRegistrationEnabled;
    
    public function __construct(
        Request $request,
        SecurityContextInterface $context,
        FormFactory $factory,
        TwigEngine $twigEngine,
        Translator $translator,
        UserManager $userManager,
        $isSelfRegistrationEnabled
    )
    {
        $this->request = $request;
        $this->securityContext = $context;
        $this->formFactory = $factory;
        $this->twigEngine = $twigEngine;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->isSelfRegistrationEnabled = $isSelfRegistrationEnabled;
    }
    
    public function newAction()
    {
        $this->checkAccess();
        
        $user = new User();
        $form = $this->formFactory->create(new UserType(), $user);

        return $this->twigEngine->renderResponse(
            'ClarolineCoreBundle:Registration:form.html.twig', 
            array('form' => $form->createView())
        );
    }
    
    public function createAction()
    {
        $this->checkAccess();
        
        $msg = null;
        $user = new User();
        $form = $this->formFactory->create(new UserType(), $user);
        $form->bindRequest($this->request);
        
        if ($form->isValid())
        {
            $this->userManager->create($user);
            $msg = $this->translator->trans(
                'profile.account_created', 
                array(), 
                'ClarolineUserBundle'
            );
        }

        return $this->twigEngine->renderResponse(
            'ClarolineCoreBundle:Registration:form.html.twig', 
            array(
                'form' => $form->createView(),
                'msg' => $msg
            )
        );
    }
    
    private function checkAccess()
    {
        if (! $this->securityContext->getToken()->getUser() instanceof User && $this->isSelfRegistrationEnabled)
        {
            return;
        }
        
        if ($this->securityContext->isGranted('ROLE_ADMIN'))
        {
            return;
        }
        
        if ($this->securityContext->isGranted('CREATE', ClassIdentity::fromDomainClass('Claroline\CoreBundle\Entity\User')))
        {
            return;
        }
        
        
        throw new AccessDeniedException();
    }
}