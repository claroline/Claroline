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
     *     "user/picker/mode/{mode}",
     *     name="claro_user_picker",
     *     defaults={"mode"="multiple"},
     *     options = {"expose"=true}
     * )
     * @EXT\ParamConverter("authenticatedUser", options={"authenticatedUser" = true})
     * @EXT\Template()
     */
    public function userPickerAction($mode = 'mutiple')
    {
        return array('mode' => $mode);
    }

    /**
     * @EXT\Route(
     *     "users/list/for/user/picker/mode/{mode}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}",
     *     name="claro_users_list_for_user_picker",
     *     defaults={"page"=1, "max"=50, "orderedBy"="lastName","order"="ASC","search"="","mode"="multiple"},
     *     options = {"expose"=true}
     * )
     * @EXT\Route(
     *     "users/list/for/user/picker/mode/{mode}/page/{page}/max/{max}/ordered/by/{orderedBy}/order/{order}/search/{search}",
     *     name="claro_searched_users_list_for_user_picker",
     *     defaults={"page"=1, "max"=50, "orderedBy"="lastName","order"="ASC","search"="","mode"="multiple"},
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
     * @EXT\Template()
     */
    public function usersListForUserPickerAction(
        User $authenticatedUser,
        array $groups,
        array $roles,
        array $workspaces,
        $search = '',
        $page = 1,
        $max = 50,
        $orderedBy = 'lastName',
        $order = 'ASC',
        $mode = 'multiple'
    )
    {
        $users = $this->userManager->getUsersForUserPicker(
            $authenticatedUser,
            $search,
            false,
            $page,
            $max,
            $orderedBy,
            $order,
            $workspaces,
            $roles,
            $groups
        );

        return array(
            'users' => $users,
            'search' => $search,
            'page' => $page,
            'max' => $max,
            'orderedBy' => $orderedBy,
            'order' => $order,
            'mode' => $mode
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
