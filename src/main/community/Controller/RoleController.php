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

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ToolManager $toolManager
    ) {
        $this->authorization = $authorization;
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
     *
     * @EXT\ParamConverter("role", options={"mapping": {"id": "uuid"}})
     */
    public function listRightsAction(Role $role, string $contextType, string $contextId = null): JsonResponse
    {
        $this->checkPermission('OPEN', $role, [], true);

        $rights = [];

        $orderedTools = $this->toolManager->getOrderedTools($contextType, $contextId);
        foreach ($orderedTools as $orderedTool) {
            $rights[$orderedTool->getName()] = $this->toolManager->getPermissions($orderedTool, $role);
        }

        return new JsonResponse($rights);
    }

    /**
     * Manages workspace tools accesses for a Role.
     *
     * @Route("/{id}/rights/{contextType}/{contextId}", name="apiv2_role_rights_update", defaults={"contextId"=null}, methods={"PUT"})
     *
     * @EXT\ParamConverter("role", options={"mapping": {"id": "uuid"}})
     */
    public function updateRightsAction(Request $request, Role $role, string $contextType, string $contextId = null): JsonResponse
    {
        $this->checkPermission('ADMINISTRATE', $role, [], true);

        $rightsData = $this->decodeRequest($request);

        if ($rightsData) {
            $this->om->startFlushSuite();

            foreach ($rightsData as $toolName => $toolRights) {
                $orderedTool = $this->toolManager->getOrderedTool($toolName, $contextType, $contextId);
                if ($orderedTool) {
                    $this->toolManager->setPermissions($toolRights, $orderedTool, $role);
                }
            }

            $this->om->endFlushSuite();
        }

        return new JsonResponse();
    }

    protected function getDefaultHiddenFilters(): array
    {
        return [
            'grantable' => true,
        ];
    }
}
