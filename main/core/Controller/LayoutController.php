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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Event\InjectJavascriptEvent;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Icap\NotificationBundle\Manager\NotificationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
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

    private $dispatcher;
    private $roleManager;
    private $workspaceManager;
    private $notificationManager;
    private $tokenStorage;
    private $utils;
    private $configHandler;
    private $toolManager;
    private $serializer;

    /**
     * LayoutController constructor.
     *
     * @DI\InjectParams({
     *     "roleManager"         = @DI\Inject("claroline.manager.role_manager"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "notificationManager" = @DI\Inject("icap.notification.manager"),
     *     "tokenStorage"        = @DI\Inject("security.token_storage"),
     *     "utils"               = @DI\Inject("claroline.security.utilities"),
     *     "configHandler"       = @DI\Inject("claroline.config.platform_config_handler"),
     *     "toolManager"         = @DI\Inject("claroline.manager.tool_manager"),
     *     "dispatcher"          = @DI\Inject("claroline.event.event_dispatcher"),
     *     "serializer"          = @DI\Inject("claroline.api.serializer")
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
        SerializerProvider $serializer
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
    }

    /**
     * @EXT\Template()
     *
     * Displays the platform footer.
     *
     * @return array
     */
    public function footerAction()
    {
        // TODO: find the lightest way to get that information
        $version = $this->get('claroline.manager.version_manager')->getDistributionVersion();

        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $selfRegistration = $this->configHandler->getParameter('allow_self_registration') &&
            $this->roleManager->validateRoleInsert(new User(), $roleUser);

        return [
            'footerMessage' => $this->configHandler->getParameter('footer'),
            'footerLogin' => $this->configHandler->getParameter('footer_login'),
            'footerWorkspaces' => $this->configHandler->getParameter('footer_workspaces'),
            'headerLocale' => $this->configHandler->getParameter('header_locale'),
            'coreVersion' => $version,
            'selfRegistration' => $selfRegistration,
        ];
    }

    /**
     * Displays the platform top bar. Its content depends on the user status
     * (anonymous/logged, profile, etc.) and the platform options (e.g. self-
     * registration allowed/prohibited).
     *
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true},
     *      converter="strict_id"
     * )
     * @EXT\Template()
     *
     * @param Workspace $workspace
     * @param Request   $request
     *
     * @return array
     */
    public function topBarAction(Workspace $workspace = null, Request $request)
    {
        $user = null;
        $token = $this->tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
        }

        $workspaces = [];
        $personalWs = null;
        if ($user instanceof User) {
            $personalWs = $user->getPersonalWorkspace();
            $workspaces = $this->workspaceManager->getRecentWorkspaceForUser($user, $this->utils->getRoles($token));
        }

        $lockedOrderedTools = $this->toolManager->getOrderedToolsLockedByAdmin(1);
        $adminTools = [];
        $excludedTools = [];

        foreach ($lockedOrderedTools as $lockedOrderedTool) {
            $lockedTool = $lockedOrderedTool->getTool();

            if ($lockedOrderedTool->isVisibleInDesktop()) {
                $adminTools[] = $lockedTool;
            }
            $excludedTools[] = $lockedTool;
        }
        // current context (desktop, index or workspace)
        $current = 'desktop';
        if ('claro_admin_open_tool' === $request->get('_route')) {
            $current = 'administration';
        } elseif ('claro_index' === $request->get('_route')) {
            $current = 'home';
        } elseif ('claro_workspace_open_tool' === $request->get('_route')) {
            $current = 'workspace';
        }

        // if has_role('ROLE_USURPATE_WORKSPACE_ROLE') or is_impersonated()
        // if ($role instanceof \Symfony\Component\Security\Core\Role\SwitchUserRole)

        // I think we will need to merge this with the default platform config object
        // this can be done when the top bar will be moved in the main react app
        return [
            'current' => $current,
            'display' => [
                'about' => $this->configHandler->getParameter('show_about_button'),
                'help' => $this->configHandler->getParameter('show_help_button'),
                'registration' => $this->configHandler->getParameter('allow_self_registration'),
                'locale' => $this->configHandler->getParameter('header_locale'),
            ],

            'workspaces' => [
                'creatable' => $this->authorization->isGranted('CREATE', new Workspace()),
                'current' => $workspace ? $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]) : null,
                'personal' => $personalWs ? $this->serializer->serialize($personalWs, [Options::SERIALIZE_MINIMAL]) : null,
                'history' => array_map(function (Workspace $workspace) { // TODO : async load it on ws menu open
                    return $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
                }, $workspaces),
            ],

            'notifications' => [
                'count' => $token->getUser() instanceof User ? $this->notificationManager->countUnviewedNotifications($token->getUser()) : '',
                'refreshDelay' => $this->configHandler->getParameter('notifications_refresh_delay'),
            ],

            //'isImpersonated' => $this->isImpersonated(),
            //'homeMenu' => $homeMenu,
            'administration' => array_map(function (AdminTool $tool) {
                return [
                    'icon' => $tool->getClass(),
                    'name' => $tool->getName(),
                    'open' => ['claro_admin_open_tool', ['toolName' => $tool->getName()]],
                ];
            }, $this->toolManager->getAdminToolsByRoles($token->getRoles())),

            'userTools' => array_map(function (Tool $tool) {
                return [
                    'icon' => $tool->getClass(),
                    'name' => $tool->getName(),
                    'open' => ['claro_desktop_open_tool', ['toolName' => $tool->getName()]],
                ];
            }, $token->getUser() instanceof User ?
              $this->toolManager->getDisplayedDesktopOrderedTools($token->getUser(), 1, $excludedTools)
              :
              []
            ),

            'tools' => array_map(function (Tool $tool) {
                return [
                    'icon' => $tool->getClass(),
                    'name' => $tool->getName(),
                    'open' => ['claro_desktop_open_tool', ['toolName' => $tool->getName()]],
                ];
            }, $token->getUser() instanceof User ?
            $this->toolManager->getDisplayedDesktopOrderedTools($token->getUser(), 0, $excludedTools)
            : []
          ),
        ];
    }

    /**
     * Renders the warning bar when a workspace role is impersonated.
     *
     * @EXT\Template()
     *
     * @return array
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

        return [
            'isImpersonated' => $this->isImpersonated(),
            'workspace' => $workspaceName,
            'role' => $roleName,
        ];
    }

    //not routed
    public function injectJavascriptAction()
    {
        /** @var InjectJavascriptEvent $event */
        $event = $this->dispatcher->dispatch('inject_javascript_layout', InjectJavascriptEvent::class);

        return new Response($event->getContent());
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
