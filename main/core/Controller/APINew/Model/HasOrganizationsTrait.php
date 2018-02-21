<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\API\Crud;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages an organizations collection on an entity.
 */
trait HasOrganizationsTrait
{
    /**
     * List organizations of the collection.
     *
     * @EXT\Route("/{id}/organization")
     * @EXT\Method("GET")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
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
     * @EXT\Route("/{id}/organization")
     * @EXT\Method("PATCH")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addOrganizationsAction($id, $class, Request $request)
    {
        $object = $this->find($class, $id);
        $organizations = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Organization\Organization');
        $this->crud->patch($object, 'organization', Crud::COLLECTION_ADD, $organizations);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Removes organizations from the collection.
     *
     * @EXT\Route("/{id}/organization")
     * @EXT\Method("DELETE")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeOrganizationsAction($id, $class, Request $request)
    {
        $object = $this->find($class, $id);
        $organizations = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Organization\Organization');
        $this->crud->patch($object, 'organization', Crud::COLLECTION_REMOVE, $organizations);

        return new JsonResponse(
              $this->serializer->serialize($object)
          );
    }
}
