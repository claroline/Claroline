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
use Claroline\CoreBundle\Manager\FacetManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\WorkspaceManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\TranslatorInterface;

class UserController extends Controller
{
    private $facetManager;
    private $groupManager;
    private $roleManager;
    private $translator;
    private $userManager;
    private $workspaceManager;

    /**
     * @DI\InjectParams({
     *     "facetManager"     = @DI\Inject("claroline.manager.facet_manager"),
     *     "groupManager"     = @DI\Inject("claroline.manager.group_manager"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "translator"       = @DI\Inject("translator"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "workspaceManager" = @DI\Inject("claroline.manager.workspace_manager")
     * })
     */
    public function __construct(
        FacetManager $facetManager,
        GroupManager $groupManager,
        RoleManager $roleManager,
        TranslatorInterface $translator,
        UserManager $userManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->facetManager = $facetManager;
        $this->groupManager = $groupManager;
        $this->roleManager = $roleManager;
        $this->translator = $translator;
        $this->userManager = $userManager;
        $this->workspaceManager = $workspaceManager;
    }

    /**
     * @EXT\Route(
     *     "/searchInWorkspace/{workspaceId}/{search}",
     *     name="claro_user_search_in_workspace",
     *     options = {"expose"=true},
     *     requirements={"workspaceId" = "\d+"}
     * )
     *
     * @EXT\Template("ClarolineCoreBundle:User:user_search_workspace_results.html.twig")
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
     *     "user/picker/name/{pickerName}/title/{pickerTitle}/mode/{mode}/show/all/{showAllUsers}/filters/{showFilters}/{showId}/{showPicture}/{showUsername}/{showMail}/{showCode}/{showGroups}/{showPlatformRoles}/{attachName}/{filterAdminOrgas}",
     *     name="claro_user_picker",
     *     defaults={"mode"="single","showAllUsers"=0,"showFilters"=1,"showId"=0,"showPicture"=0,"showUsername"=1,"showMail"=0,"showCode"=0,"showGroups"=0,"showPlatformRoles"=0,"attachName"=1,"filterAdminOrgas"=0},
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
     * @EXT\ParamConverter(
     *     "shownWorkspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name" = "shownWorkspaceIds"}
     * )
     * @EXT\Template()
     */
    public function userPickerAction(
        User $authenticatedUser,
        array $excludedUsers,
        array $forcedUsers,
        array $selectedUsers,
        array $forcedGroups,
        array $forcedRoles,
        array $forcedWorkspaces,
        array $shownWorkspaces,
        $pickerName,
        $pickerTitle,
        $mode = 'single',
        $showAllUsers = 0,
        $showFilters = 1,
        $showId = 0,
        $showPicture = 0,
        $showUsername = 1,
        $showMail = 0,
        $showCode = 0,
        $showGroups = 0,
        $showPlatformRoles = 0,
        $attachName = 1,
        $filterAdminOrgas = 0
    ) {
        $adminRole = $this->roleManager->getRoleByUserAndRoleName($authenticatedUser, 'ROLE_ADMIN');
        $isAdmin = !is_null($adminRole);
        $excludedIds = [];
        $forcedUsersIds = [];
        $forcedGroupsIds = [];
        $forcedRolesIds = [];
        $forcedWorkspacesIds = [];
        $shownWorkspacesIds = [];

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
        foreach ($shownWorkspaces as $shownWorkspace) {
            $shownWorkspacesIds[] = $shownWorkspace->getId();
        }

        return [
            'pickerName' => $pickerName,
            'pickerTitle' => $pickerTitle,
            'mode' => $mode,
            'showAllUsers' => $showAllUsers,
            'showFilters' => $showFilters,
            'showId' => $showId,
            'showPicture' => $showPicture,
            'showUsername' => $showUsername,
            'showMail' => $showMail,
            'showCode' => $showCode,
            'showGroups' => $showGroups,
            'showPlatformRoles' => $showPlatformRoles,
            'attachName' => $attachName,
            'excludedUsersIds' => $excludedIds,
            'forcedUsersIds' => $forcedUsersIds,
            'selectedUsers' => $selectedUsers,
            'forcedGroupsIds' => $forcedGroupsIds,
            'forcedRolesIds' => $forcedRolesIds,
            'forcedWorkspacesIds' => $forcedWorkspacesIds,
            'shownWorkspacesIds' => $shownWorkspacesIds,
            'isAdmin' => $isAdmin,
            'filterAdminOrgas' => $filterAdminOrgas,
        ];
    }

    /**
     * @EXT\Route(
     *     "users/list/for/user/picker/mode/{mode}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/show/all/{showAllUsers}/{showId}/{showPicture}/{showUsername}/{showMail}/{showCode}/{showGroups}/{showPlatformRoles}/{attachName}/{filterAdminOrgas}",
     *     name="claro_users_list_for_user_picker",
     *     defaults={"page"=1, "max"=50, "orderedBy"="lastName","order"="ASC","search"="","mode"="single","showAllUsers"=0,"showId"=0,"showPicture"=0,"showUsername"=1,"showMail"=0,"showCode"=0,"showGroups"=0,"showPlatformRoles"=0,"attachName"=1,"filterAdminOrgas"=0},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "users/list/for/user/picker/mode/{mode}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}/show/all/{showAllUsers}/{showId}/{showPicture}/{showUsername}/{showMail}/{showCode}/{showGroups}/{showPlatformRoles}/{attachName}/{filterAdminOrgas}",
     *     name="claro_searched_users_list_for_user_picker",
     *     defaults={"page"=1, "max"=50, "orderedBy"="lastName","order"="ASC","search"="","mode"="single","showAllUsers"=0,"showId"=0,"showPicture"=0,"showUsername"=1,"showMail"=0,"showCode"=0,"showGroups"=0,"showPlatformRoles"=0,"attachName"=1,"filterAdminOrgas"=0},
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
     * @EXT\ParamConverter(
     *     "shownWorkspaces",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"multipleIds" = true, "name" = "shownWorkspaceIds"}
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
        array $shownWorkspaces,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC',
        $mode = 'single',
        $showAllUsers = 0,
        $showId = 0,
        $showPicture = 0,
        $showUsername = 1,
        $showMail = 0,
        $showCode = 0,
        $showGroups = 0,
        $showPlatformRoles = 0,
        $attachName = 1,
        $filterAdminOrgas = 0
    ) {
        $withAllUsers = intval($showAllUsers) === 1;
        $withUsername = intval($showUsername) === 1;
        $withMail = intval($showMail) === 1;
        $withCode = intval($showCode) === 1;
        $withAdminOrgas = intval($filterAdminOrgas) === 1;
        $profilePreferences = $this->facetManager->getVisiblePublicPreference();
        $shownWorkspaceIds = [];

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
            $forcedWorkspaces,
            $withAdminOrgas
        );

        foreach ($shownWorkspaces as $ws) {
            $shownWorkspaceIds[] = $ws->getId();
        }

        return [
            'users' => $users,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'mode' => $mode,
            'showAllUsers' => $showAllUsers,
            'showId' => $showId,
            'showPicture' => $showPicture,
            'showUsername' => $showUsername,
            'showMail' => $showMail,
            'showCode' => $showCode,
            'showGroups' => $showGroups,
            'showPlatformRoles' => $showPlatformRoles,
            'attachName' => $attachName,
            'profilePreferences' => $profilePreferences,
            'shownWorkspaceIds' => $shownWorkspaceIds,
            'filterAdminOrgas' => $filterAdminOrgas,
        ];
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
    ) {
        $datas = [];
        $adminRole = $this->roleManager->getRoleByUserAndRoleName(
            $authenticatedUser,
            'ROLE_ADMIN'
        );
        $isAdmin = !is_null($adminRole);

        switch ($filterType) {
            case 'group':

                if ($isAdmin) {
                    $groups = $this->groupManager->getAll();
                } else {
                    $groups = $authenticatedUser->getGroups();
                }

                foreach ($groups as $group) {
                    $id = $group->getId();
                    $name = $group->getName();
                    $datas[] = ['id' => $id, 'name' => $name];
                }
                break;

            case 'role':

                if ($isAdmin) {
                    $roles = $this->roleManager->getAllPlatformRoles();
                } else {
                    $roles = [];
                }

                foreach ($roles as $role) {
                    $id = $role->getId();
                    $name = $this->translator->trans(
                        $role->getTranslationKey(),
                        [],
                        'platform'
                    );
                    $datas[] = ['id' => $id, 'name' => $name];
                }
                break;

            case 'workspace':

                if ($isAdmin) {
                    $workspaces = $this->workspaceManager->getAllNonPersonalWorkspaces();
                } else {
                    $workspaces = $this->workspaceManager
                        ->getWorkspacesByUser($authenticatedUser);
                }

                foreach ($workspaces as $workspace) {
                    $id = $workspace->getId();
                    $name = $workspace->getName().' ['.$workspace->getCode().']';
                    $datas[] = ['id' => $id, 'name' => $name];
                }
                break;

            default:
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
        $datas = [];
        $wsRoles = $this->roleManager->getWorkspaceRoles($workspace);

        foreach ($wsRoles as $role) {
            $datas[] = [
                'id' => $role->getId(),
                'name' => $this->translator->trans(
                    $role->getTranslationKey(),
                    [],
                    'platform'
                ),
            ];
        }

        return new JsonResponse($datas, 200);
    }

    /**
     * @EXT\Route(
     *     "user/{user}/infos/request",
     *     name="claro_user_infos_request",
     *     options = {"expose"=true}
     * )
     */
    public function userInfosRequestAction(User $user)
    {
        $datas = [
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'username' => $user->getUsername(),
            'mail' => $user->getMail(),
            'phone' => $user->getPhone(),
            'picture' => $user->getPicture(),
        ];

        return new JsonResponse($datas, 200);
    }

    /**
     * @EXT\Route(
     *     "users/infos/request",
     *     name="claro_users_infos_request",
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter(
     *     "users",
     *      class="ClarolineCoreBundle:User",
     *      options={"multipleIds" = true, "name" = "userIds"}
     * )
     *
     * @param User[] $users
     *
     * @return JsonResponse
     */
    public function usersInfosRequestAction(array $users)
    {
        $data = [];

        foreach ($users as $user) {
            $userRole = $this->roleManager->getUserRole($user->getUsername());

            $data[] = [
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'username' => $user->getUsername(),
                'mail' => $user->getMail(),
                'phone' => $user->getPhone(),
                'picture' => $user->getPicture(),
                'administrative_code' => $user->getAdministrativeCode(),
                'guid' => $user->getUuid(),
                'personal_workspace_id' => is_null($user->getPersonalWorkspace()) ?
                    null :
                    $user->getPersonalWorkspace()->getId(),
                'user_role_id' => is_null($userRole) ? null : $userRole->getId(),

            ];
        }

        return new JsonResponse($data, 200);
    }
}
