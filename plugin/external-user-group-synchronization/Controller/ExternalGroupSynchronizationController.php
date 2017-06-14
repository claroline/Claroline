<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 4/12/17
 */

namespace Claroline\ExternalSynchronizationBundle\Controller;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\ExternalSynchronizationBundle\Manager\ExternalSynchronizationGroupManager;
use Claroline\ExternalSynchronizationBundle\Manager\ExternalSynchronizationManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ExternalGroupSynchronizationController extends Controller
{
    /**
     * @var ExternalSynchronizationManager
     * @DI\Inject("claroline.manager.external_user_group_sync_manager")
     */
    private $externalUserGroupSyncManager;

    /**
     * @var ExternalSynchronizationGroupManager
     * @DI\Inject("claroline.manager.external_group_sync_manager")
     */
    private $externalGroupSyncManager;

    /**
     * @var AuthorizationCheckerInterface
     * @DI\Inject("security.authorization_checker")
     */
    private $authorization;

    /** @var RoleManager
     *  @DI\Inject("claroline.manager.role_manager")
     */
    private $roleManager;

    /**
     * @var GroupManager
     * @DI\Inject("claroline.manager.group_manager")
     */
    private $groupManager;

    /**
     * @EXT\Route("/workspace/{workspace}/page/{page}/max/{max}/order/{order}/direction/{direction}/search/{search}",
     *     name="claro_admin_external_user_sync_groups_list",
     *     options={"expose"=true},
     *     defaults={"page"=1, "search"="", "max"=50, "order"="name", "direction"="ASC"},
     * )
     * @EXT\Template("ClarolineExternalSynchronizationBundle:Groups:list.html.twig")
     * @EXT\ParamConverter("user", options={"authenticatedUser" = true})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function externalGroupsListAction(Workspace $workspace, $page, $max, $order, $direction, $search)
    {
        $this->checkAccess($workspace);
        $canEdit = $this->hasEditionAccess($workspace);
        $isAdmin = $this->isAdmin();
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);
        $sources = $this->externalUserGroupSyncManager->getExternalSourcesNames(['group_config']);
        $pager = $this->externalGroupSyncManager->getExternalGroupsByRolesAndSearch(
            $wsRoles,
            $search,
            $page,
            $max,
            $order,
            $direction
        );

        return [
            'workspace' => $workspace,
            'pager' => $pager,
            'search' => $search,
            'wsRoles' => $wsRoles,
            'max' => $max,
            'order' => $order,
            'direction' => $direction,
            'canEdit' => $canEdit,
            'isAdmin' => $isAdmin,
            'sources' => $sources,
        ];
    }

    /**
     * @EXT\Route("/workspace/{workspace}/externalgroups/source/{source}/order/{order}/direction/{direction}/search/{search}",
     *     name="claro_admin_external_groups_list_search",
     *     defaults={"page"=1, "search"="", "order"="name", "direction"="ASC"},
     *     options={"expose"=true},
     * )
     * @EXT\Template("ClarolineExternalSynchronizationBundle:Groups:externalGroupsList.html.twig")
     */
    public function unregisteredExternalGroupsListAction(Workspace $workspace, $source, $order, $direction, $search)
    {
        $this->checkAccess($workspace);
        $canEdit = $this->hasEditionAccess($workspace);

        $externalGroups = $search ? $this->externalUserGroupSyncManager->searchGroupsForExternalSource($source, $search) : [];
        $wsRoles = $this->roleManager->getRolesByWorkspace($workspace);

        return [
            'externalGroups' => $externalGroups,
            'wsRoles' => $wsRoles,
            'source' => $source,
            'canEdit' => $canEdit,
        ];
    }

    /**
     * @EXT\Route("/workspace/{workspace}/externalgroups/source/{source}",
     *     name="claro_admin_external_groups_register",
     *     options={"expose"=true},
     * )
     * @EXT\ParamConverter(
     *     "roles",
     *     class="ClarolineCoreBundle:Role",
     *     options={"multipleIds"=true, "name"="roleIds"}
     * )
     */
    public function registerExternalGroupsAction(Request $request, array $roles, Workspace $workspace, $source)
    {
        $externalGroupIds = $request->get('groupIds');

        foreach ($externalGroupIds as $externalGroupId) {
            $externalGroup = $this->externalGroupSyncManager->getExternalGroupByExternalIdAndSourceSlug($externalGroupId, $source);
            if (is_null($externalGroup)) {
                // Group doesn't exist and has to be created
                $group = $this->externalUserGroupSyncManager->getExternalSourceGroupById($source, $externalGroupId);
                $extGroup = $this->externalGroupSyncManager->importExternalGroup(
                    $externalGroupId,
                    $roles,
                    $source,
                    $group['name'],
                    $group['code']
                );
                $this->externalUserGroupSyncManager->syncrhonizeGroupForExternalSource($source, $extGroup);
            } else {
                // External group exists, find related internal group and adjust roles
                $this->groupManager->setPlatformRoles($externalGroup->getGroup(), $roles);
            }
        }

        return new JsonResponse(['registered' => true], 200);
    }

    private function checkAccess(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('users', $workspace)) {
            throw new AccessDeniedException();
        }
    }

    private function hasEditionAccess(Workspace $workspace)
    {
        return $this->authorization->isGranted(['users', 'edit'], $workspace);
    }

    private function isAdmin()
    {
        return $this->authorization->isGranted('ROLE_ADMIN');
    }
}
