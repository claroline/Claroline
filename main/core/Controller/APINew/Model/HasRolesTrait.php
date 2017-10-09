<?php

namespace Claroline\CoreBundle\Controller\APINew\Model;

use Claroline\CoreBundle\API\Crud;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

trait HasRolesTrait
{
    /**
     * @Route("{uuid}/role")
     * @Method("PATCH")
     */
    public function addRolesAction($uuid, $class, Request $request, $env)
    {
        try {
            $object = $this->find($class, $uuid);
            $roles = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Role');
            $this->crud->patch($object, 'role', Crud::COLLECTION_ADD, $roles);

            return new JsonResponse(
            $this->serializer->serialize($object)
        );
        } catch (\Exception $e) {
            $this->handleException($e, $env);
        }
    }

    /**
     * @Route("{uuid}/role")
     * @Method("DELETE")
     */
    public function removeRolesAction($uuid, $class, Request $request, $env)
    {
        try {
            $object = $this->find($class, $uuid);
            $roles = $this->decodeIdsString($request, 'Claroline\CoreBundle\Entity\Role');
            $this->crud->patch($object, 'role', Crud::COLLECTION_REMOVE, $roles);

            return new JsonResponse(
          $this->serializer->serialize($object)
      );
        } catch (\Exception $e) {
            $this->handleException($e, $env);
        }
    }
}
