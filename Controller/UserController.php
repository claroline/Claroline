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

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class UserController extends Controller
{
    private $groupManager;
    private $roleManager;
    private $userManager;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "groupManager"     = @DI\Inject("claroline.manager.group_manager"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        GroupManager $groupManager,
        RoleManager $roleManager,
        UserManager $userManager,
        WorkspaceManager $workspaceManager
    )
    {
        $this->groupManager = $groupManager;
        $this->roleManager = $roleManager;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route("/searchInWorkspace/{workspaceId}/{search}",
     *      name="claro_user_search_in_workspace",
     *      options = {"expose"=true},
     *      requirements={"workspaceId" = "\d+"}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:User:user_search_workspace_results.html.twig")
     *
     */
    public function userSearchInWorkspaceAction($workspaceId, $search)
    {
        $workspace = $this->workspaceManager->getWorkspaceById($workspaceId);
        $users = $this->userManager->getAllUsersByWorkspaceAndName($workspace, $search, 1, 10);
        $usersArray = $this->userManager->toArrayForPicker($users);

        return new JsonResponse($usersArray);
    }

    /**
     * @EXT\Route(
     *     "user/picker/name/{pickerName}/title/{pickerTitle}/mode/{mode}/show/all/{showAllUsers}/filters/{showFilters}/{showUsername}/{showMail}/{showCode}",
     *     name="claro_user_picker",
     *     defaults={"mode"="multiple","showAllUsers"=0,"showFilters"=1,"showUsername"=1,"showMail"=0,"showCode"=0},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "excludedUsers",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "excludedUserIds"}
     * )
     * @EXT\ParamConverter(
     *     "forcedUsers",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "forcedUserIds"}
     * )
     * @EXT\ParamConverter(
     *     "selectedUsers",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "selectedUserIds"}
     * )
     * @EXT\ParamConverter(
     *     "forcedGroups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name" = "forcedGroupIds"}
     * )
     * @EXT\ParamConverter(
     *     "forcedRoles",
     *      class="ClarolineCoreBundle:Role",
     *      options={"multipleIds" = true, "name" = "forcedRoleIds"}
     * )
     * @EXT\ParamConverter(
     *     "forcedWorkspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name" = "forcedWorkspaceIds"}
     * )
     * @EXT\Template()
     */
    public function userPickerAction(
        array $excludedUsers,
        array $forcedUsers,
        array $selectedUsers,
        array $forcedGroups,
        array $forcedRoles,
        array $forcedWorkspaces,
        $pickerName,
        $pickerTitle,
        $mode = 'mutiple',
        $showAllUsers = 0,
        $showFilters = 1,
        $showUsername = 1,
        $showMail = 0,
        $showCode = 0
    )
    {
        $excludedIds = array();
        $forcedUsersIds = array();
        $forcedGroupsIds = array();
        $forcedRolesIds = array();
        $forcedWorkspacesIds = array();

        foreach ($excludedUsers as $excludedUser) {
            $excludedIds[] = $excludedUser->getId();
        }

        foreach ($forcedUsers as $forcedUser) {
            $forcedUsersIds[] = $forcedUser->getId();
        }

        foreach ($forcedGroups as $forcedGroup) {
            $forcedGroupsIds[] = $forcedGroup->getId();
        }

        foreach ($forcedRoles as $forcedRole) {
            $forcedRolesIds[] = $forcedRole->getId();
        }

        foreach ($forcedWorkspaces as $forcedWorkspace) {
            $forcedWorkspacesIds[] = $forcedWorkspace->getId();
        }

        return array(
            'pickerName' => $pickerName,
            'pickerTitle' => $pickerTitle,
            'mode' => $mode,
            'showAllUsers' => $showAllUsers,
            'showFilters' => $showFilters,
            'showUsername' => $showUsername,
            'showMail' => $showMail,
            'showCode' => $showCode,
            'excludedUsersIds' => $excludedIds,
            'forcedUsersIds' => $forcedUsersIds,
            'selectedUsers' => $selectedUsers,
            'forcedGroupsIds' => $forcedGroupsIds,
            'forcedRolesIds' => $forcedRolesIds,
            'forcedWorkspacesIds' => $forcedWorkspacesIds
        );
    }

    /**
     * @EXT\Route(
     *     "users/list/for/user/picker/mode/{mode}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/show/all/{showAllUsers}/{showUsername}/{showMail}/{showCode}",
     *     name="claro_users_list_for_user_picker",
     *     defaults={"page"=1, "max"=50, "orderedBy"="lastName","order"="ASC","search"="","mode"="multiple","showAllUsers"=0,"showUsername"=1,"showMail"=0,"showCode"=0},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "users/list/for/user/picker/mode/{mode}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/show/all/{showAllUsers}/{showUsername}/{showMail}/{showCode}/search/{search}",
     *     name="claro_searched_users_list_for_user_picker",
     *     defaults={"page"=1, "max"=50, "orderedBy"="lastName","order"="ASC","search"="","mode"="multiple","showAllUsers"=0,"showUsername"=1,"showMail"=0,"showCode"=0},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *     "groups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name" = "groupIds"}
     * )
     * @EXT\ParamConverter(
     *     "roles",
     *      class="ClarolineCoreBundle:Role",
     *      options={"multipleIds" = true, "name" = "roleIds"}
     * )
     * @EXT\ParamConverter(
     *     "workspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name" = "workspaceIds"}
     * )
     * @EXT\ParamConverter(
     *     "excludedUsers",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "excludedUserIds"}
     * )
     * @EXT\ParamConverter(
     *     "forcedUsers",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "forcedUserIds"}
     * )
     * @EXT\ParamConverter(
     *     "forcedGroups",
     *      class="ClarolineCoreBundle:Group",
     *      options={"multipleIds" = true, "name" = "forcedGroupIds"}
     * )
     * @EXT\ParamConverter(
     *     "forcedRoles",
     *      class="ClarolineCoreBundle:Role",
     *      options={"multipleIds" = true, "name" = "forcedRoleIds"}
     * )
     * @EXT\ParamConverter(
     *     "forcedWorkspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name" = "forcedWorkspaceIds"}
     * )
     * @EXT\Template()
     */
    public function usersListForUserPickerAction(
        User $authenticatedUser,
        array $groups,
        array $roles,
        array $workspaces,
        array $excludedUsers,
        array $forcedUsers,
        array $forcedGroups,
        array $forcedRoles,
        array $forcedWorkspaces,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC',
        $mode = 'multiple',
        $showAllUsers = 0,
        $showUsername = 1,
        $showMail = 0,
        $showCode = 0
    )
    {
        $withAllUsers = intval($showAllUsers) === 1;
        $withUsername = intval($showUsername) === 1;
        $withMail = intval($showMail) === 1;
        $withCode = intval($showCode) === 1;

        $users = $this->userManager->getUsersForUserPicker(
            $authenticatedUser,
            $search,
            $withAllUsers,
            $withUsername,
            $withMail,
            $withCode,
            $page,
            $max,
            $orderedBy,
            $order,
            $workspaces,
            $roles,
            $groups,
            $excludedUsers,
            $forcedUsers,
            $forcedGroups,
            $forcedRoles,
            $forcedWorkspaces
        );

        return array(
            'users' => $users,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'mode' => $mode,
            'showAllUsers' => $showAllUsers,
            'showUsername' => $showUsername,
            'showMail' => $showMail,
            'showCode' => $showCode
        );
    }

    /**
     * @EXT\Route(
     *     "filters/list/type/{filterType}/for/user/picker",
     *     name="claro_filters_list_for_user_picker",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function filtersListForUserPickerAction(
        User $authenticatedUser,
        $filterType
    )
    {
        $datas = array();
        $adminRole = $this->roleManager->getRoleByUserAndRoleName(
            $authenticatedUser,
            'ROLE_ADMIN'
        );
        $isAdmin = !is_null($adminRole);

        switch ($filterType) {
            case 'group':

                if ($isAdmin) {
                    $groups = $this->groupManager->getAllGroupsWithoutPager('name');
                } else {
                    $groups = $authenticatedUser->getGroups();
                }

                foreach ($groups as $group) {
                    $id = $group->getId();
                    $name = $group->getName();
                    $datas[] = array('id' => $id, 'name' => $name);
                }
                break;

            case 'role' :

                if ($isAdmin) {
                    $roles = $this->roleManager->getAllPlatformRoles();
                } else {
                    $roles = array();
                }

                foreach ($roles as $role) {
                    $id = $role->getId();
                    $name = $role->getTranslationKey();
                    $datas[] = array('id' => $id, 'name' => $name);
                }
                break;

            case 'workspace' :

                if ($isAdmin) {
                    $workspaces = $this->workspaceManager->getAllNonPersonalWorkspaces();
                } else {
                    $workspaces = $this->workspaceManager
                        ->getWorkspacesByUser($authenticatedUser);
                }

                foreach ($workspaces as $workspace) {
                    $id = $workspace->getId();
                    $name = $workspace->getName() . ' [' . $workspace->getCode() . ']';
                    $datas[] = array('id' => $id, 'name' => $name);
                }
                break;

            default :
                break;
        }

        return new JsonResponse($datas, 200);
    }

    /**
     * @EXT\Route(
     *     "workspace/{workspace}/roles/list/for/user/picker",
     *     name="claro_workspace_roles_list_for_user_picker",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     */
    public function workspaceRolesListForUserPickerAction(Workspace $workspace)
    {
        $datas = array();
        $wsRoles = $this->roleManager->getWorkspaceRoles($workspace);

        foreach ($wsRoles as $role) {
            $datas[] = array('id' => $role->getId(), 'name' => $role->getTranslationKey());
        }

        return new JsonResponse($datas, 200);
    }
}
