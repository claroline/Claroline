<?php

namespace Claroline\CoreBundle\Controller\Model;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Manages a roles collection on an entity.
 */
trait HasRolesTrait
{
    abstract protected function checkPermission($permission, $object = null, ?array $options = [], ?bool $throwException = false): bool;

    abstract public static function getClass(): string;

    abstract public static function getName(): string;

    /**
     * @ApiDoc(
     *     description="List the objects of class Claroline\CoreBundle\Entity\Role.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list=Claroline\CoreBundle\Entity\Role"}
     * )
     */
    #[Route(path: '/{id}/role', name: 'list_roles', methods: ['GET'], priority: 1)]
    public function listRolesAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $this->crud->get(static::getClass(), $id);

        return new JsonResponse(
            $this->crud->list(Role::class, array_merge($request->query->all(), [
                'hiddenFilters' => [
                    static::getName() => [$id],
                    'grantable' => true,
                ],
            ]))
        );
    }

    /**
     * @ApiDoc(
     *     description="Add objects of class Claroline\CoreBundle\Entity\Role.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The role id or uuid."}
     *     }
     * )
     */
    #[Route(path: '/{id}/role', name: 'add_roles', methods: ['PATCH'], priority: 1)]
    public function addRolesAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);

        $roles = $this->decodeIdsString($request, Role::class);
        $this->crud->patch($object, 'role', Crud::COLLECTION_ADD, $roles);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * @ApiDoc(
     *     description="Remove objects of class Claroline\CoreBundle\Entity\Role.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The role id or uuid."}
     *     }
     * )
     */
    #[Route(path: '/{id}/role', name: 'remove_roles', methods: ['DELETE'], priority: 1)]
    public function removeRolesAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);

        $roles = $this->decodeIdsString($request, Role::class);
        $this->crud->patch($object, 'role', Crud::COLLECTION_REMOVE, $roles);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }
}
