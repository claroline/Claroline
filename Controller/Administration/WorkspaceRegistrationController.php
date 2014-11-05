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
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use Claroline\CoreBundle\Manager\WorkspaceTagManager;
use Claroline\CoreBundle\Manager\ToolManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WorkspaceRegistrationController extends Controller
{
    private $userManager;
    private $roleManager;
    private $groupManager;
    private $toolManager;
    private $workspaceManager;
    private $workspaceTagManager;
    private $formFactory;
    private $translator;
    private $sc;
    private $adminWorkspaceRegistrationTool;

    /**
     * @DI\InjectParams({
     *     "userManager"         = @DI\Inject("claroline.manager.user_manager"),
     *     "roleManager"         = @DI\Inject("claroline.manager.role_manager"),
     *     "groupManager"        = @DI\Inject("claroline.manager.group_manager"),
     *     "toolManager"         = @DI\Inject("claroline.manager.tool_manager"),
     *     "workspaceManager"    = @DI\Inject("claroline.manager.workspace_manager"),
     *     "workspaceTagManager" = @DI\Inject("claroline.manager.workspace_tag_manager"),
     *     "formFactory"         = @DI\Inject("claroline.form.factory"),
     *     "translator"          = @DI\Inject("translator"),
     *     "sc"                  = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        UserManager $userManager,
        RoleManager $roleManager,
        GroupManager $groupManager,
        ToolManager $toolManager,
        WorkspaceManager $workspaceManager,
        WorkspaceTagManager $workspaceTagManager,
        FormFactory $formFactory,
        Translator $translator,
        SecurityContextInterface $sc
    )
    {
        $this->userManager         = $userManager;
        $this->roleManager         = $roleManager;
        $this->groupManager        = $groupManager;
        $this->workspaceManager    = $workspaceManager;
        $this->workspaceTagManager = $workspaceTagManager;
        $this->formFactory         = $formFactory;
        $this->translator          = $translator;
        $this->toolManager         = $toolManager;
        $this->sc                  = $sc;
        $this->adminWorkspaceRegistrationTool
            = $this->toolManager->getAdminToolByName('registration_to_workspace');
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
        $this->checkOpen();

        if ($search === '') {
            $datas = $this->workspaceTagManager
                ->getDatasForWorkspaceList(false, $search, $max);

            return array(
                'workspaces'    => $datas['workspaces'],
                'tags'          => $datas['tags'],
                'tagWorkspaces' => $datas['tagWorkspaces'],
                'hierarchy'     => $datas['hierarchy'],
                'rootTags'      => $datas['rootTags'],
                'displayable'   => $datas['displayable'],
                'nonPersonalWs' => $datas['nonPersonalWs'],
                'personalWs'    => $datas['personalWs'],
                'search'        => '',
                'max'           => $max
            );
        } else {
            $pager = $this->workspaceManager->getDisplayableWorkspacesBySearchPager($search, 1);

            return array('workspaces' => $pager, 'search' => $search);
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
        $this->checkOpen();
        $pager = $this->userManager->getAllUsers(1);

        return array('workspaces' => $workspaces, 'users' => $pager, 'search' => '');
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
        $this->checkOpen();
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
     * @param integer $page
     * @param string  $search
     *
     * @return Response
     */
    public function userListPagerAction($page, $search)
    {
        $this->checkOpen();
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
     * @param integer $page
     * @param string  $search
     *
     * @return Response
     */
    public function groupListPagerAction($page, $search)
    {
        $this->checkOpen();
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
    )
    {
        $this->checkOpen();

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
    )
    {
        $this->checkOpen();

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
     *
     * @param Role[] $roles
     * @param User[] $users
     *
     * @return Response
     */
    public function subscribeMultipleUsersToOneWorkspaceAction(
        array $roles,
        array $users
    )
    {
        $this->checkOpen();
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
     *
     * @param Role[] $roles
     * @param Group[] $groups
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscribeMultipleGroupsToOneWorkspaceAction(
        array $roles,
        array $groups
    )
    {
        $this->checkOpen();
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

    private function checkOpen()
    {
        if ($this->sc->isGranted('OPEN', $this->adminWorkspaceRegistrationTool)) {
            return true;
        }

        throw new AccessDeniedException();
    }
}
