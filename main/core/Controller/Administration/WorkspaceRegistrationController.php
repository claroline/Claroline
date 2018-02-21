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
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('registration_to_workspace')")
 */
class WorkspaceRegistrationController extends Controller
{
    private $adminWorkspaceRegistrationTool;
    private $groupManager;
    private $roleManager;
    private $session;
    private $translator;
    private $userManager;
    private $workspaceManager;
    private $workspaceTagManager;

    /**
     * @DI\InjectParams({
     *     "groupManager"        = @DI\Inject("claroline.manager.group_manager"),
     *     "roleManager"         = @DI\Inject("claroline.manager.role_manager"),
     *     "session"             = @DI\Inject("session"),
     *     "translator"          = @DI\Inject("translator"),
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager" = @DI\Inject("claroline.manager.workspace_tag_manager")
     * })
     */
    public function __construct(
        GroupManager $groupManager,
        RoleManager $roleManager,
        SessionInterface $session,
        TranslatorInterface $translator,
        UserManager $userManager,
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager
    ) {
        $this->groupManager = $groupManager;
        $this->roleManager = $roleManager;
        $this->session = $session;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
    }

    /**
     * @EXT\Route(
     *    "/registration/management/max/{max}",
     *    name="claro_admin_registration_management",
     *    defaults={"search"="","max"=20},
     *    options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "/registration/management/search/{search}/max/{max}",
     *     name="claro_admin_registration_management_search",
     *    defaults={"max"=20},
     *     options = {"expose"=true}
     * )
     *
     * @EXT\Template()
     *
     * @param string search
     *
     * @return Response
     */
    public function registrationManagementAction($search = '', $max = 20)
    {
        if ('' === $search) {
            $datas = $this->workspaceTagManager
                ->getDatasForWorkspaceList(false, $search, $max);

            return [
                'workspaces' => $datas['workspaces'],
                'tags' => $datas['tags'],
                'tagWorkspaces' => $datas['tagWorkspaces'],
                'hierarchy' => $datas['hierarchy'],
                'rootTags' => $datas['rootTags'],
                'displayable' => $datas['displayable'],
                'nonPersonalWs' => $datas['nonPersonalWs'],
                'personalWs' => $datas['personalWs'],
                'search' => '',
                'max' => $max,
            ];
        } else {
            $pager = $this->workspaceManager->getDisplayableWorkspacesBySearchPager($search, 1);

            return ['workspaces' => $pager, 'search' => $search];
        }
    }

    /**
     * @EXT\Route(
     *    "registration/management/users",
     *    name="claro_admin_registration_management_users",
     *    options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true}
     * )
     *
     * @EXT\Template()
     *
     * @param Workspace[] $workspaces
     *
     * @return Response
     */
    public function registrationManagementUserListAction(array $workspaces)
    {
        $pager = $this->userManager->getAllUsers(1);

        return ['workspaces' => $workspaces, 'users' => $pager, 'search' => ''];
    }

    /**
     * @EXT\Route(
     *    "registration/management/groups",
     *    name="claro_admin_registration_management_groups",
     *    options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true}
     * )
     *
     * @EXT\Template()
     *
     * @param Workspace[] $workspaces
     *
     * @return Response
     */
    public function registrationManagementGroupListAction(array $workspaces)
    {
        $pager = $this->groupManager->getGroups(1);

        return ['workspaces' => $workspaces, 'groups' => $pager, 'search' => ''];
    }

    /**
     * @EXT\Route(
     *     "/registration/list/users/page/{page}",
     *     name="claro_users_list_registration_pager",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Route(
     *     "/registration/list/users/page/{page}/search/{search}",
     *     name="claro_users_list_registration_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * Renders the user list in a pager for registration.
     *
     * @param int    $page
     * @param string $search
     *
     * @return Response
     */
    public function userListPagerAction($page, $search)
    {
        $pager = '' === $search ?
            $this->userManager->getAllUsers($page) :
            $this->userManager->getUsersByName($search, $page);

        return ['users' => $pager, 'search' => $search];
    }

    /**
     * @EXT\Route(
     *     "/registration/list/groups/page/{page}",
     *     name="claro_groups_list_registration_pager",
     *     defaults={"page"=1, "search"=""},
     *     options={"expose"=true}
     * )
     * @EXT\Route(
     *     "/registration/list/groups/page/{page}/search/{search}",
     *     name="claro_groups_list_registration_pager_search",
     *     defaults={"page"=1},
     *     options={"expose"=true}
     * )
     * @EXT\Template()
     *
     * Renders the group list in a pager for registration.
     *
     * @param int    $page
     * @param string $search
     *
     * @return Response
     */
    public function groupListPagerAction($page, $search)
    {
        $pager = '' === $search ?
            $this->groupManager->getGroups($page) :
            $this->groupManager->getGroupsByName($search, $page);

        return ['groups' => $pager, 'search' => $search];
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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name" = "workspaceIds"}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     *
     * @param string      $roleKey
     * @param Workspace[] $workspaces
     * @param User[]      $users
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscribeMultipleUsersToMultipleWorkspacesAction(
        $roleKey,
        array $workspaces,
        array $users
    ) {
        foreach ($workspaces as $workspace) {
            $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace($roleKey, $workspace);

            if (!is_null($role)) {
                $this->roleManager->associateRoleToMultipleSubjects($users, $role);
            }
        }

        $msg = '';

        foreach ($users as $user) {
            $msg .= $user->getFirstName().' '.$user->getLastName().' ';
            $msg .= $this->translator->trans(
                'has_been_suscribed_with_role',
                [],
                'platform'
            );
            $msg .= ' "'.
                $this->translator->trans(
                    $roleKey,
                    [],
                    'platform'
                ).
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
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name" = "workspaceIds"}
     * )
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     *
     * @param string      $roleKey
     * @param Workspace[] $workspaces
     * @param Group[]     $groups
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscribeMultipleGroupsToMultipleWorkspacesAction(
        $roleKey,
        array $workspaces,
        array $groups
    ) {
        foreach ($workspaces as $workspace) {
            $role = $this->roleManager->getRoleByTranslationKeyAndWorkspace($roleKey, $workspace);

            if (!is_null($role)) {
                $this->roleManager->associateRoleToMultipleSubjects($groups, $role);
            }
        }

        $msg = '';

        foreach ($groups as $group) {
            $msg .= '"'.$group->getName().'" ';
            $msg .= $this->translator->trans(
                'has_been_suscribed_with_role_group',
                [],
                'platform'
            );
            $msg .= ' "'.
                $this->translator->trans(
                    $roleKey,
                    [],
                    'platform'
                ).
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
     *
     * @param Role[] $roles
     * @param User[] $users
     *
     * @return Response
     */
    public function subscribeMultipleUsersToOneWorkspaceAction(
        array $roles,
        array $users
    ) {
        $msg = '';

        foreach ($users as $user) {
            foreach ($roles as $role) {
                $this->roleManager->associateRole($user, $role);
                $msg .= $user->getFirstName().' '.$user->getLastName().' ';
                $msg .= $this->translator->trans(
                    'has_been_suscribed_with_role',
                    [],
                    'platform'
                );
                $msg .= ' "'.
                    $this->translator->trans(
                        $role->getTranslationKey(),
                        [],
                        'platform'
                    ).
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
     *
     * @param Role[]  $roles
     * @param Group[] $groups
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscribeMultipleGroupsToOneWorkspaceAction(
        array $roles,
        array $groups
    ) {
        $msg = '';

        foreach ($groups as $group) {
            foreach ($roles as $role) {
                $this->roleManager->associateRole($group, $role);
                $msg .= '"'.$group->getName().'" ';
                $msg .= $this->translator->trans(
                    'has_been_suscribed_with_role_group',
                    [],
                    'platform'
                );
                $msg .= ' "'.
                    $this->translator->trans(
                        $role->getTranslationKey(),
                        [],
                        'platform'
                    ).
                    '"-;-';
            }
        }

        return new Response($msg, 200);
    }

    /**
     * @EXT\Route(
     *    "workspace/{workspace}/users/unregistration/management/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *    name="claro_admin_workspace_users_unregistration_management",
     *    defaults={"search"="","page"=1,"max"=50,"orderedBy"="username","order"="ASC"},
     *    options = {"expose"=true}
     * )
     * @EXT\Template()
     *
     * @param Workspace $workspace
     * @param string    $search
     * @param int       $page
     * @param int       $max
     * @param string    $orderedBy
     * @param string    $order
     *
     * @return Response
     */
    public function workspaceUsersUnregistrationManagementAction(
        Workspace $workspace,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'username',
        $order = 'ASC'
    ) {
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $pager = '' === $search ?
            $this->userManager->getByRolesIncludingGroups(
                $wsRoles,
                $page,
                $max,
                $orderedBy,
                $order
            ) :
            $this->userManager->getByRolesAndNameIncludingGroups(
                $wsRoles,
                $search,
                $page,
                $max,
                $orderedBy,
                $order
            );

        return [
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
        ];
    }

    /**
     * @EXT\Route(
     *    "workspace/{workspace}/groups/unregistration/management/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *    name="claro_admin_workspace_groups_unregistration_management",
     *    defaults={"search"="","page"=1,"max"=50,"orderedBy"="name","order"="ASC"},
     *    options = {"expose"=true}
     * )
     * @EXT\Template()
     *
     * @param Workspace $workspace
     * @param string    $search
     * @param int       $page
     * @param int       $max
     * @param string    $orderedBy
     * @param string    $order
     *
     * @return Response
     */
    public function workspaceGroupsUnregistrationManagementAction(
        Workspace $workspace,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'name',
        $order = 'ASC'
    ) {
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $pager = ('' === $search) ?
            $this->groupManager->getGroupsByRoles(
                $wsRoles,
                $page,
                $max,
                $orderedBy,
                $order
            ) :
            $this->groupManager->getGroupsByRolesAndName(
                $wsRoles,
                $search,
                $page,
                $max,
                $orderedBy,
                $order
            );

        return [
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
        ];
    }

    /**
     * @EXT\Route(
     *    "unregistration/management/workspace/{workspace}/roles/users",
     *    name="claro_admin_unsubscribe_users_from_workspace",
     *    options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     *
     * @param Workspace $workspace
     * @param User[]    $users
     *
     * @return Response
     */
    public function unsubscribeMultipleUsersFromWorkspaceAction(
        Workspace $workspace,
        array $users
    ) {
        $this->roleManager->resetWorkspaceRoleForSubjects($users, $workspace);
        $sessionFlashBag = $this->session->getFlashBag();

        foreach ($users as $user) {
            $msg = $user->getFirstName().' '.$user->getLastName().' ';
            $msg .= $this->translator->trans(
                'has_been_unregistered_from_workspace',
                [],
                'platform'
            );
            $sessionFlashBag->add('success', $msg);
        }

        return new Response('success', 200);
    }

    /**
     * @EXT\Route(
     *    "unregistration/management/workspace/{workspace}/roles/groups",
     *    name="claro_admin_unsubscribe_groups_from_workspace",
     *    options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name" = "subjectIds"}
     * )
     *
     * @param Workspace $workspace
     * @param Group[]   $groups
     *
     * @return Response
     */
    public function unsubscribeMultipleGroupsFromWorkspaceAction(
        Workspace $workspace,
        array $groups
    ) {
        $this->roleManager->resetWorkspaceRoleForSubjects($groups, $workspace);
        $sessionFlashBag = $this->session->getFlashBag();

        foreach ($groups as $group) {
            $msg = $group->getName().' ';
            $msg .= $this->translator->trans(
                'has_been_unregistered_from_workspace',
                [],
                'platform'
            );
            $sessionFlashBag->add('success', $msg);
        }

        return new Response('success', 200);
    }
}
