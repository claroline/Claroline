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

use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\HomeManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Translation\TranslatorInterface;

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
    ) {
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
        return [];
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
        // TODO: replace core bundle version by distribution version when available
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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topBarAction(Workspace $workspace = null)
    {
        if ($token = $this->tokenStorage->getToken()) {
            $tools = $this->toolManager->getAdminToolsByRoles($token->getRoles());
        } else {
            $tools = [];
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
        $loginTargetRoute = $this->configHandler->getParameter('login_target_route');

        if (is_numeric($homeMenu)) {
            $homeMenu = $this->homeManager->getContentByType('menu', $homeMenu);
        }

        if ($token) {
            $user = $token->getUser();
            $roles = $this->utils->getRoles($token);
        } else {
            $roles = ['ROLE_ANONYMOUS'];
        }
        $adminTools = [];

        if ($token) {
            $secRoles = $this->tokenStorage->getToken()->getRoles();
        } else {
            $secRoles = [];
        }

        $adminTools = $this->toolManager->getAdminToolsByRoles($secRoles);
        $isLogged = !in_array('ROLE_ANONYMOUS', $roles);

        if ($isLogged) {
            $tools = $this->toolManager->getAdminToolsByRoles($secRoles);
            $canAdministrate = count($tools) > 0;
            $personalWs = $user->getPersonalWorkspace();
            $workspaces = $this->findWorkspacesFromLogs();
        } else {
            $workspaces = [];

            if (true === $this->configHandler->getParameter('allow_self_registration') &&
                $this->roleManager->validateRoleInsert(
                    new User(),
                    $this->roleManager->getRoleByName('ROLE_USER')
                )
            ) {
                $registerTarget = $this->router->generate('claro_user_registration');
            }

            if (!$loginTargetRoute) {
                $loginTarget = $this->router->generate('claro_security_login');
            } else {
                $loginTarget = $this->routeExists($loginTargetRoute) ? $this->router->generate($loginTargetRoute) : $loginTargetRoute;
            }
        }

        $translator = $this->translator;
        usort($adminTools, function ($a, $b) use ($translator) {
            return $translator->trans($a->getName(), [], 'tools') > $translator->trans($b->getName(), [], 'tools');
        });

        return [
            'isLogged' => $isLogged,
            'register_target' => $registerTarget,
            'login_target' => $loginTarget,
            'workspaces' => $workspaces,
            'personalWs' => $personalWs,
            'isImpersonated' => $this->isImpersonated(),
            'isInAWorkspace' => null !== $workspace,
            'currentWorkspace' => $workspace,
            'canAdministrate' => $canAdministrate,
            'headerLocale' => $this->configHandler->getParameter('header_locale'),
            'homeMenu' => $homeMenu,
            'adminTools' => $adminTools,
            'showHelpButton' => $showHelpButton,
            'helpUrl' => $helpUrl,
        ];
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
        $event = $this->dispatcher->dispatch('inject_javascript_layout', 'InjectJavascript');

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

    private function findWorkspacesFromLogs()
    {
        $token = $this->tokenStorage->getToken();
        $user = $token->getUser();
        $roles = $this->utils->getRoles($token);
        $wsLogs = $this->workspaceManager->getLatestWorkspacesByUser($user, $roles);
        $workspaces = [];

        if (!empty($wsLogs)) {
            foreach ($wsLogs as $wsLog) {
                $workspaces[] = $wsLog['workspace'];
            }
        }

        return $workspaces;
    }

    private function routeExists($name)
    {
        // I assume that you have a link to the container in your twig extension class
        $router = $this->container->get('router');

        return (null === $router->getRouteCollection()->get($name)) ? false : true;
    }
}
