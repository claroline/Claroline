<?php

namespace Claroline\AppBundle\Controller\Platform;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\ToolsOptions;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Maintenance\MaintenanceHandler;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\MessageBundle\Entity\Message;
use Icap\NotificationBundle\Manager\NotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\Request;
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

    /** @var FinderProvider */
    private $finder;

    /** @var ToolManager */
    private $toolManager;

    /** @var NotificationManager */
    private $notificationManager;

    /**
     * ClientController constructor.
     *
     * @DI\InjectParams({
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "dispatcher"          = @DI\Inject("claroline.event.event_dispatcher"),
     *     "configHandler"       = @DI\Inject("claroline.config.platform_config_handler"),
     *     "finder"              = @DI\Inject("claroline.api.finder"),
     *     "toolManager"         = @DI\Inject("claroline.manager.tool_manager"),
     *     "notificationManager" = @DI\Inject("icap.notification.manager")
     * })
     *
     * @param TokenStorageInterface        $tokenStorage
     * @param StrictDispatcher             $dispatcher
     * @param PlatformConfigurationHandler $configHandler
     * @param FinderProvider               $finder
     * @param ToolManager                  $toolManager
     * @param NotificationManager          $notificationManager
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        StrictDispatcher $dispatcher,
        PlatformConfigurationHandler $configHandler,
        FinderProvider $finder,
        ToolManager $toolManager,
        NotificationManager $notificationManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->dispatcher = $dispatcher;
        $this->configHandler = $configHandler;
        $this->finder = $finder;
        $this->toolManager = $toolManager;
        $this->notificationManager = $notificationManager;
    }

    /**
     * Renders the Claroline web application.
     *
     * @EXT\Route("/", name="claro_index")
     * @EXT\Template("ClarolineAppBundle::index.html.twig")
     *
     * @param Request $request
     *
     * @return array
     *
     * @todo simplify me
     */
    public function indexAction(Request $request)
    {
        $user = null;
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
        }

        $orderedTools = [];

        if ($user instanceof User) {
            $session = $request->getSession();

            // Only computes tools configured by admin one time by session
            if (is_null($session->get('ordered_tools-computed-'.$user->getUuid()))) {
                $toolsRolesConfig = $this->toolManager->getUserDesktopToolsConfiguration($user);
                $orderedTools = $this->toolManager->computeUserOrderedTools($user, $toolsRolesConfig);
                $session->set('ordered_tools-computed-'.$user->getUuid(), true);
            } else {
                $orderedTools = $this->toolManager->getOrderedToolsByUser($user);
            }
        }

        $tools = array_filter($orderedTools, function (OrderedTool $ot) {
            $tool = $ot->getTool();

            return $ot->isVisibleInDesktop() &&
                !in_array($tool->getName(), ToolsOptions::EXCLUDED_TOOLS) &&
                (ToolsOptions::TOOL_CATEGORY === $tool->getDesktopCategory() || ToolsOptions::USER_CATEGORY === $tool->getDesktopCategory());
        });
        $notificationTools = array_filter($orderedTools, function (OrderedTool $ot) {
            $tool = $ot->getTool();

            return $ot->isVisibleInDesktop() &&
                !in_array($tool->getName(), ToolsOptions::EXCLUDED_TOOLS) &&
                ToolsOptions::NOTIFICATION_CATEGORY === $tool->getDesktopCategory();
        });

        return [
            'meta' => [],
            'maintenance' => [
                'enabled' => MaintenanceHandler::isMaintenanceEnabled(),
                'message' => $this->configHandler->getParameter('maintenance.message'),
            ],
            'impersonated' => $this->isImpersonated(),

            'header' => [
                'menus' => $this->configHandler->getParameter('header_menu'),
                'display' => [
                    'name' => $this->configHandler->getParameter('name_active'),
                    'about' => $this->configHandler->getParameter('show_about_button'),
                    'help' => $this->configHandler->getParameter('show_help_button'),
                ],

                'notifications' => [
                    'count' => [
                        'notification' => $token->getUser() instanceof User ? $this->notificationManager->countUnviewedNotifications($token->getUser()) : 0,
                        'messaging' => $token->getUser() instanceof User ? $this->finder->fetch(
                            Message::class,
                            ['removed' => false, 'read' => false, 'sent' => false],
                            null,
                            0,
                            -1,
                            true
                        ) : 0,
                    ],
                    'refreshDelay' => $this->configHandler->getParameter('notifications_refresh_delay'),
                ],
                'administration' => array_map(function (AdminTool $tool) {
                    return [
                        'icon' => $tool->getClass(),
                        'name' => $tool->getName(),
                    ];
                }, $this->toolManager->getAdminToolsByRoles($token->getRoles())),

                'tools' => array_map(function (OrderedTool $orderedTool) {
                    $tool = $orderedTool->getTool();

                    return [
                        'icon' => $tool->getClass(),
                        'name' => $tool->getName(),
                    ];
                }, array_values($tools)),

                'notificationTools' => array_map(function (OrderedTool $orderedTool) {
                    $tool = $orderedTool->getTool();

                    return [
                        'icon' => $tool->getClass(),
                        'name' => $tool->getName(),
                    ];
                }, array_values($notificationTools)),
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
