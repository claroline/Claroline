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

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\StrictDispatcher;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\AuthenticationManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('user_management')")
 */
class GroupsController extends Controller
{
    private $userManager;
    private $roleManager;
    private $groupManager;
    private $eventDispatcher;
    private $formFactory;
    private $request;
    private $router;
    private $userAdminTool;
    private $translator;
    private $authenticationManager;

    /**
     * @DI\InjectParams({
     *     "userManager"            = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"            = @DI\Inject("claroline.manager.role_manager"),
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager"),
     *     "eventDispatcher"        = @DI\Inject("claroline.event.event_dispatcher"),
     *     "formFactory"            = @DI\Inject("claroline.form.factory"),
     *     "request"                = @DI\Inject("request"),
     *     "router"                 = @DI\Inject("router"),
     *     "translator"             = @DI\Inject("translator"),
     *     "authenticationManager"  = @DI\Inject("claroline.common.authentication_manager")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        GroupManager $groupManager,
        StrictDispatcher $eventDispatcher,
        FormFactory $formFactory,
        Request $request,
        RouterInterface $router,
        TranslatorInterface $translator,
        AuthenticationManager $authenticationManager
    )
    {
        $this->userManager = $userManager;
        $this->roleManager = $roleManager;
        $this->groupManager = $groupManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->request = $request;
        $this->router = $router;
        $this->translator = $translator;
        $this->authenticationManager = $authenticationManager;
    }

    /**
     * @EXT\Route("/new", name="claro_admin_group_creation_form")
     * @EXT\Template
     *
     * Displays the group creation form.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function creationFormAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP);

        return array('form_group' => $form->createView());
    }

    /**
     * @EXT\Route("/new/submit", name="claro_admin_create_group")
     * @EXT\Method("POST")
     * @EXT\Template("ClarolineCoreBundle:Administration/Groups:creationForm.html.twig")
     *
     * Creates a group and redirects to the group list.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction()
    {
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP, array());
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $group = $form->getData();
            $userRole = $this->roleManager->getRoleByName('ROLE_USER');
            $group->setPlatformRole($userRole);
            $this->groupManager->insertGroup($group);

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return array('form_group' => $form->createView());
    }

    /**
     * @EXT\Route(
     *     "/page/{page}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_admin_group_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id", "direction"="ASC"}
     * )
     * @EXT\Route(
     *     "/page/{page}/search/{search}/max/{max}/order/{order}/direction/{direction}",
     *     name="claro_admin_group_list_search",
     *     defaults={"page"=1, "max"=50, "order"="id", "direction"="ASC"},
     *     options = {"expose"=true}
     * )
     * @EXT\Template()
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\Group",
     *     options={"orderable"=true}
     * )
     *
     * Returns the platform group list.
     *
     * @param integer $page
     * @param string $search
     * @param integer $max
     * @param string $order
     *
     * @param $direction
     * @return array
     */
    public function listAction($page, $search, $max, $order, $direction)
    {
        $pager = $search === '' ?
            $this->groupManager->getGroups($page, $max, $order, $direction) :
            $this->groupManager->getGroupsByName($search, $page, $max, $order, $direction);

        return array('pager' => $pager, 'search' => $search, 'max' => $max, 'order' => $order, 'direction' => $direction);
    }

    /**
     * @EXT\Route(
     *     "/index",
     *     name="claro_admin_group_index",
     *     options={"expose"=true},
     * )
     * @EXT\Template()
     *
     * @return array
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @EXT\Route(
     *     "/",
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
     * @param Group[] $groups
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(array $groups)
    {
        foreach ($groups as $group) {
            $this->groupManager->deleteGroup($group);
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupDelete', array($group));
        }

        return new Response('groups removed', 204);
    }

    /**
     * @EXT\Route(
     *     "/{groupId}/users/page/{page}/max/{max}/order/{order}",
     *     name="claro_admin_user_of_group_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id"}
     * )
     * @EXT\Route(
     *     "/{groupId}/users/page/{page}/search/{search}/max/{max}/{order}",
     *     name="claro_admin_user_of_group_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1, "max"=50, "order"="id"}
     * )
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\User",
     *     options={"orderable"=true}
     * )
     *
     * Returns the users of a group.
     *
     * @param Group   $group
     * @param integer $page
     * @param string  $search
     * @param integer $max
     * @param string  $order
     *
     * @return array
     */
    public function listMembersAction(Group $group, $page, $search, $max, $order)
    {
        $pager = $search === '' ?
            $this->userManager->getUsersByGroup($group, $page, $max, $order) :
            $this->userManager->getUsersByNameAndGroup($search, $group, $page, $max, $order);

        return array('pager' => $pager, 'search' => $search, 'group' => $group, 'max' => $max, 'order' => $order);
    }

    /**
     * @EXT\Route(
     *     "/{groupId}/add-users/page/{page}/max/{max}/order/{order}",
     *     name="claro_admin_outside_of_group_user_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"="", "max"=50, "order"="id"}
     * )
     * @EXT\Route(
     *     "/{groupId}/add-users/page/{page}/search/{search}/max/{max}/order/{order}",
     *     name="claro_admin_outside_of_group_user_list_search",
     *     options={"expose"=true},
     *     defaults={"page"=1, "max"=50, "order"="id"}
     * )
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template
     * @EXT\ParamConverter(
     *     "order",
     *     class="Claroline\CoreBundle\Entity\User",
     *     options={"orderable"=true}
     * )
     *
     * Displays the user list with a control allowing to add them to a group.
     *
     * @param Group   $group
     * @param integer $page
     * @param string  $search
     * @param integer $max
     * @param string  $order
     *
     * @return array
     */
    public function listNonMembersAction(Group $group, $page, $search, $max, $order)
    {
        $pager = $search === '' ?
            $this->userManager->getGroupOutsiders($group, $page, $max, $order) :
            $this->userManager->getGroupOutsidersByName($group, $page, $search, $max, $order);

        return array('pager' => $pager, 'search' => $search, 'group' => $group, 'max' => $max, 'order' => $order);
    }

    /**
     * @EXT\Route(
     *     "/{groupId}/users",
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
     * Adds multiple users to a group.
     *
     * @param Group     $group
     * @param User[]    $users
     *
     * @return Response
     */
    public function addMembersAction(Group $group, array $users)
    {
        if (!$this->groupManager->validateAddUsersToGroup($users, $group)) {
            return new Response($this->translator->trans('fail_add_user_group_message', array(), 'platform'), 500);
        }

        $this->groupManager->addUsersToGroup($group, $users);

        foreach ($users as $user) {
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupAddUser', array($group, $user));
        }

        return new Response('success', 204);
    }

    /**
     * @EXT\Route(
     *     "/{groupId}/users",
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
     * @param Group  $group
     * @param User[] $users
     *
     * @return Response
     */
    public function removeMembersAction(Group $group, array $users)
    {
        $this->groupManager->removeUsersFromGroup($group, $users);

        foreach ($users as $user) {
            $this->eventDispatcher->dispatch('log', 'Log\LogGroupRemoveUser', array($group, $user));
        }

        return new Response('User(s) removed', 204);
    }

    /**
     * @EXT\Route(
     *     "/{groupId}",
     *     name="claro_admin_group_settings_form",
     *     requirements={"groupId"="^(?=.*[1-9].*$)\d*$"}
     * )
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template
     *
     * Displays an edition form for a group.
     *
     * @param Group $group
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function settingsFormAction(Group $group)
    {
        $roles = $group->getPlatformRole();
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP_SETTINGS, array('isAdmin' => true, 'roles' => $roles), $group);
        $unavailableRoles = [];
        $roles = $this->roleManager->getAllPlatformRoles();

        foreach ($roles as $role) {
            $isAvailable = $this->roleManager->validateRoleInsert($group, $role);
            if (!$isAvailable) {
                $unavailableRoles[] = $role;
            }
        }

        return array(
            'group' => $group,
            'form_settings' => $form->createView(),
            'unavailableRoles' => $unavailableRoles
        );
    }

    /**
     * @EXT\Route("/{groupId}/update", name="claro_admin_update_group_settings")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration/Groups:settingsForm.html.twig")
     *
     * Updates the settings of a group and redirects to the group list.
     *
     * @param Group $group
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateSettingsAction(Group $group)
    {
        $oldRoles = $group->getPlatformRoles();
        $form = $this->formFactory->create(FormFactory::TYPE_GROUP_SETTINGS, array('isAdmin' => true, 'roles' => $oldRoles), $group);
        $form->handleRequest($this->request);
        $unavailableRoles = [];
        $roles = $form['platformRoles']->getData();

        foreach ($roles as $role) {
            $isAvailable = $this->roleManager->validateRoleInsert($group, $role);
            if (!$isAvailable) {
                $unavailableRoles[] = $role;
            }
        }

        if ($form->isValid() && count($unavailableRoles) === 0) {
            $group = $form->getData();
            $newRoles = $form['platformRoles']->getData();
            $this->groupManager->setPlatformRoles($group, $newRoles);
            $this->groupManager->updateGroup($group, $oldRoles);

            return $this->redirect($this->generateUrl('claro_admin_group_list'));
        }

        return array(
            'group' => $group,
            'form_settings' => $form->createView(),
            'unavailableRoles' => $unavailableRoles
        );
    }

    /**
     * @EXT\Route("/{groupId}/import", name="claro_admin_import_users_into_group_form")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template
     *
     * @param Group $group
     *
     * @return Response
     */
    public function importMembersFormAction(Group $group)
    {
        $form = $this->formFactory->create(FormFactory::TYPE_IMPORT_USERS_IN_GROUP);

        return array('form' => $form->createView(), 'group' => $group);
    }

    /**
     * @EXT\Route("/{groupId}/import/submit", name="claro_admin_import_users_into_group")
     * @EXT\Method("POST")
     * @EXT\ParamConverter(
     *      "group",
     *      class="ClarolineCoreBundle:Group",
     *      options={"id" = "groupId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Administration/Groups:importMembersForm.html.twig")
     *
     * @param Group $group
     *
     * @return Response
     */
    public function importMembersAction(Group $group)
    {
        $validFile = true;
        $form = $this->formFactory->create(FormFactory::TYPE_IMPORT_USERS_IN_GROUP);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $file = $form->get('file')->getData();
            $lines = str_getcsv(file_get_contents($file), PHP_EOL);

            foreach ($lines as $line) {
                $users[] = str_getcsv($line, ';');
            }

            if ($validFile && $this->groupManager->validateAddUsersToGroup($users, $group)) {

                $roleUser = $this->roleManager->getRoleByName('ROLE_USER');
                $max = $roleUser->getMaxUsers();
                $total = $this->userManager->countUsersByRoleIncludingGroup($roleUser);

                if ($total + count($users) > $max) {
                    return array('form' => $form->createView(), 'error' => true, 'group' => $group);
                }

                //$this->userManager->importUsers($users);
                $this->groupManager->importUsers($group, $users);

                return new RedirectResponse(
                    $this->router->generate('claro_admin_user_of_group_list', array('groupId' => $group->getId()))
                );
            }
        }

        return array('form' => $form->createView(), 'group' => $group);
    }
}
