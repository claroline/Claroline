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

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Administration\ProfilePicsImportType;
use Claroline\CoreBundle\Form\ImportUserType;
use Claroline\CoreBundle\Form\ProfileCreationType;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\AuthenticationManager;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

    /**
     * @DI\InjectParams({
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager"),
     *     "configHandler"          = @DI\Inject("claroline.config.platform_config_handler"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"            = @DI\Inject("form.factory"),
     *     "localeManager"          = @DI\Inject("claroline.common.locale_manager"),
     *     "mailManager"            = @DI\Inject("claroline.manager.mail_manager"),
     *     "request"                = @DI\Inject("request"),
     *     "rightsManager"          = @DI\Inject("claroline.manager.rights_manager"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "router"                 = @DI\Inject("router"),
     *     "authorization"          = @DI\Inject("security.authorization_checker"),
     *     "session"                = @DI\Inject("session"),
     *     "toolManager"            = @DI\Inject("claroline.manager.tool_manager"),
     *     "toolMaskDecoderManager" = @DI\Inject("claroline.manager.tool_mask_decoder_manager"),
     *     "translator"             = @DI\Inject("translator"),
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"       = @DI\Inject("claroline.manager.workspace_manager")
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
        SessionInterface $session,
        StrictDispatcher $eventDispatcher,
        ToolManager $toolManager,
        ToolMaskDecoderManager $toolMaskDecoderManager,
        TranslatorInterface $translator,
        UserManager $userManager,
        WorkspaceManager $workspaceManager
    )
    {
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
    }

    /**
     * @EXT\Route("/menu", name="claro_admin_users_management")
     * @EXT\Template
     *
     * @return Response
     */
    public function indexAction()
    {
        $canUserBeCreated = $this->roleManager->validateRoleInsert(
            new User(),
            $this->roleManager->getRoleByName('ROLE_USER')
        );

        return array('canUserBeCreated' => $canUserBeCreated);
    }

    /**
     * @EXT\Route("/new", name="claro_admin_user_creation_form")
     * @EXT\Template
     *
     * Displays the user creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userCreationFormAction()
    {
        $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
        $isAdmin = ($this->authorization->isGranted('ROLE_ADMIN')) ? true : false;
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
            array($roleUser),
            $isAdmin,
            $this->authenticationManager->getDrivers()
        );
        $form = $this->formFactory->create($profileType);

        $error = null;

        if (!$this->mailManager->isMailerAvailable()) {
            $error = 'mail_not_available';
        }

        return array(
            'form_complete_user' => $form->createView(),
            'error' => $error,
            'unavailableRoles' => $unavailableRoles
        );
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
        $isAdmin = ($this->authorization->isGranted('ROLE_ADMIN')) ? true : false;

        $profileType = new ProfileCreationType(
            $this->localeManager,
            array($roleUser),
            $isAdmin,
            $this->authenticationManager->getDrivers()
        );
        $form = $this->formFactory->create($profileType);
        $form->handleRequest($this->request);

        $unavailableRoles = [];

        foreach ($form->get('platformRoles')->getData() as $role) {
            $isAvailable = $this->roleManager->validateRoleInsert(new User(), $role);

            if (!$isAvailable) {
                $unavailableRoles[] = $role;
            }
        }

        $isAvailable = $this->roleManager->validateRoleInsert(new User(), $roleUser);

        if (!$isAvailable) {
            $unavailableRoles[] = $roleUser;
        }

        $unavailableRoles = array_unique($unavailableRoles);

        if ($form->isValid() && count($unavailableRoles) === 0) {
            $user = $form->getData();
            $newRoles = $form->get('platformRoles')->getData();
            $this->userManager->createUser($user, true, $newRoles);
            $sessionFlashBag->add(
                'success',
                $this->translator->trans('user_creation_success', array(), 'platform')
            );

            return $this->redirect($this->generateUrl('claro_admin_user_list'));
        }

        $error = null;

        if (!$this->mailManager->isMailerAvailable()) {
            $error = 'mail_not_available';
        }

        return array(
            'form_complete_user' => $form->createView(),
            'error' => $error,
            'unavailableRoles' => $unavailableRoles,
        );
    }

    /**
     * @EXT\Route(
     *     "/",
     *     name="claro_admin_multidelete_user",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true}
     * )
     *
     * Removes many users from the platform.
     *
     * @param User[] $users
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(array $users)
    {
        foreach ($users as $user) {
            if (!$this->authorization->isGranted('ROLE_ADMIN') && $user->hasRole('ROLE_ADMIN')) {
                throw new AccessDeniedException();
            }

            $this->userManager->deleteUser($user);
            $this->eventDispatcher->dispatch('log', 'Log\LogUserDelete', array($user));
        }

        return new Response('user(s) removed', 204);
    }

    /**
     * @EXT\Route(
     *     "/page/{page}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_admin_user_list",
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id","direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/users/page/{page}/search/{search}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_admin_user_list_search",
     *     defaults={"page"=1, "max"=50, "order"="id","direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Template
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\User",
     *     options={"orderable"=true}
     * )
     *
     * Displays the platform user list.
     *
     * @param integer $page
     * @param string  $search
     * @param integer $max
     * @param string  $order
     * @param string  $direction
     *
     * @return array
     */
    public function listAction($page, $search, $max, $order, $direction)
    {
        $canUserBeCreated = $this->roleManager->validateRoleInsert(new User(),$this->roleManager->getRoleByName('ROLE_USER'));
        $pager = $search === '' ?
            $this->userManager->getAllUsers($page, $max, $order, $direction) :
            $this->userManager->getUsersByName($search, $page, $max, $order, $direction);

        return array(
            'canUserBeCreated' => $canUserBeCreated,
            'pager' => $pager,
            'search' => $search,
            'max' => $max,
            'order' => $order,
            'direction' => $direction,
        );
    }

    /**
     * @EXT\Route(
     *     "/page/{page}/pic",
     *     name="claro_admin_user_list_pics",
     *     defaults={"page"=1, "search"=""},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/page/{page}/pic/search/{search}",
     *     name="claro_admin_user_list_search_pics",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     * @EXT\Template
     *
     * Displays the platform user list.
     *
     * @param integer $page
     * @param string  $search
     *
     * @return array
     */
    public function listPicsAction($page, $search)
    {
        $pager = $search === '' ?
            $this->userManager->getAllUsers($page) :
            $this->userManager->getUsersByName($search, $page);

        return array('pager' => $pager, 'search' => $search);
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

        return array('form' => $form->createView(), 'error' => null);
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
     * @param User    $user
     * @param integer $page
     * @param integer $max
     *
     * @return array
     */
    public function userWorkspaceListAction(User $user, $page, $max)
    {
        $pager = $this->workspaceManager->getOpenableWorkspacesByRolesPager($user->getRoles(), $page, $max);

        return array('user' => $user, 'pager' => $pager, 'page' => $page, 'max' => $max);
    }

    /**
     * @EXT\Route("/import/submit", name="claro_admin_import_users")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration/Users:importForm.html.twig")
     *
     * @return Response
     */
    public function importAction()
    {
        $form = $this->formFactory->create(new ImportUserType(true));
        $form->handleRequest($this->request);
        $mode = $form->get('mode')->getData();

        if ($mode === 'update') {
            $form = $this->formFactory->create(new ImportUserType(true, 1));
            $form->handleRequest($this->request);
        }

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $sendMail = $form->get('sendMail')->getData();
            $lines = str_getcsv(file_get_contents($file), PHP_EOL);
            $users = array();
            $toUpdate = array();
            $sessionFlashBag = $this->session->getFlashBag();

            foreach ($lines as $line) {

                if ($mode === 'update') {
                    $datas = str_getcsv($line, ';');
                    $username = $datas[2];
                    $email = $datas[4];
                    $existingUser = $this->userManager->getUserByUsernameOrMail(
                        $username,
                        $email
                    );

                    if (is_null($existingUser)) {
                        $users[] = $datas;
                    } else {
                        $toUpdate[] = $datas;
                    }
                } else {
                    $users[] = str_getcsv($line, ';');
                }
            }

            $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
            $max = $roleUser->getMaxUsers();
            $total = $this->userManager->countUsersByRoleIncludingGroup($roleUser);

            if ($total + count($users) > $max) {

                return array('form' => $form->createView(), 'error' => 'role_user unavailable');
            }

            $additionalRoles = $form->get('roles')->getData();

            foreach ($additionalRoles as $additionalRole) {
                $max = $additionalRole->getMaxUsers();
                $total = $this->userManager->countUsersByRoleIncludingGroup($additionalRole);

                if ($total + count($users) > $max) {

                    return array(
                        'form' => $form->createView(),
                        'error' => $additionalRole->getName() . ' unavailable'
                    );
                }
            }

            if (count($toUpdate) > 0) {
                $updatedNames = $this->userManager->updateImportedUsers(
                    $toUpdate,
                    $additionalRoles->toArray()
                );

                foreach ($updatedNames as $name) {
                    $msg =  '<' . $name . '> ';
                    $msg .= $this->translator->trans(
                        'has_been_updated',
                        array(),
                        'platform'
                    );
                    $sessionFlashBag->add('success', $msg);
                }
            }
            $createdNames = $this->userManager->importUsers(
                $users,
                $sendMail,
                null,
                $additionalRoles
            );

            foreach ($createdNames as $name) {
                $msg =  '<' . $name . '> ';
                $msg .= $this->translator->trans(
                    'has_been_created',
                    array(),
                    'platform'
                );
                $sessionFlashBag->add('success', $msg);
            }

            return new RedirectResponse($this->router->generate('claro_admin_user_list'));
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route("/export/users/{format}", name="claro_admin_export_users")
     *
     * @return Response
     */
    public function export($format)
    {
        $exporter = $this->container->get('claroline.exporter.' . $format);
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
        $response->headers->set('Content-Disposition', 'attachment; filename=users.' . $format);

        switch ($format) {
            case 'csv': $response->headers->set('Content-Type', 'text/csv'); break;
            case 'xls': $response->headers->set('Content-Type', 'application/vnd.ms-excel'); break;
        }

        $response->headers->set('Connection', 'close');

        return $response;
    }

    /**
     * @EXT\Route("/user/workspace/index", name="claro_admin_user_personal_workspace_index"),
     *
     * @EXT\Template("ClarolineCoreBundle:Administration/Users:personnalWorkspaceIndex.html.twig")
     */
    public function personalWorkspaceIndexAction()
    {
        return array();
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

        return array(
            'personalWsToolConfigs' => $personalWsToolConfigs,
            'roles'                 => $roles,
            'tools'                 => $tools,
            'maskDecoders'          => $maskDecoders
        );
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

        return array(
            'roles' => $roles,
            'rights' => $rights
        );
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

        return new JsonResponse(array(), 200);
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

        return new JsonResponse(array(), 200);
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

        return new JsonResponse(array(), 200);
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

        return new JsonResponse(array(), 200);
    }

    /**
     * @EXT\Route(
     *     "/pws/create/{user}",
     *     name="claro_admin_pws_create",
     *     options={"expose"=true}
     * )
     */
    public function createPersonalWorkspace(User $user)
    {
        if (!$user->getPersonalWorkspace()) {
            $this->userManager->setPersonalWorkspace($user);
        } else {
            throw new \Exception('Workspace already exists');
        }

        return new JsonResponse(array(), 200);

    }

    /**
     * @EXT\Route(
     *     "/pws/delete/{user}",
     *     name="claro_admin_pws_delete",
     *     options={"expose"=true}
     * )
     */
    public function deletePersonalWorkspace(User $user)
    {
        $personalWorkspace = $user->getPersonalWorkspace();
        $this->eventDispatcher->dispatch('log', 'Log\LogWorkspaceDelete', array($personalWorkspace));
        $this->workspaceManager->deleteWorkspace($personalWorkspace);

        return new JsonResponse(array(), 200);
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

        return array('form' => $form->createView());
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

        return array('form' => $form->createView());
    }
}
