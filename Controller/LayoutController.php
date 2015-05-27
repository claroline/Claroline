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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Manager\HomeManager;
use Claroline\CoreBundle\Event\StrictDispatcher;

/**
 * Actions of this controller are not routed. They're intended to be rendered
 * directly in the base "ClarolineCoreBundle::layout.html.twig" template.
 */
class LayoutController extends Controller
{
    private $roleManager;
    private $workspaceManager;
    private $router;
    private $tokenStorage;
    private $utils;
    private $translator;
    private $configHandler;
    private $toolManager;
    private $dipatcher;

    /**
     * @DI\InjectParams({
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "tokenStorage"       = @DI\Inject("security.token_storage"),
     *     "utils"              = @DI\Inject("claroline.security.utilities"),
     *     "translator"         = @DI\Inject("translator"),
     *     "configHandler"      = @DI\Inject("claroline.config.platform_config_handler"),
     *     "toolManager"        = @DI\Inject("claroline.manager.tool_manager"),
     *     "homeManager"        = @DI\Inject("claroline.manager.home_manager"),
     *     "dispatcher"     = @DI\Inject("claroline.event.event_dispatcher")
     * })
     */
    public function __construct(
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager,
        ToolManager $toolManager,
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage,
        Utilities $utils,
        TranslatorInterface $translator,
        PlatformConfigurationHandler $configHandler,
        HomeManager $homeManager,
        StrictDispatcher $dispatcher
    )
    {
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->toolManager = $toolManager;
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->utils = $utils;
        $this->translator = $translator;
        $this->configHandler = $configHandler;
        $this->homeManager = $homeManager;
        $this->dispatcher = $dispatcher;
    }

    /**
     * @EXT\Template()
     *
     * Displays the platform header.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function headerAction()
    {
        return array();
    }

    /**
     * @EXT\Template()
     *
     * Displays the platform footer.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function footerAction()
    {
        //for some reason this doesn't work unless we use the cache:warm command. Since it's annoying
        //and that I can't find why, we retrieve the version the old fashioned way;
        $bundleManager = $this->get('claroline.manager.bundle_manager');
        $version = $bundleManager->getCoreBundleVersion();

        return array(
            'footerMessage' => $this->configHandler->getParameter('footer'),
            'footerLogin' => $this->configHandler->getParameter('footer_login'),
            'footerWorkspaces' => $this->configHandler->getParameter('footer_workspaces'),
            'headerLocale' => $this->configHandler->getParameter('header_locale'),
            'coreVersion' => $version
        );
    }

    /**
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template()
     *
     * Displays the platform top bar. Its content depends on the user status
     * (anonymous/logged, profile, etc.) and the platform options (e.g. self-
     * registration allowed/prohibited).
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topBarAction(Workspace $workspace = null)
    {
        if ($token = $this->tokenStorage->getToken()) {
            $tools = $this->toolManager->getAdminToolsByRoles($token->getRoles());
        } else {
            $tools = array();
        }

        $canAdministrate = count($tools) > 0;
        $isLogged = false;
        $registerTarget = null;
        $loginTarget = null;
        $workspaces = null;
        $personalWs = null;
        $homeMenu = $this->configHandler->getParameter('home_menu');
        $showHelpButton = $this->configHandler->getParameter('show_help_button');
        $helpUrl = $this->configHandler->getParameter('help_url');

        if (is_numeric($homeMenu)) {
            $homeMenu = $this->homeManager->getContentByType('menu', $homeMenu);
        }

        if ($token) {
            $user = $token->getUser();
            $roles = $this->utils->getRoles($token);
        } else {
            $roles = array('ROLE_ANONYMOUS');
        }
        $adminTools = array();

        if ($token) {
            $secRoles = $this->tokenStorage->getToken()->getRoles();
        } else {
            $secRoles = array();
        }

        $adminTools = $this->toolManager->getAdminToolsByRoles($secRoles);
        $isLogged = !in_array('ROLE_ANONYMOUS', $roles);

        if ($isLogged) {
            $tools = $this->toolManager->getAdminToolsByRoles($secRoles);
            $canAdministrate = count($tools) > 0;
            $personalWs = $user->getPersonalWorkspace();
            $workspaces = $this->findWorkspacesFromLogs();
        } else {
            $workspaces = $this->workspaceManager->getWorkspacesByAnonymous();

            if (true === $this->configHandler->getParameter('allow_self_registration') &&
                $this->roleManager->validateRoleInsert(
                    new User(),
                    $this->roleManager->getRoleByName('ROLE_USER')
                )
            ) {
                $registerTarget = 'claro_registration_user_registration_form';
            }

            $loginTarget = $this->router->generate('claro_desktop_open');
        }

        return array(
            'isLogged' => $isLogged,
            'register_target' => $registerTarget,
            'login_target' => $loginTarget,
            'workspaces' => $workspaces,
            'personalWs' => $personalWs,
            "isImpersonated" => $this->isImpersonated(),
            'isInAWorkspace' => $workspace !== null,
            'currentWorkspace' => $workspace,
            'canAdministrate' => $canAdministrate,
            'headerLocale' => $this->configHandler->getParameter('header_locale'),
            'homeMenu' => $homeMenu,
            'adminTools' => $adminTools,
            'showHelpButton' => $showHelpButton,
            'helpUrl' => $helpUrl
        );
    }

    /**
     * @EXT\Template()
     *
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
        $isUserImpersonated = false;
        $workspaceName = '';
        $roleName = '';

        foreach ($roles as $role) {
            if (strstr($role, 'ROLE_USURPATE_WORKSPACE_ROLE')) {
                $isRoleImpersonated = true;
            }

            if (strstr($role, 'ROLE_PREVIOUS_ADMIN')) {
                $isUserImpersonated = true;
            }
        }

        if ($isRoleImpersonated) {
            foreach ($roles as $role) {
                if (strstr($role, 'ROLE_WS')) {
                    $impersonatedRole = $role;
                }
            }
            if ($impersonatedRole === null) {
                $roleName = 'ROLE_ANONYMOUS';
            } else {
                $guid = substr($impersonatedRole, strripos($impersonatedRole, '_') + 1);
                $workspace = $this->workspaceManager->getOneByGuid($guid);
                $roleEntity = $this->roleManager->getRoleByName($impersonatedRole);
                $roleName = $roleEntity->getTranslationKey();
                $workspaceName = $workspace->getName();
            }
        }

        return array(
            "isImpersonated" => $this->isImpersonated(),
            'workspace' => $workspaceName,
            'role' => $roleName
        );
    }

    //not routed
    public function injectJavascriptAction()
    {
        $event = $this->dispatcher->dispatch('inject_javascript_layout', 'InjectJavascript');

        return new Response($event->getContent());
    }

    private function isImpersonated()
    {
        if ($token = $this->tokenStorage->getToken()) {
            foreach ($token->getRoles() as $role) {
                if ($role instanceof \Symfony\Component\Security\Core\Role\SwitchUserRole) {
                    return true;
                }
            }
        }

        return false;
    }

    private function findWorkspacesFromLogs()
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        $roles = $this->utils->getRoles($token);
        $wsLogs = $this->workspaceManager->getLatestWorkspacesByUser($user, $roles);
        $workspaces = array();

        if (!empty($wsLogs)) {
            foreach ($wsLogs as $wsLog) {
                $workspaces[] = $wsLog['workspace'];
            }
        }

        return $workspaces;
    }
}
