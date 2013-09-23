<?php

namespace Claroline\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Translator;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Library\Configuration\UnwritableException;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Claroline\CoreBundle\Manager\MailManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\SecurityExtraBundle\Annotation as SEC;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ADMIN')")
 *
 * Controller of the platform administration section (users, groups,
 * workspaces, platform settings, etc.).
 */
class AdministrationController extends Controller
{
    private $userManager;
    private $roleManager;
    private $groupManager;
    private $workspaceManager;
    private $workspaceTagManager;
    private $security;
    private $eventDispatcher;
    private $configHandler;
    private $formFactory;
    private $analyticsManager;
    private $translator;
    private $request;
    private $mailManager;

    /**
     * @DI\InjectParams({
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"         = @DI\Inject("claroline.manager.role_manager"),
     *     "groupManager"        = @DI\Inject("claroline.manager.group_manager"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager" = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "security"            = @DI\Inject("security.context"),
     *     "eventDispatcher"     = @DI\Inject("claroline.event.event_dispatcher"),
     *     "configHandler"       = @DI\Inject("claroline.config.platform_config_handler"),
     *     "formFactory"         = @DI\Inject("claroline.form.factory"),
     *     "analyticsManager"    = @DI\Inject("claroline.manager.analytics_manager"),
     *     "translator"          = @DI\Inject("translator"),
     *     "request"             = @DI\Inject("request"),
     *     "mailManager"        = @DI\Inject("claroline.manager.mail_manager")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        GroupManager $groupManager,
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager,
        SecurityContextInterface $security,
        StrictDispatcher $eventDispatcher,
        PlatformConfigurationHandler $configHandler,
        FormFactory $formFactory,
        AnalyticsManager $analyticsManager,
        Translator $translator,
        Request $request,
        MailManager $mailManager
    )
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->groupManager = $groupManager;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
        $this->security = $security;
        $this->eventDispatcher = $eventDispatcher;
        $this->configHandler = $configHandler;
        $this->formFactory = $formFactory;
        $this->analyticsManager = $analyticsManager;
        $this->translator = $translator;
        $this->request = $request;
        $this->mailManager = $mailManager;
    }

    /**
     * @EXT\Template("ClarolineCoreBundle:Administration:index.html.twig")
     *
     * Displays the administration section index.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/user/form",
     *     name="claro_admin_user_creation_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     *
     * Displays the user creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function userCreationFormAction(User $currentUser)
    {
        $roles = $this->roleManager->getPlatformRoles($currentUser);
        $form = $this->formFactory->create(FormFactory::TYPE_USER, array($roles));
        if ($this->mailManager->isMailerAvailable()) {

            return array('form_complete_user' => $form->createView());

        }

        return array(
            'form_complete_user' => $form->createView(),
            'error' => 'Mail not available'
            );
    }

    /**
     * @EXT\Route(
     *     "/user",
     *     name="claro_admin_create_user"
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\Template("ClarolineCoreBundle:Administration:userCreationForm.html.twig")
     *
     * Creates an user (and its personal workspace) and redirects to the user list.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createUserAction(User $currentUser)
    {
        $roles = $this->roleManager->getPlatformRoles($currentUser);
        $form = $this->formFactory->create(FormFactory::TYPE_USER, array($roles));
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $user = $form->getData();
            $newRoles = $form->get('platformRoles')->getData();
            $this->userManager->insertUserWithRoles($user, $newRoles);

            if ($this->mailManager->isMailerAvailable()) {
                $body = $this->translator->trans('admin_form_username', array(), 'platform').': '.$user->getUsername(). $this->translator->trans('admin_form_plainPassword_first', array(), 'platform'). ': '.$user->getPlainPassword();

                if ($this->mailManager->sendPlainPassword('noreply@claroline.net', $user->getMail(), $body)) {
                    return $this->redirect($this->generateUrl('claro_admin_user_list'));

                }

                return $this->redirect($this->generateUrl('claro_admin_user_list'));

            }

            return $this->redirect($this->generateUrl('claro_admin_user_list'));

        }

        return array('form_complete_user' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/users",
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteUsersAction(array $users)
    {
        foreach ($users as $user) {
            $this->userManager->deleteUser($user);
            $this->eventDispatcher->dispatch('log', 'Log\LogUserDelete', array($user));
        }

        return new Response('user(s) removed', 204);
    }

    /**
     * @EXT\Route(
     *     "users/page/{page}",
     *     name="claro_admin_user_list",
     *     defaults={"page"=1, "search"=""},
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "users/page/{page}/search/{search}",
     *     name="claro_admin_user_list_search",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     * Displays the platform user list.
     */
    public function userListAction($page, $search)
    {
        $pager = $search === '' ?
            $this->userManager->getAllUsers($page) :
            $this->userManager->getUsersByName($search, $page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/groups/page/{page}",
     *     name="claro_admin_group_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "groups/page/{page}/search/{search}",
     *     name="claro_admin_group_list_search",
     *     defaults={"page"=1},
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     * Returns the platform group list.
     */
    public function groupListAction($page, $search)
    {
        $pager = $search === '' ?
            $this->groupManager->getGroups($page) :
            $this->groupManager->getGroupsByName($search, $page);

        return array('pager' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/group/{groupId}/users/page/{page}",
     *     name="claro_admin_user_of_group_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Route(
     *     "/group/{groupId}/users/page/{page}/search/{search}",
     *     name="claro_admin_user_of_group_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template()
     *
     * Returns the users of a group.
     */
    public function usersOfGroupListAction(Group $group, $page, $search)
    {
        $pager = $search === '' ?
            $this->userManager->getUsersByGroup($group, $page) :
            $this->userManager->getUsersByNameAndGroup($search, $group, $page);

        return array('pager' => $pager, 'search' => $search, 'group' => $group);
    }

    /**
     * @EXT\Route(
     *     "/group/add/{groupId}/page/{page}",
     *     name="claro_admin_outside_of_group_user_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"=""}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Route(
     *     "/group/add/{groupId}/page/{page}/search/{search}",
     *     name="claro_admin_outside_of_group_user_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template()
     *
     * Displays the user list with a control allowing to add them to a group.
     */
    public function outsideOfGroupUserListAction(Group $group, $page, $search)
    {
        $pager = $search === '' ?
            $this->userManager->getGroupOutsiders($group, $page) :
            $this->userManager->getGroupOutsidersByName($group, $search, $page);

        return array('pager' => $pager, 'search' => $search, 'group' => $group);
    }

    /**
     * @EXT\Route(
     *     "/group/form",
     *     name="claro_admin_group_creation_form"
     * )
     * @EXT\Method("GET")
     * @EXT\Template()
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupCreationFormAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP);

        return array('form_group' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/group",
     *     name="claro_admin_create_group"
     * )
     * @EXT\Method("POST")
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:groupCreationForm.html.twig")
     *
     * Creates a group and redirects to the group list.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createGroupAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP, array());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $group = $form->getData();
            $userRole = $this->roleManager->getRoleByName('ROLE_USER');
            $group->setPlatformRole($userRole);
            $this->groupManager->insertGroup($group);
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupCreate', array($group));

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return array('form_group' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/group/{groupId}/users",
     *     name="claro_admin_multiadd_user_to_group",
     *     requirements={"groupId"="^(?=.*[0-9].*$)\d*$"},
     *     options={"expose"=true}
     * )
     * @EXT\Method("PUT")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true}
     * )
     *
     * Adds multiple user to a group.
     *
     * @param integer $groupId
     *
     * @return Response
     */
    public function addUsersToGroupAction(Group $group, array $users)
    {
        $this->groupManager->addUsersToGroup($group, $users);

        foreach ($users as $user) {
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupAddUser', array($group, $user));
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/group/{groupId}/users",
     *     name="claro_admin_multidelete_user_from_group",
     *     options={"expose"=true},
     *     requirements={"groupId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true}
     * )
     *
     * Removes users from a group.
     *
     * @param integer $groupId
     *
     * @return Response
     */
    public function deleteUsersFromGroupAction(Group $group, array $users)
    {
        $this->groupManager->removeUsersFromGroup($group, $users);

        foreach ($users as $user) {
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupRemoveUser', array($group, $user));
        }

        return new Response('user removed', 204);
    }

    /**
     * @EXT\Route(
     *     "/groups",
     *     name="claro_admin_multidelete_group",
     *     options={"expose"=true}
     * )
     * @EXT\Method("DELETE")
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true}
     * )
     *
     * Deletes multiple groups.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteGroupsAction(array $groups)
    {
        foreach ($groups as $group) {
            $this->groupManager->deleteGroup($group);
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupDelete', array($group));
        }

        return new Response('groups removed', 204);
    }

    /**
     * @EXT\Route(
     *     "/group/settings/form/{groupId}",
     *     name="claro_admin_group_settings_form",
     *     requirements={"groupId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * Displays an edition form for a group.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function groupSettingsFormAction(Group $group)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP_SETTINGS, array(), $group);

        return array(
            'group' => $group,
            'form_settings' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "/group/settings/update/{groupId}",
     *     name="claro_admin_update_group_settings"
     * )
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:groupSettingsForm.html.twig")
     *
     * Updates the settings of a group and redirects to the group list.
     *
     * @param integer $groupId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateGroupSettingsAction(Group $group)
    {
        $oldPlatformRoleTransactionKey = $group->getPlatformRole()->getTranslationKey();

        $form = $this->formFactory->create(FormFactory::TYPE_GROUP_SETTINGS, array(), $group);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $group = $form->getData();
            $this->groupManager->updateGroup($group, $oldPlatformRoleTransactionKey);

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return array(
            'group' => $group,
            'form_settings' => $form->createView()
        );
    }

    /**
     * @EXT\Route(
     *     "/platform/settings/form",
     *     name="claro_admin_platform_settings_form"
     * )
     * @EXT\Route(
     *     "/",
     *     name="claro_admin_index",
     *     options={"expose"=true}
     * )
     *
     * @EXT\Template()
     *
     * Displays the platform settings.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function platformSettingsFormAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            FormFactory::TYPE_PLATFORM_PARAMETERS,
            array($this->getThemes()),
            $platformConfig
        );

        return array('form_settings' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "claro_admin_update_platform_settings",
     *     name="claro_admin_update_platform_settings"
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:platformSettingsForm.html.twig")
     *
     * Updates the platform settings and redirects to the settings form.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updatePlatformSettingsAction()
    {
        $platformConfig = $this->configHandler->getPlatformConfig();
        $form = $this->formFactory->create(
            FormFactory::TYPE_PLATFORM_PARAMETERS,
            array($this->getThemes()),
            $platformConfig
        );
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            try {
                $this->configHandler->setParameter(
                    'allow_self_registration',
                    $form['selfRegistration'] ->getData()
                );
                $this->configHandler->setParameter(
                    'locale_language',
                    $form['localLanguage']->getData()
                );
                $this->configHandler->setParameter(
                    'theme',
                    $form['theme']->getData()
                );
            } catch (UnwritableException $e) {
                $form->addError(
                    new FormError(
                        $this->translator->trans(
                            'unwritable_file_exception',
                            array('%path%' => $e->getPath()),
                            'platform'
                        )
                    )
                );

                return array('form_settings' => $form->createView());
            }
        }

        return $this->redirect($this->generateUrl('claro_admin_platform_settings_form'));
    }

    /**
     * @EXT\Route(
     *     "plugins",
     *     name="claro_admin_plugins"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Display the plugin list
     *
     * @return Response
     */
    public function pluginListAction()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $plugins = $em->getRepository('ClarolineCoreBundle:Plugin')->findAll();

        return array('plugins' => $plugins);
    }

    /**
     * @EXT\Route(
     *     "/plugin/{domain}/options",
     *     name="claro_admin_plugin_options"
     * )
     * @EXT\Method("GET")
     *
     * Redirects to the plugin mangagement page.
     *
     * @param string $domain
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function pluginParametersAction($domain)
    {
        $eventName = "plugin_options_{$domain}";
        $event = $this->eventDispatcher->dispatch($eventName, 'PluginOptions', array());

        return $event->getResponse();
    }

    /**
     * @EXT\Route(
     *    "user/management",
     *    name="claro_admin_users_management"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * @return Response
     */
    public function usersManagementAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *    "user/management/import/form",
     *     name="claro_admin_import_users_form"
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * @return Response
     */
    public function importUsersFormAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_USER_IMPORT);

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "user/management/import",
     *     name="claro_admin_import_users"
     * )
     *
     * @EXT\Method({"POST", "GET"})
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:importUsersForm.html.twig")
     *
     * @return Response
     */
    public function importUsers()
    {
        $validFile = true;
        $form = $this->formFactory->create(FormFactory::TYPE_USER_IMPORT);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $lines = str_getcsv(file_get_contents($file), PHP_EOL, ',');

            foreach ($lines as $line) {
                $users[] = str_getcsv($line);
            }

            if ($validFile) {
                $nonImportedUsers = $this->userManager->importUsers($users);

                if (count($nonImportedUsers) > 0) {
                    foreach ($nonImportedUsers as $nonImportedUser) {
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            $nonImportedUser['firstName'] . ' ' .
                            $nonImportedUser['lastName'] . ' [' .
                            $nonImportedUser['username'] . '] ' .
                            $this->translator->trans('has_not_been_imported', array(), 'platform')
                        );
                    }
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        $this->translator->trans('users_minimum_requirement_msg', array(), 'platform')
                    );
                    return array('form' => $form->createView());
                }
                return $this->redirect($this->generateUrl('claro_admin_user_list'));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @EXT\Route(
     *    "group/{groupId}/management/import/form",
     *     name="claro_admin_import_users_into_group_form"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     *
     * @EXT\Template()
     *
     * @return Response
     */
    public function importUsersIntoGroupFormAction(Group $group)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_USER_IMPORT);

        return array('form' => $form->createView(), 'group' => $group);
    }

    /**
     * @EXT\Route(
     *    "group/{groupId}/management/import",
     *     name="claro_admin_import_users_into_group"
     * )
     * @EXT\Method({"POST", "GET"})
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:importUsersIntoGroupForm.html.twig")
     *
     * @return Response
     */
    public function importUsersIntoGroupAction(Group $group)
    {
        $validFile = true;
        $form = $this->formFactory->create(FormFactory::TYPE_USER_IMPORT);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $lines = str_getcsv(file_get_contents($file), PHP_EOL, ',');

            foreach ($lines as $line) {
                $linesTab = explode(',', $line);
                $nbElements = count($linesTab);

                if ($nbElements < 5) {
                    $validFile = false;
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        $this->translator->trans('invalid_csv_file', array(), 'platform')
                    );
                    break;
                }
                $users[] = str_getcsv($line);
            }

            if ($validFile) {
                $this->userManager->importUsers($users);
                $nonImportedUsers = $this->groupManager->importUsers($group, $users);

                foreach ($nonImportedUsers as $nonImportedUser) {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        $nonImportedUser['firstName'] . ' ' .
                        $nonImportedUser['lastName'] . ' [' .
                        $nonImportedUser['username'] . '] ' .
                        $this->translator->trans('has_not_been_imported_into_the_group', array(), 'platform')
                    );
                }

                return $this->redirect(
                    $this->generateUrl(
                        'claro_admin_user_of_group_list',
                        array('groupId' => $group->getId())
                    )
                );
            }
        }

        return array('form' => $form->createView(), 'group' => $group);
    }

    /**
     *  Get the list of themes availables.
     *  @TODO use directory iterator
     *
     *  @return array with a list of the themes availables.
     */
    private function getThemes()
    {
        $tmp = array();

        foreach ($this->get('claroline.common.theme_service')->getThemes() as $theme) {
            $tmp[str_replace(' ', '-', strtolower($theme->getName()))] = $theme->getName();
        }

        return $tmp;
    }

    /**
     * @EXT\Route(
     *     "/logs/",
     *     name="claro_admin_logs_show",
     *     defaults={"page" = 1}
     * )
     * @EXT\Route(
     *     "/logs/{page}",
     *     name="claro_admin_logs_show_paginated",
     *     requirements={"page" = "\d+"},
     *     defaults={"page" = 1}
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Displays logs list using filter parameteres and page number
     *
     * @param $page int The requested page number.
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function logListAction($page)
    {
        return $this->get('claroline.log.manager')->getAdminList($page);
    }

    /**
     * @EXT\Route(
     *     "/analytics/",
     *     name="claro_admin_analytics_show"
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:analytics.html.twig")
     *
     * Displays platform analytics home page
     *
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsAction()
    {
        $lastMonthActions = $this->analyticsManager->getDailyActionNumberForDateRange();
        $mostViewedWS = $this->analyticsManager->topWSByAction(null, 'ws_tool_read', 5);
        $mostViewedMedia = $this->analyticsManager->topMediaByAction(null, 'resource_read', 5);
        $mostDownloadedResources = $this->analyticsManager->topResourcesByAction(null, 'resource_export', 5);
        $usersCount = $this->userManager->getNbUsers();

        return array(
            'barChartData' => $lastMonthActions,
            'usersCount' => $usersCount,
            'mostViewedWS' => $mostViewedWS,
            'mostViewedMedia' => $mostViewedMedia,
            'mostDownloadedResources' => $mostDownloadedResources
        );
    }

    /**
     * @EXT\Route(
     *     "/analytics/connections",
     *     name="claro_admin_analytics_connections"
     * )
     *
     * @EXT\Method({"GET", "POST"})
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:analytics_connections.html.twig")
     *
     * Displays platform analytics connections page
     *
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsConnectionsAction()
    {
        $criteriaForm = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_ANALYTICS_CONNECTIONS,
            array(),
            array(
                "range"=>$this->analyticsManager->getDefaultRange(),
                "unique"=>"false"
            )
        );
        
        $criteriaForm->handleRequest($this->request);
        $unique = false;
        if ($criteriaForm->isValid()) {
            $range = $criteriaForm->get('range')->getData();
            $unique = $criteriaForm->get('unique')->getData() === 'true';
        }
        $actionsForRange = $this->analyticsManager
            ->getDailyActionNumberForDateRange($range, 'user_login', $unique);
        
        $connections = $actionsForRange;
        $activeUsers = $this->analyticsManager->getActiveUsers();

        return array(
            'connections' => $connections,
            'form_criteria' => $criteriaForm->createView(),
            'activeUsers' => $activeUsers
        );
    }

    /**
     * @EXT\Route(
     *     "/analytics/resources",
     *     name="claro_admin_analytics_resources"
     * )
     *
     * @EXT\Method("GET")
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:analytics_resources.html.twig")
     *
     * Displays platform analytics resources page
     *
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsResourcesAction()
    {
        $manager = $this->get('doctrine.orm.entity_manager');
        $wsCount = $this->workspaceManager->getNbWorkspaces();
        $resourceCount = $manager->getRepository('ClarolineCoreBundle:Resource\ResourceType')
            ->countResourcesByType();

        return array(
            'wsCount' => $wsCount,
            'resourceCount' => $resourceCount
        );
    }

    /**
     * @EXT\Route(
     *     "/analytics/top/{top_type}",
     *     name="claro_admin_analytics_top",
     *     defaults={"top_type" = "top_users_connections"}
     * )
     *
     * @EXT\Method({"GET", "POST"})
     *
     * @EXT\Template("ClarolineCoreBundle:Administration:analytics_top.html.twig")
     *
     * Displays platform analytics top activity page
     *
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function analyticsTopAction($top_type)
    {
        $criteriaForm = $this->formFactory->create(
            FormFactory::TYPE_ADMIN_ANALYTICS_TOP,
            array(),
            array(
                "top_type"=>$top_type,
                "top_number"=>30,
                "range"=>$this->analyticsManager->getDefaultRange()
            )
        );

        $criteriaForm->handleRequest($this->request);

        $range = $criteriaForm->get('range')->getData();
        $topType = $criteriaForm->get('top_type')->getData();
        $max = $criteriaForm->get('top_number')->getData();
        $listData = $this->analyticsManager->getTopByCriteria($range, $topType, $max);

        return array(
            'form_criteria' => $criteriaForm->createView(),
            'list_data' => $listData
        );
    }

    /**
     * @EXT\Route(
     *    "registration/management",
     *    name="claro_admin_registration_management",
     *    defaults={"search"=""},
     *    options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "registration/management/search/{search}",
     *     name="claro_admin_registration_management_search",
     *     options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * @return Response
     */
    public function registrationManagementAction($search)
    {
        if ($search === '') {
            $datas = $this->workspaceTagManager->getDatasForWorkspaceList(false);

            return array(
                'workspaces' => $datas['workspaces'],
                'tags' => $datas['tags'],
                'tagWorkspaces' => $datas['tagWorkspaces'],
                'hierarchy' => $datas['hierarchy'],
                'rootTags' => $datas['rootTags'],
                'displayable' => $datas['displayable'],
                'search' => ''
            );
        }
        $pager = $this->workspaceManager->getDisplayableWorkspacesBySearchPager($search, 1);

        return array('workspaces' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *    "registration/management/users",
     *    name="claro_admin_registration_management_users",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"multipleIds" = true}
     * )
     *
     * @EXT\Template()
     *
     * @return Response
     */
    public function registrationManagementUserListAction(array $workspaces)
    {
        $pager = $this->userManager->getAllUsers(1);

        return array('workspaces' => $workspaces, 'users' => $pager, 'search' => '');
    }

    /**
     * @EXT\Route(
     *    "registration/management/groups",
     *    name="claro_admin_registration_management_groups",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"multipleIds" = true}
     * )
     *
     * @EXT\Template()
     *
     * @return Response
     */
    public function registrationManagementGroupListAction(array $workspaces)
    {
        $pager = $this->groupManager->getGroups(1);

        return array('workspaces' => $workspaces, 'groups' => $pager, 'search' => '');
    }

    /**
     * @EXT\Route(
     *     "/registration/list/users/page/{page}",
     *     name="claro_users_list_registration_pager",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/registration/list/users/page/{page}/search/{search}",
     *     name="claro_users_list_registration_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the user list in a pager for registration.
     *
     * @return Response
     */
    public function userListPagerAction($page, $search)
    {
        $pager = $search === '' ?
            $this->userManager->getAllUsers($page) :
            $this->userManager->getUsersByName($search, $page);

        return array('users' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *     "/registration/list/groups/page/{page}",
     *     name="claro_groups_list_registration_pager",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     * @EXT\Route(
     *     "/registration/list/groups/page/{page}/search/{search}",
     *     name="claro_groups_list_registration_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Method("GET")
     *
     * @EXT\Template()
     *
     * Renders the group list in a pager for registration.
     *
     * @return Response
     */
    public function groupListPagerAction($page, $search)
    {
        $pager = $search === '' ?
            $this->groupManager->getGroups($page) :
            $this->groupManager->getGroupsByName($search, $page);

        return array('groups' => $pager, 'search' => $search);
    }

    /**
     * @EXT\Route(
     *    "registration/management/workspaces/users/{roleKey}",
     *    name="claro_admin_subscribe_users_to_workspaces",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"multipleIds" = true, "name" = "workspaceIds"}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     */
    public function subscribeMultipleUsersToMultipleWorkspacesAction(
        $roleKey,
        array $workspaces,
        array $users
    )
    {
        foreach ($workspaces as $workspace) {
            $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace($roleKey, $workspace);

            if (!is_null($role)) {
                $this->roleManager->associateRoleToMultipleSubjects($users, $role);
            }
        }

        $msg = '';

        foreach ($users as $user) {
            $msg .= $user->getFirstName() . ' ' . $user->getLastName() . ' ';
            $msg .= $this->translator->trans(
                'has_been_suscribed_with_role',
                array(),
                'platform'
            );
            $msg .= ' "' .
                $this->translator->trans(
                    $roleKey,
                    array(),
                    'platform'
                ) .
                '"-;-';
        }

        return new Response($msg, 200);
    }

    /**
     * @EXT\Route(
     *    "registration/management/workspaces/groups/{roleKey}",
     *    name="claro_admin_subscribe_groups_to_workspaces",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\AbstractWorkspace",
     *      options={"multipleIds" = true, "name" = "workspaceIds"}
     * )
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     */
    public function subscribeMultipleGroupsToMultipleWorkspacesAction(
        $roleKey,
        array $workspaces,
        array $groups
    )
    {
        foreach ($workspaces as $workspace) {
            $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace($roleKey, $workspace);

            if (!is_null($role)) {
                $this->roleManager->associateRoleToMultipleSubjects($groups, $role);
            }
        }

        $msg = '';

        foreach ($groups as $group) {
            $msg .= '"' . $group->getName() . '" ';
            $msg .= $this->translator->trans(
                'has_been_suscribed_with_role_group',
                array(),
                'platform'
            );
            $msg .= ' "' .
                $this->translator->trans(
                    $roleKey,
                    array(),
                    'platform'
                ) .
                '"-;-';
        }

        return new Response($msg, 200);
    }

    /**
     * @EXT\Route(
     *    "registration/management/workspaces/roles/users",
     *    name="claro_admin_subscribe_users_to_one_workspace",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "roles",
     *      class="ClarolineCoreBundle:Role",
     *      options={"multipleIds" = true, "name" = "roleIds"}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     */
    public function subscribeMultipleUsersToOneWorkspaceAction(
        array $roles,
        array $users
    )
    {
        $msg = '';

        foreach ($users as $user) {
            foreach ($roles as $role) {
                $this->roleManager->associateRole($user, $role);
                $msg .= $user->getFirstName() . ' ' . $user->getLastName() . ' ';
                $msg .= $this->translator->trans(
                    'has_been_suscribed_with_role',
                    array(),
                    'platform'
                );
                $msg .= ' "' .
                    $this->translator->trans(
                        $role->getTranslationKey(),
                        array(),
                        'platform'
                    ) .
                    '"-;-';
            }
        }

        return new Response($msg, 200);
    }

    /**
     * @EXT\Route(
     *    "registration/management/workspaces/roles/groups",
     *    name="claro_admin_subscribe_groups_to_one_workspace",
     *    options = {"expose"=true}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *     "roles",
     *      class="ClarolineCoreBundle:Role",
     *      options={"multipleIds" = true, "name" = "roleIds"}
     * )
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     */
    public function subscribeMultipleGroupsToOneWorkspaceAction(
        array $roles,
        array $groups
    )
    {
        $msg = '';

        foreach ($groups as $group) {
            foreach ($roles as $role) {
                $this->roleManager->associateRole($group, $role);
                $msg .= '"' . $group->getName() . '" ';
                $msg .= $this->translator->trans(
                    'has_been_suscribed_with_role_group',
                    array(),
                    'platform'
                );
                $msg .= ' "' .
                    $this->translator->trans(
                        $role->getTranslationKey(),
                        array(),
                        'platform'
                    ) .
                    '"-;-';
            }
        }

        return new Response($msg, 200);
    }
}
