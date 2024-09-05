<?php

namespace Claroline\CoreBundle\Controller\Model;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages a workspaces collection on an entity.
 */
trait HasWorkspacesTrait
{
    abstract protected function checkPermission($permission, $object = null, ?array $options = [], ?bool $throwException = false): bool;

    abstract public static function getClass(): string;

    abstract public static function getName(): string;

    /**
     * List workspaces of the collection.
     *
     * @Route("/{id}/workspace", name="list_workspaces", methods={"GET"})
     *
     * @ApiDoc(
     *     description="List the objects of class Claroline\CoreBundle\Entity\Workspace\Workspace.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list=Claroline\CoreBundle\Entity\Workspace\Workspace"}
     * )
     */
    public function listWorkspacesAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $this->crud->get(static::getClass(), $id);

        return new JsonResponse(
            $this->crud->list(Workspace::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => [static::getName() => [$id]]]
            ))
        );
    }

    /**
     * Adds workspaces to the collection.
     *
     * @Route("/{id}/workspace", name="add_workspaces", methods={"PATCH"})
     *
     * @ApiDoc(
     *     description="Add objects of class Claroline\CoreBundle\Entity\Workspace\Workspace.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The workspace id or uuid."}
     *     }
     * )
     */
    public function addWorkspacesAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);

        $workspaces = $this->decodeIdsString($request, Workspace::class);
        $this->crud->patch($object, 'workspace', Crud::COLLECTION_ADD, $workspaces);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Removes workspaces from the collection.
     *
     * @Route("/{id}/workspace", name="remove_workspaces", methods={"DELETE"})
     *
     * @ApiDoc(
     *     description="Removes objects of class Claroline\CoreBundle\Entity\Workspace\Workspace.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The workspace id or uuid."}
     *     }
     * )
     */
    public function removeWorkspacesAction(string $id, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get(static::getClass(), $id);

        $workspaces = $this->decodeIdsString($request, Workspace::class);
        $this->crud->patch($object, 'workspace', Crud::COLLECTION_REMOVE, $workspaces);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }
}
