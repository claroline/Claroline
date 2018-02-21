<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Administration;

use Claroline\AppBundle\API\FinderProvider;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\API\Serializer\ParametersSerializer;
use Claroline\CoreBundle\API\Serializer\User\ProfileSerializer;
use Claroline\CoreBundle\Entity\Action\AdditionalAction;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Administration\ProfilePicsImportType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\LocaleManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\RightsManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\ToolManager;
use Claroline\CoreBundle\Manager\ToolMaskDecoderManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @todo rename UserController (without s)
 *
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('user_management')")
 */
class UsersController extends Controller
{
    private $configHandler;
    private $eventDispatcher;
    private $formFactory;
    private $localeManager;
    private $mailManager;
    private $request;
    private $rightsManager;
    private $roleManager;
    private $router;
    private $session;
    private $toolManager;
    private $toolMaskDecoderManager;
    private $translator;
    private $userAdminTool;
    private $userManager;
    private $workspaceManager;
    private $authorization;
    private $groupManager;
    private $tokenStorage;
    private $finder;
    private $parametersSerializer;
    private $profileSerializer;

    /**
     * @DI\InjectParams({
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager"),
     *     "configHandler"          = @DI\Inject("claroline.config.platform_config_handler"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "localeManager"          = @DI\Inject("claroline.manager.locale_manager"),
     *     "mailManager"            = @DI\Inject("claroline.manager.mail_manager"),
     *     "request"                = @DI\Inject("request"),
     *     "rightsManager"          = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "router"                 = @DI\Inject("router"),
     *     "authorization"          = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "session"                = @DI\Inject("session"),
     *     "toolManager"            = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolMaskDecoderManager" = @DI\Inject("claroline.manager.tool_mask_decoder_manager"),
     *     "translator"             = @DI\Inject("translator"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager"),
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager"),
     *     "finder"                 = @DI\Inject("claroline.api.finder"),
     *     "parametersSerializer"   = @DI\Inject("claroline.serializer.parameters"),
     *     "profileSerializer"      = @DI\Inject("claroline.serializer.profile")
     * })
     */
    public function __construct(
        AuthenticationManager $authenticationManager,
        FormFactory $formFactory,
        LocaleManager $localeManager,
        MailManager $mailManager,
        PlatformConfigurationHandler $configHandler,
        Request $request,
        RightsManager $rightsManager,
        RoleManager $roleManager,
        RouterInterface $router,
        AuthorizationCheckerInterface $authorization,
        TokenStorageInterface $tokenStorage,
        SessionInterface $session,
        StrictDispatcher $eventDispatcher,
        ToolManager $toolManager,
        ToolMaskDecoderManager $toolMaskDecoderManager,
        TranslatorInterface $translator,
        UserManager $userManager,
        WorkspaceManager $workspaceManager,
        GroupManager $groupManager,
        FinderProvider $finder,
        ParametersSerializer $parametersSerializer,
        ProfileSerializer $profileSerializer
    ) {
        $this->authenticationManager = $authenticationManager;
        $this->configHandler = $configHandler;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->localeManager = $localeManager;
        $this->mailManager = $mailManager;
        $this->request = $request;
        $this->rightsManager = $rightsManager;
        $this->roleManager = $roleManager;
        $this->router = $router;
        $this->authorization = $authorization;
        $this->session = $session;
        $this->toolManager = $toolManager;
        $this->toolMaskDecoderManager = $toolMaskDecoderManager;
        $this->translator = $translator;
        $this->userAdminTool = $this->toolManager->getAdminToolByName('user_management');
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->groupManager = $groupManager;
        $this->tokenStorage = $tokenStorage;
        $this->finder = $finder;
        $this->parametersSerializer = $parametersSerializer;
        $this->profileSerializer = $profileSerializer;
    }

    /**
     * @EXT\Route(
     *     "/index",
     *     name="claro_admin_users_index",
     *     options = {"expose"=true}
     * )
     * @EXT\Template
     *
     * Displays the platform user list.
     *
     * @return array
     */
    public function indexAction()
    {
        return [
            // todo : put it in the async load of form
            'parameters' => $this->parametersSerializer->serialize(),
            'profile' => $this->profileSerializer->serialize(),
        ];
    }

    /**
     * @EXT\Route(
     *     "/user/{user}/workspaces/page/{page}/max/{max}",
     *     name="claro_admin_user_workspaces",
     *     defaults={"page"=1, "max"=50},
     *     options={"expose"=true}
     * )
     * @EXT\Template
     *
     * @param User $user
     * @param int  $page
     * @param int  $max
     *
     * @return array
     */
    public function userWorkspaceListAction(User $user, $page, $max)
    {
        $pager = $this->workspaceManager->getOpenableWorkspacesByRolesPager($user->getRoles(), $page, $max);

        return ['user' => $user, 'pager' => $pager, 'page' => $page, 'max' => $max];
    }

    /**
     * @EXT\Route("/export/users/{format}", name="claro_admin_export_users")
     *
     * @return Response
     */
    public function export($format)
    {
        $exporter = $this->container->get('claroline.exporter.'.$format);
        $exporterManager = $this->container->get('claroline.manager.exporter_manager');
        $file = $exporterManager->export('Claroline\CoreBundle\Entity\User', $exporter);
        $response = new StreamedResponse();

        $response->setCallBack(
            function () use ($file) {
                readfile($file);
            }
        );

        $response->headers->set('Content-Transfer-Encoding', 'octet-stream');
        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', 'attachment; filename=users.'.$format);

        switch ($format) {
            case 'csv': $response->headers->set('Content-Type', 'text/csv'); break;
            case 'xls': $response->headers->set('Content-Type', 'application/vnd.ms-excel'); break;
        }

        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route(
     *     "/workspace/personal/tool/config",
     *     name="claro_admin_workspace_tool_config_index"
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\Users:personalWorkspaceToolConfig.html.twig").
     *
     * @return array
     */
    public function personalWorkspaceToolConfigIndexAction()
    {
        $personalWsToolConfigs = $this->toolManager->getPersonalWorkspaceToolConfigAsArray();
        $maskDecoders = $this->toolManager->getAllWorkspaceMaskDecodersAsArray();
        $roles = $this->roleManager->getAllPlatformRoles();
        $tools = $this->toolManager->getAvailableWorkspaceTools();

        return [
            'personalWsToolConfigs' => $personalWsToolConfigs,
            'roles' => $roles,
            'tools' => $tools,
            'maskDecoders' => $maskDecoders,
        ];
    }

    /**
     * @EXT\Route(
     *     "/pws/tool/activate/{perm}/{role}/{tool}",
     *     name="claro_admin_pws_activate_tool",
     *     options={"expose"=true}
     * )
     */
    public function activatePersonalWorkspaceToolPermAction($perm, Role $role, Tool $tool)
    {
        $this->toolManager->activatePersonalWorkspaceToolPerm($perm, $tool, $role);

        return new JsonResponse([], 200);
    }

    /**
     * @EXT\Route(
     *     "/pws/tool/remove/{perm}/{role}/{tool}",
     *     name="claro_admin_pws_remove_tool",
     *     options={"expose"=true}
     * )
     */
    public function removePersonalWorkspaceToolPermAction($perm, Role $role, Tool $tool)
    {
        $this->toolManager->removePersonalWorkspaceToolPerm($perm, $tool, $role);

        return new JsonResponse([], 200);
    }

    /**
     * @EXT\Route(
     *     "import/profile/pics/form",
     *     name="import_profile_pics_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     * )
     */
    public function importProfilePicsFormAction()
    {
        $form = $this->createForm(new ProfilePicsImportType());

        return ['form' => $form->createView()];
    }

    /**
     * @EXT\Route(
     *     "import/profile/pics",
     *     name="import_profile_pics",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration/Users:importProfilePicsForm.html.twig")
     */
    public function importProfilePicsAction()
    {
        $form = $this->createForm(new ProfilePicsImportType());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $this->userManager->importPictureFiles($file);
        }

        return ['form' => $form->createView()];
    }

    /**
     * @EXT\Route(
     *     "/{user}/admin/action/{action}",
     *     name="admin_user_action",
     *     options={"expose"=true}
     * )
     */
    public function executeUserAdminAction(User $user, AdditionalAction $action)
    {
        $event = $this->eventDispatcher->dispatch($action->getType().'_'.$action->getAction(), 'AdminUserAction', ['user' => $user]);

        return $event->getResponse();
    }

    /**
     * This method should be moved.
     *
     * @EXT\Route(
     *     "/{group}/admin/action/{action}",
     *     name="admin_group_action",
     *     options={"expose"=true}
     * )
     */
    public function executeGroupAdminAction(Group $group, AdditionalAction $action)
    {
        $event = $this->eventDispatcher->dispatch($action->getType().'_'.$action->getAction(), 'AdminGroupAction', ['group' => $group]);

        return $event->getResponse();
    }
}
