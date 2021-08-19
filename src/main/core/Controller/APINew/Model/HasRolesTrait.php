<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\Annotations\ApiDoc;
use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Role;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

trait HasRolesTrait
{
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
     *
     * @param string $id
     * @param string $class
     *
     * @return JsonResponse
     */
    public function listRolesAction($id, $class, Request $request)
    {
        return new JsonResponse(
            $this->finder->search(Role::class, array_merge(
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
     *
     * @param string $id
     * @param string $class
     * @param string $env
     *
     * @return JsonResponse
     */
    public function addRolesAction($id, $class, Request $request)
    {
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
     *
     * @param string $id
     * @param string $class
     *
     * @return JsonResponse
     */
    public function removeRolesAction($id, $class, Request $request)
    {
        $object = $this->crud->get($class, $id);
        $roles = $this->decodeIdsString($request, Role::class);
        $this->crud->patch($object, 'role', Crud::COLLECTION_REMOVE, $roles);

        return new JsonResponse(
          $this->serializer->serialize($object)
      );
    }
}
