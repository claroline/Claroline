<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\ToolsOptions;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\Layout\InjectJavascriptEvent;
use Claroline\CoreBundle\Event\Layout\InjectStylesheetEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Claroline\MessageBundle\Entity\Message;
use Icap\NotificationBundle\Manager\NotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

/**
 * Actions of this controller are not routed. They're intended to be rendered
 * directly in the base "ClarolineCoreBundle::layout.html.twig" template.
 */
class LayoutController extends Controller
{
    use PermissionCheckerTrait;

    /** @var StrictDispatcher */
    private $dispatcher;
    /** @var RoleManager */
    private $roleManager;
    /** @var WorkspaceManager */
    private $workspaceManager;
    /** @var NotificationManager */
    private $notificationManager;
    /** @var TokenStorageInterface */
    private $tokenStorage;
    /** @var Utilities */
    private $utils;
    /** @var PlatformConfigurationHandler */
    private $configHandler;
    /** @var ToolManager */
    private $toolManager;
    /** @var SerializerProvider */
    private $serializer;
    /** @var FinderProvider */
    private $finder;

    /**
     * LayoutController constructor.
     *
     * @DI\InjectParams({
     *     "templating"          = @DI\Inject("templating"),
     *     "roleManager"         = @DI\Inject("claroline.manager.role_manager"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "notificationManager" = @DI\Inject("icap.notification.manager"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "utils"               = @DI\Inject("claroline.security.utilities"),
     *     "configHandler"       = @DI\Inject("claroline.config.platform_config_handler"),
     *     "toolManager"         = @DI\Inject("claroline.manager.tool_manager"),
     *     "dispatcher"          = @DI\Inject("claroline.event.event_dispatcher"),
     *     "serializer"          = @DI\Inject("claroline.api.serializer"),
     *     "finder"              = @DI\Inject("claroline.api.finder")
     * })
     *
     * @param RoleManager                  $roleManager
     * @param WorkspaceManager             $workspaceManager
     * @param ToolManager                  $toolManager
     * @param NotificationManager          $notificationManager
     * @param TokenStorageInterface        $tokenStorage
     * @param Utilities                    $utils
     * @param PlatformConfigurationHandler $configHandler
     * @param StrictDispatcher             $dispatcher
     * @param SerializerProvider           $serializer
     * @param FinderProvider               $finder
     */
    public function __construct(
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager,
        ToolManager $toolManager,
        NotificationManager $notificationManager,
        TokenStorageInterface $tokenStorage,
        Utilities $utils,
        PlatformConfigurationHandler $configHandler,
        StrictDispatcher $dispatcher,
        SerializerProvider $serializer,
        FinderProvider $finder
    ) {
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->toolManager = $toolManager;
        $this->notificationManager = $notificationManager;
        $this->tokenStorage = $tokenStorage;
        $this->utils = $utils;
        $this->configHandler = $configHandler;
        $this->dispatcher = $dispatcher;
        $this->serializer = $serializer;
        $this->finder = $finder;
    }

    /**
     * Renders the platform top bar.
     * Its content depends on the user status (anonymous/logged, profile, etc.)
     * and the platform options (e.g. self-registration allowed/prohibited).
     *
     * @param Request   $request
     * @param Workspace $workspace
     *
     * @return Response
     *
     * @todo simplify me
     */
    public function topBarAction(Request $request, Workspace $workspace = null)
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
                ToolsOptions::TOOL_CATEGORY === $tool->getDesktopCategory();
        });
        $userTools = array_filter($orderedTools, function (OrderedTool $ot) {
            $tool = $ot->getTool();

            return $ot->isVisibleInDesktop() &&
                !in_array($tool->getName(), ToolsOptions::EXCLUDED_TOOLS) &&
                ToolsOptions::USER_CATEGORY === $tool->getDesktopCategory();
        });
        $notificationTools = array_filter($orderedTools, function (OrderedTool $ot) {
            $tool = $ot->getTool();

            return $ot->isVisibleInDesktop() &&
                !in_array($tool->getName(), ToolsOptions::EXCLUDED_TOOLS) &&
                ToolsOptions::NOTIFICATION_CATEGORY === $tool->getDesktopCategory();
        });

        // current context (desktop, index or workspace)
        // TODO : find a more generic way to calculate it and use constants
        $current = 'home';
        if ($workspace) {
            $current = 'workspace';
        } elseif ('claro_admin_open_tool' === $request->get('_route') || null === $request->get('_route')) {
            $current = 'administration';
        } elseif ('claro_desktop_open_tool' === $request->get('_route')) {
            $current = 'desktop';
        }

        // I think we will need to merge this with the default platform config object
        // this can be done when the top bar will be moved in the main react app
        return $this->render('ClarolineCoreBundle:layout:top_bar.html.twig', [
            'isImpersonated' => $this->isImpersonated(),
            'mainMenu' => $this->configHandler->getParameter('header_menu'),
            'context' => [
                'type' => $current,
                'data' => $workspace ? $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]) : null,
            ],
            'display' => [
                'about' => $this->configHandler->getParameter('show_about_button'),
                'help' => $this->configHandler->getParameter('show_help_button'),
                'registration' => $this->configHandler->getParameter('allow_self_registration'),
                'locale' => $this->configHandler->getParameter('header_locale'),
                'name' => $this->configHandler->getParameter('name_active'),
            ],

            'notifications' => [
                'count' => [
                  'notifications' => $token->getUser() instanceof User ? $this->notificationManager->countUnviewedNotifications($token->getUser()) : 0,
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
                    'open' => ['claro_admin_open_tool', ['toolName' => $tool->getName()]],
                ];
            }, $this->toolManager->getAdminToolsByRoles($token->getRoles())),

            'userTools' => array_map(function (OrderedTool $orderedTool) {
                $tool = $orderedTool->getTool();

                return [
                    'icon' => $tool->getClass(),
                    'name' => $tool->getName(),
                    'open' => ['claro_desktop_open_tool', ['toolName' => $tool->getName()]],
                ];
            }, array_values($userTools)),

            'tools' => array_map(function (OrderedTool $orderedTool) {
                $tool = $orderedTool->getTool();

                return [
                    'icon' => $tool->getClass(),
                    'name' => $tool->getName(),
                    'open' => ['claro_desktop_open_tool', ['toolName' => $tool->getName()]],
                ];
            }, array_values($tools)),

            'notificationTools' => array_map(function (OrderedTool $orderedTool) {
                $tool = $orderedTool->getTool();

                return [
                    'icon' => $tool->getClass(),
                    'name' => $tool->getName(),
                    'open' => ['claro_desktop_open_tool', ['toolName' => $tool->getName()]],
                ];
            }, array_values($notificationTools)),
        ]);
    }

    /**
     * Renders the platform footer.
     *
     * @return Response
     */
    public function footerAction()
    {
        // TODO: find the lightest way to get that information
        $version = $this->get('claroline.manager.version_manager')->getDistributionVersion();

        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $selfRegistration = $this->configHandler->getParameter('allow_self_registration') &&
            $this->roleManager->validateRoleInsert(new User(), $roleUser);

        return $this->render('ClarolineCoreBundle:layout:footer.html.twig', [
            'footerMessage' => $this->configHandler->getParameter('footer'),
            'footerLogin' => $this->configHandler->getParameter('footer_login'),
            'footerWorkspaces' => $this->configHandler->getParameter('footer_workspaces'),
            'headerLocale' => $this->configHandler->getParameter('header_locale'),
            'coreVersion' => $version,
            'selfRegistration' => $selfRegistration,
        ]);
    }

    /**
     * Renders the warning bar when a workspace role is impersonated.
     *
     * @return Response
     */
    public function renderWarningImpersonationAction()
    {
        $token = $this->tokenStorage->getToken();
        $roles = $this->utils->getRoles($token);
        $impersonatedRole = null;
        $isRoleImpersonated = false;
        $workspaceName = '';
        $roleName = '';

        foreach ($roles as $role) {
            if (strstr($role, 'ROLE_USURPATE_WORKSPACE_ROLE')) {
                $isRoleImpersonated = true;
            }
        }

        if ($isRoleImpersonated) {
            foreach ($roles as $role) {
                if (strstr($role, 'ROLE_WS')) {
                    $impersonatedRole = $role;
                }
            }
            if (null === $impersonatedRole) {
                $roleName = 'ROLE_ANONYMOUS';
            } else {
                $guid = substr($impersonatedRole, strripos($impersonatedRole, '_') + 1);
                $workspace = $this->workspaceManager->getOneByGuid($guid);
                $roleEntity = $this->roleManager->getRoleByName($impersonatedRole);
                $roleName = $roleEntity->getTranslationKey();

                if ($workspace) {
                    $workspaceName = $workspace->getName();
                }
            }
        }

        return $this->render('ClarolineCoreBundle:layout:render_warning_impersonation.html.twig', [
            'isImpersonated' => $this->isImpersonated(),
            'workspace' => $workspaceName,
            'role' => $roleName,
        ]);
    }

    public function injectJavascriptAction()
    {
        /** @var InjectJavascriptEvent $event */
        $event = $this->dispatcher->dispatch('layout.inject.javascript', InjectJavascriptEvent::class);

        return new Response(
            $event->getContent()
        );
    }

    public function injectStylesheetAction()
    {
        /** @var InjectStylesheetEvent $event */
        $event = $this->dispatcher->dispatch('layout.inject.stylesheet', InjectStylesheetEvent::class);

        return new Response(
            $event->getContent()
        );
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
