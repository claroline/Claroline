<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\MessageManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * Actions of this controller are not routed. They're intended to be rendered
 * directly in the base "ClarolineCoreBundle::layout.html.twig" template.
 */
class LayoutController extends Controller
{
    private $messageManager;
    private $roleManager;
    private $workspaceManager;
    private $router;
    private $security;
    private $utils;
    private $translator;
    private $configHandler;

    /**
     * @DI\InjectParams({
     *     "messageManager"     = @DI\Inject("claroline.manager.message_manager"),
     *     "roleManager"        = @DI\Inject("claroline.manager.role_manager"),
     *     "workspaceManager"   = @DI\Inject("claroline.manager.workspace_manager"),
     *     "router"             = @DI\Inject("router"),
     *     "security"           = @DI\Inject("security.context"),
     *     "utils"              = @DI\Inject("claroline.security.utilities"),
     *     "translator"         = @DI\Inject("translator"),
     *     "configHandler"      = @DI\Inject("claroline.config.platform_config_handler")
     * })
     */
    public function __construct(
        MessageManager $messageManager,
        RoleManager $roleManager,
        WorkspaceManager $workspaceManager,
        UrlGeneratorInterface $router,
        SecurityContextInterface $security,
        Utilities $utils,
        Translator $translator,
        PlatformConfigurationHandler $configHandler
    )
    {
        $this->messageManager = $messageManager;
        $this->roleManager = $roleManager;
        $this->workspaceManager = $workspaceManager;
        $this->router = $router;
        $this->security = $security;
        $this->utils = $utils;
        $this->translator = $translator;
        $this->configHandler = $configHandler;
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
        return array();
    }

    /**
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template()
     *
     * Displays the platform top bar. Its content depends on the user status
     * (anonymous/logged, profile, etc.) and the platform options (e.g. self-
     * registration allowed/prohibited).
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function topBarAction(AbstractWorkspace $workspace = null)
    {
        $isLogged = false;
        $countUnreadMessages = 0;
        $username = null;
        $registerTarget = null;
        $loginTarget = null;
        $workspaces = null;
        $personalWs = null;
        $isInAWorkspace = false;

        $token = $this->security->getToken();
        $user = $token->getUser();
        $roles = $this->utils->getRoles($token);

        if (!is_null($workspace)) {
            $isInAWorkspace = true;
        }

        if (!in_array('ROLE_ANONYMOUS', $roles)) {
            $isLogged = true;
        }

        if ($isLogged) {
            $isLogged = true;
            $countUnreadMessages = $this->messageManager->getNbUnreadMessages($user);
            $username = $user->getFirstName() . ' ' . $user->getLastName();
            $personalWs = $user->getPersonalWorkspace();
            $workspaces = $this->findWorkspacesFromLogs();
        } else {
            $username = $this->translator->trans('login', array(), 'platform');
            $workspaces = $this->workspaceManager->getWorkspacesByAnonymous();

            if (true === $this->configHandler->getParameter('allow_self_registration')) {
                $registerTarget = 'claro_registration_user_registration_form';
            }

            $loginTarget = $this->router->generate('claro_desktop_open');
        }

        return array(
            'isLogged' => $isLogged,
            'countUnreadMessages' => $countUnreadMessages,
            'username' => $username,
            'register_target' => $registerTarget,
            'login_target' => $loginTarget,
            'workspaces' => $workspaces,
            'personalWs' => $personalWs,
            "isImpersonated" => $this->isImpersonated(),
            'isInAWorkspace' => $isInAWorkspace,
            'currentWorkspace' => $workspace
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
        $token = $this->security->getToken();
        $roles = $this->utils->getRoles($token);
        $impersonatedRole = null;

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
        }

        return array(
            "isImpersonated" => $this->isImpersonated(),
            'workspace' => $workspace->getName(),
            'role' => $roleName
        );
    }

    private function isImpersonated()
    {
        foreach ($this->security->getToken()->getRoles() as $role) {
            if ($role instanceof \Symfony\Component\Security\Core\Role\SwitchUserRole) {
                return true;
            }
        }

        return false;
    }

    private function findWorkspacesFromLogs()
    {
        $token = $this->security->getToken();
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
