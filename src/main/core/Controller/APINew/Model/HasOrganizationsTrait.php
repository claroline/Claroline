<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages an organizations collection on an entity.
 */
trait HasOrganizationsTrait
{
    abstract protected function checkPermission($permission, $object, ?array $options = [], ?bool $throwException = false);

    /**
     * List organizations of the collection.
     *
     * @Route("/{id}/organization", methods={"GET"})
     * @ApiDoc(
     *     description="List the objects of class Claroline\CoreBundle\Entity\Organization\Organization.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list=Claroline\CoreBundle\Entity\Organization\Organization"}
     * )
     */
    public function listOrganizationsAction(string $id, string $class, Request $request): JsonResponse
    {
        $object = $this->crud->get($class, $id);
        $this->checkPermission('OPEN', $object, [], true);

        return new JsonResponse(
            $this->crud->list(Organization::class, array_merge(
                $request->query->all(),
                ['hiddenFilters' => [$this->getName() => [$id]]]
            ))
        );
    }

    /**
     * Adds organizations to the collection.
     *
     * @Route("/{id}/organization", methods={"PATCH"})
     * @ApiDoc(
     *     description="Adds objects of class Claroline\CoreBundle\Entity\Organization\Organization.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The organization id or uuid."}
     *     }
     * )
     */
    public function addOrganizationsAction(string $id, string $class, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get($class, $id);
        $organizations = $this->decodeIdsString($request, Organization::class);
        $this->crud->patch($object, 'organization', Crud::COLLECTION_ADD, $organizations);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Removes organizations from the collection.
     *
     * @Route("/{id}/organization", methods={"DELETE"})
     * @ApiDoc(
     *     description="Removes objects of class Claroline\CoreBundle\Entity\Organization\Organization.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The organization id or uuid."}
     *     }
     * )
     */
    public function removeOrganizationsAction(string $id, string $class, Request $request): JsonResponse
    {
        // no need to secure entrypoint, the CRUD will do it for us.

        $object = $this->crud->get($class, $id);
        $organizations = $this->decodeIdsString($request, Organization::class);
        $this->crud->patch($object, 'organization', Crud::COLLECTION_REMOVE, $organizations);

        return new JsonResponse(
              $this->serializer->serialize($object)
          );
    }
}
