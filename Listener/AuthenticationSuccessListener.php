<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Listener;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\TermsOfServiceType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * @DI\Service
 */
class AuthenticationSuccessListener
{
    private $securityContext;
    private $eventDispatcher;
    private $configurationHandler;
    private $templating;
    private $formFactory;
    private $termsOfService;
    private $manager;

    /**
     * @DI\InjectParams({
     *     "context"                = @DI\Inject("security.context"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "configurationHandler"   = @DI\Inject("claroline.config.platform_config_handler"),
     *     "templating"             = @DI\Inject("templating"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "termsOfService"         = @DI\Inject("claroline.common.terms_of_service_manager"),
     *     "manager"                = @DI\Inject("claroline.persistence.object_manager"),
     * })
     *
     */
    public function __construct(
        SecurityContextInterface $context,
        StrictDispatcher $eventDispatcher,
        PlatformConfigurationHandler $configurationHandler,
        EngineInterface $templating,
        FormFactory $formFactory,
        TermsOfServiceManager $termsOfService,
        ObjectManager $manager
    )
    {
        $this->securityContext = $context;
        $this->eventDispatcher = $eventDispatcher;
        $this->configurationHandler = $configurationHandler;
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->termsOfService = $termsOfService;
        $this->manager = $manager;
    }

    /**
     * @DI\Observe("security.interactive_login")
     *
     * @param WorkspaceLogEvent $event
     */
    public function onAuthenticationSuccess()
    {
        $user = $this->securityContext->getToken()->getUser();
        $this->eventDispatcher->dispatch('log', 'Log\LogUserLogin', array($user));
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->securityContext->setToken($token);
    }

    /**
     * @DI\Observe("kernel.request")
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($event->isMasterRequest() and
            $user = $this->getUser($event->getRequest()) and
            $content = $this->termsOfService->getTermsOfService(false)) {
            if ($termsOfService = $event->getRequest()->get('accept_terms_of_service_form') and
                isset($termsOfService['terms_of_service'])
            ) {
                $user->setAcceptedTerms(true);
                $this->manager->persist($user);
                $this->manager->flush();
            } else {
                $form = $this->formFactory->create(new TermsOfServiceType(), $content);
                $response = $this->templating->render(
                    "ClarolineCoreBundle:Authentication:termsOfService.html.twig",
                    array('form' => $form->createView())
                );

                $event->setResponse(new Response($response));
            }
        }
    }

    /**
     * Return a user if need to accept the terms of service
     *
     * @return Claroline\CoreBundle\Entity\User
     */
    private function getUser($request)
    {
        if ($this->configurationHandler->getParameter('terms_of_service') and
            $request->get('_route') !== 'claroline_locale_change' and
            $request->get('_route') !== 'claroline_locale_select' and
            $request->get('_route') !== 'bazinga_exposetranslation_js' and
            $token = $this->securityContext->getToken() and
            $user = $token->getUser() and
            $user instanceof User and
            !$user->hasAcceptedTerms()
        ) {
            return $user;
        }
    }
}
