<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages an organizations collection on an entity.
 */
trait HasOrganizationsTrait
{
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
     *
     * @param string $id
     * @param string $class
     *
     * @return JsonResponse
     */
    public function listOrganizationsAction($id, $class, Request $request)
    {
        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Organization\Organization', array_merge(
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
     *
     * @param string $id
     * @param string $class
     *
     * @return JsonResponse
     */
    public function addOrganizationsAction($id, $class, Request $request)
    {
        $object = $this->crud->get($class, $id);
        $organizations = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Organization\Organization');
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
     *
     * @param string $id
     * @param string $class
     *
     * @return JsonResponse
     */
    public function removeOrganizationsAction($id, $class, Request $request)
    {
        $object = $this->crud->get($class, $id);
        $organizations = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Organization\Organization');
        $this->crud->patch($object, 'organization', Crud::COLLECTION_REMOVE, $organizations);

        return new JsonResponse(
              $this->serializer->serialize($object)
          );
    }
}
