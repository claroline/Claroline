<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages a roles collection on an entity.
 */
trait HasRolesTrait
{
    abstract protected function checkPermission($permission, $object, ?array $options = [], ?bool $throwException = false);

    /**
     * @ApiDoc(
     *     description="List the roles of an object.",
     *     queryString={
     *         "$finder=Claroline\CoreBundle\Entity\Role&",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     }
     * )
     *
     * @Route("/{id}/role", methods={"GET"})
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
    public function listRolesAction(string $id, string $class, Request $request): JsonResponse
    {
        $object = $this->crud->get($class, $id);
        $this->checkPermission('OPEN', $object, [], true);

        return new JsonResponse(
            $this->crud->list(Role::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => [$this->getName() => [$id]]]
            ))
        );
    }

    /**
     * @Route("/{id}/role", methods={"PATCH"})
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
    public function addRolesAction(string $id, string $class, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get($class, $id);
        $roles = $this->decodeIdsString($request, Role::class);
        $this->crud->patch($object, 'role', Crud::COLLECTION_ADD, $roles);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * @Route("/{id}/role", methods={"DELETE"})
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
    public function removeRolesAction(string $id, string $class, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get($class, $id);
        $roles = $this->decodeIdsString($request, Role::class);
        $this->crud->patch($object, 'role', Crud::COLLECTION_REMOVE, $roles);

        return new JsonResponse(
          $this->serializer->serialize($object)
      );
    }
}
