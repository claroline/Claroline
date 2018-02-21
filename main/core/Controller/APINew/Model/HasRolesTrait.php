<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\AppBundle\API\Crud;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait HasRolesTrait
{
    /**
     * @EXT\Route("/{id}/role")
     * @EXT\Method("GET")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function listRolesAction($id, $class, Request $request)
    {
        return new JsonResponse(
            $this->finder->search('Claroline\CoreBundle\Entity\Role', array_merge(
                $request->query->all(),
                ['hiddenFilters' => [$this->getName() => [$id]]]
            ))
        );
    }

    /**
     * @EXT\Route("/{id}/role")
     * @EXT\Method("PATCH")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     * @param string  $env
     *
     * @return JsonResponse
     */
    public function addRolesAction($id, $class, Request $request)
    {
        $object = $this->find($class, $id);
        $roles = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Role');
        $this->crud->patch($object, 'role', Crud::COLLECTION_ADD, $roles);

        return new JsonResponse(
            $this->serializer->serialize($object)
        );
    }

    /**
     * @EXT\Route("/{id}/role")
     * @EXT\Method("DELETE")
     *
     * @param string  $id
     * @param string  $class
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function removeRolesAction($id, $class, Request $request)
    {
        $object = $this->find($class, $id);
        $roles = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Role');
        $this->crud->patch($object, 'role', Crud::COLLECTION_REMOVE, $roles);

        return new JsonResponse(
          $this->serializer->serialize($object)
      );
    }
}
