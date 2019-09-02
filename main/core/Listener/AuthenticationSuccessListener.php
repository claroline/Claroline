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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\TermsOfServiceType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Logger\FileLogger;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service("claroline.security.authentication.success_handler")
 */
class AuthenticationSuccessListener implements AuthenticationSuccessHandlerInterface
{
    use LoggableTrait;
    /** @var Kernel */
    private $kernel;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var StrictDispatcher */
    private $eventDispatcher;
    /** @var PlatformConfigurationHandler */
    private $configurationHandler;
    /** @var EngineInterface */
    private $templating;
    /** @var FormFactory */
    private $formFactory;
    /** @var TermsOfServiceManager */
    private $termsOfService;
    /** @var ObjectManager */
    private $manager;
    /** @var Router */
    private $router;
    /** @var UserManager */
    private $userManager;
    /** @var RequestStack */
    private $requestStack;

    /**
     * AuthenticationSuccessListener constructor.
     *
     * @DI\InjectParams({
     *     "kernel"               = @DI\Inject("kernel"),
     *     "authorization"        = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"         = @DI\Inject("security.token_storage"),
     *     "eventDispatcher"      = @DI\Inject("claroline.event.event_dispatcher"),
     *     "configurationHandler" = @DI\Inject("claroline.config.platform_config_handler"),
     *     "templating"           = @DI\Inject("templating"),
     *     "formFactory"          = @DI\Inject("form.factory"),
     *     "termsOfService"       = @DI\Inject("claroline.common.terms_of_service_manager"),
     *     "manager"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "router"               = @DI\Inject("router"),
     *     "userManager"          = @DI\Inject("claroline.manager.user_manager"),
     *     "requestStack"         = @DI\Inject("request_stack"),
     *     "kernelRootDir"        = @DI\Inject("%kernel.root_dir%")
     * })
     *
     * @param Kernel                        $kernel
     * @param TokenStorageInterface         $tokenStorage
     * @param AuthorizationCheckerInterface $authorization
     * @param StrictDispatcher              $eventDispatcher
     * @param PlatformConfigurationHandler  $configurationHandler
     * @param EngineInterface               $templating
     * @param FormFactory                   $formFactory
     * @param TermsOfServiceManager         $termsOfService
     * @param ObjectManager                 $manager
     * @param Router                        $router
     * @param UserManager                   $userManager
     * @param RequestStack                  $requestStack
     * @param string                        $kernelRootDir
     */
    public function __construct(
        Kernel $kernel,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        StrictDispatcher $eventDispatcher,
        PlatformConfigurationHandler $configurationHandler,
        EngineInterface $templating,
        FormFactory $formFactory,
        TermsOfServiceManager $termsOfService,
        ObjectManager $manager,
        Router $router,
        UserManager $userManager,
        RequestStack $requestStack,
        $kernelRootDir
    ) {
        $this->kernel = $kernel;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->eventDispatcher = $eventDispatcher;
        $this->configurationHandler = $configurationHandler;
        $this->templating = $templating;
        $this->formFactory = $formFactory;
        $this->termsOfService = $termsOfService;
        $this->manager = $manager;
        $this->router = $router;
        $this->userManager = $userManager;
        $this->requestStack = $requestStack;
        $this->logger = FileLogger::get($kernelRootDir.'/logs/login.log', 'claroline.login.logger');
    }

    /**
     * @DI\Observe("security.interactive_login")
     */
    public function onLoginSuccess()
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ('anon.' === $user) {
            return;
        }

        $this->userManager->logUser($user);
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @deprecated
     *
     * @todo remove me
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        return null;
    }

    /**
     * Checks the current user has accepted term of services if any before displaying platform.
     *
     * @DI\Observe("kernel.request")
     *
     * @param GetResponseEvent $event
     */
    public function checkTermOfServices(GetResponseEvent $event)
    {
        if ('prod' === $this->kernel->getEnvironment() && $event->isMasterRequest()) {
            $user = !empty($this->tokenStorage->getToken()) ? $this->tokenStorage->getToken()->getUser() : null;
            if ($user instanceof User && $this->configurationHandler->getParameter('terms_of_service')) {
                $this->showTermOfServices($event);
            }
        }
    }

    private function showTermOfServices(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()
            && ($user = $this->getUser($event->getRequest()))
            && !$user->hasAcceptedTerms()
            && !$this->isImpersonated()
            && ($content = $this->termsOfService->getTermsOfService(true))
        ) {
            $content = isset($content['fr']) ? $content['fr'] : null;

            if (($termsOfService = $event->getRequest()->get('terms_of_service'))
                && isset($termsOfService['terms_of_service'])
            ) {
                $user->setAcceptedTerms(true);
                $this->manager->persist($user);
                $this->manager->flush();
            } else {
                $form = $this->formFactory->create(TermsOfServiceType::class, $content);
                $response = $this->templating->render(
                    'ClarolineCoreBundle:authentication:terms_of_service.html.twig',
                    ['form' => $form->createView()]
                );

                $event->setResponse(new Response($response));
            }
        }
    }

    /**
     * Return a user if need to accept the terms of service.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return User
     */
    private function getUser(Request $request)
    {
        if ($this->configurationHandler->getParameter('terms_of_service')
            && 'claroline_locale_change' !== $request->get('_route')
            && 'bazinga_exposetranslation_js' !== $request->get('_route')
            && ($token = $this->tokenStorage->getToken())
            && ($user = $token->getUser())
            && $user instanceof User
        ) {
            return $user;
        }

        return null;
    }

    public function isImpersonated()
    {
        if ($this->authorization->isGranted('ROLE_PREVIOUS_ADMIN')) {
            foreach ($this->tokenStorage->getToken()->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    return true;
                }
            }
        }

        return false;
    }
}
