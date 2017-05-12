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

use Claroline\CoreBundle\Entity\Action\AdditionalAction;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Administration\ProfilePicsImportType;
use Claroline\CoreBundle\Form\ImportUserType;
use Claroline\CoreBundle\Form\ProfileCreationType;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
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
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager")
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
        GroupManager $groupManager
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
    }

    /**
     * @EXT\Route("/new", name="claro_admin_user_creation_form")
     * @EXT\Template
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     *
     * Displays the user creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userCreationFormAction(User $currentUser)
    {
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $roles = $this->roleManager->getAllPlatformRoles();
        $unavailableRoles = [];

        foreach ($roles as $role) {
            $isAvailable = $this->roleManager->validateRoleInsert(new User(), $role);

            if (!$isAvailable) {
                $unavailableRoles[] = $role;
            }
        }
        $profileType = new ProfileCreationType(
            $this->localeManager,
            [$roleUser],
            $currentUser,
            $this->authenticationManager->getDrivers()
        );
        $form = $this->formFactory->create($profileType);

        $error = null;

        if (!$this->mailManager->isMailerAvailable()) {
            $error = 'mail_not_available';
        }
        $groupsData = [];
        $groups = $this->groupManager->getAllGroupsWithoutPager();

        foreach ($groups as $group) {
            $organizations = $group->getOrganizations();

            foreach ($organizations as $organization) {
                $organizationId = $organization->getId();

                if (!isset($groups[$organizationId])) {
                    $groupsData[$organizationId] = [];
                }
                $groupsData[$organizationId][] = $group->getId();
            }
        }

        return [
            'form_complete_user' => $form->createView(),
            'error' => $error,
            'unavailableRoles' => $unavailableRoles,
            'groupsData' => $groupsData,
        ];
    }

    /**
     * @EXT\Route("/new/submit", name="claro_admin_create_user")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Administration/Users:userCreationForm.html.twig")
     *
     * Creates an user (and its personal workspace) and redirects to the user list.
     *
     * @param User $currentUser
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(User $currentUser)
    {
        $sessionFlashBag = $this->session->getFlashBag();
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $profileType = new ProfileCreationType(
            $this->localeManager,
            [$roleUser],
            $currentUser,
            $this->authenticationManager->getDrivers()
        );
        $form = $this->formFactory->create($profileType);
        $form->handleRequest($this->request);
        $roles = $form->get('platformRoles')->getData();
        $unavailableRoles = $this->roleManager->validateNewUserRolesInsert($roles);
        $groupsData = [];
        $groups = $this->groupManager->getAllGroupsWithoutPager();

        foreach ($groups as $group) {
            $organizations = $group->getOrganizations();

            foreach ($organizations as $organization) {
                $organizationId = $organization->getId();

                if (!isset($groups[$organizationId])) {
                    $groupsData[$organizationId] = [];
                }
                $groupsData[$organizationId][] = $group->getId();
            }
        }
        if ($form->isValid() && count($unavailableRoles) === 0) {
            $user = $form->getData();
            $newRoles = $form->get('platformRoles')->getData();
            $orgas = $form->get('organizations')->getData();
            $this->userManager->createUser($user, true, $newRoles, null, null, $orgas);
            $sessionFlashBag->add(
                'success',
                $this->translator->trans('user_creation_success', [], 'platform')
            );

            return $this->redirect($this->generateUrl('claro_admin_users_index'));
        }

        $error = null;

        if (!$this->mailManager->isMailerAvailable()) {
            $error = 'mail_not_available';
        }

        return [
            'form_complete_user' => $form->createView(),
            'error' => $error,
            'unavailableRoles' => $unavailableRoles,
            'groupsData' => $groupsData,
        ];
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
        return [];
    }

    /**
     * @EXT\Route("/import", name="claro_admin_import_users_form")
     * @EXT\Template
     *
     * @return Response
     */
    public function importFormAction()
    {
        $form = $this->formFactory->create(new ImportUserType(true));

        return ['form' => $form->createView(), 'error' => null];
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
     * @EXT\Route("/import/submit", name="claro_admin_import_users")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration/Users:importForm.html.twig")
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Claroline\CoreBundle\Manager\Exception\AddRoleException
     */
    public function importAction(Request $request)
    {
        $form = $this->formFactory->create(new ImportUserType(true));
        $form->handleRequest($request);
        $mode = $form->get('mode')->getData();
        $options = ['ignore-update' => true];

        if ($mode === 'update') {
            $form = $this->formFactory->create(new ImportUserType(true, 1));
            $form->handleRequest($this->request);
            $options['ignore-update'] = false;
        } else {
        }

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $sendMail = $form->get('sendMail')->getData();
            $enableEmailNotification = $form->get('enable_mail_notification')->getData();
            $data = file_get_contents($file);
            $data = $this->container->get('claroline.utilities.misc')->formatCsvOutput($data);
            $lines = str_getcsv($data, PHP_EOL);
            $users = [];
            $sessionFlashBag = $this->session->getFlashBag();

            foreach ($lines as $line) {
                $users[] = str_getcsv($line, ';');
            }

            $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
            $max = $roleUser->getMaxUsers();
            $total = $this->userManager->countUsersByRoleIncludingGroup($roleUser);

            $countUsersToUpdate = $options['ignore-update'] ? 0 : $this->userManager->countUsersToUpdate($users);

            if ($total + count($users) - $countUsersToUpdate > $max) {
                return ['form' => $form->createView(), 'error' => 'role_user unavailable'];
            }

            $additionalRoles = $form->get('roles')->getData();

            foreach ($additionalRoles as $additionalRole) {
                $max = $additionalRole->getMaxUsers();
                $total = $this->userManager->countUsersByRoleIncludingGroup($additionalRole);
                //this is not completely true I thnk
                if ($total + count($users) - $countUsersToUpdate > $max) {
                    return [
                        'form' => $form->createView(),
                        'error' => $additionalRole->getName().' unavailable',
                    ];
                }
            }

            $logs = $this->userManager->importUsers(
                $users,
                $sendMail,
                null,
                $additionalRoles,
                $enableEmailNotification,
                $options
            );

            foreach ($logs as $key => $names) {
                $msgClass = 'success';
                if ($key === 'skipped') {
                    $msgClass = 'error';
                }
                foreach ($names as $name) {
                    $msg = '<'.$name.'> ';
                    $msg .= $this->translator->trans(
                        'has_been_'.$key,
                        [],
                        'platform'
                    );
                    $sessionFlashBag->add($msgClass, $msg);
                }
            }

            return new RedirectResponse($this->router->generate('claro_admin_users_index'));
        }

        return ['form' => $form->createView()];
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
     *     "/workspace/personal/resources/config",
     *     name="claro_admin_personal_workspace_resource_rights"
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration\Users:personalWorkspaceResourceRightsConfig.html.twig")
     *
     * @return array
     */
    public function personalWorkspaceResourceRightsConfigAction()
    {
        $roles = $this->roleManager->getAllPlatformRoles();
        $rights = $this->rightsManager->getAllPersonalWorkspaceRightsConfig();

        return [
            'roles' => $roles,
            'rights' => $rights,
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
     *     "/pws/rights/activate/{role}",
     *     name="claro_admin_pws_activate_rights_change",
     *     options={"expose"=true}
     * )
     */
    public function activatePersonalWorkspaceRightsAction(Role $role)
    {
        $this->rightsManager->activatePersonalWorkspaceRightsPerm($role);

        return new JsonResponse([], 200);
    }

    /**
     * @EXT\Route(
     *     "/pws/rights/deactivate/{role}",
     *     name="claro_admin_pws_deactivate_rights_change",
     *     options={"expose"=true}
     * )
     */
    public function deactivatePersonalWorkspaceRightsAction(Role $role)
    {
        $this->rightsManager->deactivatePersonalWorkspaceRightsPerm($role);

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
