<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\API\Crud;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages a groups collection on an entity.
 */
trait HasGroupsTrait
{
    /**
     * List groups of the collection.
     *
     * @EXT\Route("/{id}/group")
     * @EXT\Method("GET")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listGroupsAction($id, $class, Request $request)
    {
        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Group', array_merge(
                $request->query->all(),
                ['hiddenFilters' => [$this->getName() => [$id]]]
            ))
        );
    }

    /**
     * Adds groups to the collection.
     *
     * @EXT\Route("/{id}/group")
     * @EXT\Method("PATCH")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addGroupsAction($id, $class, Request $request)
    {
        $object = $this->find($class, $id);
        $groups = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Group');
        $this->crud->patch($object, 'group', Crud::COLLECTION_ADD, $groups);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Removes groups from the collection.
     *
     * @EXT\Route("/{id}/group")
     * @EXT\Method("DELETE")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeGroupsAction($id, $class, Request $request)
    {
        $object = $this->find($class, $id);
        $groups = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Group');
        $this->crud->patch($object, 'group', Crud::COLLECTION_REMOVE, $groups);

        return new JsonResponse($this->serializer->serialize($object));
    }
}
