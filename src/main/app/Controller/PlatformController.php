<?php

namespace Claroline\AppBundle\Controller;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\API\Serializer\Platform\ClientSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\PrivacyBundle\Manager\PrivacyManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

/**
 * PlatformController is in charge of the rendering of the Claroline web UI.
 */
class PlatformController
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly Environment $templating,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly PlatformConfigurationHandler $configHandler,
        private readonly PlatformManager $platformManager,
        private readonly LocaleManager $localeManager,
        private readonly SecurityManager $securityManager,
        private readonly PrivacyManager $privacyManager,
        private readonly ContextProvider $contextProvider,
        private readonly SerializerProvider $serializer,
        private readonly ClientSerializer $clientSerializer
    ) {
    }

    /**
     * Renders the Claroline web application.
     *
     * @Route("/", name="claro_index")
     */
    public function indexAction(Request $request): Response
    {
        $currentUser = null;
        if ($this->tokenStorage->getToken()->getUser() instanceof User) {
            $currentUser = $this->serializer->serialize(
                $this->tokenStorage->getToken()->getUser(), [Options::SERIALIZE_FACET] // TODO : we should only get the minimal representation of user here
            );
        }

        return new Response(
            $this->templating->render('@ClarolineApp/index.html.twig', [
                'baseUrl' => $this->platformManager->getUrl(),
                'parameters' => $this->clientSerializer->serialize(),
                'maintenance' => [
                    'enabled' => MaintenanceHandler::isMaintenanceEnabled() || $this->configHandler->getParameter('maintenance.enable'),
                    'message' => $this->configHandler->getParameter('maintenance.message'),
                ],
                'currentUser' => $currentUser,
                'impersonated' => $this->securityManager->isImpersonated(),
                'contexts' => $this->contextProvider->getAvailableContexts(),
                'client' => [
                    'ip' => $request->getClientIp(),
                    'forwarded' => $request->headers->get('X-Forwarded-For'), // I can only get trusted proxies if I use symfony getClientIps()
                ],
                'header' => [
                    'menus' => $this->configHandler->getParameter('header'),
                    'display' => [
                        'name' => $this->configHandler->getParameter('name_active'),
                        'about' => $this->configHandler->getParameter('show_about_button'),
                        'help' => $this->configHandler->getParameter('show_help_button'),
                    ],
                ],
                'footer' => [
                    'content' => $this->configHandler->getParameter('footer.content'),
                    'display' => [
                        'show' => $this->configHandler->getParameter('footer.show'),
                        'locale' => $this->configHandler->getParameter('footer.show_locale'),
                        'help' => $this->configHandler->getParameter('footer.show_help'),
                        'termsOfService' => $this->privacyManager->getTosEnabled(),
                    ],
                ],

                // additional assets for the platform
                // assets defined by users in the platform configuration
                'javascripts' => $this->configHandler->getParameter('javascripts'),
                'stylesheets' => $this->configHandler->getParameter('stylesheets'),
                // assets injected from plugins
                'injectedJavascripts' => $this->injectJavascript(),
                'injectedStylesheets' => $this->injectStylesheet(),
            ])
        );
    }

    /**
     * Change locale.
     *
     * @Route("/locale/{locale}", name="claroline_locale_change")
     */
    public function changeAction(Request $request, string $locale): RedirectResponse
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof User) {
            $this->localeManager->setUserLocale($locale);
        }

        $request->setLocale($locale);
        $request->getSession()->set('_locale', $locale);

        return new RedirectResponse(
            $request->headers->get('referer')
        );
    }

    /**
     * Gets the javascript injected by the plugins if any.
     */
    private function injectJavascript(): string
    {
        $event = new InjectJavascriptEvent();
        $this->dispatcher->dispatch($event, 'layout.inject.javascript');

        return $event->getContent();
    }

    /**
     * Gets the styles injected by the plugins if any.
     */
    private function injectStylesheet(): string
    {
        $event = new InjectStylesheetEvent();
        $this->dispatcher->dispatch($event, 'layout.inject.stylesheet');

        return $event->getContent();
    }
}
