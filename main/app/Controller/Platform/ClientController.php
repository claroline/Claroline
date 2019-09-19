<?php

namespace Claroline\AppBundle\Controller\Platform;

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

/**
 * ClientController.
 * It's responsible of the rendering of the Claroline web UI.
 */
class ClientController
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var StrictDispatcher */
    private $dispatcher;

    /** @var PlatformConfigurationHandler */
    private $configHandler;

    /**
     * ClientController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"  = @DI\Inject("security.token_storage"),
     *     "dispatcher"    = @DI\Inject("claroline.event.event_dispatcher"),
     *     "configHandler" = @DI\Inject("claroline.config.platform_config_handler")
     * })
     *
     * @param TokenStorageInterface        $tokenStorage
     * @param StrictDispatcher             $dispatcher
     * @param PlatformConfigurationHandler $configHandler
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        StrictDispatcher $dispatcher,
        PlatformConfigurationHandler $configHandler
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->configHandler = $configHandler;
    }

    /**
     * Renders the Claroline web application.
     *
     * @EXT\Route("/", name="claro_index")
     * @EXT\Template("ClarolineAppBundle::index.html.twig")
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            'meta' => [],
            'maintenance' => [
                'enabled' => MaintenanceHandler::isMaintenanceEnabled() || $this->configHandler->getParameter('maintenance.enable'),
                'message' => $this->configHandler->getParameter('maintenance.message'),
            ],
            'impersonated' => $this->isImpersonated(),

            'header' => [
                'menus' => array_values($this->configHandler->getParameter('header')),
                'display' => [
                    'name' => $this->configHandler->getParameter('name_active'),
                    'about' => $this->configHandler->getParameter('show_about_button'),
                    'help' => $this->configHandler->getParameter('show_help_button'),
                ],
            ],
            'footer' => [
                'content' => $this->configHandler->getParameter('footer.content'),
                'display' => [
                    'locale' => $this->configHandler->getParameter('footer.show_locale'),
                    'help' => $this->configHandler->getParameter('footer.show_help'),
                    'termsOfService' => $this->configHandler->getParameter('footer.show_terms_of_service'),
                ],
            ],

            'injectedJavascripts' => $this->injectJavascript(),
            'injectedStylesheets' => $this->injectStylesheet(),
        ];
    }

    /**
     * Gets the javascript injected by the plugins if any.
     *
     * @return string
     */
    private function injectJavascript()
    {
        /** @var InjectJavascriptEvent $event */
        $event = $this->dispatcher->dispatch('layout.inject.javascript', InjectJavascriptEvent::class);

        return $event->getContent();
    }

    /**
     * Gets the styles injected by the plugins if any.
     *
     * @return string
     */
    private function injectStylesheet()
    {
        /** @var InjectStylesheetEvent $event */
        $event = $this->dispatcher->dispatch('layout.inject.stylesheet', InjectStylesheetEvent::class);

        return $event->getContent();
    }

    private function isImpersonated()
    {
        if ($token = $this->tokenStorage->getToken()) {
            foreach ($token->getRoles() as $role) {
                if ($role instanceof SwitchUserRole) {
                    return true;
                }
            }
        }

        return false;
    }
}
