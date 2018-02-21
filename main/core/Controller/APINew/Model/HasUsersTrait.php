<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\API\Crud;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Manages a users collection on an entity.
 */
trait HasUsersTrait
{
    /**
     * List users of the collection.
     *
     * @EXT\Route("/{id}/user")
     * @EXT\Method("GET")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listUsersAction($id, $class, Request $request)
    {
        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\User', array_merge(
                $request->query->all(),
                ['hiddenFilters' => [$this->getName() => [$id]]]
            ))
        );
    }

    /**
     * Adds users to the collection.
     *
     * @EXT\Route("/{id}/user")
     * @EXT\Method("PATCH")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function addUsersAction($id, $class, Request $request)
    {
        $object = $this->find($class, $id);
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $this->crud->patch($object, 'user', Crud::COLLECTION_ADD, $users);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Removes users from the collection.
     *
     * @EXT\Route("/{id}/user")
     * @EXT\Method("DELETE")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeUsersAction($id, $class, Request $request)
    {
        $object = $this->find($class, $id);
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $this->crud->patch($object, 'user', Crud::COLLECTION_REMOVE, $users);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }
}
