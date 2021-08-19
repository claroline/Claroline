<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Manages a users collection on an entity.
 */
trait HasUsersTrait
{
    /**
     * List users of the collection.
     *
     * @Route("/{id}/user", methods={"GET"})
     * @ApiDoc(
     *     description="List the objects of class Claroline\CoreBundle\Entity\User.",
     *     queryString={
     *         "$finder",
     *         {"name": "page", "type": "integer", "description": "The queried page."},
     *         {"name": "limit", "type": "integer", "description": "The max amount of objects per page."},
     *         {"name": "sortBy", "type": "string", "description": "Sort by the property if you want to."}
     *     },
     *     response={"$list=Claroline\CoreBundle\Entity\User"}
     * )
     *
     * @param string $id
     * @param string $class
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
     * @Route("/{id}/user", methods={"PATCH"})
     * @ApiDoc(
     *     description="Add objects of class Claroline\CoreBundle\Entity\User.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The user id or uuid."}
     *     }
     * )
     *
     * @param string $id
     * @param string $class
     *
     * @return JsonResponse
     */
    public function addUsersAction($id, $class, Request $request)
    {
        $object = $this->crud->get($class, $id);
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $this->crud->patch($object, 'user', Crud::COLLECTION_ADD, $users);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * Removes users from the collection.
     *
     * @Route("/{id}/user", methods={"DELETE"})
     * @ApiDoc(
     *     description="Removes objects of class Claroline\CoreBundle\Entity\User.",
     *     parameters={
     *         {"name": "id", "type": "string", "description": "The object id."}
     *     },
     *     response={"$object"},
     *     queryString={
     *         {"name": "ids[]", "type": {"string", "integer"}, "description": "The user id or uuid."}
     *     }
     * )
     *
     * @param string $id
     * @param string $class
     *
     * @return JsonResponse
     */
    public function removeUsersAction($id, $class, Request $request)
    {
        $object = $this->crud->get($class, $id);
        $users = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\User');
        $this->crud->patch($object, 'user', Crud::COLLECTION_REMOVE, $users);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }
}
