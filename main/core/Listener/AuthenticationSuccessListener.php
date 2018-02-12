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

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\TermsOfServiceType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Configuration\PlatformDefaults;
use Claroline\CoreBundle\Library\Logger\FileLogger;
use Claroline\CoreBundle\Manager\TermsOfServiceManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LogLevel;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Service("claroline.authentication_handler")
 */
class AuthenticationSuccessListener implements AuthenticationSuccessHandlerInterface
{
    use LoggableTrait;
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
     * @DI\InjectParams({
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "configurationHandler"   = @DI\Inject("claroline.config.platform_config_handler"),
     *     "templating"             = @DI\Inject("templating"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "termsOfService"         = @DI\Inject("claroline.common.terms_of_service_manager"),
     *     "manager"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "router"                 = @DI\Inject("router"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "requestStack"           = @DI\Inject("request_stack"),
     *     "kernelRootDir"          = @DI\Inject("%kernel.root_dir%")
     * })
     */
    public function __construct(
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
    public function onLoginSuccess(InteractiveLoginEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if ($user->getInitDate() === null) {
            $this->userManager->setUserInitDate($user);
        }

        $this->userManager->logUser($user);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $securityRoute = null;
        $securityUri = $request->getSession()->get('_security.main.target_path');
        // Get route name if security Uri present
        if ($securityUri) {
            $securityUriClean = preg_replace("/(app_dev.php\/|app_dev.php\/)/i", '', parse_url($securityUri, PHP_URL_PATH));
            try {
                $securityRoute = $this->router->match($securityUriClean)['_route'];
            } catch (MethodNotAllowedException $e) {
                $this->log($e->getMessage(), LogLevel::ERROR);
                $this->router->getContext()->setMethod('GET');
                $securityRoute = $this->router->match($securityUriClean)['_route'];
            } catch (\Exception $e) {
                // In case of any exception matching the securityUri, redirect to desktop
                $this->log($e->getMessage(), LogLevel::ERROR);

                return new RedirectResponse($this->router->generate('claro_desktop_open'));
            }
        }
        // If login route then check other conditions.
        if ($securityRoute && !$this->isRouteExcluded($securityRoute)) {
            return new RedirectResponse($securityUri);
        }

        if ($this->configurationHandler->isRedirectOption(PlatformDefaults::$REDIRECT_OPTIONS['DESKTOP'])) {
            return new RedirectResponse($this->router->generate('claro_desktop_open'));
        } elseif (
            $this->configurationHandler->isRedirectOption(PlatformDefaults::$REDIRECT_OPTIONS['LAST'])
            && $uri = $request->getSession()->get('redirect_route')
        ) {
            return new RedirectResponse($uri);
        } elseif (
            $this->configurationHandler->isRedirectOption(PlatformDefaults::$REDIRECT_OPTIONS['URL'])
            && null !== $url = $this->configurationHandler->getParameter('redirect_after_login_url')
        ) {
            return new RedirectResponse($url);
        } elseif (
            $this->configurationHandler->isRedirectOption(PlatformDefaults::$REDIRECT_OPTIONS['WORKSPACE_TAG'])
            && null !== $defaultWorkspaceTag = $this->configurationHandler->getParameter('default_workspace_tag')
        ) {
            $event = $this->eventDispatcher->dispatch(
                'claroline_retrieve_user_workspaces_by_tag',
                'GenericData',
                [
                    [
                        'tag' => $defaultWorkspaceTag,
                        'user' => $user,
                        'ordered_by' => 'id',
                        'order' => 'ASC',
                    ],
                ]
            );
            $workspaces = $event->getResponse();

            if (is_array($workspaces) && count($workspaces) > 0) {
                $workspace = $workspaces[0];
                $route = $this->router->generate(
                    'claro_workspace_open',
                    ['workspaceId' => $workspace->getId()]
                );

                return new RedirectResponse($route);
            }
        }

        return new RedirectResponse($this->router->generate('claro_desktop_open'));
    }

    /**
     * @DI\Observe("kernel.request")
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if ($this->configurationHandler->getParameter('terms_of_service')) {
            $this->showTermOfServices($event);
        }
    }

    /**
     * @DI\Observe("kernel.response", priority = 1)
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ($this->configurationHandler->isRedirectOption(PlatformDefaults::$REDIRECT_OPTIONS['LAST'])) {
            $this->saveLastUri($event);
        }
    }

    private function saveLastUri(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');
        if ($event->isMasterRequest()
            && !empty($route)
            && !$request->isXmlHttpRequest()
            && !is_a($event->getResponse(), JsonResponse::class)
            && !$event->getResponse()->headers->contains('Content-Type', 'application/json')
            && 'GET' === $request->getMethod()
            && 200 === $event->getResponse()->getStatusCode()
            && !$event->getResponse() instanceof StreamedResponse
            && !$this->isRouteExcluded($route)
        ) {
            if ($token = $this->tokenStorage->getToken()) {
                if ('anon.' === $token->getUser()) {
                    $uri = $request->getRequestUri();
                    $request->getSession()->set('redirect_route', $uri);
                }
            }
        }
    }

    private function showTermOfServices(GetResponseEvent $event)
    {
        if ($event->isMasterRequest()
            && ($user = $this->getUser($event->getRequest()))
            && !$user->hasAcceptedTerms()
            && !$this->isImpersonated()
            && ($content = $this->termsOfService->getTermsOfService(false))
        ) {
            if (($termsOfService = $event->getRequest()->get('accept_terms_of_service_form'))
                && isset($termsOfService['terms_of_service'])
            ) {
                $user->setAcceptedTerms(true);
                $this->manager->persist($user);
                $this->manager->flush();
            } else {
                $form = $this->formFactory->create(new TermsOfServiceType(), $content);
                $response = $this->templating->render(
                    'ClarolineCoreBundle:Authentication:termsOfService.html.twig',
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
     * @return Claroline\CoreBundle\Entity\User
     */
    private function getUser(Request $request)
    {
        if ($this->configurationHandler->getParameter('terms_of_service')
            && $request->get('_route') !== 'claroline_locale_change'
            && $request->get('_route') !== 'claroline_locale_select'
            && $request->get('_route') !== 'bazinga_exposetranslation_js'
            && ($token = $this->tokenStorage->getToken())
            && ($user = $token->getUser())
            && $user instanceof User
        ) {
            return $user;
        }
    }

    /**
     * Tests if given route is one of excluded routes.
     * Excluded routes are:
     * 1. all login routes (claro_security_login, claro_security_*)
     * 2. register routes
     * 3. oauth routes and oauth redirect routes.
     *
     * @param $route
     *
     * @return bool
     */
    private function isRouteExcluded($route)
    {
        return in_array($route, $this->getExcludedRoutes())
            || preg_match('/(claro_security_|oauth_|_login|claro_file|media|claro_cas_|claro_ldap_)/', $route);
    }

    private function getExcludedRoutes()
    {
        return [
            'bazinga_jstranslation_js',
            'bazinga_exposetranslation_js',
            'login_check',
            'login',
            'claro_user_registration',
            'claro_o365_login',
            'claro_cas_login',
            'claro_ldap_login',
        ];
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
    }
}
