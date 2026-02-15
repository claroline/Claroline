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

use Claroline\AppBundle\API\Finder\FinderRequest;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\CoreBundle\Component\Context\DesktopContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Controller\Model\HasGroupsTrait;
use Claroline\CoreBundle\Controller\Model\HasUsersTrait;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\Tool\ToolManager;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedJsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/role', name: 'apiv2_role_')]
class RoleController extends AbstractCrudController
{
    use HasUsersTrait;
    use HasGroupsTrait;
    use PermissionCheckerTrait;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        private readonly ToolManager $toolManager,
        private readonly RoleManager $roleManager,
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

    #[Route(path: '/{roleType<platform|workspace|user>?platform}/{contextId}', name: 'list', methods: ['GET'])]
    public function listAction(
        #[MapQueryString]
        ?FinderRequest $finderRequest = new FinderRequest(),
        ?string $roleType = Role::PLATFORM,
        ?string $contextId = null,
    ): StreamedJsonResponse {
        $this->checkToolAccess('OPEN', $contextId);

        if ($contextId) {
            $workspace = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $contextId]);
            $finderRequest->addFilter('workspace', $workspace->getUuid());
        }

        $finderRequest->addFilter('type', $roleType);

        return parent::listAction($finderRequest);
    }

    /**
     * Get a role rights for the given context.
     */
    #[Route(path: '/{id}/rights/{contextType}/{contextId}', name: 'rights_list', defaults: ['contextId' => null], methods: ['GET'])]
    public function listRightsAction(
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Role $role,
        string $contextType,
        ?string $contextId = null
    ): JsonResponse {
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
     */
    #[Route(path: '/{id}/rights/{contextType}/{contextId}', name: 'rights_update', defaults: ['contextId' => null], methods: ['PUT'])]
    public function updateRightsAction(
        Request $request,
        #[MapEntity(mapping: ['id' => 'uuid'])]
        Role $role,
        string $contextType,
        ?string $contextId = null
    ): JsonResponse {
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

    #[Route(path: '/user', name: 'create_user_roles', methods: ['POST'])]
    public function generateUserRolesAction(Request $request): JsonResponse
    {
        $this->checkToolAccess('OPEN');

        $roles = [];
        $data = $this->decodeRequest($request);

        $this->om->startFlushSuite();
        foreach ($data as $roleData) {
            $roles[] = $this->crud->createOrUpdate(Role::class, $roleData);
        }
        $this->om->endFlushSuite();

        return new JsonResponse(array_map(function (Role $role) {
            return $this->serializer->serialize($role, [SerializerInterface::SERIALIZE_LIST]);
        }, $roles), 201);
    }

    #[Route(path: '/user/all', name: 'generate_all_user_roles', methods: ['POST'])]
    public function generateAllUserRolesAction(): JsonResponse
    {
        $this->checkToolAccess('ADMINISTRATE');

        $this->roleManager->generateUserRoles();

        return new JsonResponse();
    }

    private function checkToolAccess(string $permission, ?string $contextId = null): void
    {
        if ($contextId) {
            $communityTool = $this->toolManager->getOrderedTool('community', WorkspaceContext::getName(), $contextId);
        } else {
            $communityTool = $this->toolManager->getOrderedTool('community', DesktopContext::getName());
        }

        if (is_null($communityTool) || !$this->authorization->isGranted($permission, $communityTool)) {
            throw new AccessDeniedException(sprintf('Operation "%s" cannot be done on object %s', $permission, get_class($communityTool)));
        }
    }
}
