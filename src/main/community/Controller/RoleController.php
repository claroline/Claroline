<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Controller;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\APINew\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\APINew\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\LogManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @Route("/role")
 */
class RoleController extends AbstractCrudController
{
    use HasUsersTrait;
    use HasGroupsTrait;
    use PermissionCheckerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ToolManager */
    private $toolManager;
    /** @var LogManager */
    private $logManager;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ToolManager $toolManager,
        LogManager $logManager
    ) {
        $this->authorization = $authorization;
        $this->toolManager = $toolManager;
        $this->logManager = $logManager;
    }

    public function getName(): string
    {
        return 'role';
    }

    public function getClass(): string
    {
        return Role::class;
    }

    /**
     * @ApiDoc(
     *     description="List the objects of class $class.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list"}
     * )
     *
     * @param string $class
     */
    public function listAction(Request $request, $class): JsonResponse
    {
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return parent::listAction($request, $class);
    }

    /**
     * Get a role rights for the given context.
     *
     * @Route("/{id}/rights/{contextType}/{contextId}", name="apiv2_role_rights_list", defaults={"contextId"=null}, methods={"GET"})
     * @EXT\ParamConverter("role", options={"mapping": {"id": "uuid"}})
     */
    public function listRightsAction(Role $role, string $contextType, ?string $contextId = null): JsonResponse
    {
        $this->checkPermission('OPEN', $role, [], true);

        $rights = [];

        switch ($contextType) {
            case Tool::DESKTOP:
                // get desktop tools
                $orderedTools = $this->om->getRepository(OrderedTool::class)->findByDesktop();
                foreach ($orderedTools as $orderedTool) {
                    $rights[$orderedTool->getTool()->getName()] = $this->toolManager->getPermissions($orderedTool, $role);
                }

                break;
            case Tool::WORKSPACE:
                // get workspace tools
                $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $contextId]);
                $orderedTools = $this->om->getRepository(OrderedTool::class)->findByWorkspace($workspace);
                foreach ($orderedTools as $orderedTool) {
                    $rights[$orderedTool->getTool()->getName()] = $this->toolManager->getPermissions($orderedTool, $role);
                }

                break;
            case Tool::ADMINISTRATION:
                $adminTools = $this->om->getRepository(AdminTool::class)->findAll();
                foreach ($adminTools as $adminTool) {
                    $rights[$adminTool->getName()] = [
                        'open' => $role->getAdminTools()->contains($adminTool),
                    ];
                }

                break;
        }

        return new JsonResponse($rights);
    }

    /**
     * Manages workspace tools accesses for a Role.
     *
     * @Route("/{id}/rights/{contextType}/{contextId}", name="apiv2_role_rights_update", defaults={"contextId"=null}, methods={"PUT"})
     * @EXT\ParamConverter("role", options={"mapping": {"id": "uuid"}})
     */
    public function updateRightsAction(Request $request, Role $role, string $contextType, ?string $contextId = null): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $role, [], true);

        $rightsData = $this->decodeRequest($request);

        if ($rightsData) {
            $this->om->startFlushSuite();

            switch ($contextType) {
                case Tool::DESKTOP:
                case Tool::WORKSPACE:
                    foreach ($rightsData as $toolName => $toolRights) {
                        $orderedTool = $this->toolManager->getOrderedTool($toolName, $contextType, $contextId);
                        if ($orderedTool) {
                            $this->toolManager->setPermissions($toolRights, $orderedTool, $role);
                        }
                    }

                    break;
                case Tool::ADMINISTRATION:
                    foreach ($rightsData as $toolName => $toolRights) {
                        $adminTool = $this->toolManager->getAdminToolByName($toolName);
                        if ($adminTool) {
                            if ($toolRights['open']) {
                                $adminTool->addRole($role);
                            } else {
                                $adminTool->removeRole($role);
                            }

                            $this->om->persist($adminTool);
                        }
                    }

                    break;
            }

            $this->om->endFlushSuite();
        }

        return new JsonResponse();
    }

    /**
     * @Route("/{id}/analytics/{year}", name="apiv2_role_analytics")
     * @EXT\ParamConverter("role", options={"mapping": {"id": "uuid"}})
     * @EXT\ParamConverter("currentUser", converter="current_user", options={"allowAnonymous"=false})
     */
    public function analyticsAction(User $currentUser, Role $role, string $year): JsonResponse
    {
        $this->checkPermission('OPEN', $role, [], true);

        // get values for user administrated organizations
        $organizations = null;
        $defaultFilters = [];
        if (!$currentUser->hasRole('ROLE_ADMIN')) {
            $organizations = $currentUser->getOrganizations();
            $defaultFilters = [
                'organization' => $organizations,
            ];
        }

        $connections = $this->logManager->getData([
            'hiddenFilters' => array_merge($defaultFilters, [
                'doerActive' => true,
                'doerCreated' => $year.'-12-31',
                'doerRoles' => [$role->getId()],
                'action' => 'user-login',
                'unique' => true,

                // filter for current year
                'dateLog' => $year.'-01-01',
                'dateTo' => $year.'-12-31',
            ]),
        ]);

        $actions = $this->logManager->getData([
            'hiddenFilters' => array_merge($defaultFilters, [
                'doerActive' => true,
                'doerCreated' => $year.'-12-31',
                'doerRoles' => [$role->getId()],

                // filter for current year
                'dateLog' => $year.'-01-01',
                'dateTo' => $year.'-12-31',
            ]),
        ]);

        return new JsonResponse([
            'users' => $this->om->getRepository(User::class)->countUsersByRole($role, $organizations, $year.'-12-31'),
            'connections' => array_reduce($connections, function (int $total, array $connection) {
                return $total + ($connection['total'] ?? 0);
            }, 0),
            'actions' => array_reduce($actions, function (int $total, array $action) {
                return $total + ($action['total'] ?? 0);
            }, 0),
        ]);
    }

    protected function getDefaultHiddenFilters(): array
    {
        return [
            'grantable' => true,
        ];
    }
}
