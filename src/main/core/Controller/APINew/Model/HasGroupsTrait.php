<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Group;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages a groups collection on an entity.
 */
trait HasGroupsTrait
{
    abstract protected function checkPermission($permission, $object, ?array $options = [], ?bool $throwException = false);

    /**
     * List groups of the collection.
     *
     * @Route("/{id}/group", methods={"GET"})
     * @ApiDoc(
     *     description="List the objects of class Claroline\CoreBundle\Entity\Group.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list=Claroline\CoreBundle\Entity\Group"}
     * )
     */
    public function listGroupsAction(string $id, string $class, Request $request): JsonResponse
    {
        $object = $this->crud->get($class, $id);
        $this->checkPermission('OPEN', $object, [], true);

        return new JsonResponse(
            $this->crud->list(Group::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => [$this->getName() => [$id]]]
            ))
        );
    }

    /**
     * Adds groups to the collection.
     *
     * @Route("/{id}/group", methods={"PATCH"})
     * @ApiDoc(
     *     description="Add objects of class Claroline\CoreBundle\Entity\Group.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The groups id or uuid."}
     *     }
     * )
     */
    public function addGroupsAction(string $id, string $class, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get($class, $id);
        $groups = $this->decodeIdsString($request, Group::class);
        $this->crud->patch($object, 'group', Crud::COLLECTION_ADD, $groups);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Removes groups from the collection.
     *
     * @Route("/{id}/group", methods={"DELETE"})
     * @ApiDoc(
     *     description="Removes objects of class Claroline\CoreBundle\Entity\Group.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The groups id or uuid."}
     *     }
     * )
     */
    public function removeGroupsAction(string $id, string $class, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get($class, $id);
        $groups = $this->decodeIdsString($request, Group::class);
        $this->crud->patch($object, 'group', Crud::COLLECTION_REMOVE, $groups);

        return new JsonResponse($this->serializer->serialize($object));
    }
}
