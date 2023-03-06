<?php

namespace Claroline\AppBundle\Controller\Platform;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Manager\SecurityManager;
use Claroline\CoreBundle\API\Serializer\Platform\ClientSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

/**
 * ClientController.
 * It's responsible of the rendering of the Claroline web UI.
 */
class ClientController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Environment */
    private $templating;
    /** @var EventDispatcherInterface */
    private $dispatcher;
    /** @var PlatformConfigurationHandler */
    private $configHandler;
    /** @var SecurityManager */
    private $securityManager;
    /** @var ToolManager */
    private $toolManager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ClientSerializer */
    private $clientSerializer;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        Environment $templating,
        EventDispatcherInterface $dispatcher,
        PlatformConfigurationHandler $configHandler,
        SecurityManager $securityManager,
        ToolManager $toolManager,
        SerializerProvider $serializer,
        ClientSerializer $clientSerializer
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templating = $templating;
        $this->dispatcher = $dispatcher;
        $this->configHandler = $configHandler;
        $this->securityManager = $securityManager;
        $this->toolManager = $toolManager;
        $this->serializer = $serializer;
        $this->clientSerializer = $clientSerializer;
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
                $this->tokenStorage->getToken()->getUser()
            );
        }

        return new Response(
            $this->templating->render('@ClarolineApp/index.html.twig', [
                'parameters' => $this->clientSerializer->serialize(),
                'maintenance' => [
                    'enabled' => MaintenanceHandler::isMaintenanceEnabled() || $this->configHandler->getParameter('maintenance.enable'),
                    'message' => $this->configHandler->getParameter('maintenance.message'),
                ],
                'currentUser' => $currentUser,
                'impersonated' => $this->securityManager->isImpersonated(),
                'administration' => !empty($this->toolManager->getAdminToolsByRoles($this->tokenStorage->getToken()->getRoleNames())),
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
                        'termsOfService' => $this->configHandler->getParameter('footer.show_terms_of_service'),
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
