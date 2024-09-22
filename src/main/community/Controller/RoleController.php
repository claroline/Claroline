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

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Controller\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: '/role', name: 'apiv2_role_')]
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

    public static function getName(): string
    {
        return 'role';
    }

    public static function getClass(): string
    {
        return Role::class;
    }

    /**
     * Get a role rights for the given context.
     *
     */
    #[Route(path: '/{id}/rights/{contextType}/{contextId}', name: 'rights_list', defaults: ['contextId' => null], methods: ['GET'])]
    public function listRightsAction(#[MapEntity(mapping: ['id' => 'uuid'])]
    Role $role, string $contextType, string $contextId = null): JsonResponse
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
     */
    #[Route(path: '/{id}/rights/{contextType}/{contextId}', name: 'rights_update', defaults: ['contextId' => null], methods: ['PUT'])]
    public function updateRightsAction(Request $request, #[MapEntity(mapping: ['id' => 'uuid'])]
    Role $role, string $contextType, string $contextId = null): JsonResponse
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
        $this->checkPermission('IS_AUTHENTICATED_FULLY', null, [], true);

        return [
            'grantable' => true,
        ];
    }
}
